<?php
return array(
    'phpbnl13_status_api' => array(
        'table'     => 'status',
        'page_size' => 10, // number of status items to return by default
    ),
    'phlyrestfully' => array(
        'renderer' => array(
            'hydrators' => array(
                'History\Status' => 'Hydrator\ClassMethods',
            ),
        ),
        'resources' => array(
            'History\StatusResourcePublicController' => array(
                'identifier'              => 'History\StatusResourceController',
                'listener'                => 'History\PersistenceListener',
                'page_size'               => 10,
                'route_name'              => 'phpbnl13_status_api/public',
                'collection_name'         => 'status',
                'collection_http_options' => array('GET'),
            ),
            'History\StatusResourceUserController' => array(
                'identifier'      => 'History\StatusResourceController',
                'listener'        => 'History\PersistenceListener',
                'page_size'       => 10,
                'route_name'      => 'phpbnl13_status_api/user',
                'collection_name' => 'status',
            ),
        ),
    ),
    'router' => array('routes' => array(
        'phpbnl13_status_api' => array(
            'type' => 'Literal',
            'options' => array(
                'route'    => '/api/history',
                'defaults' => array(
                    'controller' => 'History\StatusResourcePublicController',
                ),
            ),
            'may_terminate' => false,
            'child_routes' => array(
                'public' => array(
                    'type' => 'Literal',
                    'options' => array(
                        'route'    => '/public',
                    ),
                ),
                'user' => array(
                    'type' => 'Segment',
                    'options' => array(
                        'route'    => '/user/:user[/:id]',
                        'defaults' => array(
                            'controller' => 'History\StatusResourceUserController',
                        ),
                        'constraints' => array(
                            'user' => '[a-z0-9_-]+',
                            'id'   => '[a-f0-9]{5,40}',
                        ),
                    ),
                ),
                'documentation' => array(
                    'type' => 'Literal',
                    'options' => array(
                        'route'    => '/documentation',
                        'defaults' => array(
                            'controller' => 'PhlySimplePage\Controller\Page',
                            'template'   => 'phpbnl13_status_api/documentation',
                        ),
                    ),
                    'may_terminate' => true,
                    'child_routes' => array(
                        'collection' => array(
                            'type'    => 'Literal',
                            'options' => array(
                                'route'    => '/collection',
                                'defaults' => array(
                                    'template'   => 'phpbnl13_status_api/documentation/collection',
                                ),
                            ),
                        ),
                        'status' => array(
                            'type'    => 'Literal',
                            'options' => array(
                                'route'    => '/status',
                                'defaults' => array(
                                    'template'   => 'phpbnl13_status_api/documentation/status',
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    )),
    'service_manager' => array(
        'aliases' => array(
            'History\DbAdapter' => 'Zend\Db\Adapter\Adapter',
            'History\PersistenceListener' => 'History\StatusDbPersistence',
        ),
        'invokables' => array(
            'Hydrator\ClassMethods' => 'Zend\Stdlib\Hydrator\ClassMethods',
        ),
        'factories' => array(
            'History\DbTable' => 'History\Service\DbTableFactory',
            'History\StatusDbPersistence' => 'History\Service\StatusDbPersistenceFactory',
            'History\StatusResource' => 'History\Service\StatusResourceFactory',
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'phpbnl13_status_api/documentation' => __DIR__ . '/../view/phpbnl13_status_api/documentation.phtml',
            'phpbnl13_status_api/documentation/collection' => __DIR__ . '/../view/phpbnl13_status_api/documentation/collection.phtml',
            'phpbnl13_status_api/documentation/status' => __DIR__ . '/../view/phpbnl13_status_api/documentation/status.phtml',
        ),
    ),
);
