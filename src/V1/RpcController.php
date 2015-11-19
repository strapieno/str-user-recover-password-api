<?php

namespace Strapieno\UserRecoverPassword\Api\V1;

use Strapieno\User\Model\UserModelService;
use Zend\Mvc\MvcEvent;
use ZF\Rpc\RpcController as ApigilityRpcController;

/**
 * Class RpcController
 */
class RpcController extends ApigilityRpcController
{
    /**
     * @param MvcEvent $e
     */
    public function generateToken(MvcEvent $e)
    {

        $app = $e->getApplication();
        $serviceLocator = $app->getServiceManager();

        $userService = $this->model()->get(UserModelService::class);
        var_dump(get_class($userService));
        die();
    }

    /**
     * @param MvcEvent $e
     */
    public function resetPassword(MvcEvent $e)
    {

    }
}