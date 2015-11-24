# php
 Stampery API for PHP. Notarize all your data using the blockchain!

 [![Latest Stable Version](https://poser.pugx.org/pugx/badge-poser/version.svg)](https://packagist.org/packages/stampery/php)
 [![License](https://poser.pugx.org/pugx/badge-poser/license.svg)](https://packagist.org/packages/stampery/php)

 ## Get Started

```
composer require stampery/php
```

```javascript
$stampery = include('stampery.inc.php');
$stampery = new Stampery('830fa1bf-bee7-4412-c1d3-31dddba2213d');
```

### Arbitrary object stamping
```javascript
$data = array( 'str' => 'Create a proof of this using the blockchain' );

$stampery->stamp($data);
```
### String stamping
```javascript
$data = array( 'name' => 'Name of the string' );
$file = file_get_contents('document.txt');

$stampery->stamp($data, $file);
```
### Resource stamping
```javascript
$data = array();
$file = fopen('document.txt', 'r');

$stampery->stamp($data, $file);
```
### Getting a stamp
```javascript
$stampery->get($hash);
```

You can get your API key [signing up](https://stampery.com/signup) and going to [your account](https://stampery.com/account) -> Apps.

## License

Code released under [the MIT license](https://github.com/stampery/js/blob/master/LICENSE).

Copyright 2015 Stampery
