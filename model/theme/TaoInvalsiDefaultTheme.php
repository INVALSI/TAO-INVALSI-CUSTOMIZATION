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

namespace oat\taoInvalsi\model\theme;

use oat\tao\helpers\Template;
use oat\tao\model\theme\DefaultTheme;
use oat\tao\model\theme\Theme;

/**
 * Class TaoInvalsiDefaultTheme
 *
 * @package oat\taoInvalsi\model\theme
 */
class TaoInvalsiDefaultTheme extends DefaultTheme
{

    /**
     * Id of this extension
     */
    const EXTENSION_ID = 'taoInvalsi';


    /**
     * Theme's label
     */
    const THEME_LABEL = 'TAO Invalsi default theme';


    /**
     * Theme's id, by default label without spec chars
     */
    const THEME_ID = 'taoInvalsiDefaultTheme';


    /**
     * Constructor.
     */
    public function __construct()
    {
    }


    /**
     * @return string
     */
    public function getId()
    {
        return self::THEME_ID;
    }


    /**
     * @return string
     */
    public function getLabel()
    {
        return __(self::THEME_LABEL);
    }


    /**
     * @param string $id
     * @param string $context
     * @return string
     */
    public function getTemplate($id, $context = Theme::CONTEXT_BACKOFFICE)
    {
        switch ($id) {
            case 'header-logo' :
                $template = Template::getTemplate(
                    'themes/platform/' . self::THEME_ID . '/header-logo.tpl',
                    self::EXTENSION_ID
                );
                break;

            case 'footer' :
                $template = Template::getTemplate(
                    'themes/platform/' . self::THEME_ID . '/footer.tpl',
                    self::EXTENSION_ID
                );
                break;

            case 'login':
                $template = Template::getTemplate(
                    'themes/platform/' . self::THEME_ID . '/login.tpl',
                    self::EXTENSION_ID
                );
                break;

            case 'gdpr' :
                $template = Template::getTemplate(
                    'themes/platform/' . self::THEME_ID . '/gdpr.tpl',
                    self::EXTENSION_ID
                );
                break;

            default:
                $template = parent::getTemplate($id);
        }
        return $template;
    }


    /**
     * @param string $context
     * @return string
     */
    public function getStylesheet($context = Theme::CONTEXT_BACKOFFICE)
    {
        return Template::css('themes/platform/' . self::THEME_ID . '/theme.css', self::EXTENSION_ID);
    }
}
