<?php

namespace Strapieno\UserRecoverPassword\Api\V1\InputFilter;

use Strapieno\Auth\Model\OAuth2\AdapterInterface;
use Strapieno\User\Model\InputFilter\DefaultInputFilter;
use Zend\InputFilter\InputFilter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class GenerateTokenInputFilterFactory
 */
class GenerateTokenInputFilterFactory implements FactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if ($serviceLocator instanceof AbstractPluginManager) {
            $serviceLocator = $serviceLocator->getServiceLocator();
        }

        /** @var $storage AdapterInterface */
        $storage = $serviceLocator->get('Strapieno\Auth\Model\OAuth2\StorageAdapter');
        $inputFilterManager = $serviceLocator->get('InputFilterManager');

        /** @var $userDefaultInputFilter DefaultInputFilter */
        $userDefaultInputFilter = $inputFilterManager->get('Strapieno\User\Model\InputFilter\DefaultInputFilter');
        if (!$userDefaultInputFilter->has($storage->getIdentityField())) {
            // TODO exception
        }

        $input = $userDefaultInputFilter->get($storage->getIdentityField());
        $inputFilter = new InputFilter();
        return $inputFilter->add($input);
    }
}