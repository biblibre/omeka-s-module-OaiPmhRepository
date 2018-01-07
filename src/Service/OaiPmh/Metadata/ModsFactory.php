<?php

namespace OaiPmhRepository\Service\OaiPmh\Metadata;

use Interop\Container\ContainerInterface;
use OaiPmhRepository\OaiPmh\Metadata\Mods;
use Zend\ServiceManager\Factory\FactoryInterface;

class ModsFactory implements FactoryInterface
{
    /**
     * Prepare the Mods format.
     *
     * @return Mods
     */
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $settings = $services->get('Omeka\Settings');
        $oaiSetManager = $services->get('OaiPmhRepository\OaiPmh\OaiSetManager');
        $oaiSet = $oaiSetManager->get($settings->get('oaipmhrepository_oai_set_format', 'base'));
        $metadataFormat = new Mods();
        $metadataFormat->setSettings($settings);
        $metadataFormat->setOaiSet($oaiSet);
        return $metadataFormat;
    }
}
