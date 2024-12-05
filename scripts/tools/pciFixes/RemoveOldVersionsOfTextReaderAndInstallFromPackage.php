<?php

declare(strict_types=1);

namespace oat\taoInvalsi\scripts\tools\pciFixes;

class RemoveOldVersionsOfTextReaderAndInstallFromPackage extends AbstractRemoveAllVersionsOfPciAndInstall
{
    protected const PCI_NAME = 'textReaderInteraction';
}