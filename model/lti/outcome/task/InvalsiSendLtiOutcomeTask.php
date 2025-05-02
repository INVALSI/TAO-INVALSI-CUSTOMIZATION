<?php

declare(strict_types=1);

namespace oat\taoInvalsi\model\lti\outcome\task;

use oat\ltiDeliveryProvider\model\execution\implementation\Lti1p3ContextCacheRepository;
use oat\ltiDeliveryProvider\model\tasks\SendLtiOutcomeTask;
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\taoLti\models\classes\LtiOutcome\LtiOutcomeXmlFactory;
use oat\taoLti\models\classes\LtiService;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoResultServer\models\classes\ResultAliasServiceInterface;
use \taoResultServer_models_classes_OutcomeVariable;
use common_report_Report;

class InvalsiSendLtiOutcomeTask extends SendLtiOutcomeTask
{
    private const SOURCEDID_VARIABLE = 'lis_result_sourcedid';

    public function __invoke($params)
    {
        $report = new common_report_Report(common_report_Report::TYPE_ERROR);
        $deliveryResultIdentifier = $params['deliveryResultIdentifier'];
        $consumerKey = $params['consumerKey'];
        $serviceUrl = $params['serviceUrl'];

        try {
            $deliveryExecution = ServiceProxy::singleton()->getDeliveryExecution($deliveryResultIdentifier);
            $resultsService = ResultsService::singleton();
            $implementation = $resultsService->getReadableImplementation($deliveryExecution->getDelivery());
            $resultsService->setImplementation($implementation);

            $variables = $resultsService->getVariableDataFromDeliveryResult(
                $deliveryResultIdentifier,
                [taoResultServer_models_classes_OutcomeVariable::class]
            );

            $submitted = 0;
            /** @var taoResultServer_models_classes_OutcomeVariable $variable */
            foreach ($variables as $variable) {
                if (self::VARIABLE_IDENTIFIER == $variable->getIdentifier()) {
                    $this->sendLtiOutcome($variable, $deliveryResultIdentifier, $consumerKey, $serviceUrl);
                    $submitted++;
                    break;
                }
            }
            if (0 === $submitted) {
                throw new \common_Exception(
                    'No LTI Outcome has been submitter for execution' . $deliveryResultIdentifier
                );
            }
        } catch (\Exception $exception) {
            $report->setMessage($exception->getMessage());
        }

        $report->setType(common_report_Report::TYPE_SUCCESS);
        return $report;
    }

    /**
     * @param taoResultServer_models_classes_OutcomeVariable $testVariable
     * @param $deliveryResultIdentifier
     * @param $consumerKey
     * @param $serviceUrl
     * @return bool
     * @throws \common_exception_Error
     * @throws \oat\taoLti\models\classes\LtiException
     */
    private function sendLtiOutcome(
        taoResultServer_models_classes_OutcomeVariable $testVariable,
                                                       $deliveryResultIdentifier,
                                                       $consumerKey,
                                                       $serviceUrl
    ): bool
    {
        $grade = (string)$testVariable->getValue();

        /** @var ResultAliasServiceInterface $resultAliasService */
        $resultAliasService = $this->getServiceLocator()->get(ResultAliasServiceInterface::SERVICE_ID);
        $deliveryResultAlias = $resultAliasService->getResultAlias($deliveryResultIdentifier);

        $this->logWarning('[SendLtiOutcomeTask] result alias: ' . current($deliveryResultAlias));

        if (empty($deliveryResultAlias) || (int)$deliveryResultAlias === 0) {
            $deliveryResultAlias = $this->getResultAliasFromLti1p3Context($deliveryResultIdentifier);

            $this->logWarning('[SendLtiOutcomeTask] result alias: ' . current($deliveryResultAlias));
        }

        $deliveryResultIdentifier = empty($deliveryResultAlias)
            ? $deliveryResultIdentifier
            : current($deliveryResultAlias);

        $this->logWarning('[SendLtiOutcomeTask] result alias: ' . $deliveryResultIdentifier);

        $message = $this->getLtiOutcomeXmlFactory()->buildReplaceResultRequest(
            $deliveryResultIdentifier,
            $grade,
            uniqid('', true)
        );

        $credentialResource = LtiService::singleton()->getCredential($consumerKey);
        $credentials = new \tao_models_classes_oauth_Credentials($credentialResource);
        //Building POX raw http message
        $unSignedOutComeRequest = new \common_http_Request($serviceUrl, 'POST', []);
        $unSignedOutComeRequest->setBody($message);
        $signingService = new \tao_models_classes_oauth_Service();
        $signedRequest = $signingService->sign($unSignedOutComeRequest, $credentials, true);
        //Hack for moodle compatibility, the header is ignored for the signature computation
        $signedRequest->setHeader("Content-Type", "application/xml");

        $response = $signedRequest->send(true);

        if ('200' != $response->httpCode) {
            $this->logWarning("Request sent (Body)\n" . $signedRequest->getBody() . "\n");
            $this->logWarning("Request sent (Headers)\n" . serialize($signedRequest->getHeaders()) . "\n");
            $this->logWarning("\nHTTP Code received: " . $response->httpCode . "\n");

            $this->logWarning("\nHTTP From: " . $response->effectiveUrl . "\n");
            $this->logWarning("\nHTTP Content received: " . $response->responseData . "\n");

            throw new \common_exception_Error(
                'An HTTP level problem occurred when sending the outcome to the service url'
            );
        } else {
            $this->logInfo('Submited LTI score with id "' . $deliveryResultIdentifier . '"');
        }
        return true;
    }

    private function getResultAliasFromLti1p3Context(string $deliveryExecutionUri)
    {
        $sourcedId = null;

        /** @var Lti1p3ContextCacheRepository $lti1p3ContextService */
        $lti1p3ContextService = $this->getServiceManager()->get(Lti1p3ContextCacheRepository::class);

        $contextData = $lti1p3ContextService->findByDeliveryExecutionId($deliveryExecutionUri);

        if (!is_null($contextData)) {
            $variables = $contextData->getVariables();
            if (!empty($variables)) {
                $this->logWarning('[DEBUG] launchData variables: ' . json_encode($variables));
                if ($contextData->hasVariable(self::SOURCEDID_VARIABLE)) {
                    $sourcedId = $variables[self::SOURCEDID_VARIABLE];
                    $this->logWarning('[DEBUG] launchData decoded data sourcedId: ' . $sourcedId);
                }
            }
        }

        return [$sourcedId];
    }

    private function getLtiOutcomeXmlFactory(): LtiOutcomeXmlFactory
    {
        return $this->getServiceLocator()->get(LtiOutcomeXmlFactory::class);
    }
}