<?php

include('stampery.inc.php');

$s = new Stampery('367c6ec2-5791-4cf5-8094-4bae00c639b4', 'prod');

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
