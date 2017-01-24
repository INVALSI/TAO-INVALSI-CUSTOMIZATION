<?php
/**
 * Copyright (c) 2017 Open Assessment Technologies, S.A.
 *
 * @author Jérôme Bogaerts, <jerome@taotesting.com>
 */

namespace oat\taoInvalsi\scripts\tools;

use oat\tao\scripts\tools\AbstractIndexedCsv;
use \common_report_Report as Report;

class ExtractExclusivelySkippedFromBqCsvDuplicates extends AbstractIndexedCsv
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
                
                if (\tao_helpers_Array::arraysContainOnlyValue($rows, '88', 0, [0, 1])) {
                    fputcsv($destinationFp, $rows[0]);
                    $writtenCount++;
                }
            }
        }
        
        return new Report(
            Report::TYPE_SUCCESS,
            "${writtenCount} record(s) written in file '" . realpath($this->getDestination()) . "'."
        );
    }
}
