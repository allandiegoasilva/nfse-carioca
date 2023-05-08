<?php
/*
 * Local configuration file to provide any overrides to your app.php configuration.
 * Copy and save this file as app_local.php and make changes as required.
 * Note: It is not recommended to commit files with credentials such as app_local.php
 * into source code version control.
 */
return [

    /* 
     NFSE CONFIG
    */
    "nfse" => [
        "certificate" => __DIR__ . "/../cert/pca-expert.pfx",
        "private_key" => "02032023",
        "links" => [
            "PRD" => "",
            "HML" => ""
        ]
    ],

    'debug' => filter_var(env('DEBUG', true), FILTER_VALIDATE_BOOLEAN),

    'Security' => [
        'salt' => env('SECURITY_SALT', 'e95cde01b632fe5af5f474d0515e912e23088d75b06ca605b2c56ec749b0c235'),
    ],

    'Datasources' => [
        'default' => [
            'host' => 'localhost',
            'port' => 3306,
            'username' => 'root',
            'password' => '',
            'database' => 'nfse',
            'url' => env('DATABASE_URL', null),
        ],
    ],
    'EmailTransport' => [
        'default' => [
            'host' => 'localhost',
            'port' => 25,
            'username' => null,
            'password' => null,
            'client' => null,
            'url' => env('EMAIL_TRANSPORT_DEFAULT_URL', null),
        ],
    ],
];
