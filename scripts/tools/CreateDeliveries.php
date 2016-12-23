<?php
/**
 * Copyright (c) 2016 Open Assessment Technologies, S.A.
 *
 * @author Jérôme Bogaerts, <jerome@taotesting.com>
 */

namespace oat\taoInvalsi\scripts\tools;

use oat\oatbox\action\Action;
use oat\taoDeliveryRdf\model\SimpleDeliveryFactory;
use oat\taoDeliveryRdf\model\DeliveryContainerService;
use oat\authKeyValue\helpers\DataGeneration;
use oat\taoGroups\models\GroupsService;

class CreateDeliveries implements Action
{
    private $basePath;
    private $persistence;
    
    protected function getBasePath()
    {
        return $this->basePath;
    }
    
    protected function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }
    
    protected function getPersistence()
    {
        return $this->persistence;
    }
    
    protected function setPersistence(\common_persistence_SqlPersistence $persistence)
    {
        $this->persistence = $persistence;
    }
    
    public function __invoke($params)
    {
        \common_ext_ExtensionsManager::singleton()->getExtensionById('taoQtiTest');
        \common_ext_ExtensionsManager::singleton()->getExtensionById('taoDeliveryRdf');
        
        $this->setPersistence(\common_persistence_Manager::getPersistence('default'));
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
            
            $test = current(current($report->getSuccesses())->getSuccesses())->getData()->rdfsResource;
           
            foreach ($deliveries as $delivery) {
                $report->add($this->createDelivery($test, $delivery));
            }
        }
        
        return $report;
    }
    
    protected function createDelivery(\core_kernel_classes_Resource $test, array $delivery)
    {
        // Delivery creation main report.
        $report = new \common_report_Report(
            \common_report_Report::TYPE_INFO, 
            "Unknown status."
        );
        
        $deliveryReport = SimpleDeliveryFactory::create(new \core_kernel_classes_Class(CLASS_COMPILEDDELIVERY), $test, $delivery['label']);
        $report->add($deliveryReport);
        
        if ($deliveryReport->getType() !== \common_report_Report::TYPE_ERROR) {
            // Configure Delivery.
            $deliveryResource = $deliveryReport->getData();
            $deliveryResource->setPropertyValue(new \core_kernel_classes_Property(TAO_DELIVERY_MAXEXEC_PROP), $delivery['tokens']);
            $deliveryResource->setPropertyValue(new \core_kernel_classes_Property(TAO_DELIVERY_START_PROP), $delivery['startTimestamp']);
            $deliveryResource->setPropertyValue(new \core_kernel_classes_Property(TAO_DELIVERY_END_PROP), $delivery['endTimestamp']);
            
            $features = $deliveryResource->getPropertyValues(new \core_kernel_classes_Property(DeliveryContainerService::TEST_RUNNER_FEATURES_PROPERTY));
            
            if (isset($delivery['progressBar']) && $delivery['progressBar'] === false && ($index = array_search('progressBar', $features)) !== false) {
                unset($features[$index]);
            }
            
            $deliveryResource->editPropertyValues(new \core_kernel_classes_Property(DeliveryContainerService::TEST_RUNNER_FEATURES_PROPERTY), $features);
            
            // Create related groups.
            foreach ($delivery['groups'] as $group) {
                $report->add($this->createGroup($group, $deliveryResource));
            }
        }
        
        $successMsg = "Delivery '" . $delivery['label'] . "' was successfully created.";
        $errorMsg = "Delivery '" . $delivery['label'] . "' cound not be created.";
        
        $this->populateReport($report, $successMsg, $errorMsg);
        
        return $report;
    }
    
    protected function createGroup(array $group, \core_kernel_classes_Resource $delivery)
    {
        $report = new \common_report_Report(
            \common_report_Report::TYPE_INFO, 
            "Unknown status."
        );
        
        $groupClass = new \core_kernel_classes_Class(TAO_GROUP_CLASS);
        $groupResource = $groupClass->createInstance($group['label'], '', $group['uri']);
        $groupResource->setPropertyValue(new \core_kernel_classes_Property(PROPERTY_GROUP_DELVIERY), $delivery->getUri());
        
        $report->setType(\common_report_Report::TYPE_SUCCESS);
        $report->setMessage("Group '" . $group['label'] . "' successfully created.");
        
        $absoluteSource = realpath($this->getBasePath() . '/' . $group['users']);
        $fp = fopen($absoluteSource, 'r');
        $userCount = 0;
        
        while (($data = fgetcsv($fp, '', ',')) !== false) {
            $login = $data[0];
            $password = \core_kernel_users_Service::getPasswordHash()->encrypt($data[1]);
            $lang = $data[2];
            $userUri = $group['userUriPattern'] . strtolower($login);
            
            // Insert user data in key/value storage.
            DataGeneration::createUser(
                [
                    PROPERTY_USER_LOGIN => $login,
                    PROPERTY_USER_PASSWORD => $password,
                    RDFS_LABEL => "Test Taker " . $login,
                    //PROPERTY_USER_FIRSTNAME => "Test Taker " . $login,
                    //PROPERTY_USER_LASTNAME => "Group " . $group['label'],
                    GroupsService::PROPERTY_MEMBERS_URI => [$groupResource->getUri()]
                ],
                $lang,
                $userUri
            );
            
            // Make user label/login available for RDS Results.
            $this->getPersistence()->insert(
                'statements',
                array(
                    'modelid' => 1,
                    'subject' => $userUri,
                    'predicate' => RDFS_LABEL,
                    'object' => $login,
                    'l_language' => 'en-US'
                )
            );
            
            $this->getPersistence()->insert(
                'statements',
                array(
                    'modelid' => 1,
                    'subject' => $userUri,
                    'predicate' => PROPERTY_USER_LOGIN,
                    'object' => $login,
                    'l_language' => 'en-US'
                )
            );
            
            $userCount++;
        }
        
        $report->add(
            new \common_report_Report(
                \common_report_Report::TYPE_SUCCESS, 
                "${userCount} users assigned to Group '" . $group['label'] . "'"
            )
        );
        
        return $report;
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
