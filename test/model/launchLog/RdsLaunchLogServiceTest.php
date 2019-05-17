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

namespace oat\taoInvalsi\test\model\lauchLog\implementation;

use oat\taoInvalsi\model\launchLog\RdsLaunchLogService;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use oat\oatbox\service\ServiceManager;
use oat\generis\test\TestCase;

/**
 * Interface RdsLaunchLogServiceTest
 *
 * @package oat\taoInvalsi\test\model\lauchLog
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 */
class RdsLaunchLogServiceTest extends TestCase
{

    protected $service;
    protected $persistence;

    public function testLog()
    {
        $service = $this->getService();
        $time = '1541514390.43095';
        $deliveryExecution = $this->mockDeliveryExecution('foo', 'bar', 'baz', $time);
        $this->assertEquals(0, count($service->search()));
        $this->assertTrue($service->log($deliveryExecution));
        $expectedArr = [
            'delivery_execution_id' => 'foo',
            'delivery_id' => 'bar',
            'test_taker_id' => 'baz',
            'browser' => 'unknown',
            'browser_version' => 'unknown',
            'os' => 'unknown',
            'os_version' => 'unknown',
            'created_at' => \tao_helpers_Date::getTimeStamp($time, true),
        ];
        $this->assertEquals(1, count($service->search()));
        sort($expectedArr);
        $searchResult = $service->search()[0];
        sort($searchResult);
        $this->assertEquals($expectedArr, $searchResult);
    }

    public function testSearch()
    {
        $service = $this->getService();
        $time1 = '1541514390.43095';
        $time2 = '1541514392.43095';
        $deliveryExecution1 = $this->mockDeliveryExecution('foo1', 'bar1', 'baz1', $time1);
        $deliveryExecution2 = $this->mockDeliveryExecution('foo2', 'bar2', 'baz2', $time2);
        $this->assertEquals(0, count($service->search()));
        $this->assertTrue($service->log($deliveryExecution1));
        $this->assertTrue($service->log($deliveryExecution2));
        $this->assertEquals(2, count($service->search()));

        $this->assertEquals('foo1', $service->search([RdsLaunchLogService::DELIVERY_EXECUTION_ID => 'foo1'])[0][RdsLaunchLogService::DELIVERY_EXECUTION_ID]);
        $this->assertEquals(0, count($service->search([RdsLaunchLogService::DELIVERY_ID => 'foo1'])));
        $this->assertEquals('foo1', $service->search(['from' => '1541514390', 'to' => '1541514391'])[0][RdsLaunchLogService::DELIVERY_EXECUTION_ID]);
        $this->assertEquals(1, count($service->search(['from' => '1541514390', 'to' => '1541514391'])));
        $this->assertEquals(1, count($service->search([], ['limit' => 1])));
        $this->assertEquals(1, count($service->search([], ['offset' => 1])));
    }

    /**
     * @throws
     * @return DeliveryExecutionInterface
     */
    protected function mockDeliveryExecution($id, $deliveryId, $userId, $startTime)
    {
        $de = $this->getMockBuilder(DeliveryExecutionInterface::class)
            ->setMethods([
                'getIdentifier', 'getDelivery', 'getUserIdentifier', 'getStartTime', 'getLabel', 'getFinishTime', 'getState', 'setState'
            ])->getMock();
        $de->method('getIdentifier')->willReturn($id);
        $de->method('getDelivery')->willReturn(new \core_kernel_classes_Resource($deliveryId));
        $de->method('getUserIdentifier')->willReturn($userId);
        $de->method('getStartTime')->willReturn($startTime);

        return $de;
    }

    /**
     * @throws
     * @return RdsLaunchLogService
     */
    protected function getService()
    {
        $persistenceManager = $this->getSqlMock('launch_log');
        $this->persistence = $persistenceManager->getPersistenceById('launch_log');
        RdsLaunchLogService::install($this->persistence);
        $service = new RdsLaunchLogService([
            RdsLaunchLogService::OPTION_PERSISTENCE => 'launch_log',
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
        $config = new \common_persistence_KeyValuePersistence([], new \common_persistence_InMemoryKvDriver());
        $config->set(\common_persistence_Manager::SERVICE_ID, $persistenceManager);
        $serviceManager = new ServiceManager($config);
        $service->setServiceLocator($serviceManager);
        return $service;
    }

}
