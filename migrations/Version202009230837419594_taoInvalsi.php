<?php

declare(strict_types=1);

namespace oat\taoInvalsi\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202009230837419594_taoInvalsi extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Added System Status extension.';
    }

    public function up(Schema $schema): void
    {
        $this->write(
            'a) Please make sure to setup CRON job: */10 * * * * root cd /var/www/html/tao'
            . '&& /usr/bin/flock -n /var/lock/tao-InstanceCheck.lock -c "sudo -u www-data nohup /usr/bin/php index.php'
            . ' \'oat\taoSystemStatus\scripts\tools\RunInstanceCheck\' 2>&1 >>/var/log/tao/InstanceCheck.log &"'
            . PHP_EOL . ' b) Create an appropriate user with "System Status Manager" role.'
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
