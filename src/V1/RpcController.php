<?php
namespace Strapieno\UserRecoverPassword\Api\V1;

use Matryoshka\Model\Object\IdentityAwareInterface;
use Strapieno\Auth\Model\OAuth2\AdapterInterface;
use Strapieno\User\Model\Criteria\Mongo\UserMongoCollectionCriteria;
use Strapieno\User\Model\Entity\State\Registered;
use Strapieno\User\Model\Entity\State\UserStateAwareInterface;
use Strapieno\User\Model\Entity\UserInterface;
use Strapieno\User\Model\UserModelInterface;
use Strapieno\User\Model\UserModelService;
use Strapieno\Utils\Model\Entity\PasswordAwareInterface;
use Strapieno\Utils\Model\Entity\RercoverPasswordAwareInterface;
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
        $identityValue = $inputFilter->get('identity')->getValue();
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
                    'Strapieno\Utils\Model\Entity\RercoverPasswordAwareInterface'
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
        $sm = $this->getServiceLocator();

        $criteria = (new UserMongoCollectionCriteria())->setRecoverPasswordToken($e->getRouteMatch()->getParam('token'));
        /** @var $userService  UserModelInterface */
        $userService = $this->model()->get(UserModelService::class);
        $result = $userService->find($criteria);

        if ($result->count() == 1) {

            $data = $this->getResetPasswordData($e);
            if ($data instanceof ApiProblemModel) {
                return $data;
            }

            /** @var $user UserInterface */
            $user = $result->current();
            if (!$user instanceof PasswordAwareInterface) {
                $message = sprintf('Class %s must be an instance of %s', get_class($user), PasswordAwareInterface::class);
                return new ApiProblemModel(new ApiProblem(500, $message));
            }

            if ($user instanceof RercoverPasswordAwareInterface) {
                $user->setRecoverPasswordToken(null);
            }

            if ($user instanceof UserStateAwareInterface && $user->getState() instanceof Registered) {
                $user->validated();
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
            return new ApiProblemModel(
                new ApiProblem(500, 'Missing Oauth2 storage or wrong interface given')
            );
        }
        return $adapter;
    }

    /**
     * @param MvcEvent $e
     * @return array|ApiProblemModel
     */
    protected function getResetPasswordData(MvcEvent $e)
    {
        $manager = $this->getServiceLocator()->get('InputFilterManager');
        $config = $this->getServiceLocator()->get('Config');

        $inputFilter = null;
        if (isset($config['reset-password-inputfilter']) && $config['reset-password-inputfilter'] == 'user') {
            $inputFilterUser = $manager->get('Strapieno\User\Model\InputFilter\DefaultInputFilter');

            if ($inputFilterUser->has('password')) {

                $inputFilter = new InputFilter();
                $inputFilter->add($inputFilterUser->get('password'));
            } else {
                return new ApiProblemModel(new ApiProblem(500, 'Missing Input password; cannot validate request'));
            }
        }

        if ($inputFilter == null && $manager->has('Strapieno\UserRecoverPassword\Api\V1\InputFilter\ResetPasswordInputFilter')) {
            $inputFilter = $manager->get('Strapieno\UserRecoverPassword\Api\V1\InputFilter\ResetPasswordInputFilter');
        }

        if ($inputFilter == null) {
            if (!$inputFilter instanceof InputFilter) {
                return new ApiProblemModel(new ApiProblem(500, 'Missing InputFilter; cannot validate request'));
            }
        }

        /** @var $data ParameterDataContainer */
        $data = $e->getParam('ZFContentNegotiationParameterData')->getBodyParams();

        $inputFilter->setData($data);
        if (!$inputFilter->isValid()) {
            return new ApiProblemModel(
                new ApiProblem(422, 'Failed Validation', null, null, [
                    'validation_messages' => $inputFilter->getMessages(),
                ])
            );
        }
        return  $inputFilter->getValues();
    }
}