<?php

namespace RcmLogin\Factory;

use RcmLogin\Filter\RedirectFilter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class RedirectFilterFactory
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
class RedirectFilterFactory implements FactoryInterface
{
    /**
     * createService
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return RedirectFilter
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $filter = new RedirectFilter(
            $serviceLocator->get('RcmLogin\Validator\RedirectValidator')
        );

        return $filter;
    }
}
