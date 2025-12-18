<?php

declare(strict_types=1);

namespace oat\taoInvalsi\scripts\install;

use oat\oatbox\extension\InstallAction;
use oat\oatbox\reporting\Report;
use oat\qtiItemPci\model\IMSPciModel;
use oat\qtiItemPci\model\PciModel;
use oat\taoQtiItem\model\portableElement\exception\PortableElementInvalidModelException;
use oat\taoQtiItem\model\portableElement\exception\PortableElementNotFoundException;
use oat\taoQtiItem\model\portableElement\exception\PortableElementParserException;
use oat\taoQtiItem\model\portableElement\model\PortableElementModel;
use oat\taoQtiItem\model\portableElement\model\PortableModelRegistry;
use oat\taoQtiItem\model\portableElement\PortableElementService;
use oat\taoQtiItem\model\portableElement\storage\PortableElementRegistry;
use oat\taoQtiItem\model\qti\interaction\CustomInteraction;

class DowngradePcisToPreviousVersions extends InstallAction
{
    private const MATHENTRY_PCI_NAME = 'mathEntryInteraction';
    private const TEXTREADER_PCI_NAME = 'textReaderInteraction';

    private const PCIS_TO_REMOVE_INSTALL = [
        self::MATHENTRY_PCI_NAME,
        self::TEXTREADER_PCI_NAME
    ];

    private const DEFAULT_PCI_CORRECT_VERSIONS_PATHS = [
        self::MATHENTRY_PCI_NAME => TAO_INSTALL_PATH . 'taoInvalsi/scripts/tools/pciFixes/fixedPcis/fixedMathEntry.zip',
        self::TEXTREADER_PCI_NAME => TAO_INSTALL_PATH . 'taoInvalsi/scripts/tools/pciFixes/fixedPcis/fixedTextReader.zip'
    ];

    /** @var PortableElementService|null $service */
    protected ?PortableElementService $service = null;

    /** @var bool @debug */
    private bool $debug = false;

    /** @var string $currentPciName */
    private string $currentPciName;

    /** @var array $pciRegistries */
    private array $pciRegistries;

    /** @var array $pciPaths */
    private array $pciPaths = [];

    /**
     * @param $params
     *
     * @return Report
     *
     * @throws \common_exception_Error
     */
    public function __invoke($params): Report
    {
        $report = Report::createInfo(
            'Downgrading required PCIs to the versions approved in INVALSI reality'
        );

        $this->setParams($params);

        foreach (self::PCIS_TO_REMOVE_INSTALL as $this->currentPciName) {
            $pciCommonName = $this->getPciCommonName($this->currentPciName);

            $message = 'Performing installation of the provided version of %s PCI from package';
            $subReport = Report::createInfo(
                sprintf($message, $pciCommonName)
            );
            $report->add($subReport);

            if ($this->debug) {
                $report->add($this->getAvailableVersions($pciCommonName));
            }

            $report->add($this->removeAllPciVersions($pciCommonName));

            if ($this->debug) {
                $report->add($this->getAvailableVersions($pciCommonName));
            }

            $report->add(
                $this->installCorrectVersionOfPci(
                    $pciCommonName,
                    $this->pciPaths[$this->currentPciName]
                )
            );

            if ($this->debug) {
                $report->add($this->getAvailableVersions($pciCommonName));
            }
        }

        $subReport = Report::createInfo(
            'Script finished!'
        );
        $report->add($subReport);

        return $report;
    }

    /**
     * Initialize incoming params
     *
     * @param array $params Array of params for the downgrader
     *
     * @return void
     */
    private function setParams(array $params = []): void
    {
        if (empty($params)) {
            $this->pciPaths = self::DEFAULT_PCI_CORRECT_VERSIONS_PATHS;
            $this->debug = false;
        } else {
            $this->pciPaths = $params['paths'] ?? self::DEFAULT_PCI_CORRECT_VERSIONS_PATHS;
            $this->debug = $params['debug'] ?? false;
        }
    }

