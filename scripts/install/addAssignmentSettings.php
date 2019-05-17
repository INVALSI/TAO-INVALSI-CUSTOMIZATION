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

use oat\oatbox\extension\InstallAction;
use oat\taoDelivery\model\AssignmentService;
use oat\taoDeliveryRdf\model\GroupAssignment;

class addAssignmentSettings extends InstallAction
{
    public function __invoke($params)
    {
        /** @var GroupAssignment $assignment */
        $assignment = $this->getServiceManager()->get(AssignmentService::SERVICE_ID);
        $assignment->setOption(GroupAssignment::DISPLAY_DATES_OPTION, false);
        $this->registerService(AssignmentService::SERVICE_ID, $assignment);

        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'Assessment dates won\'t be shown');
    }
}