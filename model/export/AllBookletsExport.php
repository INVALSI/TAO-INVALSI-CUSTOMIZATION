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
 * @author Antoine Robin <antoine@taotesting.com>
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 */

namespace oat\taoInvalsi\model\export;


use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\ServiceManager;
use oat\taoResultServer\models\classes\ResultServerService;
use oat\taoResultServer\models\classes\ResultManagement;
use oat\oatbox\filesystem\File;
use qtism\common\datatypes\QtiPair;
use qtism\data\storage\php\PhpDocument;
use qtism\data\AssessmentTest;
use qtism\data\ExtendedAssessmentItemRef;
use qtism\data\state\OutcomeDeclaration;
use qtism\data\state\ResponseDeclaration;
use qtism\runtime\common\MultipleContainer;

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
    
    private $matchTypeByDeliveries = [];
    
    private $matchSetsByDeliveries = [];
    
    private $hottextTypeByDeliveries = [];

    private $choiceTypeByDeliveries = [];

    /**
     * @var array
     */
    private $deliveries;

    private $headers = null;

    /**
     * Filesystem File to manage exported file storage
     *
     * @var File
     */
    protected $exportFile;
    
    protected $flySystemFile;

    public function __construct(array $deliveries)
    {
        $this->deliveries = $deliveries;
    }

    /**
     * Generate array with statistics for all deliveries
     * @return \common_report_Report
     */
    public function export()
    {
        $report = \common_report_Report::createSuccess();

        // Initialize temporary file.
        $tmpLocation = tempnam(sys_get_temp_dir(), 'invalsiexport');
        $this->exportFile = fopen($tmpLocation, 'w+');

        $headers = $this->getHeaders();
        $this->writeToCsv($headers, true);

        $rowsReport = $this->getRows();
        $report->add($rowsReport);

        if($report->containsError()){
            $report->setType(\common_report_Report::TYPE_ERROR);
            $report->setMessage('Some errors occurred during export :');
        }

        $rows = $rowsReport->getData();
        $report->setMessage($rows.' rows has been exported');
        
        // Write as stream and close temporary file.
        $filename = 'export_'. date('His') .'.csv';
        $this->flySystemFile = $this->getServiceManager()
            ->get(FileSystemService::SERVICE_ID)
            ->getDirectory('taoMonitoring')
            ->getFile(date('Y_m_d') .'/bookletExport/'. gethostname() .'/'. $filename);
            
        rewind($this->exportFile);
        $this->flySystemFile->write($this->exportFile);
        
        fclose($this->exportFile);
        unlink($tmpLocation);
        
        return $report;
    }

    /**
     * Get the list of headers for all deliveries that we want to export
     * @return array $headers
     * @throws \common_exception_Error
     */
    private function getHeaders()
    {

        if(is_null($this->headers)){
            $headers = array('ID', 'IDFORM');
            $report = \common_report_Report::createSuccess();


            /** @var \tao_models_classes_service_FileStorage $storageService */
            $storageService = $this->getServiceManager()->get(\tao_models_classes_service_FileStorage::SERVICE_ID);

            foreach($this->deliveries as $delivery){
                //Get item list
                $deliveryUri = $delivery->getUri();
                $this->variablesByDeliveries[$deliveryUri] = [];
                $this->matchTypeByDeliveries[$deliveryUri] = [];
                $this->hottextTypeByDeliveries[$deliveryUri] = [];
                $directories = $delivery->getPropertyValues(new \core_kernel_classes_Property(PROPERTY_COMPILEDDELIVERY_DIRECTORY));
                
                foreach ($directories as $directory) {
                    $dir = $storageService->getDirectoryById($directory);
                    $files = $dir->getFlyIterator(3);
                    
                    /** @var File $file */
                    foreach ($files as $file) {
                        
                        if ($file->getBasename() === 'compact-test.php') {
                            
                            $phpDocument = new PhpDocument();
                            $phpDocument->loadFromString($file->read());
                            /** @var AssessmentTest $assementTest */
                            $assementTest = $phpDocument->getDocumentComponent();
                            $components = $assementTest->getComponentsByClassName('assessmentItemRef');

                            /** @var ExtendedAssessmentItemRef $component */
                            foreach ($components as $component) {
                                
                                $href = explode('|',$component->getHref());
                                $private = $href[2];
                                $itemJsonDir = $storageService->getDirectoryById($private);
                                $itemJsonFiles = $itemJsonDir->getFlyIterator(3);
                                $choices = array();

                                foreach ($itemJsonFiles as $itemJsonFile) {
                                    
                                    if ($itemJsonFile->getBasename() === 'item.json') {
                                        
                                        $itemJson = json_decode($itemJsonFile->read());
                                        $interactions = AllBookletsExportUtils::getInteractions($itemJson);
                                        
                                        if (isset($interactions['matchInteraction'])) {
                                            
                                            foreach ($interactions['matchInteraction'] as $responseIdentifier => $jsonInteraction) {
                                                $headerResponseIdentifier = $component->getIdentifier() . '-' . $responseIdentifier;
                                                
                                                if (AllBookletsExportUtils::isMatchByColumn($jsonInteraction)) {
                                                    // By column strategy.
                                                    $matchSetIndex = 0;
                                                    $this->matchTypeByDeliveries[$deliveryUri][$headerResponseIdentifier] = $matchSetIndex;
                                                } elseif (AllBookletsExportUtils::isMatchByRow($jsonInteraction)) {
                                                    // By row strategy.
                                                    $matchSetIndex = 1;
                                                    $this->matchTypeByDeliveries[$deliveryUri][$headerResponseIdentifier] = $matchSetIndex;
                                                } else {
                                                    // No strategy could be identified, regular output for this interaction.
                                                    continue;
                                                }
                                                
                                                $matchSets = AllBookletsExportUtils::getMatchSets($jsonInteraction, $responseIdentifier);
                                                $this->matchSetsByDeliveries[$deliveryUri][$headerResponseIdentifier] = $matchSets;
                                                $choices[$responseIdentifier] = $matchSets[$matchSetIndex];
                                            }
                                        } elseif (isset($interactions['gapMatchInteraction'])) {

                                            foreach ($interactions['gapMatchInteraction'] as $responseIdentifier => $jsonInteraction) {
                                                $headerResponseIdentifier = $component->getIdentifier() . '-' . $responseIdentifier;
                                                $this->matchTypeByDeliveries[$deliveryUri][$headerResponseIdentifier] = 1;
                                                
                                                $matchSets = AllBookletsExportUtils::getMatchSets($jsonInteraction, $responseIdentifier);
                                                $choices[$responseIdentifier] = $matchSets[0];
                                            }
                                        } elseif (isset($interactions['hottextInteraction'])) {
                                            foreach ($interactions['hottextInteraction'] as $responseIdentifier => $jsonInteraction) {
                                                $headerResponseIdentifier = $component->getIdentifier() . '-' . $responseIdentifier;
                                                $this->hottextTypeByDeliveries[$deliveryUri][$headerResponseIdentifier] = true;
                                                
                                                $matchSets = AllBookletsExportUtils::getMatchSets($jsonInteraction, $responseIdentifier);
                                                $choices[$responseIdentifier] = $matchSets[0];
                                            }
                                        } elseif (isset($interactions['choiceInteraction'])) {
                                            foreach ($interactions['choiceInteraction'] as $responseIdentifier => $jsonInteraction) {
                                                $headerResponseIdentifier = $component->getIdentifier() . '-' . $responseIdentifier;
                                                $matchSets = AllBookletsExportUtils::getMatchSets($jsonInteraction, $responseIdentifier);
                                                $this->choiceTypeByDeliveries[$deliveryUri][$headerResponseIdentifier] = $matchSets[0];
                                            }
                                        }

                                        break;
                                    }
                                }
                                
                                $identifier = $component->getIdentifier();
                                $outcomes = $component->getOutcomeDeclarations();
                                $responses = $component->getResponseDeclarations();
                                
                                /** @var ResponseDeclaration $response */
                                foreach ($responses as $response) {
                                    
                                    $headerResponseIdentifier = $identifier . '-' . $response->getIdentifier();
                                    
                                    if (isset($choices[$response->getIdentifier()])) {
                                        
                                        // Non default response variable structure (e.g. matchInteraction)
                                        foreach ($choices[$response->getIdentifier()] as $choiceId) {
                                            if (isset($this->matchSetsByDeliveries[$deliveryUri][$headerResponseIdentifier])) {
                                                $alpha = range('a', 'z');
                                                $choiceId = $alpha[AllBookletsExportUtils::matchSetIndex($choiceId, $this->matchSetsByDeliveries[$deliveryUri][$headerResponseIdentifier])];
                                            }
                                            
                                            $this->variablesByDeliveries[$delivery->getUri()][] = $headerResponseIdentifier . '-' . $choiceId;
                                        }
                                        
                                    } else {
                                        
                                        // Default response variable structure.
                                        $this->variablesByDeliveries[$delivery->getUri()][] = $headerResponseIdentifier;
                                    }
                                }

                                /** @var OutcomeDeclaration $outcome */
                                foreach ($outcomes as $outcome) {
                                    $this->variablesByDeliveries[$delivery->getUri()][] = $identifier . '-' . $outcome->getIdentifier();
                                }

                                foreach($this->expectedVariables as $expectedVariable){
                                    $this->variablesByDeliveries[$delivery->getUri()][] = $identifier . '-' . $expectedVariable;
                                }
                            }
                            break 2;
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

            $this->headers = $headers;
        }
        return $this->headers;

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
            $deliveryUri = $delivery->getUri();
            $storage = $this->getServiceManager()->get(ResultServerService::SERVICE_ID)->getResultStorage($delivery);

            if($storage instanceof ResultManagement){
                $results = $storage->getResultByDelivery([$deliveryUri]);
                // get each row and write it to the csv

                foreach($results as $result){
                    $execution = $execution = \taoDelivery_models_classes_execution_ServiceProxy::singleton()->getDeliveryExecution($result['deliveryResultIdentifier']);
                    $row = array();

                    $user = new \core_kernel_classes_Resource($execution->getUserIdentifier());
                    $row['ID'] = $user->getLabel();
                    $row['IDFORM'] = $delivery->getLabel();


                    $callIds = $storage->getRelatedItemCallIds($execution->getIdentifier());
                    foreach ($callIds as $callId) {
                        
                        $itemResults = $storage->getVariables($callId);
                        $splitCallId = explode('#', $callId);
                        $itemIdentifier = explode('.', $splitCallId[1]);
                        $itemIdentifier = $itemIdentifier[1];
                        
                        foreach ($itemResults as $itemResult) {
                            
                            $itemResult = array_pop($itemResult);
                            /** @var \taoResultServer_models_classes_Variable $variable */
                            $variable = $itemResult->variable;
                            $headerIdentifier = $itemIdentifier . '-' . $variable->getIdentifier();
                            
                            if (in_array($headerIdentifier, $this->variablesByDeliveries[$deliveryUri])){

                                $tmpVal = $this->formatVariable($variable);
                                if((isset($this->choiceTypeByDeliveries[$deliveryUri]) && in_array($headerIdentifier, array_keys($this->choiceTypeByDeliveries[$deliveryUri]))) &&
                                    ($index = array_search($tmpVal, $this->choiceTypeByDeliveries[$deliveryUri][$headerIdentifier])) !== false){
                                    $row[$headerIdentifier] = $index + 1;
                                } else {
                                    // Default response variable structure.
                                    $row[$headerIdentifier] = $tmpVal;
                                }


                            } elseif (in_array($headerIdentifier, array_keys($this->matchTypeByDeliveries[$deliveryUri]))) {
                                // Non default response variable structure (matchInteraction/gapMatchInteraction).
                                $this->setColumnToEmpty($row, $this->variablesByDeliveries[$deliveryUri], $headerIdentifier);
                                
                                $tmpVal = $this->formatVariable($variable);
                                $val = \taoQtiCommon_helpers_Utils::toQtiDatatype('multiple', 'pair', $tmpVal);
                                $alpha = range('a', 'z');
                                
                                if ($val) {
                                    foreach ($val as $v) {
                                        if (isset($this->matchTypeByDeliveries[$deliveryUri][$headerIdentifier]) && $this->matchTypeByDeliveries[$deliveryUri][$headerIdentifier] === 0) {
                                            // By column strategy.
                                            $cmp = $v->getFirst();
                                            $set = $v->getSecond();
                                        } else {
                                            // By row strategy.
                                            $cmp = $v->getSecond();
                                            $set = $v->getFirst();
                                        }
                                        
                                        if (isset($this->matchSetsByDeliveries[$deliveryUri][$headerIdentifier])) {
                                            $matchHeaderIdentifier = $headerIdentifier . '-' . $alpha[AllBookletsExportUtils::matchSetIndex($cmp, $this->matchSetsByDeliveries[$deliveryUri][$headerIdentifier])];
                                        } else {
                                            $matchHeaderIdentifier = $headerIdentifier . '-' . $cmp;
                                        }
                                        
                                        if (in_array($matchHeaderIdentifier, $this->variablesByDeliveries[$deliveryUri])) {
                                            $row[$matchHeaderIdentifier] = isset($this->matchSetsByDeliveries[$deliveryUri][$headerIdentifier]) ? (AllBookletsExportUtils::matchSetIndex($set, $this->matchSetsByDeliveries[$deliveryUri][$headerIdentifier]) + 1) : $set;
                                        }
                                    }
                                }
                            } elseif (in_array($headerIdentifier, array_keys($this->hottextTypeByDeliveries[$deliveryUri]))) {
                                // Non default response variable structure (hottextInteraction).
                                $this->setColumnToEmpty($row, $this->variablesByDeliveries[$deliveryUri], $headerIdentifier);
                                
                                $tmpVal = $this->formatVariable($variable);
                                $val = \taoQtiCommon_helpers_Utils::toQtiDatatype('multiple', 'identifier', $tmpVal);
                                
                                if ($val) {
                                    foreach ($val as $v) {
                                        $v = trim($v->getValue(), "'");
                                        if (in_array($headerIdentifier . '-' . $v, $this->variablesByDeliveries[$deliveryUri])) {
                                            $row[$headerIdentifier . '-' . $v] = '1';
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if (empty($row)) {
                        $report->setType(\common_report_Report::TYPE_ERROR);
                        $report->setMessage('Impossible to get row for theses delivery ' . $delivery . 'and execution ' . $execution->getIdentifier());
                        return $report;
                    }
                    
                    $i++;
                    $this->fillRow($row, $deliveryUri);
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
     * @param bool $isHeader
     */
    private function writeToCsv(array $row, $isHeader = false)
    {
        // order the row to match the headers
        if (!$isHeader) {
            $headers = $this->getHeaders();
            $newRow = [];
            
            foreach($headers as $value) {
                if (isset($row[$value])) {
                    $newRow[] = $row[$value];
                } else {
                    \common_Logger::w('column : ' . $value . ' is missing in row ' .implode(',', $row));
                }
            }
            
            $row = $newRow;
        }


        //write the row
        $content = self::CSV_ENCLOSURE
            . implode(self::CSV_ENCLOSURE . self::CSV_DELIMITER . self::CSV_ENCLOSURE, $row)
            . self::CSV_ENCLOSURE
            . "\n";

        if (!fwrite($this->exportFile, $content)) {
            \common_Logger::w('Fail to write in the csv : '.implode(',', $row));
        }
    }

    /**
     * Get the filename
     * @return string
     */
    public function getFilename()
    {
        return $this->flySystemFile->getPrefix().$this->flySystemFile->getBasename();
    }

    /**
     * Format the variable value to correspond to what invalsi want
     * @param \taoResultServer_models_classes_Variable $variable
     * @return string $variable
     */
    private function formatVariable($variable)
    {
        if($variable instanceof \taoResultServer_models_classes_ResponseVariable){
            if ($variable->getIdentifier() === 'duration') {
                $duration = AllBookletsExportUtils::formatDuration($variable->getValue());
                return ($duration === false) ? '' : $duration;
            } else {
                return $variable->getValue();
            }
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
        
        foreach ($this->variablesByDeliveries as $delivery => $columns) {
            if($delivery === $deliveryUri){
                // Columns that are related to $deliveryUri.
                $neededColumns = $this->variablesByDeliveries[$deliveryUri];
            } else {
                // Columns that are NOT related to $deliveryUri.
                $emptyColumns = array_merge($emptyColumns, $this->variablesByDeliveries[$delivery]);
            }
        }
        
        foreach ($neededColumns as $column) {
            if (!in_array($column, array_keys($row))) {
                $row[$column] = self::NOT_REACHED;
            } elseif ($column === 'item-25-Q07-a') {
                // When item-25-Q07-a is 1 ...
                if ($row[$column] === 1 && isset($row['item-26-Q08'])) {
                    // item-26-Q08 has to be invalidated.
                    $row['item-26-Q08'] = self::NOT_REACHED;
                }
            }
            
            if ($row[$column] === '' || $row[$column] === '[]' || $row[$column] === '<>') {
                $row[$column] = self::NOT_SELECTED;
            }
        }

        foreach ($emptyColumns as $column) {
            if(!in_array($column, array_keys($row))){
                $row[$column] = self::NOT_REQUIRED;
            }
        }
    }
    
    private function setColumnToEmpty(array &$row, array $columns, $headerIdentifier) {
        foreach ($columns as $col) {
            if (preg_match('/^' . preg_quote($headerIdentifier) . '-/', $col) === 1) {
                $row[$col] = '';
            }
        }
    }

    private function getServiceManager()
    {
        return ServiceManager::getServiceManager();
    }
}
