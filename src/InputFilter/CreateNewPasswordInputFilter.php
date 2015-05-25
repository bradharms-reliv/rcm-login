<?php

namespace RcmLogin\InputFilter;

use Zend\I18n\Validator\Alnum;
use Zend\InputFilter\Factory as InputFactory;

/**
 * Created by JetBrains PhpStorm.
 * User: brian
 * Date: 7/25/13
 * Time: 10:37 AM
 * To change this template use File | Settings | File Templates.
 */
class CreateNewPasswordInputFilter extends \Zend\InputFilter\InputFilter
{

    public function __construct()
    {

        $factory = new InputFactory();

        $this->add(
            $factory->createInput(
                [
                    'name' => 'password',
                    'required' => true,
                    'filters' => [
                        new \Zend\Filter\StripTags(),
                        new \Zend\Filter\StringTrim(),
                    ],
                    'validators' => [
                        new Alnum()
                    ]
                ]
            )
        );

        $this->add(
            $factory->createInput(
                [
                    'name' => 'passwordTwo',
                    'required' => true,
                    'filters' => [
                        new \Zend\Filter\StripTags(),
                        new \Zend\Filter\StringTrim(),
                    ],
                    'validators' => [
                        new Alnum()
                    ]
                ]
            )
        );

    }
}
