<?php
/*
 * Copyright (C) 2006 Technikum-Wien
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA
 * .
 * Authors: Manfred Kindl <manfred.kindl@technikum-wien.at>
 */

// Oberflaeche zur Aenderung von Beispielen und Upload von Bildern
require_once ('../../../config/cis.config.inc.php');
require_once ('../../../config/global.config.inc.php');
require_once ('../../../include/functions.inc.php');
require_once ('../../../include/person.class.php');
require_once ('../../../include/prestudent.class.php');
require_once ('../../../include/benutzerberechtigung.class.php');
require_once ('../../../include/akte.class.php');
require_once ('../../../include/dokument.class.php');
require_once ('../../../include/mail.class.php');
require_once ('../../../include/phrasen.class.php');
require_once ('../../../include/dms.class.php');
require_once ('../../../include/fotostatus.class.php');
require_once ('../../../include/studiensemester.class.php');
require_once ('../../../include/nation.class.php');
require_once ('../../../include/personlog.class.php');
require_once ('../bewerbung.config.inc.php');
require_once ('../include/functions.inc.php');

header("Content-Type: text/html; charset=utf-8");

session_cache_limiter('none'); //muss gesetzt werden sonst funktioniert der Download mit IE8 nicht
session_start();

$sprache = getSprache();
$p = new phrasen($sprache);
$log = new personlog();

$db = new basis_db();

if (! isset($_SESSION['bewerbung/user']) || $_SESSION['bewerbung/user'] == '')
{
	header('registration.php?method=allgemein');
	exit($p->t('bewerbung/sitzungAbgelaufen'));
}

if (isset($_GET['lang']))
	setSprache($_GET['lang']);

$person_id = isset($_GET['person_id']) ? $_GET['person_id'] : '';

if (! isset($_SESSION['bewerbung/personId']))
	die($p->t('global/keineBerechtigungFuerDieseSeite'));

if ($person_id != $_SESSION['bewerbung/personId'])
	die($p->t('global/keineBerechtigungFuerDieseSeite'));

$dokumenttyp = (isset($_REQUEST['dokumenttyp'])) ? $_REQUEST['dokumenttyp'] : '';
$kategorie_kurzbz = isset($_REQUEST['kategorie_kurzbz']) ? $_REQUEST['kategorie_kurzbz'] : '';
$ausstellungsnation = (isset($_POST['ausstellungsnation'])) ? $_POST['ausstellungsnation'] : '';
$error = '';
$message = '';
$dokumenttyp_upload = '';
$detailDiv = '';

$nation = new nation();
$nation->getAll($ohnesperre = true);

