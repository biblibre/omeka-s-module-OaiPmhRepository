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
     * Append the url of the files of the item (media and digital objects) to
     * the record, according to the configured file link mode.
     *
     * Modes original/large/medium/square output one url per file; mode iiif
     * outputs the IIIF Presentation manifest url of the item (one per record),
     * which is the right granularity for items backed by digital objects, as
     * the manifest aggregates them.
     */
    protected function appendFileLinks(DOMElement $oai, ItemRepresentation $item): void
    {
        $fileLink = $this->params['oai_dc']['file_link'] ?? 'none';
        if ($fileLink === 'none') {
            return;
        }

        $element = $this->dcElementForProperty($this->params['oai_dc']['file_property'] ?? 'dcterms:identifier');
        $uriAttributes = ['xsi:type' => 'dcterms:URI'];

        // Mode iiif: one manifest url for the whole item (covers media and
        // digital objects). Requires module IiifServer.
        if ($fileLink === 'iiif') {
            $viewHelpers = $this->services->get('ViewHelperManager');
            if (!$viewHelpers->has('iiifUrl')) {
                return;
            }
            $url = $viewHelpers->get('iiifUrl')->__invoke($item, 'iiifserver/manifest', '2');
            if ($url) {
                $this->appendNewElement($oai, $element, $url, $uriAttributes);
            }
            return;
        }

        // Per-file modes: standard media and digital objects (linked to the
        // item through resource values).
        foreach ($this->itemFiles($item) as $file) {
            $url = $fileLink === 'original'
                ? $file->originalUrl()
                : $file->thumbnailDisplayUrl($fileLink);
            if ($url) {
                $this->appendNewElement($oai, $element, $url, $uriAttributes);
            }
        }
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
