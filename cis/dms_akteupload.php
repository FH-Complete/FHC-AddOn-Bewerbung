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
require_once('../../../config/global.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/person.class.php');
require_once('../../../include/prestudent.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/akte.class.php');
require_once('../../../include/dokument.class.php');
require_once('../../../include/mail.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/dms.class.php');
require_once('../../../include/fotostatus.class.php');
require_once('../../../include/studiensemester.class.php');

header("Content-Type: text/html; charset=utf-8");

session_cache_limiter('none'); //muss gesetzt werden sonst funktioniert der Download mit IE8 nicht
session_start();

$sprache = getSprache();
$p=new phrasen($sprache);

$db = new basis_db();

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

$PHP_SELF = $_SERVER['PHP_SELF'];
echo '<!DOCTYPE HTML>
<html>
		<head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>'.$p->t('bewerbung/fileUpload').'</title>
        <link href="../../../submodules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="../../../skin/fhcomplete.css" rel="stylesheet" type="text/css">
		<script src="../../../include/js/jquery.min.1.11.1.js"></script>
		<script src="../../../submodules/bootstrap/dist/js/bootstrap.min.js"></script>
        <script>
        function showExtensionInfo()
        {

            var typ = $("#dokumenttyp").val();
            var extinfo="";
            if(typ=="Lichtbil")
                extinfo="jpg";
            else
                extinfo="jpg, png, gif, tiff, bmp, pdf, zip, doc, docx";
            $("#extinfo").html("'.$p->t('bewerbung/ExtensionInformation').'"+extinfo);
        }

        $(function() {
          showExtensionInfo()
        });
        </script>
        <style>
        body {
            margin:10px;
        }
        </style>
        </head>
		<body>';

// Benoetigte Dokumente abfragen
$studiensemester = new studiensemester();
$studiensemester->getStudiensemesterOnlinebewerbung();
$stsem_array = array();
foreach($studiensemester->studiensemester AS $s)
	$stsem_array[] = $s->studiensemester_kurzbz;

	$qry = "SELECT DISTINCT studiengang_kz,typ||kurzbz AS kuerzel FROM public.tbl_dokumentstudiengang
				JOIN public.tbl_prestudent USING (studiengang_kz)
				JOIN public.tbl_prestudentstatus USING (prestudent_id)
				JOIN public.tbl_studiengang USING (studiengang_kz)
				WHERE person_id =".$db->db_add_param($person_id, FHC_INTEGER)."
 				AND tbl_prestudentstatus.status_kurzbz = 'Interessent'
 				/*AND tbl_prestudentstatus.studiensemester_kurzbz IN (".$db->implode4SQL($stsem_array).")*/
 				ORDER BY kuerzel";

	$benoetigt = array();
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			$benoetigt[] = $row->studiengang_kz;
		}
	}

