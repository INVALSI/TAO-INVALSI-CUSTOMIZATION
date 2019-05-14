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
 *  Copyright (c) 2016 - 2019 (original work) Open Assessment Technologies S.A.
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