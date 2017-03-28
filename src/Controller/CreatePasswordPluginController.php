<?php

namespace RcmLogin\Controller;

use App\Model\CheckoutMsgs;
use Doctrine\ORM\EntityManager;
use Rcm\Plugin\BaseController;
use Rcm\Plugin\PluginInterface;
use RcmLogin\Form\CreateNewPasswordForm;
use RcmUser\Service\RcmUserService;
use Zend\InputFilter\InputFilterInterface;

/**
 * CreatePasswordPluginController
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
    /**
     * @var \RcmUser\Service\RcmUserService
     */
    protected $rcmUserService;

    /**
     * @var
     */
    protected $entityManager;

    /**
     * @var InputFilterInterface
     */
    protected $createPasswordInputFilter;

    /**
     * CreatePasswordPluginController constructor.
     * @param EntityManager $entityManager
     * @param null $config
     * @param RcmUserService $rcmUserService
     * @param InputFilterInterface $createPasswordInputFilter
     * @param string $pluginName\
     */
    public function __construct(
        EntityManager $entityManager,
        $config,
        RcmUserService $rcmUserService,
        InputFilterInterface $createPasswordInputFilter,
        $pluginName = 'RcmCreateNewPassword'
    ) {
        $this->entityMgr = $entityManager;
        $this->createPasswordInputFilter = $createPasswordInputFilter;
        parent::__construct($config, $pluginName);
        $this->rcmUserService = $rcmUserService;
    }

    /**
     * @return InputFilterInterface
     */
    protected function getCreatePasswordInputFilter()
    {
        return clone($this->createPasswordInputFilter);
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
     * @param int $instanceId plugin instance id
     * @param array $instanceConfig Instance Config
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function renderInstance($instanceId, $instanceConfig, $skipAndJustCallParent = false)
    {
        if ($skipAndJustCallParent) {
            return parent::renderInstance($instanceId, $instanceConfig);
        }

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
            $error = $this->handlePostForCreatePassword(
                $form,
                $instanceConfig,
                $passwordEntity->getUserId()
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
                'error' => $error,
                'labelHelper' => $this->getLabelViewHelper(),
            ]
        );

        return $view;
    }

    /**
     * handlePost
     *
     * @param CreateNewPasswordForm $form
     * @param array $instanceConfig
     * @param string $userId
     *
     * @return null
     * @throws \Exception
     */
    protected function handlePostForCreatePassword(
        CreateNewPasswordForm $form,
        $instanceConfig,
        $userId
    ) {
        $form->setInputFilter($this->getCreatePasswordInputFilter());
        $form->setData($this->getRequest()->getPost());

        if ($form->isValid()) {
            $formData = $form->getData();


            $newPasswordOne = $formData['password'];
            $newPasswordTwo = $formData['passwordTwo'];

            if ($newPasswordOne != $newPasswordTwo) {
                return $instanceConfig['translate']['passwordsDoNotMatch'];
            }

            $user = $this->rcmUserService->buildNewUser();
            $user->setId($userId);

            $result = $this->rcmUserService->readUser($user);

            if (!$result->isSuccess()) {
                return $instanceConfig['translate']['invalidLink'];
            }

            $user = $result->getUser();
            $user->setPassword($newPasswordTwo);

            $result = $this->rcmUserService->updateUser($user);

            if (!$result->isSuccess()) {
                throw new \Exception($result->getMessagesString());
            }
        } else {

            return CheckoutMsgs::GENERIC;
        }
    }

    /**
     * notAuthorized
     *
     * @return \Zend\Http\Response
     */
    protected function notAuthorized()
    {
        return $this->redirect()->toUrl('/forgot-password?invalidLink=1');
    }

    /**
     * postIsForThisPlugin
     *
     * @return bool
     */
    public function postIsForThisPlugin()
    {
        if (!$this->getRequest()->isPost()) {
            return false;
        }

        return
            $this->getRequest()->getPost('rcmPluginName') == $this->pluginName
            || $this->getRequest()->getPost('rcmPluginName') == 'RcmCreateNewPassword';
    }
}
