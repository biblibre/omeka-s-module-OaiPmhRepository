<?php declare(strict_types=1);
/**
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright John Flatness, Center for History and New Media, 2013-2014
 * @copyright BibLibre, 2016
 * @copyright Daniel Berthereau, 2014-2023
 */
namespace OaiPmhRepository;

use Laminas\EventManager\Event;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Renderer\PhpRenderer;
use OaiPmhRepository\Form\ConfigForm;
use Omeka\Module\AbstractModule;
use Omeka\Stdlib\Message;

/**
 * OaiPmhRepository module class.
 */
class Module extends AbstractModule
{
    const SETTINGS = [
        'oaipmhrepository_name' => '',
        'oaipmhrepository_namespace_id' => '',
        'oaipmhrepository_metadata_formats' => [
            'oai_dc',
            'cdwalite',
            'mets',
            'mods',
            'oai_dcterms',
            'simple_xml',
        ],
        'oaipmhrepository_expose_media' => false,
        'oaipmhrepository_hide_empty_sets' => true,
        'oaipmhrepository_global_repository' => 'item_set',
        'oaipmhrepository_list_item_sets' => [],
        'oaipmhrepository_sets_queries' => [],
        'oaipmhrepository_by_site_repository' => 'disabled',
        'oaipmhrepository_append_identifier_global' => 'absolute_site_url',
        'oaipmhrepository_append_identifier_site' => 'absolute_site_url',
        'oaipmhrepository_oai_set_format' => 'basic',
        'oaipmhrepository_generic_dcterms' => [
            // Of course dcterms is not included.
            'oai_dc',
            'mets',
            'cdwalite',
            'mods',
            'simple_xml',
        ],
        'oaipmhrepository_map_properties' => [
            '# Quick mapping between Bibliographic Ontology (bibo) and Dublin Core terms. See https://www.bibliontology.com/',
            'bibo:abstract' => 'dcterms:abstract',
            'bibo:affirmedBy' => 'dcterms:relation',
            'bibo:annotates' => 'dcterms:description',
            'bibo:asin' => 'dcterms:identifier',
            'bibo:chapter' => 'dcterms:format',
            'bibo:citedBy' => 'dcterms:isReferencedBy',
            'bibo:cites' => 'dcterms:references',
            'bibo:coden' => 'dcterms:identifier',
            'bibo:content' => 'dcterms:description',
            'bibo:court' => 'dcterms:spatial',
            'bibo:argued' => 'dcterms:date',
            'bibo:director' => 'dcterms:contributor',
            'bibo:distributor' => 'dcterms:publisher',
            'bibo:doi' => 'dcterms:identifier',
            'bibo:eanucc13' => 'dcterms:identifier',
            'bibo:edition' => 'dcterms:format',
            'bibo:editor' => 'dcterms:publisher',
            'bibo:eissn' => 'dcterms:identifier',
            'bibo:gtin14' => 'dcterms:identifier',
            'bibo:handle' => 'dcterms:identifier',
            'bibo:identifier' => 'dcterms:identifier',
            'bibo:interviewee' => 'dcterms:contributor',
            'bibo:interviewer' => 'dcterms:contributor',
            'bibo:isbn' => 'dcterms:identifier',
            'bibo:isbn10' => 'dcterms:identifier',
            'bibo:isbn13' => 'dcterms:identifier',
            'bibo:issn' => 'dcterms:identifier',
            'bibo:issue' => 'dcterms:format',
            'bibo:issuer' => 'dcterms:publisher',
            'bibo:lccn' => 'dcterms:identifier',
            'bibo:authorList' => 'dcterms:creator',
            'bibo:contributorList' => 'dcterms:contributor',
            'bibo:editorList' => 'dcterms:publisher',
            'bibo:locator' => 'dcterms:identifier',
            'bibo:number' => 'dcterms:format',
            'bibo:numPages' => 'dcterms:format',
            'bibo:numVolumes' => 'dcterms:format',
            'bibo:oclcnum' => 'dcterms:identifier',
            'bibo:organizer' => 'dcterms:contributor',
            'bibo:owner' => 'dcterms:provenance',
            'bibo:pageEnd' => 'dcterms:format',
            'bibo:pageStart' => 'dcterms:format',
            'bibo:pages' => 'dcterms:format',
            'bibo:performer' => 'dcterms:creator',
            'bibo:pmid' => 'dcterms:identifier',
            'bibo:prefixName' => 'dcterms:description',
            'bibo:presentedAt' => 'dcterms:relation',
            'bibo:presents' => 'dcterms:relation',
            'bibo:producer' => 'dcterms:contributor',
            'bibo:recipient' => 'dcterms:contributor',
            'bibo:reproducedIn' => 'dcterms:relation',
            'bibo:reversedBy' => 'dcterms:relation',
            'bibo:reviewOf' => 'dcterms:relation',
            'bibo:section' => 'dcterms:format',
            'bibo:shortTitle' => 'dcterms:alternative',
            'bibo:shortDescription' => 'dcterms:abstract',
            'bibo:sici' => 'dcterms:identifier',
            'bibo:degree' => 'dcterms:description',
            'bibo:status' => 'dcterms:description',
            'bibo:subsequentLegalDecision' => 'dcterms:relation',
            'bibo:suffixName' => 'dcterms:description',
            'bibo:transcriptOf' => 'dcterms:isVersionOf',
            'bibo:translationOf' => 'dcterms:isVersionOf',
            'bibo:translator' => 'dcterms:contributor',
            'bibo:upc' => 'dcterms:identifier',
            'bibo:uri' => 'dcterms:identifier',
            'bibo:volume' => 'dcterms:format',

            // Alert: Warning, the version on http://xmlns.com/foaf/0.1/ is outdated (2004)! Use the 2014 one on archive.org.
            '# Quick mapping between Friend of a Friend (foaf) and Dublin Core terms. See https://web.archive.org/web/20220620163542/http://xmlns.com/foaf/spec/20140114.html',
            'foaf:account' => 'dcterms:identifier', // Online Accounts / IM // Social Web
            'foaf:accountName' => 'dcterms:identifier', // Online Accounts / IM // Social Web
            'foaf:accountServiceHomepage' => 'dcterms:references', // Online Accounts / IM // Social Web
            'foaf:age' => 'dcterms:extent', // Personal Info // Core / Agent
            'foaf:aimChatID' => 'dcterms:identifier', // Online Accounts / IM
            'foaf:based_near' => 'dcterms:spatial', // Personal Info // Core / Agent
            'foaf:birthday' => 'dcterms:issued', // Personal Info
            'foaf:currentProject' => 'dcterms:requires', // Personal Info // Social Web
            'foaf:depiction' => 'dcterms:description', // Basics // Core / Agent
            'foaf:depicts' => 'dcterms:description', // Basics // Core / Agent
            'foaf:dnaChecksum' => 'dcterms:identifier', // Personal Info // archaic
            'foaf:family_name' => 'dcterms:title', // Basics // archaic
            'foaf:familyName' => 'dcterms:title', // Basics // Core / Agent
            'foaf:firstName' => 'dcterms:title', // Basics
            'foaf:focus' => 'dcterms:subject', // Personal Info // Linked Data utiliies
            'foaf:fundedBy' => 'dcterms:isReferencedBy', // Projects and Groups // archaic
            'foaf:geekcode' => 'dcterms:abstract', // Personal Info // archaic
            'foaf:gender' => 'dcterms:format', // Personal Info
            'foaf:givenName' => 'dcterms:title', // Basics // Core / Agent
            'foaf:givenname' => 'dcterms:title', // Basics // archaic
            'foaf:holdsAccount' => 'dcterms:identifier', // Online Accounts / IM // archaic
            'foaf:homepage' => 'dcterms:references', // Basics // Social Web
            'foaf:icqChatID' => 'dcterms:identifier', // Online Accounts / IM
            'foaf:img' => 'dcterms:hasFormat', // Basics // Core / Agent
            'foaf:interest' => 'dcterms:subject', // Personal Info // Social Web
            'foaf:isPrimaryTopicOf' => 'dcterms:isReferencedBy', // Documents and Images // Core / Agent
            'foaf:jabberID' => 'dcterms:identifier', // Online Accounts / IM // Social Web
            'foaf:knows' => 'dcterms:relation', // Personal Info // Core / Agent
            'foaf:lastName' => 'dcterms:title', // Basics
            'foaf:logo' => 'dcterms:hasFormat', // Documents and Images // Social Web
            'foaf:made' => 'dcterms:isReferencedBy', // Documents and Images // Core / Agent
            'foaf:maker' => 'dcterms:creator', // Documents and Images // Core / Agent
            'foaf:mbox' => 'dcterms:identifier', // Basics // Social Web
            'foaf:mbox_sha1sum' => 'dcterms:identifier', // Basics // Social Web
            'foaf:member' => 'dcterms:isPartOf', // Projects and Groups // Core / Project
            'foaf:membershipClass' => 'dcterms:isPartOf', // Projects and Groups
            'foaf:msnChatID' => 'dcterms:identifier', // Online Accounts / IM
            'foaf:myersBriggs' => 'dcterms:abstract', // Personal Info
            'foaf:name' => 'dcterms:title', // Basics // Core / Agent
            'foaf:nick' => 'dcterms:alternative', // Basics // Social Web
            'foaf:openid' => 'dcterms:identifier', // Online Accounts / IM // Social Web
            'foaf:page' => 'dcterms:references', // Documents and Images // Social Web
            'foaf:pastProject' => 'dcterms:requires', // Personal Info // Social Web
            'foaf:phone' => 'dcterms:identifier', // Basics
            'foaf:plan' => 'dcterms:abstract', // Personal Info
            'foaf:primaryTopic' => 'dcterms:subject', // Documents and Images // Core / Agent
            'foaf:publications' => 'dcterms:references', // Personal Info // Social Web
            'foaf:schoolHomepage' => 'dcterms:references', // Personal Info // Social Web
            'foaf:sha1' => 'dcterms:identifier', // Documents and Images // Social Web
            'foaf:skypeID' => 'dcterms:identifier', // Online Accounts / IM
            'foaf:status' => 'dcterms:medium', // Personal Info
            'foaf:surname' => 'dcterms:alternative', // Basics // archaic
            'foaf:theme' => 'dcterms:subject', // Projects and Groups // archaic
            'foaf:thumbnail' => 'dcterms:hasFormat', // Documents and Images // Social Web
            'foaf:tipjar' => 'dcterms:identifier', // Documents and Images // Social Web
            'foaf:title' => 'dcterms:title', // Basics // Core / Agent
            'foaf:topic' => 'dcterms:subject', // Documents and Images // Social Web
            'foaf:topic_interest' => 'dcterms:subject', // Personal Info // Social Web
            'foaf:weblog' => 'dcterms:references', // Personal Info // Social Web
            'foaf:workInfoHomepage' => 'dcterms:references', // Personal Info // Social Web
            'foaf:workplaceHomepage' => 'dcterms:references', // Personal Info // Social Web
            'foaf:yahooChatID' => 'dcterms:identifier', // Online Accounts / IM
        ],
        'oaipmhrepository_format_literal_striptags' => [
            'oai_dc',
            'oai_dcterms',
            'mets',
            'cdwalite',
            'mods',
            // 'simple_xml',
        ],
        'oaipmhrepository_format_resource' => 'url_attr_title',
        'oaipmhrepository_format_resource_property' => 'dcterms:identifier',
        'oaipmhrepository_format_uri' => 'uri_attr_label',
        'oaipmhrepository_oai_dc_class_type' => 'term',
        'oaipmhrepository_oai_dcterms_class_type' => 'term',
        'oaipmhrepository_oai_table_class_type' => null,
        'oaipmhrepository_oai_dc_bnf_vignette' => 'none',
        'oaipmhrepository_oai_dcterms_bnf_vignette' => 'none',
        'oaipmhrepository_mets_data_item' => 'dcterms',
        'oaipmhrepository_mets_data_media' => 'dcterms',
        'oaipmhrepository_human_interface' => true,
        'oaipmhrepository_redirect_route' => '',
        'oaipmhrepository_list_limit' => 50,
        'oaipmhrepository_token_expiration_time' => 10,
    ];

