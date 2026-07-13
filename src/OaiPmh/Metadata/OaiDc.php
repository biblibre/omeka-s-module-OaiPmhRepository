<?php declare(strict_types=1);
/**
 * @author John Flatness, Yu-Hsun Lin
 * @copyright Copyright 2009 John Flatness, Yu-Hsun Lin
 * @copyright BibLibre, 2016
 * @copyright Daniel Berthereau, 2014-2023
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace OaiPmhRepository\OaiPmh\Metadata;

use DOMElement;
use Omeka\Api\Representation\ItemRepresentation;

/**
 * Class implementing metadata output for the required oai_dc metadata format.
 * oai_dc is output of the 15 unqualified Dublin Core fields.
 */
class OaiDc extends AbstractMetadata
{
    /** OAI-PMH metadata prefix */
    const METADATA_PREFIX = 'oai_dc';

    /** XML namespace for output format */
    const METADATA_NAMESPACE = 'http://www.openarchives.org/OAI/2.0/oai_dc/';

    /** XML schema for output format */
    const METADATA_SCHEMA = 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd';

    /** XML namespace for unqualified Dublin Core */
    const DC_NAMESPACE_URI = 'http://purl.org/dc/elements/1.1/';

    /**
     * Appends Dublin Core metadata.
     *
     * {@inheritDoc}
     */
    public function appendMetadata(DOMElement $metadataElement, ItemRepresentation $item): void
    {
        $document = $metadataElement->ownerDocument;

        $oai = $document->createElementNS(self::METADATA_NAMESPACE, 'oai_dc:dc');
        $metadataElement->appendChild($oai);

        /* Must manually specify XML schema uri per spec, but DOM won't include
         * a redundant xmlns:xsi attribute, so we just set the attribute
         */
        $oai->setAttribute('xmlns:dc', self::DC_NAMESPACE_URI);
        $oai->setAttribute('xmlns:xsi', parent::XML_SCHEMA_NAMESPACE_URI);
        $oai->setAttribute('xsi:schemaLocation', self::METADATA_NAMESPACE . ' ' .
            self::METADATA_SCHEMA);

        /* Each of the 15 unqualified Dublin Core elements, in the order
         * specified by the oai_dc XML schema
         */
        $localNames = [
            'title',
            'creator',
            'subject',
            'description',
            'publisher',
            'contributor',
            'date',
            'type',
            'format',
            'identifier',
            'source',
            'language',
            'relation',
            'coverage',
            'rights',
        ];

        $classType = $this->params['oai_dc']['class_type'];
        $classTypeTable = $this->params['oai_dc']['class_type_table'];
        $bnfVignette = $this->params['oai_dc']['bnf_vignette'];
        $formatUri = $this->params['format_uri'];
        $formatUriAttributes = in_array($formatUri, ['uri', 'uri_attr_label'])
            ? ['xsi:type' => 'dcterms:URI']
            : [];

        /* Must create elements using createElement to make DOM allow a
         * top-level xmlns declaration instead of wasteful and non-
         * compliant per-node declarations.
         */
        $values = $this->filterValuesPre($item);
        foreach ($localNames as $localName) {
            $vals = [];
            $term = 'dcterms:' . $localName;
            $termValues = $values[$term]['values'] ?? [];
            foreach ($termValues as $value) {
                [$text, $attributes] = $this->formatValue($value);
                $vals[] = $text;
                $this->appendNewElement($oai, 'dc:' . $localName, $text, $attributes);
            }
            // Option class as type.
            if ($classType !== 'no' && $localName === 'type' && ($class = $item->resourceClass())) {
                if ($classType === 'term') {
                    $text = $class->term();
                } elseif ($classType === 'local') {
                    $text = $class->localName();
                } elseif ($classType === 'label') {
                    $text = $item->displayResourceClassLabel();
                } elseif ($classType === 'table') {
                    $text = $classTypeTable->labelFromCode($class->term());
                } else {
                    $text = null;
                }
                if ($text && !in_array($text, $vals)) {
                    $this->appendNewElement($oai, 'dc:type', $text, []);
                }
            }
            // Option BnF vignette.
            elseif ($bnfVignette !== 'none' && $localName === 'relation') {
                $thumbnail = $item->thumbnailDisplayUrl($bnfVignette);
                if ($thumbnail && !in_array($thumbnail, $vals)) {
                    $this->appendNewElement($oai, 'dc:relation', 'vignette : ' . $thumbnail, []);
                }
            }
        }

        $appendIdentifier = $this->singleIdentifier($item);
        if ($appendIdentifier) {
            $this->appendNewElement($oai, 'dc:identifier', $appendIdentifier, $formatUriAttributes);
        }

        // Also append an identifier for each file
        if ($this->params['expose_media']) {
            foreach ($item->media() as $media) {
                $this->appendNewElement($oai, 'dc:identifier', $media->originalUrl(), $formatUriAttributes);
            }
        }

        $this->appendFileLinks($oai, $item);
    }

