<?php
/**
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; under version 2
 *  of the License (non-upgradable).
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 *  Copyright (c) 2016 - 2019 (original work) Open Assessment Technologies S.A.
 */

namespace oat\taoInvalsi\scripts\tools;

use oat\tao\scripts\tools\AbstractIndexedCsv;
use \common_report_Report as Report;

/**
 * BQ2017 Resolve Partially Skipped Rows from Duplicate CSV file.
 * 
 * This script has to be applied on 
 * 
 * "oat\tao\scripts\tools\ExtractCsvDuplicates" - "oat\tao\scripts\tools\ResolveExclusivelySkippedFromBqCsvDuplicates"
 * 
 * * Finding students having at least one delivery taken with no attempt at all.
 * * For these stuends, extract the rows describing deliveries where at least one item has been attempted.
 * 
 * Script parameters:
 * 
 * * Parameter 1: The path to the source CSV file.
 * * Parameter 2: The path to the destination CSV file.
 * * Parameter 3: The column index of the column identifying students with a unique ID.
 * * Parameter 4: Whether not (0|1) the first line of the source CSV file contains column names.
 */
class ResolvePartiallySkippedFromBqCsvDuplicates extends AbstractIndexedCsv
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
