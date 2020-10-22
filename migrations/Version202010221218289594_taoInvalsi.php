<?php

declare(strict_types=1);

namespace oat\taoInvalsi\migrations;

use common_Exception;
use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoInvalsi\model\export\PrefixParser;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202010221218289594_taoInvalsi extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Registers new PDF for MATH test of Autumn\'20 campaign';
    }

    /**
     * @param Schema $schema
     *
     * @throws common_Exception
     */
    public function up(Schema $schema): void
    {
        $qtiTestService = $this->getServiceLocator()->get('taoQtiTest/testRunner');
        $config = $qtiTestService ->getConfig();
    
        //removing as we don't need underscores in the testRunner.conf, even if the category in test XML uses them
        if (array_key_exists('documentUrlA09', $config['plugins']['formula'])) {
            unset($config['plugins']['formula']['documentUrlA09']);
        }
        
        $config['plugins']['formula']['documentUrlA09'] = '/taoInvalsi/views/js/runner/plugins/tools/formula/formulaA09.pdf';
    
        $qtiTestService->setOption('config', $config);
        $this->getServiceLocator()->register('taoQtiTest/testRunner', $qtiTestService);
    }

    public function down(Schema $schema): void
    {
        $qtiTestService = $this->getServiceLocator()->get('taoQtiTest/testRunner');
        $config = $qtiTestService ->getConfig();
    
        //removing as we don't need underscores in the testRunner.conf, even if the category in test XML uses them
        if (array_key_exists('documentUrlA09', $config['plugins']['formula'])) {
            unset($config['plugins']['formula']['documentUrlA09']);
        }
    
        $qtiTestService->setOption('config', $config);
        $this->getServiceLocator()->register('taoQtiTest/testRunner', $qtiTestService);
    }
}
