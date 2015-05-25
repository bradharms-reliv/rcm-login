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

    protected $templateMailer;
    /**
     * @var \RcmUser\Service\RcmUserService
     */
    protected $rcmUserManager;

    protected $entityMgr;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * __construct
     *
     * @param EntityManager    $entityManager    entityManager
     * @param null             $config           config
     * @param TemplateMailer   $templateMailer   templateMailer
     * @param RcmUserService   $rcmUserManager   rcmUserManager
     */
    public function __construct(
        EntityManager $entityManager,
        $config,
        TemplateMailer $templateMailer,
        RcmUserService $rcmUserManager
    ) {
        $this->entityMgr = $entityManager;
        parent::__construct($config);
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
        $postSuccess = false;
        $error = '';

        if ($this->postIsForThisPlugin('RcmResetPassword')) {
            $error = $this->handlePost($form, $instanceConfig);

            if (empty($error)) {
                $postSuccess = true;
            }
        }

        $view = parent::renderInstance(
            $instanceId,
            $instanceConfig
        );

        $view->setTemplate('rcm-reset-password/plugin');

        $view->setVariables(
            [
                'form' => $form,
                'postSuccess' => $postSuccess,
                'error' => $error
            ]
        );

        return $view;
    }

    /**
     * Handle Post for Plugin
     *
     * @param ResetPasswordForm $form
     * @param                   $instanceConfig
     *
     * @return null
     */
    protected function handlePost(ResetPasswordForm $form, $instanceConfig)
    {
        $resetPw = new ResetPassword();
        $form->setInputFilter($resetPw->getInputFilter());
        $form->setData($this->getRequest()->getPost());

        if ($form->isValid()) {
            $formData = $form->getData();
            $rcn = $formData['rcn'];

            $user = $this->rcmUserManager->buildNewUser();
            $user->setUsername($rcn);

            try {
                $result = $this->rcmUserManager->readUser($user);
            } catch (DistributorNotFoundException $e) {
                return $instanceConfig['translate']['notFound'];
            }

            if ($result->isSuccess()) {
                $user = $result->getUser();

                /** @var \Vista\Entity\Profile $profile */
                $profile = $user->getProperty('VistaApiUserProfile');

                if ($profile instanceof Profile && $profile->getEmail()) {
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
            }
        }

        return $instanceConfig['translate']['notFound'];
    }

    /**
     * @param $resetPw
     * @param $rcn
     * @param $userEmail
     * @param $instanceConfig
     */
    protected function sendEmail(ResetPassword $resetPw, $rcn, $userEmail, $instanceConfig)
    {
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

    }
}
