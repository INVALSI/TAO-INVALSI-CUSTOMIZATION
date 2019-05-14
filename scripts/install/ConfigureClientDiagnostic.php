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

/**
 * Class ConfigureClientDiagnostic
 * @package oat\taoInvalsi\scripts\install
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
class ConfigureClientDiagnostic extends InstallAction
{
    /**
     * @param $params
     * @return \common_report_Report
     */
    public function __invoke($params)
    {
        try {
            /** @var \common_ext_ExtensionsManager $extensionManager */
            $extensionManager = $this->getServiceLocator()->get(\common_ext_ExtensionsManager::SERVICE_ID);
            if ($extensionManager->isInstalled('taoClientDiagnostic')) {
                $extension = $extensionManager->getExtensionById('taoClientDiagnostic');
                $config = $extension->getConfig('clientDiag');

                // Activate intensive bandwidth test.
                if (!isset($config['testers']) || !isset($config['testers']['intensive_bandwidth'])) {
                    $config['testers']['intensive_bandwidth'] = [
                        'enabled' => false,
                        'level' => 2,
                        'tester' => 'taoClientDiagnostic/tools/bandwidth/tester',
                        'unit' => 1.2,
                        'ideal' => 45,
                        'max' => 100
                    ];
                }

                $config['testers']['intensive_bandwidth']['enabled'] = true;

                // Activate data collection via HTTP GET.
                $config['customInput']['istituto'] = 'school';

                $extension->setConfig('clientDiag', $config);

                return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, "The Media Intensive check has been activated!");
            } else {
                return new \common_report_Report(\common_report_Report::TYPE_WARNING, "Cannot configure extension taoClientDiagnostic. It is not installed!");
            }
        } catch (\Exception $e) {
            return new \common_report_Report(\common_report_Report::TYPE_ERROR, $e->getMessage());
        }
    }
}
