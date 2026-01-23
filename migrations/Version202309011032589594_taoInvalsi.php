<?php

declare(strict_types=1);

namespace oat\taoInvalsi\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\reporting\Report;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use common_ext_Extension as Extension;
use common_ext_ExtensionsManager as ExtensionsManager;

/**
 * phpcs:disable Squiz.Classes.ValidClassName
 */
final class Version202309011032589594_taoInvalsi extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove the textToSpeech configuration (previously TextHelp)';
    }

    public function up(Schema $schema): void
    {
        $config = $this->getConfig();

        // Rimuoviamo il plugin dalla configurazione invece di migrarlo
        if (isset($config['plugins'][self::PLUGIN_ID])) {
            unset($config['plugins'][self::PLUGIN_ID]);
            $this->setConfig($config);
        }

        $this->addReport(
            Report::createSuccess(
                sprintf(
                    'Configuration for %s successfully removed.',
                    self::PLUGIN_ID
                )
            )
        );
    }

    public function down(Schema $schema): void
    {
        // Non facciamo nulla nel revert per evitare di reinserire TextHelp
        $this->addReport(Report::createInfo('No revert action performed for textToSpeech.'));
    }

    private const CONFIG_FILE = 'testRunner';
    private const EXTENSION_ID = 'taoQtiTest';
    private const PLUGIN_ID = 'textToSpeech';
    private $extensionManager;
    private $extension;

    private function getConfig(): array
    {
        return $this->getExtension()->getConfig(self::CONFIG_FILE);
    }

    private function setConfig(array $config)
    {
        $this->getExtension()->setConfig(self::CONFIG_FILE, $config);
    }

    private function getExtension(): Extension
    {
        if ($this->extension) {
            return $this->extension;
        }

        $this->extension = $this->getExtensionManager()->getExtensionById(self::EXTENSION_ID);
        return $this->extension;
    }

    private function getExtensionManager(): ExtensionsManager
    {
        if ($this->extensionManager) {
            return $this->extensionManager;
        }

        $this->extensionManager = $this->getServiceLocator()->get(ExtensionsManager::SERVICE_ID);
        return $this->extensionManager;
    }
}
