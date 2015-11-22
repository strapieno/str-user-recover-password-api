<?php

namespace Strapieno\UserRecoverPassword\Api\V1\InputFilter;

use Strapieno\Auth\Model\OAuth2\AdapterInterface;
use Strapieno\User\Model\InputFilter\DefaultInputFilter;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ResetPasswordInputFilterFactory
 */
class ResetPasswordInputFilterFactory implements FactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $inputFilterManager = $serviceLocator->get('InputFilterManager');

        /** @var $userDefaultInputFilter DefaultInputFilter */
        $userDefaultInputFilter = $inputFilterManager->get('Strapieno\User\Model\InputFilter\DefaultInputFilter');
        // TODO retrive name field (password) from config
        if (!$userDefaultInputFilter->has('password')) {
            // TODO exception
        }

        $input = $userDefaultInputFilter->get('password');
        $inputFilter = new InputFilter();
        // Add input
        $inputFilter->add($input);

        $input = (new Input('token'))->setRequired(true);
        return $input;
    }
}