<?php
namespace Strapieno\UserRecoverPassword\Api;

use Strapieno\Auth\Api\Authentication\AuthenticationListenerAggregate;
use Zend\ModuleManager\Feature\HydratorProviderInterface;
use Zend\ModuleManager\Feature\InputFilterProviderInterface;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ArrayUtils;

/**
 * Class Module
 */
class Module implements InputFilterProviderInterface
{
    /**
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * {@inheritdoc}
     */
    public function getInputFilterConfig()
    {
        return include __DIR__ . '/config/input-filter.config.php';
    }

    /**
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/',
                ],
            ],
        ];
    }
}
