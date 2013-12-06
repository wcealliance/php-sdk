Verified API SDK for PHP
================

Communicate with the Verified REST API using PHP

### Install with Composer
If you're using [Composer](https://github.com/composer/composer) to manage
dependencies, you can add the Verified SDK with it.

```javascript
{
  "require" : {
    "verified/verified-sdk-php": "dev-master"
  },
  "minimum-stability": "dev"
}
```

### Install source from GitHub
The Verified SDK requires [Unirest](https://github.com/Mashape/unirest-php) as a dependency.
It also requires PHP `v5.3+`, `cURL` and `mcrypt` extensions for PHP. Download the PHP library from Github, and require in your script like so:

To install the source code:

```bash
$ git clone git@github.com:verified/verified-sdk-php.git
```

And include it in your scripts:

```php
// first include Unirest
require_once '/path/to/unirest-php/lib/Unirest.php';
// then include this
require_once '/path/to/verified-sdk-php/lib/Verified.php';
```

##Usage
The main requirements for running this SDK are `api_key` and `api_secret`. Please make sure you have them before you proceed any further.

The class can be configured in multiple ways,
by sending in a configuration array during class instantiation:
```php
// create a new instance
$config_array = array(
  "api_key"    => 'YOUR_API_KEY',
  "api_secret" => 'YOUR_API_SECRET'
);
$verified = new Verified($config_array);
```
Or by using a more OOP approach:
```php
$verified = new Verified();
$verified->setKey('YOUR_API_KEY')
  ->setSecret('YOUR_API_SECRET')
  ->setConfig('api_version', '1');
```
