<?php

declare(strict_types=1);

namespace oat\taoInvalsi\model\render\PostProcessor;

class InvalsiTextReaderPostProcessor extends InvalsiFallbackInteractionPostProcessor
{
    public const INTERACTION_IDENTIFIER = 'textReaderInteraction';
    private const CONTENT_PREFIX = 'content-';
    private const PAGES_ELEMENT = 'pages';

    public function postProcess(array $element): array
    {
        foreach ($element['properties'] as $key => &$value) {
            if (strpos($key, self::CONTENT_PREFIX) === 0) {
                $value = $this->itemAssetsReplacement->postProcessAssets($value);
            } elseif ($key === self::PAGES_ELEMENT) {
                if (is_array($value)) {
                    foreach ($value as &$pageValue) {
                        $pageValue['content'] = $this->iterateOverItemJson($pageValue['content'], $element['properties']);
                    }
                } else {
                    $jsonDecodedValue = json_decode($value, true);
                    if (json_last_error() === 0) {
                        foreach ($jsonDecodedValue as &$pageValue) {
                            $pageValue['content'] = $this->iterateOverItemJson($pageValue['content'], $element['properties']);
                        }

                        $value = json_encode($jsonDecodedValue);
                    } else {
                        $value = $this->itemAssetsReplacement->postProcessAssets($value);
                    }
                }
            }
        }

        return $element;
    }
}