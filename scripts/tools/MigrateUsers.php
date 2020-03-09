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

namespace oat\taoInvalsi\scripts\tools;


use oat\generis\persistence\PersistenceManager;
use oat\oatbox\extension\AbstractAction;
use oat\tao\model\TaoOntology;

/**
 */
class MigrateUsers extends AbstractAction
{

    public function __invoke($params)
    {
        $class = new \core_kernel_classes_Class(TaoOntology::CLASS_URI_TAO_USER);

        /** @var PersistenceManager $persistenceManager */
        $persistenceManager = $this->getServiceLocator()->get(PersistenceManager::SERVICE_ID);
        /** \common_persistence_SqlPersistence */
        $persistence = $persistenceManager->getPersistenceById('default');
        /** @var \Doctrine\DBAL\Query\QueryBuilder $qb */
        $qb = $persistence->getPlatForm()->getQueryBuilder();
        $qb->from('tmp_tt');
        $qb->select('*');
        $data = $qb->execute()->fetchAll();
        foreach ($data as $triples) {
            $class->createInstance($triples['object'], '', $triples['subject']);
        }
        return \common_report_Report::createInfo(count($data) . ' users created');
    }



}