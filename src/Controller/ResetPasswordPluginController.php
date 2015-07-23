<?php

/**
 * Reset Password Plugin Controller
 *
 * Main controller for the online app
 *
 * PHP version 5.3
 *
 * LICENSE: No License yet
 *
 * @category  Reliv
 * @author    Brian Janish <bjanish@relivinc.com>
 * @copyright 2013 Reliv International
 * @license   License.txt New BSD License
 * @version   GIT: <git_id>
 */
namespace RcmLogin\Controller;

use Doctrine\ORM\EntityManager;
use Rcm\Plugin\BaseController;
use Rcm\Plugin\PluginInterface;
use RcmLogin\Entity\ResetPassword;
use RcmLogin\Form\ResetPasswordForm;
use App\Controller\TemplateMailer;
use RcmUser\Service\RcmUserService;
use Vista\Entity\Profile;
use Vista\Exception\DistributorNotFoundException;
use Zend\Mail\Exception\InvalidArgumentException;

/**
 * Reset Password Plugin Controller
 *
 * Main controller for the online app
 *
 * @category  Reliv
 * @author    Brian Janish <bjanish@relivinc.com>
 * @copyright 2013 Reliv International
 * @license   License.txt New BSD License
 * @version   Release: 1.0
 *
 */
class ResetPasswordPluginController extends BaseController implements PluginInterface
{

    /**
     * @var TemplateMailer
     */
    protected $templateMailer;
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
     * __construct
     *
     * @param EntityManager  $entityManager  entityManager
     * @param null           $config         config
     * @param TemplateMailer $templateMailer templateMailer
     * @param RcmUserService $rcmUserManager rcmUserManager
     */
    public function __construct(
        EntityManager $entityManager,
        $config,
        TemplateMailer $templateMailer,
        RcmUserService $rcmUserManager
    ) {
        $this->entityMgr = $entityManager;
        parent::__construct($config, 'RcmResetPassword');
        $this->templateMailer = $templateMailer;
        $this->rcmUserManager = $rcmUserManager;
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

        $view->setTemplate('rcm-reset-password/plugin');

        $view->setVariables(
            [
                'form' => $form,
                'postSuccess' => false,
                'error' => null
            ]
        );

        if (!$this->postIsForThisPlugin()) {
            return $view;
        }

        // Handle Post
        $error = $this->handlePost($form, $instanceConfig);
        $view->setVariable('error', $error);

        if (empty($error)) {
            $view->setVariable('postSuccess', true);
        }

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
    protected function handlePost(ResetPasswordForm $form, $instanceConfig)
    {
        $resetPw = new ResetPassword();
        $form->setInputFilter($resetPw->getInputFilter());
        $form->setData($this->getRequest()->getPost());

        if (!$form->isValid()) {
            return $instanceConfig['translate']['notFound'];
        }

        $formData = $form->getData();
        $rcn = $formData['rcn'];

        $user = $this->rcmUserManager->buildNewUser();
        $user->setUsername($rcn);

        try {
            $result = $this->rcmUserManager->readUser($user);
        } catch (DistributorNotFoundException $e) {
            return $instanceConfig['translate']['notFound'];
        }

        if (!$result->isSuccess()) {
            return $instanceConfig['translate']['notFound'];
        }

        $user = $result->getUser();

        /** @var \Vista\Entity\Profile $profile */
        $profile = $user->getProperty('VistaApiUserProfile');

        if (!$profile instanceof Profile || !$profile->getEmail()) {
            return $instanceConfig['translate']['notFound'];
        }

        $resetPw->setRcn($rcn);
        $this->entityMgr->persist($resetPw);
        $this->entityMgr->flush();
        $this->sendEmail(
            $resetPw,
            $rcn,
            $profile->getEmail(),
            $instanceConfig
        );

        return null;
    }

    /**
     * @param $resetPw
     * @param $rcn
     * @param $userEmail
     * @param $instanceConfig
     */
    protected function sendEmail(
        ResetPassword $resetPw,
        $rcn,
        $userEmail,
        $instanceConfig
    ) {
        try {
            $this->templateMailer->sendEmailTemplateFromConfigArray(
                $userEmail,
                $instanceConfig['prospectEmail'],
                [
                    'name' => '',
                    'rcn' => $rcn,
                    'url' =>
                        'https://' . $_SERVER['HTTP_HOST'] . '/reset-password?id='
                        . $resetPw->getResetId() . '&key=' . $resetPw->getHashKey()
                ]
            );
        } catch (InvalidArgumentException $e) {
            // Do nothing
        }
    }
}
