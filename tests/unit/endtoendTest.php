<?php

class endtoendTest extends baseTest
{

    private $baseConfig = array();

    public function _before()
    {
        parent::_before();
        $this->baseConfig = array(
            "api_endpoint"    => 'https://wceaapi.org/',
            "api_version"     => '1.1',
            "response_type"   => 'json'
        );
    }

    public function _after()
    {
        parent::_after();
    }

    public function testGet()
    {
        $this->execute();
    }

    public function testPut()
    {
        $this->execute('edit');
    }

    public function testDelete()
    {
        $this->execute('delete');
    }

    public function testPost()
    {
        $this->execute('add');
    }

    private function execute($type = 'get')
    {
        $verbs = array(
            "get"    => "GET",
            "add"    => "POST",
            "edit"   => "PUT",
            "delete" => "DELETE"
        );

        $baseUrl = $this->baseConfig['api_endpoint'] . 'v' .$this->baseConfig['api_version'];
        $payload = array('foo' => 'bar');

        $res = $this->API->{$type . 'Me'}($payload);
        $qs  = $type == 'get' ? '?foo=bar' : '';
        $this->assertSame($verbs[$type], $res['httpMethod']);
        $this->assertSame($payload, $res['body']);
        $this->assertSame($baseUrl . '/me/' . $qs, $res['url']);
        $this->assertFalse($this->API->getError());
        $this->assertSame(array(), $this->API->getLinks());

        $res = $this->API->{$type . 'MeSomethingElse'}($payload);
        $qs  = $type == 'get' ? '?foo=bar' : '';
        $this->assertSame($verbs[$type], $res['httpMethod']);
        $this->assertSame($payload, $res['body']);
        $this->assertSame($baseUrl . '/me/somethingElse' . $qs, $res['url']);
        $this->assertFalse($this->API->getError());
        $this->assertSame(array(), $this->API->getLinks());

        $payload = array('foo' => 'bar', 'baz' => 'foo');
        $qs  = $type == 'get' ? '?foo=bar&baz=foo' : '';
        $res = $this->API->{$type . 'MeSomething'}($payload);
        $this->assertSame($verbs[$type], $res['httpMethod']);
        $this->assertSame($payload, $res['body']);
        $this->assertSame($baseUrl . '/me/something' . $qs, $res['url']);
        $this->assertFalse($this->API->getError());
        $this->assertSame(array(), $this->API->getLinks());

        //metadata is available
        $payload = array('foo' => 'bar');
        $qs  = $type == 'get' ? '?foo=bar' : '';
        $res = $this->API->{$type . 'Me'}($payload);
        $this->assertSame($verbs[$type], $res['httpMethod']);
        $this->assertSame($payload, $res['body']);
        $this->assertSame($baseUrl . '/me/' . $qs, $res['url']);
        $this->assertFalse($this->API->getError());
        $this->assertSame(array(), $this->API->getLinks());
        $this->assertSame(array(
            'status' => "SUCCESS",
            'offset' => 0,
            'count' => 20,
            'total' => 21,
            'links' => array()
        ), $this->API->getMetadata());

        // Errors are properly caught and sent to the right class property
        $payload = array('showError' => 1);
        $qs  = $type == 'get' ? '?showError=1' : '';
        $res = $this->API->{$type . 'MeSomethingElse'}($payload);
        $err = $this->API->getError();
        $this->assertFalse($res);
        $this->assertSame($baseUrl . '/me/somethingElse' . $qs, $err['url']);

        // HATEOAS links are properly parsed
        $payload = array('showLinks' => 1);
        $res = $this->API->{$type . 'MeSomethingElse'}($payload);
        $this->assertFalse($this->API->getError());
        $this->assertSame(array(
            'next' => array(
                'method' => $verbs[$type],
                'uri'    => 'http://someuri.com'
            )
        ), $this->API->getLinks());

        if ($type == 'get' || $type == 'edit') {

            $payload = array('foo' => 'bar');
            $res = $this->API->{$type . 'Me'}(1, $payload);
            $qs  = $type == 'get' ? '?foo=bar' : '';
            $this->assertSame($verbs[$type], $res['httpMethod']);
            $this->assertSame($payload, $res['body']);
            $this->assertSame($baseUrl . '/me/1/' . $qs, $res['url']);
            $this->assertFalse($this->API->getError());
            $this->assertSame(array(), $this->API->getLinks());

            $payload = array('foo' => 'bar', 'baz' => 'foo');
            $qs  = $type == 'get' ? '?foo=bar&baz=foo' : '';
            $res = $this->API->{$type . 'MeSomething'}(1, $payload);
            $this->assertSame($verbs[$type], $res['httpMethod']);
            $this->assertSame($payload, $res['body']);
            $this->assertSame($baseUrl . '/me/1/something' . $qs, $res['url']);
            $this->assertFalse($this->API->getError());
            $this->assertSame(array(), $this->API->getLinks());

            $payload = array('foo' => 'bar', 'q' => '(something:else)');
            $qs  = $type == 'get' ? '?foo=bar&q=(something:else)' : '';
            $res = $this->API->{$type . 'MeSomethingElse'}(1, $payload);
            $this->assertSame($verbs[$type], $res['httpMethod']);
            $this->assertSame($payload, $res['body']);
            $this->assertSame($baseUrl . '/me/1/somethingElse' . $qs, $res['url']);
            $this->assertFalse($this->API->getError());
            $this->assertSame(array(), $this->API->getLinks());

            // q is properly parsed, if it's supplied as an array
            $payload = array('foo' => 'bar', 'q' => array(
                'a-name' => 'is_something',
                'num[gt]' => 10,
                'ek[eq]' => 'qwerty'
            ));
            $qs  = $type == 'get' ? '?foo=bar&q=(a-name:is_something,num[gt]:10,ek[eq]:qwerty)' : '';
            $res = $this->API->{$type . 'MeSomethingElse'}(1, $payload);
            $this->assertSame($verbs[$type], $res['httpMethod']);
            $this->assertSame($baseUrl . '/me/1/somethingElse' . $qs, $res['url']);
            $this->assertFalse($this->API->getError());
            $this->assertSame(array(), $this->API->getLinks());
        }

    }
}
