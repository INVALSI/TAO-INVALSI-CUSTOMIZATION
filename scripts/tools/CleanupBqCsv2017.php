<?php
/**
 * Copyright (c) 2017 Open Assessment Technologies, S.A.
 *
 * @author Jérôme Bogaerts, <jerome@taotesting.com>
 */

namespace oat\taoInvalsi\scripts\tools;

use oat\oatbox\action\Action;
use \common_report_Report as Report;

class CleanupBqCsv2017 implements Action
{
    public function __invoke($params)
    {
        $source = $params[0];
        $consistencyThreshold = intval($params[1]);
        $verbose = boolval($params[2]);
        $filePrefix = $params[3];
        
        $realSource = realpath($source);
        $pathInfo = pathinfo($realSource);
        $basePath = $pathInfo['dirname'];
        
        $report = new Report(
            Report::TYPE_INFO,
            'BQ 2017 CSV Clean up Script Report'
        );
        
        $scripts = [
            'branch'    => new \oat\taoInvalsi\scripts\tools\ResolveBranchRuleFromBqCsv(),
            'clean'   => new \oat\tao\scripts\tools\RemoveCsvRowsByPattern(),
            'dupes'   => new \oat\tao\scripts\tools\ExtractCsvDuplicates(),
            'exclude'   => new \oat\tao\scripts\tools\CsvExclusion(),
            'resolve' => new \oat\taoInvalsi\scripts\tools\ResolveBqCsvDuplicates(),
            'merge' => new \oat\tao\scripts\tools\MergeCsv()
        ];
        
        $report->add(
            $scripts['branch']([
                $realSource,
                $basePath . "/${filePrefix}raw-aggregate-br.csv",
                0,
                1
            ])
        );
        
        $report->add(
            $scripts['clean']([
                $basePath . "/${filePrefix}raw-aggregate-br.csv",
                $basePath . "/${filePrefix}raw-aggregate-noqa.csv",
                0,
                "/^Test-taker|^OAT_QA/u",
                1
            ])
        );
        
        $report->add(
            $scripts['dupes']([
                $basePath . "/${filePrefix}raw-aggregate-noqa.csv",
                $basePath . "/${filePrefix}duplicates.csv",
                0,
                1
            ])
        );
        
        $report->add(
            $scripts['exclude']([
                $basePath . "/${filePrefix}raw-aggregate-noqa.csv",
                $basePath . "/${filePrefix}raw-aggregate-noqa-noduplicates.csv",
                0,
                1,
                $basePath . "/${filePrefix}duplicates.csv"
            ])
        );
        
        $report->add(
            $scripts['resolve']([
                $basePath . "/${filePrefix}duplicates.csv",
                $basePath . "/${filePrefix}resolved-duplicates.csv",
                0,
                1,
                $consistencyThreshold,
                $verbose
            ])
        );
        
        $report->add(
            $scripts['exclude']([
                $basePath . "/${filePrefix}duplicates.csv",
                $basePath . "/${filePrefix}remaining-duplicates.csv",
                0,
                1,
                $basePath . "/${filePrefix}resolved-duplicates.csv"
            ])
        );
        
        $report->add(
            $scripts['merge']([
                $basePath . "/${filePrefix}raw-aggregate-noqa-noduplicates.csv",
                $basePath . "/${filePrefix}resolved-duplicates.csv",
                $basePath . "/${filePrefix}raw-aggregate-resolved.csv",
                1
            ])
        );
        
        return $report;
    }
}
