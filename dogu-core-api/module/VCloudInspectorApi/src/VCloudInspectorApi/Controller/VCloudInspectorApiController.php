<?php
namespace VCloudInspectorApi\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

class VCloudInspectorApiController extends AbstractRestfulController
{
    protected $acceptCriteria = array(
        'Zend\View\Model\JsonModel' => array(
            'application/json',
        ),
    );

    public function getList()
    {
        //$viewModel = $this->acceptableViewModelSelector($this->acceptCriteria);
        $viewModel = new JsonModel();

        try {
            // $hostname = 'mongo.local';
            $hostname = '127.0.0.1';

            $mongoClient = new \MongoClient('mongodb://' . $hostname);
            $mongoDB = $mongoClient->selectDB('vCloudNG');
            $collection = $mongoDB->selectCollection('queries');

            $objects = array();

            /*
            foreach (array('ADMIN_VM' => 'vm') as $collectionName => $type) {

                $objects[$type] = array();
                foreach ($db->$collectionName->find() as $document) {

                    die(print_r($document, true));

                    $id = preg_replace(
                        '/^.*[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
                        '$1',
                        $document['get_href']
                    );
                    $name = $document['get_name'];

                    die(print_r($document));

                    if (array_key_exists($id, $objects[$type])) {
                        $objects[$type][$id]['name'] = $name;
                        $objects[$type][$id]['names'][] = $name;
                    } else {
                        $objects[$type][$id] = array(
                            'name' => $name,
                            'names' => array($name),
                        );
                    }
                }
            }*/

            $viewModel->status = 'success';
            $viewModel->message = 'Successfully retrieved items';
            $viewModel->data = array(
                'types' => array(
                    "vm" => "Virtual Machines",
                    "vApp" => "vApps",
                    "vAppTemplate" => "vApp Templates",
                    "org" => "Organizations",
                ),
                'objects' => $objects,
            );
        }
        catch(\Exception $e) {
            $viewModel->status = 'error';
            $viewModel->message = 'Failed retrieving vCloud objects from ' . $hostname;
            $viewModel->description = $e->getMessage();
            $viewModel->stacktrace = $e->getTrace();
        }

        return $viewModel;
    }
}
