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


namespace oat\taoInvalsi\model\session;


use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class SessionExporter implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    private $login;

    public function __construct($login = [])
    {
        $this->login = $login;
    }

    public function export($file = '')
    {
        /** @var Capture $service */

        $tmpPath = tempnam(sys_get_temp_dir(), 'capture_'.uniqid());
        $temp = fopen($tmpPath, 'w+');

        $service = $this->getServiceLocator()->get(Capture::SERVICE_ID);
        fputcsv($temp,['login','action','time']);
        foreach ($this->login as $login) {
            $userData = $service->getUserActions($login);
            foreach ($userData as $action){
                fputcsv($temp, array_merge([$login],$action));
            }
        }

        if (is_file($file)) {
            throw  new \common_Exception('File already exists ' . $file);
        }

        if (($resource = fopen($file, 'w+')) === false) {
            throw  new \common_Exception('Impossible to write to the file' . $file);
        }

        fseek($temp,0);

        $sizeCopied = stream_copy_to_stream($temp, $resource);
        fclose($resource);
        fclose($temp);
        unlink($tmpPath);

        return $sizeCopied;
    }
}