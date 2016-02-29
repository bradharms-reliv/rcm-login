<?php

namespace RcmLogin\Factory;

use RcmLogin\Exception\MissingConfigException;
use RcmLogin\Validator\RedirectValidator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class RedirectValidatorFactory
 *
 * PHP version 5
 *
 * @category  Reliv
 * @package   RcmLogin\Factory
 * @copyright 2015 Reliv International
 * @license   License.txt
 * @version   Release: <package_version>
 * @link      https://github.com/reliv
 */
class RedirectValidatorFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');

        if (empty($config['rcmPlugin']['RcmLogin']['redirectBlacklistPattern'])) {
            throw new MissingConfigException(
                'Missing Black List Pattern from config'
            );
        }

        $validator = new RedirectValidator(array(
            'pattern' => $config['rcmPlugin']['RcmLogin']['redirectBlacklistPattern']
        ));

        return $validator;
    }
}
