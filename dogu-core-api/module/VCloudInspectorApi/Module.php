<?php
namespace VCloudInspectorApi;

use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\Http\Request as HttpRequest;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ModelInterface;

class Module
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoloader_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function onBootstrap(MvcEvent $e)
    {
        // Attach the JSON view strategy
        $app      = $e->getTarget();
        $locator  = $app->getServiceManager();
        $view     = $locator->get('Zend\View\View');
        $strategy = $locator->get('ViewJsonStrategy');
        $view->getEventManager()->attach($strategy, 100);

        // Attach a listener to check for errors
        $events = $e->getTarget()->getEventManager();
        $events->attach(MvcEvent::EVENT_RENDER, array($this, 'onRenderError'));
        $events->attach(MvcEvent::EVENT_FINISH, array($this, 'onFinish'));
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onRenderError($e)
    {
        // must be an error
        if (!$e->isError()) {
            return;
        }

        // Create a new JsonModel - use application/api-problem+json fields.
        $response = $e->getResponse();


        // Make debugging easier if we're using xdebug!
        ini_set('html_errors', 0);

        // If we have a JsonModel in the result, then do nothing
        $currentModel = $e->getResult();
        if ($currentModel instanceof JsonModel) {
            return;
        }

        if ($currentModel instanceof ModelInterface && $currentModel->reason) {
            switch ($currentModel->reason) {
                case APPLICATION::ERROR_CONTROLLER_CANNOT_DISPATCH:
                    $description = 'The requested controller was unable to dispatch the request.';
                    break;
                case APPLICATION::ERROR_CONTROLLER_NOT_FOUND:
                    $description = 'The requested controller could not be mapped to an existing controller class.';
                    break;
                case APPLICATION::ERROR_CONTROLLER_INVALID:
                    $description = 'The requested controller was not dispatchable.';
                    break;
                case APPLICATION::ERROR_ROUTER_NO_MATCH:
                    $app      = $e->getTarget();
                    $locator  = $app->getServiceManager();
                    $request  = $e->getRequest();
                    $router   = $locator->get('router');
                    $routes = array();
                    foreach ($router->getRoutes() as $routeName => $route) {
                        $routes[$routeName] = $route;
                    }
                    $description = 'The requested URL "' . $request->getUri()->getPath()
                        . '" could not be matched by routing. Tested routes: ' . implode(', ', array_keys($routes));
                    break;
                case APPLICATION::ERROR_EXCEPTION:
                    $description = $currentModel->message;
                    break;
                default:
                    $description = $currentModel->message;
                    break;
            }
        }

        // Check the accept headers for application/json
        $request = $e->getRequest();
        if (!$request instanceof HttpRequest) {
            $response->setStatusCode(400);
            $response->setReasonPhrase('Bad Request');
            $description = 'Not a HTTP request: ' . get_class($response) . '.';
        }
        else {
            $headers = $request->getHeaders();
            if (!$headers->has('Accept')) {
                $response->setStatusCode(406);
                $response->setReasonPhrase('Not Acceptable');
                $description = 'Accept header is missing.';
            }
            else {
                $accept = $headers->get('Accept');
                $match  = $accept->match('application/json');
                if (!$match || $match->getTypeString() === '*/*') {
                    $response->setStatusCode(406);
                    $response->setReasonPhrase('Not Acceptable');
                    $description = 'Content-type "' . $accept->getFieldValue() . '" is not acceptable. Please use "application/json".';
                }
            }
        }

        $model = new JsonModel(array(
            'status' => 'error',
            'httpStatus' => $response->getStatusCode(),
            'message' => $response->getReasonPhrase(),
            'description' => $description,
        ));

        // Find out what the error is
        $exception  = $currentModel->getVariable('exception');

        if ($exception) {
            if ($exception->getCode()) {
                $e->getResponse()->setStatusCode($exception->getCode());
            }
            $model->detail = $exception->getMessage();

            // find the previous exceptions
            $messages = array();
            while ($exception = $exception->getPrevious()) {
                $messages[] = '* ' . $exception->getMessage();
            };
            if (count($messages)) {
                $exceptionString = implode("\n", $messages);
                $model->messages = $exceptionString;
            }
        }

        // set our new view model
        $model->setTerminal(true);
        $e->setResult($model);
        $e->setViewModel($model);
    }

    public function onFinish($e)
    {
        $response = $e->getResponse();
        if ($e->getResult()->status === 'error') {
            $response->setStatusCode(500);
            $response->setReasonPhrase('Internal Server Error');
        }

        $headers = $response->getHeaders();
        $contentType = $headers->get('Content-Type');
        if ($contentType) {
            if (strpos($contentType->getFieldValue(), 'application/json') !== false
                && strpos($response->getContent(), 'httpStatus')) {
                // This is (almost certainly!) an api-problem
                $headers->addHeaderLine('Content-Type', 'application/api-problem+json');
            }
        }

        $headers->addHeaderLine('Access-Control-Allow-Origin','*');
        $headers->addHeaderLine('Access-Control-Allow-Headers','X-Requested-With');
    }
}
