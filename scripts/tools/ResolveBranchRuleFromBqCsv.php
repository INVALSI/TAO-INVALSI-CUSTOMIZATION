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
 * BQ2017 Resolve Branch Rule Invalidation Script.
 * 
 * This script aims at resolving the branch rule invalidation from BQ2017
 * in case it is not applied on the export file.
 * 
 * The script will scan all rows of the source file, and check the value
 * of columns "item-25-Q07-a" and "item-26-Q08". In case of "item-25-Q07-a"
 * has a value of 1 and "item-26-Q08" has value which is different from 88,
 * "item-26-Q08" becomes invalidated with the 88 value.
 * 
 * Script parameters:
 * 
 * * Parameter 1: The path to the source CSV file.
 * * Parameter 2: The path to the destination CSV file.
 * * Parameter 3: Whether or not (integer 0|1) the first row of the file contains column names.
 */
class ResolveBranchRuleFromBqCsv implements Action
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
        
        // Flags
        $withHeader = false;
        
        if (isset($params[2])) {
            $withHeader = boolval($params[2]);
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
        
        if ($withHeader) {
            // Consume file header.
            fputcsv($destinationFp, fgetcsv($sourceFp));
        }
        
        $invalidationCount = 0;
        $writtenCount = 0;
        while ($sourceData = @fgetcsv($sourceFp)) {
            // When column "item-25-Q07-a" is 1 AND column "item-26-Q08" != 88... 
            if ($sourceData[29] === '1' && $sourceData[35] !== '88') {
                // Invalidate "item-26-Q08"!
                $sourceData[35] = '88';
                $invalidationCount++;
            }
            
            fputcsv($destinationFp, $sourceData);
            $writtenCount++;
        }
        
        @fclose($sourceFp);
        @fclose($destinationFp);
        
        return new Report(
            Report::TYPE_SUCCESS,
            "${writtenCount} record(s) written in file '" . $destination . "'. ${invalidationCount} branch rule invalidation(s) occured."
        );
    }
}
