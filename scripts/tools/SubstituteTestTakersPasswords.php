<?php
/**
 * Copyright (c) 2016 Open Assessment Technologies, S.A.
 *
 * @author A.Zagovorichev, <zagovorichev@1pt.com>
 */


namespace oat\taoInvalsi\scripts\tools;


use oat\oatbox\action\Action;
use oat\tao\model\TaoOntology;

class SubstituteTestTakersPasswords implements Action
{
    /** @var  \common_report_Report */
    private $report;
    
    /**
     * @var bool
     */
    private $dryrun = false;
    
    public function __invoke($params)
    {
        if (empty($params)) {
            return new \common_report_Report(\common_report_Report::TYPE_ERROR, 'USAGE: ' . __CLASS__ . ' PASSWORDS_VERSION [dryrun]');
        }

        $version = array_shift($params);

        $sp = DIRECTORY_SEPARATOR;
        $path = __DIR__ . $sp . 'data' . $sp . 'SubstituteTestTakersPasswords' . $sp . 'v' . $version . '.csv';

        if (!file_exists($path)) {
            return new \common_report_Report(\common_report_Report::TYPE_ERROR, 'File ' . $path .' do not exists');
        }

        if (!is_readable($path)) {
            return new \common_report_Report(\common_report_Report::TYPE_ERROR, 'File ' . $path .' is not readable');
        }

        $this->dryrun = in_array('dryrun', $params) || in_array('--dryrun', $params);

        $this->report = new \common_report_Report(\common_report_Report::TYPE_INFO, 'Start substitute test takers passwords');

        $csv_data = new \tao_helpers_data_CsvFile([]);
        $csv_data->load($path);

        $data = $csv_data->getData();
        if (!$data || !count($data)) {
            return new \common_report_Report(\common_report_Report::TYPE_ERROR, 'File ' . $path .' without data');
        }

        $userService = \tao_models_classes_UserService::singleton();
        $userClass = new \core_kernel_classes_Class(TaoOntology::SUBJECT_CLASS_URI);
        
        foreach ($data as $tt) {
            if (!$userService->loginExists($tt[0])) {
                $this->report->add(new \common_report_Report(\common_report_Report::TYPE_WARNING, 'User not found ' . $tt[0]));
            } else {
                $user = $userClass->searchInstances([
                    PROPERTY_USER_LOGIN => $tt[0]
                ]);
                
                if ($user) {
                    $user = current($user);
                }
                
                if (!$user || !$userService->userHasRoles($user, TaoOntology::PROPERTY_INSTANCE_ROLE_DELIVERY)) {
                    $this->report->add(new \common_report_Report(\common_report_Report::TYPE_WARNING, 'User ' . $tt[0] . ' not a test taker'));
                } else {
                
                    if ($this->dryrun) {
                        $this->report->add(new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'Would changed password for ' . $tt[0]));
                    } else {
                        $userService->setPassword($user, $tt[1]);
                        $this->report->add(new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'Password changed for ' . $tt[0]));
                    }
                }
            }
        }
        
        return $this->report;
    }
}
