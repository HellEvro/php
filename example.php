<?php

include('stampery.inc.php');

// Sign up and get your secret token at https://api-dashboard.stampery.com
$s = new Stampery('user-secret');

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
