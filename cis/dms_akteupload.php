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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>,
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */

// Oberflaeche zur Aenderung von Beispielen und Upload von Bildern

require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/person.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/akte.class.php');
require_once('../../../include/dokument.class.php');
require_once('../../../include/mail.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/dms.class.php');
require_once('../../../include/fotostatus.class.php');

header("Content-Type: text/html; charset=utf-8");

session_cache_limiter('none'); //muss gesetzt werden sonst funktioniert der Download mit IE8 nicht
session_start();
if (!isset($_SESSION['bewerbung/user']) || $_SESSION['bewerbung/user']=='')
{
    header('registration.php?method=allgemein');
    exit;
}

if(isset($_GET['lang']))
	setSprache($_GET['lang']);

$person_id = isset($_GET['person_id'])?$_GET['person_id']:'';

if(!isset($_SESSION['bewerbung/personId']))
	die($p->t('global/keineBerechtigungFuerDieseSeite'));

if($person_id!=$_SESSION['bewerbung/personId'])
	die($p->t('global/keineBerechtigungFuerDieseSeite'));

$dokumenttyp = (isset($_GET['dokumenttyp']))? $_GET['dokumenttyp'] : '';
$kategorie_kurzbz = isset($_REQUEST['kategorie_kurzbz'])?$_REQUEST['kategorie_kurzbz']:'';
$sprache = getSprache();
$p=new phrasen($sprache);

$PHP_SELF = $_SERVER['PHP_SELF'];
echo "<html>
		<head><title>".$p->t('bewerbung/fileUpload')."</title></head>
		<body>";

//Bei Upload des Bildes
if(isset($_POST['submitbild']))
{
    $error = false;

    // dms Eintrag anlegen
    if(isset($_POST['fileupload']))
    {
        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $filename = uniqid();
        $filename.=".".$ext;
        $uploadfile = DMS_PATH.$filename;


        if(move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile))
        {
            if(!chgrp($uploadfile,'dms'))
                echo 'CHGRP failed';
            if(!chmod($uploadfile, 0774))
                echo 'CHMOD failed';
            exec('sudo chown wwwrun '.$uploadfile);

            $dms = new dms();

            $dms->version='0';
            $dms->kategorie_kurzbz=$kategorie_kurzbz;

            $dms->insertamum=date('Y-m-d H:i:s');
            //$dms->insertvon = $user;
            $dms->mimetype=$_FILES['file']['type'];
            $dms->filename = $filename;
            $dms->name = $_FILES['file']['name'];

            if($dms->save(true))
            {
                $dms_id=$dms->dms_id;

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
    }

	if(isset($_FILES['file']['tmp_name']) && !$error)
	{
		//Extension herausfiltern
    	$ext = explode('.',$_FILES['file']['name']);
        $ext = mb_strtolower($ext[count($ext)-1]);

		$filename = $_FILES['file']['tmp_name'];

		//$fp = fopen($filename,'r');
		//auslesen
		//$content = fread($fp, filesize($filename));
		//fclose($fp);

		$akte = new akte();

		if($akte->getAkten($_GET['person_id'], 'Lichtbil'))
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

		$dokument = new dokument();
		$dokument->loadDokumenttyp($_REQUEST['dokumenttyp']);

		$exts_arr = explode(".",strtolower($_FILES['file']['name']));
		$extension = end($exts_arr);
		$titel = '';

		// da nur 32 zeichen gespeichert werden dürfen, muss anhand vom typ gekürzt werden
		if($_REQUEST['dokumenttyp']=='Lebenslf')
			$titel = $p->t('incoming/lebenslauf').".".$extension;
		if($_REQUEST['dokumenttyp']=='LearnAgr')
			$titel = $p->t('incoming/learningAgreement').".".$extension;
		if($_REQUEST['dokumenttyp']=='Motivat')
			$titel = $p->t('incoming/motivationsschreiben').".".$extension;
		if($_REQUEST['dokumenttyp']=='Zeugnis')
			$titel = $p->t('incoming/zeugnis').".".$extension;
		if($_REQUEST['dokumenttyp']=='Lichtbil')
			$titel = $p->t('incoming/lichtbild').".".$extension;


		$akte->dokument_kurzbz = $_REQUEST['dokumenttyp'];
                $akte->bezeichnung = $_FILES['file']['name'];
		$akte->person_id = $_GET['person_id'];
		//$akte->inhalt = base64_encode($content);
		$akte->mimetype = $_FILES['file']['type'];
		$akte->erstelltam = date('Y-m-d H:i:s');
		$akte->gedruckt = false;
		$akte->titel = $titel;
		//$akte->bezeichnung = $dokument->bezeichnung;
		$akte->updateamum = date('Y-m-d H:i:s');
	//	$akte->updatevon = $user;
		$akte->insertamum = date('Y-m-d H:i:s');
		$akte->nachgereicht = false;
		$akte->anmerkung = '';
	//	$akte->insertvon = $user;
		$akte->uid = '';
                $akte->dms_id = $dms_id;
		$akte->new = true;


        if (!$akte->save())
        {
            echo "<b>" . $p->t('global/fehleraufgetreten') . ": $akte->errormsg</b>";
        }
        else
        {
            echo "<b>" . $p->t('global/erfolgreichgespeichert') . "</b>";
            if($_REQUEST['dokumenttyp']=='Lichtbil')
            {
                // Wenn ein Foto hochgeladen wird dieses auch in die Person speichern
                //groesse auf maximal 101x130 begrenzen
    			$tempname = resize($uploadfile, 101, 130);

    			//in DB speichern
    			//File oeffnen
    			$fp = fopen($tempname,'r');
    			//auslesen
    			$content = fread($fp, filesize($tempname));
    			fclose($fp);
                unset($tempname);
    			//in base64 umrechnen
    			$content = base64_encode($content);

    			$person = new person();
    			if($person->load($_GET['person_id']))
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
    					$fs->insertvon = 'bewerbertool';
    					$fs->updateamum = date('Y-m-d H:i:s');
    					$fs->updatevon = 'bewerbertool';
    					if(!$fs->save(true))
    						echo '<span class="error">Fehler beim Setzen des Bildstatus</span>';
    				}
                    else
                    {
                        echo '<span class="error">Fehler beim Speichern der Person</span>';
                    }
                }
                else
                {
                    echo '<span class="error">Personen nicht gefunden</sapn>';
                }
            }
        }

		echo "<script>

                var loc = window.opener.location;
                window.opener.location = 'bewerbung.php?active=dokumente';
            </script>";
	}
}

