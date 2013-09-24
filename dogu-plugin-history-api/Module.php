<?php

namespace History;

use PhlyRestfully\HalResource;
use PhlyRestfully\Link;
use PhlyRestfully\LinkCollectionAwareInterface;
use PhlyRestfully\View\RestfulJsonModel;
use Zend\Paginator\Paginator;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;

class Module
{
    public function getAutoloaderConfig()
    {
        return array('Zend\Loader\StandardAutoloader' => array(
            'namespaces' => array(
                __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
            ),
        ));
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap($e)
    {
        $app    = $e->getTarget();
        $events = $app->getEventManager();
        $events->attach('route', array($this, 'onRoute'), -100);

        $sharedEvents = $events->getSharedManager();
        $sharedEvents->attach(
            'PhlySimplePage\PageController',
            'dispatch',
            array($this, 'onDispatchDocs'),
            -1
        );
    }

    public function onRoute($e)
    {
        $controllers = 'History\StatusResourceController';

        $matches = $e->getRouteMatch();
        if (!$matches) {
            return;
        }
        $controller = $matches->getParam('controller', false);
        if (!in_array($controller,
            array('History\StatusResourcePublicController', 'History\StatusResourceUserController')
        )) {
            return;
        }

        $app          = $e->getTarget();
        $services     = $app->getServiceManager();
        $events       = $app->getEventManager();
        $sharedEvents = $events->getSharedManager();
        $user         = $matches->getParam('user', false);

        // Add a "Link" header pointing to the documentation
        $sharedEvents->attach(
            $controllers,
            'dispatch',
            array($this, 'setDocumentationLink'),
            10
        );

        // Add a "describedby" relation to resources
        $sharedEvents->attach(
            $controllers,
            array('getList.post', 'get.post', 'create.post', 'patch.post', 'update.post'),
            array($this, 'setDescribedByRelation')
        );

        // Add metadata to collections
        $sharedEvents->attach(
            $controllers,
            'dispatch',
            array($this, 'onDispatchCollection'),
            -1
        );

        $sharedEvents->attach($controllers, 'getList.post', function ($e) {
            $collection = $e->getParam('collection');
            $collection->setResourceRoute('phpbnl13_status_api/user');
        });

        // Set a listener on the renderCollection.resource event to ensure
        // individual status links pass in the user to the route.
        $helpers = $services->get('ViewHelperManager');
        $links   = $helpers->get('HalLinks');
        $links->getEventManager()->attach('renderCollection.resource', function ($e) use ($user) {
            $eventParams = $e->getParams();
            $resource    = $eventParams['resource'];

            if (!$resource instanceof Status) {
                return;
            }

            $eventParams['route'] = 'phpbnl13_status_api/user';
            $eventParams['routeParams']['user']  = $resource->getUser();
        });
    }

    public function onDispatchDocs($e)
    {
        $route = $e->getRouteMatch()->getMatchedRouteName();
        $base  = 'phpbnl13_status_api/documentation';
        if (strlen($route) < strlen($base)
            || 0 !== strpos($route, $base)
        ) {
            return;
        }

        $model = $e->getResult();
        $model->setTerminal(true);

        $response = $e->getResponse();
        $headers  = $response->getHeaders();

        if ($route == $base) {
            $headers->addHeaderLine('content-type', 'text/x-markdown');
            return;
        }

        $headers->addHeaderLine('content-type', 'application/json');
    }

    public function setDocumentationLink($e)
    {
        $controller = $e->getTarget();
        $docsUrl    = $controller->halLinks()->createLink('phpbnl13_status_api/documentation', false);
        $response   = $e->getResponse();
        $response->getHeaders()->addHeaderLine(
            'Link',
            sprintf('<%s>; rel="describedby"', $docsUrl)
        );
    }

    public function onDispatchCollection($e)
    {
        $result = $e->getResult();
        if (!$result instanceof RestfulJsonModel) {
            return;
        }
        if (!$result->isHalCollection()) {
            return;
        }
        $collection = $result->getPayload();

        if (!$collection->collection instanceof Paginator) {
            return;
        }
        $collection->setAttributes(array(
            'count'    => $collection->collection->getTotalItemCount(),
            'page'     => $collection->page,
            'per_page' => $collection->pageSize,
        ));
    }

    public function setDescribedByRelation($e)
    {
        $resource = $e->getParam('resource', false);
        if (!$resource) {
            $resource = $e->getParam('collection', false);
        }

        if (!$resource instanceof LinkCollectionAwareInterface) {
            return;
        }
        $link = new Link('describedby');

        if ($resource instanceof HalResource) {
            $link->setRoute('phpbnl13_status_api/documentation/status');
        } else {
            $link->setRoute('phpbnl13_status_api/documentation/collection');
        }
        $resource->getLinks()->add($link);
    }
}
