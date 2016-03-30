# Stampery PHP
 Stampery API for PHP. Notarize all your data using the blockchain!

 [![Latest Stable Version](https://poser.pugx.org/stampery/php/v/stable)](https://packagist.org/packages/stampery/php)
 [![License](https://poser.pugx.org/stampery/php/license)](https://packagist.org/packages/stampery/php)

## Getting Started

```
composer require stampery/php
```

```php
$stampery = include('stampery.inc.php');
$stampery = new Stampery('830fa1bf-bee7-4412-c1d3-31dddba2213d');
```

### Arbitrary object stamping
```php
$data = array( 'str' => 'Create a proof of this using the blockchain' );

$stampery->stamp($data);
```
### String stamping
```php
$data = array( 'name' => 'Name of the string' );
$file = file_get_contents('document.txt');

$stampery->stamp($data, $file);
```
### Resource stamping
```php
$data = array();
$file = fopen('document.txt', 'r');

$stampery->stamp($data, $file);
```
### Getting a stamp
```php
$stampery->get($hash);
```
### Checking data integrity
```php
$stampery->checkIntegrity($hash, $data);
```
### Checking file integrity
```php
$stampery->checkIntegrity($hash, $data, $file);
```

You can get your API key [signing up](https://stampery.com/signup) and going to [your account](https://stampery.com/account) -> Apps.

## License

Code released under [the MIT license](https://github.com/stampery/js/blob/master/LICENSE).

Copyright 2015 Stampery
