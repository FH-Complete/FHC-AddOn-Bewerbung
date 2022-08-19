<?php
/* Copyright (C) 2006 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 *
 */

// Oberflaeche zum Upload von Bildern

header("Content-Type: text/html; charset=utf-8");

session_cache_limiter('none'); //muss gesetzt werden sonst funktioniert der Download mit IE8 nicht
session_start();

require_once('../../../config/cis.config.inc.php');
require_once('../../../config/global.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/person.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/akte.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/fotostatus.class.php');
require_once('../bewerbung.config.inc.php');

$sprache = getSprache();
$p = new phrasen($sprache);

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

	<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">

	<link rel="stylesheet" type="text/css" href="../../../vendor/components/jqueryui/themes/base/jquery-ui.min.css">
	<link rel="stylesheet" type="text/css" href="../../../vendor/tomazdragar/simplecropper/css/jquery.Jcrop.css">
	<link rel="stylesheet" type="text/css" href="../../../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">

	<script type="text/javascript" src="../../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../../include/js/jquery.ui.datepicker.translation.js"></script>
	<script type="text/javascript" src="../../../vendor/jquery/sizzle/sizzle.js"></script>
	<script type="text/javascript" src="../../../vendor/tapmodo/Jcrop/js/Jcrop.min.js"></script>
	<script type="text/javascript" src="../../../vendor/tomazdragar/simplecropper/scripts/jquery.SimpleCropper.js"></script>
	<script type="text/javascript" src="../../../vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>

	<title>'.$p->t('profil/Bildupload').'</title>
	<script>
	// Reloaded die Parent-Seite
	function reloadParent()
	{
		var loc = window.opener.location;
		window.opener.location = "bewerbung.php?active=dokumente";
		window.close();
	}

	$("document").ready(function() {

		$("#saveimgbutton").click(function() {
			//src und person_id von hidden input feldern
			var img = document.getElementById("croppingdiv").getElementsByTagName("img")[0];
			//var src = (img.src).substring(22, (img.src).length);
			var src = (img.src).replace(/^data:.*,/,""); //Entfernt den data-string (data:image/png;base64,) vom Beginn des Codes damit nur der reine base64 Code zurueckgegeben wird
			var person_id = document.getElementById("person_id");
			var person_idValue = person_id.getAttribute("value");

			//in crop.php wird das bild verarbeitet und abgespeichert
			$.post("crop.php", {src:src, person_idValue:person_idValue, orig_src:orig_img, img_name:img_name, img_type:img_type}, function() {});

			setTimeout(reloadParent, 1000);
		});
	});
	</script>
	<style type="text/css">
	.cropme
	{
		width: 300px;
		height: 400px;
		margin:10px;
		vertical-align: middle;
		cursor:pointer;
		font-size: 20px;
		font-family: Helvetica, Arial, sans-serif;
		color: #ffffff;
		text-decoration: none;
		background-color: #5cb85c;
		display: inline-block;
		cursor:pointer;
		line-height: 400px;
	}
	.cropme:hover
	{
		width: 300px;
		height: 400px;
		margin:10px;
		vertical-align: middle;
		cursor:pointer;
		font-size: 20px;
		font-family: Helvetica, Arial, sans-serif;
		color: #ffffff;
		text-decoration: none;
		background-color: #4cae4c;
		display: inline-block;
		cursor:pointer;
		line-height: 400px;
	}
	</style>
</head>
<body style="text-align: center">
<h1>'.$p->t('profil/Bildupload').'</h1>';

function resize($filename, $width, $height)
{
		$ext = explode('.',$_FILES['bild']['name']);
		$ext = strtolower($ext[count($ext)-1]);

		// Hoehe und Breite neu berechnen
		list($width_orig, $height_orig) = getimagesize($filename);

		if ($width && ($width_orig < $height_orig))
		{
		   $width = ($height / $height_orig) * $width_orig;
		}
		else
		{
		   $height = ($width / $width_orig) * $height_orig;
		}

		$image_p = imagecreatetruecolor($width, $height);

		$image = imagecreatefromjpeg($filename);

		//Bild nur verkleinern aber nicht vergroessern
		if($width_orig>$width || $height_orig>$height)
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
		else
			$image_p = $image;

		imagejpeg($image_p, $filename, 80);

		@imagedestroy($image_p);
		@imagedestroy($image);
}

if(isset($_GET['person_id']))
	$person_id = $_GET['person_id'];
else
	die($p->t('global/fehlerBeiDerParameteruebergabe'));

if(!isset($_SESSION['bewerbung/personId']))
	die($p->t('global/keineBerechtigungFuerDieseSeite'));

if($person_id!=$_SESSION['bewerbung/personId'])
	die($p->t('global/keineBerechtigungFuerDieseSeite'));

$fs = new fotostatus();
if($fs->akzeptiert($person_id))
	die($p->t('profil/profilfotoUploadGesperrt'));

echo $p->t('bewerbung/BilduploadInfotext',array($p->t('dms_link/bildRichtlinien'))).'<br><br>';
echo '<div class="simple-cropper-images">
	'.$p->t('bewerbung/fotoAuswaehlen', $person_id).'
		<center>
			<div class="cropme" id="croppingdiv">
				Datei auswählen
			</div>
		</center>
		<script>
			// Init Simple Cropper
			$(".cropme").simpleCropper();
		</script>
	</div>
	<input type="button" name="submitbild" id="saveimgbutton" value="'.$p->t('profil/bildSpeichern').'" class="btn btn-default">
	<input type="hidden" id="person_id" value="'.$person_id.'" />';

if (isset($_POST['src'])) {
	$src = $_POST['src'];
	echo $src;
}
?>
</body>
</html>