$PHP_SELF = $_SERVER['PHP_SELF']; ?>
<!DOCTYPE HTML>
<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title><?php echo $p->t('bewerbung/fileUpload'); ?></title>
		<link rel="stylesheet" type="text/css" href="../../../vendor/components/bootstrap/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="../../../skin/fhcomplete.css">
		<link rel="stylesheet" href="../include/css/croppie.css">
		<script type="text/javascript" src="../../../vendor/components/jquery/jquery.min.js"></script>
		<script type="text/javascript" src="../../../vendor/components/bootstrap/js/bootstrap.min.js"></script>
		<script src="../include/js/croppie.js"></script>
		<script type="text/javascript">
		function showExtensionInfo()
		{
			var typ = $("#dokumenttyp").val();
			var extinfo = "";
			var fileReaderSupport = true;
			if(typ=="Lichtbil")
				extinfo = "jpg";
			else
				extinfo = "jpg, pdf";
			$("#extinfo").html("<?php echo $p->t('bewerbung/ExtensionInformation'); ?>"+extinfo);

			// Check for support of FileReader.
			if ( !window.FileReader || !window.File || !window.FileList || !window.Blob )
				fileReaderSupport = false;

			// Lichtbilder werden mit Croppie zugeschnitten und in imageupload.php hochgeladen
			if (typ == 'Lichtbil' && fileReaderSupport)
			{
				$("#submitimage").show();
				$("#submitfile").hide();
				imageUpload();
			}
			else
			{
				$("#submitimage").hide();
				$("#submitfile").show();
				$(".croppie-container").empty();
			}

			var options = $( "#dokumenttyp option" ).size();
			if (options == 0)
			{
				$("#documentForm").hide();
				$("#infotextVollstaendig").show();
			}
			else
			{
				$("#documentForm").show();
				$("#infotextVollstaendig").hide();
			}
			

			// Enable/Disable Upload wenn Akte vorhanden
// 			var akte_vorhanden = $('select').find(':selected').data('aktevorhanden');
			
		};

		function imageUpload()
		{
			$uploadCrop = $(".croppie-container").croppie({
				enableExif: true,
				enforceBoundary: true,
				viewport: {
					width: 240,
					height: 320
				},
				boundary: {
					width: 400,
					height: 400
				}
			});

			// Empfehlung von https://www.passbildgroesse.de/ sind 827x1063. Das Seitenverhältnis 828x1104 passt aber besser zum FH-Ausweis
			$("#fileselect").on("change", function () { readFile(this); });
			$("#submitimage").on("click", function (ev) 
			{
				// Check ob File gewählt wurde
				if ($('input[type=file]').val() == '')
				{
					$("#messages").empty();
					$("#messages").html(	'<div class="alert alert-danger" id="danger-alert_dms_akteupload">'+
											'<button type="button" class="close" data-dismiss="alert">x</button>'+
											'<strong>No file selected</strong>'+
											'</div>');
				}
				else
				{
					$uploadCrop.croppie("result", {
						type: "base64",
	// 					size: {width: 828, height: 1104}, 
						size: "original", 
						format: 'jpeg',
						backgroundColor: '#DDDDDD'
					}).then(function (resultdata) {
						var src = resultdata;
						var person_id = <?php echo $person_id; ?>;
						var filename = $('input[type=file]').val().split('\\').pop();

						//in imageupload.php wird das Bild verarbeitet und abgespeichert
						$.post(
							"imageupload.php", 
							{src: src, person_id: person_id, img_filename: filename, img_type: 'image/jpeg'}, 
							function(data) 
							{
								if (data.type == "success")
								{
									$("#messages").empty();
									$("#messages").html(	'<div class="alert alert-success" id="success-alert_dms_akteupload">'+
															'<button type="button" class="close" data-dismiss="alert">x</button>'+
															'<strong>'+data.msg+'</strong>'+
															'</div>');
									window.setTimeout(function() 
									{
										$("#success-alert_dms_akteupload").fadeTo(500, 0).slideUp(500, function(){
											$(this).remove();
										});
										location.reload(true);
									}, 1500);
								}
								else if (data.type == "error")
								{
									$("#messages").empty();
									$("#messages").html(	'<div class="alert alert-danger" id="danger-alert_dms_akteupload">'+
															'<button type="button" class="close" data-dismiss="alert">x</button>'+
															'<strong>'+data.msg+'</strong>'+
															'</div>');
								}
							},
							"json"
						);
					});
				}
			});
		};

		$(function() 
		{
			showExtensionInfo();
			showDetails($("#dokumenttyp").val());
			var showAusstellungsdetails = $('select').find(':selected').data('ausstellungsdetails');
			showAusstellungsnation(showAusstellungsdetails);
		});

		function readFile(input) 
		{
 			if (input.files && input.files[0]) 
 	 		{
				var reader = new FileReader();

				reader.onload = function (e) 
				{
					var image = new Image();
					image.src = e.target.result;

					image.onload = function () {
						// Check auf Filetype
						var splittedSource = this.src.split(';'); // base64 String splitten
						var filetype = splittedSource[0];
						if (filetype != 'data:image/jpeg' && filetype != 'data:image/jpg')
						{
							alert("Das Bild muss von Typ .jpg sein");
							return false;
						}
						// Check auf Bildgroeße
						var height = this.height;
						var width = this.width;
						if (height < 320 || width < 240) 
						{
							alert("Das Bild muss mindestens die Auflösung 240x320 Pixel haben.\nBitte wählen Sie ein größeres Bild.");
							return false;
						}
						else
						{
							$(".croppie-container").addClass("ready");
							$uploadCrop.croppie("bind", 
							{
								url: e.target.result
							}).then(function()
							{
								console.log("jQuery bind complete");
							});
						}
					};
					
					
				}
				reader.readAsDataURL(input.files[0]);
			}
			else 
			{
				alert("Sorry - you\'re browser doesn\'t support the FileReader API");
			}
		};

		function showAusstellungsnation(action)
		{
			$("#ausstellungsnation").toggle(action);
			$("#ausstellungsnation").prop("required",action);
			$("#ausstellungsnation").css("margin-top", "1em");

			if (action === true)
				$("#ausstellungsnation").prop("disabled", false);
			else
				$("#ausstellungsnation").prop("disabled", true);
		};


		function checkAusstellungsnation() 
		{
			if ($("#ausstellungsnation").is(":visible") && $("#ausstellungsnation").val() == "")
			{
				$("#ausstellungsnation").addClass("errorAusstellungsnation");
				return false;
			}
			else
			{
				$("#ausstellungsnation").addClass("errorAusstellungsnation");
				return true;
			}

		};

		function showDetails(dokument) 
		{			
			$(".datailDivs").hide();
			$("#details_"+dokument).show();			

			/*if (action === true)
				$("#ausstellungsnation").prop("disabled", false);
			else
				$("#ausstellungsnation").prop("disabled", true);*/

		};

		window.setTimeout(function() 
		{
			$("#success-alert_dms_akteupload").fadeTo(500, 0).slideUp(500, function(){
				$(this).remove();
			});
		}, 1500);

		</script>
		<style>
		body 
		{
			margin:10px;
		}
		.errorAusstellungsnation
		{
			border-color: #a94442;
		}
		</style>
		</head>
		<body>
