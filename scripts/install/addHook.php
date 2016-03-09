<?php
/**  
 * Copyright (c) 2015 (original work) Open Assessment Technologies;
 */

use oat\taoQtiItem\model\HookRegistry;

HookRegistry::getRegistry()->set('taoInvalsiCreator', 'oat\taoInvalsi\model\CreatorHook');
