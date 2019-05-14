<?php
/**
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; under version 2
 *  of the License (non-upgradable).
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 *  Copyright (c) 2019 (original work) Open Assessment Technologies S.A.
 */

namespace oat\taoInvalsi\model\qtiTestCategory;


use oat\taoQtiTest\models\TestCategoryPreset;
use oat\taoQtiTest\models\TestCategoryPresetProvider;
use oat\taoQtiTest\models\TestCategoryPresetProviderInterface;

class InvalsiCategoryPresetProvider extends TestCategoryPresetProvider implements TestCategoryPresetProviderInterface
{
    const GROUP_FORMULA = 'formula';

    protected function getPresetGroups()
    {
        $existentGroups = parent::getPresetGroups();

        return array_merge_recursive($existentGroups, [
            self::GROUP_FORMULA => [
                'groupId'    => self::GROUP_FORMULA,
                'groupLabel' => __('Formula'),
                'groupOrder' => 400,
                'presets'    => []
            ]
        ]);
    }

    public function registerPresets(TestCategoryPresetProvider $presetService)
    {
        $extManager = $presetService->getServiceLocator()->get(\common_ext_ExtensionsManager::SERVICE_ID);
        $testRunnerConfig = $extManager->getExtensionById('taoQtiTest')->getConfig('testRunner');

        $pluginsConfig = $testRunnerConfig['plugins'];
        $formulaConfig = isset($pluginsConfig['formula']) ? $pluginsConfig['formula'] : [];

        $categories = [];
        $weights = [];
        foreach ($formulaConfig as $key => $path) {
            $id = str_replace('documentUrl', 'formula', $key);

            $grade = preg_replace('/([A-Za-z_])/', '', $id);

            $hasLanguage = strpos($id, '_');
            if (false !== $hasLanguage && substr($id, $hasLanguage + 1, 3) === 'GER') {
                $language = 'German';
            } elseif (false !== $hasLanguage && substr($id, $hasLanguage + 1, 3) === 'SLO') {
                $language = 'Slovenian';
            } else {
                $language = false;
            }

            $labelTranslated = $hasLanguage ? __('Grade %s (%s)', $grade, $language) : __('Grade %s', $grade);

            $formulaArray = [
                'id' => $id,
                'label' => $labelTranslated,
                'qtiCategory' => 'x-tao-option-' . $id,
                'description' => __('Apply "%s" category to the test items', $id),
                'order' => 0,
                'pluginId' => 'formula'
            ];

            $categories[$id] = $formulaArray;
            $weight = (int)$grade;
            while (false !== array_search($weight, $weights)) {
                $weight++;
            }
            $weights[$id] = $weight;
        }

        $weights = array_flip($weights);
        sort($weights);
        $weights = array_flip($weights);

        foreach ($categories as $key => $category) {
            $weight = $weights[$key];
            $category['order'] = 300 + ($weight * 10);

            $categories[$key] = TestCategoryPreset::fromArray($category);
        }

        if (!empty($categories)) {
            $presetService->register(
                self::GROUP_FORMULA,
                $categories
            );
        }
    }
}