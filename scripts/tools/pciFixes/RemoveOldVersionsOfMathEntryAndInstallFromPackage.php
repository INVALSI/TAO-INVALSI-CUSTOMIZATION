<?php

declare(strict_types=1);

namespace oat\taoInvalsi\scripts\tools\pciFixes;

/**
 * Class AbstractRemoveAllVersionsOfPciAndInstall
 * @package oat\taoInvalsi\scripts\tools\pciFixes
 * User: uncleempty
 * Date: 05/12/2024
 *
 * Abstract script removing existing (if any) versions of the given PCI and installs needed version from the provided package,
 * Executed using one of the implementations.
 *
 * Example:
 *  - `sudo -u www-data php index.php 'oat\taoInvalsi\scripts\tools\pciFixes\RemoveOldVersionsOfMathEntryAndInstallFromPackage' -p <pathToPackage> -f`
 *
 * Running the script without providing `-f` flag - will only output existing versions of the given PCI,
 *  without removing/installing anything
 */
class RemoveOldVersionsOfMathEntryAndInstallFromPackage extends AbstractRemoveAllVersionsOfPciAndInstall
{
    protected const PCI_NAME = 'mathEntryInteraction';
}