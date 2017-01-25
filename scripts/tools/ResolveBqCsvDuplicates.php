<?php
/**
 * Copyright (c) 2017 Open Assessment Technologies, S.A.
 *
 * @author Jérôme Bogaerts, <jerome@taotesting.com>
 */

namespace oat\taoInvalsi\scripts\tools;

use oat\tao\scripts\tools\AbstractIndexedCsv;
use \common_report_Report as Report;

/**
 * BQ2017 CSV Duplicate Results Resolution.
 * 
 * Script parameters:
 * 
 * * Parameter 1: The path to the source CSV file.
 * * Parameter 2: The path to the destination CSV file.
 * * Parameter 3: The column index of the column identifying students with a unique ID.
 * * Parameter 4: Whether not (0|1) the first line of the source CSV file contains column names.
 */
class ResolveBqCsvDuplicates extends AbstractIndexedCsv
{
    private static $responseColumns = [
        'item-19-Q01' => 5,
        'item-20-Q02' => 9,
        'item-21-Q03' => 13,
        'item-22-Q04' => 17,
        'item-23-Q05' => 21,
        'item-24-Q06' => 25,
        'item-25-Q07-a' => 29,
        'item-25-Q07-b' => 30,
        'item-25-Q07-c' => 31,
        'item-26-Q08' => 35,
        'item-27-Q09' => 39,
        'item-28-Q10' => 43,
        'item-29-Q11' => 47,
        'item-30-Q12' => 51,
        'item-31-Q13-a' => 55,
        'item-31-Q13-b' => 56,
        'item-31-Q13-c' => 57,
        'item-31-Q13-d' => 58,
        'item-31-Q13-e' => 59,
        'item-31-Q13-f' => 60,
        'item-32-Q14' => 64,
        'item-33-Q15-a' => 68,
        'item-33-Q15-b' => 69,
        'item-34-Q16-a' => 73,
        'item-34-Q16-b' => 74
    ];
    
    private $caseResolved;
    private $writtenCount = 0;
    
    protected function setCaseResolved($rowResolved)
    {
        $this->caseResolved = $rowResolved;
    }
    
    protected function isCaseResolved()
    {
        return $this->caseResolved;
    }
    
    protected function setWrittenCount($writtenCount)
    {
        $this->writtenCount = $writtenCount;
    }
    
    protected function getWrittenCount()
    {
        return $this->writtenCount;
    }
    
    protected function incrementWrittenCount()
    {
        $this->writtenCount++;
    }
    
    protected function process()
    {
        /**
         * Case 1: The response data is composed by fully skipped [88] values only.
         * Case 2 - The response data is strictly identical amongst rows.
         * Case 3 - The response data is consistent but not strictly equivalent amongst rows. The row with the less [88/99] values is selected.
         * Case 4 - The response data is incconsistent amongst rows. The row with the less [88/99] values is selected, because it has $consistencyTreshold or more consistent responses amongst rows.
         */
        
        $sourceFp = $this->getSourceFp();
        $destinationFp = $this->getDestinationFp();
        $index = $this->getIndex();
        $writtenCount = 0;
        $potentialCount = 0;
        $expectedConsistencyCount = count(self::$responseColumns);
        $consistencyTreshold = 13;
        
        $report = new Report(
            Report::TYPE_SUCCESS,
            'Unknown'
        );
        
        foreach ($index as $key => $positions) {
            if (count($positions) > 1) {
                // Mark the current case as not resolved.
                $this->setCaseResolved(false);
                $mixedCase = false;
            
                $rows = [];
                
                // Read all rows related to $key.
                foreach ($positions as $pos) {
                    fseek($sourceFp, $pos);
                    $rows[] = fgetcsv($sourceFp);
                }
                
                $skipped = [];
                $notSkipped = [];
                $fullSkipped = \tao_helpers_Array::arraysContainOnlyValue($rows, '88', 0, [0, 1], $notSkipped, $skipped);
                
                if ($fullSkipped) {
                    // Full Skip Case.
                    $this->resolveRow(
                        $rows[0],
                        'Case 1',
                        $report
                    );
                } else {
                    // Consistency Resolution Case.
                    if (count($notSkipped) > 0 && count($skipped) > 0) {
                        $mixedCase = true;
                        // Partially Skipped Case
                        $newRows = [];
                        foreach ($notSkipped as $index) {
                            $newRows[] = $rows[$index];
                        }
                        
                        $rows = $newRows;
                    }
                    
                    if (count($rows) === 1) {
                        // No more doubts...
                        $this->resolveRow(
                            $rows[0],
                            'Case 2',
                            $report
                        );
                    } else {
                        
                        $studentResponsesRows = [];
                        foreach ($rows as $row) {
                           $studentRow = [];
                            
                            foreach (self::$responseColumns as $column) {
                                $studentRow[] = $row[$column];
                            }
                            
                            $studentResponsesRows[] = $studentRow;
                        }
                        
                        // Peferct consistency check.
                        $consistencyCount = \tao_helpers_Array::countConsistentColumns($studentResponsesRows);
                        
                        if ($consistencyCount === $expectedConsistencyCount) {
                            // Strict equality between response data between rows.
                            $this->resolveRow(
                                $rows[0],
                                'Case 3' . (($mixedCase) ? ' (fully skipped [88] related rows not taken into account)' : '' ),
                                $report
                            );
                        } else {
                            // Rows are not strictly equivalent.
                            $less88Or99 = \tao_helpers_Array::minArrayCountValues(['88', '99'], $studentResponsesRows, true);
                            
                            if (count($less88Or99) === 1) {
                                // The row with less 88/99 could be strictly determined.
                                $less88Or99 = $less88Or99[0];
                                $consistencyCount = \tao_helpers_Array::countConsistentColumns($studentResponsesRows, ['88', '99'], true);
                                
                                if ($consistencyCount !== false && $consistencyCount === $expectedConsistencyCount) {
                                    $this->resolveRow(
                                        $rows[$less88Or99],
                                        "Case 4 [consistency score = ${consistencyCount}]" . (($mixedCase) ? ' (fully skipped [88] related rows not taken into account)' : ''),
                                        $report
                                    );
                                } elseif ($consistencyCount >= $consistencyTreshold) {
                                    $this->resolveRow(
                                        $rows[$less88Or99],
                                        "Case 5 [consistency score = ${consistencyCount}]" . (($mixedCase) ? ' (fully skipped [88] related rows not taken into account)' : ''),
                                        $report
                                    );
                                }
                            }
                        }
                    }
                }
                
                // Is this case resolved?
                if (!$this->isCaseResolved()) {
                    $report->add(
                        new Report(
                            Report::TYPE_INFO,
                            $rows[0][0] . ': Case could not be resolved.'
                        )
                    );
                }
            }
        }
        
         $report->setMessage($this->getWrittenCount() . " cases resolved and written in file '" . realpath($this->getDestination()) . "'.");
         return $report;
    }
    
    private function resolveRow(array $row, $info, Report $report)
    {
        $this->setCaseResolved(true);
        fputcsv($this->getDestinationFp(), $row);
        $this->incrementWrittenCount();
        $report->add(
            new Report(
                Report::TYPE_SUCCESS,
                $row[0] . ': ' . $info
            )
        );
    }
}
