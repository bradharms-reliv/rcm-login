<?php

namespace RcmLogin\Controller;

use Rcm\Plugin\PluginInterface;
use Rcm\Plugin\BaseController;
use RcmUser\Service\RcmUserService;
use Zend\Authentication\Result;
use Zend\EventManager\Event;
use Zend\Stdlib\ResponseInterface;

/**
 * Plugin Controller
 *
 * This is the main controller for this plugin
 *
 * @category  Reliv
 * @author    Rod McNew <rmcnew@relivinc.com>
 * @copyright 2012 Reliv International
 * @license   License.txt New BSD License
 * @version   Release: 1.0
 *
 */
class PluginController extends BaseController implements PluginInterface
{

    /**
     * @var \RcmUser\Service\RcmUserService $rcmUserService
     */
    protected $rcmUserService;

    /**
     * PluginController constructor.
     *
     * @param array          $config
     * @param RcmUserService $rcmUserService
     */
    public function __construct(
        $config,
        RcmUserService $rcmUserService
    ) {
        parent::__construct($config);
        $this->rcmUserService = $rcmUserService;
    }

    /**
     * renderInstance
     *
     * @param int   $instanceId
     * @param array $instanceConfig
     *
     * @return mixed|\Zend\Http\Response|ResponseInterface|\Zend\View\Model\ViewModel
     */
    public function renderInstance($instanceId, $instanceConfig)
    {
        $error = null;
        $username = null;

        $view = parent::renderInstance(
            $instanceId,
            $instanceConfig
        );

        $view->setVariables(
            [
                'error' => $error,
                'username' => $username,
            ]
        );

        if (!$this->postIsForThisPlugin()) {
            return $view;
        }

        $user = $this->getUser();

        // Invalid User
        if (empty($user)) {
            $error = $instanceConfig['translate']['missing'];
            $view->setVariable('error', $error);

            return $view;
        }

        /** @var \Zend\Authentication\Result $authResult */
        $authResult = $this->rcmUserService->authenticate($user);

        // Valid auth
        if ($authResult->isValid()) {
            $parms = array(
                'request' => $this->getRequest(),
                'response' => $this->getResponse()
            );

            $event = new Event('LoginSuccessEvent', $this, $parms);
            $eventManager = $this->getEventManager();

            /** @var \Zend\EventManager\ResponseCollection $responses */
            $responses = $eventManager->trigger($event, null, [], function ($v) {
                return ($v instanceof ResponseInterface);
            });

            $response = $responses->last();

            if ($response instanceof ResponseInterface) {
                return $response;
            }

            return $this->getResponse();
        }

        // Invalid Auth
        if ($authResult->getCode() == Result::FAILURE_UNCATEGORIZED
            && !empty($this->config['rcmPlugin']['RcmLogin']['uncategorizedErrorRedirect'])
        ) {
            return $this->redirect()
                ->toUrl(
                    $this->config['rcmPlugin']['RcmLogin']['uncategorizedErrorRedirect']
                );
        }

        $error = $instanceConfig['translate']['invalid'];
        $view->setVariable('error', $error);

        return $view;
    }

    /**
     * getUser
     *
     * @return null|\RcmUser\User\Entity\User
     */
    protected function getUser()
    {
        $username = trim(
            filter_var(
                $this->getRequest()->getPost('username'),
                FILTER_SANITIZE_STRING
            )
        );

        $password = filter_var(
            $this->getRequest()->getPost('password'),
            FILTER_SANITIZE_STRING
        );

        if (empty($username) || empty($password)) {
            return null;
        }

        $user = $this->rcmUserService->buildNewUser();
        $user->setUsername($username);
        $user->setPassword($password);

        return $user;
    }
}