    protected $propertiesMap;

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap(MvcEvent $event)
    {
        parent::onBootstrap($event);
        $this->addAclRules();
        $this->addRoutes();
    }

    public function install(ServiceLocatorInterface $services)
    {
        $connection = $services->get('Omeka\Connection');
        $settings = $services->get('Omeka\Settings');

        $connection->executeStatement(<<<'SQL'
            CREATE TABLE `oaipmhrepository_token` (
                `id` INT AUTO_INCREMENT NOT NULL COMMENT 'primary key (also the value of the token)',
                `verb` VARCHAR(15) NOT NULL COMMENT 'Verb of original request',
                `metadata_prefix` VARCHAR(190) NOT NULL COMMENT 'metadataPrefix of original request',
                `cursor` INT NOT NULL COMMENT 'Position of cursor within result set',
                `from` DATETIME DEFAULT NULL COMMENT 'Optional from argument of original request',
                `until` DATETIME DEFAULT NULL COMMENT 'Optional until argument of original request',
                `set` VARCHAR(190) DEFAULT NULL COMMENT 'Optional set argument of original request',
                `expiration` DATETIME NOT NULL COMMENT 'Datestamp after which token is expired',
                INDEX IDX_F99CFEE424CD504D (`expiration`),
                PRIMARY KEY(`id`)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
        SQL);

        foreach (self::SETTINGS as $id => $default) {
            $settings->set($id, $default);
        }
        $settings->set('oaipmhrepository_name', $settings->get('installation_title'));
        $settings->set('oaipmhrepository_namespace_id', $this->getServerNameWithoutProtocol($services));
    }

    public function uninstall(ServiceLocatorInterface $services)
    {
        $connection = $services->get('Omeka\Connection');
        $settings = $services->get('Omeka\Settings');

        $connection->executeStatement('DROP TABLE IF EXISTS oaipmhrepository_token');

        foreach (self::SETTINGS as $id => $default) {
            $settings->delete($id, $default);
        }
    }

    public function upgrade($oldVersion, $newVersion, ServiceLocatorInterface $services)
    {
        $plugins = $services->get('ControllerPluginManager');
        $settings = $services->get('Omeka\Settings');
        $connection = $services->get('Omeka\Connection');
        $messenger = $plugins->get('messenger');

        $defaultSettings = self::SETTINGS;

        if (version_compare($oldVersion, '0.3', '<')) {
            $connection = $services->get('Omeka\Connection');
            $sql = <<<'SQL'
ALTER TABLE oai_pmh_repository_token CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE verb verb VARCHAR(190) NOT NULL, CHANGE metadata_prefix metadata_prefix VARCHAR(190) NOT NULL, CHANGE `cursor` `cursor` INT NOT NULL, CHANGE `set` `set` INT DEFAULT NULL;
DROP INDEX expiration ON oai_pmh_repository_token;
CREATE INDEX IDX_E9AC4F9524CD504D ON oai_pmh_repository_token (expiration);
SQL;
            $connection->executeStatement($sql);

            $settings->set('oaipmhrepository_name', $settings->get('oaipmh_repository_name',
                $settings->get('installation_title')));
            $settings->set('oaipmhrepository_namespace_id', $settings->get('oaipmhrepository_namespace_id',
                $this->getServerNameWithoutProtocol($services)));
            $settings->set('oaipmhrepository_expose_media', $settings->get('oaipmh_repository_namespace_expose_files',
                $defaultSettings['oaipmhrepository_expose_media']));
            $settings->set('oaipmhrepository_list_limit',
                $defaultSettings['oaipmhrepository_list_limit']);
            $settings->set('oaipmhrepository_token_expiration_time',
                $defaultSettings['oaipmhrepository_token_expiration_time']);

            $settings->delete('oaipmh_repository_name');
            $settings->delete('oaipmh_repository_namespace_id');
            $settings->delete('oaipmh_repository_namespace_expose_files');
            $settings->delete('oaipmh_repository_record_limit');
            $settings->delete('oaipmh_repository_list_limit');
            $settings->delete('oaipmh_repository_expiration_time');
            $settings->delete('oaipmh_repository_token_expiration_time');
        }

        if (version_compare($oldVersion, '0.3.1', '<')) {
            $settings->set('oaipmhrepository_global_repository',
                $defaultSettings['oaipmhrepository_global_repository']);
            $settings->set('oaipmhrepository_by_site_repository', 'item_set');
            $settings->set('oaipmhrepository_oai_set_format',
                $defaultSettings['oaipmhrepository_oai_set_format']);
            $settings->set('oaipmhrepository_human_interface',
                $defaultSettings['oaipmhrepository_human_interface']);
            $settings->set('oaipmhrepository_hide_empty_sets',
                $defaultSettings['oaipmhrepository_hide_empty_sets']);
        }

        if (version_compare($oldVersion, '3.2.2', '<')) {
            $connection = $services->get('Omeka\Connection');
            $sql = <<<'SQL'
ALTER TABLE oai_pmh_repository_token CHANGE `set` `set` VARCHAR(190) DEFAULT NULL;
SQL;
            $connection->executeStatement($sql);

            $settings->set('oaipmhrepository_append_identifier_global',
                $defaultSettings['oaipmhrepository_append_identifier_global']);
            $settings->set('oaipmhrepository_append_identifier_site',
                $defaultSettings['oaipmhrepository_append_identifier_site']);
        }

        if (version_compare($oldVersion, '3.3.0', '<')) {
            $settings->set('oaipmhrepository_metadata_formats',
                $defaultSettings['oaipmhrepository_metadata_formats']);
            $settings->set('oaipmhrepository_generic_dcterms',
                $defaultSettings['oaipmhrepository_generic_dcterms']);
            $settings->set('oaipmhrepository_mets_data_item',
                $defaultSettings['oaipmhrepository_mets_data_item']);
            $settings->set('oaipmhrepository_mets_data_media',
                $defaultSettings['oaipmhrepository_mets_data_media']);
        }

        if (version_compare($oldVersion, '3.3.5.2', '<')) {
            $messenger->addWarning('The event "oaipmhrepository.values" that may be used by other modules was deprecated and replaced by event "oaipmhrepository.values.pre".'); // @translate
            $messenger->addWarning('Futhermore, a new option allows to map any term to any other term, so any values can be exposed if needed.'); // @translate

            $settings->set(
                'oaipmhrepository_generic_dcterms',
                $settings->get('oaipmhrepository_generic_dcterms', true) ? ['oai_dc', 'cdwalite', 'mets', 'mods'] : []
            );
            $settings->set('oaipmhrepository_map_properties', $defaultSettings['oaipmhrepository_map_properties']);
        }

        if (version_compare($oldVersion, '3.3.5.6', '<')) {
            $messenger->addWarning('It is now possible to define oai sets with a specific list of item sets or with a list of search queries.'); // @translate
        }

        if (version_compare($oldVersion, '3.3.6', '<')) {
            $messenger->addSuccess('A simple mapping of foaf properties to Dublin Core has been added to the default config. It allows to publish, for example, common metadata of people.'); // @translate

            // Update the mapping if this is the original one.
            $mapProperties = $settings->get('oaipmhrepository_map_properties');
            $mapPropertiesOriginal = $defaultSettings['oaipmhrepository_map_properties'];
            if ($mapProperties
                && count($mapPropertiesOriginal) === 68
                && array_slice($mapProperties, 1, 67, true) === array_slice($mapPropertiesOriginal, 1, 67, true)
            ) {
                $settings->set('oaipmhrepository_map_properties', $mapPropertiesOriginal);
            } else {
                $message = new Message(
                    'You can copy the %sdefault mapping foaf to dcterms%s in the config of the module if needed.', // @translate
                    '<a href="https://gitlab.com/Daniel-KM/Omeka-S-module-OaiPmhRepository/-/blob/master/config/module.config.php#L130" target="_blank" rel="noopener">',
                    '</a>',
                );
                $message->setEscapeHtml(false);
                $messenger->addWarning($message);
            }

            $message = new Message(
                'An option was added to append a thumbnail url according to the non-standard %1$srecommandation%2$s of the Bibliothèque nationale de France.', // @translate
                '<a href="https://www.bnf.fr/sites/default/files/2019-02/Guide_oaipmh.pdf" target="_blank" rel="noopener">',
                '</a>',
            );
            $message->setEscapeHtml(false);
            $messenger->addSuccess($message);

            $messenger->addWarning('The deprecated event "oaipmhrepository.values" was removed. Use "oaipmhrepository.values.pre" instead.'); // @translate

            $metadataFormats = $settings->get('oaipmhrepository_metadata_formats', []);
            $metadataFormats[] = 'simple_xml';
            $settings->set('oaipmhrepository_metadata_formats', $metadataFormats);

            $urlHelper = $services->get('ViewHelperManager')->get('url');
            $message = new Message(
                'A new output metadata format was added, "simple_xml", that contains all the values in a simple xml, not only the dublin core ones. You can disabled it in the %sconfig of the module%s.', // @translate
                '<a href="' . $urlHelper('admin/default', ['controller' => 'module', 'action' => 'configure'], ['query' => ['id' => 'OaiPmhRepository']]) . '">',
                '</a>',
            );
            $message->setEscapeHtml(false);
            $messenger->addSuccess($message);
        }

        if (version_compare($oldVersion, '3.4.7', '<')) {
            $sql = <<<'SQL'
ALTER TABLE `oai_pmh_repository_token`
    DROP INDEX IDX_E9AC4F9524CD504D;
SQL;
            try {
                $connection->executeStatement($sql);
            } catch (\Exception $e) {
                // Nothing.
            }
            $sql = <<<'SQL'
ALTER TABLE `oai_pmh_repository_token`
    ADD INDEX IDX_F99CFEE424CD504D (`expiration`),
    CHANGE `verb` `verb` varchar(15) NOT NULL AFTER `id`,
    RENAME TO `oaipmhrepository_token`;
SQL;
            try {
                $connection->executeStatement($sql);
            } catch (\Exception $e) {
                // Nothing.
            }

            $messenger->addSuccess('Some new options were added for compliance with non-standard requirements of BnF (Bibliothèque nationale de France): thumbnail, uri without attribute, class as main type.'); // @translate
        }

        if (version_compare($oldVersion, '3.4.8', '<')) {
            // In some cases, the table was not removed or updated in 3.4.7.
            $sql = <<<'SQL'
ALTER TABLE `oaipmhrepository_token`
    DROP INDEX IDX_E9AC4F9524CD504D;
SQL;
            try {
                $connection->executeStatement($sql);
            } catch (\Exception $e) {
                // Nothing.
            }
            $sql = <<<'SQL'
ALTER TABLE `oaipmhrepository_token`
    ADD INDEX IDX_F99CFEE424CD504D (`expiration`),
    CHANGE `verb` `verb` varchar(15) NOT NULL AFTER `id`;
SQL;
            try {
                $connection->executeStatement($sql);
            } catch (\Exception $e) {
                // Nothing.
            }
            $sql = <<<'SQL'
DROP TABLE `oai_pmh_repository_token`;
SQL;
            try {
                $connection->executeStatement($sql);
            } catch (\Exception $e) {
                // Nothing.
            }
        }

        if (version_compare($oldVersion, '3.4.9', '<')) {
            $settings->set('oaipmhrepository_format_literal_striptags', $defaultSettings['oaipmhrepository_format_literal_striptags']);
            $messenger->addSuccess('A new option allows to strip xml/html tags of data. It is enable by default for all formats except simple_xml.'); // @translate
        }
    }

    public function getConfigForm(PhpRenderer $renderer)
    {
        $services = $this->getServiceLocator();
        $forms = $services->get('FormElementManager');
        $settings = $services->get('Omeka\Settings');

        $form = $forms->get(ConfigForm::class);

        $data = [];
        foreach (self::SETTINGS as $id => $default) {
            $data[$id] = $settings->get($id, $default);
        }

        $form->setData($data);
        $form->prepare();

        return $renderer->formCollection($form);
    }

    public function handleConfigForm(AbstractController $controller)
    {
        $services = $this->getServiceLocator();
        $forms = $services->get('FormElementManager');
        $settings = $services->get('Omeka\Settings');

        $form = $forms->get(ConfigForm::class);

        $form->setData($controller->getRequest()->getPost());
        if (!$form->isValid()) {
            $controller->messenger()->addFormErrors($form);
            return false;
        }

        $data = $form->getData();
        if (empty($data['oaipmhrepository_metadata_formats'])) {
            $data['oaipmhrepository_metadata_formats'] = ['oai_dc'];
        }

        foreach (array_keys(self::SETTINGS) as $id) {
            if (array_key_exists($id, $data)) {
                $settings->set($id, $data[$id]);
            }
        }

        return true;
    }

    /**
     * Add ACL rules for this module.
     */
    protected function addAclRules(): void
    {
        /** @var \Omeka\Permissions\Acl $acl */
        $acl = $this->getServiceLocator()->get('Omeka\Acl');
        $acl
            ->allow(
                null,
                [
                    \OaiPmhRepository\Entity\OaiPmhRepositoryToken::class,
                    \OaiPmhRepository\Api\Adapter\OaiPmhRepositoryTokenAdapter::class,
                    \OaiPmhRepository\Controller\RequestController::class,
                ]
            );
    }

    protected function addRoutes(): void
    {
        $serviceLocator = $this->getServiceLocator();

        $settings = $serviceLocator->get('Omeka\Settings');
        $redirect = $settings->get('oaipmhrepository_redirect_route');
        if (empty($redirect)) {
            return;
        }

        $router = $serviceLocator->get('Router');
        if (!$router instanceof \Laminas\Router\Http\TreeRouteStack) {
            return;
        }

        $router->addRoute('oai-pmh-redirect', [
            'type' => \Laminas\Router\Http\Literal::class,
            'options' => [
                'route' => $redirect,
                'defaults' => [
                    '__NAMESPACE__' => 'OaiPmhRepository\Controller',
                    'controller' => Controller\RequestController::class,
                    'action' => 'redirect',
                    'oai-repository' => 'global',
                ],
            ],
        ]);
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager): void
    {
        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Index',
            'view.browse.after',
            [$this, 'filterAdminDashboardPanels']
        );

        // The AbstractMetadata class is used in order to manage all formats,
        // even if OaiDcTerms doesn't require it.
        $sharedEventManager->attach(
            \OaiPmhRepository\OaiPmh\Metadata\AbstractMetadata::class,
            'oaipmhrepository.values.pre',
            [$this, 'filterOaiPmhRepositoryValuesPre'],
            // Process internal filter first.
            100
        );
    }

