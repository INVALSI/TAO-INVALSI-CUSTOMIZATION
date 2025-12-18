<?php

declare(strict_types=1);

namespace oat\taoInvalsi\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\cache\SimpleCache;
use oat\oatbox\reporting\Report;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\tao\scripts\update\OntologyUpdater;
use common_ext_Extension as Extension;
use common_ext_ExtensionsManager as ExtensionsManager;

final class Version202407020907089594_taoInvalsi extends AbstractMigration
{
    private const CLIENT_LIB_REGISTRY = 'client_lib_config_registry';
    private const ITEM_PREVIEWERS = [
        ['id' => 'qtiItemNUI', 'label' => 'Preview'],
        ['id' => 'qtiItem', 'label' => 'Preview (legacy)'],
    ];
    private const TEST_PREVIEWERS = [
        ['id' => 'qtiTestNUI', 'label' => 'Preview'],
        ['id' => 'qtiTest', 'label' => 'Preview (legacy)'],
    ];

    private ?ExtensionsManager $extensionManager;
    private ?Extension $extensionTao;

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->purgeCache();
        OntologyUpdater::syncModels();

        $config = $this->getClientLibRegistry();

        $config = $this->addProviders($config, 'taoItems/controller/items/action', self::ITEM_PREVIEWERS);
        $config = $this->addProviders($config, 'taoQtiItem/qtiCreator/plugins/menu/preview', self::ITEM_PREVIEWERS);

        $config = $this->addProviders($config, 'taoTests/controller/tests/action', self::TEST_PREVIEWERS);
        $config = $this->addProviders($config, 'taoQtiTest/controller/creator/creator', self::TEST_PREVIEWERS);

        $this->setClientLibRegistry($config);

        $this->addReport(
            Report::createSuccess('Configuration for multiple preview buttons successfully migrated.')
        );
    }

    public function down(Schema $schema): void
    {
        $this->purgeCache();
        OntologyUpdater::syncModels();

        $config = $this->getClientLibRegistry();

        $config = $this->removeProviders($config, 'taoItems/controller/items/action');
        $config = $this->removeProviders($config, 'taoQtiItem/qtiCreator/plugins/menu/preview');

        $config = $this->removeProviders($config, 'taoTests/controller/tests/action');
        $config = $this->removeProviders($config, 'taoQtiTest/controller/creator/creator');

        $this->setClientLibRegistry($config);

        $this->addReport(
            Report::createSuccess('Configuration for multiple preview buttons successfully rolled back.')
        );
    }

    private function addProviders(array $config, string $key, array $providers): array
    {
        $config[$key]['providers'] = $providers;
        return $config;
    }

    private function removeProviders(array $config, string $key): array
    {
        unset($config[$key]['providers']);
        return $config;
    }

    private function getClientLibRegistry(): array
    {
        $extension = $this->getTaoExtension();

        return $extension->getConfig(self::CLIENT_LIB_REGISTRY);
    }

    private function setClientLibRegistry(array $config)
    {
        $extension = $this->getTaoExtension();

        $extension->setConfig(self::CLIENT_LIB_REGISTRY, $config);
    }

    private function getTaoExtension(): Extension
    {
        if (isset($this->extensionTao)) {
            return $this->extensionTao;
        }
        $this->extensionTao = $this->getExtension('tao');
        return $this->extensionTao;
    }

    private function getExtension(string $extensionId): Extension
    {
        return $this->getExtensionManager()->getExtensionById($extensionId);
    }

    private function getExtensionManager(): ExtensionsManager
    {
        if (isset($this->extensionManager)) {
            return $this->extensionManager;
        }

        $this->extensionManager = $this->getServiceLocator()->get(ExtensionsManager::SERVICE_ID);
        return $this->extensionManager;
    }

    private function purgeCache()
    {
        $this->getServiceLocator()->get(SimpleCache::SERVICE_ID)->clear();
    }
}
