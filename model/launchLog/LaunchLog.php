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

namespace oat\taoInvalsi\model\launchLog;

use oat\taoDelivery\model\execution\DeliveryExecutionInterface;

/**
 * Interface LaunchLog
 *
 * @package oat\taoInvalsi\model\taoInvalsi
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 */
interface LaunchLog
{
    const SERVICE_ID = 'taoInvalsi/LaunchLog';

    const DELIVERY_EXECUTION_ID = 'delivery_execution_id';
    const DELIVERY_ID = 'delivery_id';
    const TEST_TAKER_ID = 'test_taker_id';
    const BROWSER = 'browser';
    const BROWSER_VERSION = 'browser_version';
    const OS = 'os';
    const OS_VERSION = 'os_version';
    const CREATED_AT = 'created_at';

    public function log(DeliveryExecutionInterface $deliveryExecution);

    /**
     * @param array $params
     *  [
     *      'delivery_execution_id' => '',
     *      'DELIVERY_EXECUTION_ID' => '',
     *      'test_taker_id' => '',
     *      'from' => '',
     *      'to' => '',
     *  ]
     * @param array $options
     *  [
     *      'order' => 'created_at',
     *      'dir' => 'asc',
     *      'limit' => null, // to get all records
     *      'offset' => 0
     *  ]
     * @return mixed
     */
    public function search($params = [], $options = []);
}