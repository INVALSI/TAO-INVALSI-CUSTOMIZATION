<?php
/**
 * ***********************************************************************
 * INVALSI CONFIDENTIAL
 * __________________
 *
 *  All Rights Reserved.
 *
 *  NOTICE:  All information contained herein is, and remains
 *  the property of INVALSI and its suppliers, if any.  The
 *  intellectual and technical concepts contained herein are
 *  proprietary to INVALSI and its suppliers, and are protected
 *  by trade secret or copyright law.
 *  Dissemination of this information or reproduction of this material
 *  is strictly forbidden unless prior written permission is obtained
 *  from INVALSI.
 *  ************************************************************************
 */

declare(strict_types=1);

namespace oat\taoInvalsi\scripts\tools;

use Exception;
use oat\taoResultExports\model\export\AllBookletsExport;
use \oat\taoInvalsi\model\export\AllBookletsExport as InvalsiBookletExporter;
use \oat\taoResultExports\scripts\tools\{
    GenerateCsvFile as OriginalGenerateCsvFile
};

class GenerateCsvFile extends OriginalGenerateCsvFile
{
    public const OPTION_WEEKLY = 'weekly';
    public const OPTION_FROM_DATE = 'fromStartDate';
    public const OPTION_FROM_THE_BEGINNING_OF_TIME = 'fromTheBeginningOfTime';

    protected const OPTION_COVERAGE_VALUES = [
        self::OPTION_DAILY,
        self::OPTION_WEEKLY,
        self::OPTION_FROM_DATE,
        self::OPTION_FROM_THE_BEGINNING_OF_TIME,
    ];

    protected function provideOptions(): array
    {
        return array_merge(parent::provideOptions(), [
            self::OPTION_WEEKLY => [
                'longPrefix' => self::OPTION_WEEKLY,
                'flag' => true,
                'description' => 'DEPRECATED. Please, use coverage option. Split result exports by week'
            ],

            InvalsiBookletExporter::OPTION_EXPORT_FOLDER => [
                'longPrefix' => InvalsiBookletExporter::OPTION_EXPORT_FOLDER,
                'prefix' => 'ef',
                'defaultValue' => 'PRODUCTION',
                'cast' => 'string',
                'description' => 'Default export folder aka environment (PROD/PREPROD/etc)'
            ],

            InvalsiBookletExporter::OPTION_CAMPAIGN_CODE => [
                'longPrefix' => InvalsiBookletExporter::OPTION_CAMPAIGN_CODE,
                'prefix' => 'cc',
                'defaultValue' => null,
                'cast' => 'string',
                'description' => 'Campaign code to be used as the "root" folder of the exports'
            ],

            InvalsiBookletExporter::OPTION_START_DATE => [
                'longPrefix' => InvalsiBookletExporter::OPTION_START_DATE,
                'defaultValue' => null,
                'cast' => 'string',
                'description' => "Start time for deliveries executions export. Format: Y-m-d H:i:s. Example: --start_date '2021-03-04 03:19:06'"
            ],

            InvalsiBookletExporter::OPTION_EXCLUDE_COLUMNS => [
                'longPrefix' => InvalsiBookletExporter::OPTION_EXCLUDE_COLUMNS,
                'defaultValue' => null,
                'cast' => 'string',
                'description' => 'Columns that will be excluded from the report. Example: --exclude_columns SCORE,ATTEMPT_ID'
            ],
        ]);
    }

    protected function getConfiguredBookletExporter(): AllBookletsExport
    {
        if (
            $this->getOption(self::OPTION_COVERAGE) === self::OPTION_FROM_DATE
            && empty($this->getOption(InvalsiBookletExporter::OPTION_START_DATE))
        ) {
            throw new Exception(sprintf("Option '%s' should be set for the coverage type '%s'", InvalsiBookletExporter::OPTION_START_DATE, self::OPTION_FROM_DATE));
        }

        $configuredExporter = parent::getConfiguredBookletExporter();

        if ($configuredExporter instanceof InvalsiBookletExporter) {
            $configuredExporter->setOption(InvalsiBookletExporter::OPTION_EXPORT_FOLDER, $this->getOption(InvalsiBookletExporter::OPTION_EXPORT_FOLDER));
            $configuredExporter->setOption(InvalsiBookletExporter::OPTION_CAMPAIGN_CODE, $this->getOption(InvalsiBookletExporter::OPTION_CAMPAIGN_CODE));
            $configuredExporter->setOption(InvalsiBookletExporter::OPTION_START_DATE, $this->getOption(InvalsiBookletExporter::OPTION_START_DATE));
            $configuredExporter->setOption(InvalsiBookletExporter::OPTION_EXCLUDE_COLUMNS, $this->getOption(InvalsiBookletExporter::OPTION_EXCLUDE_COLUMNS));
        }

        return $configuredExporter;
    }
}
