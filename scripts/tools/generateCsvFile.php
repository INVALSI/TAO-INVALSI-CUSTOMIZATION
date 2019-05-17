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
