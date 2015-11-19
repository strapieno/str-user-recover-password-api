<?php

return [
    'routes' => [
        'api-rpc' => [
            'type' => 'Literal',
            'options' => [
                'route' => '/rpc'
            ],
            'child_routes' => [
                'user-recover-password' => [
                    'type' => 'Segment',
                    'may_terminate' => true,
                    'options' => [
                        'route' => '/user/recover-password',
                        'defaults' => [
                            'action' => 'generateToken'
                            'controller' => 'Strapieno\OauthClient\Api\V1\Rest\Controller'
                        ]
                    ]
                ]
            ]
        ]
    ]
    'controllers' => [
        'invokables' => [
            'Strapieno\UserRecoverPassword\Api\V1\RpcController' => 'Strapieno\UserRecoverPassword\Api\V1\RpcController',
        ]
    ],
    'zf-rpc' => [
        'Strapieno\UserRecoverPassword\Api\V1\RpcController' => [
            'service_name' => 'recover-password',
            'http_methods' => ['POST'],
            'route_name' => 'api-rpc/user-recover-password',
        ],
    ],
     'zf-content-negotiation' => [
        'accept_whitelist' => [
            'Strapieno\UserRecoverPassword\Api\V1\RpcController' => [
                'application/hal+json',
                'application/json',
            ]
        ],
        'content_type_whitelist' => [
            'Strapieno\UserRecoverPassword\Api\V1\RpcController' => [
                'application/json',
            ]
        ]
    ],
];

