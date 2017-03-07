<?php

ini_set('default_charset', 'utf-8');

phpinfo();

$arr = array('a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5);

echo json_encode($arr);

if( !function_exists('json_encode') ){
  print("gefunden");
} else {
  print("nicht gefunden");
}
?>
