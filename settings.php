<?php
    if (file_exists(__DIR__ . '/settings.local.php')){
        include_once __DIR__ . '/settings.local.php';
    }

    define('website_title', 'PHP Website Bootstrap Demo');

    function GetComponentsSettings()
    {
        return [
            'default'=>[
                'IDatabase'=>[
                    'RealizeAs'=>'MySqlDB',
                    'Config'=>[
                        'db_host' => 'localhost:3306', //MySQL55
                        'db_username' => 'root',
                        'db_password' => 'root',
                        'db_name' => 'PhpWebsiteBootstrapDemo',
                        'db_prefix' => ''
                    ]
                ],
                'IAccountManager'=>[
                    'RealizeAs'=>'AccountManager'
                ],
                'IOAuth2'=>[
                    'RealizeAs'=>'OAuth2',
                    'Config'=>[
                        'Providers'=>[
                            'GoogleOAuthProvider'=>[
                                'ClientId'=>'',
                                'ClientSecret'=>'',
                                'ApiKey'=>''
                            ]
                        ]
                    ]
                ],
                'IPageRenderer'=>[
                    'RealizeAs'=>'PageRenderer',
                    'Config'=>[
                        'theme'=>'default',
                        'default_template'=>'main.php'
                    ]
                ]
            ]
        ];
    }
?>
