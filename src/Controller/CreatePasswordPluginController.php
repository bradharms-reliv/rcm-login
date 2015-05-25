<?php

/**
 * Create Password Controller
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

use App\Controller\TemplateMailer;
use Doctrine\ORM\EntityManager;
use Rcm\Http\Response;
use Rcm\Plugin\BaseController;
use Rcm\Plugin\PluginInterface;
use RcmLogin\Form\CreateNewPasswordForm;
use RcmLogin\InputFilter\CreateNewPasswordInputFilter;
use RcmUser\Service\RcmUserService;
use Vista\Exception\DistributorNotFoundException;

/**
 * Online App Plugin Controller
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
class CreatePasswordPluginController extends BaseController implements PluginInterface
{

    protected $templateMailer;
    /**
     * @var \RcmUser\Service\RcmUserService
     */
    protected $rcmUserService;

    protected $entityManager;

    /**
     * @param EntityManager  $entityManager
     * @param null           $config
     * @param TemplateMailer $templateMailer
     * @param RcmUserService $rcmUserService
     */
    public function __construct(
        EntityManager $entityManager,
        $config,
        TemplateMailer $templateMailer,
        RcmUserService $rcmUserService
    ) {
        $this->entityMgr = $entityManager;
        parent::__construct($config, 'RcmCreateNewPassword');
        $this->templateMailer = $templateMailer;
        $this->rcmUserService = $rcmUserService;
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
        $form = new CreateNewPasswordForm($instanceConfig);

        $postSuccess = false;
        $error = '';
        $hideForm = false;
        $passwordEntity = null;

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();
        $key = $request->getQuery('key', null);

        if ($key) {
            /** @var \RcmLogin\Entity\ResetPassword $passwordEntity */
            $passwordEntity = $this->entityMgr
                ->getRepository('RcmLogin\Entity\ResetPassword')
                ->findOneBy(['hashKey' => $key]);
        }

        if (!$passwordEntity) {
            return $this->notAuthorized();
        }

        $createdDate = $passwordEntity->getCreatedDate();

        if (strtotime("now") - $createdDate->getTimeStamp() > 172800) {
            return $this->notAuthorized();
        }

        if ($this->postIsForThisPlugin()) {
            $error = $this->handlePost(
                $form,
                $instanceConfig,
                $passwordEntity->getRcn()
            );

            if (!$error) {
                $postSuccess = true;

                $this->entityMgr->remove($passwordEntity);
                $this->entityMgr->flush();
            }
        }

        $view = parent::renderInstance(
            $instanceId,
            $instanceConfig
        );

        $view->setTemplate('rcm-create-new-password/plugin');

        $view->setVariables(
            [
                'hideForm' => $hideForm,
                'form' => $form,
                'postSuccess' => $postSuccess,
                'error' => $error
            ]
        );
        return $view;
    }

    protected function handlePost(
        CreateNewPasswordForm $form,
        $instanceConfig,
        $userId
    ) {
        $form->setInputFilter(new CreateNewPasswordInputFilter());
        $form->setData($this->getRequest()->getPost());

        if ($form->isValid()) {
            $formData = $form->getData();
            $newPasswordOne = $formData['password'];
            $newPasswordTwo = $formData['passwordTwo'];

            if ($newPasswordOne != $newPasswordTwo) {
                return $instanceConfig['translate']['passwordsDoNotMatch'];
            }

            $user = $this->rcmUserService->buildNewUser();
            $user->setUsername($userId);

            try {
                $result = $this->rcmUserService->readUser($user);
            } catch (DistributorNotFoundException $e) {
                return $instanceConfig['translate']['systemError'];
            }

            if (!$result->isSuccess()) {
                return $instanceConfig['translate']['invalidLink'];
            }

            $user = $result->getUser();
            $user->setPassword($newPasswordTwo);

            $result = $this->rcmUserService->updateUser($user);

            if (!$result->isSuccess()) {
                throw new \Exception($result->getMessagesString());
            }
        }

        return null;
    }

    protected function notAuthorized()
    {
        $response = new Response();
        $response->setStatusCode(401);
        return $response;
    }
}
