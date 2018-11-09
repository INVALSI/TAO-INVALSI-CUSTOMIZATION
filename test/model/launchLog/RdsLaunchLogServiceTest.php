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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 *
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
