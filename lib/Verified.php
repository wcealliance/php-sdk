<?php

    class Verified
    {
        private $api_key;
        private $api_secret;
        private $config = array();

        /**
         * Constructor
         * Extends the default config array and sets the values of api_key and api_secret
         *
         * @param array $config
         */
        public function __construct($config = array())
        {
            //declare default options
            $defaults = array(
                "api_endpoint"    => 'http://verifiedapi.org/',
                "api_version"     => '1',
                "envelope"        => true,
                "suppress_errors" => false,
                "response_type"   => 'json'
            );
            foreach ($config as $key => $value) {
                //pluck the api key out of the config array
                if ($key == "api_key") {
                    $this->api_key = $value;
                    continue;
                }
                //pluck the api secret out of the config array
                if ($key == "api_secret") {
                    $this->api_secret = $value;
                    continue;
                }
                //overwrite the default value of config item if it exists
                if (isset($defaults[$key])) {
                    $defaults[$key] = $value;
                }
            }

            //store the config back into the class property
            $this->config = $defaults;
        }

        /**
         * Setter for $api_key
         * returns the class itself so calls are chainable
         *
         * @param string $api_key
         * @return $this
         */
        public function setKey($api_key)
        {
            $this->api_key = $api_key;

            return $this;
        }

        /**
         * Setter for $api_secret
         * returns the class itself so calls are chainable
         *
         * @param string $api_secret
         * @return $this
         */
        public function setSecret($api_secret)
        {
            $this->api_secret = $api_secret;

            return $this;
        }

        /**
         * Setter for config items
         * returns the class itself so calls are chainable
         *
         * @param string $key
         * @param string $val
         * @return $this
         */
        public function setConfig($key, $val)
        {
            if (isset($this->config[$key])) {
                $this->config[$key] = $val;
            }

            return $this;
        }

        /**
         * Getter for config items
         *
         * @param string $key
         * @return mixed
         */
        public function getConfig($key)
        {
            if (isset($this->config[$key])) {
                return $this->config[$key];
            }

            return null;
        }

        /**
         * Magic method to catch non-existent methods and channel them into useful method calls
         * via the API
         *
         * @param string $method
         * @param array $args
         * @return mixed
         */
        public function __call($method, $args)
        {
            return $this->call($method, $args);
        }

        /**
         * Workhorse method that processes the methods intercepted by the __call magic method
         *
         * @param string $method
         * @param array $args
         * @return mixed
         */
        protected function call($method, $args)
        {
            // if the method exists, short out here
            if (method_exists($this, $method)) {
                return call_user_func_array($this->$method, $args);
            }

            $endpoint = $this->parseMethodCall($method);
            if ($endpoint !== false) {

            } else {
                throw new Exception("Invalid method signature, method does not exist");
            }
        }

        //=============================
        // Private Methods
        //=============================

        /**
         * Signs requests as required by the API
         *
         * @param string $verb
         * @param string $endpoint
         * @param string $request_time
         * @return string
         */
        private function getSignature($verb, $endpoint, $request_time)
        {
            $token = preg_replace("/\s+/", "", $request_time) . strtoupper($verb) . $endpoint;

            return hash_hmac("sha256", $token, $this->api_secret);
        }

        /**
         * Method call parser that converts a method call into a REST resource endpoint
         *
         * @param string $method
         * @return array
         */
        private function parseMethodCall($method)
        {
            $ret = false;
            $verbs = array(
                "get"    => "GET",
                "add"    => "POST",
                "edit"   => "PUT",
                "delete" => "DELETE"
            );
            //split the camelcase
            $words = preg_split("/(?<=[a-z])(?=[A-Z])/x", $method);

            //first word is verb
            if (isset($verbs[strtolower($words[0])])) {
                $ret["method"] = $verbs[strtolower($words[0])];
                //move to next word
                array_shift($words);
                //this word should be the main resource
                if (isset($words[0])) {
                    $ret["endpoint"] = $this->config['api_endpoint'].
                        'v' . $this->config['api_version'] . "/" .
                        strtolower($words[0]) . "/";
                }
                //move to next word
                array_shift($words);
                $subResource = implode("", $words);
                if ($subResource != "") {
                    $ret["sub_resource"] = lcfirst($subResource);
                }
            }

            return $ret;
        }

    }
