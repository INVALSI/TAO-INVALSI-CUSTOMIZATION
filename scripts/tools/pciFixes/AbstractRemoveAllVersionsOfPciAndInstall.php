<?php

declare(strict_types=1);

namespace oat\taoInvalsi\scripts\tools\pciFixes;

use oat\oatbox\extension\script\ScriptAction;
use oat\taoQtiItem\model\portableElement\PortableElementService;
use oat\oatbox\reporting\Report;
use oat\qtiItemPci\model\IMSPciModel;
use oat\qtiItemPci\model\PciModel;
use oat\taoQtiItem\model\portableElement\exception\PortableElementInvalidModelException;
use oat\taoQtiItem\model\portableElement\exception\PortableElementNotFoundException;
use oat\taoQtiItem\model\portableElement\exception\PortableElementParserException;
use oat\taoQtiItem\model\portableElement\model\PortableModelRegistry;
use oat\taoQtiItem\model\qti\interaction\CustomInteraction;

abstract class AbstractRemoveAllVersionsOfPciAndInstall extends ScriptAction
{
    protected const PCI_NAME = '';

    protected const OPTION_FORCE = 'force';
    protected const OPTION_PACKAGE = 'package';

    protected PortableElementService $service;

    protected string $archivePath;
    protected bool $isForcedRemoval;

    protected function provideOptions(): array
    {
        return [
            self::OPTION_FORCE => [
                'prefix' => 'f',
                'longPrefix' => self::OPTION_FORCE,
                'description' => 'Force removal of previous versions and install of the new version',
                'flag' => true
            ],
            self::OPTION_PACKAGE => [
                'prefix' => 'p',
                'longPrefix' => self::OPTION_PACKAGE,
                'description' => 'Path to archive with PCI to install',
                'required' => true
            ]
        ];
    }

    protected function provideDescription(): string
    {
        $msg = "This command will execute import of the `%s` PCI ZIP-package. However, without `-f` flag,
         it won't remove older versions and install a new one. WARNING: providing `-f` is risky - 
         it should be done on the platform where are
         no tests/items are using the PCIs to be removed - otherwise this content might be broken."
        ;
        return sprintf($msg, $this->getPciCommonName());
    }

    protected function run(): Report
    {
        $this->setUpOptions();

        $pciCommonName = $this->getPciCommonName();

        $message = 'Performing installation of the provided version of %s PCI from package';
        $report = Report::createInfo(
            sprintf($message, $pciCommonName)
        );

        $registry = (new IMSPciModel())->getRegistry();
        $oatRegistry = (new PciModel())->getRegistry();

        try {
            $versions = array_keys($registry->getAllVersions(static::PCI_NAME));

            $message = 'Existing IMS versions of the %s PCI: ' . json_encode($versions);
            $subReport = Report::createInfo(
                sprintf($message, $pciCommonName)
            );
            $report->add($subReport);
        } catch (PortableElementNotFoundException $e) {
            $message = 'No IMS versions not found for the %s PCI';
            $subReport = Report::createWarning(sprintf($message, $pciCommonName));
            $report->add($subReport);
        }

        try {
            $oatVersions = array_keys($oatRegistry->getAllVersions(static::PCI_NAME));

            $message = 'Existing OAT versions of the %s PCI: ' . json_encode($oatVersions);
            $subReport = Report::createInfo(
                sprintf($message, $pciCommonName)
            );
            $report->add($subReport);
        } catch (PortableElementNotFoundException $e) {
            $message = 'No OAT versions not found for the %s PCI';
            $subReport = Report::createWarning(sprintf($message, $pciCommonName));
            $report->add($subReport);
        }

        if ($this->isForcedRemoval) {
            if ($registry->has(static::PCI_NAME)) {
                /** @noinspection PhpUnhandledExceptionInspection */
                $registry->removeAllVersions(static::PCI_NAME);
            }
            if ($oatRegistry->has(static::PCI_NAME)) {
                /** @noinspection PhpUnhandledExceptionInspection */
                $oatRegistry->removeAllVersions(static::PCI_NAME);
            }

            foreach ($this->getPciModels() as $pciModel) {
                try {
                    $portableElement = $this->getService()->import($pciModel->getId(), $this->archivePath);
                    if (!is_null($portableElement)) {
                        break;//stop at the first one
                    }
                } catch (PortableElementInvalidModelException|PortableElementParserException $e) {
                    //Nothing to do, just skip
                }
            }
        } else {
            $subReport = Report::createWarning(
                'Script is running without `-f` flag - nothing would be removed and/or installed!'
            );
            $report->add($subReport);
        }

        return $report;
    }

    protected function getPciModels()
    {
        $pciModels = [];
        foreach (PortableModelRegistry::getRegistry()->getModels() as $model) {
            if (is_subclass_of($model->getQtiElementClassName(), CustomInteraction::class)) {
                $pciModels[] = $model;
            }
        }
        return $pciModels;
    }

    protected function getService()
    {
        if (!$this->service) {
            $this->service = new PortableElementService();
            $this->service->setServiceLocator($this->getServiceManager());
        }
        return $this->service;
    }

    protected function getPciCommonName()
    {
        return ucfirst(
            implode(
                ' ',
                preg_split(
                    '/(?=[A-Z])/',
                    str_replace(
                        'Interaction',
                        '',
                        static::PCI_NAME
                    )
                )
            )
        );
    }

    private function setUpOptions()
    {
        $this->archivePath = $this->getOption('package');
        $this->isForcedRemoval = $this->getOption('force') ?? false;
    }
}