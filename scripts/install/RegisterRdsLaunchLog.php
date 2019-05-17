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
