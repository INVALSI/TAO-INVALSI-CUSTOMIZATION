<?php
/**
 * ***********************************************************************
 * INVALSI CONFIDENTIAL
 * __________________
 *
 *  All Rights Reserved.
 *
 *  NOTICE:  All information contained herein is, and remains
 *  the property of INVALSI and its suppliers, if any.  The
 *  intellectual and technical concepts contained herein are
 *  proprietary to INVALSI and its suppliers, and are protected
 *  by trade secret or copyright law.
 *  Dissemination of this information or reproduction of this material
 *  is strictly forbidden unless prior written permission is obtained
 *  from INVALSI.
 *  ************************************************************************
 */

namespace oat\taoInvalsi\model\launchLog;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\SchemaException;
use oat\oatbox\service\ConfigurableService;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionCreated;
use Sinergi\BrowserDetector\Browser;
use Sinergi\BrowserDetector\Os;

/**
 * Interface RdsLaunchLogService
 *
 * @package oat\taoInvalsi\model\lauchLog
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 */
class RdsLaunchLogService extends ConfigurableService implements LaunchLog
{
    const TABLE_NAME = 'launch_log';

    /**
     * @param DeliveryExecutionInterface $deliveryExecution
     * @return bool
     * @throws \common_exception_NotFound
     */
    public function log(DeliveryExecutionInterface $deliveryExecution)
    {
        $result = $this->getPersistence()->insert(
            self::TABLE_NAME,
            [
                self::DELIVERY_EXECUTION_ID => $deliveryExecution->getIdentifier(),
                self::DELIVERY_ID => $deliveryExecution->getDelivery()->getUri(),
                self::TEST_TAKER_ID => $this->getTestTakerId($deliveryExecution),
                self::BROWSER => $this->getBrowserDetector()->getName(),
                self::BROWSER_VERSION => $this->getBrowserDetector()->getVersion(),
                self::OS => $this->getOsDetector()->getName(),
                self::OS_VERSION => $this->getOsDetector()->getVersion(),
                self::CREATED_AT => \tao_helpers_Date::getTimeStamp($deliveryExecution->getStartTime(), true),
            ]
        );

        return $result === 1;
    }

    /**
     * @inheritdoc
     */
    public function search($params = [], $options = [])
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->select('*')
            ->from(static::TABLE_NAME)
        ;

        $fields = $this->getFields();
        foreach ($params as $key => $val) {
            if (in_array($key, $fields)) {
                $queryBuilder->andWhere($key . '= :'.$key);
                $queryBuilder->setParameter($key, $val);
            }
        }

        if (isset($params['from'])) {
            $queryBuilder->andWhere(self::CREATED_AT . ' >=:createdAtMore');
            $queryBuilder->setParameter('createdAtMore', $params['from']);
        }

        if (isset($params['to'])) {
            $queryBuilder->andWhere(self::CREATED_AT . ' <=:createdAtLess');
            $queryBuilder->setParameter('createdAtLess', $params['to']);
        }

        if (isset($options['limit'])) {
            $queryBuilder->setMaxResults($options['limit']);
        }

        if (isset($options['offset'])) {
            $queryBuilder->setFirstResult($options['offset']);
        }

        if (isset($options['order']) && isset($options['dir'])) {
            $queryBuilder->orderBy($options['order'], strtoupper($options['dir']));
        }

        $result = $queryBuilder->execute()->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * Catch DeliveryExecutionCreated event and log it.
     *
     * @param DeliveryExecutionCreated $event
     * @return bool
     * @throws \common_exception_NotFound
     */
    public function catchDeliveryExecutionCreated(DeliveryExecutionCreated $event)
    {
        $de = $event->getDeliveryExecution();
        return $this->log($de);
    }

    /**
     * Get test taker id by delivery execution
     * @param DeliveryExecutionInterface $deliveryExecution
     * @return string
     * @throws \common_exception_NotFound
     */
    protected function getTestTakerId(DeliveryExecutionInterface $deliveryExecution)
    {
        return $deliveryExecution->getUserIdentifier();
    }

    /**
     * @return array
     */
    protected function getFields()
    {
        $fields = $this->getOption(static::OPTION_FIELDS);

        return is_null($fields) ? [] : $fields;
    }

    /**
     * @return \common_persistence_SqlPersistence
     */
    private function getPersistence()
    {
        $persistenceId = $this->getOption(self::OPTION_PERSISTENCE);
        $persistenceManager = $this->getServiceLocator()->get(\common_persistence_Manager::SERVICE_ID);
        return $persistenceManager->getPersistenceById($persistenceId);
    }

    /**
     * @return QueryBuilder
     */
    private function getQueryBuilder()
    {
        return $this->getPersistence()->getPlatform()->getQueryBuilder();
    }


    /**
     * Initialize log storage
     *
     * @param \common_persistence_Persistence $persistence
     * @return \common_report_Report
     */
    public static function install(\common_persistence_Persistence $persistence)
    {
        $schemaManager = $persistence->getDriver()->getSchemaManager();
        $schema = $schemaManager->createSchema();
        $fromSchema = clone $schema;

        try {
            $table = $schema->createTable(self::TABLE_NAME);
            $table->addOption('engine', 'InnoDB');
            $table->addColumn(static::DELIVERY_EXECUTION_ID, 'string', ['length' => 255]);
            $table->addColumn(static::DELIVERY_ID, 'string', ['length' => 255]);
            $table->addColumn(static::TEST_TAKER_ID, 'string', ['length' => 255]);
            $table->addColumn(static::BROWSER, 'text');
            $table->addColumn(static::BROWSER_VERSION, 'string', ['length' => 255]);
            $table->addColumn(static::OS, "text");
            $table->addColumn(static::OS_VERSION, 'string', ['length' => 255]);
            $table->addColumn(static::CREATED_AT, 'decimal', ['precision' => 16, 'scale' => 6, "notnull" => true]);
        } catch(SchemaException $e) {
            \common_Logger::i('Database Schema already up to date.');
        }

        $queries = $persistence->getPlatform()->getMigrateSchemaSql($fromSchema, $schema);

        foreach ($queries as $query) {
            $persistence->exec($query);
        }

        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, __('RDS scheduler storage successfully installed'));
    }

    /**
     * Get the browser detector
     *
     * @return Browser
     */
    protected function getBrowserDetector()
    {
        return new Browser();
    }

    /**
     * Get the operating system detector
     *
     * @return Os
     */
    protected function getOsDetector()
    {
        return new Os();
    }
}
