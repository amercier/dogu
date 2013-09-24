<?php

namespace History\Service;

use PhlyRestfully\Resource;
use History\StatusDbPersistence;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class StatusResourceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $services)
    {
        $events   = $services->get('EventManager');
        $resource = new Resource;
        $resource->setEventManager($events);

        $listener = $services->get('History\PersistenceListener');
        $events->attach($listener);

        return $resource;
    }
}
