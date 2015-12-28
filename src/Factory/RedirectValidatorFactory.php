<?php
/**
 * @category  RCM
 * @author    Westin Shafer <wshafer@relivinc.com>
 * @copyright 2012 Reliv International
 * @license   License.txt New BSD License
 * @version   GIT: reliv
 * @link      http://ci.reliv.com/confluence
 */

namespace RcmLogin\Factory;

use RcmLogin\Exception\MissingConfigException;
use RcmLogin\Validator\RedirectValidator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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
