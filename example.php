<?php

include('stampery.inc.php');

// Sign up and get your secret token at https://api-dashboard.stampery.com
// Please use 'beta' for testing and 'prod' for production
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
