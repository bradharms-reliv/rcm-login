<?php

namespace RcmLogin\EventListener;

use Zend\EventManager\Event;
use Zend\Filter\FilterInterface;
use Zend\Http\Request;
use Zend\Http\Response;

/**
 * Rcm Login Default Event Listener
 *
 * @author    Westin Shafer <wshafer@relivinc.com>
 * @copyright 2012 Reliv International
 * @license   License.txt New BSD License
 * @link      http://github.com/reliv
 */
class Login
{
    /**
     * @var FilterInterface
     */
    protected $filter;

    /**
     * @var array
     */
    protected $redirectParams;

    /**
     * @param FilterInterface $filter
     * @param array           $redirectParams
     */
    public function __construct(
        FilterInterface $filter,
        array $redirectParams = ['redirect', 'redirect-from']
    ) {
        $this->filter = $filter;
        $this->redirectParams = $redirectParams;
    }

    /**
     * LoginSuccess
     *
     * @param Event $event event
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

        $redirect = $this->getRedirectQueryParam($request);

        $redirect = $this->filter->filter($redirect);

        if (empty($redirect)
            && !empty($config['rcmPlugin']['RcmLogin']['defaultSuccessRedirect'])
        ) {
            $redirect = $config['rcmPlugin']['RcmLogin']['defaultSuccessRedirect'];
        } elseif (empty($redirect)) {
            $redirect = $request->getUri()->toString();
        }

        $response = new \Rcm\Http\Response();
        $response->setStatusCode(302);
        $response->getHeaders()->addHeaderLine('Location', $redirect);

        return $response;
    }

    /**
     * @param Request $request
     *
     * @return mixed|null|\Zend\Stdlib\ParametersInterface
     */
    protected function getRedirectQueryParam(
        Request $request
    ) {
        foreach ($this->redirectParams as $redirectParam) {
            $redirect = $request->getQuery($redirectParam, null);

            if (!empty($redirect)) {
                return $redirect;
            }
        }

        return null;
    }
}
