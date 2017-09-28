<?php
/**
 * PluginControllerTest.php
 *
 * LongDescHere
 *
 * PHP version 5
 *
 * @category  Reliv
 * @package   RcmLogin\Test\RcmLogin\Controller
 * @copyright 2014 Reliv International
 * @license   License.txt New BSD License
 * @version   GIT: <git_id>
 * @link      https://github.com/reliv
 */

namespace RcmLogin\Test\RcmLogin\Controller;

use RcmLogin\Controller\PluginController;
use RcmUser\User\Entity\User;
use Zend\Authentication\Result;

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
