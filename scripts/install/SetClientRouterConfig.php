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
use oat\tao\model\ClientLibConfigRegistry;

/**
 * Register extra routes
 *
 * @author Martin Nicholson <martin@taotesting.com>
 */
class SetClientRouterConfig extends InstallAction
{
    public function __invoke($params)
    {
        ClientLibConfigRegistry::getRegistry()->register(
            'taoDelivery/controller/DeliveryServer/index',
            array('extraRoutes' => ['taoInvalsi/Delivery/userMenu?page=delivery'])
        );
        ClientLibConfigRegistry::getRegistry()->register(
            'taoQtiTest/controller/runner/runner',
            array('extraRoutes' => ['taoInvalsi/Delivery/userMenu?page=testRunner'])
        );
        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'Extra routes added.');
    }
}