    /**
     * Get PCI "common name" from the interaction name
     *
     * @param string $pciName
     *
     * @return string
     */
    private function getPciCommonName(string $pciName): string
    {
        return ucfirst(
            implode(
                ' ',
                preg_split(
                    '/(?=[A-Z])/',
                    str_replace(
                        'Interaction',
                        '',
                        $pciName
                    )
                )
            )
        );
    }

    /**
     * Initialize available PCI registries
     *
     * @return PortableElementRegistry[]
     */
    private function getPciRegistries(): array
    {
        if (empty ($this->pciRegistries)) {
            $this->pciRegistries = [
                'IMS' => (new IMSPciModel())->getRegistry(),
                'OAT' => (new PciModel())->getRegistry()
            ];
        }
        return $this->pciRegistries;
    }

    /**
     * Get available PCI versions on the platform
     *
     * @param string $pciCommonName
     *
     * @return Report
     *
     * @throws \common_exception_Error
     */
    private function getAvailableVersions(string $pciCommonName): Report
    {
        $report = Report::createInfo(
            'Checking already available versions in both IMS and OAT PCI registries'
        );

        $registries = $this->getPciRegistries();

        foreach ($registries as $registryName => $registry) {
            try {
                $versions = array_keys($registry->getAllVersions($this->currentPciName));

                $message = 'Existing %s versions of the %s PCI: ' . json_encode($versions);
            } catch (PortableElementNotFoundException $e) {
                $message = 'In %s registry there were found no version of %s PCI';
            }
            $subReport = Report::createInfo(
                sprintf(
                    $message,
                    $registryName,
                    $pciCommonName
                )
            );
            $report->add($subReport);
        }

        return $report;
    }

    /**
     * Remove all the PCI versions (for the given PCI) available on the platform
     *
     * @param string $pciCommonName
     *
     * @return Report
     *
     * @throws \common_exception_Error
     */
    private function removeAllPciVersions(string $pciCommonName): Report
    {
        $message = 'Removing all existing versions of %s PCI from registries';
        $report = Report::createInfo(
            sprintf($message, $pciCommonName)
        );

        $pciRegistries = $this->getPciRegistries();

        foreach ($pciRegistries as $registryName => $registry) {
            try {
                if ($registry->has($this->currentPciName)) {
                    $registry->removeAllVersions($this->currentPciName);

                    $message = 'Removed all the versions of %s PCI from %s registry';
                    $subReport = Report::createSuccess(
                        sprintf($message, $pciCommonName, $registryName)
                    );

                } else {
                    $message = 'There are no versions of %s PCI in %s registry';
                    $subReport = Report::createWarning(
                        sprintf($message, $pciCommonName, $registryName)
                    );

                }
                $report->add($subReport);
            } catch (\Throwable $e) {
                $message = 'Failed to remove version of %s PCI from %s registry';
                $subReport = Report::createError(
                    sprintf($message, $pciCommonName, $registryName)
                );

                $report->add($subReport);
            }
        }

        return $report;
    }

    /**
     * Install PCI from the package
     *
     * @param string $pciCommonName
     * @param string $archivePath
     *
     * @return Report
     *
     * @throws \oat\taoQtiItem\model\portableElement\exception\PortableElementInconsistencyModelException
     */
    private function installCorrectVersionOfPci(string $pciCommonName, string $archivePath): Report
    {
        $message = 'Installing needed version of %s PCI from ZIP-package';
        $report = Report::createInfo(
            sprintf($message, $pciCommonName)
        );

        $portableElement = null;

        foreach ($this->getPciModels() as $pciModel) {
            try {
                $portableElement = $this->getService()->import($pciModel->getId(), realpath($archivePath));
                if (!is_null($portableElement)) {
                    break;//stop at the first one
                }
            } catch (PortableElementInvalidModelException|PortableElementParserException $e) {
                //Nothing to do, just skip
            }
        }

        if (!is_null($portableElement)) {
            $message = 'Required version of %s PCI was successfully installed from ZIP-package';
            $report = Report::createSuccess(
                sprintf($message, $pciCommonName)
            );
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

}