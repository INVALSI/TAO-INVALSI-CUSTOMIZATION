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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA
 *
 */

namespace oat\taoInvalsi\model\runner\session;

use oat\taoQtiTest\models\runner\session\TestSession;

/**
 * TestSession override
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
class InvalsiTestSession extends TestSession
{

    /**
     * Force the timeout for all constraints with allowLateSubmission to true.
     *
     * @see {TestSession::getTimeConstraint}
     */
     protected function getTimeConstraint($source, $navigationMode, $considerMinTime, $applyExtraTime = true)
    {
        $constraint = parent::getTimeConstraint($source, $this->getTimerDuration($source->getIdentifier()), $navigationMode, $considerMinTime, $applyExtraTime);
        if(!is_null($constraint) && !is_null($constraint->getSource()) && !is_null($constraint->getSource()->getTimeLimits())){
            $constraint->getSource()->getTimeLimits()->setAllowLateSubmission(false);
        }
        return $constraint;
     }

}
