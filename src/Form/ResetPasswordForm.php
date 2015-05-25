<?php
namespace RcmLogin\Form;

use Zend\Form\Form;

class ResetPasswordForm extends Form
{
    public function __construct($instanceConfig)
    {
        parent::__construct();

        $this->setAttribute('method', 'post');
        $this->setAttribute('action', $_SERVER['REQUEST_URI']);
        $this->setAttribute('class', 'rcm-reset-password-form');

        //Helps prevent this form's posts from affecting other plugins
        $this->add(
            [
                'name' => 'rcmPluginName',
                'attributes' => [
                    'type' => 'hidden',
                    'value' => 'RcmResetPassword'
                ]
            ]
        );

        $this->add(
            [
                'name' => 'rcn',
                'attributes' => ['type' => 'text']
            ]
        );

    }
}
