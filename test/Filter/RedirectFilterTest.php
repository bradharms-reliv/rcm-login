<?php

namespace RcmLogin\Test;

use RcmLogin\Filter\RedirectFilter;

class RedirectFilterTest extends \PHPUnit_Framework_TestCase
{
    /** @var RedirectFilter */
    protected $filter;

    public function setup()
    {
        $this->filter = new RedirectFilter();
    }

    public function testValidatorFalse()
    {
        $this->assertNull($this->filter->filter(
            'http://reliv.com/some-page'
        ));

        $this->assertNull($this->filter->filter(
            'https://reliv.com/some-page'
        ));

        $this->assertNull($this->filter->filter(
            '//reliv.com/some-page'
        ));

        $this->assertNull($this->filter->filter(
            'ftp://reliv.com/some-page'
        ));

        $this->assertNull($this->filter->filter(
            'scp://reliv.com/some-page'
        ));
    }

    public function testValidatorTrue()
    {
        $this->assertNotNull($this->filter->filter(
            'some-page'
        ));
    }

    public function testUrlDecodedFilter()
    {
        $page = '%2Fsome-page';
        $expected = urldecode($page);
        $result = $this->filter->filter($page);
        $this->assertEquals($expected, $result);
    }

    public function testFilterVar()
    {
        $page = 'rel�v';
        $expected = 'relv';
        $result = $this->filter->filter($page);
        $this->assertEquals($expected, $result);
    }
}