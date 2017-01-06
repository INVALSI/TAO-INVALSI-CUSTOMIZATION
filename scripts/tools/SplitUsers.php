<?php
/**
 * Copyright (c) 2016 Open Assessment Technologies, S.A.
 *
 * @author Jérôme Bogaerts, <jerome@taotesting.com>
 */

namespace oat\taoInvalsi\scripts\tools;

use oat\oatbox\action\Action;

class SplitUsers implements Action
{
    public function __invoke($params)
    {
        $source = $params[0];
        $dest = $params[1];
        $loginColumn = intval($params[2]);
        $passwordColumn = intval($params[3]);
        $groupColumn = intval($params[4]);
        
        $filePointers = [];
        $mainFp = fopen($source, 'r');
        
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
                    $password
                ]
            );
        }
        
        foreach ($filePointers as $fp) {
            fclose($fp);
        }
        
        fclose($mainFp);
        
        return new \common_report_Report(
            \common_report_Report::TYPE_INFO,
            "User split done!"
        );
    }
}