    public function filterAdminDashboardPanels(Event $event): void
    {
        $services = $this->getServiceLocator();
        $settings = $services->get('Omeka\Settings');
        $api = $services->get('Omeka\ApiManager');

        $sites = $api->search('sites')->getContent();

        if (empty($sites)) {
            return;
        }
        $view = $event->getTarget();
        echo $view->partial('common/admin/oai-pmh-repository-dashboard', [
            'sites' => $sites,
            'globalRepository' => $settings->get('oaipmhrepository_global_repository'),
            'bySiteRepository' => $settings->get('oaipmhrepository_by_site_repository'),
        ]);
    }

    public function filterOaiPmhRepositoryValuesPre(Event $event): void
    {
        $this->mapDublinCoreTerms($event);
        $this->mapOtherProperties($event);
    }

    protected function mapDublinCoreTerms(Event $event): void
    {
        static $genericDcterms;
        static $map;

        if (is_null($genericDcterms)) {
            $services = $this->getServiceLocator();
            $settings = $services->get('Omeka\Settings');
            $genericDcterms = array_diff(
                $settings->get('oaipmhrepository_generic_dcterms', ['oai_dc', 'cdwalite', 'mets', 'mods']),
                ['oai_dcterms', 'simple_xml']
            );
            $map = include __DIR__ . '/data/mappings/dc_generic.php';
        }
        if (!count($genericDcterms) || !count($map)) {
            return;
        }

        $prefix = $event->getParam('prefix');
        if (!in_array($prefix, $genericDcterms)) {
            return;
        }

        $resource = $event->getParam('resource');

        // Check if the filter is enable for the current format.
        if ($prefix === 'mets') {
            $services = $this->getServiceLocator();
            $settings = $services->get('Omeka\Settings');
            switch (get_class($resource)) {
                case \Omeka\Api\Representation\MediaRepresentation::class:
                    $dataFormat = $settings->get('oaipmhrepository_mets_data_media');
                    break;
                case \Omeka\Api\Representation\ItemRepresentation::class:
                default:
                    $dataFormat = $settings->get('oaipmhrepository_mets_data_item');
                    break;
            }
            if ($dataFormat === 'dcterms') {
                return;
            }
        }

        $values = $event->getParam('values');

        foreach ($map as $destinationTerm => $dcterms) {
            foreach ($dcterms as $sourceTerm) {
                if (empty($values[$sourceTerm]['values'])) {
                    continue;
                }
                if (empty($values[$destinationTerm]['values'])) {
                    $values[$destinationTerm]['values'] = array_values($values[$sourceTerm]['values']);
                } else {
                    $values[$destinationTerm]['values'] = array_merge(
                    array_values($values[$destinationTerm]['values']),
                    array_values($values[$sourceTerm]['values'])
                );
                }
            }
        }

        $event->setParam('values', $values);
    }

