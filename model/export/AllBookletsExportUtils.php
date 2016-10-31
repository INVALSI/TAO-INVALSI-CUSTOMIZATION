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
 * Copyright (c) 2016  (original work) Open Assessment Technologies SA;
 *
 * @author Antoine Robin <antoine@taotesting.com>
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 */

namespace oat\taoInvalsi\model\export;

class AllBookletsExportUtils
{
    static public function getInteractions(\stdClass $json)
    {
        $interactions = array(
            'choiceInteraction',
            'matchInteraction',
            'orderInteraction'
        );
        
        $result = array();

        $stack = new \SplStack();
        $stack->push($json);
        
        while (count($stack) > 0) {
            
            $current = $stack->pop();
            
            if (isset($current->qtiClass) && isset($current->attributes) && isset($current->attributes->responseIdentifier) && in_array($current->qtiClass, $interactions)) {
                
                if (!isset($result[$current->qtiClass])) {
                    $result[$current->qtiClass] = array();
                }
                
                $result[$current->qtiClass][$current->attributes->responseIdentifier] = $current;
            }
            
            if ($current instanceof \stdClass) {
                foreach ($current as $val) {
                    $stack->push($val);
                }
            }
        }
        
        return $result;
    }
    
    static public function getMatchSets(\stdClass $jsonInteraction, $responseIdentifier)
    {
        $result = [];
        
        if (isset($jsonInteraction->qtiClass) && $jsonInteraction->qtiClass === 'matchInteraction') {
            for ($i = 0; $i <= 1; $i++) {
                $choices = [];
                foreach ($jsonInteraction->choices[$i] as $choice) {
                    $choices[] = $choice->identifier;
                }
                $result[] = $choices;
            }
        }
        
        return $result;
    }
    
    static public function isMatchByColumn(\stdClass $jsonInteraction)
    {
        $result = false;

        if (isset($jsonInteraction->qtiClass) && $jsonInteraction->qtiClass === 'matchInteraction') {
            
            foreach ($jsonInteraction->choices[0] as $choice) {
                if ($choice->attributes->matchMax != 1) {
                    break;
                }
                
                $result = true;
            }
        }
        
        return $result;
    }
    
    static public function isMatchByRow(\stdClass $jsonInteraction)
    {
        $result = false;
        
        if (isset($jsonInteraction->qtiClass) && $jsonInteraction->qtiClass === 'matchInteraction') {
            foreach ($jsonInteraction->choices[1] as $choice) {
                if ($choice->attributes->matchMax != 1) {
                    break;
                }
                
                $result = true;
            }
        }
        
        return $result;
    }
}