//Bei Upload eines Dokuments
if(isset($_POST['submitbild']))
{
    $error = false;

    // dms Eintrag anlegen
    if(isset($_POST['fileupload']))
    {
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

        if((in_array($ext,array('jpg','jpeg')) && $_REQUEST['dokumenttyp']=='Lichtbil')
          || ($_REQUEST['dokumenttyp']!='Lichtbil' && in_array($ext, array('zip','pdf','doc','docx','jpg','png','gif','tiff','bmp'))))
        {
            $filename = uniqid();
            $filename.=".".$ext;
            $uploadfile = DMS_PATH.$filename;

            if(move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile))
            {
				/*
                if(!@chgrp($uploadfile,'dms'))
                    echo 'CHGRP failed';
                if(!@chmod($uploadfile, 0774))
                    echo 'CHMOD failed';
                //exec('sudo chown wwwrun '.$uploadfile);
				*/
                //Wenn Akte mit DMS-ID vorhanden, dann neue DMS-Version hochladen
                $akte = new akte();
                $version='0';
                $dms_id='';
                if($akte->getAkten($_GET['person_id'], $_REQUEST['dokumenttyp']))
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
				
                $dms->dms_id=$dms_id;
                $dms->version=$version;
                $dms->kategorie_kurzbz=$kategorie_kurzbz;

                $dms->insertamum=date('Y-m-d H:i:s');
                $dms->insertvon = 'online';
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
        else
        {
            echo '<span class="error">'.$p->t('bewerbung/falscherDateityp').'</span>';
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

		if($akte->getAkten($_GET['person_id'], $_REQUEST['dokumenttyp']))
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
        if($_REQUEST['dokumenttyp']=='Maturaze')
			$titel = $p->t('bewerbung/maturazeugnis').".".$extension;


		$akte->dokument_kurzbz = $_REQUEST['dokumenttyp'];
        $akte->bezeichnung = mb_substr($_FILES['file']['name'], 0, 32);
		$akte->person_id = $_GET['person_id'];
		//$akte->inhalt = base64_encode($content);
		$akte->mimetype = $_FILES['file']['type'];
		$akte->erstelltam = date('Y-m-d H:i:s');
		$akte->gedruckt = false;
		$akte->titel = $titel;
		//$akte->bezeichnung = $dokument->bezeichnung;
		$akte->updateamum = date('Y-m-d H:i:s');
		$akte->updatevon = 'online';
		$akte->insertamum = date('Y-m-d H:i:s');
		$akte->nachgereicht = false;
		$akte->anmerkung = '';
		$akte->insertvon = 'online';
		$akte->uid = '';
        $akte->dms_id = $dms_id;


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
    					$fs->insertvon = 'online';
    					$fs->updateamum = date('Y-m-d H:i:s');
    					$fs->updatevon = 'online';
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
        //Wenn nach dem Abschicken einer Bewerbung ein Dokument hochgeladen wird, wird ein Infomail verschickt
		
		$abgeschickt = array();
		$prestudent= new prestudent();
		$prestudent->getPrestudenten($person_id);

		// Beim verschicken der Infomail wird auch das vorvorige Studiensemester hinzugefügt, damit auch Infomails für Studiensemester verschickt werden, für die man sich nicht mehr bewerben aber noch Dokumente hochladen kann. 
		if (isset($stsem_array[0]))
			array_unshift($stsem_array, $studiensemester->jump($stsem_array[0],-2));

		foreach($prestudent->result as $prest)
		{
			$prestudent2 = new prestudent();
			$prestudent2->getPrestudentRolle($prest->prestudent_id,'Interessent');
			foreach($prestudent2->result AS $row)
			{
				if(in_array($row->studiensemester_kurzbz, $stsem_array))
				{
					if($row->bestaetigtam!='' && in_array($prest->studiengang_kz, $benoetigt))
					{
						sendDokumentupload($prest->studiengang_kz,$dokument->dokument_kurzbz,$row->orgform_kurzbz,$row->studiensemester_kurzbz,$row->prestudent_id,$dms_id);
					}
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
	$dokument->getAllDokumenteForPerson($person_id, true);
	$akzeptiert = new dokument();

	echo '	<form method="POST" enctype="multipart/form-data" action="'.$PHP_SELF.'?person_id='.$_GET['person_id'].'&dokumenttyp='.$dokumenttyp.'" class="form-horizontal">
            <div class="form-group">
				<label for="file" class="col-xs-2 control-label">'.$p->t('incoming/dokument').':</label>
				<div class="col-xs-5">
					<input type="file" name="file" class="file"/>
				</div>
			</div>
            <div class="form-group">
				<label for="file" class="col-xs-2 control-label">'.$p->t('incoming/dokumenttyp').':</label>
				<div class="col-xs-5">
					<SELECT name="dokumenttyp" id="dokumenttyp" onchange="showExtensionInfo()" class="form-control">';
				foreach ($dokument->result as $dok)
				{
					if (CAMPUS_NAME=='FH Technikum Wien')// An der FHTW koennen auch akzeptierte Dokumente hochgeladen werden, wenn noch keine Akte dazu existiert
					{
						$akte = new akte;
						$akte->getAkten($person_id, $dok->dokument_kurzbz);
						if (count($akte->result)==0)
						{
							$selected=($dokumenttyp == $dok->dokument_kurzbz)?'selected':'';
							echo '<option '.$selected.' value="'.$dok->dokument_kurzbz.'" >'.$dok->bezeichnung_mehrsprachig[$sprache]."</option>\n";
						}
					}
					else 
					{
						if (!$akzeptiert->akzeptiert($dok->dokument_kurzbz,$person_id))
						{
							$selected=($dokumenttyp == $dok->dokument_kurzbz)?'selected':'';
							echo '<option '.$selected.' value="'.$dok->dokument_kurzbz.'" >'.$dok->bezeichnung_mehrsprachig[$sprache]."</option>\n";
						}
					}
				}
	echo '			</select>
                </div>
            </div>
            <input type="submit" name="submitbild" value="Upload" class="btn btn-default">
            <p class="help-block"><span id="extinfo"></span></p>
            <input type="hidden" name="kategorie_kurzbz" id="kategorie_kurzbz" value="Akte">
            <input type="hidden" name="fileupload" id="fileupload">
		</form>';

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

// Sendet eine Email an die Assistenz, dass ein neues Dokument hochgeladen wurde
function sendDokumentupload($empfaenger_stgkz,$dokument_kurzbz,$orgform_kurzbz,$studiensemester_kurzbz,$prestudent_id,$dms_id)
{
	global $person_id, $p;
	
	//Array fuer Mailempfaenger. Vorruebergehende Loesung. Kindlm am 28.10.2015
	$empf_array = array();
	if(defined('BEWERBERTOOL_UPLOAD_EMPFAENGER'))
		$empf_array = unserialize(BEWERBERTOOL_UPLOAD_EMPFAENGER);
	
	$person = new person();
	$person->load($person_id);
	$dokumentbezeichnung = '';

	$studiengang = new studiengang();
	$studiengang->load($empfaenger_stgkz);
	$typ = new studiengang();
	$typ->getStudiengangTyp($studiengang->typ);

	$email = $p->t('bewerbung/emailDokumentuploadStart');
	$email.= '<br><table style="font-size:small"><tbody>';
	$email.= '<tr><td><b>'.$p->t('global/studiengang').'</b></td><td>'.$typ->bezeichnung.' '.$studiengang->bezeichnung.($orgform_kurzbz!=''?' ('.$orgform_kurzbz.')':'').'</td></tr>';
	$email.= '<tr><td><b>'.$p->t('global/studiensemester').'</b></td><td>'.$studiensemester_kurzbz.'</td></tr>';
	$email.= '<tr><td><b>'.$p->t('global/name').'</b></td><td>'.$person->vorname.' '.$person->nachname.'</td></tr>';
	$email.= '<tr><td><b>'.$p->t('bewerbung/dokument').'</b></td><td>';
	$akte = new akte;
	$akte->getAkten($person_id,$dokument_kurzbz);
	foreach($akte->result AS $row)
	{
		$dokument = new dokument();
		$dokument->loadDokumenttyp($row->dokument_kurzbz);
		if ($row->insertvon=='online')
		{
			if($row->nachgereicht==true)
				$email.= '- '.$dokument->bezeichnung_mehrsprachig[DEFAULT_LANGUAGE].' -> '.$p->t('bewerbung/dokumentWirdNachgereicht').'<br>';
			else
				$email.= '<a href="'.APP_ROOT.'cms/dms.php?id='.$dms_id.'">'.$dokument->bezeichnung_mehrsprachig[DEFAULT_LANGUAGE].' ['.$row->bezeichnung.']</a><br>';
			$dokumentbezeichnung = $dokument->bezeichnung_mehrsprachig[DEFAULT_LANGUAGE];
		}
	}
	$email.= '</td>';
	$email.= '<tr><td><b>'.$p->t('bewerbung/prestudentID').'</b></td><td>'.$prestudent_id.'</td></tr>';
	$email.= '</tbody></table>';
	$email.= '<br>'.$p->t('bewerbung/emailBodyEnde');

	if(defined('BEWERBERTOOL_MAILEMPFANG') && BEWERBERTOOL_MAILEMPFANG!='')
		$empfaenger = BEWERBERTOOL_MAILEMPFANG;
	elseif(isset($empf_array[$empfaenger_stgkz]))
		$empfaenger = $empf_array[$empfaenger_stgkz];
	else
		$empfaenger = $studiengang->email;

	$mail = new mail($empfaenger, 'no-reply', $p->t('bewerbung/dokumentuploadZuBewerbung',array($dokumentbezeichnung)).' '.$person->vorname.' '.$person->nachname, 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Link vollständig darzustellen.');
	$mail->setHTMLContent($email);
	if(!$mail->send())
		return false;
		else
			return true;

}


?>
</body>
</html>
