<?php
return array(
    'modules' => array(
        'PhlyRestfully',
        'PhlySimplePage',
        'History',
        'Application',
    ),
    'module_listener_options' => array(
        'config_glob_paths'    => array(
            'config/autoload/{,*.}{global,local}.php',
        ),
        'module_paths' => array(
            './module',
            './plugins',
            './vendor',
        ),
    ),
);
