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

/**
 * An INVALSI Specific CSV Users Splitter
 * 
 * As the INVALSI customer provides their groups/users as a single large CSV file, this script
 * aims at splitting users by group in specific CSV files.
 * 
 * The source CSV file must be a 3 column file with no headers.
 * 
 * * Column X is the login
 * * Column y is the password
 * * Column Z is the group identifier
 * 
 * The destination parameter of this script must be a directory where the CSV files will be written,
 * using the group identifier as a file name.
 * 
 * Parameters are all required and are the following:
 * 
 * * Parameter 1 - Source - The path to the source CSV file.
 * * Parameter 2 - Destination - The destination directory where to write the resulting CSV files.
 * * Parameter 3 - Login Column Index - The index (starting by 0) of the column where to find login values.
 * * Parameter 4 - Password Column Index - The index (starting by 0) of the column where to find password values.
 * * Parameter 5 - Group Identifier Column Index - The index (starting by 0) of the column where to find group identifier values.
 * * Parameter 6 - Language - The language code to be used.
 * 
 * The destination user CSV files will contain 3 columns appearing in the following order with no headers:
 * 
 * * Column 1 - Login
 * * Column 2 - Password
 * * Column 3 - Language Code
 */
class SplitUsers implements Action
{
    public function __invoke($params)
    {
        if (empty($params[0]) === true) {
            return new \common_report_Report(
                \common_report_Report::TYPE_ERROR,
                "Parameter 1 'Source' must be provided."
            );
        } elseif (is_readable($params[0]) === false) {
            return new \common_report_Report(
                \common_report_Report::TYPE_ERROR,
                "Parameter 1 'Source' leads to a file that cannot be read."
            );
        } 
        
        $source = $params[0];
        
        if (empty($params[1]) === true) {
            return new \common_report_Report(
                \common_report_Report::TYPE_ERROR,
                "Parameter 2 'Destination' must be provided."
            );
        } elseif (is_writable($params[1]) === false) {
            return new \common_report_Report(
                \common_report_Report::TYPE_ERROR,
                "Parameter 2 'Destination' leads to a directory that cannot be written."
            );
        }
        
        $dest = $params[1];
        
        if (isset($params[2]) === false) {
            return new \common_report_Report(
                \common_report_Report::TYPE_ERROR,
                "Parameter 3 'Login Column Index' must be provided."
            );
        }
        
        $loginColumn = intval($params[2]);
        
        if (isset($params[3]) === false) {
            return new \common_report_Report(
                \common_report_Report::TYPE_ERROR,
                "Parameter 4 'Password Column Index' must be provided."
            );
        }
        
        $passwordColumn = intval($params[3]);
        
        if (isset($params[4]) === false) {
            return new \common_report_Report(
                \common_report_Report::TYPE_ERROR,
                "Parameter 5 'Group Column Index' must be provided."
            );
        }
        
        $groupColumn = intval($params[4]);
        
        if (empty($params[5]) === true) {
            return new \common_report_Report(
                \common_report_Report::TYPE_ERROR,
                "Parameter 6 'Language' must be provided."
            );
        }
        
        $language = $params[5];
        
        $filePointers = [];
        $mainFp = fopen($source, 'r');
        $writtenUsers = 0;
        
        // Write all users in files to separate them by group identifier.
        while (($data = fgetcsv($mainFp, '', ',')) !== false) {
            $login = $data[$loginColumn];
            $password = $data[$passwordColumn];
            $group = $data[$groupColumn];
            
            $identifiedGroups = array_keys($filePointers);
            if (!in_array($group, $identifiedGroups)) {
                $filePointers[$group] = fopen("${dest}/${group}.csv", 'w');
            }
            
            fputcsv(
                $filePointers[$group],
                [
                    $login,
                    $password,
                    $language
                ]
            );
            
            $writtenUsers++;
        }
        
        // Close all files if possible.
        foreach ($filePointers as $fp) {
            @fclose($fp);
        }
        
        @fclose($mainFp);
        
        return new \common_report_Report(
            \common_report_Report::TYPE_SUCCESS,
            "${writtenUsers} users written amongst " . count($filePointers) . " files."
        );
    }
}
