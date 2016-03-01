<?php

namespace RcmLogin\Controller;

use Doctrine\ORM\EntityManager;
use Rcm\Plugin\BaseController;
use Rcm\Plugin\PluginInterface;
use RcmLogin\Email\Mailer;
use RcmLogin\Entity\ResetPassword;
use RcmLogin\Form\ResetPasswordForm;
use RcmUser\Service\RcmUserService;

/**
 * Reset Password Plugin Controller
 *
 * @category  Reliv
 * @author    Brian Janish <bjanish@relivinc.com>
 * @copyright 2013 Reliv International
 * @license   License.txt New BSD License
 * @version   Release: 1.0
 *
 */
class ResetPasswordPluginController extends BaseController implements
    PluginInterface
{

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @var \RcmUser\Service\RcmUserService
     */
    protected $rcmUserManager;

    /**
     * @var EntityManager
     */
    protected $entityMgr;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * ResetPasswordPluginController constructor.
     *
     * @param EntityManager  $entityManager
     * @param null           $config
     * @param Mailer         $mailer
     * @param RcmUserService $rcmUserManager
     */
    public function __construct(
        EntityManager $entityManager,
        $config,
        Mailer $mailer,
        RcmUserService $rcmUserManager
    ) {
        $this->entityMgr = $entityManager;
        parent::__construct($config, 'RcmResetPassword');
        $this->mailer = $mailer;
        $this->rcmUserManager = $rcmUserManager;
    }

    /**
     * getLabelViewHelper
     *
     * @return \RcmLogin\Form\LabelHelper
     */
    protected function getLabelViewHelper()
    {
        return $this->getServiceLocator()->get('RcmLogin\Form\LabelHelper');
    }

    /**
     * Plugin Action - Returns the guest-facing view model for this plugin
     *
     * @param int   $instanceId     plugin instance id
     * @param array $instanceConfig Instance Config
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function renderInstance($instanceId, $instanceConfig)
    {
        $form = new ResetPasswordForm($instanceConfig);
        $error = null;
        $view = parent::renderInstance(
            $instanceId,
            $instanceConfig
        );

        if ($this->params()->fromQuery('invalidLink')) {
            $error = 'The password reset link you used is invalid.'
                . ' It may be expired or have already been used. Please try again below.';
        }

        $view->setTemplate('rcm-reset-password/plugin');
        $view->setVariables(
            [
                'form' => $form,
                'postSuccess' => false,
                'error' => $error,
                'labelHelper' => $this->getLabelViewHelper(),
            ]
        );

        if (!$this->postIsForThisPlugin()) {
            return $view;
        }

        // Handle Post
        $error = $this->handlePost($form, $instanceConfig);

        if (empty($error)) {
            $view->setVariable('postSuccess', true);
        }

        $view->setVariable('error', $error);

        return $view;
    }

    /**
     * Handle Post for Plugin
     *
     * @param ResetPasswordForm $form
     * @param                   $instanceConfig
     *
     * @return null|string
     */
    protected function handlePost(
        ResetPasswordForm $form,
        $instanceConfig
    ) {
        $resetPw = new ResetPassword();
        $form->setInputFilter($resetPw->getInputFilter());
        $form->setData($this->getRequest()->getPost());

        if (!$form->isValid()) {
            return;
        }

        $formData = $form->getData();
        $userId = $formData['userId'];

        $user = $this->rcmUserManager->buildNewUser();
        $user->setUsername($userId);

        $result = $this->rcmUserManager->readUser($user);

        if (!$result->isSuccess()) {
            return;
        }

        $user = $result->getUser();
        if (!$user->getEmail()) {
            return;
        }

        $resetPw->setUserId($user->getId());

        $this->entityMgr->persist($resetPw);
        $this->entityMgr->flush();
        $this->mailer->sendRestPasswordEmail(
            $resetPw,
            $user,
            $instanceConfig
        );

        return;
    }
}
