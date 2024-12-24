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
 * Authors: Karl Burkhart 	<burkhart@technikum-wien.at>
 * 			Manfred Kindl 	<kindlm@technikum-wien.at>
 */

require_once('../../../config/cis.config.inc.php');
require_once('../../../config/global.config.inc.php');
require_once('../bewerbung.config.inc.php');

session_cache_limiter('none'); // muss gesetzt werden sonst funktioniert der Download mit IE8 nicht
session_start();

// Definiert die verwendeten views (Tabreiter)
// Die Tabs werden in der definierten Reihenfolge ausgegeben aber in der Indexreihenfolge geladen
$tabs = array();
if (defined('BEWERBERTOOL_UEBERSICHT_ANZEIGEN') && BEWERBERTOOL_UEBERSICHT_ANZEIGEN)
	$tabs[13] = 'uebersicht';
if (!defined('BEWERBERTOOL_ALLGEMEIN_ANZEIGEN') || BEWERBERTOOL_ALLGEMEIN_ANZEIGEN)
	$tabs[14] = 'allgemein';

$tabs[0]='daten';
$tabs[1]='kontakt';

if (!defined('BEWERBERTOOL_DOKUMENTE_ANZEIGEN') || BEWERBERTOOL_DOKUMENTE_ANZEIGEN)
	$tabs[2]='dokumente';
if (defined('BEWERBERTOOL_UHSTAT1_ANZEIGEN') && BEWERBERTOOL_UHSTAT1_ANZEIGEN)
	$tabs[3]='uhstat';
if(defined('BEWERBERTOOL_AUSBILDUNG_ANZEIGEN') && BEWERBERTOOL_AUSBILDUNG_ANZEIGEN)
	$tabs[4]='ausbildung';
if(!defined('BEWERBERTOOL_ZGV_ANZEIGEN') || BEWERBERTOOL_ZGV_ANZEIGEN)
	$tabs[5]='zgv';
if(!defined('BEWERBERTOOL_ZAHLUNGEN_ANZEIGEN') || BEWERBERTOOL_ZAHLUNGEN_ANZEIGEN)
	$tabs[6]='zahlungen';
if(defined('BEWERBERTOOL_RECHNUNGSKONTAKT_ANZEIGEN') && BEWERBERTOOL_RECHNUNGSKONTAKT_ANZEIGEN)
	$tabs[7]='rechnungskontakt';
if(!defined('BEWERBERTOOL_REIHUNGSTEST_ANZEIGEN') || BEWERBERTOOL_REIHUNGSTEST_ANZEIGEN)
	$tabs[8]='aufnahme';
if(!defined('BEWERBERTOOL_ABSCHICKEN_ANZEIGEN') || BEWERBERTOOL_ABSCHICKEN_ANZEIGEN)
	$tabs[9]='abschicken';
if(defined('BEWERBERTOOL_MESSAGES_ANZEIGEN') && BEWERBERTOOL_MESSAGES_ANZEIGEN)
	$tabs[10]='messages';
if(defined('BEWERBERTOOL_SICHERHEIT_ANZEIGEN') && BEWERBERTOOL_SICHERHEIT_ANZEIGEN)
	$tabs[11]='sicherheit';
if(defined('BEWERBERTOOL_AKTEN_ANZEIGEN') && BEWERBERTOOL_AKTEN_ANZEIGEN)
	$tabs[12]='akten';
if(defined('BEWERBERTOOL_INVOICES_ANZEIGEN') && BEWERBERTOOL_INVOICES_ANZEIGEN)
	$tabs[15]='invoices';

$tabLadefolge = $tabs;
ksort($tabLadefolge);
$tabLadefolge = array_values($tabLadefolge);
$tabs = array_values($tabs);

if (! isset($_SESSION['bewerbung/user']) || $_SESSION['bewerbung/user'] == '')
{
	$_SESSION['request_uri'] = $_SERVER['REQUEST_URI'];

	header('Location: registration.php?method='.$tabs[0]);
	exit();
}

require_once('../../../include/adresse.class.php');
require_once('../../../include/akte.class.php');
require_once ('../../../include/aufmerksamdurch.class.php');
require_once ('../../../include/basis_db.class.php');
require_once ('../../../include/bankverbindung.class.php');
require_once ('../../../include/benutzer.class.php');
require_once ('../../../include/benutzerberechtigung.class.php');
require_once ('../../../include/bewerbungstermin.class.php');
require_once ('../../../include/bisberufstaetigkeit.class.php');
require_once ('../../../include/datum.class.php');
require_once ('../../../include/dms.class.php');
require_once ('../../../include/dokument.class.php');
require_once ('../../../include/fotostatus.class.php');
require_once ('../../../include/functions.inc.php');
require_once ('../../../include/gemeinde.class.php');
require_once ('../../../include/geschlecht.class.php');
require_once ('../../../include/kontakt.class.php');
require_once ('../../../include/konto.class.php');
require_once ('../../../include/mail.class.php');
require_once ('../../../include/nation.class.php');
require_once ('../../../include/notiz.class.php');
require_once ('../../../include/organisationseinheit.class.php');
require_once ('../../../include/organisationsform.class.php');
require_once ('../../../include/ort.class.php');
require_once ('../../../include/person.class.php');
require_once ('../../../include/personlog.class.php');
require_once ('../../../include/phrasen.class.php');
require_once ('../../../include/preinteressent.class.php');
require_once ('../../../include/prestudent.class.php');
require_once ('../../../include/reihungstest.class.php');
require_once ('../../../include/sprache.class.php');
require_once ('../../../include/studiengang.class.php');
require_once ('../../../include/studienordnung.class.php');
require_once ('../../../include/studienplan.class.php');
require_once ('../../../include/studiensemester.class.php');
require_once ('../../../include/zgv.class.php');
require_once ('../include/functions.inc.php');
require_once ('../../../include/rueckstellung.class.php');
require_once ('../../../include/kennzeichen.class.php');


if (isset($_GET['logout']))
{
	session_destroy();
	header('Location: registration.php');
}

$person_id = (int)$_SESSION['bewerbung/personId'];
$akte_id = isset($_POST['akte_id']) ? $_POST['akte_id'] : '';
$method = isset($_POST['method']) ? $_POST['method'] : '';
$datum = new datum();
$person = new person();

if (! $person->load($person_id))
{
	die($p->t('global/fehlerBeimLadenDesDatensatzes'));
}

$kennzeichen = new kennzeichen();

$eobLogin = false;
if ($kennzeichen->load_pers($person_id, ['eobRegistrierungsId']))
{
	$eobLogin = count($kennzeichen->result) > 0;
}
else
{
	die($kennzeichen->errormsg);
}


$spracheGet = filter_input(INPUT_GET, 'sprache');

if(isset($spracheGet))
{
	$spracheGet = new sprache();
	if($spracheGet->load($_GET['sprache']))
	{
		setSprache($_GET['sprache']);
	}
	else
	{
		setSprache(DEFAULT_LANGUAGE);
	}
}
// $sprache = DEFAULT_LANGUAGE;
$sprache = getSprache();
//echo var_dump($sprache);
$sprachindex = new sprache();
$spracheIndex = $sprachindex->getIndexFromSprache($sprache);
$p = new phrasen($sprache);
$log = new personlog();
$rueckstellung = new rueckstellung();

// Erstellen eines Array mit allen Studiengängen
$studiengaenge_obj = new studiengang();
$studiengaenge_obj->getAll();
$studiengaenge_arr = array();

foreach ($studiengaenge_obj->result as $row)
{
	$studiengaenge_arr[$row->studiengang_kz]['kurzbz'] = $row->kurzbz;
	$studiengaenge_arr[$row->studiengang_kz]['bezeichnung'] = $row->bezeichnung;
	$studiengaenge_arr[$row->studiengang_kz]['english'] = $row->english;
	$studiengaenge_arr[$row->studiengang_kz]['typ'] = $row->typ;
	$studiengaenge_arr[$row->studiengang_kz]['orgform_kurzbz'] = $row->orgform_kurzbz;
	$studiengaenge_arr[$row->studiengang_kz]['oe_kurzbz'] = $row->oe_kurzbz;
}

$eingabegesperrt = false;

// Wenn die eingeloggte Person bereits Student oder Mitarbeiter ist
// duerfen die Stammdaten nicht mehr geaendert werden
$benutzer = new benutzer();
if ($benutzer->getBenutzerFromPerson($person->person_id, false))
{
	if (count($benutzer->result) > 0)
	{
		$eingabegesperrt = true;
	}
}

// Wenn bereits eine Bewerbung abgeschickt wurde, duerfen die Stammdaten nicht mehr geaendert werden
if (check_person_bewerbungabgeschickt($person_id))
	$eingabegesperrt = true;

$message = '&nbsp;';

// $vollstaendig = '<span class="badge alert-success">'.$p->t('bewerbung/vollstaendig').' <span class="glyphicon glyphicon-ok"></span></span>';
// $unvollstaendig = '<span class="badge alert-danger">'.$p->t('bewerbung/unvollstaendig').' <span class="glyphicon glyphicon-remove"></span></span>';
$vollstaendig = '<span style="color: #3c763d;">'.$p->t('bewerbung/vollstaendig').'</span>';
$unvollstaendig = '<span style="color: #a94442;">' . $p->t('bewerbung/unvollstaendig') . '</span>';
$teilvollstaendig = '<span style="color: #8A6D3B;">' . $p->t('bewerbung/teilweiseVollstaendig') . '</span>';

$save_error_dokumente = '';
if ($method == 'delete')
{
	$akte = new akte();
	if (! $akte->load($akte_id))
	{
		$message = $p->t('global/fehlerBeiDerParameteruebergabe');
		$save_error_dokumente = true;
	}
	else
	{
		if ($akte->person_id != $person_id)
		{
			$save_error_dokumente = true;
			die($p->t('global/fehlerBeimLadenDesDatensatzes'));
		}

		$dms_id = $akte->dms_id;
		$dms = new dms();

		if ($akte->delete($akte_id))
		{
			if (! $dms->deleteDms($dms_id))
			{
				$save_error_dokumente = true;
				$message = $p->t('global/fehlerBeimLoeschenDesEintrags');
			}
			else
			{
				$save_error_dokumente = false;
				$message = $p->t('global/erfolgreichgelöscht');
				// Geparkten Logeintrag löschen
				$rueckstellung->deleteParked($person_id);
				// Logeintrag schreiben
				$log->log($person_id, 'Action', array(
					'name' => 'Document ' . $akte->bezeichnung . ' deleted',
					'success' => true,
					'message' => 'Document ' . $akte->bezeichnung . ' "' . $akte->titel . '" deleted by user'
				), 'bewerbung', 'bewerbung', null, 'online');
			}
		}
		else
		{
			$save_error_dokumente = true;
			$message = $p->t('global/fehlerBeimLoeschenDesEintrags');
		}
	}
}

// Löschen einer Akte per Ajax
$deleteAkte = filter_input(INPUT_POST, 'deleteAkte', FILTER_VALIDATE_BOOLEAN);
if ($deleteAkte && isset($_POST['akte_id']))
{
	$akte_id = filter_input(INPUT_POST, 'akte_id', FILTER_VALIDATE_INT);

	$akte = new akte();
	if (! $akte->load($akte_id))
	{
		echo json_encode(array(
			'status' => 'fehler',
			'msg' => $p->t('global/fehlerBeiDerParameteruebergabe')
		));
		exit();
	}
	else
	{
		if ($akte->person_id != $person_id)
		{
			echo json_encode(array(
				'status' => 'fehler',
				'msg' => $p->t('global/fehlerBeimLadenDesDatensatzes')
			));
			exit();
		}

		$dms_id = $akte->dms_id;
		$dms = new dms();

		if ($akte->delete($akte_id))
		{
			if (! $dms->deleteDms($dms_id))
			{
				echo json_encode(array(
					'status' => 'fehler',
					'msg' => $p->t('global/fehlerBeimLoeschenDesEintrags')
				));
				exit();
			}
			else
			{
				// Geparkten Logeintrag löschen
				$rueckstellung->deleteParked($person_id);
				// Logeintrag schreiben
				$log->log($person_id, 'Action', array(
					'name' => 'Document ' . $akte->bezeichnung . ' deleted',
					'success' => true,
					'message' => 'Document ' . $akte->bezeichnung . ' "' . $akte->titel . '" deleted by user'
				), 'bewerbung', 'bewerbung', null, 'online');
				echo json_encode(array(
					'status' => 'ok',
					'msg' => $p->t('global/erfolgreichgelöscht')
				));
				exit();
			}
		}
		else
		{
			echo json_encode(array(
				'status' => 'fehler',
				'msg' => $p->t('global/fehlerBeimLoeschenDesEintrags')
			));
			exit();
		}
	}
}

$bewerbungStornieren = filter_input(INPUT_POST, 'bewerbungStornieren', FILTER_VALIDATE_BOOLEAN);
// Stornieren von Bewerbungen. Es wird ein Status "Abgewiesen" mit Statusgrund angelegt.
// Der Bewerber darf seine Bewerbung je Studiensemester im selben Studiengang nur einmal stornieren.
if ($bewerbungStornieren && isset($_POST['prestudent_id']))
{
	$prestudent_id = filter_input(INPUT_POST, 'prestudent_id', FILTER_VALIDATE_INT);
	$studiensemester_kurzbz = filter_input(INPUT_POST, 'studiensemester_kurzbz');

	$prestudent_status = new prestudent($prestudent_id);
	$prestudent_status->getLastStatus($prestudent_id, $studiensemester_kurzbz);

	$statusbestaetigt = $prestudent_status->bestaetigtam != '' || $prestudent_status->bestaetigtvon != ''?true:false;
	if ($prestudent_status->status_kurzbz == 'Interessent' && $statusbestaetigt == false)
	{
		// Status "Abgewiesen" mit Statusgrund anlegen
		$prestudent_status->status_kurzbz = 'Abgewiesener';
		$prestudent_status->studiensemester_kurzbz = $studiensemester_kurzbz;
		$prestudent_status->datum = date("Y-m-d H:i:s");
		$prestudent_status->insertamum = date("Y-m-d H:i:s");
		$prestudent_status->insertvon = 'online';
	// 	$prestudent_status->updateamum = date("Y-m-d H:i:s");
	// 	$prestudent_status->updatevon = 'online';
		$prestudent_status->new = true;
		// Wenn BEWERBERTOOL_STORNIERUNG_STATUSGRUND_ID definiert ist, wird ein Statusgrund gesetzt
		if (defined('BEWERBERTOOL_STORNIERUNG_STATUSGRUND_ID') && is_int(BEWERBERTOOL_STORNIERUNG_STATUSGRUND_ID))
			$prestudent_status->statusgrund_id = BEWERBERTOOL_STORNIERUNG_STATUSGRUND_ID;

		if(!$prestudent_status->save_rolle())
		{
			echo json_encode(array(
				'status' => 'fehler',
				'msg' => $prestudent_status->errormsg
			));
			$message = $p->t('global/fehlerBeimSpeichernDerDaten');
			exit();
		}
		else
		{
			// Geparkten Logeintrag löschen
			$rueckstellung->deleteParked($person->person_id);
			// Logeintrag schreiben
			$stg = new studiengang($prestudent_status->studiengang_kz);
			$log->log($person->person_id,
				'Action',
				array('name'=>'Application Deleted By User','success'=>true,'message'=>'Application For '.$stg->bezeichnung_arr[$sprache].' ('.$prestudent_status->orgform_kurzbz.') Studienplan '.$prestudent_status->studienplan_id.' Deleted By User'),
				'bewerbung',
				'bewerbung',
				$stg->oe_kurzbz,
				'online');

			echo json_encode(array(
				'status' => 'ok'
			));
			exit();
		}
	}
}