<?php 
// Benoetigte Dokumente abfragen
$studiensemester = new studiensemester();
$studiensemester->getStudiensemesterOnlinebewerbung();
$stsem_array = array();
foreach ($studiensemester->studiensemester as $s)
	$stsem_array[] = $s->studiensemester_kurzbz;

$qry = "SELECT DISTINCT studiengang_kz,typ||kurzbz AS kuerzel FROM public.tbl_dokumentstudiengang
				JOIN public.tbl_prestudent USING (studiengang_kz)
				JOIN public.tbl_prestudentstatus USING (prestudent_id)
				JOIN public.tbl_studiengang USING (studiengang_kz)
				WHERE person_id =" . $db->db_add_param($person_id, FHC_INTEGER) . "
 				AND tbl_prestudentstatus.status_kurzbz = 'Interessent'
 				/*AND tbl_prestudentstatus.studiensemester_kurzbz IN (" . $db->implode4SQL($stsem_array) . ")*/
 				ORDER BY kuerzel";

$benoetigt = array();
if ($result = $db->db_query($qry))
{
	while ($row = $db->db_fetch_object($result))
	{
		$benoetigt[] = $row->studiengang_kz;
	}
}

// Bei Upload eines Dokuments
if (isset($_POST['submitfile']))
{
	$error = false;
	$message = '';
	// Check, ob ein File gewaelt wurde
	if (!empty($_FILES['file']['tmp_name'])) 
	{
		$dokumenttyp_upload = $_REQUEST['dokumenttyp'];
		
		// Check, ob Akte vorhanden
		$akte = new akte();
		$akte->getAkten($person_id, $dokumenttyp_upload);
		if (!isset($akte->result[0]) || ($akte->result[0]->inhalt == '' && $akte->result[0]->dms_id == ''))
		{
			if ($dokumenttyp_upload != '')
			{
				// DMS-Eintrag erstellen
				if (isset($_POST['fileupload']))
				{
					$ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
						
					// Auf gültige Dateitypen prüfen
					if (($_REQUEST['dokumenttyp'] == 'Lichtbil' && in_array($ext, array(
						'jpg',
						'jpeg'
					))) || ($_REQUEST['dokumenttyp'] != 'Lichtbil' && in_array($ext, array(
						'pdf',
						'jpg',
						'jpeg'
					))))
					{
						$filename = uniqid();
						$filename .= "." . $ext;
						$uploadfile = DMS_PATH . $filename;
						
						if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile))
						{
							/*
							 * if(!@chgrp($uploadfile,'dms'))
							 * echo 'CHGRP failed';
							 * if(!@chmod($uploadfile, 0774))
							 * echo 'CHMOD failed';
							 * //exec('sudo chown wwwrun '.$uploadfile);
							 */
							// Wenn Akte mit DMS-ID vorhanden, wird diese geladen
							// Derzeit soll nur eine Akte pro Typ hochgeladen werden können
							// Daher wird immer ein neuer DMS-Eintrag erstellt
		// 					$akte = new akte();
							$version = '0';
							$dms_id = '';
		
							/*if ($akte->getAkten($_GET['person_id'], $dokumenttyp_upload))
							{
								// erste Akte @todo: Ist auch so in content/akte.php. Kann irrefuehrende Ergebisse liefern, wenn bereits mehrere Akten des selben Typs vorhanden sind.
								if (isset($akte->result[0]))
								{
									$akte = $akte->result[0];
									if ($akte->dms_id != '')
									{
										$dms = new dms();
										$dms->load($akte->dms_id);
										
										$version = $dms->version + 1;
										$dms_id = $akte->dms_id;
									}
								}
							}*/
							
							$dms = new dms();
							//$dms->dms_id = $dms_id;
							$dms->version = $version;
							$dms->kategorie_kurzbz = 'Akte';
							
							$dms->insertamum = date('Y-m-d H:i:s');
							$dms->insertvon = 'online';
							$dms->mimetype = $_FILES['file']['type'];
							$dms->filename = $filename;
							$dms->name = $_FILES['file']['name'];
							
							if ($dms->save(true))
							{
								$dms_id = $dms->dms_id;
							}
							else
							{
								$message .= $p->t('global/fehlerBeimSpeichernDerDaten');
								$error = true;
							}
						}
						else
						{
							$message .= $p->t('global/dateiNichtErfolgreichHochgeladen');
							$error = true;
						}
					}
					else
					{
						$message .= $p->t('bewerbung/falscherDateityp');
						$error = true;
					}
				}
			}
			else
			{
				$message .= $p->t('bewerbung/keinDokumententypUebergeben'); // @todo: Phrase
				$error = true;
			}
			
			if (isset($_FILES['file']['tmp_name']) && ! $error)
			{
				// Extension herausfiltern
				$ext = explode('.', $_FILES['file']['name']);
				$ext = mb_strtolower($ext[count($ext) - 1]);
				
				$filename = $_FILES['file']['tmp_name'];
				
				$akte = new akte();
				
				// Lichtbilder darf es nur einmal geben und werden überschrieben
				// Normale Akten werden für jeden Upload neu angelegt, es sei denn es gibt bereits Eine mit "nachgereicht"==true
				// Dann wird diese überschrieben
				
				// Derzeit soll nur eine Akte pro Typ hochgeladen werden können
				// Daher wird immer eine neue Akte angelegt es sei denn es gibt bereits Eine mit "nachgereicht"==true
				$akte->getAkten($_GET['person_id'], $dokumenttyp_upload);
				if (count($akte->result) > 0)
				{
					// Wenn ein Dokument im Status "nachgereicht" ist, wird der Datensatz aktualisiert
					if ($akte->result[0]->nachgereicht === true)
					{
						$akte = $akte->result[0];
						$akte->new = false;
						$akte->updateamum = date('Y-m-d H:i:s');
						$akte->updatevon = 'online';
					}
					else
					{
						$akte->new = true;
						$akte->insertamum = date('Y-m-d H:i:s');
						$akte->insertvon = 'online';
					}
				}
				else
				{
					$akte->new = true;
					$akte->insertamum = date('Y-m-d H:i:s');
					$akte->insertvon = 'online';
				}
	
				$dokument = new dokument();
				$dokument->loadDokumenttyp($dokumenttyp_upload);
				
				$exts_arr = explode(".", strtolower($_FILES['file']['name']));
				$extension = end($exts_arr);
				$titel = '';
				
				$akte->dokument_kurzbz = $dokumenttyp_upload;
				$akte->titel = cutString($_FILES['file']['name'], 32, '~', true); // Dateiname
				$akte->bezeichnung = cutString($dokument->bezeichnung, 32); // Dokumentbezeichnung
				$akte->person_id = $person_id;
				/*if ($dokumenttyp_upload == 'Lichtbil')
				{
					// Fotos auf maximal 827x1063 begrenzen
					resize($uploadfile, 827, 1063);
					
					$fp = fopen($uploadfile, 'r');
					// auslesen
					$content = fread($fp, filesize($uploadfile));
					fclose($fp);
					
					$akte->inhalt = base64_encode($content);
				}*/
				$akte->mimetype = $_FILES['file']['type'];
				$akte->erstelltam = date('Y-m-d H:i:s');
				$akte->gedruckt = false;
				$akte->nachgereicht = false;
// 				$akte->anmerkung = ''; Auch bei nachträglichem Upload bleibt die Anmerkung erhalten
				$akte->uid = '';
				$akte->dms_id = $dms_id;
				$akte->ausstellungsnation = $ausstellungsnation;
				
				if (! $akte->save())
				{
					$message .= $p->t('global/fehleraufgetreten') . ": $akte->errormsg";
				}
				else
				{
					$message .= $p->t('global/erfolgreichgespeichert');
					// Logeintrag schreiben
					$log->log($person_id, 'Action', array(
						'name' => 'New document uploaded',
						'success' => true,
						'message' => 'Document ' . $akte->bezeichnung . ' "' . $akte->titel . '" uploaded'
					), 'bewerbung', 'bewerbung', null, 'online');

					if ($dokumenttyp_upload == 'Lichtbil')
					{
						// Wenn ein Foto hochgeladen wird, dieses auch in die Person speichern
						// groesse auf maximal 101x130 begrenzen
						$tempname = resize($uploadfile, 240, 320);
						
						// in DB speichern
						// File oeffnen
						$fp = fopen($tempname, 'r');
						// auslesen
						$content = fread($fp, filesize($tempname));
						fclose($fp);
						unset($tempname);
						// in base64 umrechnen
						$content = base64_encode($content);
						
						$person = new person();
						if ($person->load($_GET['person_id']))
						{
							// base64 Wert in die Datenbank speichern
							$person->foto = $content;
							$person->new = false;
							if ($person->save())
							{
								$fs = new fotostatus();
								$fs->person_id = $person->person_id;
								$fs->fotostatus_kurzbz = 'hochgeladen';
								$fs->datum = date('Y-m-d');
								$fs->insertamum = date('Y-m-d H:i:s');
								$fs->insertvon = 'online';
	// 							$fs->updateamum = date('Y-m-d H:i:s');
	// 							$fs->updatevon = 'online';
								if (! $fs->save(true))
									echo '<span class="error">Fehler beim Setzen des Bildstatus</span>';
							}
							else
							{
								echo '<span class="error">Fehler beim Speichern der Person</span>';
							}
						}
						else
						{
							echo '<span class="error">Personen nicht gefunden</span>';
						}
					}
				}
				
				if (! defined('BEWERBERTOOL_SEND_UPLOAD_EMPFAENGER') || BEWERBERTOOL_SEND_UPLOAD_EMPFAENGER)
				{
					// Wenn nach dem Abschicken einer Bewerbung ein Dokument hochgeladen wird, wird ein Infomail verschickt
					$prestudent = new prestudent();
					$prestudent->getPrestudenten($person_id);
					
					// Beim verschicken der Infomail wird auch das vorvorige Studiensemester hinzugefügt, damit auch Infomails für Studiensemester verschickt werden, für die man sich nicht mehr bewerben aber noch Dokumente hochladen kann.
					if (isset($stsem_array[0]))
						array_unshift($stsem_array, $studiensemester->jump($stsem_array[0], - 2));
					
					foreach ($prestudent->result as $prest)
					{
						$prestudent2 = new prestudent();
						$prestudent2->getPrestudentRolle($prest->prestudent_id, 'Interessent');
						foreach ($prestudent2->result as $row)
						{
							if (in_array($row->studiensemester_kurzbz, $stsem_array))
							{
								if ($row->bestaetigtam != '' && in_array($prest->studiengang_kz, $benoetigt))
								{
									sendDokumentupload($prest->studiengang_kz, $dokument->dokument_kurzbz, $row->orgform_kurzbz, $row->studiensemester_kurzbz, $row->prestudent_id, $dms_id);
								}
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
		else 
		{
			$message .= $p->t('bewerbung/akteBereitsVorhanden');
			$error = true;
		}
	}
	else
	{
		$message .= $p->t('bewerbung/keineDateiAusgewaehlt');
		$error = true;
	}
}

$dokumente_abzugeben = getAllDokumenteBewerbungstoolForPerson($person_id);
$akte_vorhanden = array();

foreach ($dokumente_abzugeben as $dok)
{
	$akte = new akte();
	$akte->getAkten($person_id, $dok->dokument_kurzbz);
	if ($dok->anzahl_akten_vorhanden > 0 && isset($akte->result[0]))
	{
		$akte_vorhanden[$dok->dokument_kurzbz] = true;
	}
	else 
		$akte_vorhanden[$dok->dokument_kurzbz] = false;
}

echo '<div class="container" id="messages">';
	
if ($error === false)
{
	echo '<div class="alert alert-success" id="success-alert_dms_akteupload">
	<button type="button" class="close" data-dismiss="alert">x</button>
	<strong>'.$message.'</strong>
	</div>';
}
elseif ($error === true)
{
	echo '<div class="alert alert-danger" id="danger-alert_dms_akteupload">
	<button type="button" class="close" data-dismiss="alert">x</button>
	<strong>'.$p->t('global/fehleraufgetreten').' </strong>'.$message.'
	</div>';
}
echo '</div>';

if ($person_id != '')
{
	echo '
	<form id="documentForm" method="POST" enctype="multipart/form-data" action="' . $PHP_SELF . '?person_id=' . $_GET['person_id'] . '&dokumenttyp=' . $dokumenttyp . '" class="form-horizontal" onsubmit="return checkAusstellungsnation()">
	<div class="container"> <br />
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading"><strong>Upload files</strong></div>
					<div class="panel-body">

							<SELECT name="dokumenttyp" id="dokumenttyp" onchange="showExtensionInfo()" class="form-control">';
	foreach ($dokumente_abzugeben as $dok)
	{
		if ($dok->pflicht === true || check_person_statusbestaetigt($person_id, 'Interessent', '', ''))
		{
			// Studiengänge für die das Dokument benötigt wird
			$benoetigtStudiengang = new dokument();
			$benoetigtStudiengang->getStudiengaengeDokument($dok->dokument_kurzbz, $person_id);
			$ben_kz = array();
			$detailstring = '';
			foreach ($benoetigtStudiengang->result as $row)
			{
					$ben_kz[] .= $row->studiengang_kz;
			}
			// Detailbeschreibungen zu Dokumenten holen
			$details = new dokument();
			$details->getBeschreibungenDokumente($ben_kz, $dok->dokument_kurzbz);
	
			$zaehlerBeschreibungAllg = 0;
			if ($dokumenttyp == $dok->dokument_kurzbz)
			{
				$selected = 'selected="selected"';
				$display = '';
			}
			else 
			{
				$selected = '';
				$display = 'display: none;';
			}
		
			foreach ($details->result as $row)
			{
				$stg = new studiengang();
				$stg->load($row->studiengang_kz);
				
				if ($row->dokumentbeschreibung_mehrsprachig[getSprache()] != '' && $zaehlerBeschreibungAllg == 0)
				{
					$detailstring .= $row->dokumentbeschreibung_mehrsprachig[getSprache()];
					// Allgemeine Dokumentbeschreibung nur einmal ausgeben
					$zaehlerBeschreibungAllg ++;
				}
				if ($row->beschreibung_mehrsprachig[getSprache()] != '')
				{
					if ($detailstring != '')
						$detailstring .= '<br/><hr/>';
					$detailstring .= '<b>'.$stg->kuerzel.'</b>: '.($row->beschreibung_mehrsprachig[getSprache()]);
				}
				else
					$detailstring .= '';
			}
			
			if ($detailstring != '')
				$detailDiv .= '<div id="details_'.$dok->dokument_kurzbz.'" class="datailDivs panel panel-info" style="'.$display.'"><div class="panel-heading">'.$detailstring.'</div></div>';
	
			$event = '	onclick="showAusstellungsnation('.($dok->ausstellungsdetails === true ? 'true' : 'false').'); showDetails(\''.$dok->dokument_kurzbz.'\')" 
						onselect="showAusstellungsnation('.($dok->ausstellungsdetails === true ? 'true' : 'false').'); showDetails(\''.$dok->dokument_kurzbz.'\')"';
	
			if ($akte_vorhanden[$dok->dokument_kurzbz] === true)
				continue;
			else
				echo '<option ' . $selected . ' value="' . $dok->dokument_kurzbz . '" '.$event.' data-ausstellungsdetails="'.($dok->ausstellungsdetails === true ? 'true' : 'false').'">' . $dok->bezeichnung_mehrsprachig[$sprache] . '</option>\n';
		}
	}
	echo '	</select>';
	echo $detailDiv;
	// DropDown für Länderauswahl wenn "ausstellungsdetails" true ist
	$dokumenttypObj = new dokument();
	$dokumenttypObj->loadDokumenttyp($dokumenttyp);
	if ($dokumenttypObj->ausstellungsdetails === true)
		$style = 'style="margin-top: 1em; display: block;" required="required"';
	else 
		$style = 'style="display: none;" disabled="disabled"';
	echo'	<select name="ausstellungsnation" id="ausstellungsnation" class="form-control" '.$style.'>
				<option value="">'. $p->t('bewerbung/bitteAusstellungsnationAuswaehlen') .'</option>
				<option value="A">'.	($sprache=='German'? 'Österreich':'Austria') .'</option>';
				$selected = '';
				foreach ($nation->nation as $nat)
				{
					$selected = ($ausstellungsnation == $nat->code) ? 'selected="selected"' : '';
					echo '<option value="'. $nat->code .'" '. $selected .'>';

					if ($sprache == 'German')
						echo $nat->langtext;
					else
						echo $nat->engltext;

					echo '</option>';
				}
				echo '</select><br>';
				// Container für Bildzuschnitt bei Lichtbild
				echo '<div class="croppie-container"></div>';
				echo'
						<div class="">
							<input id="fileselect" type="file" name="file" class="file" />
						</div><br>
						<input id="submitfile" type="submit" name="submitfile" value="Upload" class="btn btn-labeled btn-primary">
						<input id="submitimage" type="button" name="submitimage" value="Upload" class="btn btn-labeled btn-primary" style="display: none">
						<p class="help-block"><span id="extinfo"></span></p>
						<input type="hidden" name="kategorie_kurzbz" id="kategorie_kurzbz" value="Akte">
						<input type="hidden" name="fileupload" id="fileupload">
					</div>
				</div>
			</div>
		</div>
	</div>

	</form>
	<div id="infotextVollstaendig" style="display: none">
		<div class="container"> <br />
			<div class="row">
				<div class="col-md-12">
					<div class="alert alert-success">	
					<strong>'.$p->t('bewerbung/dokumenteVollstaendig').'</strong>
					</div>
				</div>
			</div>
		</div>
	</div>
';
	
	/*
	 * echo ' <form method="POST" enctype="multipart/form-data" action="'.$PHP_SELF.'?person_id='.$_GET['person_id'].'&dokumenttyp='.$dokumenttyp.'" class="form-horizontal">
	 * <div class="form-group">
	 * <label for="file" class="col-xs-2 control-label">'.$p->t('incoming/dokument').':</label>
	 * <div class="col-xs-5">
	 * <input type="file" name="file" class="file"/>
	 * </div>
	 * </div>
	 * <div class="form-group">
	 * <label for="file" class="col-xs-2 control-label">'.$p->t('incoming/dokumenttyp').':</label>
	 * <div class="col-xs-5">
	 * <SELECT name="dokumenttyp" id="dokumenttyp" onchange="showExtensionInfo()" class="form-control">';
	 * foreach ($dokument->result as $dok)
	 * {
	 * if (CAMPUS_NAME == 'FH Technikum Wien')// An der FHTW koennen auch akzeptierte Dokumente hochgeladen werden, wenn noch keine Akte dazu existiert
	 * {
	 * // $akte = new akte;
	 * // $akte->getAkten($person_id, $dok->dokument_kurzbz);
	 * // if (count($akte->result) == 0)
	 * {
	 * $selected = ($dokumenttyp == $dok->dokument_kurzbz)?'selected':'';
	 * if ($dok->dokument_kurzbz == 'Lichtbil')
	 * echo '<option '.$selected.' value="'.$dok->dokument_kurzbz.'" onclick="window.location.href=\'bildupload.php?person_id='.$person_id.'&dokumenttyp=Lichtbil\'; window.resizeTo(700, 800);" onselect="window.location.href=\'bildupload.php?person_id='.$person_id.'&dokumenttyp=Lichtbil\'; window.resizeTo(700, 800);">'.$dok->bezeichnung_mehrsprachig[$sprache]."</option>\n";
	 * else
	 * echo '<option '.$selected.' value="'.$dok->dokument_kurzbz.'" >'.$dok->bezeichnung_mehrsprachig[$sprache]."</option>\n";
	 * }
	 * }
	 * else
	 * {
	 * // Mehrfachupload moeglich
	 * //if (!$akzeptiert->akzeptiert($dok->dokument_kurzbz,$person_id))
	 * {
	 * $selected=($dokumenttyp == $dok->dokument_kurzbz)?'selected':'';
	 * echo '<option '.$selected.' value="'.$dok->dokument_kurzbz.'" >'.$dok->bezeichnung_mehrsprachig[$sprache]."</option>\n";
	 * }
	 * }
	 * }
	 * echo ' </select>
	 * </div>
	 * </div>
	 * <input type="submit" name="submitfile" value="Upload" class="btn btn-default">
	 * <p class="help-block"><span id="extinfo"></span></p>
	 * <input type="hidden" name="kategorie_kurzbz" id="kategorie_kurzbz" value="Akte">
	 * <input type="hidden" name="fileupload" id="fileupload">
	 * </div></div></div></div></div>
	 * </form>';
	 */
}
else
{
	echo $p->t('bewerbung/fehlerKeinePersonId');
}
function resize($filename, $width, $height)
{
	$ext = explode('.', $_FILES['file']['name']);
	$ext = mb_strtolower($ext[count($ext) - 1]);
	
	// Hoehe und Breite neu berechnen
	list ($width_orig, $height_orig) = getimagesize($filename);
	
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
	
	// Bild nur verkleinern aber nicht vergroessern
	if ($width_orig > $width || $height_orig > $height)
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
function sendDokumentupload($empfaenger_stgkz, $dokument_kurzbz, $orgform_kurzbz, $studiensemester_kurzbz, $prestudent_id, $dms_id)
{
	global $person_id, $p;
	
	// Array fuer Mailempfaenger. Vorruebergehende Loesung. Kindlm am 28.10.2015
	$empf_array = array();
	if (defined('BEWERBERTOOL_UPLOAD_EMPFAENGER'))
		$empf_array = unserialize(BEWERBERTOOL_UPLOAD_EMPFAENGER);
	
	$person = new person();
	$person->load($person_id);
	$dokumentbezeichnung = '';
	
	$studiengang = new studiengang();
	$studiengang->load($empfaenger_stgkz);
	$typ = new studiengang();
	$typ->getStudiengangTyp($studiengang->typ);
	
	$email = $p->t('bewerbung/emailDokumentuploadStart');
	$email .= '<br><table style="font-size:small"><tbody>';
	$email .= '<tr><td><b>' . $p->t('global/studiengang') . '</b></td><td>' . $typ->bezeichnung . ' ' . $studiengang->bezeichnung . ($orgform_kurzbz != '' ? ' (' . $orgform_kurzbz . ')' : '') . '</td></tr>';
	$email .= '<tr><td><b>' . $p->t('global/studiensemester') . '</b></td><td>' . $studiensemester_kurzbz . '</td></tr>';
	$email .= '<tr><td><b>' . $p->t('global/name') . '</b></td><td>' . $person->vorname . ' ' . $person->nachname . '</td></tr>';
	$email .= '<tr><td><b>' . $p->t('bewerbung/dokument') . '</b></td><td>';
	$akte = new akte();
	$akte->getAkten($person_id, $dokument_kurzbz);
	foreach ($akte->result as $row)
	{
		$dokument = new dokument();
		$dokument->loadDokumenttyp($row->dokument_kurzbz);
		if ($row->insertvon == 'online')
		{
			if ($row->nachgereicht == true)
				$email .= '- ' . $dokument->bezeichnung_mehrsprachig[DEFAULT_LANGUAGE] . ' -> ' . $p->t('bewerbung/dokumentWirdNachgereicht') . '<br>';
			else
				$email .= '<a href="' . APP_ROOT . 'cms/dms.php?id=' . $dms_id . '">' . $dokument->bezeichnung_mehrsprachig[DEFAULT_LANGUAGE] . ' [' . $row->bezeichnung . ']</a><br>';
			$dokumentbezeichnung = $dokument->bezeichnung_mehrsprachig[DEFAULT_LANGUAGE];
		}
	}
	$email .= '</td>';
	$email .= '<tr><td><b>' . $p->t('bewerbung/prestudentID') . '</b></td><td>' . $prestudent_id . '</td></tr>';
	$email .= '</tbody></table>';
	$email .= '<br>' . $p->t('bewerbung/emailBodyEnde');
	
	if (defined('BEWERBERTOOL_MAILEMPFANG') && BEWERBERTOOL_MAILEMPFANG != '')
		$empfaenger = BEWERBERTOOL_MAILEMPFANG;
	elseif (isset($empf_array[$empfaenger_stgkz]))
		$empfaenger = $empf_array[$empfaenger_stgkz];
	else
		$empfaenger = $studiengang->email;
	
	$mail = new mail($empfaenger, 'no-reply', $p->t('bewerbung/dokumentuploadZuBewerbung', array(
		$dokumentbezeichnung
	)) . ' ' . $person->vorname . ' ' . $person->nachname, 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Link vollständig darzustellen.');
	$mail->setHTMLContent($email);
	if (! $mail->send())
		return false;
	else
		return true;
}

?>
</body>
</html>
