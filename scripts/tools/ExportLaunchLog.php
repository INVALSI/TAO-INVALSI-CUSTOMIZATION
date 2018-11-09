<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\taoInvalsi\scripts\tools;

use common_report_Report as Report;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\extension\script\ScriptAction;
use oat\tao\model\export\implementation\CsvExporter;
use oat\taoInvalsi\model\launchLog\LaunchLog;

/**
 * Class ExportLaunchLog
 *
 * launch example:
 *
 * `sudo php index.php '\oat\taoInvalsi\scripts\tools\ExportLaunchLog' -f 1541747400 -d './launch_log.csv'`
 *
 * @package oat\taoInvalsi\scripts\tools
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class ExportLaunchLog extends ScriptAction
{
    use OntologyAwareTrait;

    /**
     * @return string
     */
    protected function provideDescription()
    {
        return __('Export launch log to csv');
    }

    /**
     * @return array
     */
    protected function provideOptions()
    {
        return [
            'destination' => [
                'prefix' => 'd',
                'longPrefix' => 'destination',
                'cast' => 'string',
                'required' => true,
                'description' => 'Destination file path'
            ],
            'from' => [
                'prefix' => 'f',
                'longPrefix' => 'from',
                'defaultValue' => 0,
                'cast' => 'double',
                'required' => false,
                'description' => 'Start time - from (timestamp)'
            ],
            'to' => [
                'prefix' => 't',
                'longPrefix' => 'from',
                'defaultValue' => PHP_INT_MAX,
                'cast' => 'double',
                'required' => false,
                'description' => 'Start time - to (timestamp)'
            ],
            'chunk_size' => [
                'prefix' => 'c',
                'longPrefix' => 'chunk_size',
                'defaultValue' => 1000,
                'cast' => 'integer',
                'required' => false,
                'description' => 'chunk size'
            ],
        ];
    }

    /**
     * @return array
     */
    protected function provideUsage()
    {
        return [
            'prefix' => 'h',
            'longPrefix' => 'help',
            'description' => 'Prints a help statement'
        ];
    }

    /**
     * Export launch log to csv
     * @return Report|void
     * @throws \common_exception_InvalidArgumentType
     */
    public function run()
    {
        $from = $this->getOption('from');
        $to = $this->getOption('to');
        $chunkSize = (int) $this->getOption('chunk_size');
        $pathInfo = pathinfo($this->getOption('destination'));
        $destination = realpath($pathInfo['dirname']) . DIRECTORY_SEPARATOR . $pathInfo['basename'];
        /** @var LaunchLog $logService */
        $logService = $this->getServiceLocator()->get(LaunchLog::class);

        $limit = $chunkSize;
        $offset = 0;
        $total = 0;
        file_put_contents($destination, '');

        do {
            $launches = $logService->search(['from' => $from, 'to' => $to], ['limit' => $limit, 'offset' => $offset]);
            $exporter = new CsvExporter($this->prepareData($launches));
            $csv = $exporter->export($total === 0);
            file_put_contents($destination, $csv . PHP_EOL, FILE_APPEND);
            $offset += $limit;
            $total += count($launches);
        } while (!empty($launches));

        if ($total === 0) {
            return new Report(Report::TYPE_WARNING, __('No entries found'));
        }

        return new Report(Report::TYPE_SUCCESS, __('%d entries have been exported. File path: %s', $total, $destination));
    }

    /**
     * @param array launches
     * @return array
     */
    private function prepareData($launches)
    {
        $data = [];
        $cache = [];
        foreach ($launches as $launch) {
            if (!isset($cache['tt'][$launch[LaunchLog::TEST_TAKER_ID]])) {
                $tt = $this->getResource($launch[LaunchLog::TEST_TAKER_ID]);
                $cache['tt'][$launch[LaunchLog::TEST_TAKER_ID]] = $tt->getLabel();
            }
            if (!isset($cache['delivery'][$launch[LaunchLog::TEST_TAKER_ID]])) {
                $delivery = $this->getResource($launch[LaunchLog::DELIVERY_ID]);
                $cache['delivery'][$launch[LaunchLog::DELIVERY_ID]] = $delivery->getLabel();
            }
            $data[] = [
                'test_taker' => $cache['tt'][$launch[LaunchLog::TEST_TAKER_ID]],
                'delivery_label' => $cache['delivery'][$launch[LaunchLog::DELIVERY_ID]],
                'OS' => $launch[LaunchLog::OS],
                'OS_version' => $launch[LaunchLog::OS_VERSION],
                'browser' => $launch[LaunchLog::BROWSER],
                'browser_version' => $launch[LaunchLog::BROWSER_VERSION],
            ];
        }
        return $data;
    }
}