$changePriority = filter_input(INPUT_POST, 'changePriority', FILTER_VALIDATE_BOOLEAN);
// Ändern der Priorität von Bewerbungen
if ($changePriority && isset($_POST['ausgang_prestudent_id'])
	&& isset($_POST['ziel_prestudent_id'])
	&& isset($_POST['ausgang_prioritaet'])
	&& isset($_POST['ziel_prioritaet'])
	&& isset($_POST['studiensemester_kurzbz']))
{
	$ausgang_prestudent_id = filter_input(INPUT_POST, 'ausgang_prestudent_id', FILTER_VALIDATE_INT);
	$ziel_prestudent_id = filter_input(INPUT_POST, 'ziel_prestudent_id', FILTER_VALIDATE_INT);
	$ausgangsPrioritaet = filter_input(INPUT_POST, 'ausgang_prioritaet');
	$zielPrioritaet = filter_input(INPUT_POST, 'ziel_prioritaet');
	$studiensemester_kurzbz = filter_input(INPUT_POST, 'studiensemester_kurzbz');

	$prestudent1 = new prestudent($ausgang_prestudent_id);
	$prestudent2 = new prestudent($ziel_prestudent_id);
	$hoechstePrio = new prestudent();
	$hoechstePrio->getPriorisierungPersonStudiensemester($prestudent1->person_id, $studiensemester_kurzbz);
	// Wenn $ausgangsPrioritaet NULL ist, höchste Prio ermitteln, diese setzen und $zielPrioritaet +1 setzen
	if ($ausgangsPrioritaet == '')
	{
		// Wenn höchste Prio auch NULL ist, dann Werte direkt setzen
		if ($hoechstePrio->priorisierung == '')
		{
			$ausgangsPrioritaetNeu = 1;
			$zielPrioritaetNeu = 2;
		}
		else
		{
			// Wenn $zielPrioritaet NULL ist, höchste Prio ermitteln und $ausgangsPrioritaet +1 setzen und $zielPrioritaet + 2 setzen
			if ($zielPrioritaet == '')
			{
				$ausgangsPrioritaetNeu = $hoechstePrio->priorisierung + 1;
				$zielPrioritaetNeu = $hoechstePrio->priorisierung + 2;
			}
			else
			{
				$ausgangsPrioritaetNeu = $zielPrioritaet;
				$zielPrioritaetNeu = $hoechstePrio->priorisierung + 1;
			}
		}
	}
	else
	{
		// Wenn $zielPrioritaet NULL ist, $ausgangsPrioritaet höchste Prio +1 setzen und $zielPrioritaet gelcih $ausgangsPrioritaet setzen
		if ($zielPrioritaet == '')
		{
			$ausgangsPrioritaetNeu = $hoechstePrio->priorisierung + 1;
			$zielPrioritaetNeu = $ausgangsPrioritaet;
		}
		else
		{
			$ausgangsPrioritaetNeu = $zielPrioritaet;
			$zielPrioritaetNeu = $ausgangsPrioritaet;
		}
	}

	$prestudent1->priorisierung = $ausgangsPrioritaetNeu;
	if (!$prestudent1->save())
	{
		echo json_encode(array(
			'status' => 'fehler',
			'msg' => $prestudent1->errormsg
		));
		exit();
	}
	else
	{
		$prestudent2->priorisierung = $zielPrioritaetNeu;
		if (!$prestudent2->save())
		{
			echo json_encode(array(
				'status' => 'fehler',
				'msg' => $prestudent2->errormsg
			));
			exit();
		}
		else
		{
			echo json_encode(array(
				'status' => 'ok'
			));
			exit();
		}
	}
}

$aktionReihungstest = filter_input(INPUT_POST, 'aktionReihungstest', FILTER_VALIDATE_BOOLEAN);
// An- oder Abmelden von Reihungstests
if ($aktionReihungstest)
{
	$rt_id = filter_input(INPUT_POST, 'reihungstest_id', FILTER_VALIDATE_INT);
	$studienplan_id = filter_input(INPUT_POST, 'studienplan_id', FILTER_VALIDATE_INT);
	$aktion = filter_input(INPUT_POST, 'aktion');

	if (defined('REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND') && is_numeric(REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND))
	{
		$schwund = REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND;
	}
	else
	{
		$schwund = '';
	}

	if ($aktion == 'save')
	{
		$reihungstest = new reihungstest();
		// Pruefen der verfuegbaren Plaetze
		if ($reihungstest->getTeilnehmerAnzahl($rt_id) >= $reihungstest->getVerfuegbarePlaetzeReihungstest($rt_id, $schwund))
		{
			echo json_encode(array(
				'status' => 'fehler',
				'msg' => 'Keine Plätze mehr verfügbar'
			));
			exit();
		}
		// Prüfen, ob schon eine Anmeldung für diesen RT existiert
		if ($reihungstest->getPersonReihungstest($person_id, $rt_id))
		{
			echo json_encode(array(
				'status' => 'fehler',
				'msg' => 'Sie sind bereits für diesen Reihungstest angemeldet. Bitte aktualisieren Sie die Seite.'
			));
			exit();
		}
		$reihungstest->new = true;
		$reihungstest->reihungstest_id = $rt_id;
		$reihungstest->person_id = $person_id;
		$reihungstest->studienplan_id = $studienplan_id;
		$reihungstest->anmeldedatum = date("Y-m-d H:i:s");
		$reihungstest->teilgenommen = false;
		$reihungstest->ort_kurzbz = '';
		$reihungstest->punkte = '';
		$reihungstest->insertamum = date("Y-m-d H:i:s");
		$reihungstest->insertvon = 'online';

		if (! $reihungstest->savePersonReihungstest())
		{
			echo json_encode(array(
				'status' => 'fehler',
				'msg' => $reihungstest->errormsg
			));
			$message = $p->t('global/fehlerBeimSpeichernDerDaten');
			exit();
		}
		else
		{
			// Reihungstest laden um Log-Daten zu befüllen
			$rt = new reihungstest($rt_id);
			// Geparkten Logeintrag löschen
			$rueckstellung->deleteParked($person_id);
			// Logeintrag schreiben
			$log->log($person_id,
				'Processstate',
				array('name' => 'Signed-on for placement test', 'message' => 'Subscribed for placement test on ' . $rt->datum . ' at ' . $rt->uhrzeit . ' for Studienplan ' . $studienplan_id ),
				'aufnahme',
				'bewerbung',
				null,
				'online');

			echo json_encode(array(
				'status' => 'ok'
			));
			// E-Mail zur Bestätigung an Bewerber schicken
			/*$kontakt = new kontakt();
			$kontakt->load_persKontakttyp($person_id, 'email');
			$mailadresse = isset($kontakt->result[0]->kontakt) ? $kontakt->result[0]->kontakt : '';
			if ($mailadresse != '')
			{
				$person = new person();
				$person->load($person_id);
				if($person->geschlecht == 'm')
					$anrede = $p->t('bewerbung/anredeMaennlich');
				else
					$anrede = $p->t('bewerbung/anredeWeiblich');

				$email = new mail($mailadresse, 'no-reply', $p->t('bewerbung/anmeldungReihungstestMailBetreff'), 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Inhalt vollständig darzustellen.');
				$email_bewerber = $p->t('bewerbung/anmeldungReihungstestMail', array(
					$person->vorname,
					$person->nachname,
					$anrede,
					substr($tagbez[$spracheIndex][$datum->formatDatum($rt->datum, 'N')], 0, 2).', '.$datum->formatDatum($rt->datum, 'd.m.Y'),
					$datum->formatDatum($rt->uhrzeit,'H:i')));

				$email->setHTMLContent($email_bewerber);
				$email->send();
			}*/
			exit();
		}
	}
	elseif ($aktion == 'delete')
	{
		$rt_person_id = new reihungstest();
		$rt_person_id->getPersonReihungstest($person_id, $rt_id);

		$reihungstest = new reihungstest($rt_id);

		// Löschen der Anmeldung nur möglich, wenn BEWERBERTOOL_REIHUNGSTEST_STORNIERBAR_TAGE oder Anmeldefrist noch nicht vorbei
		if (defined('BEWERBERTOOL_REIHUNGSTEST_STORNIERBAR_TAGE') && BEWERBERTOOL_REIHUNGSTEST_STORNIERBAR_TAGE != '')
		{
			$time = strtotime($reihungstest->datum.' 23:59:59 -'.BEWERBERTOOL_REIHUNGSTEST_STORNIERBAR_TAGE.'days');
			if ($time < time())
			{
				echo json_encode(array(
					'status' => 'fehler',
					'msg' => 'Der Termin konnte nicht storniert werden, da die Anmeldefrist vorbei ist'
				));
				exit();// @todo: Phrasenmodul
			}
		}
		elseif ($reihungstest->anmeldefrist != '' && strtotime($reihungstest->anmeldefrist.' 23:59:59') < time())
		{
			echo json_encode(array(
				'status' => 'fehler',
				'msg' => 'Der Termin konnte nicht storniert werden, da die Anmeldefrist vorbei ist'
			));
			exit();// @todo: Phrasenmodul
		}

		if (! $reihungstest->deletePersonReihungstest($rt_person_id->rt_person_id))
		{
			echo json_encode(array(
				'status' => 'fehler',
				'msg' => $reihungstest->errormsg
			));
			exit();
		}
		else
		{
			// Reihungstest laden um Log-Daten zu befüllen
			$rt = new reihungstest($rt_id);
			// Geparkten Logeintrag löschen
			$rueckstellung->deleteParked($person_id);
			// Logeintrag schreiben
			$log->log($person_id,
				'Action',
				array('name' => 'Signed-off for placement test', 'message' => 'Signed-off for placement test on ' . $rt->datum . ' at ' . $rt->uhrzeit . ' for Studienplan ' . $studienplan_id ),
				'aufnahme',
				'bewerbung',
				null,
				'online');

				echo json_encode(array(
					'status' => 'ok'
				));
				exit();
		}
	}
}
$save_error_abschicken = '';
if (isset($_POST['btn_bewerbung_abschicken']))
{
	// Die BFI-KI nimmt automatisch Kontobelastungen vor, wenn es eine neue Bewerbung gibt.
	// Wenn die Seite dazwischen nicht aktualisiert wird, kann man dennoch abschicken.
	// Darum wird hier nochmal auf Belastungen gecheckt
	if (CAMPUS_NAME != 'FH Technikum Wien')
	{
		if (defined('BEWERBERTOOL_ZAHLUNGEN_ANZEIGEN') && BEWERBERTOOL_ZAHLUNGEN_ANZEIGEN === true)
		{
			$konto = new konto();
			if (!$konto->checkKontostand($person_id))
			{
				$message = $p->t('bewerbung/zahlungAusstaendig');
				$save_error_abschicken = true;
			}
		}
	}

	// Mail an zuständige Assistenz schicken
	$pr_id = isset($_POST['prestudent_id']) ? $_POST['prestudent_id'] : '';
	$sendmail = false; // Damit das Mail beim Seitenreload nicht nochmal geschickt wird
	$bewerbungszeitraum_gueltig = true;

	if ($pr_id != '' && $save_error_abschicken == '')
	{
		// Status Bewerber anlegen
		$prestudent_status = new prestudent();
		$prestudent_status->load($pr_id);

		$alterstatus = new prestudent();
		$alterstatus->getLastStatus($pr_id);

		$studiengang = new studiengang($prestudent_status->studiengang_kz);

		// Nation für die Anzeige der richtigen Bewerbungsfrist laden
		if ($studiengang->typ == 'm')
		{
			$zgv_nation = $prestudent_status->zgvmanation;
		}
		else
		{
			$zgv_nation = $prestudent_status->zgvnation;
		}

		$nation = new nation($zgv_nation);
		$nationengruppe = $nation->nationengruppe_kurzbz;

		if ($nationengruppe == '')
		{
			$nationengruppe = 0;
		}

		// check ob es status schon gibt
		if ($prestudent_status->load_rolle($pr_id, 'Interessent', $alterstatus->studiensemester_kurzbz, '1'))
		{
			// Check, ob Bewerbungsfrist schon begonnen hat, bzw abgelaufen ist
			$bewerbungsfristen = new bewerbungstermin();
			$bewerbungsfristen->getBewerbungstermine($prestudent_status->studiengang_kz, $prestudent_status->studiensemester_kurzbz, 'insertamum DESC', $prestudent_status->studienplan_id, $nationengruppe);

			if (isset($bewerbungsfristen->result[0]))
			{
				$bewerbungsfristen = $bewerbungsfristen->result[0];
				// Wenn Nachfrist gesetzt und das Nachfrist-Datum befuellt ist, gilt die Nachfrist
				// sonst das Endedatum, wenn eines gesetzt ist
				if ($bewerbungsfristen->nachfrist == true && $bewerbungsfristen->nachfrist_ende != '')
				{
					// Zeit bis Fristablauf zaehlen
					if (((strtotime($bewerbungsfristen->nachfrist_ende) - time()) / 86400) <= 0 || ((time() - strtotime($bewerbungsfristen->beginn)) / 86400) <= 0)
						$bewerbungszeitraum_gueltig = false;
				}
				elseif ($bewerbungsfristen->ende != '')
				{
					// Zeit bis Fristablauf zaehlen
					if (((strtotime($bewerbungsfristen->ende) - time()) / 86400) <= 0 || ((time() - strtotime($bewerbungsfristen->beginn)) / 86400) <= 0)
						$bewerbungszeitraum_gueltig = false;
				}
				elseif ($bewerbungsfristen->beginn != '')
				{
					// Zeit bis Fristablauf zaehlen
					if (((time() - strtotime($bewerbungsfristen->beginn)) / 86400) <= 0)
						$bewerbungszeitraum_gueltig = false;
				}
			}
			if ($bewerbungszeitraum_gueltig == true)
			{
				// An der FHTW wird das bestaetigungsdatum NICHT gesetzt
				if (CAMPUS_NAME != 'FH Technikum Wien')
					$prestudent_status->bestaetigtam = date('Y-m-d H:i:s');

				if ($prestudent_status->bewerbung_abgeschicktamum == '')
				{
					$prestudent_status->bewerbung_abgeschicktamum = date('Y-m-d H:i:s');
					$sendmail = true;
				}
				else
					$sendmail = false;

				$prestudent_status->new = false;
				$prestudent_status->updateamum = date('Y-m-d H:i:s');
				$prestudent_status->updatevon = 'online';

				if (! $prestudent_status->save_rolle())
					die($p->t('global/fehlerBeimSpeichernDerDaten'));
			}
			else
			{
				$message = $p->t('bewerbung/bewerbungAusserhalbZeitraum');
				$save_error_abschicken = true;
			}
		}

		$prestudent = new prestudent();
		$prestudent->load($pr_id);
		$studiengang = new studiengang();
		$studiengang->load($prestudent->studiengang_kz);
		if ($sendmail == true && $bewerbungszeitraum_gueltig == true && $save_error_abschicken == '')
		{
			if (sendBewerbung($pr_id, $prestudent_status->studiensemester_kurzbz, $prestudent_status->orgform_kurzbz, $prestudent_status->studienplan_id))
			{
				$message = $p->t('bewerbung/erfolgreichBeworben', array(
					$studiengang->bezeichnung_arr[$sprache]
				));
				// echo '<script type="text/javascript">alert("'.$p->t('bewerbung/erfolgreichBeworben',array($studiengang->bezeichnung_arr[$sprache])).'");</script>';
				// echo '<script type="text/javascript">window.location="'.$_SERVER['PHP_SELF'].'?active=abschicken";</script>';
				$save_error_abschicken = false;
				// Geparkten Logeintrag löschen
				$rueckstellung->deleteParked($prestudent_status->person_id);
				// Logeintrag schreiben
				$log->log($prestudent_status->person_id, 'Processstate', array(
					'name' => 'Application sent',
					'message' => 'Application for ' . $studiengang->bezeichnung_arr[$sprache] . ' ' . $prestudent_status->orgform_kurzbz . ' ' . $prestudent_status->studiensemester_kurzbz . ' Studienplan ' . $prestudent_status->studienplan_id . ' has been sent'
				), 'bewerbung', 'bewerbung', $studiengang->oe_kurzbz, 'online');
			}
			else
			{
				echo '<script type="text/javascript">alert("' . $p->t('bewerbung/fehlerBeimVersendenDerBewerbung') . '");</script>';
				$save_error_abschicken = true;
				// Geparkten Logeintrag löschen
				$rueckstellung->deleteParked($prestudent_status->person_id);
				// Logeintrag schreiben
				$log->log($prestudent_status->person_id, 'Processstate', array(
					'name' => 'Application sent',
					'message' => 'Error sending application for ' . $studiengang->bezeichnung_arr[$sprache] . ' ' . $prestudent_status->orgform_kurzbz . ' ' . $prestudent_status->studiensemester_kurzbz . ' Studienplan ' . $prestudent_status->studienplan_id
				), 'bewerbung', 'bewerbung', $studiengang->oe_kurzbz, 'online');
			}
		}
	}
}

