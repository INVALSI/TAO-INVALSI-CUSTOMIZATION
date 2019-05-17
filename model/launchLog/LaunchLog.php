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
    const OPTION_PERSISTENCE = 'persistence';
    const OPTION_FIELDS = 'fields';

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

    /**
     * @param \common_persistence_Persistence $persistence
     * @return mixed
     */
    public static function install(\common_persistence_Persistence $persistence);
}