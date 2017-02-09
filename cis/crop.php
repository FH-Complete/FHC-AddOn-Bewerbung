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
 * Authors: Manfred Kindl <manfred.kindl@technikum-wien.at>
 *
 */

header("Content-Type: text/html; charset=utf-8");

session_cache_limiter('none'); //muss gesetzt werden sonst funktioniert der Download mit IE8 nicht
session_start();

require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/person.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/akte.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/fotostatus.class.php');
require_once('../../../include/dms.class.php');
require_once('../bewerbung.config.inc.php');

$src = $_POST['src'];
$orig_src = $_POST['orig_src'];
$img_name = $_POST['img_name'];
$img_type = $_POST['img_type'];

$p=new phrasen($sprache);

function resize($filename, $width, $height)
{
	$ext = 'jpg';

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
/**
 * Cuts the string to the given limit minus the stringlength of the placeholderSign and adds the placeholderSign at the end of the string
 * If $keepFilextension is true, the string is checked for a PATHINFO_EXTENSION and the extension is added to the returned string.
 * @param string $string The input string to be cutted
 * @param integer $limit The length of the returned string (including the placeholderSigns)
 * @param string $placeholderSign Optional. Default null. The string to be added at the end of the cutted string.
 * @param bool $keepFilextension. Default false. When set to true the
 * @return string The cutted string with the placeholderSign at the end
 */
function cutString($string, $limit, $placeholderSign = '', $keepFilextension = false)
{
	$offset = strlen($placeholderSign);
	$extension = '';
	if ($keepFilextension)
	{
		$extension = '.'.pathinfo($string, PATHINFO_EXTENSION);
		$offset = $offset + strlen($extension);
	}

	if(strlen($string) > ($limit - $offset))
	{
		return substr($string, 0, ($limit - $offset)).$placeholderSign.$extension;
	}
	else
	{
		return $string;
	}
}

if(isset($_POST['person_idValue']))
	$person_id = $_POST['person_idValue'];
else
	die($p->t('global/fehlerBeiDerParameteruebergabe'));

if(!isset($_SESSION['bewerbung/personId']))
	die($p->t('global/keineBerechtigungFuerDieseSeite'));

if($person_id!=$_SESSION['bewerbung/personId'])
	die($p->t('global/keineBerechtigungFuerDieseSeite'));
			
$fs = new fotostatus();
if($fs->akzeptiert($person_id))
	die($p->t('profil/profilfotoUploadGesperrt'));

$ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
$filename = uniqid();
$filename .= ".".$ext;
$filename_path = DMS_PATH.$filename;
$filecontent = (preg_replace('/^data:.*,/', '', $orig_src));
// Manchmal erzeugt preg_replace null obwohl $orig_src gesetzt ist. Keine Ahnung warum. Dann wird $orig_src mittels PHP gekürzt.
if ($filecontent == '' && $orig_src != '')
{
	$filecontent = substr($orig_src, strpos($orig_src, ',')+1);
}

$newfile = fopen($filename_path, 'w');
fwrite($newfile, base64_decode($filecontent));

if(fclose($newfile))
{
	//Wenn Akte mit DMS-ID vorhanden, dann neue DMS-Version hochladen
	$akte = new akte();
	$version='0';
	$dms_id='';
	if($akte->getAkten($person_id, 'Lichtbil'))
	{
		//erste Akte @todo: Ist auch so in content/akte.php. Kann irrefuehrende Ergebisse liefern, wenn bereits mehrere Akten des selben Typs vorhanden sind.
		if(isset($akte->result[0]))
		{
			$akte = $akte->result[0];
			if ($akte->dms_id!='')
			{
				$dms = new dms();
				$dms->load($akte->dms_id);
					
				$version=$dms->version+1;
				$dms_id=$akte->dms_id;
			}
		}
	}

	$dms = new dms();

	$dms->dms_id = $dms_id;
	$dms->version = $version;
	$dms->kategorie_kurzbz = '';

	$dms->insertamum = date('Y-m-d H:i:s');
	$dms->insertvon = 'online';
	$dms->mimetype = cutString($img_type, 256);
	$dms->filename = cutString($filename, 256, '', true);
	$dms->name = cutString($img_name, 256, '', true);

	if($dms->save(true))
	{
		$dms_id = $dms->dms_id;
	}
	else
	{
		echo $p->t('global/fehlerBeimSpeichernDerDaten');
		$error = true;
	}
}
else
{
	echo $p->t('global/dateiNichtErfolgreichHochgeladen');
	$error = true;
}

//file als png und jpg abspeichern
$tmpfname = tempnam(sys_get_temp_dir(), 'FHC');
file_put_contents($tmpfname, base64_decode($src));
$imageTmp = imagecreatefrompng($tmpfname);
imagejpeg($imageTmp, $tmpfname, 100);

$ext = strtolower(pathinfo($tmpfname, PATHINFO_EXTENSION));

$filename = uniqid();
$filename.=".".$ext;

//profilbild speichern
if(file_exists($tmpfname))
{
	$width=101;
	$height=130;

	//groesse auf maximal 827x1063 begrenzen
	resize($tmpfname, 827, 1063);

	$fp = fopen($tmpfname,'r');
	//auslesen
	$content = fread($fp, filesize($tmpfname));
	fclose($fp);

	$akte = new akte();

	if($akte->getAkten($person_id, 'Lichtbil'))
	{
		if(count($akte->result)>0)
		{
			$akte = $akte->result[0];
			$akte->new = false;
		}
		else
			$akte->new = true;
	}
	else
	{
		$akte->new = true;
	}

	$akte->dokument_kurzbz = 'Lichtbil';
	$akte->person_id = $person_id;
	$akte->inhalt = base64_encode($content);
	$akte->mimetype = "image/png";
	$akte->erstelltam = date('Y-m-d H:i:s');
	$akte->gedruckt = false;
	$akte->titel = cutString($img_name, 32, '', true);
	//$akte->bezeichnung = "Lichtbild gross";
	$akte->updateamum = date('Y-m-d H:i:s');
	$akte->updatevon = 'online';
	$akte->insertamum = date('Y-m-d H:i:s');
	$akte->insertvon = 'online';
	$akte->uid = '';
	$akte->dms_id = $dms_id;

	if(!$akte->save())
	{
		echo "<b>Fehler: $akte->errormsg</b>";
	}

	//groesse auf maximal 101x130 begrenzen
	resize($tmpfname, 101, 130);

	//in DB speichern
	//File oeffnen
	$fp = fopen($tmpfname,'r');
	//auslesen
	$content = fread($fp, filesize($tmpfname));
	fclose($fp);
	//in base64-Werte umrechnen
	$content = base64_encode($content);

	$person = new person();
	if($person->load($person_id))
	{
		//base64 Wert in die Datenbank speichern
		$person->foto = $content;
		$person->new = false;
		if($person->save())
		{
			$fs = new fotostatus();
			$fs->person_id=$person->person_id;
			$fs->fotostatus_kurzbz='hochgeladen';
			$fs->datum = date('Y-m-d');
			$fs->insertamum = date('Y-m-d H:i:s');
			$fs->insertvon = $user;
			$fs->updateamum = date('Y-m-d H:i:s');
			$fs->updatevon = $user;
			if(!$fs->save(true))
				echo '<span class="error">Fehler beim Setzen des Bildstatus</span>';
			else
			{

				echo "<b>Bild wurde erfolgreich gespeichert</b>";
			}
		}
		else
			echo '<b>'.$person->errormsg.'</b><br />';
	}
}

//temporäre files löschen
unlink($tmpfname);
?>
