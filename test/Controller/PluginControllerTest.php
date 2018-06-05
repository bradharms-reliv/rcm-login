<?php

namespace RcmLogin\Test\RcmLogin\Controller;

use PHPUnit\Framework\TestCase;
use RcmLogin\Controller\PluginController;
use Zend\Validator\Csrf;
use \Mockery;

require_once __DIR__ . '/../autoload.php';

class PluginControllerTest extends TestCase
{
    public function testRenderInstance()
    {
        $csrfValidator = Mockery::mock(Csrf::class);
        $csrfValidator->allows()->getHash();

        $unit = new PluginController(
            [],
            $csrfValidator
        );

        $result = $unit->renderInstance(1, []);

        $this->assertEquals($result->getTemplate(), 'rcm-login/plugin');
    }
}
