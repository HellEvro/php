<?php

include('stampery.inc.php');

$s = new Stampery('2d4cdee7-38b0-4a66-da87-c1ab05b43768', 'prod');

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
