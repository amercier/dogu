<?php

namespace History\Service;

use History\StatusDbTable;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DbTableFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $services)
    {
        $adapter = $services->get('History\DbAdapter');
        $config  = $services->get('Config');
        $table   = 'status';
        if (isset($config['phpbnl13_status_api'])
            && isset($config['phpbnl13_status_api']['table'])
        ) {
            $table = $config['phpbnl13_status_api']['table'];
        }

        return new StatusDbTable($adapter, $table);
    }
}
