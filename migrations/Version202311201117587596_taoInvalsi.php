<?php

declare(strict_types=1);

namespace oat\taoInvalsi\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\reporting\Report;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoInvalsi\model\export\AllBookletsExport;

/**
 * phpcs:disable Squiz.Classes.ValidClassName
 */
final class Version202311201117587596_taoInvalsi extends AbstractMigration
{
    private const EXPORT_SERVICE_NAME = 'taoResultExports/BookletsExport';

    public function getDescription(): string
    {
        return 'Add configuration option to AllBookletsExport service Invalsi implementation';
    }

    public function up(Schema $schema): void
    {
        $bookletsExportService = $this->getServiceLocator()->get(self::EXPORT_SERVICE_NAME);
        $serviceOptions = $bookletsExportService->getOptions();

        if (!array_key_exists(AllBookletsExport::OPTION_OVERRIDE_YEAR_IN_PATH, $serviceOptions)) {
            $serviceOptions[AllBookLetsExport::OPTION_OVERRIDE_YEAR_IN_PATH] = '';
        }

        $bookletsExportService->setOptions($serviceOptions);
        $this->getServiceLocator()->register(self::EXPORT_SERVICE_NAME, $bookletsExportService);

        $this->addReport(
            Report::createSuccess(
                sprintf(
                    'Configuration for %s successfully migrated upwards.',
                    self::EXPORT_SERVICE_NAME
                )
            )
        );
    }

    public function down(Schema $schema): void
    {
        $bookletsExportService = $this->getServiceLocator()->get(self::EXPORT_SERVICE_NAME);
        $serviceOptions = $bookletsExportService->getOptions();

        if (array_key_exists(AllBookletsExport::OPTION_OVERRIDE_YEAR_IN_PATH, $serviceOptions)) {
            unset($serviceOptions[AllBookletsExport::OPTION_OVERRIDE_YEAR_IN_PATH]);
        }

        $bookletsExportService->setOptions($serviceOptions);
        $this->getServiceLocator()->register(self::EXPORT_SERVICE_NAME, $bookletsExportService);

        $this->addReport(
            Report::createSuccess(
                sprintf(
                    'Configuration for %s successfully migrated downwards.',
                    self::EXPORT_SERVICE_NAME
                )
            )
        );
    }
}