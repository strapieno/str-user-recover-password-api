<?php

return [
    'router' => [
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
                                'controller' => 'Strapieno\UserRecoverPassword\Api\V1\RecoverRpcController',
                                'action' => 'generateToken'
                            ],
                        ]
                    ],
                    'reset-password' => [
                        'type' => 'Segment',
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/reset-password/:token',
                            'defaults' => [
                                'controller' => 'Strapieno\UserRecoverPassword\Api\V1\ResetRpcController',
                                'action' => 'resetPassword'
                            ],
                            'constraints' => [
                                'token' => '[0-9a-zA-Z-_]{32}'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],
    'controllers' => [
        'invokables' => [
            'Strapieno\UserRecoverPassword\Api\V1\RecoverRpcController' => 'Strapieno\UserRecoverPassword\Api\V1\RpcController',
            'Strapieno\UserRecoverPassword\Api\V1\ResetRpcController' => 'Strapieno\UserRecoverPassword\Api\V1\RpcController',
        ]
    ],
    'zf-rpc' => [
        'Strapieno\UserRecoverPassword\Api\V1\RecoverRpcController' => [
            'service_name' => 'recover-password',
            'http_methods' => ['POST'],
            'route_name' => 'api-rpc/recover-password',
        ],
        'Strapieno\UserRecoverPassword\Api\V1\ResetRpcController' => [
            'service_name' => 'reset-password',
            'http_methods' => ['POST'],
            'route_name' => 'api-rpc/reset-password',
        ],
    ],
     'zf-content-negotiation' => [
        'accept_whitelist' => [
            'Strapieno\UserRecoverPassword\Api\V1\RecoverRpcController' => [
                'application/hal+json',
                'application/json',
            ],
            'Strapieno\UserRecoverPassword\Api\V1\ResetRpcController' => [
                'application/hal+json',
                'application/json',
            ]

        ],
        'content_type_whitelist' => [
            'Strapieno\UserRecoverPassword\Api\V1\RecoverRpcController' => [
                'application/json',
            ],
            'Strapieno\UserRecoverPassword\Api\V1\ResetRpcController' => [
                'application/json',
            ]
        ]
    ],
    'zf-content-validation' => [
        'Strapieno\UserRecoverPassword\Api\V1\RecoverRpcController' => [
            'input_filter' => 'Strapieno\UserRecoverPassword\Api\V1\InputFilter\GenerateTokenInputFilter'
        ],
        'Strapieno\UserRecoverPassword\Api\V1\ResetRpcController' => [
            'input_filter' => 'Strapieno\UserRecoverPassword\Api\V1\InputFilter\ResetPasswordInputFilter'
        ]
    ],
    'strapieno_input_filter_specs' => [
        'Strapieno\UserRecoverPassword\Api\V1\InputFilter\GenerateTokenInputFilter' => [
            'identity' => [
                'name' => 'identity',
                'require' => true,
                'allow_empty' => false
            ]
        ],
        'Strapieno\UserRecoverPassword\Api\V1\InputFilter\ResetPasswordInputFilter' => [
            'password' => [
                'name' => 'password',
                'require' => true,
                'allow_empty' => false
            ]
        ]
    ]
];

