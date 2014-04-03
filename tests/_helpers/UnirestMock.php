<?php

/**
 * Mock Unirest Class
 * ==================
 * Using the same interface as the main Unirest class,
 * Just returns whatever has been sent to it
 */

class Unirest
{
    public static function get($url, $headers = array(), $body = NULL, $username = NULL, $password = NULL)
    {
        return self::request('GET', $url, $body, $headers, $username, $password);
    }

    public static function post($url, $headers = array(), $body = NULL, $username = NULL, $password = NULL)
    {
        return self::request('POST', $url, $body, $headers, $username, $password);
    }

    public static function put($url, $headers = array(), $body = NULL, $username = NULL, $password = NULL)
    {
        return self::request('PUT', $url, $body, $headers, $username, $password);
    }

    public static function delete($url, $headers = array(), $body = NULL, $username = NULL, $password = NULL)
    {
        return self::request('DELETE', $url, $body, $headers, $username, $password);
    }

    public static function request($httpMethod, $url, $body = NULL, $headers = array(), $username = NULL, $password = NULL)
    {
        if ($httpMethod != 'GET') {
            if (is_array($body) || $body instanceof Traversable) {
                Unirest::http_build_query_for_curl($body, $postBody);
            }
        } elseif (is_array($body)) {
            if (strpos($url, '?') !== false) {
                $url .= "&";
            } else {
                $url .= "?";
            }
            Unirest::http_build_query_for_curl($body, $postBody);
            $url .= urldecode(http_build_query($postBody));
        }

        $response = new \stdClass;
        $response->body = new \stdClass;

        //metadata injection
        $response->body->_meta = array(
            'status' => "SUCCESS",
            'offset' => 0,
            'count' => 20,
            'total' => 21,
            'links' => array()
        );

        //response headers
        $response->headers = array(
            'Status' => '200 OK',
            'Connection' => 'Keep-Alive'
        );

        // Error Triggering via params
        $showError = false;
        foreach ($body as $key => $value) {
            if ($key == 'showError' && $value == '1') {
                $showError = true;
            }
            if ($key == 'showLinks' && $value == '1') {
                $response->body->_meta['links'] = array(
                    'next' => array(
                        'method' => $httpMethod,
                        'uri'    => 'http://someuri.com'
                    )
                );
            }
        }

        if ($showError) {
            $response->headers['Status'] = '404 Not Found';
            $response->body->_meta['status'] = "ERROR";
            $response->body->_meta['httpMethod'] = $httpMethod;
            $response->body->_meta['url'] = $url;
        }
        // End Error triggering

        $response->body->records = array(
            'httpMethod' => $httpMethod,
            'url' => $url,
            'body' => $body,
            'headers' => $headers,
            'username' => $username,
            'password' => $password
        );

        return $response;
    }

    public static function http_build_query_for_curl($arrays, &$new = array(), $prefix = null)
    {
        if (is_object($arrays)) {
            $arrays = get_object_vars($arrays);
        }

        foreach ($arrays AS $key => $value) {
            $k = isset($prefix) ? $prefix . '[' . $key . ']' : $key;
            $new[$k] = $value;
        }
    }
}
