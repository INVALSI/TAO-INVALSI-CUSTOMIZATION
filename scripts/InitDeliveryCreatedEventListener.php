<?php

namespace oat\taoInvalsi\scripts;

use oat\oatbox\action\Action;
use oat\oatbox\event\EventManager;
use oat\oatbox\event\EventManagerAwareTrait;
use oat\oatbox\service\ServiceManager;
use oat\taoDeliveryRdf\model\event\DeliveryCreatedEvent;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class InitDeliveryCreatedEventListener implements Action, ServiceLocatorAwareInterface
{
    use EventManagerAwareTrait;
    use ServiceLocatorAwareTrait;

    private function getServiceManager() {
        return ServiceManager::getServiceManager();
    }

    public function __invoke($params)
    {
        $this->getEventManager()->attach(DeliveryCreatedEvent::class, [DeliveryCreatedEventListener::class, 'copyMissingPciAssets']);
        $this->getServiceManager()->register(EventManager::CONFIG_ID, $this->getEventManager());

        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS);
    }
}