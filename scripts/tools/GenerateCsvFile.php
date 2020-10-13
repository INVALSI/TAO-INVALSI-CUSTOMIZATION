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

namespace oat\taoInvalsi\scripts\tools;

use \oat\taoResultExports\scripts\tools\{
    GenerateCsvFile as OriginalGenerateCsvFile
};
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\taoResultExports\model\export\AllBookletsExport;

class GenerateCsvFile extends OriginalGenerateCsvFile
{
    protected const OPTION_WEEKLY = 'weekly';

    protected function provideOptions(): array
    {
        return array_merge(parent::provideOptions(), [
            self::OPTION_WEEKLY => [
                'longPrefix' => self::OPTION_WEEKLY,
                'flag' => true,
                'description' => 'Split result exports by week'
            ]
        ]);
    }
}
