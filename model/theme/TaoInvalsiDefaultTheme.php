<?php
/**  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
 *               
 * 
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
