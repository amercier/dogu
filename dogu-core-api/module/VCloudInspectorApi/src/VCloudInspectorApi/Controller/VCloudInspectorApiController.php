<?php
namespace VCloudInspectorApi\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

class VCloudInspectorApiController extends AbstractRestfulController
{
    const UUID_PATTERN = '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}';

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
                    'adminGroup' => 'Groups (admin)',
                    'group' => 'Groups',
                    'adminCatalog' => 'Catalogs',
                    'catalog' => 'Catalogs (admin)',
                    'adminOrgVdc' => 'Org vDCs (admin)',
                    'host' => 'Hosts',
                    'externalNetwork' => 'External Networks',
                    'role' => 'Roles',
                    'adminService' => 'Services (admin)',
                    'service' => 'Services',
                    'strandedItem' => 'Stranded Items',
                    'datastore' => 'Datastores',
                    'adminDisk' => 'Disks',
                    'providerVdcStorageProfile' => 'vDC Storage Profiles',
                    'virtualCenter' => 'vCenters',
                    'networkPool' => 'Network Pools',
                    'providerVdc' => 'Provider vDCs',
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

    public function get($id)
    {
        $viewModel = $this->acceptableViewModelSelector($this->acceptCriteria);

        try {
            // $hostname = 'mongo.local';
            $hostname = '127.0.0.1';

            $mongoClient = new \MongoClient('mongodb://' . $hostname);
            $mongoDB = $mongoClient->selectDB('vCloudNG');
            $collection = $mongoDB->selectCollection('objects');

            if (!preg_match('/^(.*)-([^-]+)-(' . self::UUID_PATTERN . ')$/', $id, $matches)) {
                throw new Exception("Invalid ID '$id'", 400);
            }

            $cursor = $collection->find(array(
                '_id.host' => $matches[1],
                '_id.queryType' => $matches[2],
                '_id.object' => $matches[3],
            ));

            if (!$cursor->hasNext()) {
                throw new Exception("Unknown object with ID '$id'");
            }

            $object = $cursor->getNext();

            if ($cursor->hasNext()) {
                throw new Exception("Found multiple objects with ID '$id'");
            }

            $viewModel->status = 'success';
            $viewModel->message = 'Successfully retrieved item "' . $id . '"';
            $viewModel->data = $object;
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
