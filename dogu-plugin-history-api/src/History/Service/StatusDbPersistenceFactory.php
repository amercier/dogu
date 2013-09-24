<?php

namespace History\Service;

use PhlyRestfully\Resource;
use History\StatusDbPersistence;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class StatusDbPersistenceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $services)
    {
        $table = $services->get('History\DbTable');
        return new StatusDbPersistence($table);
    }
}