    protected function mapOtherProperties(Event $event): void
    {
        static $mapping;

        if (is_null($mapping)) {
            /**
             * @var \Laminas\ServiceManager\ServiceLocatorInterface $services
             * @var \Omeka\Api\Manager $api
             * @var \Omeka\Settings\Settings $settings
             */
            $services = $this->getServiceLocator();
            $settings = $services->get('Omeka\Settings');
            $mapping = $settings->get('oaipmhrepository_map_properties', []);
            foreach ($mapping as $sourceTerm => $destinationTerm) {
                if ($sourceTerm === $destinationTerm
                    || empty($sourceTerm)
                    || empty($destinationTerm)
                    || is_numeric($sourceTerm)
                    || is_numeric($destinationTerm)
                    || mb_substr($sourceTerm, 0, 1) === '#'
                    || mb_substr($destinationTerm, 0, 1) === '#'
                    || !strpos($sourceTerm, ':')
                    || !strpos($destinationTerm, ':')
                ) {
                    unset($mapping[$sourceTerm]);
                    continue;
                }
                $propertyId = $this->getPropertyId($sourceTerm);
                if (!$propertyId) {
                    unset($mapping[$sourceTerm]);
                    continue;
                }
                $propertyId = $this->getPropertyId($destinationTerm);
                if (!$propertyId) {
                    unset($mapping[$sourceTerm]);
                    continue;
                }
            }
        }
        if (!count($mapping)) {
            return;
        }

        $services = $this->getServiceLocator();
        $api = $services->get('Omeka\ApiManager');

        /** @var \Omeka\Api\Representation\AbstractResourceEntityRepresentation $resource */
        $resource = $event->getParam('resource');
        $template = $resource->resourceTemplate();

        $values = $event->getParam('values', []);

        // Do a double loop for quicker process. Mapping is already checked.

        foreach ($mapping as $sourceTerm => $destinationTerm) {
            if (isset($values[$destinationTerm]['values'])) {
                continue;
            }
            $propertyId = $this->getPropertyId($destinationTerm);
            if (!$propertyId) {
                continue;
            }
            $rtp = $template ? $template->resourceTemplateProperty($propertyId) : null;
            if ($rtp) {
                $alternateLabel = $rtp->alternateLabel();
                $alternateComment = $rtp->alternateComment();
            } else {
                $alternateLabel = null;
                $alternateComment = null;
            }
            $values[$destinationTerm] = [
                // The api manages the cache automatically via doctrine.
                'property' => $api->read('properties', $propertyId)->getContent(),
                'alternate_label' => $alternateLabel,
                'alternate_comment' => $alternateComment,
                'values' => [],
            ];
        }

        foreach ($mapping as $sourceTerm => $destinationTerm) {
            if (empty($values[$sourceTerm]['values'])) {
                continue;
            }
            if (empty($values[$destinationTerm]['values'])) {
                $values[$destinationTerm]['values'] = array_values($values[$sourceTerm]['values']);
            } else {
                $values[$destinationTerm]['values'] = array_merge(
                    array_values($values[$destinationTerm]['values']),
                    array_values($values[$sourceTerm]['values'])
                );
            }
        }

        $event->setParam('values', $values);
    }

    protected function getServerNameWithoutProtocol($serviceLocator)
    {
        $viewHelpers = $serviceLocator->get('ViewHelperManager');
        $serverUrlHelper = $viewHelpers->get('serverUrl');

        $host = $serverUrlHelper->getHost();
        if ($host) {
            $serverName = preg_replace('~(?:\w+://)?([^:]+)(?::\d*)?$~', '$1', $host);
            $name = preg_replace('/[^a-z0-9\-\.]/i', '', $serverName);
        }

        if (empty($name) || $name === 'localhost') {
            $name = 'default.must.change';
        }

        return $name;
    }

    protected function getPropertyId(string $term)
    {
        if (!isset($this->propertiesMap)) {
            $connection = $this->getServiceLocator()->get('Omeka\Connection');
            $this->propertiesMap = $connection->fetchAllKeyValue(<<<'SQL'
                SELECT CONCAT(vocabulary.prefix, ':', property.local_name), property.id
                FROM property JOIN vocabulary ON (property.vocabulary_id = vocabulary.id)
            SQL);
        }

        return $this->propertiesMap[$term] ?? null;
    }
}
