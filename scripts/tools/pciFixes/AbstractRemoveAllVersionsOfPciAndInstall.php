<?php

declare(strict_types=1);

namespace oat\taoInvalsi\scripts\tools\pciFixes;

use oat\oatbox\extension\script\ScriptAction;
use oat\taoQtiItem\model\portableElement\model\PortableElementModel;
use oat\taoQtiItem\model\portableElement\PortableElementService;
use oat\oatbox\reporting\Report;
use oat\qtiItemPci\model\IMSPciModel;
use oat\qtiItemPci\model\PciModel;
use oat\taoQtiItem\model\portableElement\exception\PortableElementInvalidModelException;
use oat\taoQtiItem\model\portableElement\exception\PortableElementNotFoundException;
use oat\taoQtiItem\model\portableElement\exception\PortableElementParserException;
use oat\taoQtiItem\model\portableElement\model\PortableModelRegistry;
use oat\taoQtiItem\model\qti\interaction\CustomInteraction;

/**
 * Class AbstractRemoveAllVersionsOfPciAndInstall
 * @package oat\taoInvalsi\scripts\tools\pciFixes
 * User: uncleempty
 * Date: 05/12/2024
 *
 * Abstract script removing existing (if any) versions of the given PCI and installs needed version from the provided package,
 * Executed using one of the implementations.
 *
 * Examples:
 *  - `sudo -u www-data php index.php 'oat\taoInvalsi\scripts\tools\pciFixes\RemoveOldVersionsOfTextReaderAndInstallFromPackage' -p <pathToPackage> -f`
 *  - `sudo -u www-data php index.php 'oat\taoInvalsi\scripts\tools\pciFixes\RemoveOldVersionsOfMathEntryAndInstallFromPackage' -p <pathToPackage> -f`
 *
 * Running the script without providing `-f` flag - will only output existing versions of the given PCI,
 *  without removing/installing anything
 */
abstract class AbstractRemoveAllVersionsOfPciAndInstall extends ScriptAction
{
    protected const PCI_NAME = '';

    protected const OPTION_FORCE = 'force';
    protected const OPTION_PACKAGE = 'package';

    /** @var PortableElementService $service */
    protected PortableElementService $service;

    /** @var string $archivePath */
    protected string $archivePath;

    /** @var bool $isForcedRemoval */
    protected bool $isForcedRemoval;

    /**
     * Action options definition
     *
     * @return array[]
     */
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

    /**
     * Action description
     *
     * @return string
     */
    protected function provideDescription(): string
    {
        $msg = "This command will execute import of the `%s` PCI ZIP-package. However, without `-f` flag,
         it won't remove older versions and install a new one. WARNING: providing `-f` is risky - 
         it should be done on the platform where are
         no tests/items are using the PCIs to be removed - otherwise this content might be broken."
        ;
        return sprintf($msg, $this->getPciCommonName());
    }

    /**
     * Run action
     *
     * @return Report
     *
     * @throws PortableElementNotFoundException
     * @throws \common_exception_Error
     * @throws \oat\taoQtiItem\model\portableElement\exception\PortableElementInconsistencyModelException
     */
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

    /**
     * Get available PCI models
     *
     * @return PortableElementModel[]
     */
    protected function getPciModels(): array
    {
        $pciModels = [];
        foreach (PortableModelRegistry::getRegistry()->getModels() as $model) {
            if (is_subclass_of($model->getQtiElementClassName(), CustomInteraction::class)) {
                $pciModels[] = $model;
            }
        }
        return $pciModels;
    }

    /**
     * Get service implementation
     *
     * @return PortableElementService
     *
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    protected function getService(): PortableElementService
    {
        if (!$this->service) {
            $this->service = new PortableElementService();
            $this->service->setServiceLocator($this->getServiceManager());
        }
        return $this->service;
    }

    /**
     * Extract PCI common name out of PCI interaction name
     *
     * @return string
     */
    protected function getPciCommonName(): string
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

    /**
     * Initialize script params
     *
     * @return void
     */
    private function setUpOptions(): void
    {
        $this->archivePath = $this->getOption('package');
        $this->isForcedRemoval = $this->getOption('force') ?? false;
    }
}