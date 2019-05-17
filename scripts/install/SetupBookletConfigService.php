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
/**
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */

namespace oat\taoInvalsi\scripts\install;

use oat\oatbox\extension\InstallAction;
use oat\oatbox\service\ServiceNotFoundException;
use oat\tao\helpers\Template;
use oat\taoBooklet\model\BookletClassService;
use oat\taoBooklet\model\BookletConfigService;

class SetupBookletConfigService extends InstallAction
{
    public function __invoke($params)
    {
        try {
            $bookletConfigService = $this->getServiceManager()->get(BookletConfigService::SERVICE_ID);
            $options = $bookletConfigService->getOptions();
        } catch( ServiceNotFoundException $e) {
            $bookletConfigService = new BookletConfigService();
            $options = [];
        }

        $bookletConfigService->setOptions(array_merge($options, [
            BookletConfigService::OPTION_DEFAULT_VALUES => [
                BookletClassService::PROPERTY_LAYOUT => [
                    BookletClassService::INSTANCE_LAYOUT_COVER,
                    BookletClassService::INSTANCE_LAYOUT_HEADER,
                    BookletClassService::INSTANCE_LAYOUT_FOOTER,
                    BookletClassService::INSTANCE_LAYOUT_ONE_PAGE_ITEM,
                ],

                BookletClassService::PROPERTY_COVER_PAGE => [
                    BookletClassService::INSTANCE_COVER_PAGE_TITLE,
                    BookletClassService::INSTANCE_COVER_PAGE_DESCRIPTION,
                    BookletClassService::INSTANCE_COVER_PAGE_DATE,
                    BookletClassService::INSTANCE_COVER_PAGE_LOGO,
                    BookletClassService::INSTANCE_COVER_PAGE_QRCODE,
                ],

                BookletClassService::PROPERTY_PAGE_HEADER => [
                    BookletClassService::INSTANCE_PAGE_LOGO,
                    BookletClassService::INSTANCE_PAGE_TITLE,
                    BookletClassService::INSTANCE_PAGE_DATE,
                ],

                BookletClassService::PROPERTY_PAGE_FOOTER => [
                    BookletClassService::INSTANCE_PAGE_MENTION,
                    BookletClassService::INSTANCE_PAGE_LINK,
                    BookletClassService::INSTANCE_PAGE_NUMBER,
                ],
            ],

            BookletConfigService::OPTION_MENTION => 'Built with TAO',

            BookletConfigService::OPTION_LINK => 'www.invalsi.it',

            BookletConfigService::OPTION_LOGO => Template::img('invalsi_logo_big.png', 'taoInvalsi'),
        ]));

        $this->registerService(BookletConfigService::SERVICE_ID, $bookletConfigService);
    }
}
