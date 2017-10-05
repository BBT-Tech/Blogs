<?php
require_once "config.php";
require_once "PDO_MySQL.class.php";

$link = M("table");
dump($link->select());
dump($link->_sql());

?>