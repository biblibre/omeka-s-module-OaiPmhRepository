<?php declare(strict_types=1);

namespace OaiPmhRepositoryTest\Controller;

use CommonTest\AbstractHttpControllerTestCase;
use OaiPmhRepositoryTest\OaiPmhRepositoryTestTrait;

/**
 * Validity of the response to ListSets, in particular the set descriptions.
 */
class ListSetsTest extends AbstractHttpControllerTestCase
{
    use OaiPmhRepositoryTestTrait;

    protected $itemSet;
    protected $item;

    public function setUp(): void
    {
        parent::setUp();
        $this->loginAdmin();

        $this->itemSet = $this->api()->create('item_sets', [
            'o:is_public' => true,
            'dcterms:title' => [['type' => 'literal', 'property_id' => 1, '@value' => 'Set with description']],
            'dcterms:description' => [['type' => 'literal', 'property_id' => 4, '@value' => "First paragraph & more.\n\nSecond paragraph."]],
        ])->getContent();
        $this->item = $this->api()->create('items', [
            'o:is_public' => true,
            'o:item_set' => [['o:id' => $this->itemSet->id()]],
        ])->getContent();

        $settings = $this->getApplicationServiceLocator()->get('Omeka\Settings');
        $settings->set('oaipmhrepository_namespace_id', 'test');
        $settings->set('oaipmhrepository_global_repository', 'list_item_sets');
        $settings->set('oaipmhrepository_list_item_sets', [$this->itemSet->id()]);
    }

    public function tearDown(): void
    {
        $this->loginAdmin();
        foreach ([['items', $this->item], ['item_sets', $this->itemSet]] as [$resourceName, $resource]) {
            try {
                $this->api()->delete($resourceName, $resource->id());
            } catch (\Exception $e) {
            }
        }
        $this->cleanupResources();
    }

    public function testSetDescriptionIsValid(): void
    {
        $this->reset();
        $_SERVER['REQUEST_URI'] = '/';
        $this->dispatch('/oai?verb=ListSets');
        $this->assertResponseStatusCode(200);

        $document = new \DOMDocument();
        $document->loadXML($this->getResponse()->getContent());

        $xpath = new \DOMXPath($document);
        $xpath->registerNamespace('oai', 'http://www.openarchives.org/OAI/2.0/');
        $xpath->registerNamespace('oai_dc', 'http://www.openarchives.org/OAI/2.0/oai_dc/');
        $xpath->registerNamespace('dc', 'http://purl.org/dc/elements/1.1/');
        $this->assertSame(
            "First paragraph & more.\n\nSecond paragraph.",
            $xpath->evaluate('string(//oai:set/oai:setDescription/oai_dc:dc/dc:description)')
        );

        $previous = libxml_use_internal_errors(true);
        $isValid = $document->schemaValidate(dirname(__DIR__) . '/fixtures/xsd/oai-pmh-with-oai_dc.xsd');
        $errors = array_map(fn ($error) => trim($error->message), libxml_get_errors());
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $this->assertTrue($isValid, implode("\n", $errors));
    }
}
