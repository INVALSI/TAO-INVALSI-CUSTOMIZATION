<?php

declare(strict_types=1);

namespace oat\taoInvalsi\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\ltiDeliveryProvider\model\LtiOutcomeService;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoInvalsi\model\lti\outcome\InvalsiLtiOutcomeService;

class Version202504201442089594_taoInvalsi extends AbstractMigration
{
    private const MATHENTRY_PCI_NAME = 'mathEntryInteraction';
    private const TEXTREADER_PCI_NAME = 'textReaderInteraction';

    public function getDescription(): string
    {
        return 'Change implementation of LtiOutcomeService to Invalsi one, adding possibility to fetch missing data from Lti1p3 context.';
    }

    public function up(Schema $schema): void
    {
        $this->getServiceLocator()->unregister(LtiOutcomeService::SERVICE_ID);
        $this->getServiceLocator()->register(LtiOutcomeService::SERVICE_ID, new InvalsiLtiOutcomeService());
    }

    public function down(Schema $schema): void
    {
        $this->getServiceLocator()->unregister(LtiOutcomeService::SERVICE_ID);
        $this->getServiceLocator()->register(LtiOutcomeService::SERVICE_ID, new LtiOutcomeService());
    }
}