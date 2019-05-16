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
 *  Copyright (c) 2016 (original work) Open Assessment Technologies S.A.
 */

namespace oat\taoInvalsi\scripts\tools;


use oat\oatbox\action\Action;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\taoInvalsi\model\export\AllBookletsExport;
use \common_report_Report as Report;

class generateCsvFile implements Action
{
    public function __invoke($params)
    {
        \common_ext_ExtensionsManager::singleton()->getExtensionById('taoResultServer');
        \common_ext_ExtensionsManager::singleton()->getExtensionById('taoDeliveryRdf');

        // Param 1: Deliveries to be exported.
        $delivery = array_shift($params);
        if (is_null($delivery)) {
            return new Report(
                Report::TYPE_ERROR,
                "Missing parameter 1 - 'deliveries': 'all' or a comma (',') separated list of Delivery URIs." 
            );
        }
        
        if (strtolower($delivery) === 'all'){
            $deliveries = DeliveryAssemblyService::singleton()->getRootClass()->getInstances(true);
        } else {
            $deliveries = [];
            $explodedDeliveries = explode(',', $delivery);
            foreach($explodedDeliveries as $delivery){
                  $deliveries[] = new \core_kernel_classes_Resource($delivery);
            }
        }
        
        // Param 2: Identifier strategy.
        $identifierStrategy = array_shift($params);
        if (is_null($identifierStrategy)) {
            return new Report(
                Report::TYPE_ERROR,
                "Missing parameter 2 - 'identifier strategy': possible values are 'itemRef'|'identifier'|'title'|'label'."
            );
        }
        
        // Param 3: Variable policy.
        $variablePolicy = array_shift($params);
        if (is_null($variablePolicy)) {
            return new Report(
                Report::TYPE_ERROR,
                "Missing parameter 3 - 'variable policy': possible values are 'all'|'response'|'outcome'."
            );
        }
        $variablePolicy = self::transformStringVariablePolicyIntoConstant(strtolower($variablePolicy));
        
        $bookletExporter = new AllBookletsExport($deliveries, $identifierStrategy);
        $bookletExporter->setVariablePolicy($variablePolicy);
        
        // Param 4: Variable blacklist.
        $variableBlacklist = array_shift($params);
        if (is_null($variableBlacklist) === false) {
            $bookletExporter->setVariableBlacklist(explode(',', $variableBlacklist));
        }
        
        // Param 5: Raw mode.
        $rawMode = array_shift($params);
        if (is_null($rawMode) === false) {
            $bookletExporter->addAlternateMissingDataEnconding(AllBookletsExport::NOT_RESPONDED, '');
        }
        
        $exportReport = $bookletExporter->export();


        $report = new \common_report_Report(
            \common_report_Report::TYPE_INFO,
            'Writing file : '.$bookletExporter->getFilename() . PHP_EOL . 'for deliveries : '.implode(',', $deliveries)
        );
        
        $report->add($exportReport);

        return $report;
    }
    
    private static function transformStringVariablePolicyIntoConstant($strVariablePolicy)
    {
        $variablePolicy = AllBookletsExport::VARIABLE_POLICY_ALL;
        
        switch ($strVariablePolicy) {
            case 'outcome':
                $variablePolicy = AllBookletsExport::VARIABLE_POLICY_OUTCOME;
                break;
                
            case 'response':
                $variablePolicy = AllBookletsExport::VARIABLE_POLICY_RESPONSE;
                break;
        }
        
        return $variablePolicy;
    }
}
