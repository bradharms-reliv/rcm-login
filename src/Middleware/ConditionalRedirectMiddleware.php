<?php

namespace RcmLogin\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface as Delegate;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Diactoros\Response\RedirectResponse;

/**
 * Middleware that conditionally redirects the user to the login page if they are not allowed to access the current page.
 */
class ConditionalRedirectMiddleware implements MiddlewareInterface
{
    /** @var callable */
    public $isAllowed;

    /**
     * @param callable $isAllowed Returns whether the user is allowed to view a response correlating the passed request
     */
    public function __construct(
        callable $isAllowed 
    ) {
        $this->isAllowed = $isAllowed;
    }

    public function process(
        Request $request,
        Delegate $delegate
    ) {
        /** @var callable */
        $isAllowed = $this->isAllowed;

        if (!$isAllowed($request)) {
            $url = '/login?errorCode=unauthorized&redirect='
                . urlencode($request->getUri()->getPath());
            return new RedirectResponse($url);
        }

        return $delegate->process($request);
    }
}
