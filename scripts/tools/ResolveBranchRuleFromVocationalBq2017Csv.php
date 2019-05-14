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

use oat\oatbox\action\Action;
use \common_report_Report as Report;

/**
 * BQ2017 (Vocational version) Resolve Branch Rule Invalidation Script.
 * 
 * This script aims at resolving the branch rule invalidation from BQ2017
 * in case it is not applied on the export file.
 * 
 * The script will scan all rows of the source file, and check the value
 * of columns "Q07-Q07-a" and "Q08-Q08". In case of "Q08-Q08"
 * has a value of 1 and "Q08-Q08" has value which is different from 88,
 * "Q08-Q08" becomes invalidated with the 88 value.
 * 
 * Script parameters:
 * 
 * * Parameter 1: The path to the source CSV file.
 * * Parameter 2: The path to the destination CSV file.
 * * Parameter 3: Whether or not (integer 0|1) the first row of the file contains column names.
 */
class ResolveBranchRuleFromVocationalBq2017Csv implements Action
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
            // When column "Q07-Q07-a" is 1.
            if ($sourceData[10] === '1' && $sourceData[13] !== '88') {
                // Invalidate "Q08-Q08"!
                $sourceData[13] = '88';
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
