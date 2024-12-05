<?php

declare(strict_types=1);

namespace oat\taoInvalsi\scripts\tools\pciFixes;

class RemoveOldVersionsOfMathEntryAndInstallFromPackage extends AbstractRemoveAllVersionsOfPciAndInstall
{
    protected const PCI_NAME = 'mathEntryInteraction';
}