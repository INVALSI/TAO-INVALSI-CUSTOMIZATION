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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA
 *
 */

namespace oat\taoInvalsi\scripts\tools;

use oat\generis\model\OntologyAwareTrait;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\taoResultExports\model\export\AllBookletsExport;
use oat\taoResultExports\scripts\tools\GenerateCsvFile as BasicGenerateCsvFile;
/**
 * Class GenerateCsvFile
 * @package oat\taoResultExports\scripts\tools
 *
 * usage : sudo -u www-data php index.php 'oat\taoInvalsi\scripts\tools\GenerateCsvFile'
 */
class GenerateCsvFile extends BasicGenerateCsvFile
{
    use OntologyAwareTrait;

    protected function provideDescription()
    {
        return 'TAO Invalsi - generate Csv';
    }

    public function run()
    {
        $delivery = $this->getOption('delivery');

        if (is_null($delivery) || strtolower($delivery) === 'all'){
            $deliveries = DeliveryAssemblyService::singleton()->getRootClass()->getInstances(true);
        } else {
            $deliveries = [];
            $explodedDeliveries = explode(',', $delivery);
            foreach($explodedDeliveries as $delivery){
                  $deliveries[] = $this->getResource($delivery);
            }
        }
        
        // Param 2: Identifier strategy.
        $identifierStrategy = $this->getOption('strategy');
        
        // Param 3: Variable policy.
        $variablePolicy = $this->getOption('policy');

        $variablePolicy = self::transformStringVariablePolicyIntoConstant(strtolower($variablePolicy));


        $prefix = ($this->hasOption('prefix'))?$this->getOption('prefix'):'export';

        /** @var AllBookletsExport $bookletExporter */
        $bookletExporter = $this->getServiceLocator()->get(AllBookletsExport::SERVICE_ID);
        $bookletExporter->setDeliveries($deliveries);
        $bookletExporter->setIdentifierStrategy($identifierStrategy);
        $bookletExporter->setAllowTimestampInFilename($this->isTimestampNeeded());
        $bookletExporter->setPrefix($prefix);
        $bookletExporter->setVariablePolicy($variablePolicy);
        
        // Param 4: Variable blacklist.
        $variableBlacklist = $this->getOption('blacklist');
        if(!is_null($variableBlacklist)){
            $bookletExporter->setVariableBlacklist(explode(',', $variableBlacklist));
        }

        // Param 5: Raw mode.
        if ($this->hasOption('raw')) {
            $bookletExporter->addAlternateMissingDataEnconding($bookletExporter->getOption(AllBookletsExport::NOT_RESPONDED_OPTION), '');
        }

        if ($this->getOption('exotic')){
            $bookletExporter->setAllowExoticCharactersExport($this->getOption('exotic'));
        }

        // Param 6: split export by day
        if (!$this->hasOption('daily')) {
            $exportReport = $bookletExporter->export();
        } else {
            $exportReport = $bookletExporter->dailyExport();
        }

        $report = new \common_report_Report(
            \common_report_Report::TYPE_INFO,
            'Exporting deliveries : '. PHP_EOL . implode(PHP_EOL, array_map(function($d) {
                return $d->getUri() . ' => ' . $d->getLabel();
            }, $deliveries))
        );
        
        $report->add($exportReport);

        return $report;
    }

    protected function formatTime($s)
    {
        $h = floor($s / 3600);
        $m = floor(($s / 60) % 60);
        $s = $s % 60;

        return "${h}h ${m}m ${s}s";
    }

    private static function transformStringVariablePolicyIntoConstant($strVariablePolicy)
    {
        $variablePolicy = AllBookletsExport::VARIABLE_POLICY_ALL;
        
        switch ($strVariablePolicy) {
            case 'outcome':
                $variablePolicy = AllBookletsExport::VARIABLE_POLICY_OUTCOME;
                break;
                
            case 'response':
                $variablePolicy = AllBookletsExport::VARIABLE_POLICY_RESPONSE;
                break;
        }
        
        return $variablePolicy;
    }

    protected function showTime()
    {
        return true;
    }

    private function isTimestampNeeded()
    {
        $withoutTimestamp = $this->getOption('withoutTimestamp', false);

        return !(bool)$withoutTimestamp;
    }
}
