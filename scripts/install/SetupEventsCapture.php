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

use oat\oatbox\event\EventManager;
use oat\oatbox\extension\InstallAction;
use oat\tao\model\event\LoginSucceedEvent;
use oat\tao\model\event\LogoutSucceedEvent;
use oat\taoInvalsi\model\session\Capture;

class SetupEventsCapture extends InstallAction
{
    public function __invoke($params)
    {
        $this->getServiceManager()->register(Capture::SERVICE_ID, new Capture([Capture::OPTION_PERSISTENCE => 'cache']));

        /** @var EventManager $eventManager */
        $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
        $eventManager->attach(LoginSucceedEvent::class, [Capture::SERVICE_ID, 'storeLogin']);
        $eventManager->attach(LogoutSucceedEvent::class, [Capture::SERVICE_ID, 'storeLogout']);
        $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);

        return new \common_report_Report(\common_report_Report::TYPE_ERROR);

    }
}
