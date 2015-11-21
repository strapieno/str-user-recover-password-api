<?php

return [
    'routes' => [
        'api-rpc' => [
            'type' => 'Literal',
            'options' => [
                'route' => '/rpc'
            ],
            'child_routes' => [
                'recover-password' => [
                    'type' => 'Segment',
                    'may_terminate' => true,
                    'options' => [
                        'route' => '/recover-password',
                        'defaults' => [
                            'action' => 'generateToken',
                            'controller' => 'Strapieno\UserRecoverPassword\Api\V1\RpcController'
                        ]
                    ]
                ]
            ]
        ]
    ],
    'controllers' => [
        'invokables' => [
            'Strapieno\UserRecoverPassword\Api\V1\RpcController' => 'Strapieno\UserRecoverPassword\Api\V1\RpcController',
        ]
    ],
    'zf-rpc' => [
        'Strapieno\UserRecoverPassword\Api\V1\RpcController' => [
            'service_name' => 'recover-password',
            'http_methods' => ['POST'],
            'route_name' => 'api-rpc/recover-password',
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
    'zf-content-validation' => [
        'Strapieno\UserRecoverPassword\Api\V1\RpcController' => [
            'input_filter' => 'Strapieno\UserRecoverPassword\Api\V1\GenerateTokenInputFilter'
        ],
    ],
];

