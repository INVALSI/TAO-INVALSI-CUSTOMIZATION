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
 *  Copyright (c) 2018 (original work) Open Assessment Technologies S.A.
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