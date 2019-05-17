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

use oat\oatbox\action\Action;
use \common_report_Report as Report;

/**
 * German BQ 2017 Remove Extra Columns Script.
 * 
 * This script aims at removing unnecessary columns from the
 * German BQ 2017 Results Exports (i.e. duration, numAttempts, SCORE).
 * 
 * Script parameters:
 * 
 * * Parameter 1: The path to the source CSV file.
 * * Parameter 2: The path to the destination CSV file.
 */
class RemoveExtraColumnsFromGermanBq2017Csv implements Action
{
    public function __invoke($params)
    {
        // -- Deal with parameters.
        // Files
        if (!empty($params[0])) {
            $source = $params[0];
        } else {
            return new Report(
                Report::TYPE_ERROR,
                "'Source' parameter not provided."
            );
        }
        
        if (!empty($params[1])) {
           $destination = $params[1];
        } else {
            return new Report(
                Report::TYPE_ERROR,
                "'Destination' parameter not provided."
            );
        }
        
        // -- Deal with file handling.
        $sourceFp = @fopen($source, 'r');
        $destinationFp = @fopen($destination, 'w');
        
        if ($sourceFp === false) {
            return new Report(
                Report::TYPE_ERROR,
                "Source file '" . $source . "' could not be open."
            );
        }
        
        if ($destinationFp === false) {
            return new Report(
                Report::TYPE_ERROR,
                "Destination file '" . $destination . "' could not be open."
            );
        }
        
        $extraColumns = [0, 1, 2, 3, 7, 11, 15, 19, 23, 27, 31, 32, 33, 37, 41, 45, 49, 53, 57, 58, 59, 60, 61, 62, 66, 70, 71, 75, 76];
        
        $writtenCount = 0;
        while ($sourceData = @fgetcsv($sourceFp)) {
            $columnsToDelete = array_diff(array_keys($sourceData), $extraColumns);
            foreach ($columnsToDelete as $toDelete) {
                unset($sourceData[$toDelete]);
            }
            
            fputcsv($destinationFp, $sourceData);
            $writtenCount++;
        }
        
        @fclose($sourceFp);
        @fclose($destinationFp);
        
        return new Report(
            Report::TYPE_SUCCESS,
            "${writtenCount} lines processed and written in file '" . $destination . "' in order to remove extra columns."
        );
    }
}
