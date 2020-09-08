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
final class Version202009080848039594_taoInvalsi extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Registers PrefixParser service with detection rules';
    }

    /**
     * @param Schema $schema
     *
     * @throws common_Exception
     */
    public function up(Schema $schema): void
    {
        $this->getServiceLocator()->register(
            PrefixParser::SERVICE_ID,
            new PrefixParser(
                [
                    PrefixParser::OPTION_DETECTION_REGEXP => '/.*(\d\d)_(\w\w)_(\w\w\w)(\d\d).*/',
                    PrefixParser::OPTION_GRADE_RULE => 'G%4$s',
                    PrefixParser::OPTION_SUBJECT_RULE => '%3$s',
                    PrefixParser::OPTION_CAMPAIGN_RULE => '%2$s',
                    PrefixParser::OPTION_ACTIVITY_RULE => '%1$s',
                ]
            )
        );
    }

    public function down(Schema $schema): void
    {
        $this->getServiceLocator()->unregister(PrefixParser::SERVICE_ID);
    }
}
