<?php

namespace RcmLogin\Test;

use RcmLogin\Filter\RedirectFilter;
use RcmLogin\Validator\RedirectValidator;

class RedirectFilterTest extends \PHPUnit_Framework_TestCase
{
    /** @var RedirectFilter */
    protected $filter;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $validatorMock;

    public function setup()
    {
        $this->validatorMock = $this->getMockBuilder('RcmLogin\Validator\RedirectValidator')
            ->disableOriginalConstructor()
            ->getMock();

        $this->filter = new RedirectFilter($this->validatorMock);
    }

    protected function tearDown()
    {
        $this->validatorMock = null;
    }

    public function testValidatorFalse()
    {
        $this->validatorMock->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));

        $this->assertNull($this->filter->filter(
            'http://reliv.com/some-page'
        ));
    }

    public function testUrlDecodedFilter()
    {
        $this->validatorMock->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $page = '%2Fsome-page';
        $expected = urldecode($page);
        $result = $this->filter->filter($page);
        $this->assertEquals($expected, $result);
    }

    public function testFilterVar()
    {
        $this->validatorMock->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $page = 'rel�v';
        $expected = 'relv';
        $result = $this->filter->filter($page);
        $this->assertEquals($expected, $result);
    }
}