if($person_id !='')
{
	$dokument = new dokument();
	$dokument->getAllDokumenteForPerson($person_id);
	echo "	<form method='POST' enctype='multipart/form-data' action='$PHP_SELF?person_id=".$_GET['person_id']."'>
			<table>
				<tr>
					<td>".$p->t('incoming/dokument').":</td>
					<td>
						<input type='file' name='file' />
					</td>
				</tr>
				<tr>
					<td>".$p->t('incoming/dokumenttyp').":</td>
					<td>
					 <SELECT name='dokumenttyp'>";
				foreach ($dokument->result as $dok)
				{
                                    $selected=($dokumenttyp == $dok->dokument_kurzbz)?'selected':'';

                    echo '<option '.$selected.' value="'.$dok->dokument_kurzbz.'" >'.$dok->bezeichnung_mehrsprachig[$sprache]."</option>\n";

				}
	echo "			</select>
					</td>
				</tr>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td><input type='hidden' name='kategorie_kurzbz' id='kategorie_kurzbz' value='Akte'>
                    <td><input type='hidden' name='fileupload' id='fileupload'></td>
					<td><input type='submit' name='submitbild' value='Upload'></td>

				</tr>
			</table>
			</form>";

}
else
{
	echo $p->t('bewerbung/fehlerKeinePersonId');
}

function resize($filename, $width, $height)
{
	$ext = explode('.',$_FILES['file']['name']);
    $ext = mb_strtolower($ext[count($ext)-1]);

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

    $tmpfname = tempnam(sys_get_temp_dir(), 'FHC');

	imagejpeg($image_p, $tmpfname, 80);

    imagedestroy($image_p);
	@imagedestroy($image);
    return $tmpfname;
}

?>
</body>
</html>
