<?php

class configTest extends baseTest
{
    private $baseConfig = array();

    public function _before()
    {
        parent::_before();
        $this->baseConfig = array(
            "api_endpoint"    => 'http://verifiedapi.org/',
            "api_version"     => '1',
            "response_type"   => 'json'
        );
    }

    public function _after()
    {
        parent::_after();
    }

    public function testConstruction()
    {
        // construction with no config given
        $v = new Verified();
        foreach ($this->baseConfig as $key => $value) {
            $this->assertSame($this->baseConfig[$key], $v->getConfig($key));
        }

        //construction with config
        $tempConfig = array(
            "api_endpoint"    => 'http://example.org/',
            "api_version"     => '3',
            "response_type"   => 'whale'
        );
        $v = new Verified($tempConfig);
        foreach ($tempConfig as $key => $value) {
            $this->assertSame($tempConfig[$key], $v->getConfig($key));
        }

        //construction with config and extra params
        $tempConfig = array(
            "api_endpoint"    => 'http://example.org/',
            "api_version"     => '3',
            "response_type"   => 'whale',
            "another"         => 'anything',
            "api_key"         => '123456',
            "api_secret"      => '0987654'
        );
        $v = new Verified($tempConfig);
        $this->assertNull($v->getConfig('another'));
        $this->assertNull($v->getConfig('api_key'));
        $this->assertNull($v->getConfig('api_secret'));

        //check api keys and secret are stored in private properties
        $reflection = new \ReflectionClass($v);
        $prop = $reflection->getProperty('api_key');
        $prop->setAccessible(true);
        $this->assertSame($tempConfig['api_key'], $prop->getValue($v));
        $prop = $reflection->getProperty('api_secret');
        $prop->setAccessible(true);
        $this->assertSame($tempConfig['api_secret'], $prop->getValue($v));

    }

    public function testConfigSettersAndGetters()
    {
        $tempConfig = array(
            "api_endpoint"    => 'http://example101.org/',
            "api_version"     => '3.2',
            "response_type"   => 'shark',
            "another"         => 'anything',
            "api_key"         => 'a-123456',
            "api_secret"      => 'a-0987654'
        );

        $this->V->setKey($tempConfig['api_key']);
        $this->V->setSecret($tempConfig['api_secret']);

        $this->V->setConfig('api_endpoint', $tempConfig['api_endpoint']);
        $this->V->setConfig('api_version', $tempConfig['api_version']);
        $this->V->setConfig('response_type', $tempConfig['response_type']);
        $this->V->setConfig('another', $tempConfig['another']);

        //test getters return the right value
        $this->assertSame($tempConfig['api_endpoint'], $this->V->getConfig('api_endpoint'));
        $this->assertSame($tempConfig['api_version'], $this->V->getConfig('api_version'));
        $this->assertSame($tempConfig['response_type'], $this->V->getConfig('response_type'));
        $this->assertNull($this->V->getConfig('another'));

        //test private properties are set properly with the setters
        $reflection = new \ReflectionClass($this->V);
        $prop = $reflection->getProperty('api_key');
        $prop->setAccessible(true);
        $this->assertSame($tempConfig['api_key'], $prop->getValue($this->V));
        $prop = $reflection->getProperty('api_secret');
        $prop->setAccessible(true);
        $this->assertSame($tempConfig['api_secret'], $prop->getValue($this->V));

    }


}