    /**
     * Append file links and/or the IIIF manifest link of the item to the
     * record, according to the configuration.
     *
     * - file_link (none/original/large/medium/square/iiif): one url per file
     *   (media and digital objects). "iiif" is the IIIF Image API info.json
     *   (image server or third party image server).
     * - iiif_manifest (none/iiifserver/iiif_presentation): the IIIF
     *   Presentation manifest, built by module IIIF Server or stored by a
     *   digital object of type IIIF Presentation.
     *
     * Both are written to the Dublin Core element derived from the configured
     * property.
     */
    protected function appendFileLinks(DOMElement $oai, ItemRepresentation $item): void
    {
        $params = $this->params['oai_dc'] ?? [];
        $fileLink = $params['file_link'] ?? 'none';
        $iiifManifest = $params['iiif_manifest'] ?? 'none';
        if ($fileLink === 'none' && $iiifManifest === 'none') {
            return;
        }

        $fileElement = $this->dcElementForProperty($params['file_property'] ?? 'dcterms:hasPart');
        $manifestElement = $this->dcElementForProperty($params['iiif_manifest_property'] ?? 'dcterms:hasFormat');
        $uriAttributes = ['xsi:type' => 'dcterms:URI'];
        $viewHelpers = $this->services->get('ViewHelperManager');

        // Per-file links: media and digital objects (linked through values).
        if ($fileLink !== 'none') {
            foreach ($this->itemFiles($item) as $file) {
                $url = $this->fileLinkUrl($file, $fileLink, $viewHelpers);
                if ($url) {
                    $this->appendNewElement($oai, $fileElement, $url, $uriAttributes);
                }
            }
        }

        // IIIF manifest link, resolved automatically.
        if ($iiifManifest !== 'none') {
            foreach ($this->iiifManifestUrls($item, $viewHelpers) as $url) {
                $this->appendNewElement($oai, $manifestElement, $url, $uriAttributes);
            }
        }
    }

    /**
     * Resolve the IIIF Presentation manifest url(s) of an item.
     *
     * A digital object that references an external manifest (type IIIF
     * Presentation) is authoritative and used as is. Otherwise the manifest is
     * built by whichever manifest module is installed (IIIF Server or IIIF
     * Presentation).
     *
     * @return string[]
     */
    protected function iiifManifestUrls(ItemRepresentation $item, $viewHelpers): array
    {
        // External manifests referenced by digital objects.
        $stored = [];
        foreach ($this->itemFiles($item) as $file) {
            if (method_exists($file, 'renderer')
                && $file->renderer() === 'iiif_presentation'
                && ($url = $file->source())
            ) {
                $stored[] = $url;
            }
        }
        if ($stored) {
            return $stored;
        }

        // Manifest built by module IIIF Server.
        if ($viewHelpers->has('iiifUrl')) {
            $url = $viewHelpers->get('iiifUrl')->__invoke($item, 'iiifserver/manifest', '2');
            return $url ? [$url] : [];
        }

        // Manifest built by module IIIF Presentation.
        if (class_exists('IiifPresentation\Module', false)) {
            try {
                $url = $viewHelpers->get('url')->__invoke(
                    'iiif-presentation-2/item/manifest',
                    ['item-id' => $item->id()],
                    ['force_canonical' => true]
                );
                return $url ? [$url] : [];
            } catch (\Throwable $e) {
                return [];
            }
        }

        return [];
    }

