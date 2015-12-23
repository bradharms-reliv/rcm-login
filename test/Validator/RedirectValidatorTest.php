<?php

namespace RcmLogin\Test;

use RcmLogin\Validator\RedirectValidator;

class RedirectValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var RedirectValidator */
    protected $validator;

    public function setup()
    {
        $this->validator = new RedirectValidator('/.+:\/\/|\/\//i');
    }

    public function testValidatorFalse()
    {
        $this->assertFalse($this->validator->isValid(
            'http://reliv.com/some-page'
        ));

        $this->assertFalse($this->validator->isValid(
            'https://reliv.com/some-page'
        ));

        $this->assertFalse($this->validator->isValid(
            '//reliv.com/some-page'
        ));

        $this->assertFalse($this->validator->isValid(
            'ftp://reliv.com/some-page'
        ));

        $this->assertFalse($this->validator->isValid(
            'scp://reliv.com/some-page'
        ));
    }

    public function testValidatorTrue()
    {
        $this->assertTrue($this->validator->isValid(
            'some-page'
        ));
    }

    public function testValidatorWithNonString()
    {
        $this->assertFalse($this->validator->isValid(new \StdClass));
    }
}
