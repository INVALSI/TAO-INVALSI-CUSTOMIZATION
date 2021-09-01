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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoInvalsi\model\translation;

/**
 * Class ManualTranslations
 *
 * File to handle manually created translations
 *
 * @package oat\taoInvalsi\models\translation
 */
class ManualTranslations
{
    public function getManualTranslations()
    {
        return [
            __('You are about to leave this section.'),
            __('You are about to submit the test.'),
            __('You are about to submit this test part.'),
            __('You answered %s of %s question(s) for this section of the test'),
            __('and flagged %s of them'),
            __('There are %s unanswered questions'),
            __('There is %s unanswered question'),
            __('There are %s unanswered questions in this part of the test'),
            __('There is %s unanswered question in this part of the test'),
            __('and you flagged %s item(s) that you can review now'),
            __('You flagged %s item(s) that you can review now'),
            __('You will not be able to access this test once submitted. Click \"%s\" to continue and submit the test.'),
            __('You will not be able to return to this test after you submit your answers.'),
            __('The time limit has been reached for this part of the test.'),
            __('SUBMIT THIS PART'),
            __('SUBMIT THE TEST'),
            __('CANCEL')
        ];
    }
}
