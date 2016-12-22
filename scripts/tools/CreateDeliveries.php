<?php
/**
 * Copyright (c) 2016 Open Assessment Technologies, S.A.
 *
 * @author Jérôme Bogaerts, <jerome@taotesting.com>
 */

namespace oat\taoInvalsi\scripts\tools;

use oat\oatbox\action\Action;
use oat\taoDeliveryRdf\model\SimpleDeliveryFactory;

class CreateDeliveries implements Action
{
    private $basePath;
    
    protected function getBasePath()
    {
        return $this->basePath;
    }
    
    protected function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }
    
    public function __invoke($params)
    {
        \common_ext_ExtensionsManager::singleton()->getExtensionById('taoQtiTest');
        
        $this->setBasePath(realpath(dirname($params[0])));
        $json = json_decode(file_get_contents($params[0]), true);
        
        // Main report.
        $report = new \common_report_Report(
            \common_report_Report::TYPE_SUCCESS, 
            "Success!"
        );
        
        foreach ($json['tests'] as $test) {
            $report->add($this->importTest($test['package'], $test['deliveries']));
        }
        
        $successMsg = "The script ended gracefully.";
        $errorMsg = "The script could not be run gracefully.";
        
        $this->populateReport($report, $successMsg, $errorMsg);

        return $report;
    }
    
    protected function importTest($source, array $deliveries)
    {
        $absoluteSource = realpath($this->getBasePath() . '/' . $source);
        
        // Test import report.
        $report = new \common_report_Report(
            \common_report_Report::TYPE_INFO, 
            "Unknown status."
        );
        
        $report->add(
            \taoQtiTest_models_classes_QtiTestService::singleton()->importMultipleTests(
                new \core_kernel_classes_Class(TAO_TEST_CLASS),
                $absoluteSource
            )
        );
        
        $successMsg = "The QTI Test located at '${absoluteSource}' was successfully imported.";
        $errorMsg = "The QTI Test located at '${absoluteSource}' could not be imported.";
        
       if ($this->populateReport($report, $successMsg, $errorMsg) === true) {
            foreach ($deliveries as $delivery) {
                $this->createDelivery($delivery);
            }
        }
        
        return $report;
    }
    
    protected function createDelivery(array $delivery)
    {
        
    }
    
    private static function populateReport(\common_report_Report $report, $successMsg, $errorMsg)
    {
        if ($report->containsError() === true) {
            $report->setType(\common_report_Report::TYPE_ERROR);
            $report->setMessage($errorMsg);
            
            return false;
        } else {
            $report->setType(\common_report_Report::TYPE_SUCCESS);
            $report->setMessage($successMsg);
            
            return true;
        }
    }
}
