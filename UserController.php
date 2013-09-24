<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

class UserController extends AbstractRestfulController
{
    protected $collectionOptions = array('GET', 'POST');
    protected $resourceOptions = array('GET', 'PUT', 'DELETE');

    protected function _getOptions()
    {
        // if we have an ID, return specific item
        if ($this->params->fromRoute('id', false)) {
            return $this->resourceOptions;
        }

        // no ID, return collection
        return $this->collectionOptions;
    }

    public function option()
    {
        $response = $this->getResponse();

        // If in Options Array, Allow
        $response->getHeaders()
            ->addHeaderLine('Allow', implode(',', $this->_getOptions()));

        return $response;
    }

    public function setEventManager(EventManagerInterface $events)
    {
        $this->events = $events;

        // Register the listener and callback method with a priority of 10
        $events->attach('dispatch', array($this, 'checkOptions'), 10);
    }

    public function checkOptions($e)
    {
        if (in_array($e->getRequest()->getMethod(), $this->_getOptions())) {
            // Method Allowed, Nothing to Do
            return;
        }

        // Method Not Allowed
        $response = $this->getResponse();
        $response->setStatusCode(405);
        return $response;
    }

    public function create($data)
    {
        // Get created service to handle user creation
        // in this case userAPIService extends UserService and
        // adds in the _links or available actions to the result
        $userAPIService = $this->getServiceLocator()->get('userAPIService');

        $result = $userAPIService->create($data);
        $response = $this->getResponse();
        $response->setStatusCode(201);

        // Send Data to the View
        return new JsonModel($result);
    }
}
