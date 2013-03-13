<?php

include "../include/config.inc.php";
include "../include/mpd.class.php";

$mpd = new mpd($config['host'], $config['port'], $config['password']);

if($mpd->connected == FALSE) {
	echo "Error: " .$mpd->errStr;
} else {


echo "<table border=1>
		<tr>
			<td width=25%>Artist :</td>
			<td width=10%>Filetype :</td>
			<td width=35%>Medialink :</td>
			<td width=30%>Title :</td>
		</tr>";

foreach($mpd->playlist as $value) {
	$titleexplode = explode("://",$value['Title']);
	$title = $titleexplode[count($titleexplode) -1];
	$type = $titleexplode[count($titleexplode) -2];
	echo "<tr><td>";
	echo $value['Name'];
	echo "</td><td>";
	echo $type;
	echo "</td><td>";
	echo $value['Title'];
	echo "</td><td>";
	echo $title;
	echo "</td></tr>";
}

echo "</table>";

}

?>