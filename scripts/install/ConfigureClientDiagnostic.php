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
