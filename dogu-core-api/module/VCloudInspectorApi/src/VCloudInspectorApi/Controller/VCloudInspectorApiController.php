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
        $viewModel->status = 'success';
        $viewModel->message = 'Successfully retrieved items';
        $viewModel->data = array(
            'types' => array(
                "vm" => "Virtual Machines",
                "vApp" => "vApps",
                "vAppTemplate" => "vApp Templates",
                "org" => "Organizations",
            ),
        );
        return $viewModel;
    }
}
