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
        $viewModel = $this->acceptableViewModelSelector($this->acceptCriteria);

        try {
            // $hostname = 'mongo.local';
            $hostname = '127.0.0.1';

            $mongoClient = new \MongoClient('mongodb://' . $hostname);
            $mongoDB = $mongoClient->selectDB('vCloudNG');
            $collection = $mongoDB->selectCollection('objects');

            $objects = array();
            $counts = array();

            foreach ($collection->find(array(), array("_id" => true, "value" => true)) as $object) {

                $uuid = join('-', array(
                    $object['_id']['host'],
                    $object['_id']['queryType'],
                    $object['_id']['object'],
                ));

                $type = $object['_id']['queryType'];

                $objects[$uuid] = $object['_id'];
                if (array_key_exists('name', $object['value'])) {
                    $objects[$uuid]['name'] = $object['value']['name']['current'];
                }

                if (array_key_exists($type, $counts)) {
                    $counts[$type]++;
                }
                else {
                    $counts[$type] = 1;
                }
            }

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
                    'adminVAppNetwork' => 'vApp Networks',
                    'vAppOrgNetworkRelation' => 'vApp Org Newtork Relations',
                    'adminVApp' => 'vApps',
                    'vAppOrgVdcNetworkRelation' => 'vApp OrgVdc Network Relations',
                    'adminVM' => 'Virtual Machines',
                    'adminCatalogItem' => 'Catalog Items (admin)',
                    'adminVAppTemplate' => 'vApp Templates',
                    'edgeGateway' => 'Edge Gateways',
                    'adminMedia' => 'Medias',
                    'resourcePoolVmList' => 'Resource Pool VM Lists',
                    'adminOrgNetwork' => 'Org Networks',
                    'orgVdcNetwork' => 'Org vDC Networks',
                    'adminUser' => 'Users (admin)',
                    'adminOrgVdcStorageProfile' => 'Org vDC Storage Profiles',
                    'user' => 'Users',
                    'catalogItem' => 'Catalog Items',
                    'organization' => 'Organizations',
                    'right' => 'Rights',
                    'media' => 'Medias',
                    'vAppTemplate' => 'vApp Templates (admins)',
                    'adminGroup' => '',
                    'group' => '',
                    'adminCatalog' => '',
                    'catalog' => '',
                    'adminOrgVdc' => '',
                    'host' => '',
                    'externalNetwork' => '',
                    'role' => '',
                    'adminService' => '',
                    'service' => '',
                    'strandedItem' => '',
                    'datastore' => '',
                    'adminDisk' => '',
                    'providerVdcStorageProfile' => '',
                    'virtualCenter' => '',
                    'networkPool' => '',
                    'providerVdc' => '',
                ),
                'counts' => $counts,
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
