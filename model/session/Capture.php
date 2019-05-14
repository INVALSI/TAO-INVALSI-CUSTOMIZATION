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

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\event\LoginSucceedEvent;
use oat\tao\model\event\LogoutSucceedEvent;

class Capture extends ConfigurableService
{
    const SERVICE_ID = 'taoInvalsi/Capture';

    const OPTION_PERSISTENCE = 'persistence';
    const OPTION_LIMIT = 'limit';


    const DELIM_LOGIN = 'login';
    const DELIM_LOGOUT = 'logout';
    const DELIM_GENERIC = '|';
    const DELIM_ACTION = ';';

    /** @var \common_persistence_AdvKeyValuePersistence */
    private $persistence;

    /**
     * @param LoginSucceedEvent $event
     * @throws \common_Exception
     */
    public function storeLogin(LoginSucceedEvent $event)
    {
        $current = $this->getPersistence()->get($this->getKeyName($event->getLogin())); //@todo introduce compatibility with 'append' redis command

        $this->getPersistence()->set($this->getKeyName($event->getLogin()), ($current ?: '') . self::DELIM_LOGIN . self::DELIM_ACTION . $event->getTime() . self::DELIM_GENERIC);
    }

    /**
     * @param LogoutSucceedEvent $event
     * @throws \common_Exception
     */
    public function storeLogout(LogoutSucceedEvent $event)
    {
        $current = $this->getPersistence()->get($this->getKeyName($event->getLogin()));
        $this->getPersistence()->set($this->getKeyName($event->getLogin()), ($current ?: '') . self::DELIM_LOGOUT . self::DELIM_ACTION . $event->getTime() . self::DELIM_GENERIC);
    }

    /**
     * @return \common_persistence_AdvKeyValuePersistence
     */
    protected function getPersistence()
    {
        if (is_null($this->persistence)) {
            $pm = $this->getServiceLocator()->get(\common_persistence_Manager::SERVICE_ID);
            $this->persistence = $pm->getPersistenceById($this->getOption(self::OPTION_PERSISTENCE));
        }
        return $this->persistence;
    }

    /**
     * @param string $login
     * @return array
     */
    public function getUserActions($login)
    {
        $data = $this->getPersistence()->get($this->getKeyName($login));
        $result = array_map(function ($r) {
            return explode(self::DELIM_ACTION, $r);
        }, explode(self::DELIM_GENERIC, $data));

        $result = array_filter(array_map('array_filter', $result));
        return $result;
    }


    private function getKeyName($login)
    {
        return 'capture' . $login;
    }


}