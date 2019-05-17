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


// Item themes
use oat\tao\model\ThemeRegistry;
use Jig\Utils\StringUtils;

// Platform themes
use oat\taoInvalsi\model\theme\TaoInvalsiDefaultTheme;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\theme\ThemeService;

// Item themes
// 'camelCaseId' => 'Name of the theme'
$itemThemes = array(
    'themeInvalsi' => 'Theme Invalsi'
);

// override if required
$defaultTheme = 'themeInvalsi';

foreach($itemThemes as $themeName){
    $pathFragment = StringUtils::removeSpecChars($themeName);
    $themeId = StringUtils::camelize($pathFragment);
    ThemeRegistry::getRegistry()->registerTheme(
        $themeId,
        $themeName,
        implode(DIRECTORY_SEPARATOR, array('taoInvalsi', 'views', 'css', 'themes', 'items', $pathFragment, 'theme.css')), array('items')
    );
}

ThemeRegistry::getRegistry()->setDefaultTheme('items', $defaultTheme);

// TAO theme would usually be removed from the stack if custom themes are used
// Make sure another theme has been set to default in this case.
if($defaultTheme !== 'tao') {    
    try {
    ThemeRegistry::getRegistry()->unregisterTheme('tao');
    } catch(ThemeNotFoundException $e) {
        // theme was already deleted, nothing to worry about
    }
}


// Platform themes
$serviceManager = ServiceManager::getServiceManager();
$themeService = $serviceManager->get(ThemeService::SERVICE_ID);

$themeService->setTheme(new TaoInvalsiDefaultTheme());
$serviceManager->register(ThemeService::SERVICE_ID, $themeService);
