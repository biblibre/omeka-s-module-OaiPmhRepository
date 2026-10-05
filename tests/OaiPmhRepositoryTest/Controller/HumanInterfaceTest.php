<?php declare(strict_types=1);

namespace OaiPmhRepositoryTest\Controller;

use CommonTest\AbstractHttpControllerTestCase;
use OaiPmhRepositoryTest\OaiPmhRepositoryTestTrait;

/**
 * Css and javascript of the human interface.
 */
class HumanInterfaceTest extends AbstractHttpControllerTestCase
{
    use OaiPmhRepositoryTestTrait;

    public function tearDown(): void
    {
        $settings = $this->getApplicationServiceLocator()->get('Omeka\Settings');
        $settings->set('oaipmhrepository_human_interface', true);
        $settings->set('oaipmhrepository_human_interface_assets', 'local');
        parent::tearDown();
    }

    protected function identify(string $assets): \DOMDocument
    {
        $settings = $this->getApplicationServiceLocator()->get('Omeka\Settings');
        $settings->set('oaipmhrepository_global_repository', 'item_set');
        $settings->set('oaipmhrepository_human_interface', true);
        $settings->set('oaipmhrepository_human_interface_assets', $assets);
        $this->reset();
        $_SERVER['REQUEST_URI'] = '/';
        $this->dispatch('/oai?verb=Identify');
        $this->assertResponseStatusCode(200);
        $document = new \DOMDocument();
        $document->loadXML($this->getResponse()->getContent());
        return $document;
    }

    protected function assetsInstruction(\DOMDocument $document): ?string
    {
        $instruction = (new \DOMXPath($document))
            ->query("/processing-instruction('oai-pmh-repository-assets')")
            ->item(0);
        return $instruction ? $instruction->data : null;
    }

    public function testLocalAssets(): void
    {
        $basePath = dirname(__DIR__, 3) . '/asset/vendor/bootstrap';
        if (!file_exists("$basePath/css/bootstrap.min.css") || !file_exists("$basePath/js/bootstrap.bundle.min.js")) {
            $this->markTestSkipped('Run "composer install" to get the local assets.');
        }
        $data = $this->assetsInstruction($this->identify('local'));
        $this->assertNotNull($data);
        $this->assertMatchesRegularExpression('~^css="[^"]*/modules/OaiPmhRepository/asset/vendor/bootstrap/css/bootstrap\.min\.css[^"]*" js="[^"]*/modules/OaiPmhRepository/asset/vendor/bootstrap/js/bootstrap\.bundle\.min\.js[^"]*"$~', $data);
    }

    public function testCdnAssets(): void
    {
        $document = $this->identify('cdn');
        $this->assertNull($this->assetsInstruction($document));
        $this->assertSame('OAI-PMH', $document->documentElement->localName);
    }
}
