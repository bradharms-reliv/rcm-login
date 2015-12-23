<?php

namespace RcmLogin\Test;

use Rcm\Http\Response;
use RcmLogin\EventListener\Login;

class LoginTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $controllerMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $eventMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $serviceLocatorMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $requestMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $uriMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $filterMock;

    protected function setUp()
    {
        $this->controllerMock = $this->getMockBuilder('RcmLogin\Controller\PluginController')
            ->disableOriginalConstructor()
            ->getMock();

        $this->serviceLocatorMock = $this->getMockBuilder('Zend\ServiceManager\ServiceManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventMock = $this->getMockBuilder('Zend\EventManager\Event')
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock = $this->getMockBuilder('Zend\Http\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $this->uriMock = $this->getMockBuilder('Zend\Uri\Http')
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterMock= $this->getMockBuilder('RcmLogin\Filter\RedirectFilter')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function tearDown()
    {
        $this->controllerMock = null;
        $this->serviceLocatorMock = null;
        $this->eventMock = null;
        $this->requestMock = null;
        $this->filterMock = null;
    }

    public function testLoginSuccessNoConfig()
    {
        $page = '/login';

        $this->eventMock->expects($this->once())
            ->method('getTarget')
            ->will($this->returnValue($this->controllerMock));

        $this->controllerMock->expects($this->once())
            ->method('getServiceLocator')
            ->will($this->returnValue($this->serviceLocatorMock));

        $this->requestMock->expects($this->once())
            ->method('getQuery')
            ->with(
                $this->equalTo('redirect'),
                null
            )
            ->will($this->returnValue(null));

        $this->requestMock->expects($this->once())
            ->method('getUri')
            ->will($this->returnValue($this->uriMock));

        $this->uriMock->expects($this->once())
            ->method('toString')
            ->will($this->returnValue($page));

        $config = array();

        $map = array(
            array('config', true, $config),
            array('request', true, $this->requestMock)
        );

        $this->serviceLocatorMock
            ->method('get')
            ->will($this->returnValueMap($map));

        $listener = new Login($this->filterMock);

        $result = $listener->loginSuccess($this->eventMock);

        $this->assertTrue($result instanceof Response);
        $this->assertEquals($result->getStatusCode(), 302);

        $location = $result->getHeaders()->get('Location');
        $this->assertEquals($location->getFieldValue(), $page);
    }

    public function testLoginSuccessWithConfig()
    {
        $page = '/login';
        $configRedirect = '/some-page';

        $this->eventMock->expects($this->once())
            ->method('getTarget')
            ->will($this->returnValue($this->controllerMock));

        $this->controllerMock->expects($this->once())
            ->method('getServiceLocator')
            ->will($this->returnValue($this->serviceLocatorMock));

        $this->requestMock->expects($this->once())
            ->method('getQuery')
            ->with(
                $this->equalTo('redirect'),
                null
            )
            ->will($this->returnValue(null));

        $this->requestMock->expects($this->never())
            ->method('getUri');

        $this->uriMock->expects($this->never())
            ->method('toString');

        $config['rcmPlugin']['RcmLogin']['defaultSuccessRedirect'] = $configRedirect;

        $map = array(
            array('config', true, $config),
            array('request', true, $this->requestMock)
        );

        $this->serviceLocatorMock
            ->method('get')
            ->will($this->returnValueMap($map));

        $listener = new Login($this->filterMock);

        $result = $listener->loginSuccess($this->eventMock);

        $this->assertTrue($result instanceof Response);
        $this->assertEquals($result->getStatusCode(), 302);

        $location = $result->getHeaders()->get('Location');
        $this->assertEquals($location->getFieldValue(), $configRedirect);
    }

    public function testLoginSuccessWithRedirectQuery()
    {
        $page = '/login';
        $configRedirect = '/some-page';
        $redirectQuery = '/';

        $this->eventMock->expects($this->once())
            ->method('getTarget')
            ->will($this->returnValue($this->controllerMock));

        $this->controllerMock->expects($this->once())
            ->method('getServiceLocator')
            ->will($this->returnValue($this->serviceLocatorMock));

        $this->requestMock->expects($this->once())
            ->method('getQuery')
            ->with(
                $this->equalTo('redirect'),
                null
            )
            ->will($this->returnValue($redirectQuery));

        $this->filterMock->expects($this->once())
            ->method('filter')
            ->with(
                $this->equalTo($redirectQuery)
            )
            ->will($this->returnValue($redirectQuery));

        $this->requestMock->expects($this->never())
            ->method('getUri');

        $this->uriMock->expects($this->never())
            ->method('toString');

        $config['rcmPlugin']['RcmLogin']['defaultSuccessRedirect'] = $configRedirect;

        $map = array(
            array('config', true, $config),
            array('request', true, $this->requestMock)
        );

        $this->serviceLocatorMock
            ->method('get')
            ->will($this->returnValueMap($map));

        $listener = new Login($this->filterMock);

        $result = $listener->loginSuccess($this->eventMock);

        $this->assertTrue($result instanceof Response);
        $this->assertEquals($result->getStatusCode(), 302);

        $location = $result->getHeaders()->get('Location');
        $this->assertEquals($redirectQuery, $location->getFieldValue());
    }
}