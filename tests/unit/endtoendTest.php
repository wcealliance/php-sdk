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
        $baseUrl = $this->baseConfig['api_endpoint'] . 'v' .$this->baseConfig['api_version'];
        $payload = array('foo' => 'bar');

        $res = $this->V->getMe($payload);
        $this->assertSame('GET', $res['httpMethod']);
        $this->assertSame($payload, $res['body']);
        $this->assertSame($baseUrl . '/me/?foo=bar', $res['url']);
        $this->assertFalse($this->V->getError());
        $this->assertSame(array(), $this->V->getLinks());

        $res = $this->V->getMe(1, $payload);
        $this->assertSame('GET', $res['httpMethod']);
        $this->assertSame($payload, $res['body']);
        $this->assertSame($baseUrl . '/me/1/?foo=bar', $res['url']);
        $this->assertFalse($this->V->getError());
        $this->assertSame(array(), $this->V->getLinks());

        $payload = array('foo' => 'bar', 'baz' => 'foo');
        $res = $this->V->getMeSomething(1, $payload);
        $this->assertSame('GET', $res['httpMethod']);
        $this->assertSame($payload, $res['body']);
        $this->assertSame($baseUrl . '/me/1/something?foo=bar&baz=foo', $res['url']);
        $this->assertFalse($this->V->getError());
        $this->assertSame(array(), $this->V->getLinks());

        $payload = array('foo' => 'bar', 'q' => '(something:else)');
        $res = $this->V->getMeSomethingElse(1, $payload);
        $this->assertSame('GET', $res['httpMethod']);
        $this->assertSame($payload, $res['body']);
        $this->assertSame($baseUrl . '/me/1/somethingElse?foo=bar&q=(something:else)', $res['url']);
        $this->assertFalse($this->V->getError());
        $this->assertSame(array(), $this->V->getLinks());

        // q is properly parsed, if it's supplied as an array
        $payload = array('foo' => 'bar', 'q' => array(
            'a-name' => 'is_something',
            'num[gt]' => 10,
            'ek[eq]' => 'qwerty'
        ));
        $res = $this->V->getMeSomethingElse(1, $payload);
        $this->assertSame('GET', $res['httpMethod']);
        $this->assertSame($baseUrl . '/me/1/somethingElse?foo=bar&q=(a-name:is_something,num[gt]:10,ek[eq]:qwerty)', $res['url']);
        $this->assertFalse($this->V->getError());
        $this->assertSame(array(), $this->V->getLinks());

        // HATEOAS links are properly parsed
        $payload = array('showLinks' => 1);
        $res = $this->V->getMeSomethingElse(1, $payload);
        $this->assertFalse($this->V->getError());
        $this->assertSame(array(
            'next' => array(
                'method' => 'GET',
                'uri'    => 'http://someuri.com'
            )
        ), $this->V->getLinks());

        // Errors are properly caught and sent to the right class property
        $payload = array('showError' => 1);
        $res = $this->V->getMeSomethingElse(1, $payload);
        $err = $this->V->getError();
        $this->assertFalse($res);
        $this->assertSame($baseUrl . '/me/1/somethingElse?showError=1', $err['url']);

        //metadata is available
        $payload = array('foo' => 'bar');
        $res = $this->V->getMe($payload);
        $this->assertSame('GET', $res['httpMethod']);
        $this->assertSame($payload, $res['body']);
        $this->assertSame($baseUrl . '/me/?foo=bar', $res['url']);
        $this->assertFalse($this->V->getError());
        $this->assertSame(array(), $this->V->getLinks());
        $this->assertSame(array(
            'status' => "SUCCESS",
            'offset' => 0,
            'count' => 20,
            'total' => 21,
            'links' => array()
        ), $this->V->getMetadata());
    }
}