    /**
     * Build the url of a single file according to the file link mode.
     *
     * @param \Omeka\Api\Representation\AbstractResourceEntityRepresentation $file
     * @param \Laminas\View\HelperPluginManager $viewHelpers
     */
    protected function fileLinkUrl($file, string $mode, $viewHelpers): ?string
    {
        if ($mode === 'original') {
            return $file->originalUrl();
        }

        if ($mode === 'iiif') {
            // Remote IIIF image service (digital object or core IIIF media):
            // the stored info.json id is the service base.
            if (method_exists($file, 'renderer')
                && $file->renderer() === 'iiif'
                && method_exists($file, 'mediaData')
            ) {
                $data = $file->mediaData();
                $serviceId = is_array($data) ? ($data['id'] ?? $data['@id'] ?? null) : null;
                return is_string($serviceId) && $serviceId !== ''
                    ? rtrim($serviceId, '/') . '/info.json'
                    : null;
            }
            // Local file served by the image server (module IIIF Server).
            if ($viewHelpers->has('iiifMediaUrl')) {
                try {
                    return $viewHelpers->get('iiifMediaUrl')->__invoke($file) ?: null;
                } catch (\Throwable $e) {
                    return null;
                }
            }
            return null;
        }

        // Thumbnail sizes: large / medium / square.
        return $file->thumbnailDisplayUrl($mode);
    }

    /**
     * Collect the file-backed resources of an item: standard media plus digital
     * objects referenced through resource values.
     *
     * @return \Omeka\Api\Representation\AbstractResourceEntityRepresentation[]
     */
    protected function itemFiles(ItemRepresentation $item): array
    {
        $files = [];
        foreach ($item->media() as $media) {
            $files[] = $media;
        }
        foreach ($item->values() as $propertyData) {
            foreach ($propertyData['values'] as $value) {
                $valueResource = $value->valueResource();
                if ($valueResource && $valueResource->resourceName() === 'digital_objects') {
                    $files[$valueResource->id()] = $valueResource;
                }
            }
        }
        return array_values($files);
    }

    /**
     * Derive the Dublin Core element name (dc:*) from a dcterms property,
     * following the DCMI dumb-down. Defaults to dc:identifier.
     */
    protected function dcElementForProperty(string $term): string
    {
        static $simple = [
            'contributor', 'coverage', 'creator', 'date', 'description', 'format',
            'identifier', 'language', 'publisher', 'relation', 'rights', 'source',
            'subject', 'title', 'type',
        ];
        static $refinements = [
            'hasFormat' => 'relation', 'isFormatOf' => 'relation', 'hasPart' => 'relation',
            'isPartOf' => 'relation', 'hasVersion' => 'relation', 'isVersionOf' => 'relation',
            'references' => 'relation', 'isReferencedBy' => 'relation', 'replaces' => 'relation',
            'isReplacedBy' => 'relation', 'requires' => 'relation', 'isRequiredBy' => 'relation',
            'conformsTo' => 'relation', 'bibliographicCitation' => 'identifier',
            'extent' => 'format', 'medium' => 'format', 'abstract' => 'description',
            'tableOfContents' => 'description', 'provenance' => 'description',
            'spatial' => 'coverage', 'temporal' => 'coverage', 'accessRights' => 'rights',
            'license' => 'rights', 'alternative' => 'title',
        ];

        $local = strpos($term, ':') === false ? $term : substr($term, strpos($term, ':') + 1);
        if (in_array($local, $simple, true)) {
            return 'dc:' . $local;
        }
        return 'dc:' . ($refinements[$local] ?? 'identifier');
    }
}
