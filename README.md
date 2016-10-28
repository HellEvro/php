# Stampery
 Stampery API for PHP. Notarize all your data using the blockchain!

 [![Latest Stable Version](https://poser.pugx.org/stampery/php/v/stable)](https://packagist.org/packages/stampery/php)
 [![License](https://poser.pugx.org/stampery/php/license)](https://packagist.org/packages/stampery/php)


PHP client library for [Stampery API](https://stampery.com/api), the blockchain-powered, industrial-scale certification platform.

Seamlessly integrate industrial-scale data certification into your own PHP apps. The Stampery API adds a layer of transparency, attribution, accountability and auditability to your applications by connecting them to Stampery's infinitely scalable [Blockchain Timestamping Architecture](https://stampery.com/tech).

## Installation

  1. Install `stampery` into your project using `composer`:

```
composer require stampery/php
```

  2. Go to the [API dashboard](https://api-dashboard.stampery.com), sign up and create a token for your application. It will resemble this:

```
2f6215c7-ad87-4d6e-bf9e-e9f07aa35f1a
```

## Usage (full example)

```php
include('stampery.inc.php');

// Sign up and get your secret token at https://api-dashboard.stampery.com
$s = new Stampery('your-secret-token', 'prod');

$s->on('ready', function($s)
{
  echo("Ready to stamp!\n");
  $digest = $s->hash("Hello, blockchain!");
  $res = $s->stamp($digest);
});

$s->on('proof', function($hash, $proof)
{
  echo("Received proof for hash ".$hash."\n");
  var_dump($proof);
});

$s->start();
```

### Hashing
```php
$digest = $->hash("Hello, blockchain!");
```
### Basic stamping
```php
$stampery->stamp($digest);
```
### File stamping
```php
$file = file_get_contents('/path/to/file.txt');
$digest = $stampery->hash($file);
$stampery->stamp($digest);
```
### Arbitrary object stamping
```php
$data = array(
 "hello" => "world",
 "foo" => "bar"
);
$json = json_encode($data);
$digest = $stampery->hash($json);
$stampery->stamp($digest);
```

## Client libraries for other platforms
- [NodeJS](https://github.com/stampery/node)
- [PHP](https://github.com/stampery/php)
- [Ruby](https://github.com/stampery/ruby)
- [Python](https://github.com/stampery/python)
- [Java](https://github.com/stampery/java)
- [Go](https://github.com/stampery/go)

## Feedback

Ping us at [support@stampery.com](mailto:support@stampery.com) and we will more than happy to help you! 😃

## License

Code released under [the MIT license](https://github.com/stampery/node/blob/master/LICENSE).

Copyright 2015-2016 Stampery, Inc.
