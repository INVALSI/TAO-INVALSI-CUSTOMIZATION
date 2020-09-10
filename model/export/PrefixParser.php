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

declare(strict_types=1);

namespace oat\taoInvalsi\model\export;

use oat\oatbox\service\ConfigurableService;

class PrefixParser extends ConfigurableService
{
    public const SERVICE_ID = 'taoInvalsi/prefix_parser';

    public const OPTION_DETECTION_REGEXP = 'detection_regexp';
    public const OPTION_GRADE_RULE = 'grade_rule';
    public const OPTION_SUBJECT_RULE = 'subject_rule';
    public const OPTION_CAMPAIGN_RULE = 'campaign_rule';
    public const OPTION_ACTIVITY_RULE = 'activity_rule';

    /**
     * @param string $prefix
     *
     * @return string
     */
    public function getActivity(string $prefix): string
    {
        preg_match($this->getOption(self::OPTION_DETECTION_REGEXP), $prefix, $matches);

        return vsprintf($this->getOption(self::OPTION_ACTIVITY_RULE), $matches);
    }

    /**
     * @param string $prefix
     *
     * @return string
     */
    public function getCampaign(string $prefix): string
    {
        preg_match($this->getOption(self::OPTION_DETECTION_REGEXP), $prefix, $matches);

        return vsprintf($this->getOption(self::OPTION_CAMPAIGN_RULE), $matches);
    }

    /**
     * @param $prefix
     * @return string
     */
    public function getGrade(string $prefix): string
    {
        preg_match($this->getOption(self::OPTION_DETECTION_REGEXP), $prefix, $matches);

        return vsprintf($this->getOption(self::OPTION_GRADE_RULE), $matches);
    }

    /**
     * @param $prefix
     * @return mixed
     */
    public function getSubject(string $prefix): string
    {
        preg_match($this->getOption(self::OPTION_DETECTION_REGEXP), $prefix, $matches);

        return vsprintf($this->getOption(self::OPTION_SUBJECT_RULE), $matches);
    }
}
