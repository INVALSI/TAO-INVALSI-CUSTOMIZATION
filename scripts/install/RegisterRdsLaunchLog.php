<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2018  (original work) Open Assessment Technologies SA;
 * 
 */

namespace oat\taoInvalsi\scripts\install;

use oat\oatbox\extension\InstallAction;
use oat\taoInvalsi\model\launchLog\RdsLaunchLogService;
use oat\oatbox\service\ServiceNotFoundException;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionCreated;

/**
 * Class RegisterRdsLaunchLog
 * @package oat\taoInvalsi\scripts\install
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class RegisterRdsLaunchLog extends InstallAction
{
    
    public function __invoke($params)
    {
        try {
            /** @var RdsLaunchLogService $service */
            $service = $this->getServiceLocator()->get(RdsLaunchLogService::SERVICE_ID);
            $persistenceId = $service->getOption(RdsLaunchLogService::OPTION_PERSISTENCE);
        } catch (ServiceNotFoundException $exception) {
            $persistenceId = count($params) > 0 ? reset($params) : 'default';
            $service = new RdsLaunchLogService([
                RdsLaunchLogService::OPTION_PERSISTENCE => $persistenceId,
                RdsLaunchLogService::OPTION_FIELDS => [
                    RdsLaunchLogService::DELIVERY_EXECUTION_ID,
                    RdsLaunchLogService::DELIVERY_ID,
                    RdsLaunchLogService::TEST_TAKER_ID,
                    RdsLaunchLogService::BROWSER,
                    RdsLaunchLogService::BROWSER_VERSION,
                    RdsLaunchLogService::OS,
                    RdsLaunchLogService::OS_VERSION,
                    RdsLaunchLogService::CREATED_AT,
                ],
            ]);
            $service->setServiceLocator($this->getServiceLocator());
        }

        $persistence = $this->getServiceLocator()->get(\common_persistence_Manager::SERVICE_ID)->getPersistenceById($persistenceId);
        $service::install($persistence);

        $this->registerService(RdsLaunchLogService::SERVICE_ID, $service);

        $this->registerEvent(DeliveryExecutionCreated::class, [RdsLaunchLogService::SERVICE_ID, 'catchDeliveryExecutionCreated']);

        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, __('Registered Invalsi delivery launch log'));
    }
}
