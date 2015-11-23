<?php

namespace Strapieno\UserRecoverPassword\Api\V1;

use Strapieno\Auth\Model\OAuth2\AdapterInterface;
use Strapieno\ModelUtils\Entity\PasswordAwareInterface;
use Strapieno\ModelUtils\Entity\RercoverPasswordAwareInterface;
use Strapieno\User\Model\Criteria\Mongo\UserMongoCollectionCriteria;
use Strapieno\User\Model\Entity\UserInterface;
use Strapieno\User\Model\UserModelInterface;
use Strapieno\User\Model\UserModelService;
use Zend\Authentication\Storage\StorageInterface;
use Zend\Http\Response;
use Zend\InputFilter\InputFilter;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Model\JsonModel;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\View\ApiProblemModel;
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
        $inputFilter = $e->getParam('ZF\ContentValidation\InputFilter');
        if (!$inputFilter instanceof InputFilter) {
            return new ApiProblemModel(new ApiProblem(500, 'Missing InputFilter; cannot validate request'));
        }

        /** @var $sm ServiceLocatorInterface */
        $sm = $e->getApplication()->getServiceManager();

        $adapter = $this->getOauthStorageAdapter($sm);

        $identityField = $adapter->getIdentityField();
        $identityValue = $inputFilter->get($identityField)->getValue();
        /** @var $userService  UserModelInterface */
        $userService = $this->model()->get(UserModelService::class);

        $result = $userService->getAuthenticationUser(
            $identityField,
            $identityValue
        );

        if ($result->count() == 1) {
            /** @var $user UserInterface */
            $user = $result->current();
            if (!$user instanceof RercoverPasswordAwareInterface) {
                $message = sprintf(
                    'Class %s must be an instance of %s',
                    get_class($user),
                    'Strapieno\ModelUtils\Entity\RercoverPasswordAwareInterface'
                );
                return new ApiProblemModel(new ApiProblem(500, $message));
            }
            $user->generateRecoverPasswordToken();
            $user->save();

            return [
                'token' => $user->getRecoverPasswordToken()
            ];
        }

        return new ApiProblemModel(new ApiProblem(404, 'User not found'));
    }

    /**
     * @param MvcEvent $e
     */
    public function resetPassword(MvcEvent $e)
    {
        $inputFilter = $e->getParam('ZF\ContentValidation\InputFilter');
        if (!$inputFilter instanceof InputFilter) {
            return new ApiProblemModel(new ApiProblem(500, 'Missing InputFilter; cannot validate request'));
        }

        $sm = $this->getServiceLocator();
        $data = $inputFilter->getValues();

        $criteria = (new UserMongoCollectionCriteria())->setRecoverPasswordToken($data['token']);
        /** @var $userService  UserModelInterface */
        $userService = $this->model()->get(UserModelService::class);
        $result = $userService->find($criteria);

        if ($result->count() == 1) {
            /** @var $user UserInterface */
            $user = $result->current();
            if (!$user instanceof PasswordAwareInterface) {
                $message = sprintf(
                    'Class %s must be an instance of %s',
                    get_class($user),
                    'Strapieno\ModelUtils\Entity\PasswordAwareInterface'
                );
                return new ApiProblemModel(new ApiProblem(500, $message));
            }

            $user->setPassword($data['password']);
            $user->save();

            if ($this->getResponse() instanceof Response) {
                $this->getResponse()->setStatusCode(204);
            }
            return new JsonModel();
        }

        if ($result->count() > 1) {
            return new ApiProblemModel(new ApiProblem(409, 'Ambiguous token'));
        }

        return new ApiProblemModel(new ApiProblem(404, 'Token not found'));
    }

    /**
     * @param ServiceLocatorInterface $sm
     * @return AdapterInterface
     */
    protected function getOauthStorageAdapter(ServiceLocatorInterface $sm)
    {
        // TODO rename AdapterInterface to AdapterStorageInterface
        /** @var $adapter AdapterInterface */
        if ($sm->has('Strapieno\Auth\Model\OAuth2\StorageAdapter')
            && ($adapter = $sm->get('Strapieno\Auth\Model\OAuth2\StorageAdapter'))
            && !($adapter instanceof AdapterInterface)
        ) {
            // TODO Exception
        }

        return $adapter;
    }
}