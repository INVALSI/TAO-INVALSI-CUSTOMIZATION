<?php
/**
 * Copyright (c) 2017 Open Assessment Technologies, S.A.
 *
 * @author Jérôme Bogaerts, <jerome@taotesting.com>
 */

namespace oat\taoInvalsi\scripts\tools;

use oat\tao\scripts\tools\AbstractIndexedCsv;
use \common_report_Report as Report;

class ExtractPartiallySkippedFromBqCsvDuplicates extends AbstractIndexedCsv
{
    protected function process()
    {
        $sourceFp = $this->getSourceFp();
        $destinationFp = $this->getDestinationFp();
        $index = $this->getIndex();
        $writtenCount = 0;
        
        foreach ($index as $key => $positions) {
            if (count($positions) > 1) {
                $rows = [];
                
                // Read all rows related to $key.
                foreach ($positions as $pos) {
                    fseek($sourceFp, $pos);
                    $rows[] = fgetcsv($sourceFp);
                }
                
                $skipped = [];
                $notSkipped = [];
                \tao_helpers_Array::arraysContainOnlyValue($rows, '88', 0, [0, 1], $notSkipped, $skipped);
                
                if (count($notSkipped) > 0 && count($skipped) > 0) {
                    // Student with ID = $key 
                    // 1. did not attempt any question on at least one of the deliveries he took.
                    // 2. did attempt some questions on at least one of the deliveries he took.
                    foreach ($notSkipped as $notSkippedRowIndex) {
                        fputcsv($destinationFp, $rows[$notSkippedRowIndex]);
                        $writtenCount++;
                    }
                }
            }
        }
        
        return new Report(
            Report::TYPE_SUCCESS,
            "${writtenCount} record(s) written in file '" . realpath($this->getDestination()) . "'."
        );
    }
}
