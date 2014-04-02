<?php

/**
 * Mock Unirest Class
 * ==================
 * Using the same interface as the main Unirest class,
 * Just returns whatever has been sent to it
 */

class Unirest
{
    public static function request($httpMethod, $url, $body = NULL, $headers = array(), $username = NULL, $password = NULL)
    {
        return array(
            'httpMethod' => $httpMethod,
            'url' => $url,
            'body' => $body,
            'headers' => $headers,
            'username' => $username,
            'password' => $password
        );
    }
}