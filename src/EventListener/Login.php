<?php
/**
 * Rcm Event Wrapper
 *
 * RCM Event Wrapper will listen for events and fire the correct CMS
 * action for that event
 *
 * PHP version 5.3
 *
 * LICENSE: No License yet
 *
 * @category  Reliv
 * @package   Rcm
 * @author    Westin Shafer <wshafer@relivinc.com>
 * @copyright 2012 Reliv International
 * @license   License.txt New BSD License
 * @version   GIT: <git_id>
 * @link      http://github.com/reliv
 */
namespace RcmLogin\EventListener;

use Zend\EventManager\Event;
use Zend\Http\Response;

/**
 * Rcm Login Default Event Listener
 *
 * RCM Login Default Event Listener
 *
 * @category  Reliv
 * @package   RcmLogin
 * @author    Westin Shafer <wshafer@relivinc.com>
 * @copyright 2012 Reliv International
 * @license   License.txt New BSD License
 * @version   Release: 1.0
 * @link      http://github.com/reliv
 */
class Login
{
    /**
     * LoginSuccess
     *
     * @param MvcEvent $event event
     *
     * @return Response
     */
    public function loginSuccess(Event $event)
    {
        /** @var \Zend\ServiceManager\ServiceManager $serviceManager */
        $serviceManager = $event->getTarget()->getServiceLocator();

        $config = $serviceManager->get('config');

        /** @var $request \Zend\Http\Request */
        $request = $serviceManager->get('request');

        $redirect = $request->getUri()->toString();

        if (!empty($config['rcmPlugin']['RcmLogin']['defaultSuccessRedirect'])) {
            $redirect = $config['rcmPlugin']['RcmLogin']['defaultSuccessRedirect'];
        }

        $redirect = $request->getQuery('redirect', $redirect);

        $response = new \Rcm\Http\Response();
        $response->setStatusCode(302);
        $response->getHeaders()->addHeaderLine('Location', $redirect);

        return $response;
    }
}
