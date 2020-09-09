<?php

declare(strict_types=1);

namespace oat\taoInvalsi\migrations;

use common_Exception;
use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoInvalsi\model\export\DateRangeLookupStrategy;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202009080852249594_taoInvalsi extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Registers CampaignDateRangeLookupStrategy service';
    }

    /**
     * @param Schema $schema
     *
     * @throws common_Exception
     */
    public function up(Schema $schema): void
    {
        $this->getServiceLocator()->register(
            CampaignDateRangeLookupStrategy::SERVICE_ID,
            new CampaignDateRangeLookupStrategy(
                [
                    CampaignDateRangeLookupStrategy::OPTION_CAMPAIGNS => [
                        'TF' => CampaignDateRangeLookupStrategy::STRATEGY_BEFORE_END_OF_DAY,
                        'MS' => CampaignDateRangeLookupStrategy::STRATEGY_SAME_DAY,
                    ],
                ]
            )
        );
    }

    public function down(Schema $schema): void
    {
        $this->getServiceLocator()->unregister(DateRangeLookupStrategy::SERVICE_ID);
    }
}
