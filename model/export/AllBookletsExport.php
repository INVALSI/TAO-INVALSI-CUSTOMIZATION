<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2016  (original work) Open Assessment Technologies SA;
 *
 * @author Alexander Zagovorichev <zagovorichev@1pt.com>
 */

namespace oat\taoInvalsi\model\export;


use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\ServiceManager;
use oat\taoResultServer\models\classes\ResultServerService;
use oat\taoResultServer\models\classes\ResultManagement;
use oat\oatbox\filesystem\File;
use qtism\data\storage\php\PhpDocument;
use qtism\data\AssessmentTest;
use qtism\data\ExtendedAssessmentItemRef;
use qtism\data\state\OutcomeDeclaration;
use qtism\data\state\ResponseDeclaration;

class AllBookletsExport
{


    /**
     * Default csv delimiter
     */
    const CSV_DELIMITER = ',';

    /**
     * Default csv enclosure
     */
    const CSV_ENCLOSURE = '"';

    /**
     * // 77 - not needed for current delivery
     * //      #1# (should be set in the getAllDeliveryData on merge results)
     * //      #2# (or if item was skipped by directedPair (branch condition))
     * // 88 - page not reached
     * // 99 - student didn't select choice
     */
    const NOT_REQUIRED = 77;
    const NOT_REACHED = 88;
    const NOT_SELECTED = 99;

    /**
     * Order and expected columns
     * @var array
     */
    private $expectedVariables = [
        'duration',
        'numAttempts',
    ];

    private $variablesByDeliveries = [];


    /**
     * @var array
     */
    private $deliveries;

    /**
     * Filesystem File to manage exported file storage
     *
     * @var File
     */
    protected $exportFile;

    public function __construct(array $deliveries)
    {
        $this->deliveries = $deliveries;
        $filename = 'booklet_export_'.date('Y_m_d_H_i_s').'.csv';
        $this->exportFile = $this->getServiceManager()
            ->get(FileSystemService::SERVICE_ID)
            ->getDirectory('taoQtiItem')
            ->getFile('export/'.$filename);
    }

    /**
     * Generate array with statistics for all deliveries
     * @return \common_report_Report
     */
    public function export()
    {
        $report = \common_report_Report::createSuccess();

        $headersReport = $this->getHeaders();
        $report->add($headersReport);

        $rowsReport = $this->getRows();
        $report->add($rowsReport);

        if($report->containsError()){
            $report->setType(\common_report_Report::TYPE_ERROR);
            $report->setMessage('Some errors occurred during export :');
        }

        $rows = $rowsReport->getData();
        $report->setMessage($rows.' rows has been exported');
        return $report;
    }

    /**
     * Get the list of headers for all deliveries that we want to export
     * @return \common_report_Report
     * @throws \common_exception_Error
     */
    private function getHeaders()
    {
        $headers = array('ID', 'IDFORM');
        $report = \common_report_Report::createSuccess();


        /** @var \tao_models_classes_service_FileStorage $storageService */
        $storageService = $this->getServiceManager()->get(\tao_models_classes_service_FileStorage::SERVICE_ID);


        foreach($this->deliveries as $delivery){
            //Get item list
            $directories = $delivery->getPropertyValues(new \core_kernel_classes_Property(PROPERTY_COMPILEDDELIVERY_DIRECTORY));
            foreach($directories as $directory){
                $dir = $storageService->getDirectoryById($directory);
                $files = $dir->getFlyIterator(3);
                /** @var File $file */
                foreach($files as $file){
                    if($file->getBasename() === 'compact-test.php'){
                        $phpDocument = new PhpDocument();
                        $phpDocument->loadFromString($file->read());
                        /** @var AssessmentTest $assementTest */
                        $assementTest = $phpDocument->getDocumentComponent();
                        $components = $assementTest->getComponentsByClassName('assessmentItemRef');

                        /** @var ExtendedAssessmentItemRef $component */
                        foreach($components as $component){
                            $identifier = $component->getIdentifier();
                            $outcomes = $component->getOutcomeDeclarations();
                            $responses = $component->getResponseDeclarations();
                            /** @var ResponseDeclaration $response */
                            foreach ($responses as $response) {
                                $this->variablesByDeliveries[$delivery->getUri()][] = $identifier . '-' . $response->getIdentifier();
                            }

                            /** @var OutcomeDeclaration $outcome */
                            foreach ($outcomes as $outcome) {
                                $this->variablesByDeliveries[$delivery->getUri()][] = $identifier . '-' . $outcome->getIdentifier();
                            }

                            foreach($this->expectedVariables as $expectedVariable){
                                $this->variablesByDeliveries[$delivery->getUri()][] = $identifier . '-' . $expectedVariable;
                            }
                        }
                    }

                }
            }

        }

        foreach($this->variablesByDeliveries as $key => $variable){
            $diff = array_diff($variable, $headers);
            if(!empty($diff)){
                $headers = array_merge($headers, $diff);
            }
        }

        if(empty($headers)){
            $report->setType(\common_report_Report::TYPE_ERROR);
            $report->setMessage('Impossible to get headers for theses deliveries');
            return $report;
        }

        $this->writeToCsv($headers);

        return $report;
    }


