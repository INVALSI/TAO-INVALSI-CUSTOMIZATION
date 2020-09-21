<?php

declare(strict_types=1);

namespace oat\taoInvalsi\migrations;

use common_exception_Error;
use common_ext_Extension;
use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202009210924329594_taoInvalsi extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Updated Message Poll Configuration.';
    }

    /**
     * @param Schema $schema
     *
     * @throws common_exception_Error
     */
    public function up(Schema $schema): void
    {
        /** @var common_ext_Extension $qtiTest */
        $qtiTest = $this->getServiceLocator()->get(\common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('taoQtiTest');
        $config = $qtiTest->getConfig('testRunner');

        //enable the communicator, but does not define grouped actions
        $config['bootstrap']['communication'] = [
            'enabled'     => true,
            'type'        => 'poll',
            'extension'   => 'taoQtiTest',
            'controller'  => 'Runner',
            'action'      => 'messages',
            'syncActions' => [],
            'params'      => [
                'interval' => 30,
                'timeout' => 30,
            ]
        ];

        $qtiTest->setConfig('testRunner', $config);
    }

    /**
     * @param Schema $schema
     *
     * @throws common_exception_Error
     */
    public function down(Schema $schema): void
    {
        /** @var common_ext_Extension $qtiTest */
        $qtiTest = $this->getServiceLocator()->get(\common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('taoQtiTest');
        $config = $qtiTest->getConfig('testRunner');

        //enable the communicator, but does not define grouped actions
        $config['bootstrap']['communication'] = [
            'enabled'     => true,
            'type'        => 'request',
            'extension'   => 'taoQtiTest',
            'controller'  => 'Runner',
            'action'      => 'messages',
            'syncActions' => [],
            'params'      => [
            ]
        ];

        $qtiTest->setConfig('testRunner', $config);
    }
}
