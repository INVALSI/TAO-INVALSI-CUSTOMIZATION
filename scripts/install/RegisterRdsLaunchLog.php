<?php
/**
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; under version 2
 *  of the License (non-upgradable).
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 *  Copyright (c) 2018 (original work) Open Assessment Technologies S.A.
 */

namespace oat\taoInvalsi\scripts\install;

use oat\oatbox\extension\InstallAction;
use oat\taoInvalsi\model\launchLog\RdsLaunchLogService;
use oat\taoInvalsi\model\launchLog\LaunchLog;
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
        $persistenceId = isset($params[0]) ? $params[0] : 'default';
        $class = isset($params[1]) ? $params[1] : RdsLaunchLogService::class;

        try {
            /** @var RdsLaunchLogService $service */
            $service = $this->getServiceLocator()->get(LaunchLog::SERVICE_ID);
            $persistenceId = $service->getOption(LaunchLog::OPTION_PERSISTENCE);
        } catch (ServiceNotFoundException $exception) {
            $service = new $class([
                LaunchLog::OPTION_PERSISTENCE => $persistenceId,
                LaunchLog::OPTION_FIELDS => [
                    LaunchLog::DELIVERY_EXECUTION_ID,
                    LaunchLog::DELIVERY_ID,
                    LaunchLog::TEST_TAKER_ID,
                    LaunchLog::BROWSER,
                    LaunchLog::BROWSER_VERSION,
                    LaunchLog::OS,
                    LaunchLog::OS_VERSION,
                    LaunchLog::CREATED_AT,
                ],
            ]);
            $service->setServiceLocator($this->getServiceLocator());
        }

        $persistence = $this->getServiceLocator()->get(\common_persistence_Manager::SERVICE_ID)->getPersistenceById($persistenceId);
        $this->registerService(LaunchLog::SERVICE_ID, $service);
        $report = new \common_report_Report(\common_report_Report::TYPE_SUCCESS, __('Registered Invalsi delivery launch log'));

        try {
            $service::install($persistence);
            $report->add(new \common_report_Report(\common_report_Report::TYPE_SUCCESS, __('Launch log storage successfully installed')));
        } catch (\Doctrine\DBAL\Schema\SchemaException $e) {
            $report->add(new \common_report_Report(\common_report_Report::TYPE_WARNING, $e->getMessage()));
        }

        $this->registerEvent(DeliveryExecutionCreated::class, [LaunchLog::SERVICE_ID, 'catchDeliveryExecutionCreated']);

        $report->add(new \common_report_Report(\common_report_Report::TYPE_WARNING, __('Event listener registered in event manager')));

        return $report;
    }
}
