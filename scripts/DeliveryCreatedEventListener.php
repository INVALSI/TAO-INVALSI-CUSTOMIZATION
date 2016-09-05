<?php

namespace oat\taoInvalsi\scripts;

use oat\taoDeliveryRdf\model\event\DeliveryCreatedEvent;

class DeliveryCreatedEventListener
{
    public static function copyMissingPciAssets(DeliveryCreatedEvent $event)
    {
        \common_Logger::w(' HELLO WORLD FROM EVENT LISTENER !!! ');
    }
}