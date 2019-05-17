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

use oat\tao\scripts\tools\AbstractIndexedCsv;
use \common_report_Report as Report;

/**
 * BQ2017 Resolve Exclusively Skipped Rows from Duplicate CSV file.
 * 
 * This script has to be applied on the output of "oat\tao\scripts\tools\ExtractCsvDuplicates". It aims at:
 * 
 * * Finding all rows describing deliveries where no attempt at all were made, by student. Only rows related 
 *   to students having skipped all the items for all their deliveries are retained.
 * 
 * * From theses rows, retaining a single row (the first one, as they are describing a similar behaviour).
 * 
 * Script parameters:
 * 
 * * Parameter 1: The path to the source CSV file.
 * * Parameter 2: The path to the destination CSV file.
 * * Parameter 3: The column index of the column identifying students with a unique ID.
 * * Parameter 4: Whether not (0|1) the first line of the source CSV file contains column names.
 */
class ResolveExclusivelySkippedFromBqCsvDuplicates extends AbstractIndexedCsv
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
                    // Student with ID = $key did not attempt any question, for all deliveries he took.
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
