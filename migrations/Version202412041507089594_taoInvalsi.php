<?php

declare(strict_types=1);

namespace oat\taoInvalsi\migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Exception\IrreversibleMigration;
use oat\oatbox\cache\SimpleCache;
use oat\oatbox\reporting\Report;
use oat\pciSamples\scripts\install\RegisterPciTextReaderIMS;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\tao\scripts\update\OntologyUpdater;
use common_ext_Extension as Extension;
use common_ext_ExtensionsManager as ExtensionsManager;
use oat\taoInvalsi\scripts\install\DowngradePcisToPreviousVersions;

final class Version202412041507089594_taoInvalsi extends AbstractMigration
{
    private const MATHENTRY_PCI_NAME = 'mathEntryInteraction';
    private const TEXTREADER_PCI_NAME = 'textReaderInteraction';

    public function getDescription(): string
    {
        return 'Downgrades some of the PCIs to lower versions, as higher ones have some unwanted bugs.';
    }

    public function up(Schema $schema): void
    {
        $downgrader = new DowngradePcisToPreviousVersions();
        $this->propagate($downgrader);

        $downgradeReport = $downgrader(
            [
                'paths' => [
                    self::MATHENTRY_PCI_NAME => './taoInvalsi/scripts/tools/pciFixes/fixedPcis/fixedMathEntry.zip',
                    self::TEXTREADER_PCI_NAME => './taoInvalsi/scripts/tools/pciFixes/fixedPcis/fixedTextReader.zip'
                ]
            ]
        );

        $this->addReport($downgradeReport);
    }

    public function down(Schema $schema): void
    {
        throw new IrreversibleMigration(
            'In order to undo this migration, please revert the PCI versions manually'
        );
    }
}
