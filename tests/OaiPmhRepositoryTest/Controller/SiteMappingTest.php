<?php declare(strict_types=1);

namespace OaiPmhRepositoryTest\Controller;

use CommonTest\AbstractHttpControllerTestCase;
use OaiPmhRepositoryTest\OaiPmhRepositoryTestTrait;

/**
 * Specific mappings for the repository of a site.
 *
 * @see https://gitlab.com/Daniel-KM/Omeka-S-module-OaiPmhRepository/-/work_items/5
 */
class SiteMappingTest extends AbstractHttpControllerTestCase
{
    use OaiPmhRepositoryTestTrait;

    protected $site;
    protected $itemSet;
    protected $item;
    protected $mappingFile;

    public function setUp(): void
    {
        parent::setUp();
        $this->loginAdmin();

        $this->itemSet = $this->api()->create('item_sets', ['o:is_public' => true])->getContent();
        $this->site = $this->api()->create('sites', [
            'o:title' => 'Harvester site',
            'o:slug' => 'harvester',
            'o:theme' => 'default',
            'o:is_public' => true,
            'o:site_item_set' => [['o:item_set' => ['o:id' => $this->itemSet->id()]]],
        ])->getContent();
        $this->item = $this->api()->create('items', [
            'o:is_public' => true,
            'o:site' => [['o:id' => $this->site->id()]],
            'o:item_set' => [['o:id' => $this->itemSet->id()]],
            'dcterms:title' => [['type' => 'literal', 'property_id' => $this->propertyId('dcterms:title'), '@value' => 'Title']],
            'dcterms:alternative' => [['type' => 'literal', 'property_id' => $this->propertyId('dcterms:alternative'), '@value' => 'Local subject']],
        ])->getContent();

        $settings = $this->settings();
        $settings->set('oaipmhrepository_namespace_id', 'test');
        $settings->set('oaipmhrepository_global_repository', 'item_set');
        $settings->set('oaipmhrepository_by_site_repository', 'item_set');
        $settings->set('oaipmhrepository_map_properties_sites', [
            'harvester' => ['dcterms:alternative' => 'dcterms:subject'],
        ]);
    }

    public function tearDown(): void
    {
        $this->loginAdmin();
        $settings = $this->settings();
        $settings->set('oaipmhrepository_map_properties_sites', []);
        $settings->set('oaipmhrepository_mappers_sites', []);
        if ($this->mappingFile) {
            @unlink($this->mappingFile);
        }
        foreach ([['items', $this->item], ['sites', $this->site], ['item_sets', $this->itemSet]] as [$resourceName, $resource]) {
            try {
                $this->api()->delete($resourceName, $resource->id());
            } catch (\Exception $e) {
            }
        }
        $this->cleanupResources();
    }

    protected function settings()
    {
        return $this->getApplicationServiceLocator()->get('Omeka\Settings');
    }

    protected function propertyId(string $term): int
    {
        return $this->api()->search('properties', ['term' => $term])->getContent()[0]->id();
    }

    protected function getRecord(string $path, string $prefix): string
    {
        $this->reset();
        $_SERVER['REQUEST_URI'] = '/';
        $identifier = 'oai:test:' . $this->item->id();
        $this->dispatch("$path?verb=GetRecord&metadataPrefix=$prefix&identifier=$identifier");
        $this->assertResponseStatusCode(200);
        return $this->getResponse()->getContent();
    }

    public function testMapPropertiesOfSite(): void
    {
        $this->assertStringContainsString('<dcterms:subject>Local subject</dcterms:subject>', $this->getRecord('/s/harvester/oai', 'oai_dcterms'));
        $this->assertStringNotContainsString('<dcterms:subject>', $this->getRecord('/oai', 'oai_dcterms'));
    }

    public function testMapperOfSite(): void
    {
        $services = $this->getApplicationServiceLocator();
        if (!$services->has('Mapper\Mapper')) {
            $this->markTestSkipped('Requires Mapper module.');
        }

        $config = $services->get('Config');
        $dir = ($config['file_store']['local']['base_path'] ?: (OMEKA_PATH . '/files')) . '/mapping';
        @mkdir($dir, 0775, true);
        $this->mappingFile = $dir . '/oaipmhrepository_test_' . bin2hex(random_bytes(4)) . '.xml';
        file_put_contents($this->mappingFile, <<<'XML'
            <mapping>
                <info>
                    <label>Test</label>
                    <from>resources</from>
                    <to>oai_dc</to>
                    <querier>omeka</querier>
                </info>
                <params>
                    <param name="xmlns.oai_dc">http://www.openarchives.org/OAI/2.0/oai_dc/</param>
                    <param name="xmlns.dc">http://purl.org/dc/elements/1.1/</param>
                </params>
                <maps>
                    <map><from path="dcterms:alternative"/><to field="/oai_dc:dc/dc:coverage"/></map>
                </maps>
            </mapping>
            XML);
        $this->settings()->set('oaipmhrepository_mappers_sites', [
            'harvester' => ['oai_dc' => 'user:' . basename($this->mappingFile)],
        ]);

        $this->assertMatchesRegularExpression('~<dc:coverage[^>]*>Local subject</dc:coverage>~', $this->getRecord('/s/harvester/oai', 'oai_dc'));
        $this->assertStringNotContainsString('<dc:coverage', $this->getRecord('/oai', 'oai_dc'));
    }

    public function testConfigFormDisplaysMappingsBySite(): void
    {
        $this->reset();
        $_SERVER['REQUEST_URI'] = '/';
        $this->loginAdmin();
        $this->dispatch('/admin/module/configure?id=OaiPmhRepository');
        $this->assertResponseStatusCode(200);
        $this->assertQueryContentRegex('#oaipmhrepository_map_properties_sites', '~# harvester\s+dcterms:alternative = dcterms:subject~');
    }
}