    /**
     * Get all the rows of all executions of all deliveries
     * @return \common_report_Report
     */
    private function getRows()
    {

        $report = \common_report_Report::createSuccess();

        $i = 0;
        /** @var \core_kernel_classes_Resource $delivery */
        foreach($this->deliveries as $delivery){
            $storage = $this->getServiceManager()->get(ResultServerService::SERVICE_ID)->getResultStorage($delivery);

            if($storage instanceof ResultManagement){
                $results = $storage->getResultByDelivery([$delivery->getUri()]);
                // get each row and write it to the csv

                foreach($results as $result){
                    $execution = $execution = \taoDelivery_models_classes_execution_ServiceProxy::singleton()->getDeliveryExecution($result['deliveryResultIdentifier']);
                    $row = array();

                    $user = new \core_kernel_classes_Resource($execution->getUserIdentifier());
                    $row['ID'] = $user->getLabel();
                    $row['IDFORM'] = $delivery->getLabel();


                    $callIds = $storage->getRelatedItemCallIds($execution->getIdentifier());
                    foreach($callIds as $callId){
                        $itemResults = $storage->getVariables($callId);
                        $splitCallId = explode('#', $callId);
                        $itemIdentifier = explode('.',$splitCallId[1]);
                        $itemIdentifier = $itemIdentifier[1];
                        foreach($itemResults as $itemResult){
                            $itemResult = array_pop($itemResult);
                            /** @var \taoResultServer_models_classes_Variable $variable */
                            $variable = $itemResult->variable;
                            if(in_array($itemIdentifier.'-'.$variable->getIdentifier(), $this->variablesByDeliveries[$delivery->getUri()])){
                                $row[$itemIdentifier.'-'.$variable->getIdentifier()] = $this->formatVariable($variable);
                            } else {
                                \common_Logger::d($itemIdentifier.'-'.$variable->getIdentifier().' is not taken into account');
                            }
                        }

                    }

                    if(empty($row)){
                        $report->setType(\common_report_Report::TYPE_ERROR);
                        $report->setMessage('Impossible to get row for theses delivery '.$delivery. 'and execution '.$execution->getIdentifier());
                        return $report;
                    }
                    $i++;
                    $this->fillRow($row, $delivery->getUri());
                    $this->writeToCsv($row);
                }
            }

        }

        $report->setData($i);

        return $report;
    }

    /**
     * Write the row provided in the csv file
     * @param array $row
     */
    private function writeToCsv(array $row)
    {
        $content = self::CSV_ENCLOSURE
            . implode(self::CSV_ENCLOSURE . self::CSV_DELIMITER . self::CSV_ENCLOSURE, $row)
            . self::CSV_ENCLOSURE
            ."\n";


        if(!$this->exportFile->put($content)){
            \common_Logger::w('Fail to write in the csv : '.implode(',', $row));
        }
    }

    /**
     * Get the filename
     * @return string
     */
    public function getFilename(){
        return $this->exportFile->getPrefix().$this->exportFile->getBasename();
    }

    /**
     * Format the variable value to correspond to what invalsi want
     * @param \taoResultServer_models_classes_Variable $variable
     * @return string $variable
     */
    private function formatVariable($variable){

        if($variable instanceof \taoResultServer_models_classes_ResponseVariable){
            return $variable->getValue();
        } else {
            return $variable->getValue();
        }
    }

    /**
     * Fill empty cells with correct placeholder
     * @param array $row the row to fill
     * @param string $deliveryUri the delivery that the test taker took
     */
    private function fillRow(&$row, $deliveryUri)
    {
        $neededColumns = array();
        $emptyColumns = array();
        foreach($this->variablesByDeliveries as $delivery => $columns){
            if($delivery === $deliveryUri){
                $neededColumns = $this->variablesByDeliveries[$deliveryUri];
            } else {
                $emptyColumns = $this->variablesByDeliveries[$deliveryUri];
            }
        }
        foreach($neededColumns as $column){
            if(!in_array($column, array_keys($row))){
                $row[$column] = self::NOT_REACHED;
            }
        }

        foreach($emptyColumns as $column){
            if(!in_array($column, array_keys($row))){
                $row[$column] = self::NOT_REQUIRED;
            }
        }

    }



    private function getServiceManager()
    {
        return ServiceManager::getServiceManager();
    }
}
