<?php

class configTest extends baseTest
{
    private $baseConfig = array();

    public function _before()
    {
        parent::_before();
        $this->baseConfig = array(
            "api_endpoint"    => 'http://wceaapi.org/',
            "api_version"     => '1.1',
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
        $api = new WCEAAPI();
        foreach ($this->baseConfig as $key => $value) {
            $this->assertSame($this->baseConfig[$key], $api->getConfig($key));
        }

        //construction with config
        $tempConfig = array(
            "api_endpoint"    => 'http://example.org/',
            "api_version"     => '3',
            "response_type"   => 'whale'
        );
        $api = new WCEAAPI($tempConfig);
        foreach ($tempConfig as $key => $value) {
            $this->assertSame($tempConfig[$key], $api->getConfig($key));
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
        $api = new WCEAAPI($tempConfig);
        $this->assertNull($api->getConfig('another'));
        $this->assertNull($api->getConfig('api_key'));
        $this->assertNull($api->getConfig('api_secret'));

        //check api keys and secret are stored in private properties
        $reflection = new \ReflectionClass($api);
        $prop = $reflection->getProperty('api_key');
        $prop->setAccessible(true);
        $this->assertSame($tempConfig['api_key'], $prop->getValue($api));
        $prop = $reflection->getProperty('api_secret');
        $prop->setAccessible(true);
        $this->assertSame($tempConfig['api_secret'], $prop->getValue($api));

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

        $this->API->setKey($tempConfig['api_key']);
        $this->API->setSecret($tempConfig['api_secret']);

        $this->API->setConfig('api_endpoint', $tempConfig['api_endpoint']);
        $this->API->setConfig('api_version', $tempConfig['api_version']);
        $this->API->setConfig('response_type', $tempConfig['response_type']);
        $this->API->setConfig('another', $tempConfig['another']);

        //test getters return the right value
        $this->assertSame($tempConfig['api_endpoint'], $this->API->getConfig('api_endpoint'));
        $this->assertSame($tempConfig['api_version'], $this->API->getConfig('api_version'));
        $this->assertSame($tempConfig['response_type'], $this->API->getConfig('response_type'));
        $this->assertNull($this->API->getConfig('another'));

        //test private properties are set properly with the setters
        $reflection = new \ReflectionClass($this->API);
        $prop = $reflection->getProperty('api_key');
        $prop->setAccessible(true);
        $this->assertSame($tempConfig['api_key'], $prop->getValue($this->API));
        $prop = $reflection->getProperty('api_secret');
        $prop->setAccessible(true);
        $this->assertSame($tempConfig['api_secret'], $prop->getValue($this->API));

    }

    public function testCustomHeaders()
    {
        //test all incarnations of reserved headers are not set
        $reserved = array('Request-Time', 'Api-Key', 'Signature', 'Accept', 'Content-Type',
        'Request_Time', 'Api_Key', 'Content_Type',
        'request-time', 'api-key', 'content-type',
        'request_time', 'api_key', 'content_type', 'signature', 'accept',
        'REQUEST_TIME', 'API_KEY', 'CONTENT_TYPE', 'SIGNATURE', 'ACCEPT');
        foreach ($reserved as $r) {
            $this->API->addCustomHeader($r, 'test');
        }

        $headers = $this->API->getCustomHeaders();
        $this->assertSame(0, count($headers));
        foreach ($headers as $key => $value) {
            $this->assertFalse(in_array($key, $reserved));
        }

        //test custom headers can be set and are cased properly
        $customHeaders = array(
            'movie'        => 'Inception',
            'category'     => 'Science Fiction',
            'the_actor'    => 'Leonardo di Caprio',
            'foo-bar'      => 'Does not apply'
        );
        foreach ($customHeaders as $key => $value) {
            $this->API->addCustomHeader($key, $value);
        }
        $headers = $this->API->getCustomHeaders();
        $this->assertSame(count($customHeaders), count($headers));
        foreach ($customHeaders as $key => $value) {
            $originalKey = $key;
            $key         = $this->fixHeaderName($key);
            $this->assertSame($customHeaders[$originalKey], $headers[$key]);
        }

        //test overwriting existing header takes place properly
        $array_keys_to_test = array(
            'the_actor', 'the-actor', 'The_Actor', 'The-Actor',
            'THE_ACTOR', 'THE-ACTOR'
        );
        $i = 0;
        foreach ($array_keys_to_test as $aktt) {
            $this->API->addCustomHeader($aktt, 'Montgomery Burns' . $i);
            $this->assertSame(count($customHeaders), count($headers));
            $headers = $this->API->getCustomHeaders();
            foreach ($customHeaders as $key => $value) {
                if ($key == $aktt) {
                    $key = $this->fixHeaderName($key);
                    $this->assertSame('Montgomery Burns' . $i, $headers[$key]);
                }
            }
            $i++;
        }

        //test deleting all headers
        $this->API->deleteCustomHeaders();
        $headers = $this->API->getCustomHeaders();
        $this->assertSame(0, count($headers));

    }

    private function fixHeaderName($key)
    {
        $keyParts    = preg_split("/[-_]/", $key);
        $key         = implode(' ', $keyParts);
        $key         = str_replace(' ', '-', ucwords(strtolower(trim($key))));

        return $key;
    }

}
