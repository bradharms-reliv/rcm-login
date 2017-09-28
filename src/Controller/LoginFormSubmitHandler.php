<?php


namespace RcmLogin\Controller;


use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RcmLogin\Validator\RedirectValidator;
use RcmUser\Service\RcmUserService;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\EventManager\Event;
use Zend\EventManager\EventManager;
use Zend\Authentication\Result;

class LoginFormSubmitHandler implements MiddlewareInterface
{
    protected $rcmUserService;

    protected $eventManager;

    protected $loginFormUrl;

    protected $afterLoginSuccessUrl;

    protected $disabledAccountUrl;

    protected $redirectValidator;

    protected $redirectWhitelistRegex;

    public function __construct(
        RcmUserService $rcmUserService,
        EventManager $eventManager,
        $loginFormUrl = '/login',
        $afterLoginSuccessUrl = '/login-home',
        $disabledAccountUrl = '/account-issue',
        $redirectWhitelistRegex = '/^\/((?!\/)).*$/' //Allow only relative URLS to prevent malicous off-site redirects
    )
    {
        $this->rcmUserService = $rcmUserService;
        $this->eventManager = $eventManager;
        $this->loginFormUrl = $loginFormUrl;
        $this->afterLoginSuccessUrl = $afterLoginSuccessUrl;
        $this->disabledAccountUrl = $disabledAccountUrl;
        $this->redirectWhitelistRegex = $redirectWhitelistRegex;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $requestBody = $request->getParsedBody();

        if (
            !array_key_exists('redirect', $requestBody)
            || !array_key_exists('username', $requestBody)
            || !array_key_exists('password', $requestBody)
        ) {
            return new HtmlResponse('400 Bad Request', 400);
        }

        $redirectParamUnvalidated = filter_var($requestBody['redirect']);

        $redirectParam = null;
        if (preg_match($this->redirectWhitelistRegex, $redirectParamUnvalidated)) {
            $redirectParam = $redirectParamUnvalidated;
        }

        //Ensure redirects can only be relative URLS to improve security. (Prevents redirects to bad sites)
        if (substr($redirectParam, 0, 1) !== '/') {
            $redirectParam = null;
        }

        $username = trim(filter_var($requestBody['username'], FILTER_SANITIZE_STRING));
        $password = filter_var($requestBody['password'], FILTER_SANITIZE_STRING);

        if (empty($username) || empty($password)) {
            return new RedirectResponse(
                $this->loginFormUrl . '?errorCode=missing'
                . '&username=' . urlencode($username)
                . '&redirect=' . $redirectParam
            );
        }

        $user = $this->rcmUserService->buildNewUser();
        $user->setUsername($username);
        $user->setPassword($password);

        /** @var \Zend\Authentication\Result $authResult */
        $authResult = $this->rcmUserService->authenticate($user);

        // Valid auth
        if (!$authResult->isValid()) {
            /**
             * Used for times when we want to tell them their username and password were good but there account has been
             * disabled for some other reasion.
             */
            if ($authResult->getCode() == Result::FAILURE_UNCATEGORIZED
                && !empty($this->disabledAccountUrl)
            ) {
                return new RedirectResponse($this->disabledAccountUrl);
            }

            return new RedirectResponse($this->loginFormUrl . '?errorCode=invalid'
                . '&username=' . urlencode($username)
                . '&redirect=' . $redirectParam);
        }

        $event = new Event('LoginSuccessEvent', $this);

        /** @var \Zend\EventManager\ResponseCollection $responses */
        $responses = $this->eventManager->trigger($event, null, [], function ($v) {
            return ($v instanceof ResponseInterface);
        });

        $response = $responses->last();

        if ($response instanceof ResponseInterface) {
            return $response;
        }

        $redirectUrl = $this->afterLoginSuccessUrl;

        if ($redirectParam) {
            $redirectUrl = $redirectParam;
        }

        return new RedirectResponse($redirectUrl);
    }
}
