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

$stampery.on('proof', function(hash, proof){
 echo("Received proof for hash" . $hash . "\n");
 echo("Protocol version: " . $proof[0] . "\n");
 echo("Merkle siblings:"\n");
 var_dump($proof[1]);
 echo("Merkle root: " . $proof[2] . "\n");
 echo("Blockchain: " . array('Bitcoin', 'Ethereum')[$proof[3][0]] . "\n");
 echo("Transaction ID: " . $proof[3][1] . "\n");
});

$stampery.on('ready', function($stampery){
 echo("Stampery is ready to stamp\n");
});

$stampery->start();
```
### Hash stamping
```php
$digest = 'A69F73CCA23A9AC5C8B567DC185A756E97C982164FE25859E0D1DCC1475C80A615B2123AF1F5F94C11E3E9402C3AC558F500199D95B6D3E301758586281DCD26';
$stampery->stamp($digest);
```
### String stamping
```php
$string = 'Hello, Blockchain!';
$digest = $stampery->hash($string);
$stampery->stamp($digest);
```
### File stamping
```php
$file = file_get_contents('/path/to/file.txt');
$digest = $stampery->hash($string);
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
## License

Code released under [the MIT license](https://github.com/stampery/js/blob/master/LICENSE).

Copyright 2015 Stampery
