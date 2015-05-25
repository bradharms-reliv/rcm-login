<?php
namespace RcmLogin\Form;

use Zend\Form\Form;

/**
 * Class CreateNewPasswordForm
 */
class CreateNewPasswordForm extends Form
{
    /**
     * @param int|null|string $instanceConfig
     */
    public function __construct($instanceConfig)
    {
        parent::__construct();

        $this->setAttribute('method', 'post');
        $this->setAttribute('action', $_SERVER['REQUEST_URI']);
        $this->setAttribute('class', 'rcm-create-new-password-form');

        //Helps prevent this form's posts from affecting other plugins
        $this->add(
            [
                'name' => 'rcmPluginName',
                'attributes' => [
                    'type' => 'hidden',
                    'value' => 'RcmCreateNewPassword'
                ]
            ]
        );

        $this->add(
            [
                'name' => 'password',
                'attributes' => ['type' => 'password']
            ]
        );
        $this->add(
            [
                'name' => 'passwordTwo',
                'attributes' => ['type' => 'password']
            ]
        );

    }
}
