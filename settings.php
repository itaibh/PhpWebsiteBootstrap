<?php
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
                'IOAuthProvider'=>[
                    'RealizeAs'=>'OAuthProvider',
                    'Config'=>[
                        'Providers'=>[
                            'GoogleOAuthProvider'=>[
                                'ClientId'=>'',
                                'ClientSecret'=>'',
                                'ApiKey'=>''
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
?>
