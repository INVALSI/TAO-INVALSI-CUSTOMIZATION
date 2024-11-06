<?php

declare(strict_types=1);

namespace oat\taoInvalsi\model\render\ServiceProvider;

use oat\taoInvalsi\model\render\PostProcessor\InvalsiFallbackInteractionPostProcessor;
use oat\taoInvalsi\model\render\PostProcessor\InvalsiTextReaderPostProcessor;
use oat\taoItems\model\render\ItemAssetsReplacement;
use oat\taoQtiTest\models\classes\render\CustomInteraction\CustomInteractionPostProcessorAllocator;
use oat\taoQtiTest\models\classes\render\CustomInteraction\PostProcessor\TextReaderPostProcessor;
use oat\taoQtiTest\models\classes\render\CustomInteraction\ServiceProvider\CustomInteractionPostProcessingServiceProvider;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class InvalsiCustomInteractionPostProcessingServiceProvider extends CustomInteractionPostProcessingServiceProvider
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

//        $services->remove(CustomInteractionPostProcessingServiceProvider::class);

        $services
            ->set(InvalsiTextReaderPostProcessor::class, InvalsiTextReaderPostProcessor::class)
            ->public()
            ->args([
                service(ItemAssetsReplacement::SERVICE_ID)
            ]);


        $services
            ->set(InvalsiFallbackInteractionPostProcessor::class, InvalsiFallbackInteractionPostProcessor::class)
            ->public()
            ->args([
                service(ItemAssetsReplacement::SERVICE_ID)
            ]);

        $services
            ->set(CustomInteractionPostProcessorAllocator::class, CustomInteractionPostProcessorAllocator::class)
            ->public()
            ->args([
                [
                    TextReaderPostProcessor::INTERACTION_IDENTIFIER => service(InvalsiTextReaderPostProcessor::class),
                    InvalsiFallbackInteractionPostProcessor::INTERACTION_IDENTIFIER => service(InvalsiFallbackInteractionPostProcessor::class)
                ]
            ]);
    }
}