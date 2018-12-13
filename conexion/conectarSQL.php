<?php
function conectar($server, $user, $passwd, $bd){
//$link = mysql_connect($server,$user,$passwd);

$link = mysqli_connect($server, $user, $passwd, $bd);
//mysql_select_db($bd,$link);

$link->query("SET NAMES 'utf8'");

	return $link;
}
?>