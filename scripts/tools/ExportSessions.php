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


use \common_report_Report as Report;
use oat\oatbox\extension\AbstractAction;
use oat\taoInvalsi\model\session\SessionExporter;

/**
 *
 * Accepts only two required params
 *   --path file location
 *   --login must contain semicolon separated list of users expected to be included to output
 * $ sudo -u www-data php index.php 'oat\taoInvalsi\scripts\tools\ExportSessions' --path='<path to the file>' --login='<user1;user2;user3>'
 *
 *
 */
class ExportSessions extends AbstractAction
{
    private $path;
    private $login = [];

    public function __invoke($params)
    {
        $report = Report::createInfo('Running command...');

        try {
            $this->checkParams($params);
            $exporter = new SessionExporter($this->login);
            $this->propagate($exporter);
            $result = $exporter->export($this->path);
            $report->add(Report::createSuccess('Proceed  '.$result  .' bytes'));

        } catch (\Exception $exception) {
            return Report::createFailure($exception->getMessage());
        }

        return $report;
    }


    private function checkParams(array $params)
    {
        foreach ($params as $param) {
            list($option, $value) = explode('=', $param);

            switch ($option) {
                case '--path':
                    $this->path = $value;
                    break;

                case '--login':
                    $this->login = explode(';', $value);
                    break;


            }
        }

        if (empty($this->path) || empty($this->login)) {
            throw new \InvalidArgumentException('Missing required params');
        }
    }

}