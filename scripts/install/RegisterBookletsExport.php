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

namespace oat\taoInvalsi\scripts\install;

use oat\taoInvalsi\model\export\AllBookletsExport;
use oat\oatbox\extension\InstallAction;

/**
 * Class RegisterBookletsExport
 * @package oat\taoInvalsi\scripts\install
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class RegisterBookletsExport extends InstallAction
{
    public function __invoke($params)
    {
        $service = $this->getServiceManager()->get(AllBookletsExport::SERVICE_ID);
        $options = $service->getOptions();
        $options[AllBookletsExport::OPTION_NUMBER_OF_DAILY_EXPORT] = 1;
        $options[AllBookletsExport::NOT_REQUIRED_OPTION] = '38798977';
        $options[AllBookletsExport::NOT_ATTEMPTED_OPTION] = '29787988';
        $options[AllBookletsExport::NOT_RESPONDED_OPTION] = '17989789';
        $this->getServiceManager()->register(AllBookletsExport::SERVICE_ID, new AllBookletsExport($options));

        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, __('Registered Invalsi Booklet exporter'));
    }
}
