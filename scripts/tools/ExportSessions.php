<?php
/**
 * Copyright (c) 2018 Open Assessment Technologies, S.A.
 *
 * @author M.Kamarouski, <mikhail.kamarouski@1pt.com>
 */

namespace oat\taoInvalsi\scripts\tools;


use \common_report_Report as Report;
use oat\oatbox\extension\AbstractAction;
use oat\taoInvalsi\model\session\SessionExporter;


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