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
            $this->call('getSignature', $resource, $request_time)
        );
        
        //not a GET request, so signature should be the same
        $resource['data'] = array('foo' => 'bar', 'bar' => 'foo');
        $nonGet = array('POST', 'DELETE', 'PUT');
        foreach ($nonGet as $meth) {
            $resource['method'] = $meth;
            $this->assertSame(
                $this->getSignature($request_time, $resource['method'], $resource['endpoint'], $secret), 
                $this->call('getSignature', $resource, $request_time)
            );
        }
        
        //for a GET request signature includes the query params
        $resource['method'] = 'GET';
        $this->assertSame(
            $this->getSignature($request_time, $resource['method'], $resource['endpoint'] . "?foo=bar&bar=foo", $secret), 
            $this->call('getSignature', $resource, $request_time)
        );
        
    }
    
    
    private function getSignature($request_time, $method, $endpoint, $secret)
    {
        $token = preg_replace("/\s+/", "", $request_time) . $method . $endpoint;

        return hash_hmac("sha256", $token, $secret);
    }
    
    private function call($method, $arg1 = '', $arg2 = '', $arg3 = '')
    {
        $method = $this->reflected->getMethod($method);
        $method->setAccessible(true);
        return $method->invoke($this->V, $arg1, $arg2, $arg3);
    }
    
}