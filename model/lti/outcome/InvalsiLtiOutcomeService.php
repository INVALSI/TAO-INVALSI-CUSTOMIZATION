<?php

declare(strict_types=1);

namespace oat\taoInvalsi\model\lti\outcome;

use oat\ltiDeliveryProvider\model\LtiOutcomeService;
use oat\tao\model\taskQueue\QueueDispatcherInterface;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionState;
use oat\taoInvalsi\model\lti\outcome\task\InvalsiSendLtiOutcomeTask;
use oat\taoLti\models\classes\LtiService;
use oat\taoLti\models\classes\TaoLtiSession;

class InvalsiLtiOutcomeService extends LtiOutcomeService
{
    /**
     * @param DeliveryExecutionState $event
     * @throws \common_exception_Error
     * @throws \common_exception_NotFound
     * @throws \oat\taoLti\models\classes\LtiException
     * @throws \oat\taoLti\models\classes\LtiVariableMissingException
     */
    public function deferTransmit(DeliveryExecutionState $event)
    {
        if (
            DeliveryExecutionInterface::STATE_FINISHIED === $event->getState()
            && DeliveryExecutionInterface::STATE_FINISHIED !== $event->getPreviousState()
            && \common_session_SessionManager::getSession() instanceof TaoLtiSession
        ) {

            /** @var QueueDispatcherInterface $taskQueue */
            $taskQueue = $this->getServiceLocator()->get(QueueDispatcherInterface::SERVICE_ID);
            $launchData = LtiService::singleton()->getLtiSession()->getLaunchData();
            if ($launchData->hasVariable('lis_outcome_service_url')) {
                $params['deliveryResultIdentifier'] = $event->getDeliveryExecution()->getIdentifier();
                $params['consumerKey'] = $launchData->getOauthKey();
                $params['serviceUrl'] = $launchData->getVariable('lis_outcome_service_url');

                $taskQueue->createTask(new InvalsiSendLtiOutcomeTask(), $params, 'Submit LTI results');
            }
        }
    }
}