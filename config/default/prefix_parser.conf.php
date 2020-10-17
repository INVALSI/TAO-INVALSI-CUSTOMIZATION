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

use oat\taoInvalsi\model\export\PrefixParser;

return new PrefixParser(
    [
        PrefixParser::OPTION_DETECTION_REGEXP => '/.*(\d\d)_([\w\s]+)_(\w\w\w{3,4})(\d\d).*/',
        PrefixParser::OPTION_GRADE_RULE => 'G%5$s',
        PrefixParser::OPTION_SUBJECT_RULE => '%4$s',
        PrefixParser::OPTION_CAMPAIGN_RULE => '%3$s',
        PrefixParser::OPTION_ACTIVITY_RULE => '%2$s',
    ]
);
