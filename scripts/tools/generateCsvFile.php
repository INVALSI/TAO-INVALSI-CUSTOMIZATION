<?php
/**
 * Copyright (c) 2016 Open Assessment Technologies, S.A.
 *
 * @author A.Zagovorichev, <zagovorichev@1pt.com>
 */


namespace oat\taoInvalsi\scripts\tools;


use oat\oatbox\action\Action;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\taoInvalsi\model\export\AllBookletsExport;

class generateCsvFile implements Action
{
    public function __invoke($params)
    {
        \common_ext_ExtensionsManager::singleton()->getExtensionById('taoResultServer');
        \common_ext_ExtensionsManager::singleton()->getExtensionById('taoDeliveryRdf');

        $delivery = array_shift($params);
        var_dump($delivery);
        if(is_null($delivery) || strtolower($delivery) === 'all'){
            $deliveries = DeliveryAssemblyService::singleton()->getRootClass()->getInstances(true);
        } else {
            $deliveries = [new \core_kernel_classes_Resource($delivery)];
        }
        
        $identifierStrategy = array_shift($params);
        if (is_null($identifierStrategy)){
            $identifierStrategy = 'itemRef';
        }

        $bookletExporter = new AllBookletsExport($deliveries, $identifierStrategy);
        $exportReport = $bookletExporter->export();


        $report = new \common_report_Report(\common_report_Report::TYPE_INFO, 'Writing file : '.$bookletExporter->getFilename().PHP_EOL.'For deliveries : '.implode(',', $deliveries));
        $report->add($exportReport);

        return $report;
    }
}
