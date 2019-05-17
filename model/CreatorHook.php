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

namespace oat\taoInvalsi\model;

use oat\taoQtiItem\model\Hook;
use oat\taoQtiItem\model\Config;

/**
 * The hook used in the item creator
 */
class CreatorHook implements Hook
{
    /**
     * Affect the config
     * 
     * @param \oat\taoQtiItem\model\Config $config
     */
    public function init(Config $config)
    {
        $config->addHook('taoInvalsi/cssBlocks/hook');
    }
}
