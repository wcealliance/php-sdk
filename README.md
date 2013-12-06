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

```bash
//first include Unirest
require_once '/path/to/unirest-php/lib/Unirest.php';
//then include this
require_once '/path/to/verified-sdk-php/lib/Verified.php';
```
