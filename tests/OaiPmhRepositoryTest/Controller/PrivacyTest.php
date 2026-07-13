<?php declare(strict_types=1);

namespace OaiPmhRepositoryTest\Controller;

use CommonTest\AbstractHttpControllerTestCase;
use OaiPmhRepositoryTest\OaiPmhRepositoryTestTrait;

/**
 * Ensure the OAI-PMH repository never leaks private resources or private values
 * to an anonymous harvester.
 *
 * The protection comes from the core visibility filters (ResourceVisibility and
 * ValueVisibility), which only apply when the request has no view-all identity.
 * So every test logs out before dispatching, to act as an anonymous harvester,
 * and asserts that private content is absent from the response.
 */
class PrivacyTest extends AbstractHttpControllerTestCase
{
    use OaiPmhRepositoryTestTrait;

    const PUBLIC_TITLE = 'PUBLIC_CONTROL_TITLE';
    const PRIVATE_TITLE = 'PRIVATE_ITEM_TITLE';
    const PUBLIC_VALUE = 'PUBLIC_VALUE_DESCRIPTION';
    const PRIVATE_VALUE = 'PRIVATE_VALUE_DESCRIPTION';

    protected $publicItem;
    protected $privateItem;
    protected $mixedItem;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAdmin();

        $easyMeta = $this->getServiceLocator()->get('Common\EasyMeta');
        $titleId = $easyMeta->propertyId('dcterms:title');
        $descId = $easyMeta->propertyId('dcterms:description');

        // Public control item.
        $this->publicItem = $this->createResource([
            'o:is_public' => true,
            'dcterms:title' => [[
                'type' => 'literal',
                'property_id' => $titleId,
                '@value' => self::PUBLIC_TITLE,
            ]],
        ]);

        // Fully private item.
        $this->privateItem = $this->createResource([
            'o:is_public' => false,
            'dcterms:title' => [[
                'type' => 'literal',
                'property_id' => $titleId,
                '@value' => self::PRIVATE_TITLE,
            ]],
        ]);

        // Public item carrying one public value and one private value.
        $this->mixedItem = $this->createResource([
            'o:is_public' => true,
            'dcterms:description' => [
                [
                    'type' => 'literal',
                    'property_id' => $descId,
                    '@value' => self::PUBLIC_VALUE,
                    'is_public' => true,
                ],
                [
                    'type' => 'literal',
                    'property_id' => $descId,
                    '@value' => self::PRIVATE_VALUE,
                    'is_public' => false,
                ],
            ],
        ]);

        $this->settings()->set('oaipmhrepository_namespace_id', 'test');
        $this->settings()->set('oaipmhrepository_global_repository', 'item_set');
        $this->settings()->set('oaipmhrepository_by_site_repository', 'none');
        $this->settings()->set('oaipmhrepository_hide_empty_sets', false);

        $this->reset();

        $_SERVER['REQUEST_URI'] = '/';
    }

    public function tearDown(): void
    {
        $this->loginAdmin();
        $this->cleanupResources();
    }

    protected function settings()
    {
        return $this->getApplicationServiceLocator()->get('Omeka\Settings');
    }

    protected function createResource(array $data)
    {
        $item = $this->api()->create('items', $data)->getContent();
        $this->createdResources[] = ['type' => 'items', 'id' => $item->id()];
        return $item;
    }

    protected function dispatchOai(string $query): string
    {
        // Dispatch as an anonymous harvester: the base dispatch() re-logins the
        // admin after reset, which would disable the visibility filters.
        $this->dispatchUnauthenticated('/oai?' . $query);
        $this->assertResponseStatusCode(200);
        return (string) $this->getResponse()->getContent();
    }

    public function testPrivateItemAbsentFromListRecords(): void
    {
        $body = $this->dispatchOai('verb=ListRecords&metadataPrefix=oai_dc');
        $this->assertStringContainsString(self::PUBLIC_TITLE, $body);
        $this->assertStringNotContainsString(self::PRIVATE_TITLE, $body);
    }

    public function testPrivateItemAbsentFromListIdentifiers(): void
    {
        $body = $this->dispatchOai('verb=ListIdentifiers&metadataPrefix=oai_dc');
        $expectedPublic = 'oai:test:' . $this->publicItem->id();
        $hiddenPrivate = 'oai:test:' . $this->privateItem->id();
        $this->assertStringContainsString($expectedPublic, $body);
        $this->assertStringNotContainsString($hiddenPrivate, $body);
    }

    public function testPrivateValueAbsentFromRecord(): void
    {
        $body = $this->dispatchOai('verb=ListRecords&metadataPrefix=oai_dc');
        $this->assertStringContainsString(self::PUBLIC_VALUE, $body);
        $this->assertStringNotContainsString(self::PRIVATE_VALUE, $body);
    }

    public function testGetRecordPublicItemSucceeds(): void
    {
        $id = 'oai:test:' . $this->publicItem->id();
        $body = $this->dispatchOai('verb=GetRecord&metadataPrefix=oai_dc&identifier=' . $id);
        $this->assertStringContainsString(self::PUBLIC_TITLE, $body);
    }

    public function testGetRecordPrivateItemIsHidden(): void
    {
        $id = 'oai:test:' . $this->privateItem->id();
        $body = $this->dispatchOai('verb=GetRecord&metadataPrefix=oai_dc&identifier=' . $id);
        $this->assertStringNotContainsString(self::PRIVATE_TITLE, $body);
        $this->assertStringContainsString('idDoesNotExist', $body);
    }

    /**
     * The Mapper delegation serializes the item representation, whose private
     * values are already filtered for an anonymous request. So a mapping cannot
     * expose a value the representation does not carry.
     */
    public function testMapperDelegationInheritsValueFiltering(): void
    {
        // Rebuild a fresh anonymous application, so the visibility filters
        // apply (a cached admin ACL would otherwise expose everything).
        $this->reset();
        $this->getApplication();
        $this->logout();
        $services = $this->getApplicationServiceLocator();

        if (!$services->has('Mapper\Mapper')) {
            $this->markTestSkipped('Requires Mapper module.');
        }

        $item = $services->get('Omeka\ApiManager')
            ->read('items', $this->mixedItem->id())->getContent();

        $descriptions = [];
        foreach ($item->values() as $propertyData) {
            foreach ($propertyData['values'] as $value) {
                $descriptions[] = (string) $value->value();
            }
        }
        $this->assertContains(self::PUBLIC_VALUE, $descriptions);
        $this->assertNotContains(self::PRIVATE_VALUE, $descriptions);

        $mapper = $services->get('Mapper\Mapper');
        $mapper->setMapping(
            'privacy',
            file_get_contents(dirname(__DIR__, 4) . '/Mapper/data/mapping/dublin-core/omeka_to_oai_dc.xml')
        );
        $doc = $mapper->serialize($item);
        $xml = $doc instanceof \DOMDocument ? $doc->saveXML() : '';
        $this->assertStringContainsString(self::PUBLIC_VALUE, $xml);
        $this->assertStringNotContainsString(self::PRIVATE_VALUE, $xml);
    }
}
