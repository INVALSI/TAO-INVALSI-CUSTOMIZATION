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
namespace oat\taoInvalsi\controller;

use oat\taoInvalsi\model\export\AllBookletsExport;

class Export extends \tao_actions_CommonModule
{
    
    public function exportBooklet() {

        if(!$this->hasRequestParameter('uri')){
            throw new \common_exception_MissingParameter('uri');
        }
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