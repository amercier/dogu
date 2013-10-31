<?php
return array(

    'controllers' => array(
        'invokables' => array(
            'VCloudInspectorApi\Controller\VCloudInspectorApi' => 'VCloudInspectorApi\Controller\VCloudInspectorApiController',
        ),
    ),

    'router' => array(
        'routes' => array(
            'vcloud-inspector' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/vcloud-inspector[/:id]',
                    'constraints' => array(
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'VCloudInspectorApi\Controller\VCloudInspectorApi',
                    ),
                ),
            ),
        ),
    ),

    'view_manager' => array(
        'strategies' => array(
            'ViewJsonStrategy',
        ),
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
    )
);
