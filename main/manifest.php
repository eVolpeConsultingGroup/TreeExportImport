<?php
$manifest = array(
    'built_in_version' => '7.7.0.0',
    'acceptable_sugar_flavors' => array(
        'ENT',
        'ULT',
        'PRO',
        'CE',
    ),
    'acceptable_sugar_versions' => array(
        '10.*',
        '6.*',
    ),
    'key' => 'ev',
    'author' => 'eVolpe ,łk,sr',
    'type' => 'module',
    'is_uninstallable' => false,
    'description' => 'Object Tree export / import',
    'name' => 'ExportImportTree',
    'version' => '1.0.17',
    'published_date' => '2020-25-09 23:43:07',
    'type' => 'module',
    'remove_tables' => 'prompt',
);

$installdefs = array(
    'id' => 'ExportImportTree',
    'copy' => array(
        array(
            'from' => '<basepath>/include/ExportImportTree',
            'to' => 'custom/include/ExportImportTree',
        ),
        array(
            'from' => '<basepath>/clients/base/views/profileactions/profileactions.js',
            'to' => 'custom/clients/base/views/profileactions/profileactions.js',
        ),
    ),
    'language' => array(
        array(
            'from' => '<basepath>/ext/language/pl_PL.lang.php',
            'to_module' => 'application',
            'language' => 'pl_PL',
        ),
        array(
            'from' => '<basepath>/ext/language/en_us.lang.php',
            'to_module' => 'application',
            'language' => 'en_us',
        ),

    ),
    'menu' => array(
        array(
            'from' => '<basepath>/ext/menu/ExportImportTree.menu.php',
            'to_module' => 'application',
        ),
    ),
    'entrypoints' => array(
        array(
            'from' => '<basepath>/ext/entrypoints/ExportImportTree.entrypoints.php',
            'to_module' => 'application',
        ),
    ),
);
