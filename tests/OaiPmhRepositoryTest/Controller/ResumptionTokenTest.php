<?php declare(strict_types=1);

namespace OaiPmhRepositoryTest\Controller;

use CommonTest\AbstractHttpControllerTestCase;
use OaiPmhRepositoryTest\OaiPmhRepositoryTestTrait;

/**
 * Resumption of lists with a token.
 *
 * @see https://gitlab.com/Daniel-KM/Omeka-S-module-OaiPmhRepository/-/work_items/9
 */
class ResumptionTokenTest extends AbstractHttpControllerTestCase
{
    use OaiPmhRepositoryTestTrait;

    protected $itemSet;
    protected $items = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->loginAdmin();

        $this->itemSet = $this->api()->create('item_sets', ['o:is_public' => true])->getContent();
        foreach ([1, 2] as $i) {
            $this->items[] = $this->api()->create('items', [
                'o:is_public' => true,
                'o:item_set' => [['o:id' => $this->itemSet->id()]],
            ])->getContent();
        }

        $settings = $this->getApplicationServiceLocator()->get('Omeka\Settings');
        $settings->set('oaipmhrepository_namespace_id', 'test');
        $settings->set('oaipmhrepository_global_repository', 'item_set');
        $settings->set('oaipmhrepository_list_item_sets', [$this->itemSet->id()]);
        $settings->set('oaipmhrepository_list_limit', 1);
    }

    public function tearDown(): void
    {
        $this->loginAdmin();
        $this->getApplicationServiceLocator()->get('Omeka\Settings')->set('oaipmhrepository_list_limit', 50);
        foreach ($this->items as $item) {
            try {
                $this->api()->delete('items', $item->id());
            } catch (\Exception $e) {
            }
        }
        try {
            $this->api()->delete('item_sets', $this->itemSet->id());
        } catch (\Exception $e) {
        }
        $this->cleanupResources();
    }

    protected function request(string $query): \SimpleXMLElement
    {
        $this->reset();
        $_SERVER['REQUEST_URI'] = '/';
        $this->dispatch('/oai?' . $query);
        $this->assertResponseStatusCode(200);
        return simplexml_load_string($this->getResponse()->getContent());
    }

    public function testResumeList(): void
    {
        $xml = $this->request('verb=ListIdentifiers&metadataPrefix=oai_dc&set=' . $this->itemSet->id());
        $first = (string) $xml->ListIdentifiers->header->identifier;
        $token = (string) $xml->ListIdentifiers->resumptionToken;
        $this->assertNotSame('', $token);

        $xml = $this->request('verb=ListIdentifiers&resumptionToken=' . $token);
        $this->assertObjectNotHasProperty('error', $xml);
        $second = (string) $xml->ListIdentifiers->header->identifier;
        $this->assertNotSame($first, $second);
        $this->assertContains($second, array_map(fn ($item) => 'oai:test:' . $item->id(), $this->items));
    }

    /**
     * The current date must be passed as a date time, not as a string with a
     * time zone offset, that MySQL 8 converts into the session time zone.
     */
    public function testExpiredQueryUsesDateTime(): void
    {
        $services = $this->getApplicationServiceLocator();
        $qb = $services->get('Omeka\EntityManager')->createQueryBuilder()
            ->select('omeka_root')
            ->from(\OaiPmhRepository\Entity\OaiPmhRepositoryToken::class, 'omeka_root');
        $services->get('Omeka\ApiAdapterManager')->get('oaipmhrepository_tokens')
            ->buildQuery($qb, ['expired' => true]);

        $parameters = $qb->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertInstanceOf(\DateTimeInterface::class, $parameters->first()->getValue());
    }
}
