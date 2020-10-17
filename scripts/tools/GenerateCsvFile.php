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

use oat\taoResultExports\model\export\AllBookletsExport;
use \oat\taoInvalsi\model\export\AllBookletsExport as InvalsiBookletExporter;
use \oat\taoResultExports\scripts\tools\{
    GenerateCsvFile as OriginalGenerateCsvFile
};

class GenerateCsvFile extends OriginalGenerateCsvFile
{
    protected const OPTION_WEEKLY = 'weekly';

    protected const OPTION_COVERAGE_VALUES = [
        self::OPTION_DAILY,
        self::OPTION_WEEKLY,
    ];
    
    public const OPTION_EXPORT_FOLDER = 'export_folder';

    protected function provideOptions(): array
    {
        return array_merge(parent::provideOptions(), [
            self::OPTION_WEEKLY => [
                'longPrefix' => self::OPTION_WEEKLY,
                'flag' => true,
                'description' => 'DEPRECATED. Please, use coverage option. Split result exports by week'
            ],
    
            static::OPTION_EXPORT_FOLDER => [
                'longPrefix' => static::OPTION_EXPORT_FOLDER,
                'prefix' => 'ef',
                'defaultValue' => 'PRODUCTION',
                'cast' => 'string',
                'description' => 'Default export folder aka environment (PROD/PREPROD/etc)'
            ]
        ]);
    }
    
    protected function getConfiguredBookletExporter(): AllBookletsExport
    {
        $configuredExporter = parent::getConfiguredBookletExporter();
        
        if ($configuredExporter instanceof InvalsiBookletExporter) {
            $configuredExporter->setOption(static::OPTION_EXPORT_FOLDER, $this->getOption(static::OPTION_EXPORT_FOLDER));
        }
        
        return $configuredExporter;
    }
}
