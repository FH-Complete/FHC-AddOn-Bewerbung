<?php
/* Copyright (C) 2012 FH Technikum-Wien
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
 * Authors: 	Manfred Kindl 	<kindlm@technikum-wien.at>
 */

require_once('../../../config/cis.config.inc.php');
require_once('../../../config/global.config.inc.php');
require_once('../bewerbung.config.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/prestudent.class.php');
require_once('../../../include/person.class.php');
require_once('../../../include/organisationseinheit.class.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/datum.class.php');

$sprache = getSprache();
$p = new phrasen($sprache);
$datum = new datum();

$prestudent_id = (isset($_GET['prestudent_id'])?$_GET['prestudent_id']:'');
$studiensemester_kurzbz = (isset($_GET['studiensemester_kurzbz'])?$_GET['studiensemester_kurzbz']:'');

if($prestudent_id=='')
	die ('PreStudent_ID muss übergeben werden');

if($studiensemester_kurzbz=='')
	die ('Studiensemester_kurzbz muss übergeben werden');

$prest = new prestudent();
$prest->load($prestudent_id);

$person = new person();
$person->load($prest->person_id);

$studiengang_kz = $prest->studiengang_kz;
$stg = new studiengang();
$stg->load($studiengang_kz);
$oe = $stg->oe_kurzbz;

$user = get_uid();

//Zugriffsrechte pruefen
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('assistenz',$oe))
	die($rechte->errormsg);

$color = '';
$buttontext = '';
$bestaetigen = 'true';

echo'
	<!DOCTYPE HTML>
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta http-equiv="X-UA-Compatible" content="chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Status bestätigen</title>
	<link rel="stylesheet" type="text/css" href="../../../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
	<script type="text/javascript" src="../../../vendor/jquery/jqueryV1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../../vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
</head>
<body class="bewerbung">
<h2 style="text-align: center;">Status bestätigen</h2>
<div class="container">';

if(isset($_GET['bestaetigen']) && $_GET['bestaetigen']=='true')
{
	// check ob es status schon gibt
	if($prest->load_rolle($prestudent_id, 'Interessent', $studiensemester_kurzbz, '1'))
	{
		if($prest->bestaetigtam=='')
		{
			$prest->bestaetigtam = date('Y-m-d H:i:s');
			$prest->bestaetigtvon = $user;
			$prest->new = false;
			$prest->updateamum = date('Y-m-d H:i:s');
			$prest->updatevon = $user;

			if(!$prest->save_rolle())
				die($p->t('global/fehlerBeimSpeichernDerDaten'));
			else
				echo '<div class="alert alert-success">Status von Prestudent '.$prestudent_id.' <b>'.$person->vorname.' '.$person->nachname.'</b> erfolgreich bestätigt</div>';
		}
		else
			echo '<div class="alert alert-warning">Status von Prestudent '.$prestudent_id.' <b>'.$person->vorname.' '.$person->nachname.'</b> wurde bereits am '.$datum->formatDatum($prest->bestaetigtam, 'd.m.Y').' von User <i>'.$prest->updatevon.'</i> bestätigt</div>';

		$color = '#ec971f';
		$buttontext = 'Statusbestätigung aufheben';
		$bestaetigen = 'false';
	}
}
elseif(isset($_GET['bestaetigen']) && $_GET['bestaetigen']=='false')
{
	// check ob es status schon gibt
	if($prest->load_rolle($prestudent_id, 'Interessent', $studiensemester_kurzbz, '1'))
	{
		if($prest->bestaetigtam!='')
		{
			$prest->bestaetigtam = '';
			$prest->bestaetigtvon = '';
			$prest->new = false;
			$prest->updateamum = date('Y-m-d H:i:s');
			$prest->updatevon = $user;

			if(!$prest->save_rolle())
				die($p->t('global/fehlerBeimSpeichernDerDaten'));
			else
				echo '<div class="alert alert-success">Statusbestätigung von Prestudent '.$prestudent_id.' <b>'.$person->vorname.' '.$person->nachname.'</b> erfolgreich gelöscht</div>';
		}
		else
			echo '<div class="alert alert-warning">Statusbestätigung von Prestudent '.$prestudent_id.' <b>'.$person->vorname.' '.$person->nachname.'</b> ist noch nicht gesetzt</div>';

		$color = '#5cb85c';
		$buttontext = 'Status bestätigen';
		$bestaetigen = 'true';
	}
}

echo '	<table border="0" cellspacing="0" cellpadding="0">
		<tr><td>
			<a href="'.APP_ROOT.'addons/bewerbung/cis/status_bestaetigen.php?prestudent_id='.$prestudent_id.'&studiensemester_kurzbz='.$studiensemester_kurzbz.'&bestaetigen='.$bestaetigen.'" style="font-size: 16px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; border-radius: 3px; -webkit-border-radius: 3px; -moz-border-radius: 3px; background-color: '.$color.'; border-top: 6px solid '.$color.'; border-bottom: 6px solid '.$color.'; border-right: 12px solid '.$color.'; border-left: 12px solid '.$color.'; display: inline-block;">
				'.$buttontext.'
			</a>
		</td></tr>
		</table>';
echo '</div></body>';
?>
