<?php

include('stampery.inc.php');

$s = new Stampery('830fa1bf-bee7-4412-c1d3-31dddba2213d', 'prod');

try {

  $hash = $s->stamp(array('name' => 'Stamped data', 'foo' => 'bar'));
  var_dump($s->get($hash));

  $hash = $s->stamp(array('name' => 'Stamped file'), file_get_contents('README.md'));
  var_dump($s->get($hash));

  $hash = $s->stamp(array('name' => 'Stamped file'), fopen('README.md', 'r'));
  var_dump($s->get($hash));

} catch (Exception $e) {
  echo "API Error: \"",  $e->getMessage(), "\"\n";
}
