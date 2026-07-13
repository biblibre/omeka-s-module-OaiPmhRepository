<?php declare(strict_types=1);

namespace OaiPmhRepositoryTest\OaiPmh\Metadata;

use CommonTest\AbstractTestCase;
use OaiPmhRepository\OaiPmh\Metadata\OaiDc;
use OaiPmhRepository\OaiPmh\Metadata\OaiDcterms;
use OaiPmhRepositoryTest\OaiPmhRepositoryTestTrait;

/**
 * Tests for the file links and IIIF manifest options of the oai_dc format.
 *
 * @covers \OaiPmhRepository\OaiPmh\Metadata\OaiDc
 */
class OaiDcFileLinksTest extends AbstractTestCase
{
    use OaiPmhRepositoryTestTrait;

    protected $item;

    public function setUp(): void
    {
        parent::setUp();
        $this->loginAdmin();
        $this->item = $this->createItem([
            'dcterms:title' => [['@value' => 'Item sans média']],
        ]);
    }

    public function tearDown(): void
    {
        $this->cleanupResources();
    }

    /**
     * The Dublin Core element is derived from the configured property via the
     * DCMI dumb-down. The recommended properties (hasPart, hasFormat) both
     * dumb-down to dc:relation.
     */
    public function testDcElementForProperty(): void
    {
        $oaiDc = (new \ReflectionClass(OaiDc::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod(OaiDc::class, 'dcElementForProperty');
        $method->setAccessible(true);

        $cases = [
            'dcterms:hasPart' => 'dc:relation',
            'dcterms:hasFormat' => 'dc:relation',
            'dcterms:identifier' => 'dc:identifier',
            'dcterms:relation' => 'dc:relation',
            'dcterms:source' => 'dc:source',
            'dcterms:isPartOf' => 'dc:relation',
            'bibo:uri' => 'dc:identifier',
        ];
        foreach ($cases as $term => $expected) {
            $this->assertSame($expected, $method->invoke($oaiDc, $term), "term=$term");
        }
    }

    /**
     * The file link element depends on the format: oai_dc dumbs the property
     * down to a simple dc:* element, oai_dcterms keeps the property term as is.
     */
    public function testElementForFilePropertyPerFormat(): void
    {
        $method = function (string $class, string $term): string {
            $format = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
            $ref = new \ReflectionMethod($class, 'elementForFileProperty');
            $ref->setAccessible(true);
            return $ref->invoke($format, $term);
        };

        // oai_dc: DCMI dumb-down.
        $this->assertSame('dc:relation', $method(OaiDc::class, 'dcterms:hasPart'));
        $this->assertSame('dc:relation', $method(OaiDc::class, 'dcterms:hasFormat'));
        // oai_dcterms: property term kept as is.
        $this->assertSame('dcterms:hasPart', $method(OaiDcterms::class, 'dcterms:hasPart'));
        $this->assertSame('dcterms:hasFormat', $method(OaiDcterms::class, 'dcterms:hasFormat'));
    }

    /**
     * An item without media or digital objects produces no file link, whatever
     * the mode, and never errors (graceful end-to-end).
     */
    public function testFileLinkWithoutFilesIsGraceful(): void
    {
        $doc = $this->generateOaiDc([
            'file_link' => 'original',
            'iiif_manifest' => 'none',
            'file_property' => 'dcterms:hasPart',
            'iiif_manifest_property' => 'dcterms:hasFormat',
        ]);

        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('dc', 'http://purl.org/dc/elements/1.1/');
        // The title is present, but no file relation was added.
        $this->assertSame(1, $xpath->query('//dc:title')->length);
        $this->assertSame(0, $xpath->query('//dc:relation')->length);
    }

    /**
     * The IIIF manifest mode without any manifest module installed and without
     * a digital object referencing a manifest produces no manifest link.
     */
    public function testIiifManifestWithoutModuleIsGraceful(): void
    {
        $services = $this->getServiceLocator();
        if ($services->get('ViewHelperManager')->has('iiifUrl')
            || class_exists('IiifPresentation\Module', false)
        ) {
            $this->markTestSkipped('A IIIF manifest module is active.');
        }

        $doc = $this->generateOaiDc([
            'file_link' => 'none',
            'iiif_manifest' => 'manifest',
            'file_property' => 'dcterms:hasPart',
            'iiif_manifest_property' => 'dcterms:hasFormat',
        ]);

        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('dc', 'http://purl.org/dc/elements/1.1/');
        $this->assertSame(0, $xpath->query('//dc:relation')->length);
    }

    /**
     * Build an oai_dc format directly (without the factory, which requires an
     * MVC dispatch context) and append the metadata of the test item.
     *
     * @param array $oaiDcParams Overrides merged into the nested oai_dc params.
     */
    protected function generateOaiDc(array $oaiDcParams): \DOMDocument
    {
        $services = $this->getServiceLocator();

        $oaiSetManager = $services->get(\OaiPmhRepository\OaiPmh\OaiSetManager::class);
        $oaiSet = $oaiSetManager->get('basic');
        $oaiSet->setSetSpecType('none');

        $format = new OaiDc();
        $format
            ->setServices($services)
            ->setOaiSet($oaiSet)
            ->setParams([
                // Master switch on, so the file-link options below take effect.
                'expose_media' => true,
                'format_uri' => 'uri_attr_label',
                'format_resource' => 'url_attr_title',
                'format_resource_property' => 'dcterms:identifier',
                'format_literal_striptags' => false,
                'append_identifier_global' => 'api_url',
                'append_identifier_site' => 'api_url',
                'main_site_slug' => null,
                'oai_dc' => [
                    'class_type' => 'no',
                    'class_type_table' => null,
                    'bnf_vignette' => 'none',
                ],
            ] + $oaiDcParams);

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $root = $doc->createElement('metadata');
        $doc->appendChild($root);
        $format->appendMetadata($root, $this->item);

        // The dc:* nodes are built with literal prefixed names and a parent
        // xmlns declaration; serialize and reparse so the namespaces become
        // real and queryable by prefix.
        $reloaded = new \DOMDocument('1.0', 'UTF-8');
        $reloaded->loadXML($doc->saveXML());
        return $reloaded;
    }
}
