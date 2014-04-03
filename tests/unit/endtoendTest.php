<?php

class endtoendTest extends baseTest
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

    public function testGet()
    {
        $payload = array('foo' => 'bar');

        $res = $this->V->getMe(1, $payload);
        $this->assertSame('GET', $res['httpMethod']);
        $this->assertSame($payload, $res['body']);
        $this->assertSame(
            $this->baseConfig['api_endpoint'] . 'v' .$this->baseConfig['api_version'] . '/me/1/?foo=bar',
            $res['url']);
    }
}
