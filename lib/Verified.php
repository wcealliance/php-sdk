<?php

    namespace Verified;

    class Verified
    {
        private $api_key;
        private $api_secret;
        private $config = array();

        public function __construct($config)
        {
            $defaults = array(
                "envelope"       => true,
                "suppress_errors" => false,
                "response_type"   => 'json'
            );
            foreach ($config as $key => $value) {
                if ($key == "api_key") {
                    $this->api_key = $value;
                    continue;
                }
                if ($key == "api_secret") {
                    $this->api_secret = $value;
                    continue;
                }
                if (isset($defaults[$key])) {
                    $defaults[$key] = $value;
                }
            }
            $this->config = $defaults;
        }

        public function setKey($api_key)
        {
            $this->api_key = $api_key;
        }

        public function setSecret($api_secret)
        {
            $this->api_secret = $api_secret;
        }

        public function setConfig($key, $val)
        {
            if (isset($this->config[$key])) {
                $this->config[$key] = $val;
            }
        }

        public function getConfig($key)
        {
            if (isset($this->config[$key])) {
                return $this->config[$key];
            }

            return null;
        }

    }
