<?php

declare(strict_types=1);

namespace oat\taoInvalsi\model\render\PostProcessor;

use oat\taoItems\model\render\ItemAssetsReplacement;
use oat\taoItems\model\render\NoneItemReplacement;
use oat\taoQtiTest\models\classes\render\CustomInteraction\PostProcessor\Api\CustomInteractionPostProcessorInterface;

class InvalsiFallbackInteractionPostProcessor implements CustomInteractionPostProcessorInterface
{
    public const INTERACTION_IDENTIFIER = 'null';

    protected const CUSTOM_PATTERN = '#\b(__SLUG__\/__pva\S+)"#';

    /** @var ItemAssetsReplacement */
    protected $itemAssetsReplacement;

    public function __construct(ItemAssetsReplacement $itemAssetsReplacement)
    {
        $this->itemAssetsReplacement = $itemAssetsReplacement;
    }

    public function postProcess(array $element): array
    {
        if (! ($this->itemAssetsReplacement instanceof NoneItemReplacement) ) {
            $element = $this->iterateOverItemJson($element);
        }

        return $element;
    }

    protected function iterateOverItemJson(array $jsonArray, array $properties = []): array
    {
        $url = ROOT_URL;
        $parsedUrl = parse_url($url);
        $rootUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];

        foreach ($jsonArray as $key => $elem) {
            if (is_array($elem)) {
                if ($key === 'assets') {
                    continue;
                }
                $elemA = $this->iterateOverItemJson($elem);
                $jsonArray[$key] = $elemA;
            } else {
                $matchesValidator = [];

                if (is_string($elem)) {
                    $srcPropertyValueRegex = '/src\s*=\s*"(.+?)"/';
                    preg_match_all($srcPropertyValueRegex, $elem, $matchesValidator);
                }

                if (is_string($elem) && false !== strpos($elem, $rootUrl)) {
                    if (0 === strpos($elem, 'https') && false === strpos($elem, 'Signature=')) {
                        $elemS = $this->itemAssetsReplacement->postProcessAssets($elem);
                        $jsonArray[$key] = $elemS;
                    } else {
                        $elemStripped = $elem;
                        $pattern = str_replace('__SLUG__', $rootUrl, self::CUSTOM_PATTERN);
                        preg_match_all($pattern, $elemStripped, $matches);
                        foreach ($matches[1] as $match) {
                            if (false !== strpos($match, $rootUrl) && false === strpos($elem, 'Signature=')) {
                                $elemStripped = str_replace($match, $this->itemAssetsReplacement->postProcessAssets($match), $elemStripped);
                            }
                        }
                        $jsonArray[$key] = $elemStripped;
                    }
                } elseif (!empty($matchesValidator) && !empty($matchesValidator[1]) && !empty($properties)) {
                    foreach ($properties as $propKey => $propVal) {
                        if (0 === strpos($propKey, 'content-')) {
                            $imgName = str_replace('content-', '', $propKey);
                            $imgName = str_replace('assets/', '', $imgName);
                            if (false !== strpos($elem, $imgName)) {
                                foreach ($matchesValidator[1] as $possibleMatch) {
                                    if (false !== strpos($possibleMatch, $imgName)) {
                                        $imgName = $possibleMatch;
                                    }
                                }

                                $jsonArray[$key] = str_replace($imgName, $propVal, $jsonArray[$key]);
                            }
                        }
                    }
                }
            }
        }
        return $jsonArray;
    }
}