if (isset($_POST['submit_nachgereicht']))
{
	// gibt es schon einen eintrag?
	if (isset($_POST['akte_id']))
	{
		// Update
	}
	else
	{
		$save_error_nachreichung = false;
		// Datumsformat Nachreichung überprüfen
		if (filter_input(INPUT_POST, 'nachreichungam') != '')
		{
			if (! preg_match('/^\d{2}\.\d{2}\.(\d{2}|\d{4})$/ ', filter_input(INPUT_POST, 'nachreichungam')))
			{
				$message = $p->t('bewerbung/datumUngueltig');
				$save_error_nachreichung = true;
			}
			else
			{
				$ds = explode('.', filter_input(INPUT_POST, 'nachreichungam'));
				if (! checkdate($ds[1], $ds[0], $ds[2]))
				{
					$message = $p->t('bewerbung/datumUngueltig');
					$save_error_nachreichung = true;
				}
			}
		}
		if ($save_error_nachreichung === false)
		{
			// Insert
			$akte = new akte();
			$akte->dokument_kurzbz = $_POST['dok_kurzbz'];
			$akte->person_id = $person_id;
			$akte->erstelltam = '';
			$akte->gedruckt = false;
			$akte->titel = '';
			$akte->anmerkung = $_POST['txt_anmerkung'];
			$akte->updateamum = date('Y-m-d H:i:s');
			$akte->updatevon = 'online';
			$akte->insertamum = date('Y-m-d H:i:s');
			$akte->insertvon = 'online';
			$akte->uid = '';
			$akte->new = true;
			$akte->nachgereicht = (isset($_POST['check_nachgereicht'])) ? true : ''; // True wenn nachgereicht wird, false wenn nchgereicht wurde, null als default
			$akte->nachgereicht_am = $datum->formatDatum($_POST['nachreichungam'], 'Y-m-d');
			if (! $akte->save())
			{
				$message = $p->t('global/fehlerBeimSpeichernDerDaten') . ' ' . $akte->errormsg;
			}
			else
			{
				// Geparkten Logeintrag löschen
				$rueckstellung->deleteParked($person_id);
				// Logeintrag schreiben
				$log->log($person_id, 'Action', array(
					'name' => $_POST['dok_kurzbz'] . ' set to nachgereicht',
					'success' => true,
					'message' => 'Document ' . $_POST['dok_kurzbz'] . ' has been set to nachgereicht'
				), 'bewerbung', 'bewerbung', null, 'online');
			}





			// An der FHTW wird ein vorläufiges ZGV-Dokument bei Bachelor und Master verlangt
			if (CAMPUS_NAME == 'FH Technikum Wien' && ($_POST['dok_kurzbz'] == 'zgv_bakk' || $_POST['dok_kurzbz'] == 'zgv_mast')|| $_POST['dok_kurzbz'] == 'SprachB2')
			{
				if ($_POST['dok_kurzbz'] == 'zgv_bakk')
				{
					$preDokument = 'ZgvBaPre';
				}
				elseif ($_POST['dok_kurzbz'] == 'zgv_mast')
				{
					$preDokument = 'ZgvMaPre';
				}
				elseif ($_POST['dok_kurzbz'] == 'SprachB2')
				{
					$preDokument = 'VorlSpB2';
				}
				// Check, ob Dokumenttyp 'ZgvBaPre', 'ZgvMaPre' bzw. 'VorlSpB2' schon existiert
				$dokument = new dokument();
				if ($dokument->loadDokumenttyp($preDokument))
				{
					$error = false;
					$message = '';
					// Check, ob ein File gewaehlt wurde
					if (!empty($_FILES['filenachgereicht']['tmp_name']))
					{
						$dokumenttyp_upload = $preDokument;

						// Es wird eine neue Akte vom Typ 'ZgvBaPre', 'ZgvMaPre' bzw. 'VorlSpB2' angelegt
						// DMS-Eintrag erstellen
						$ext = strtolower(pathinfo($_FILES['filenachgereicht']['name'], PATHINFO_EXTENSION));

						// Auf gültige Dateitypen prüfen
						if (in_array($ext, array(
							'pdf',
							'jpg',
							'jpeg'
						)))
						{
							$filename = uniqid();
							$filename .= "." . $ext;
							$uploadfile = DMS_PATH . $filename;

							if (move_uploaded_file($_FILES['filenachgereicht']['tmp_name'], $uploadfile))
							{
								$dms_id = '';

								$dms = new dms();
								if(!$dms->setPermission($uploadfile))
									$message .= $dms->errormsg;

								$dms->version = '0';
								$dms->kategorie_kurzbz = 'Akte';

								$dms->insertamum = date('Y-m-d H:i:s');
								$dms->insertvon = 'online';
								$dms->mimetype = $_FILES['filenachgereicht']['type'];
								$dms->filename = $filename;
								$dms->name = $_FILES['filenachgereicht']['name'];

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

						if (! $error && isset($_FILES['filenachgereicht']['tmp_name']))
						{
							$akte = new akte();
							$akte->new = true;
							$akte->insertamum = date('Y-m-d H:i:s');
							$akte->insertvon = 'online';

							$dokument = new dokument();
							$dokument->loadDokumenttyp($preDokument);

							$akte->dokument_kurzbz = $preDokument;
							$akte->titel = cutString($_FILES['filenachgereicht']['name'], 32, '~', true); // Dateiname
							$akte->bezeichnung = cutString($dokument->bezeichnung, 32); // Dokumentbezeichnung
							$akte->person_id = $person_id;
							$akte->mimetype = $_FILES['filenachgereicht']['type'];
							$akte->erstelltam = date('Y-m-d H:i:s');
							$akte->gedruckt = false;
							$akte->nachgereicht = false;
							$akte->anmerkung = '';
							$akte->uid = '';
							$akte->dms_id = $dms_id;
							$akte->ausstellungsnation = '';

							if (! $akte->save())
							{
								$message .= $p->t('global/fehleraufgetreten') . ": $akte->errormsg";
							}
							else
							{
								$message .= $p->t('global/erfolgreichgespeichert');
								// Geparkten Logeintrag löschen
								$rueckstellung->deleteParked($person_id);
								// Logeintrag schreiben
								$log->log($person_id, 'Action', array(
									'name' => 'Document ' . $akte->bezeichnung . ' uploaded',
									'success' => true,
									'message' => 'Document ' . $akte->bezeichnung . ' "' . $akte->titel . '" uploaded'
								), 'bewerbung', 'bewerbung', null, 'online');
							}
							echo "<script>
									var loc = window.opener.location;
									window.opener.location = 'bewerbung.php?active=dokumente';
									</script>";
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
			}
		}
	}
}

if(isset($_POST['action']) && $_POST['action']=='downloadAkte')
{
	$id = $_POST['akte_id'];
	$akte = new akte();
	$akte->load($id);
	if ($akte->person_id == $person_id
		&& $akte->stud_selfservice)
	{
		if($akte->inhalt!='')
		{
			//Header fuer Datei schicken
			header("Content-type: $akte->mimetype");
			header('Content-Disposition: attachment; filename="'.$akte->titel.'"');
			echo base64_decode($akte->inhalt);
			exit;
		}
		else
		{
			die('Akte ist ohne Inhalt');
		}
	}
	else
	{
		die('Person ID stimmt nicht überein');
	}
}

if(isset($_POST['action']) && $_POST['action']=='acceptAkte')
{
	$id = $_POST['akte_id'];
	$akte = new akte();
	$akte->load($id);
	if ($akte->person_id == $person_id
		&& $akte->stud_selfservice)
	{
		$akte->akzeptiertamum = date('Y-m-d H:i:s');

		if (!$akte->save())
		{
			$message .= $p->t('global/fehleraufgetreten') . ": $akte->errormsg";
		}
		else
		{
			$message .= $p->t('global/erfolgreichgespeichert');
			// Logeintrag schreiben
			$log->log($person_id, 'Action', array(
				'name' => 'Akte '.$akte->bezeichnung.' accepted',
				'success' => true,
				'message' => 'Akte '.$akte->bezeichnung.' (ID '.$akte->akte_id.') accepted'
			), 'bewerbung', 'bewerbung', null, 'online');
		}
	}
	else
	{
		die('Person ID stimmt nicht überein');
	}
}

// gibt an welcher Tab gerade aktiv ist
$active = filter_input(INPUT_GET, 'active');

if (! $active)
{
	$active = $tabs[0];
}
$save_error_daten = '';
// Persönliche Daten speichern
if (isset($_POST['btn_person']))
{
	// Wenn Eingabe gesperrt darf nur die SVNR gespeichert werden
	if (!$eingabegesperrt)
	{
		$person->titelpre = $_POST['titel_pre'];
		$person->vorname = $_POST['vorname'];
		$person->nachname = $_POST['nachname'];
		$person->titelpost = $_POST['titelPost'];


		if(!$datum->checkDatum($_POST['geburtsdatum']))
		{
			$save_error_daten=true;
			$message = $_POST['geburtsdatum']. "<br>" . $p->t('bewerbung/datumUngueltig');;
			$person->gebdatum = '';
		}
		else
		{
			//korrigiertes Geburtsdatum speichern
			$person->gebdatum = $datum->formatDatum($_POST['geburtsdatum'], 'Y-m-d');
		}

		$person->staatsbuergerschaft = $_POST['staatsbuergerschaft'];
		$person->geschlecht = $_POST['geschlecht'];
		if ($_POST['geschlecht'] == 'm')
		{
			$person->anrede = 'Herr';
		}
		elseif ($_POST['geschlecht'] == 'w')
		{
			$person->anrede = 'Frau';
		}
		else
		{
			$person->anrede = '';
		}
		$person->gebort = $_POST['gebort'];
		$person->geburtsnation = $_POST['geburtsnation'];
	}

	if ($person->svnr == ''
		&& isset($_POST['svnr'])
		&& $_POST['svnr'] != '')
	{
		$svnr = $_POST['svnr'];
		// Check SVNR
		if ($person->checkSvnr($svnr, $person_id))
		{
			$message = $p->t('bewerbung/svnrBereitsVorhanden');
			$save_error_daten = true;
			// Geparkten Logeintrag löschen
			$rueckstellung->deleteParked($person_id);
			// Logeintrag schreiben
			$log->log($person_id, 'Action', array(
				'name' => 'Error saving Sozialversicherungsnummer',
				'success' => false,
				'message' => 'Sozialversicherungsnummer ' . $svnr . ' already present in database'
			), 'bewerbung', 'bewerbung', null, 'online');
		}
		else
		{
			$person->svnr = $svnr;
		}
	}

	$person->new = false;

	if (!$save_error_daten)
	{
		if (!$person->save())
		{
			$message = $person->errormsg;
			$save_error_daten = true;
			// Geparkten Logeintrag löschen
			$rueckstellung->deleteParked($person_id);
			// Logeintrag schreiben
			$log->log($person_id, 'Action', array(
				'name' => 'Personal data saved',
				'success' => false,
				'message' => 'Error saving personal data. Error message says: '.$person->errormsg
			), 'bewerbung', 'bewerbung', null, 'online');
		}
		else
		{
			$save_error_daten = false;
			// Geparkten Logeintrag löschen
			$rueckstellung->deleteParked($person_id);
			// Logeintrag schreiben
			$log->log($person_id, 'Action', array(
				'name' => 'Personal data saved',
				'success' => true,
				'message' => 'Personal data has been saved or changed'
			), 'bewerbung', 'bewerbung', null, 'online');
		}
	}

	$berufstaetig = filter_input(INPUT_POST, 'berufstaetig');

	if (defined('BEWERBERTOOL_BERUFSTAETIGKEIT_NOTIZ') && BEWERBERTOOL_BERUFSTAETIGKEIT_NOTIZ === false)
	{
		$facheinschlaegig = filter_input(INPUT_POST, 'facheinschlaegig');

		if (in_array($berufstaetig, array('Vollzeit', 'Teilzeit', 'Nein'), true) &&
			in_array($facheinschlaegig, array('Ja', 'Nein'), true))
		{
			$berufscodeArray = ['Ja' => ['Vollzeit' => 6, 'Teilzeit' => 7, 'Nein' => 2],
								'Nein' => ['Vollzeit' => 9, 'Teilzeit' => 10, 'Nein' => 0]];

			$berufscode = $berufscodeArray[$facheinschlaegig][$berufstaetig];

			$prestudent = new prestudent();
			$prestudent->getPrestudenten($person_id);

			$last_prestudent = count($prestudent->result) - 1;
			foreach ($prestudent->result as $key => $prestudent_beruf)
			{
				if ($key === $last_prestudent || (is_null($prestudent_beruf->berufstaetigkeit_code)))
				{
					$prestudent_beruf->berufstaetigkeit_code = $berufscode;
					$prestudent_beruf->save();
				}
			}
		}
	}
	else
	{
		if (in_array($berufstaetig, array(
			'Vollzeit',
			'Teilzeit'
		), true))
		{

			$berufstaetig_art = filter_input(INPUT_POST, 'berufstaetig_art');
			$berufstaetig_dienstgeber = filter_input(INPUT_POST, 'berufstaetig_dienstgeber');

			$notiz = new notiz();
			$notiz->person_id = $person_id;
			$notiz->verfasser_uid = '';
			$notiz->erledigt = false;
			$notiz->insertvon = 'online'; // Nicht aendern, da in notiz.class.php nach insertvon abgefragt wird
			$notiz->insertamum = date('c');
			$notiz->start = date('Y-m-d');
			$notiz->titel = 'Berufstätigkeit';
			$notiz->text = 'Berufstätig: '.$berufstaetig.'; Dienstgeber: '.$berufstaetig_dienstgeber.'; Art der Tätigkeit: '.$berufstaetig_art;
			$notiz->save(true);
			$notiz->saveZuordnung();
		}
		elseif (in_array($berufstaetig, array('Nein'), true))
		{
			$berufstaetig_art = filter_input(INPUT_POST, 'berufstaetig_art');
			$berufstaetig_dienstgeber = filter_input(INPUT_POST, 'berufstaetig_dienstgeber');

			$notiz = new notiz();
			$notiz->person_id = $person_id;
			$notiz->verfasser_uid = '';
			$notiz->erledigt = false;
			$notiz->insertvon = 'online'; // Nicht aendern, da in notiz.class.php nach insertvon abgefragt wird
			$notiz->insertamum = date('c');
			$notiz->start = date('Y-m-d');
			$notiz->titel = 'Berufstätigkeit';
			$notiz->text = 'Nicht Berufstätig';
			$notiz->save(true);
			$notiz->saveZuordnung();
		}
	}

	if (!$eingabegesperrt)
	{
		$aufmerksamdurch = filter_input(INPUT_POST, 'aufmerksamdurch');

		// Aufmerksamdurch speichern
		$prestudent = new prestudent();
		$prestudent->getPrestudenten($person_id);

		foreach ($prestudent->result as $prestudent_eintrag)
		{
			$prestudent_eintrag->new = false;
			$prestudent_eintrag->aufmerksamdurch_kurzbz = $aufmerksamdurch;
			$prestudent_eintrag->save();
		}
	}
}

$save_error_ausbildung = '';
// Ausbildung speichern
// TODO: Umbau in UDF
if (isset($_POST['btn_ausbildung']) && ! $eingabegesperrt)
{
	$ausbildung_schule = filter_input(INPUT_POST, 'ausbildung_schule');
	$ausbildung_schuleadresse = filter_input(INPUT_POST, 'ausbildung_schuleadresse');
	if ($ausbildung_schule != '' && $ausbildung_schuleadresse != '')
	{
		$notiz = new notiz();
		$notiz->person_id = $person_id;
		$notiz->verfasser_uid = '';
		$notiz->erledigt = false;
		$notiz->insertvon = 'online_ausbildung'; // Nicht aendern, da in notiz.class.php nach insertvon abgefragt wird
		$notiz->insertamum = date('c');
		$notiz->start = date('Y-m-d');
		$notiz->titel = 'Ausbildung';
		$notiz->text = 'Name der Schule:' . $ausbildung_schule . '; Adresse der Schule:' . $ausbildung_schuleadresse;
		$notiz->save(true);
		$notiz->saveZuordnung();
	}
}

$save_error_kontakt = '';
// Kontaktdaten speichern
if (isset($_POST['btn_kontakt']) && ! $eingabegesperrt)
{
	$save_error_kontakt = false;
	$kontakt = new kontakt();
	$kontakt->load_persKontakttyp($person->person_id, 'email');
	// gibt es schon kontakte von user
	if (count($kontakt->result) > 0)
	{
		/*
		 * Eine bestehende Mailadresse darf nicht bearbeitet oder entfernt werden
		 * // Es gibt bereits einen Emailkontakt
		 * $kontakt_id = $kontakt->result[0]->kontakt_id;
		 * if(isset($_POST['email']) && $_POST['email'] == '')
		 * {
		 * // löschen
		 * $kontakt->delete($kontakt_id);
		 * }
		 * else
		 * {
		 * $kontakt->person_id = $person->person_id;
		 * $kontakt->kontakt_id = $kontakt_id;
		 * $kontakt->zustellung = true;
		 * $kontakt->kontakttyp = 'email';
		 * $kontakt->kontakt = trim($_POST['email']);
		 * $kontakt->new = false;
		 * if(!$kontakt->save())
		 * {
		 * $message = $kontakt->errormsg;
		 * $save_error_kontakt=true;
		 * }
		 * else
		 * $save_error_kontakt=false;
		 * }
		 */
	}
	else
	{
		// Pruefen, ob die Mailadresse schon im System existiert
		$return = check_load_bewerbungen(trim($_POST['email']));
		if ($return)
		{
			$message = $p->t('bewerbung/mailadresseBereitsVorhanden', array(
				trim($_POST['email'])
			));
			$save_error_kontakt = true;
		}
		else
		{
			// neuen Kontakt anlegen
			$kontakt->person_id = $person->person_id;
			$kontakt->zustellung = true;
			$kontakt->kontakttyp = 'email';
			$kontakt->kontakt = trim($_POST['email']);
			$kontakt->insertamum = date('Y-m-d H:i:s');
			$kontakt->insertvon = 'online';
			$kontakt->new = true;

			if (! $kontakt->save())
			{
				$message = $kontakt->errormsg;
				$save_error_kontakt = true;
			}
			else
			{
				$save_error_kontakt = false;
				// Logeintrag schreiben
				$log->log($person->person_id, 'Action', array(
					'name' => 'E-Mail adress saved',
					'success' => true,
					'message' => 'E-Mail adress ' . trim($_POST['email']) . ' saved'
				), 'bewerbung', 'bewerbung', null, 'online');
			}
		}
	}

	if ($save_error_kontakt === false)
	{
		$kontakt_t = new kontakt();
		$kontakt_t->load_persKontakttyp($person->person_id, 'telefon');

		// gibt es schon kontakte von user
		if (count($kontakt_t->result) > 0)
		{
			// Es gibt bereits eine Telefonnummer
			$kontakt_id = $kontakt_t->result[0]->kontakt_id;
			$telefonnummer_alt = $kontakt_t->result[0]->kontakt;

			// Telefonnummer validieren
			$telefonnummer = preg_replace("/[^0-9+]/", '', $_POST['telefonnummer']);
			if ($_POST['telefonnummer'] == '')
			{
				// löschen
				$kontakt_t->delete($kontakt_id);
			}
			elseif ($telefonnummer != '' && $telefonnummer != $telefonnummer_alt)
			{
				$kontakt_t->person_id = $person->person_id;
				$kontakt_t->kontakt_id = $kontakt_id;
				$kontakt_t->zustellung = true;
				$kontakt_t->kontakttyp = 'telefon';
				$kontakt_t->kontakt = $telefonnummer;
				$kontakt_t->updateamum = date('Y-m-d H:i:s');
				$kontakt_t->updatevon = 'online';
				$kontakt_t->new = false;

				if (! $kontakt_t->save())
				{
					$message = $kontakt_t->errormsg;
					$save_error_kontakt = true;
				}
				else
				{
					$save_error_kontakt = false;
					// Geparkten Logeintrag löschen
					$rueckstellung->deleteParked($person->person_id);
					// Logeintrag schreiben
					$log->log($person->person_id, 'Action', array(
						'name' => 'Phone number updated',
						'success' => true,
						'message' => 'Phone number ' . $telefonnummer_alt . ' changed to ' . $telefonnummer
					), 'bewerbung', 'bewerbung', null, 'online');
				}
			}
			else
			{
				$message = 'Invalid phone number';
				$save_error_kontakt = true;
			}
		}
		else
		{
			if ($_POST['telefonnummer'] != '')
			{
				// Telefonnummer validieren
				$telefonnummer = preg_replace("/[^0-9+]/", '', $_POST['telefonnummer']);

				// Wenn nache preg_replace Daten uebrig bleiben, neuen Kontakt anlegen
				if ($telefonnummer != '')
				{
					$kontakt_t->person_id = $person->person_id;
					$kontakt_t->zustellung = true;
					$kontakt_t->kontakttyp = 'telefon';
					$kontakt_t->kontakt = $telefonnummer;
					$kontakt_t->insertamum = date('Y-m-d H:i:s');
					$kontakt_t->insertvon = 'online';
					$kontakt_t->new = true;

					if (! $kontakt_t->save())
					{
						$message = $kontakt_t->errormsg;
						$save_error_kontakt = true;
					}
					else
					{
						$save_error_kontakt = false;
						// Geparkten Logeintrag löschen
						$rueckstellung->deleteParked($person->person_id);
						// Logeintrag schreiben
						$log->log($person->person_id, 'Action', array(
							'name' => 'New phone number saved',
							'success' => true,
							'message' => 'Phone number ' . $telefonnummer . ' saved'
						), 'bewerbung', 'bewerbung', null, 'online');
					}
				}
				else
				{
					$message = 'Invalid phone number';
					$save_error_kontakt = true;
				}
			}
		}
	}
	// if($save_error_kontakt===false)
	{
		// Adresse Speichern
		if ((isset($_POST['strasse']) && $_POST['strasse'] != '') || (isset($_POST['plz']) && $_POST['plz'] != '') || (isset($_POST['ort']) && $_POST['ort'] != ''))
		{
			$adresse = new adresse();
			$adresse->load_pers($person->person_id);
			$gemeinde = '';
			if (count($adresse->result) > 0)
			{
				// Wenn die Nation Oesterreich ist, wird die Gemeinde aus der DB ermittelt
				if (isset($_POST['nation']) && $_POST['nation'] == 'A' && isset($_POST['ort']) && $_POST['ort'] != '' && isset($_POST['plz']) && $_POST['plz'] != '')
				{
					$gemeinde_obj = new gemeinde();
					$gemeinde_obj->getGemeinde(trim($_POST['ort']), '', trim($_POST['plz']));
					$gemeinde = $gemeinde_obj->result[0]->name;
				}
				// gibt es schon eine adresse, wird die erste adresse genommen und upgedatet
				$adresse_help = new adresse();
				$adresse_help->load($adresse->result[0]->adresse_id);
				$strasse_alt = $adresse_help->strasse;
				$strasse_neu = isset($_POST['strasse']) ? trim($_POST['strasse']) : '';
				$plz_alt = $adresse_help->plz;
				$plz_neu = isset($_POST['plz']) ? trim($_POST['plz']) : '';
				$ort_alt = $adresse_help->ort;
				$ort_neu = isset($_POST['ort']) ? trim($_POST['ort']) : '';
				$nation_alt = $adresse_help->nation;
				$nation_neu = isset($_POST['nation']) ? $_POST['nation'] : '';

				// gibt schon eine Adresse
				$adresse_help->strasse = $strasse_neu;
				$adresse_help->plz = $plz_neu;
				$adresse_help->ort = $ort_neu;
				$adresse_help->gemeinde = $gemeinde;
				$adresse_help->nation = $nation_neu;
				$adresse_help->updateamum = date('Y-m-d H:i:s');
				$adresse_help->updatevon = 'online';
				$adresse_help->new = false;
				if (! $adresse_help->save())
				{
					$message = $adresse_help->errormsg;
					$save_error_kontakt = true;
				}
				else
				{
					$save_error_kontakt = false;
					// Logeintrag schreiben wenn sich etwas geändert hat
					if ($strasse_alt != $strasse_neu ||
						$plz_alt != $plz_neu ||
						$ort_alt != $ort_neu ||
						$nation_alt != $nation_neu)
					{
						// Geparkten Logeintrag löschen
						$rueckstellung->deleteParked($person->person_id);
						// Logeintrag anlegen
						$log->log($person->person_id, 'Action', array(
							'name' => 'Adress updated',
							'success' => true,
							'message' => 'Adress ID' . $adresse->result[0]->adresse_id . ' updated'
						), 'bewerbung', 'bewerbung', null, 'online');
					}
				}
			}
			else
			{
				// adresse neu anlegen
				$adresse->typ = 'h';
				$adresse->strasse = $_POST['strasse'];
				$adresse->plz = trim($_POST['plz']);
				$adresse->ort = trim($_POST['ort']);
				$adresse->gemeinde = $_POST['gemeinde'];
				$adresse->nation = $_POST['nation'];
				$adresse->insertamum = date('Y-m-d H:i:s');
				$adresse->insertvon = 'online';
				$adresse->updateamum = date('Y-m-d H:i:s');
				$adresse->updatevon = 'online';
				$adresse->person_id = $person->person_id;
				$adresse->zustelladresse = true;
				$adresse->heimatadresse = true;
				$adresse->new = true;
				if (! $adresse->save())
				{
					$message = $adresse->errormsg;
					$save_error = true;
				}
				else
				{
					$save_error = false;
					// Geparkten Logeintrag löschen
					$rueckstellung->deleteParked($person->person_id);
					// Logeintrag schreiben
					$log->log($person->person_id, 'Action', array(
						'name' => 'New adress saved',
						'success' => true,
						'message' => 'New adress has been saved'
					), 'bewerbung', 'bewerbung', null, 'online');
				}
			}
		}
	}
}

$save_error_rechnungskontakt = '';
// Rechnungsdaten speichern
if (isset($_POST['btn_rechnungskontakt']))
{
	$save_error_rechnungskontakt = false;
	if ($save_error_rechnungskontakt === false)
	{
		$kontakt_t = new kontakt();
		$kontakt_t->load_persKontakttyp($person->person_id, 're_telefon');

		// gibt es schon kontakte von user
		if (count($kontakt_t->result) > 0)
		{
			// Es gibt bereits eine Telefonnummer
			$kontakt_id = $kontakt_t->result[0]->kontakt_id;

			// Telefonnummer validieren
			$telefonnummer = preg_replace("/[^0-9+]/", '', $_POST['re_telefonnummer']);
			if ($_POST['re_telefonnummer'] == '')
			{
				// löschen
				$kontakt_t->delete($kontakt_id);
			}
			elseif ($telefonnummer != '')
			{
				$kontakt_t->person_id = $person->person_id;
				$kontakt_t->kontakt_id = $kontakt_id;
				$kontakt_t->zustellung = false;
				$kontakt_t->kontakttyp = 're_telefon';
				$kontakt_t->kontakt = $telefonnummer;
				$kontakt_t->updateamum = date('Y-m-d H:i:s');
				$kontakt_t->updatevon = 'online';
				$kontakt_t->new = false;

				if (! $kontakt_t->save())
				{
					$message = $kontakt_t->errormsg;
					$save_error_rechnungskontakt = true;
				}
				else
					$save_error_rechnungskontakt = false;
			}
			else
			{
				$message = 'Invalid phone number';
				$save_error_rechnungskontakt = true;
			}
		}
		else
		{
			if ($_POST['re_telefonnummer'] != '')
			{
				// Telefonnummer validieren
				$telefonnummer = preg_replace("/[^0-9+]/", '', $_POST['re_telefonnummer']);

				// Wenn nache preg_replace Daten uebrig bleiben, neuen Kontakt anlegen
				if ($telefonnummer != '')
				{
					$kontakt_t->person_id = $person->person_id;
					$kontakt_t->zustellung = false;
					$kontakt_t->kontakttyp = 're_telefon';
					$kontakt_t->kontakt = $telefonnummer;
					$kontakt_t->insertamum = date('Y-m-d H:i:s');
					$kontakt_t->insertvon = 'online';
					$kontakt_t->new = true;

					if (! $kontakt_t->save())
					{
						$message = $kontakt_t->errormsg;
						$save_error_rechnungskontakt = true;
					}
					else
						$save_error_rechnungskontakt = false;
				}
				else
				{
					$message = 'Invalid phone number';
					$save_error_rechnungskontakt = true;
				}
			}
		}
	}
	if ($save_error_rechnungskontakt === false)
	{
		$kontakt_t = new kontakt();
		$kontakt_t->load_persKontakttyp($person->person_id, 're_email');

		// gibt es schon kontakte von user
		if (count($kontakt_t->result) > 0)
		{
			// Es gibt bereits eine Telefonnummer
			$kontakt_id = $kontakt_t->result[0]->kontakt_id;
			$email = $_POST['re_email'];
			if ($email == '')
			{
				// löschen
				$kontakt_t->delete($kontakt_id);
			}
			elseif ($email != '')
			{
				$kontakt_t->person_id = $person->person_id;
				$kontakt_t->kontakt_id = $kontakt_id;
				$kontakt_t->zustellung = false;
				$kontakt_t->kontakttyp = 're_email';
				$kontakt_t->kontakt = $email;
				$kontakt_t->updateamum = date('Y-m-d H:i:s');
				$kontakt_t->updatevon = 'online';
				$kontakt_t->new = false;

				if (! $kontakt_t->save())
				{
					$message = $kontakt_t->errormsg;
					$save_error_rechnungskontakt = true;
				}
				else
					$save_error_rechnungskontakt = false;
			}
			else
			{
				$message = 'Invalid Email';
				$save_error_rechnungskontakt = true;
			}
		}
		else
		{
			if ($_POST['re_email'] != '')
			{
				// Telefonnummer validieren
				$email = $_POST['re_email'];

				// Wenn nache preg_replace Daten uebrig bleiben, neuen Kontakt anlegen
				if ($email != '')
				{
					$kontakt_t->person_id = $person->person_id;
					$kontakt_t->zustellung = false;
					$kontakt_t->kontakttyp = 're_email';
					$kontakt_t->kontakt = $email;
					$kontakt_t->insertamum = date('Y-m-d H:i:s');
					$kontakt_t->insertvon = 'online';
					$kontakt_t->new = true;

					if (! $kontakt_t->save())
					{
						$message = $kontakt_t->errormsg;
						$save_error_rechnungskontakt = true;
					}
					else
						$save_error_rechnungskontakt = false;
				}
				else
				{
					$message = 'Invalid Email';
					$save_error_rechnungskontakt = true;
				}
			}
		}
	}
	// if($save_error_kontakt===false)
	{
		// Adresse Speichern
		if ((isset($_POST['re_strasse']) && $_POST['re_strasse'] != '') || (isset($_POST['re_plz']) && $_POST['re_plz'] != '') || (isset($_POST['re_ort']) && $_POST['re_ort'] != ''))
		{
			$adresse = new adresse();
			$adresse->load_rechnungsadresse($person->person_id);
			$gemeinde = '';
			if ((isset($_POST['re_vorname']) && $_POST['re_vorname'] != '') || (isset($_POST['re_nachname']) && $_POST['re_nachname'] != '') || (isset($_POST['re_titel']) && $_POST['re_titel'] != ''))
			{
				$name = $_POST['re_anrede'] . '|' . $_POST['re_titel'] . '|' . $_POST['re_vorname'] . '|' . $_POST['re_nachname'];
			}
			else
			{
				$name = '';
			}
			if (count($adresse->result) > 0)
			{
				// Wenn die Nation Oesterreich ist, wird die Gemeinde aus der DB ermittelt
				if (isset($_POST['re_nation']) && $_POST['re_nation'] == 'A' && isset($_POST['re_ort']) && $_POST['re_ort'] != '' && isset($_POST['re_plz']) && $_POST['re_plz'] != '')
				{
					$gemeinde_obj = new gemeinde();
					$gemeinde_obj->getGemeinde($_POST['re_ort'], '', trim($_POST['re_plz']));
					$gemeinde = $gemeinde_obj->result[0]->name;
				}
				// gibt es schon eine adresse, wird die erste adresse genommen und upgedatet
				$adresse_help = new adresse();
				$adresse_help->load($adresse->result[0]->adresse_id);

				// gibt schon eine Adresse
				$adresse_help->strasse = isset($_POST['re_strasse']) ? trim($_POST['re_strasse']) : '';
				$adresse_help->plz = isset($_POST['re_plz']) ? trim($_POST['re_plz']) : '';
				$adresse_help->ort = isset($_POST['re_ort']) ? trim($_POST['re_ort']) : '';
				$adresse_help->gemeinde = $gemeinde;
				$adresse_help->nation = isset($_POST['re_nation']) ? $_POST['re_nation'] : '';
				$adresse_help->updateamum = date('Y-m-d H:i:s');
				$adresse_help->updatevon = 'online';
				$adresse_help->name = $name;
				$adresse_help->new = false;
				if (! $adresse_help->save())
				{
					$message = $adresse_help->errormsg;
					$save_error_rechnungskontakt = true;
				}
				else
					$save_error_rechnungskontakt = false;
			}
			else
			{
				// adresse neu anlegen
				$adresse->typ = 'r';
				$adresse->strasse = $_POST['re_strasse'];
				$adresse->plz = trim($_POST['re_plz']);
				$adresse->ort = $_POST['re_ort'];
				$adresse->gemeinde = $_POST['re_gemeinde'];
				$adresse->nation = $_POST['re_nation'];
				$adresse->insertamum = date('Y-m-d H:i:s');
				$adresse->insertvon = 'online';
				$adresse->updateamum = date('Y-m-d H:i:s');
				$adresse->updatevon = 'online';
				$adresse->person_id = $person->person_id;
				$adresse->zustelladresse = false;
				$adresse->heimatadresse = false;
				$adresse->rechnungsadresse = true;
				$adresse->name = $name;
				$adresse->new = true;
				if (! $adresse->save())
				{
					$message = $adresse->errormsg;
					$save_error_rechnungskontakt = true;
				}
				else
					$save_error_rechnungskontakt = false;
			}
		}
	}
}

$save_error_zgv = '';
if (isset($_POST['btn_zgv']))
{
	// Zugangsvoraussetzungen speichern
	$prestudent = new prestudent();
	$prestudent->getPrestudenten($person_id);

	// $master_zgv_art = filter_input(INPUT_POST, 'master_zgv_art', FILTER_VALIDATE_INT);

	$save_error_zgv = false;
	// Datumsformat Bachelor überprüfen
	if (filter_input(INPUT_POST, 'bachelor_zgv_datum') != '')
	{
		if (! preg_match('/^\d{2}\.\d{2}\.(\d{2}|\d{4})$/ ', filter_input(INPUT_POST, 'bachelor_zgv_datum')))
		{
			$message = $p->t('bewerbung/datumUngueltig');
			$save_error_zgv = true;
			$datum_bachelor = '';
		}
		else
		{
			$ds = explode('.', filter_input(INPUT_POST, 'bachelor_zgv_datum'));
			if (! checkdate($ds[1], $ds[0], $ds[2]))
			{
				$message = $p->t('bewerbung/datumUngueltig');
				$save_error_zgv = true;
				$datum_bachelor = '';
			}
		}
	}
	// Datumsformat Master überprüfen
	if (filter_input(INPUT_POST, 'master_zgv_datum') != '')
	{
		if (! preg_match('/^\d{2}\.\d{2}\.(\d{2}|\d{4})$/ ', filter_input(INPUT_POST, 'master_zgv_datum')))
		{
			$message = $p->t('bewerbung/datumUngueltig');
			$save_error_zgv = true;
			$datum_master = '';
		}
		else
		{
			$ds = explode('.', filter_input(INPUT_POST, 'master_zgv_datum'));
			if (! checkdate($ds[1], $ds[0], $ds[2]))
			{
				$message = $p->t('bewerbung/datumUngueltig');
				$save_error_zgv = true;
				$datum_master = '';
			}
		}
	}
	$datum_bachelor = $datum->formatDatum(filter_input(INPUT_POST, 'bachelor_zgv_datum'), 'Y-m-d');
	$datum_master = $datum->formatDatum(filter_input(INPUT_POST, 'master_zgv_datum'), 'Y-m-d');

	if ($datum_bachelor > date('Y-m-d'))
	{
		$message = $p->t('bewerbung/zgvDatumNichtZukunft');
		$save_error_zgv = true;
		$datum_bachelor = '';
	}
	if ($datum_master > date('Y-m-d'))
	{
		$message = $p->t('bewerbung/zgvDatumNichtZukunft');
		$save_error_zgv = true;
		$datum_master = '';
	}

	if (! $save_error_zgv)
	{
		foreach ($prestudent->result as $prestudent_eintrag)
		{

			$prestudent_eintrag->new = false;
			$prestudent_eintrag->zgv_code = ($prestudent_eintrag->zgv_code == '' ? filter_input(INPUT_POST, 'bachelor_zgv_art', FILTER_VALIDATE_INT) : $prestudent_eintrag->zgv_code);
			$prestudent_eintrag->zgvort = ($prestudent_eintrag->zgvort == '' ? filter_input(INPUT_POST, 'bachelor_zgv_ort') : $prestudent_eintrag->zgvort);
			// An der FHTW darf das ZGV-Datum nicht vom Bewerber gesetzt werden, da dies zum prüfen der ZGV verwendet wird
			if (CAMPUS_NAME != 'FH Technikum Wien')
			{
				$prestudent_eintrag->zgvdatum = ($prestudent_eintrag->zgvdatum == '' ? $datum_bachelor : $prestudent_eintrag->zgvdatum);
			}
			$prestudent_eintrag->zgvnation = ($prestudent_eintrag->zgvnation == '' ? filter_input(INPUT_POST, 'bachelor_zgv_nation') : $prestudent_eintrag->zgvnation);
			$prestudent_eintrag->updateamum = date('Y-m-d H:i:s');
			$prestudent_eintrag->updatevon = 'online';

			$prestudent_eintrag->zgvmas_code = ($prestudent_eintrag->zgvmas_code == '' ? filter_input(INPUT_POST, 'master_zgv_art', FILTER_VALIDATE_INT) : $prestudent_eintrag->zgvmas_code);
			$prestudent_eintrag->zgvmaort = ($prestudent_eintrag->zgvmaort == '' ? filter_input(INPUT_POST, 'master_zgv_ort') : $prestudent_eintrag->zgvmaort);
			// An der FHTW darf das ZGV-Datum nicht vom Bewerber gesetzt werden, da dies zum prüfen der ZGV verwendet wird
			if (CAMPUS_NAME != 'FH Technikum Wien')
			{
				$prestudent_eintrag->zgvmadatum = ($prestudent_eintrag->zgvmadatum == '' ? $datum_master : $prestudent_eintrag->zgvmadatum);
			}
			$prestudent_eintrag->zgvmanation = ($prestudent_eintrag->zgvmanation == '' ? filter_input(INPUT_POST, 'master_zgv_nation') : $prestudent_eintrag->zgvmanation);
			$prestudent_eintrag->updateamum = date('Y-m-d H:i:s');
			$prestudent_eintrag->updatevon = 'online';

			$prestudent_eintrag->updateamum = date('c');

			if (! $prestudent_eintrag->save())
			{
				die($p->t('global/fehlerBeimSpeichernDerDaten'));
			}
		}
	}
}

// Notizen speichern
if (isset($_POST['btn_notiz']))
{
	$anmerkung = filter_input(INPUT_POST, 'anmerkung');
	$prestudent_id = filter_input(INPUT_POST, 'prestudent_id');

	// Es soll nur eine Notiz pro Person gespeichert werden
	$notiz = new notiz;
	$notiz->getBewerbungstoolNotizen($person_id);

	if ($anmerkung != '' && count($notiz->result) == 0)
	{
		$notiz = new notiz();
		$notiz->person_id = $person_id;
		$notiz->prestudent_id = $prestudent_id;
		$notiz->verfasser_uid = '';
		$notiz->erledigt = false;
		$notiz->insertvon = 'online_notiz'; // Nicht aendern, da in notiz.class.php nach insertvon abgefragt wird
		$notiz->insertamum = date('c');
		$notiz->start = date('Y-m-d');
		$notiz->titel = 'Anmerkung zur Bewerbung';
		$notiz->text = $anmerkung;
		$notiz->save(true);
		$notiz->saveZuordnung();

		// Geparkten Logeintrag löschen
		$rueckstellung->deleteParked($person_id);
		// Logeintrag schreiben
		$log->log($person_id, 'Action', array(
			'name' => 'New notiz saved',
			'success' => true,
			'message' => 'New notiz has been saved'
		), 'bewerbung', 'bewerbung', null, 'online');
	}
}
// Notizen mit Ajax speichern
$saveNotiz = filter_input(INPUT_POST, 'saveNotiz', FILTER_VALIDATE_BOOLEAN);

if ($saveNotiz)
{
	$person_id = filter_input(INPUT_POST, 'person_id');
	$prestudent_id = filter_input(INPUT_POST, 'prestudent_id');
	$anmerkungstext = trim(filter_input(INPUT_POST, 'anmerkungstext'));

	// Es soll nur eine Notiz pro Person und Prestudent gespeichert werden
	$notiz = new notiz;
	$notiz->getBewerbungstoolNotizen($person_id, $prestudent_id);

	$prestudentObj = new prestudent($prestudent_id);
	$studiengang =  new studiengang($prestudentObj->studiengang_kz);

	if ($anmerkungstext != '' && count($notiz->result) == 0)
	{
		$notiz = new notiz();
		$notiz->person_id = $person_id;
		$notiz->prestudent_id = $prestudent_id;
		$notiz->verfasser_uid = '';
		$notiz->erledigt = false;
		$notiz->insertvon = 'online_notiz'; // Nicht aendern, da in notiz.class.php nach insertvon abgefragt wird
		$notiz->insertamum = date('c');
		$notiz->start = date('Y-m-d');
		$notiz->titel = 'Anmerkung zur Bewerbung ('.$studiengang->kuerzel.')';
		$notiz->text = htmlspecialchars($anmerkungstext);
		$notiz->save(true);

		if ($notiz->saveZuordnung())
		{
			// Geparkten Logeintrag löschen
			$rueckstellung->deleteParked($person_id);
			// Logeintrag schreiben
			$log->log($person_id, 'Action', array(
				'name' => 'New notiz saved',
				'success' => true,
				'message' => 'New notiz has been saved'
			), 'bewerbung', 'bewerbung', null, 'online');

			echo json_encode(array(
					'status'=>'ok',
					'insertamum'=>date('d.m.Y', strtotime($notiz->insertamum)),
					'anmerkung'=>$notiz->text));
		}
		else
		{
			echo json_encode(array(
				'status' => 'fehler',
				'msg' => 'Error saving note for '.$person_id
			));
		}
		exit();
	}
}

$save_error_zugangscode = '';
// Neuen Zugangscode generieren
if (isset($_POST['btn_new_accesscode']))
{
	$save_error_zugangscode = false;
	$person = new person($person_id);
	$zugangscode = substr(md5(openssl_random_pseudo_bytes(20)), 0, 15);

	$person->zugangscode = $zugangscode;
	$person->updateamum = date('Y-m-d H:i:s');
	$person->updatevon = 'online';
	$person->new = false;

	if(!$person->save())
	{
		$message = $p->t('global/fehlerBeimSpeichernDerDaten');
		$save_error_zugangscode = true;
	}
	else
	{
		// Geparkten Logeintrag löschen
		$rueckstellung->deleteParked($person_id);
		// Logeintrag schreiben
		$log->log($person_id,
			'Action',
			array('name'=>'New access code','success'=>true,'message'=>'User generated a new access code.'),
			'bewerbung',
			'bewerbung',
			null,
			'online'
			);
		$message = $p->t('bewerbung/erfolgsMessageNeuerZugangscode', array($zugangscode));
	}
}

// Upload eines leeren Dokuments oder wenn die Datei zu groß ist
if (empty($_POST) && isset($_GET['fileupload']) && $_GET['fileupload'] == 'true')
{
	$save_error_dokumente = true;
	$message = $p->t('bewerbung/dateiUploadLeer');
}
// Upload eines Dokuments
if (isset($_POST['submitfile']))
{
	$save_error_dokumente = false;
	$message = '';
	$ausstellungsnation = (isset($_POST['ausstellungsnation'])) ? $_POST['ausstellungsnation'] : '';
	// Check, ob ein File gewaelt wurde
	if (!empty($_FILES['file']['tmp_name']))
	{
		$dokumenttyp_upload = $_POST['dokumenttyp'];

		// Check, ob Akte vorhanden
		$akte = new akte();
		$akte->getAkten($person_id, $dokumenttyp_upload, null, null, false, 'nachgereicht DESC');

		if (!defined('BEWERBERTOOL_ANZAHL_DOKUMENTPLOAD_JE_TYP')
			|| ((is_numeric(BEWERBERTOOL_ANZAHL_DOKUMENTPLOAD_JE_TYP)
				&& count($akte->result) < BEWERBERTOOL_ANZAHL_DOKUMENTPLOAD_JE_TYP)
				||
				(   $akte->result[0]->inhalt_vorhanden == false
					&& $akte->result[0]->dms_id == '')))
		// Wie verfahren wir mit Nachreichungen, wenn mehr als 1 Dokument vorhanden ist??
		//if (!isset($akte->result[0]) || ($akte->result[0]->inhalt == '' && $akte->result[0]->dms_id == ''))
		{
			if ($dokumenttyp_upload != '')
			{
				// DMS-Eintrag erstellen
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
						// Wenn Akte mit DMS-ID vorhanden, wird diese geladen
						// Derzeit soll nur eine Akte pro Typ hochgeladen werden können
						// Daher wird immer ein neuer DMS-Eintrag erstellt
						$version = '0';
						$dms_id = '';

						$dms = new dms();
						if(!$dms->setPermission($uploadfile))
						{
							$save_error_dokumente = true;
							$message .= $dms->errormsg;
						}

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
							$save_error_dokumente = true;
							$message .= $p->t('global/fehlerBeimSpeichernDerDaten');
						}
					}
					else
					{
						$save_error_dokumente = true;
						$message .= $p->t('global/dateiNichtErfolgreichHochgeladen');
					}
				}
				else
				{
					$save_error_dokumente = true;
					$message .= $p->t('bewerbung/falscherDateityp');
				}
			}
			else
			{
				$save_error_dokumente = true;
				$message .= $p->t('bewerbung/keinDokumententypUebergeben');
			}

			if (isset($_FILES['file']['tmp_name']) && ! $save_error_dokumente)
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
				$akte->getAkten($person_id, $dokumenttyp_upload);
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
					$save_error_dokumente = true;
					$message .= $p->t('global/fehleraufgetreten') . ": $akte->errormsg";
				}
				else
				{
					$save_error_dokumente = false;
					$message .= $p->t('global/erfolgreichgespeichert');

					// Logeintrag schreiben
					$log->log($person_id, 'Action', array(
						'name' => 'Document ' . $akte->bezeichnung . ' uploaded',
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
						if ($person->load($person_id))
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
			}
		}
		else
		{
			$save_error_dokumente = true;
			$message .= $p->t('bewerbung/akteBereitsVorhanden');
		}
	}
	else
	{
		$save_error_dokumente = true;
		$message .= $p->t('bewerbung/keineDateiAusgewaehlt');
	}
}

$addStudiengang = filter_input(INPUT_POST, 'addStudiengang', FILTER_VALIDATE_BOOLEAN);

if ($addStudiengang)
{
	$return = BewerbungPersonAddStudiengang(
		$_POST['stgkz'],
		$_POST['anm'],
		$person,
		$_POST['studiensemester'],
		(isset($_POST['orgform'])?$_POST['orgform']:''),
		(isset($_POST['sprache'])?$_POST['sprache']:'')
		);
	if ($return === true)
		echo json_encode(array('status'=>'ok'));
	else
		echo json_encode(array(
			'status' => 'fehler',
			'msg' => $return
		));
	exit();
}

$addStudienplan = filter_input(INPUT_POST, 'addStudienplan', FILTER_VALIDATE_BOOLEAN);
if ($addStudienplan)
{
	$return = BewerbungPersonAddStudienplan(
		$_POST['studienplan_id'],
		$person,
		$_POST['studiensemester'],
		isset($_POST['zgv_nation']) ? $_POST['zgv_nation'] : null
	);
	if ($return === true)
		echo json_encode(array('status'=>'ok'));
	else
		echo json_encode(array(
			'status' => 'fehler',
			'msg' => $return
		));
		exit();
}

$getGemeinden = filter_input(INPUT_POST, 'getGemeinden', FILTER_VALIDATE_BOOLEAN);

if ($getGemeinden)
{
	$return = BewerbungGetGemeinden($_POST['plz']);
	if ($return === false)
		echo json_encode(array(
			'status' => 'error',
			'msg' => $return
		));
	else
		echo json_encode(array(
			'status' => 'ok',
			'gemeinden' => $return
		));
	exit();
}

$prestudent = new prestudent();
if (! $prestudent->getPrestudenten($person->person_id))
{
	die($p->t('global/fehlerBeimLadenDesDatensatzes'));
}

// Abfrage ob persönliche Daten vollständig sind
if ($person->vorname
	&& $person->nachname
	&& $person->gebdatum
	&& $person->staatsbuergerschaft
	&& $person->geschlecht
	)
{
	if (defined('BEWERBERTOOL_AUFMERKSAMDURCH_PFLICHT') && BEWERBERTOOL_AUFMERKSAMDURCH_PFLICHT === true
		&& isset($prestudent->result[0])
		&& $prestudent->result[0]->aufmerksamdurch_kurzbz == '')
	{
		$status_person = false;
		$status_person_text = $unvollstaendig;
	}
	elseif (defined('BEWERBERTOOL_GEBURTSORT_PFLICHT') && BEWERBERTOOL_GEBURTSORT_PFLICHT === true
		&& $person->gebort == '')
	{
		$status_person = false;
		$status_person_text = $unvollstaendig;
	}
	elseif (defined('BEWERBERTOOL_GEBURTSNATION_PFLICHT') && BEWERBERTOOL_GEBURTSNATION_PFLICHT === true
		&& $person->geburtsnation == '')
	{
		$status_person = false;
		$status_person_text = $unvollstaendig;
	}
	else
	{
		$status_person = true;
		$status_person_text = $vollstaendig;
	}
}
else
{
	$status_person = false;
	$status_person_text = $unvollstaendig;
}
// Wenn Eingabegesperrt muss der Punkt vollständig sein, da der Bewerber sonst nicht abschicken kann
if ($eingabegesperrt == true)
{
	$status_person = true;
	$status_person_text = $vollstaendig;
}

$kontakt = new kontakt();
$kontakt->load_persKontakttyp($person->person_id, 'email');
$kontakttel = new kontakt();
$kontakttel->load_persKontakttyp($person->person_id, 'telefon');
$adresse = new adresse();
$adresse->load_pers($person->person_id);

if (isset($adresse->result[0])
	&& count($kontakt->result)
	&& count($adresse->result[0]->strasse)
	&& count($adresse->result[0]->plz)
	&& count($adresse->result[0]->ort)
	&& count($adresse->result[0]->nation)
	&& count($kontakttel->result))
{
	$status_kontakt = true;
	$status_kontakt_text = $vollstaendig;
}
else
{
	$status_kontakt = false;
	$status_kontakt_text = $unvollstaendig;
}
// Wenn Eingabegesperrt muss der Punkt vollständig sein, da der Bewerber sonst nicht abschicken kann
if ($eingabegesperrt == true)
{
	$status_kontakt = true;
	$status_kontakt_text = $vollstaendig;
}

$master_zgv_done = false;
$bachelor_zgv_done = false;
$stg = new studiengang();

foreach ($prestudent->result as $prestudent_eintrag)
{
	$studiengaenge[] = $prestudent_eintrag->studiengang_kz;
}
foreach ($prestudent->result as $prestudent_eintrag)
{
	$master_zgv_done = isset($prestudent_eintrag->zgvmas_code);
	if ($master_zgv_done)
		break;
}
foreach ($prestudent->result as $prestudent_eintrag)
{
	$bachelor_zgv_done = isset($prestudent_eintrag->zgv_code);
	if ($bachelor_zgv_done)
		break;
}

$status_zgv_bak = false;
$status_zgv_bak_text = $unvollstaendig;
$status_zgv_mas = false;
$status_zgv_mas_text = $unvollstaendig;
if (! defined('BEWERBERTOOL_ZGV_ANZEIGEN') || BEWERBERTOOL_ZGV_ANZEIGEN == true)
{
	if (isset($studiengaenge))
	{
		$types = $stg->getTypes($studiengaenge);

		if ($bachelor_zgv_done)
		{
			$status_zgv_bak = true;
			$status_zgv_bak_text = $vollstaendig;
		}
		if ((! in_array('m', $types, true) || $master_zgv_done))
		{
			$status_zgv_mas = true;
			$status_zgv_mas_text = $vollstaendig;
		}
	}
}
else
{
	$status_zgv_text = $vollstaendig;
	$status_zgv_bak = true;
	$status_zgv_bak_text = $vollstaendig;
	$status_zgv_mas = true;
	$status_zgv_mas_text = $vollstaendig;
}

$studiensemester_bewerbungen = array();
$prestudent_bewerbungen = getBewerbungen($person_id, true);

$status_abschicken = false;
$status_abschicken_text = $unvollstaendig;
$count_abgeschickte = 0;

// Studiensemester der aktiven Bewerbungen laden, ansonsten jene, für die sich aktuell beworben werden kann
if ($prestudent_bewerbungen)
{
	foreach ($prestudent_bewerbungen AS $row)
	{
		$studiensemester_bewerbungen[] = $row->laststatus_studiensemester_kurzbz;

		// Checken, ob schon Bewerbungen abgeschickt wurden, wenn nicht, hervorheben
		$prestudent_status = new prestudent();
		$prestudent_status->getLastStatus($row->prestudent_id, $row->laststatus_studiensemester_kurzbz, 'Interessent');

		if ($prestudent_status->bewerbung_abgeschicktamum != '' && ($count_abgeschickte == 0 || $count_abgeschickte < count($prestudent_bewerbungen)))
		{
			$status_abschicken = true;
			$status_abschicken_text = $teilvollstaendig;
			$count_abgeschickte ++;
		}
		if ($prestudent_status->bewerbung_abgeschicktamum != '' && $count_abgeschickte == count($prestudent_bewerbungen))
		{
			$status_abschicken = true;
			$status_abschicken_text = $vollstaendig;
		}
	}
}
else
{
	$status_abschicken_text = $vollstaendig;

	$stsem = new studiensemester();
	$stsem->getStudiensemesterOnlinebewerbung();
	foreach ($stsem->studiensemester as $row)
		$studiensemester_bewerbungen[] = $row->studiensemester_kurzbz;
}


//bei vorliegendem Status Interessent für Master/Diplomstudium werden Prüfungen/Aktionen ZGV durchgeführt
if (CAMPUS_NAME == 'FH Technikum Wien' && ($prestudent->existsStatusInteressentMaster($person_id)))
{
	setDokumenteMasterZGV($person_id);
}

$studiensemester_bewerbungen =  array_unique($studiensemester_bewerbungen);

$dokumente_abzugeben = getAllDokumenteBewerbungstoolForPerson($person_id, $studiensemester_bewerbungen);

// An der FHTW wird das Dokument "Invitation Letter" zum Download angeboten, wenn bei der Person vorhanden
if (CAMPUS_NAME == 'FH Technikum Wien')
{
	$invLetter = new akte();
	$invLetter->getAkten($person_id, 'InvitLet');
	if (count($invLetter->result) > 0)
	{
		$invLetterObj = new dokument();
		$invLetterObj->loadDokumenttyp('InvitLet');
		$invLetterObj->studiengang_kz = '0';
		$invLetterObj->stufe = '0';
		$invLetterObj->anzahl_akten_vorhanden = 1;
		$invLetterObj->anzahl_akten_formal_geprueft = 1;
		$invLetterObj->anzahl_dokumente_akzeptiert = 1;
		$invLetterObj->anzahl_akten_wird_nachgereicht = 0;
		array_push($dokumente_abzugeben, $invLetterObj);
	}
}

// "Zeitbestätigung" zum Download angeboten, wenn bei der Person vorhanden
if (CAMPUS_NAME == 'FH Technikum Wien')
{
	$zeitBst = new akte();
	$zeitBst ->getAkten($person_id, 'ZeitBest');
	if (count($zeitBst->result) > 0)
	{
		$zeitBstObj = new dokument();
		$zeitBstObj->loadDokumenttyp('ZeitBest');
		$zeitBstObj->studiengang_kz = '0';
		$zeitBstObj->stufe = '0';
		$zeitBstObj->anzahl_akten_vorhanden = 1;
		$zeitBstObj->anzahl_akten_formal_geprueft = 1;
		$zeitBstObj->anzahl_dokumente_akzeptiert = 1;
		$zeitBstObj->anzahl_akten_wird_nachgereicht = 0;
		array_push($dokumente_abzugeben, $zeitBstObj);
	}
}

// $dokumente_abzugeben = new dokument();
// $dokumente_abzugeben->getAllDokumenteForPerson($person_id, true);

// $akte_person = new akte();
// $akte_person->getAkten($person_id);

$missing_document = false;
$status_dokumente = false;
$status_dokumente_arr = array();
$akzeptierte_dokumente = array();


$ben_kz = array();
$ben_bezeichnung = array();
// $ben_bezeichnung['German'][] = array();
// $ben_bezeichnung['English'][] = array();

/*
 * foreach($akte_person->result as $akte)
 * {
 * $akzeptierte_dokumente[] = $akte->dokument_kurzbz;
 * }
 */
if ($dokumente_abzugeben)
{
	foreach ($dokumente_abzugeben as $dok)
	{
		if ($dok->anzahl_akten_formal_geprueft > 0 || $dok->anzahl_dokumente_akzeptiert > 0 || $dok->anzahl_akten_wird_nachgereicht > 0)
			$akzeptierte_dokumente[] = $dok->dokument_kurzbz;

		if ($dok->pflicht
			&& ! in_array($dok->dokument_kurzbz, $akzeptierte_dokumente, true)
			&& $dok->anzahl_akten_vorhanden == 0
			&& $dok->anzahl_akten_wird_nachgereicht == 0)
		{
			$missing_document = true;
		}

		// Abfragen, bei welchen Studiengaengen das Dokument benoetigt wird
		// @todo: Studiengangsnamen auch aus Studienplan holen? -> Falls noch benötigt, einfach Bezeichnung aus aktuellster Studienordnung holen
		$benoetigtStudiengang = new dokument();
		$benoetigtStudiengang->getStudiengaengeDokument($dok->dokument_kurzbz, $person_id);

		foreach ($benoetigtStudiengang->result as $row)
		{
			//if ($dok->pflicht === true || check_person_statusbestaetigt($person_id, 'Interessent', '', $row->studiengang_kz))
			{
				$stg = new studiengang();
				$stg->load($row->studiengang_kz);

				$ben_bezeichnung['German'][$dok->dokument_kurzbz][] = $stg->bezeichnung;
				$ben_bezeichnung['English'][$dok->dokument_kurzbz][] = $stg->english;
				$ben_kz[$dok->dokument_kurzbz][] = $row->studiengang_kz;
				if ($dok->pflicht
					&& $dok->anzahl_akten_vorhanden == 0
					&& $dok->anzahl_akten_wird_nachgereicht == 0
					&& $dok->anzahl_dokumente_akzeptiert == 0) //Ergänzung um nichtakzeptierte Doks
				{
					$status_dokumente_arr[$row->studiengang_kz][$row->stufe][] = $dok->dokument_kurzbz;
				}
			}
		}
	}
}
if ($missing_document && (! defined('BEWERBERTOOL_DOKUMENTE_ANZEIGEN') || BEWERBERTOOL_DOKUMENTE_ANZEIGEN == true))
{
	$status_dokumente = false;
	$status_dokumente_text = $unvollstaendig;
}
else
{
	$status_dokumente = true;
	$status_dokumente_text = $vollstaendig;
}

// Mit den Statusabhängigen Dokumenten funktioniert die Vollständigkeits-Logik der Dokumente so nicht
// Daher wird vorläufig kein Status angezeigt
$status_dokumente = true;
$status_dokumente_text = '&nbsp;';


$konto = new konto();

$status_zahlungen = true;
$status_zahlungen_text = $vollstaendig;

if (! defined('BEWERBERTOOL_ZAHLUNGEN_ANZEIGEN') || BEWERBERTOOL_ZAHLUNGEN_ANZEIGEN == true)
{
	if (! $konto->checkKontostand($person_id, true))
	{
		$status_zahlungen = false;
		$status_zahlungen_text = $unvollstaendig;
	}
}

$status_ausbildung = true;
$status_ausbildung_text = $vollstaendig;

if (defined('BEWERBERTOOL_AUSBILDUNG_ANZEIGEN') && BEWERBERTOOL_AUSBILDUNG_ANZEIGEN == true)
{
	$notiz = new notiz();
	$notiz->getBewerbungstoolNotizenAusbildung($person_id);
	if (count($notiz->result) == 0)
	{
		$status_ausbildung = false;
		$status_ausbildung_text = $unvollstaendig;
	}
}

$prestudent = new prestudent();
if (! $prestudent->getPrestudenten($person_id))
{
	die($p->t('global/fehlerBeimLadenDesDatensatzes'));
}

$status_reihungstest = false;
$status_reihungstest_text = $unvollstaendig;
$nextWinterSemester = new studiensemester();
$nextWinterSemester->getNextStudiensemester('WS');
$nextSommerSemester = new studiensemester();
$nextSommerSemester->getNextStudiensemester('SS');
$studienplanReihungstest = getPrioStudienplanForReihungstest($person_id, $nextWinterSemester->studiensemester_kurzbz);
if (! defined('BEWERBERTOOL_REIHUNGSTEST_ANZEIGEN') || BEWERBERTOOL_REIHUNGSTEST_ANZEIGEN == true)
{
	$angemeldeteReihungstests = new reihungstest();
	$angemeldeteReihungstests->getReihungstestPerson($person_id, $nextWinterSemester->studiensemester_kurzbz);
	$angemeldeteReihungstests->getReihungstestPerson($person_id, $nextSommerSemester->studiensemester_kurzbz);

	$reihungstestTermine = getReihungstestsForOnlinebewerbung(array_column($studienplanReihungstest, 'studienplan_id'), $nextWinterSemester->studiensemester_kurzbz);
	if (count($angemeldeteReihungstests->result) > 0)
	{
		$nichtAngemeldeteRtArray = array_diff(array_column($reihungstestTermine, 'studienplan_id'), array_column($angemeldeteReihungstests->result, 'studienplan_id'));

		if (count($nichtAngemeldeteRtArray) > 0)
		{
			$status_reihungstest = false;
			$status_reihungstest_text = $unvollstaendig;
		}
		else
		{
			$status_reihungstest = true;
			$status_reihungstest_text = $vollstaendig;
		}
	}
	elseif ($reihungstestTermine != '')
	{
		$status_reihungstest = false;
		$status_reihungstest_text = $unvollstaendig;
	}
}
else
{
	$status_reihungstest = true;
	$status_reihungstest_text = $vollstaendig;
}
?><!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<meta http-equiv="X-UA-Compatible" content="chrome=1">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?php echo $p->t('bewerbung/bewerbung') ?></title>
		<link rel="stylesheet" type="text/css" href="../../../vendor/twbs/bootstrap3/dist/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="../include/css/bewerbung.css">
		<link rel="stylesheet" type="text/css" href="../include/css/croppie.css">
		<link rel="stylesheet" type="text/css" href="../include/css/legende.css">
		<script type="text/javascript" src="../../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
		<script type="text/javascript" src="../../../vendor/twbs/bootstrap3/dist/js/bootstrap.min.js"></script>
		<script src="../include/js/bewerbung.js"></script>
		<script src="../include/js/croppie.js"></script>
		<script type="text/javascript">
			var activeTab = <?php echo json_encode($active) ?>,
				basename = <?php echo json_encode(basename(__FILE__)) ?>;

			function zeichenCountdown(id, max_length)
			{
				var length,rest;
				length = document.getElementById(id).value.length;
				rest = max_length - length;
				document.getElementById('countdown_'+id).innerHTML = rest;
			};

			function changeSprache(sprache)
			{
				window.location.href = "<?php echo $_SERVER['PHP_SELF'].'?active='.$active ?>&sprache=" + sprache;
			}

			window.setTimeout(function() {
				$("#success-alert").fadeTo(500, 0).slideUp(500, function(){
					$(this).remove();
				});
				$("#success-alert_daten").fadeTo(500, 0).slideUp(500, function(){
					$(this).remove();
				});
				$("#success-alert_zgv").fadeTo(500, 0).slideUp(500, function(){
					$(this).remove();
				});
				$("#success-alert_kontakt").fadeTo(500, 0).slideUp(500, function(){
					$(this).remove();
				});
			}, 1500);

			$(document).ready(function(){
				$('[data-toggle="popover"]').popover({html:true});
				$('[data-toggle="tooltip"]').tooltip();
				$('#sprache-dropdown-content a').on('click', function()
				{
					var sprache = $(this).attr('data-sprache');
					changeSprache(sprache);
				});
				// remove fileupload from get param
				var uri = window.location.toString();
				if (uri.indexOf("?") > 0)
				{
					var clean_uri = uri.substring(0, uri.indexOf("&fileupload"));
					window.history.replaceState({}, document.title, clean_uri);
				}
			});
		</script>
	</head>
	<body class="bewerbung">
		<?php
		/*if(defined('BEWERBERTOOL_GTM')) {
			echo BEWERBERTOOL_GTM;
		}*/ //Muss noch geklärt werden, ob das Datenschutzrechtlich in Ordnung geht ?>
		<nav class="navbar navbar-default">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bewerber-navigation" aria-expanded="false">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
				</div>

				<div class="collapse navbar-collapse" id="bewerber-navigation">
					<ul class="nav navbar-nav">
						<?php if(defined('BEWERBERTOOL_UEBERSICHT_ANZEIGEN') && BEWERBERTOOL_UEBERSICHT_ANZEIGEN): ?>
						<li>
							<a href="#uebersicht" aria-controls="uebersicht" role="tab" data-toggle="tab" <?php echo ($count_abgeschickte == 0?'style="background-color: #F2DEDE !important"':'');?>>
								<?php echo $p->t('bewerbung/menuUebersicht') ?><br> &nbsp;
							</a>
						</li>
						<?php endif; ?>
						<?php if(!defined('BEWERBERTOOL_ALLGEMEIN_ANZEIGEN') || BEWERBERTOOL_ALLGEMEIN_ANZEIGEN): ?>
						<li>
							<a href="#allgemein" aria-controls="allgemein" role="tab" data-toggle="tab">
								<?php echo $p->t('bewerbung/menuAllgemein') ?><br> &nbsp;
							</a>
						</li>
						<?php endif; ?>
						<li>
							<a href="#daten" aria-controls="daten" role="tab" data-toggle="tab" <?php echo ($status_person_text == $unvollstaendig?'style="background-color: #F2DEDE !important"':'style="background-color: #DFF0D8 !important"');?>>
								<?php echo $p->t('bewerbung/menuPersDaten') ?> <br> <?php echo $status_person_text;?>
							</a>
						</li>
						<li>
							<a href="#kontakt" aria-controls="kontakt" role="tab" data-toggle="tab" <?php echo ($status_kontakt_text == $unvollstaendig?'style="background-color: #F2DEDE !important"':'style="background-color: #DFF0D8 !important"');?>>
								<?php echo $p->t('bewerbung/menuKontaktinformationen') ?> <br> <?php echo $status_kontakt_text;?>
							</a>
						</li>
						<?php
						if(!defined('BEWERBERTOOL_DOKUMENTE_ANZEIGEN') || BEWERBERTOOL_DOKUMENTE_ANZEIGEN)
						{
							echo '	<li>
									<a id="tabDokumenteLink" href="#dokumente" aria-controls="dokumente" role="tab" data-toggle="tab">
										'.$p->t('bewerbung/menuDokumente').' <br> <span id="tabDokumenteStatustext"></span>
									</a>
								</li>';
						}
						 ?>

						<?php
						if(defined('BEWERBERTOOL_UHSTAT1_ANZEIGEN') && BEWERBERTOOL_UHSTAT1_ANZEIGEN)
						{
							//~ $all_person_bewerbungen = getBewerbungen($person_id);
							//~ $display = 'style="display: none !important"';
							// uhstat Formular nur anzeigen, wenn es einen melderelevanten Studiengang gibt
							//~ if ($all_person_bewerbungen)
							//~ {
								//~ foreach ($all_person_bewerbungen as $row)
								//~ {
									//~ if ($row->melderelevant === true)
									//~ {
										//~ $display = '';
										//~ break;
									//~ }
								//~ }
							//~ }
							$uhstatFilledOut = UHSTAT1FormFilledOut($person_id);

							echo '<li id="tab_uhstat">
								<a href="#uhstat" aria-controls="uhstat" role="tab" data-toggle="tab"'.($uhstatFilledOut?' style="background-color: #DFF0D8 !important"':' style="background-color: #F2DEDE !important"').'>
									'.$p->t('bewerbung/menuUhstat').'<br><span id="uhstatVollstaendig">'.($uhstatFilledOut?$vollstaendig:$unvollstaendig).'</span>
								</a>
							</li>';
						}
						?>

						<?php
						if(!defined('BEWERBERTOOL_ZGV_ANZEIGEN') || BEWERBERTOOL_ZGV_ANZEIGEN):
						?>
						<li>
							<a href="#zgv" aria-controls="zgv" role="tab" data-toggle="tab" <?php echo ($status_zgv_bak_text == $unvollstaendig || $status_zgv_mas_text == $unvollstaendig?'style="background-color: #F2DEDE !important"':'style="background-color: #DFF0D8 !important"');?>>
								<?php echo $p->t('bewerbung/menuZugangsvoraussetzungen') ?> <br> <?php echo ($status_zgv_bak_text == $unvollstaendig?$status_zgv_bak_text:$status_zgv_mas_text);?>
							</a>
						</li>
						<?php endif; ?>

						<?php
						if(defined('BEWERBERTOOL_AUSBILDUNG_ANZEIGEN') && BEWERBERTOOL_AUSBILDUNG_ANZEIGEN):
						?>
						<li>
							<a href="#ausbildung" aria-controls="ausbildung" role="tab" data-toggle="tab" <?php echo ($status_ausbildung_text == $unvollstaendig?'style="background-color: #F2DEDE !important"':'style="background-color: #DFF0D8 !important"');?>>
								<?php echo $p->t('bewerbung/menuAusbildung') ?> <br> <?php echo $status_ausbildung_text;?>
							</a>
						</li>
						<?php endif; ?>

						<?php
						if(!defined('BEWERBERTOOL_ZAHLUNGEN_ANZEIGEN') || BEWERBERTOOL_ZAHLUNGEN_ANZEIGEN):
						?>
						<li>
							<a href="#zahlungen" aria-controls="zahlungen" role="tab" data-toggle="tab" <?php echo ($status_zahlungen_text == $unvollstaendig?'style="background-color: #F2DEDE !important"':'style="background-color: #DFF0D8 !important"');?>>
								<?php echo $p->t('bewerbung/menuZahlungen') ?> <br> <?php echo $status_zahlungen_text;?>
							</a>
						</li>
						<?php endif; ?>

						<?php
						if(defined('BEWERBERTOOL_RECHNUNGSKONTAKT_ANZEIGEN') && BEWERBERTOOL_RECHNUNGSKONTAKT_ANZEIGEN):
						?>
						<li>
							<a href="#rechnungskontakt" aria-controls="rechnungskontakt" role="tab" data-toggle="tab">
								<?php echo $p->t('bewerbung/menuRechnungsKontaktinformationen') ?> <br> &nbsp;
							</a>
						</li>
						<?php endif; ?>

						<?php

						$display = '';
						if(!defined('BEWERBERTOOL_REIHUNGSTEST_ANZEIGEN') || BEWERBERTOOL_REIHUNGSTEST_ANZEIGEN)
						{
							// An der FHTW wird der Punkt "Reihungstest" erst angezeigt, wenn der Status einer Bewerbung bestätigt wurde
							if (CAMPUS_NAME == 'FH Technikum Wien')
							{
								if ($prestudent = getBewerbungen($person_id, true))
								{
									if (check_person_statusbestaetigt($person_id, 'Interessent', $nextWinterSemester->studiensemester_kurzbz))
									{
										$display = '';
									}
									else
									{
										$display = 'style="display: none"';
										if (($key = array_search('aufnahme', $tabs)) !== false)
										{
											unset($tabs[$key]);
										}
										$tabs = array_values($tabs);
									}
								}
							}
							elseif (CAMPUS_NAME == 'FH BFI Wien')
							{
								$dokument = new dokument();

								if ($dokument->akzeptiert('RTE', $person_id))
								{
									$display = '';
								}
								else
								{
									$display = 'style="display: none"';
									if (($key = array_search('aufnahme', $tabs)) !== false)
									{
										unset($tabs[$key]);
									}
									$tabs = array_values($tabs);
								}
							}

							echo '	<li id="tab_aufnahme" '.$display.'>
									<a href="#aufnahme" aria-controls="aufnahme" role="tab" data-toggle="tab" '.($status_reihungstest_text == $unvollstaendig ? 'style="background-color: #F2DEDE !important"': ($status_reihungstest_text == $teilvollstaendig ? 'style="background-color: #FCF8E3 !important"' : 'style="background-color: #DFF0D8 !important"')).'>
										'.$p->t('bewerbung/menuReihungstest').'<br>'.$status_reihungstest_text.'
									</a>
								</li>';
						}
						?>
						<?php if(!defined('BEWERBERTOOL_ABSCHICKEN_ANZEIGEN') || BEWERBERTOOL_ABSCHICKEN_ANZEIGEN):	?>
						<li>
							<a href="#abschicken" aria-controls="abschicken" role="tab" data-toggle="tab" <?php echo ($status_abschicken_text == $unvollstaendig ? 'style="background-color: #F2DEDE !important"': ($status_abschicken_text == $teilvollstaendig ? 'style="background-color: #FCF8E3 !important"' : 'style="background-color: #DFF0D8 !important"'));?>>
								<?php echo $p->t('bewerbung/menuAbschließen') ?> <br> <?php echo $status_abschicken_text;?>
							</a>
						</li>
						<?php endif; ?>
						<?php if(defined('BEWERBERTOOL_AKTEN_ANZEIGEN') && BEWERBERTOOL_AKTEN_ANZEIGEN):	?>
							<li>
								<a href="#akten" id="tabAktenLink" aria-controls="akten" role="tab" data-toggle="tab">
									<?php echo $p->t('bewerbung/akten') ?> <br> <span id="tabAktenStatustext"></span>
								</a>
							</li>
						<?php endif; ?>
						<?php if(defined('BEWERBERTOOL_MESSAGES_ANZEIGEN') && BEWERBERTOOL_MESSAGES_ANZEIGEN):	?>
							<li>
								<a href="#messages" aria-controls="messages" role="tab" data-toggle="tab">
									<?php echo $p->t('bewerbung/menuMessages') ?> <br> &nbsp;
								</a>
							</li>
						<?php endif; ?>
						<?php if(defined('BEWERBERTOOL_SICHERHEIT_ANZEIGEN') && BEWERBERTOOL_SICHERHEIT_ANZEIGEN):	?>
							<li>
								<a href="#sicherheit" aria-controls="sicherheit" role="tab" data-toggle="tab">
									<?php echo $p->t('bewerbung/menuSicherheit') ?> <br> &nbsp;
								</a>
							</li>
						<?php endif; ?>
						<?php if(defined('BEWERBERTOOL_INVOICES_ANZEIGEN') && BEWERBERTOOL_INVOICES_ANZEIGEN):	?>
							<li>
								<a href="#invoices" aria-controls="invoices" role="tab" data-toggle="tab">
									<?php echo $p->t('bewerbung/menuInvoices') ?> <br> &nbsp;
								</a>
							</li>
						<?php endif; ?>
						<li>
							<?php
								if ($count_abgeschickte == 0)
								{
									echo '	<a 	data-toggle="modal"
												data-target="#logoutModal"
												style="vertical-align: top">
												'.$p->t('bewerbung/logout').' <br> <span class="glyphicon glyphicon-log-out"></span>
											</a>';
								}
								else
								{
									echo '  <a href="bewerbung.php?logout=true">
												'.$p->t('bewerbung/logout').' <br> <span class="glyphicon glyphicon-log-out"></span>
											</a>';
								}
								?>
						</li>
						<?php
							$spracheSelect = new sprache();
							$spracheSelect->getAll(true);
						?>
						<li>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						</li>
						<li>
							<div class="sprache-dropdown">
								<button class="dropbtn">
									<?php echo $spracheSelect->getBezeichnung(getSprache(), getSprache()) ?>
									 <br> <span class="glyphicon glyphicon-triangle-bottom"></span>
								</button>
								<div id="sprache-dropdown-content" class="sprache-dropdown-content">
									<?php foreach($spracheSelect->result as $row): ?>
										<a href="#" tabindex="-1" data-sprache="<?php echo $row->sprache ?>">
											<?php echo $row->bezeichnung_arr[getSprache()] ?>
										</a>
									<?php endforeach; ?>
								</div>
							</div>

						</li>
					</ul>
				</div>
			</div>
		</nav>
		<?php
		if ($count_abgeschickte == 0)
		{
			echo '	<div class="modal fade"
						id="logoutModal"
						tabindex="10000"
						role="dialog"
						aria-labelledby="logoutModalLabel"
						aria-hidden="true">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal"
										aria-hidden="true">&times;</button>
									<h4 class="modal-title">
										'.$p->t('bewerbung/logout').'
									</h4>
								</div>
								<div class="modal-body">
											'.$p->t('bewerbung/logoutInfotext').'
										</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-default" data-dismiss="modal">
										'.$p->t('global/abbrechen').'
									</button>
									<a href="bewerbung.php?logout=true" class="btn btn-warning" role="button">
										'.$p->t('bewerbung/logout').'
									</a>
								</div>
							</div>
						</div>
					</div>';
		}

		?>
		<div class="container">
			<div class="tab-content">
				<?php
				// Tabs nach Index sortieren und in dieser Reihenfolge laden
				foreach($tabLadefolge as $tab)
				{
					require('views/' . $tab . '.php');
				}
				?>
			</div>
		</div>
		<div style="text-align:center; color:gray;"><?php echo $p->t('bewerbung/footerText')?></div>
	</body>
</html>

<?php

// Sendet eine Email an die Assistenz, dass die Bewerbung abgeschlossen ist und eine an den Bewerber zur Bestätigung
function sendBewerbung($prestudent_id, $studiensemester_kurzbz, $orgform_kurzbz, $studienplan_id = '')
{
	global $person_id, $sprache;
	$p = new phrasen(DEFAULT_LANGUAGE);

	$person = new person();
	$person->load($person_id);

	$studienplan_bezeichnung = '';
	$studiengangsbezeichnung = '';

	if ($studienplan_id != '')
	{
		$studienplan = new studienplan();
		$studienplan->loadStudienplan($studienplan_id);
		$studienplan_bezeichnung = $studienplan->bezeichnung;

		$studienordnung = new studienordnung();
		$studienordnung->getStudienordnungFromStudienplan($studienplan_id);
		if ($sprache == 'English')
		{
			$studiengangsbezeichnung = $studienordnung->studiengangbezeichnung_englisch;
		}
		else
		{
			$studiengangsbezeichnung = $studienordnung->studiengangbezeichnung;
		}
	}

	$prestudent = new prestudent();
	if (! $prestudent->load($prestudent_id))
		die($p->t('global/fehlerBeimLadenDesDatensatzes'));

	$studiengang = new studiengang();
	if (! $studiengang->load($prestudent->studiengang_kz))
		die($p->t('global/fehlerBeimLadenDesDatensatzes'));

	if ($studiengangsbezeichnung == '')
	{
		$studiengangsbezeichnung = $studiengang->bezeichnung_arr[$sprache];
	}

	$typ = new studiengang();
	$typ->getStudiengangTyp($studiengang->typ);
	$empfaenger = getMailEmpfaenger($studiengang->studiengang_kz);

	if (CAMPUS_NAME == 'FH Technikum Wien' && $person->geschlecht == 'x')
	{
		// Wenn Geschlecht "Divers" ist wird eine Notiz als Hinweis angelegt
		$notiz = new notiz();
		$notiz->person_id = $person_id;
		$notiz->verfasser_uid = '';
		$notiz->erledigt = false;
		$notiz->insertvon = 'online_notiz';
		$notiz->insertamum = date('c');
		$notiz->start = date('Y-m-d');
		$notiz->titel = 'ACHTUNG! Geschlecht: "Divers"';
		$notiz->text = 'Dokumente prüfen. Nur gültig, wenn auf offiziellem Dokument ebenfalls "Divers" angeführt wird';
		$notiz->save(true);
		$notiz->saveZuordnung();
	}
	if (CAMPUS_NAME == 'FH Technikum Wien' && $studiengang->typ != 'b' && $studiengang->typ != 'm')
	{
		$kontakt = new kontakt();
		$kontakt->load_persKontakttyp($person->person_id, 'email', 'zustellung DESC');
		$mailadresse = isset($kontakt->result[0]->kontakt) ? $kontakt->result[0]->kontakt : '';

		$kontakt_t = new kontakt();
		$kontakt_t->load_persKontakttyp($person->person_id, 'telefon', 'zustellung DESC');
		$telefon = isset($kontakt_t->result[0]->kontakt) ? $kontakt_t->result[0]->kontakt : '';
		// Wenn Telefonnumer leer, alternativ Mobilnummer abfragen
		if ($telefon == '')
		{
			$kontakt_t->load_persKontakttyp($person->person_id, 'mobil', 'zustellung DESC');
			$telefon = isset($kontakt_t->result[0]->kontakt) ? $kontakt_t->result[0]->kontakt : '';
		}

		$adresse = new adresse();
		$adresse->load_pers($person->person_id);
		$strasse = isset($adresse->result[0]->strasse) ? $adresse->result[0]->strasse : '';
		$plz = isset($adresse->result[0]->plz) ? $adresse->result[0]->plz : '';
		$ort = isset($adresse->result[0]->ort) ? $adresse->result[0]->ort : '';
		$adr_nation = isset($adresse->result[0]->nation) ? $adresse->result[0]->nation : '';
		$nation = new nation($adr_nation);

		$notiz = new notiz();
		$notiz->getBewerbungstoolNotizen($person_id, $prestudent_id);
		$anmerkungen = '';
		foreach ($notiz->result as $note)
		{
			if ($note->insertvon == 'online_notiz')
			{
				if ($anmerkungen != '')
					$anmerkungen .= '<br>';
				$anmerkungen .= '- ' . htmlspecialchars($note->text);
			}
		}

		// Prüfen, ob der Bewerber schon Student an der FHTW war
		// Wenn ja, Auflistung der Status, ansonsten "extern"
		$herkunft = '';
		$allPrestudents = new prestudent();
		$allPrestudents->getPrestudenten($prestudent->person_id);
		$stgPrestudent = new studiengang();
		$stgPrestudent->getAll('typ, kurzbz', true);

		foreach ($allPrestudents->result as $prestudentRow)
		{
			$prestudentLastStatus = new prestudent();
			$prestudentLastStatus->getLastStatus($prestudentRow->prestudent_id);

			if ($prestudentLastStatus->status_kurzbz == 'Student'
				|| $prestudentLastStatus->status_kurzbz == 'Absolvent'
				|| $prestudentLastStatus->status_kurzbz == 'Abbrecher'
				|| $prestudentLastStatus->status_kurzbz == 'Incoming'
				|| $prestudentLastStatus->status_kurzbz == 'Unterbrecher'
				|| $prestudentLastStatus->status_kurzbz == 'Diplomand'
				|| $prestudentLastStatus->status_kurzbz == 'Outgoing')
			{
				$herkunft .= $stgPrestudent->kuerzel_arr[$prestudentRow->studiengang_kz].' ('.$prestudentLastStatus->status_kurzbz.' '.$prestudentLastStatus->studiensemester_kurzbz.')<br/>';
			}
		}
		if ($herkunft == '')
		{
			$herkunft = 'extern';
		}

		$sanchoMailHeader = base64_encode(file_get_contents(APP_ROOT . 'skin/images/sancho/sancho_header_min_bw.jpg'));
		$sanchoMailFooter = base64_encode(file_get_contents(APP_ROOT . 'skin/images/sancho/sancho_footer_min_bw.jpg'));
		$email = $p->t('bewerbung/emailBodyStart', array(VILESCI_ROOT . 'vilesci/personen/personendetails.php?id='.$person_id, $sanchoMailHeader));

		// Wenn MAIL_DEBUG aktiv ist, zeige auch den Empfänger an
		if(defined('MAIL_DEBUG') && MAIL_DEBUG != '')
			$email .= '<br><br>Empfänger: '.$empfaenger.'<br><br>';
		$email .= '<br><table style="font-size:small"><tbody>';
		$email .= '<tr><td style="vertical-align:top"><b>' . $p->t('bewerbung/herkunftDesBewerbers') . '</b></td><td>'.$herkunft.'</td></tr>';
		$email .= '<tr><td><b>' . $p->t('global/studiengang') . '</b></td><td>' . $typ->bezeichnung . ' ' . $studiengangsbezeichnung . ($orgform_kurzbz != '' ? ' (' . $orgform_kurzbz . ')' : '') . '</td></tr>';
		$email .= '<tr><td><b>' . $p->t('global/studiensemester') . '</b></td><td>' . $studiensemester_kurzbz . '</td></tr>';
		if ($studienplan_bezeichnung != '')
			$email.= '<tr><td><b>'.$p->t('studienplan/studienplan').'</b></td><td>'.$studienplan_bezeichnung.'</td></tr>';
		else
			$email.= '<tr><td><b>'.$p->t('studienplan/studienplan').'</b></td><td><span style="color: red">Es konnte kein passender Studienplan ermittelt werden</span></td></tr>';

		$geschlecht = new geschlecht($person->geschlecht);
		$email.= '<tr><td><b>'.$p->t('global/geschlecht').'</b></td><td>'.$geschlecht->bezeichnung_mehrsprachig_arr[$sprache].'</td></tr>';
		//$email.= '<tr><td><b>'.$p->t('global/titel').'</b></td><td>'.$person->titelpre.'</td></tr>';
		//$email.= '<tr><td><b>'.$p->t('global/postnomen').'</b></td><td>'.$person->titelpost.'</td></tr>';
		$email.= '<tr><td><b>'.$p->t('global/vorname').'</b></td><td>'.$person->vorname.'</td></tr>';
		$email.= '<tr><td><b>'.$p->t('global/nachname').'</b></td><td>'.$person->nachname.'</td></tr>';
		//$email.= '<tr><td><b>'.$p->t('global/geburtsdatum').'</b></td><td>'.date('d.m.Y', strtotime($person->gebdatum)).'</td></tr>';
		//$email.= '<tr><td><b>'.$p->t('global/adresse').'</b></td><td>'.$strasse.'</td></tr>';
		//$email.= '<tr><td><b>'.$p->t('global/plz').'</b></td><td>'.$plz.'</td></tr>';
		//$email.= '<tr><td><b>'.$p->t('global/ort').'</b></td><td>'.$ort.'</td></tr>';
		//$email.= '<tr><td><b>'.$p->t('incoming/nation').'</b></td><td>'.$nation->langtext.'</td></tr>';
		$email.= '<tr><td><b>'.$p->t('global/emailAdresse').'</b></td><td><a href="mailto:'.$mailadresse.'">'.$mailadresse.'</a></td></tr>';
		//$email.= '<tr><td><b>'.$p->t('global/telefon').'</b></td><td>'.$telefon.'</td></tr>';
		$email.= '<tr><td style="vertical-align:top"><b>'.$p->t('global/anmerkungen').'</b></td><td>'.$anmerkungen.'</td></tr>';
		$email.= '<tr><td><b>'.$p->t('bewerbung/prestudentID').'</b></td><td>'.$prestudent_id.'</td></tr>';
		$email.= '<tr><td style="vertical-align:top"><b>'.$p->t('tools/dokumente').'</b></td><td>';
		$akte = new akte;

		$akte->getAkten($person_id);
		foreach ($akte->result as $row)
		{
			$dokument = new dokument();
			$dokument->loadDokumenttyp($row->dokument_kurzbz);
			if ($row->insertvon == 'online')
			{
				if ($row->nachgereicht == true)
					$email .= '- ' . $dokument->bezeichnung_mehrsprachig[DEFAULT_LANGUAGE] . ' -> ' . $p->t('bewerbung/dokumentWirdNachgereicht') . '<br>';
				else
					$email .= '- <a href="' . VILESCI_ROOT . '/content/akte.php?akte_id=' . $row->akte_id . '">' . $dokument->bezeichnung_mehrsprachig[DEFAULT_LANGUAGE] . '</a><br>';
			}
		}
		$email .= '</td></tr></tbody></table>';
		$email .= '<br>';
		$email .= '<table border="0" cellspacing="0" cellpadding="0">
					<tr><td>
						<a href="' . APP_ROOT . 'addons/bewerbung/cis/status_bestaetigen.php?prestudent_id=' . $prestudent_id . '&studiensemester_kurzbz=' . $studiensemester_kurzbz . '&bestaetigen=true" target="_blank" style="font-size: 16px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; border-radius: 3px; -webkit-border-radius: 3px; -moz-border-radius: 3px; background-color: #5cb85c; border-top: 6px solid #5cb85c; border-bottom: 6px solid #5cb85c; border-right: 12px solid #5cb85c; border-left: 12px solid #5cb85c; display: inline-block;">
							' . $p->t('bewerbung/statusBestaetigen') . '
						</a>
					</td></tr>
					</table>';
		$email .= '<br>';
		$email .= $p->t('bewerbung/emailBodyEnde', array($sanchoMailFooter));
	}
	else
	{
		$sanchoMailHeader = base64_encode(file_get_contents(APP_ROOT . 'skin/images/sancho/sancho_header_min_bw.jpg'));
		$sanchoMailFooter = base64_encode(file_get_contents(APP_ROOT . 'skin/images/sancho/sancho_footer_min_bw.jpg'));
		$email = $p->t('bewerbung/emailBodyStart', array(VILESCI_ROOT . 'vilesci/personen/personendetails.php?id='.$person_id, $sanchoMailHeader));
		$email .= '<br>';
		$email .= $p->t('global/studiengang') . ': ' . $typ->bezeichnung . ' ' . $studiengangsbezeichnung . ($orgform_kurzbz != '' ? ' (' . $orgform_kurzbz . ')' : '') . ' <br>';
		$email .= $p->t('global/studiensemester') . ': ' . $studiensemester_kurzbz . '<br>';
		$email .= $p->t('global/name') . ': ' . $person->vorname . ' ' . $person->nachname . '<br>';
		$email .= $p->t('bewerbung/prestudentID') . ': ' . $prestudent_id . '<br><br>';
		$email .= $p->t('bewerbung/emailBodyEnde', array($sanchoMailFooter));
	}

	// An der FHTW werden alle Bachelor-Studiengänge und Master vom Infocenter abgearbeitet und deshalb keine Mail verschickt
	// Die FIT-Studiengänge erhalten auch kein Mail
	if (CAMPUS_NAME == 'FH Technikum Wien')
	{
		if ($studiengang->typ != 'b' && $studiengang->typ != 'm' && defined('BEWERBERTOOL_DONT_SEND_MAIL_STG') && !in_array($studiengang->studiengang_kz, unserialize(BEWERBERTOOL_DONT_SEND_MAIL_STG)))
		{
			$email = wordwrap($email, 70); // Bricht den Code um, da es sonst zu Anzeigefehlern im Mail kommen kann

			$mail = new mail($empfaenger, 'no-reply', $p->t('bewerbung/bewerbung') . ' ' . $person->vorname . ' ' . $person->nachname . ($orgform_kurzbz != '' ? ' (' . $orgform_kurzbz . ')' : ''), 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Link vollständig darzustellen.');
			$mail->setHTMLContent($email);
		}
	}
	else
	{
		$email = wordwrap($email, 70); // Bricht den Code um, da es sonst zu Anzeigefehlern im Mail kommen kann

		$mail = new mail($empfaenger, 'no-reply', $p->t('bewerbung/bewerbung') . ' ' . $person->vorname . ' ' . $person->nachname . ($orgform_kurzbz != '' ? ' (' . $orgform_kurzbz . ')' : ''), 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Link vollständig darzustellen.');
		$mail->setHTMLContent($email);
	}

	// send mail to Interessent
	if (defined('BEWERBERTOOL_ERFOLGREICHBEWORBENMAIL') && BEWERBERTOOL_ERFOLGREICHBEWORBENMAIL == true)
	{
		$p = new phrasen($sprache);
		$kontakt = new kontakt();
		$kontakt->load_persKontakttyp($person->person_id, 'email', 'zustellung DESC');
		$mailadresse = isset($kontakt->result[0]->kontakt) ? $kontakt->result[0]->kontakt : '';

		if($person->geschlecht == 'm')
			$anrede = $p->t('bewerbung/anredeMaennlich');
		elseif($person->geschlecht == 'w')
			$anrede = $p->t('bewerbung/anredeWeiblich');
		else
			$anrede = $p->t('bewerbung/anredeNeutral');

		$mail_bewerber = new mail($mailadresse, 'no-reply', $p->t('bewerbung/erfolgreichBeworbenMailBetreff'), 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Inhalt vollständig darzustellen.');
		// Unterschiedliche Ansprechpersonen für Bachelor und Master
		$sanchoMailHeader = base64_encode(file_get_contents(APP_ROOT . 'skin/images/sancho/sancho_header_DEFAULT.jpg'));
		$sanchoMailFooter = base64_encode(file_get_contents(APP_ROOT . 'skin/images/sancho/sancho_footer.jpg'));
		if ($studiengang->typ == 'b')
		{
			$email_bewerber_content = $p->t('bewerbung/erfolgreichBeworbenMailBachelor', array($person->vorname, $person->nachname, $anrede, $studiengangsbezeichnung, $sanchoMailHeader, $sanchoMailFooter));
		}
		else
		{
			$email_bewerber_content = $p->t('bewerbung/erfolgreichBeworbenMail', array($person->vorname, $person->nachname, $anrede, $studiengangsbezeichnung, $empfaenger, $sanchoMailHeader, $sanchoMailFooter));
		}

		$mail_bewerber->setHTMLContent($email_bewerber_content);
		// BFI braucht keine eingebetteten Images
		if (CAMPUS_NAME != 'FH BFI Wien')
		{
			$mail_bewerber->addEmbeddedImage(APP_ROOT.'skin/images/sancho/sancho_header_DEFAULT.jpg', 'image/jpg', 'header_image', 'sancho_header');
			$mail_bewerber->addEmbeddedImage(APP_ROOT.'skin/images/sancho/sancho_footer.jpg', 'image/jpg', 'footer_image', 'sancho_footer');
		}
		if (! $mail_bewerber->send())
			return false;
	}

	// An der FHTW werden alle Bachelor-Studiengänge und Master vom Infocenter abgearbeitet und deshalb keine Mail verschickt
	if (CAMPUS_NAME == 'FH Technikum Wien')
	{
		if ($studiengang->typ != 'b' && $studiengang->typ != 'm' && defined('BEWERBERTOOL_DONT_SEND_MAIL_STG') && !in_array($studiengang->studiengang_kz, unserialize(BEWERBERTOOL_DONT_SEND_MAIL_STG)))
		{
			if (! $mail->send())
				return false;
			else
				return true;
		}
		else
			return true;
	}
	else
	{
		if (! $mail->send())
			return false;
		else
			return true;
	}
}
// sendet eine Email an die Assistenz, wenn nachträglich eine Bewerbung hinzugefügt wird
function sendAddStudiengang($prestudent_id, $studiensemester_kurzbz, $orgform_kurzbz)
{
	global $person_id, $sprache;
	$p = new phrasen(DEFAULT_LANGUAGE);

	// Array fuer Mailempfaenger. Vorruebergehende Loesung. Kindlm am 28.10.2015
	$empf_array = array();
	if (defined('BEWERBERTOOL_BEWERBUNG_EMPFAENGER'))
		$empf_array = unserialize(BEWERBERTOOL_BEWERBUNG_EMPFAENGER);

	$person = new person();
	$person->load($person_id);

	$prestudent = new prestudent();
	if (! $prestudent->load($prestudent_id))
		die($p->t('global/fehlerBeimLadenDesDatensatzes'));

	$studiengang = new studiengang();
	if (! $studiengang->load($prestudent->studiengang_kz))
		die($p->t('global/fehlerBeimLadenDesDatensatzes'));

	$typ = new studiengang();
	$typ->getStudiengangTyp($studiengang->typ);

	$kontakt = new kontakt();
	$kontakt->load_persKontakttyp($person->person_id, 'email', 'zustellung DESC');
	$mailadresse = isset($kontakt->result[0]->kontakt) ? $kontakt->result[0]->kontakt : '';

	$kontakt_t = new kontakt();
	$kontakt_t->load_persKontakttyp($person->person_id, 'telefon', 'zustellung DESC');
	$telefon = isset($kontakt_t->result[0]->kontakt) ? $kontakt_t->result[0]->kontakt : '';
	// Wenn Telefonnumer leer, alternativ Mobilnummer abfragen
	if ($telefon == '')
	{
		$kontakt_t->load_persKontakttyp($person->person_id, 'mobil', 'zustellung DESC');
		$telefon = isset($kontakt_t->result[0]->kontakt) ? $kontakt_t->result[0]->kontakt : '';
	}

	$adresse = new adresse();
	$adresse->load_pers($person->person_id);
	$strasse = isset($adresse->result[0]->strasse) ? $adresse->result[0]->strasse : '';
	$plz = isset($adresse->result[0]->plz) ? $adresse->result[0]->plz : '';
	$ort = isset($adresse->result[0]->ort) ? $adresse->result[0]->ort : '';
	$adr_nation = isset($adresse->result[0]->nation) ? $adresse->result[0]->nation : '';
	$nation = new nation($adr_nation);

	$notiz = new notiz();
	$notiz->getBewerbungstoolNotizen($person_id, $prestudent_id);
	$anmerkungen = '';
	foreach ($notiz->result as $note)
	{
		if ($note->insertvon == 'online_notiz')
		{
			if ($anmerkungen != '')
				$anmerkungen .= '<br>';
			$anmerkungen .= '- ' . htmlspecialchars($note->text);
		}
	}

	$email = 'Es hat sich ein Bewerber/eine Bewerberin am System registriert<br>';
	$email .= '<br><table style="font-size:small"><tbody>';
	$email .= '<tr><td><b>' . $p->t('global/studiengang') . '</b></td><td>' . $typ->bezeichnung . ' ' . $studiengang->bezeichnung . ($orgform_kurzbz != '' ? ' (' . $orgform_kurzbz . ')' : '') . '</td></tr>';
	$email .= '<tr><td><b>' . $p->t('global/studiensemester') . '</b></td><td>' . $studiensemester_kurzbz . '</td></tr>';
	$geschlecht = new geschlecht($person->geschlecht);
	$email .= '<tr><td><b>' . $p->t('global/geschlecht') . '</b></td><td>'.$geschlecht->bezeichnung_mehrsprachig_arr[$sprache].'</td></tr>';
	// $email.= '<tr><td><b>'.$p->t('global/titel').'</b></td><td>'.$person->titelpre.'</td></tr>';
	// $email.= '<tr><td><b>'.$p->t('global/postnomen').'</b></td><td>'.$person->titelpost.'</td></tr>';
	$email .= '<tr><td><b>' . $p->t('global/vorname') . '</b></td><td>' . $person->vorname . '</td></tr>';
	$email .= '<tr><td><b>' . $p->t('global/nachname') . '</b></td><td>' . $person->nachname . '</td></tr>';
	$email .= '<tr><td><b>' . $p->t('global/geburtsdatum') . '</b></td><td>' . date('d.m.Y', strtotime($person->gebdatum)) . '</td></tr>';
	$email .= '<tr><td><b>' . $p->t('global/emailAdresse') . '</b></td><td>' . $mailadresse . '</td></tr>';
	$email .= '<tr><td><b>' . $p->t('bewerbung/prestudentID') . '</b></td><td>' . $prestudent_id . '</td></tr>';
	$email .= '</td></tr></tbody></table>';
	$email .= '<br>';
	$email .= $p->t('bewerbung/emailBodyEnde', array(null));

	$email = wordwrap($email, 70); // Bricht den Code um, da es sonst zu Anzeigefehlern im Mail kommen kann

	$empfaenger = getMailEmpfaenger($prestudent->studiengang_kz);
	$mail = new mail($empfaenger, 'no-reply', ($person->geschlecht == 'm' ? 'Neuer Bewerber ' : $person->geschlecht == 'w' ? 'Neue Bewerberin ' : 'Neue/r Bewerber/in ') . $person->vorname . ' ' . $person->nachname . ' registriert', 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Inhalt vollständig darzustellen.');
	$mail->setHTMLContent($email);
	if (! $mail->send())
		return false;
	else
		return true;
}
