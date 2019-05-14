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


use oat\generis\model\GenerisRdf;
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
                    GenerisRdf::PROPERTY_USER_LOGIN => $tt[0]
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
