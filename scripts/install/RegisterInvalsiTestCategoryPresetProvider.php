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
use oat\taoInvalsi\model\qtiTestCategory\InvalsiCategoryPresetProvider;
use oat\taoQtiTest\models\TestCategoryPresetRegistry;
use common_report_Report as Report;

class RegisterInvalsiTestCategoryPresetProvider extends InstallAction
{

    /**
     * This will register InvalsiTestCategoryPresetProvider to enable formula features in authoring
     *
     * @param $params
     * @return Report
     * @throws \common_Exception
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    public function __invoke($params)
    {
        $registry = TestCategoryPresetRegistry::getRegistry();
        $registry->set('taoInvalsi', InvalsiCategoryPresetProvider::class);

        $this->getServiceManager()->unregister('taoQtiTest/CategoryPresetProvider');
        $this->getServiceManager()->register('taoQtiTest/CategoryPresetProvider', new InvalsiCategoryPresetProvider());

        return Report::createSuccess('Invalsi TestCategoryPresetProvider registered. Default TestCategoryPresetProvider overridden');
    }
}