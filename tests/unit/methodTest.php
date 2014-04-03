<?php

class methodTest extends baseTest
{

    private $reflected;
    
    public function _before()
    {
        parent::_before();
        $this->reflected = new \ReflectionClass($this->V);
    }

    public function _after()
    {
        parent::_after();
    }
    
    public function testSignature()
    {
        $request_time = date('c');
        $resource     = array(
            'method'   => 'GET',
            'endpoint' => '/v1/resourceName',
            'data'     => ''
        );
        $secret = '111111';
        $this->V->setSecret($secret);
        
        $this->assertSame(
            $this->getSignature($request_time, $resource['method'], $resource['endpoint'], $secret), 
            $this->call('getSignature', array($resource, $request_time))
        );
        
        //not a GET request, so signature should be the same
        $resource['data'] = array('foo' => 'bar', 'bar' => 'foo');
        $nonGet = array('POST', 'DELETE', 'PUT');
        foreach ($nonGet as $meth) {
            $resource['method'] = $meth;
            $this->assertSame(
                $this->getSignature($request_time, $resource['method'], $resource['endpoint'], $secret), 
                $this->call('getSignature', array($resource, $request_time))
            );
        }
        
        //for a GET request signature includes the query params
        $resource['method'] = 'GET';
        $this->assertSame(
            $this->getSignature($request_time, $resource['method'], $resource['endpoint'] . "?foo=bar&bar=foo", $secret), 
            $this->call('getSignature', array($resource, $request_time))
        );
        
    }
    
    public function testMethodCallParser()
    {
        $e = $this->V->getConfig('api_endpoint');
        $v = 'v' . $this->V->getConfig('api_version');
        $data = array(
            'getMe' => array(
                'endpoint' => implode("/", array($e . $v, 'me')) . '/'
            ),
            'getMeSomething' => array(
                'endpoint' => implode("/", array($e . $v, 'me')) . '/',
                'sub_resource' => 'something'
            ),
            'getMeSomethingElse' => array(
                'endpoint' => implode("/", array($e . $v, 'me')) . '/',
                'sub_resource' => 'somethingElse'
            ),
            'editMe' => array(
                'endpoint' => implode("/", array($e . $v, 'me')) . '/'
            ),
            'editMeSomewhere' => array(
                'endpoint' => implode("/", array($e . $v, 'me')) . '/',
                'sub_resource' => 'somewhere'
            ),
            'editMeSomewhereElse' => array(
                'endpoint' => implode("/", array($e . $v, 'me')) . '/',
                'sub_resource' => 'somewhereElse'
            ),
            'deleteMe' => array(
                'endpoint' => implode("/", array($e . $v, 'me')) . '/'
            ),
            'deleteMeHere' => array(
                'endpoint' => implode("/", array($e . $v, 'me')) . '/',
                'sub_resource' => 'here'
            ),
            'deleteMeWhereEver' => array(
                'endpoint' => implode("/", array($e . $v, 'me')) . '/',
                'sub_resource' => 'whereEver'
            ),
            'addMe' => array(
                'endpoint' => implode("/", array($e . $v, 'me')) . '/'
            ),
            'addMeThing' => array(
                'endpoint' => implode("/", array($e . $v, 'me')) . '/',
                'sub_resource' => 'thing'
            ),
            'addMeAnotherThing' => array(
                'endpoint' => implode("/", array($e . $v, 'me')) . '/',
                'sub_resource' => 'anotherThing'
            ),
            'getReportThing' => array(
                'endpoint' => implode("/", array($e . $v, 'report')) . '/' . 'thing' . '/',
            ),
            'getReportAnotherThing' => array(
                'endpoint' => implode("/", array($e . $v, 'report')) . '/' . 'anotherThing' . '/',
            ),
            'editReportThing' => array(
                'endpoint' => implode("/", array($e . $v, 'report')) . '/' . 'thing' . '/',
            ),
            'editReportAnotherThing' => array(
                'endpoint' => implode("/", array($e . $v, 'report')) . '/' . 'anotherThing' . '/',
            ),
            'deleteReportThing' => array(
                'endpoint' => implode("/", array($e . $v, 'report')) . '/' . 'thing' . '/',
            ),
            'deleteReportAnotherThing' => array(
                'endpoint' => implode("/", array($e . $v, 'report')) . '/' . 'anotherThing' . '/',
            ),
            'addReportThing' => array(
                'endpoint' => implode("/", array($e . $v, 'report')) . '/' . 'thing' . '/',
            ),
            'addReportAnotherThing' => array(
                'endpoint' => implode("/", array($e . $v, 'report')) . '/' . 'anotherThing' . '/',
            )
        );
        
        foreach ($data as $k => $v) {
            $returned = $this->call('parseMethodCall', array($k));
            $this->assertSame($v['endpoint'], $returned['endpoint']);
            if (isset($v['sub_resource'])) {
                $this->assertSame($v['sub_resource'], $returned['sub_resource']);
            }else{
                $this->assertArrayNotHasKey('sub_resource', $returned);
            }
        }
    }
    
    public function testQParser()
    {
        //should not be touched at all by the method
        $resource = array(
            'data' => 'q=(thing:otherthing, foo[]:bar, bar[gt]:foo)'
        );
        $expected = $resource;
        $this->call('parseQ', array(&$resource));
        $this->assertSame($expected['data'], $resource['data']);
        
        //should not be touched at all by the method
        $resource = array(
            'data' => array(
                'q' => '(thing:otherthing, foo[]:bar, bar[gt]:foo)'
            )
        );
        $expected = $resource;
        $this->call('parseQ', array(&$resource));
        $this->assertSame($expected['data']['q'], $resource['data']['q']);
        
        //should be transformed
        $resource = array(
            'data' => array(
                'q' => array(
                    'thing'   => 'otherthing',
                    'foo[]'   => 'bar',
                    'bar[gt]' => 'foo'
                )
            )
        );
        $expected = preg_replace("/\s+/", "", $expected['data']['q']);
        $this->call('parseQ', array(&$resource));
        $this->assertSame($expected, $resource['data']['q']);
        
        //should be transformed
        $resource = array(
            'data' => array(
                'q' => array(
                    'thing[lt]'   => 'otherthing',
                    'foo[eq]'   => 'bar',
                    'bar[gt]' => 'foo'
                )
            )
        );
        $expected = '(thing[lt]:otherthing,foo[eq]:bar,bar[gt]:foo)';
        $this->call('parseQ', array(&$resource));
        $this->assertSame($expected, $resource['data']['q']);
    }
    
    
    private function getSignature($request_time, $method, $endpoint, $secret)
    {
        $token = preg_replace("/\s+/", "", $request_time) . $method . $endpoint;

        return hash_hmac("sha256", $token, $secret);
    }
    
    private function call($method, $args)
    {
        $re_args = array();
        $method = $this->reflected->getMethod($method);
        $method->setAccessible(true);
        $params = $method->getParameters();
        foreach ($params as $key => $param) { 
            if ($param->isPassedByReference()) { 
                $re_args[$key] = &$args[$key]; 
            } else { 
                $re_args[$key] = $args[$key]; 
            } 
        }
        return $method->invokeArgs($this->V, $re_args);
    }
    
}