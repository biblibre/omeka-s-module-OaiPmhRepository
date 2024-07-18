<?php declare(strict_types=1);

namespace OaiPmhRepository;

return [
    'api_adapters' => [
        'invokables' => [
            'oaipmh_repository_tokens' => Api\Adapter\OaiPmhRepositoryTokenAdapter::class,
        ],
    ],
    'entity_manager' => [
        'mapping_classes_paths' => [
            dirname(__DIR__) . '/src/Entity',
        ],
        'proxy_paths' => [
            dirname(__DIR__) . '/data/doctrine-proxies',
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
    ],
    'form_elements' => [
        'factories' => [
            Form\ConfigForm::class => Service\Form\ConfigFormFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\RequestController::class => Service\Controller\RequestControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            OaiPmh\MetadataFormatManager::class => Service\OaiPmh\MetadataFormatManagerFactory::class,
            OaiPmh\OaiSetManager::class => Service\OaiPmh\OaiSetManagerFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'site' => [
                'child_routes' => [
                    'oai-pmh' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/oai',
                            'defaults' => [
                                '__NAMESPACE__' => 'OaiPmhRepository\Controller',
                                'controller' => Controller\RequestController::class,
                                'action' => 'index',
                                'oai-repository' => 'by_site',
                            ],
                        ],
                    ],
                ],
            ],
            'oai-pmh' => [
                'type' => \Laminas\Router\Http\Literal::class,
                'options' => [
                    'route' => '/oai',
                    'defaults' => [
                        '__NAMESPACE__' => 'OaiPmhRepository\Controller',
                        'controller' => Controller\RequestController::class,
                        'action' => 'index',
                        'oai-repository' => 'global',
                    ],
                ],
            ],
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => dirname(__DIR__) . '/language',
                'pattern' => '%s.mo',
                'text_domain' => null,
            ],
        ],
    ],
    'oaipmhrepository' => [
        'metadata_formats' => [
            'factories' => [
                OaiPmh\Metadata\CdwaLite::class => Service\OaiPmh\Metadata\MetadataFormatFactory::class,
                OaiPmh\Metadata\Mets::class => Service\OaiPmh\Metadata\MetadataFormatFactory::class,
                OaiPmh\Metadata\Mods::class => Service\OaiPmh\Metadata\MetadataFormatFactory::class,
                OaiPmh\Metadata\OaiDc::class => Service\OaiPmh\Metadata\MetadataFormatFactory::class,
                OaiPmh\Metadata\OaiDcterms::class => Service\OaiPmh\Metadata\MetadataFormatFactory::class,
                OaiPmh\Metadata\SimpleXml::class => Service\OaiPmh\Metadata\MetadataFormatFactory::class,
            ],
            'aliases' => [
                'cdwalite' => OaiPmh\Metadata\CdwaLite::class,
                'mets' => OaiPmh\Metadata\Mets::class,
                'mods' => OaiPmh\Metadata\Mods::class,
                'oai_dc' => OaiPmh\Metadata\OaiDc::class,
                'oai_dcterms' => OaiPmh\Metadata\OaiDcterms::class,
                'simple_xml' => OaiPmh\Metadata\SimpleXml::class,
            ],
        ],
        'oai_set_formats' => [
            'factories' => [
                'basic' => Service\OaiPmh\OaiSet\BasicFactory::class,
            ],
        ],
        'xml' => [
            'identify' => [
                'description' => [
                    // The toolkit describes the app that manages the repository.
                    // See http://oai.dlib.vt.edu/OAI/metadata/toolkit.xsd.
                    'toolkit' => [
                        'title' => 'Omeka S OAI-PMH Repository Module',
                        'author' => [
                            'name' => 'John Flatness; Julian Maurice; Daniel Berthereau; and other contributors',
                            'email' => 'john@zerocrates.org; julian.maurice@biblibre.com; daniel.git@berthereau.net',
                            'institution' => 'RRCHNM; BibLibre;',
                        ],
                        'version' => null,
                        'toolkitIcon' => 'https://omeka.org/favicon.ico',
                        'URL' => 'https://gitlab.com/Daniel-KM/Omeka-S-module-OaiPmhRepository',
                    ],
                ],
            ],
        ],
    ],
];
