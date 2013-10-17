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
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    )
);
