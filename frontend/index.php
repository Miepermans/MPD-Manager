<?php

include "../include/dbinfo.inc.php";
include "../include/config.inc.php";
include "../include/mpd.class.php";

$VolumeVar = "";
$muted = "0";

$mpd = new mpd($config['host'], $config['port'], $config['password']);

$VolumeVar = $mpd->volume;
$MuteButton = "";
//$MuteButton = '<a href="index.php?action=mute" class="btn btn-primary btn-small"><i class="icon-white icon-volume-off"></i></a>';

if(isset($_GET['action'])) {
	switch($_GET['action']) {
	case 'play':
		$mpd->Play(); header('Location: ./#current'); break;
	case 'pause':
		$mpd->Pause(); header('Location: ./#current'); break;
	case 'previous':
		$mpd->Previous(); header('Location: ./#current'); break;
	case 'next':
		$mpd->Next(); header('Location: ./#current'); break;
	case 'voldown':
		$mpd->AdjustVolume('-'.$config['volumeSteps']); header('Location: ./#current');
		$VolumeVar = $mpd->volume; break;
	case 'volup':
		$mpd->AdjustVolume('+'.$config['volumeSteps']); header('Location: ./#current');
		$VolumeVar = $mpd->volume; break;
	case 'goto':
		$mpd->SkipTo($_GET['item']); header('Location: ./#current'); break;
	case 'delete':
		$mpd->PLRemove($_GET['item']); header('Location: ./#current'); break;
	case 'repeat':
		if($mpd->repeat == 0) {
                	$mpd->SetSingle(1);
			$mpd->SetRepeat(1); 
			header('Location: ./#current');
			break;
		} else {
			$mpd->SetSingle(0);
			$mpd->SetRepeat(0);
			header('Location: ./#current');
			break;
		}
	//case 'mute':
	//	$mpd->AdjustVolume(-100);
	//	$MuteButton = '<a href="index.php?action=mute" class="btn btn-danger btn-small"><i class="icon-white icon-volume-off"></i></a>'; break;

	}
} else {
	$action = "";
}

if(isset($_POST['mediaurl'])) {
	$mpd->PLAdd($_POST['mediaurl']);
}

if(isset($_FILES["uploadedfile"]["type"])) {

  if ((($_FILES["uploadedfile"]["type"] == "audio/mp3")||($_FILES["uploadedfile"]["type"] == "audio/wav"))&& ($_FILES["uploadedfile"]["size"] < 20000000)) {
 	$target_path = $config['musicdir'];

	$targetnametemp = str_replace(array(' ', '  ', '`','"', "'"), '-', $_FILES['uploadedfile']['name']);

	$targetname = strtolower($targetnametemp);

	$target_path = $target_path . basename( $targetname); 

	if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)) {
	} else{
    	echo "There was an error uploading the file, please try again!";
	}
	//$filenameshort = explode("." , $_FILES['uploadedfile']['name']);
	//$mpd->PLAdd($filenameshort[0]);
	//echo $targetname;
	$mpd->DBRefresh();
	$mpd->PLAdd($targetname);
  }
}

	if($mpd->repeat == 0) {
			$repeatbutton = '<a href="index.php?action=repeat"><button class="btn btn-primary btn-small"><i class="icon-white icon-retweet"></i></button></a>';
	} else {
			$repeatbutton = '<a href="index.php?action=repeat"><button class="btn btn-info btn-small"><i class="icon-white icon-retweet"></i></button></a>';
	}

        switch($mpd->state) {
                case 'play':
                        $status = 'playing';
                        $badge = '<span class="badge badge-success">'.$status.'</span>';
                        $playpause = '<a href="index.php?action=pause"><button class="btn btn-primary btn-small"><i class="icon-white icon-pause"></i></button></a>'; break;
                case 'pause':
                        $status = 'paused';
                        $badge = '<span class="badge badge-warning">'.$status.'</span>';
                        $playpause = '<a href="index.php?action=play"><button class="btn btn-primary btn-small"><i class="icon-white icon-play"></i></button></a>'; break;
                default:
                        $status = 'stopped';
                        $badge = '<span class="badge badge-important">'.$status.'</span>';
                        $playpause = '<a href="index.php?action=play"><button class="btn btn-primary btn-small"><i class="icon-white icon-play"></i></button></a>'; break;
        }

$VolumeBar = '<span class="badge-spec badge-blue">Vol : '.$mpd->volume.'</span>';

include_once "head.html";

if($mpd->connected == FALSE) {
	echo "Error: " .$mpd->errStr;
}

echo '
    <div class="container">
    <h1>Current Playlist</h1>
';

echo "<table class='table table-condensed table-striped'>
		<thead>
		<tr>
			<th width=5%></th>
			<th width=25%>Artist :</th>
			<th width=45%>Medialink :</th>
			<th width=20%>Title :</th>
			<th width=5%></th>
		</tr>
		</thead>
		<tbody>";

foreach($mpd->playlist as $value) {
	$titleexplode = explode("://",$value['Title']);
	$title = $titleexplode[count($titleexplode) -1];
	$item = $value['Pos'];

	if ($item == $mpd->current_track_id) {
		echo "<tr class='info'><td>";
	} else {
		echo "<tr><td>";
	}
	echo "<a href='index.php?action=goto&item=$item'><button class='btn btn-primary btn-mini'><i class='icon-white icon-play'></i></button></a>";
	echo "</td><td>";
	if(isset($value['Artist'])){
	  echo $value['Artist'];
	} else {
	  if(isset($value['Name'])) {
	    echo $value['Name'];
          }
	}
	echo "</td><td>";
	echo $value['file'];
	echo "</td><td>";
	echo $title;
	echo "</td><td>";
	echo "<a href='index.php?action=delete&item=$item' class='btn btn-danger btn-mini'><i class='icon-white icon-trash'></i></a>";
	echo "</td></tr>";
}

echo "</tbody></table>";

echo '
	</div> <!-- /container -->
';
 
 include_once "footer.html";
 ?>
