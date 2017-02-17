<?php
/**
 * This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; under version 2
 *  of the License (non-upgradable).
 *  
 * This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 * 
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 *  Copyright (c) 2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */
namespace oat\taoInvalsi\controller;

use oat\taoInvalsi\model\export\AllBookletsExport;

class Export extends \tao_actions_CommonModule
{
    
    public function exportBooklet() {

        $deliveryUri = $this->getRequestParameter('uri');
        $delivery = new \core_kernel_classes_Resource(\tao_helpers_Uri::decode($deliveryUri));
        $bookletExporter = new AllBookletsExport([$delivery]);
        $bookletExporter->export();

        $file = $bookletExporter->getFile();
        if(is_null($file)){
            throw new \common_Exception('Unable to find file for this export');
        }

        $tmpFile = \tao_helpers_Export::getExportPath() . DIRECTORY_SEPARATOR . $file->getBasename();
        if (($resource = fopen($tmpFile, 'w')) === false) {
            throw new \common_Exception('Unable to write "' . $file->getPrefix() . '" into tmp folder("' . $tmpFile . '").');
        }
        stream_copy_to_stream($file->readStream(), $resource);
        fclose($resource);

        $filename = $this->hasRequestParameter('filename') ? $this->getRequestParameter('filename') . '.csv' : '';
        \tao_helpers_Export::outputFile($file->getBasename(), $filename);

        return;
    } 
    
}