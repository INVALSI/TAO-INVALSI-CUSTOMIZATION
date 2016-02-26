<?php
/**  
 * Copyright (c) 2015 (original work) Open Assessment Technologies;
 */

namespace oat\taoInvalsi\model;

use oat\taoQtiItem\model\Hook;
use oat\taoQtiItem\model\Config;
use oat\taoMp\model\CreatorRegistry;

/**
 * The hook used in the item creator
 *
 * @package taoMp
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

        $registry = new CreatorRegistry();

        //get info controls directly located in views/js/picCreator/dev/myInfoControl
        $hooks = $registry->getDevImplementations();
        foreach($hooks as $hook){
            $config->addInteraction($hook);
        }

        $config->addHook('taoInvalsi/cssBlocks/hook');
    }
}
