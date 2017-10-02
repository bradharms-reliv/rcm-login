<?php

namespace RcmLogin\Test\RcmLogin\Controller;

use RcmLogin\Controller\PluginController;

require_once __DIR__ . '/../autoload.php';

class PluginControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testRenderInstance()
    {
        $controller = new PluginController([]);

        $result = $controller->renderInstance(1, []);

        $this->assertEquals($result->getTemplate(), 'rcm-login/plugin');
    }
}
