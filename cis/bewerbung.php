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

require_once ('../../../config/cis.config.inc.php');
require_once ('../../../config/global.config.inc.php');
require_once ('../bewerbung.config.inc.php');

session_cache_limiter('none'); // muss gesetzt werden sonst funktioniert der Download mit IE8 nicht
session_start();

if (! isset($_SESSION['bewerbung/user']) || $_SESSION['bewerbung/user'] == '')
{
	$_SESSION['request_uri'] = $_SERVER['REQUEST_URI'];
	
	header('Location: registration.php?method=allgemein');
	exit();
}

require_once ('../../../include/konto.class.php');
require_once ('../../../include/benutzer.class.php');
require_once ('../../../include/phrasen.class.php');
require_once ('../../../include/benutzerberechtigung.class.php');
require_once ('../../../include/nation.class.php');
require_once ('../../../include/gemeinde.class.php');
require_once ('../../../include/person.class.php');
require_once ('../../../include/datum.class.php');
require_once ('../../../include/kontakt.class.php');
require_once ('../../../include/adresse.class.php');
require_once ('../../../include/prestudent.class.php');
require_once ('../../../include/studiengang.class.php');
require_once ('../../../include/zgv.class.php');
require_once ('../../../include/dms.class.php');
require_once ('../../../include/dokument.class.php');
require_once ('../../../include/akte.class.php');
require_once ('../../../include/mail.class.php');
require_once ('../../../include/studiensemester.class.php');
require_once ('../../../include/studienplan.class.php');
require_once ('../../../include/studienordnung.class.php');
require_once ('../../../include/basis_db.class.php');
require_once ('../../../include/reihungstest.class.php');
require_once ('../../../include/preinteressent.class.php');
require_once ('../../../include/notiz.class.php');
require_once ('../../../include/organisationseinheit.class.php');
require_once ('../../../include/organisationsform.class.php');
require_once ('../include/functions.inc.php');
require_once ('../../../include/functions.inc.php');
require_once ('../../../include/aufmerksamdurch.class.php');
require_once ('../../../include/bisberufstaetigkeit.class.php');
require_once ('../../../include/bewerbungstermin.class.php');
require_once ('../../../include/personlog.class.php');

if (isset($_GET['logout']))
{
	session_destroy();
	header('Location: registration.php');
}

$person_id = (int) $_SESSION['bewerbung/personId'];
$akte_id = isset($_GET['akte_id']) ? $_GET['akte_id'] : '';
$method = isset($_GET['method']) ? $_GET['method'] : '';
$datum = new datum();
$person = new person();

if (! $person->load($person_id))
{
	die($p->t('global/fehlerBeimLadenDesDatensatzes'));
}
// $sprache = DEFAULT_LANGUAGE;
$sprache = getSprache();
$p = new phrasen($sprache);
$log = new personlog();

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

if (CAMPUS_NAME == 'FH Technikum Wien')
{
	// Wenn der Status bestaetigt wurde, duerfen die Stammdaten nicht mehr geaendert werden
	if (check_person_statusbestaetigt($person_id, 'Interessent'))
		$eingabegesperrt = true;
}
else
{
	// Wenn bereits eine Bewerbung abgeschickt wurde, duerfen die Stammdaten nicht mehr geaendert werden
	if (check_person_bewerbungabgeschickt($person_id))
		$eingabegesperrt = true;
}

$message = '&nbsp;';

// $vollstaendig = '<span class="badge alert-success">'.$p->t('bewerbung/vollstaendig').' <span class="glyphicon glyphicon-ok"></span></span>';
// $unvollstaendig = '<span class="badge alert-danger">'.$p->t('bewerbung/unvollstaendig').' <span class="glyphicon glyphicon-remove"></span></span>';
$vollstaendig = '<span style="color: #3c763d;">' . $p->t('bewerbung/vollstaendig') . '</span>';
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

/*
 * Der Web-User hat keine Berechtigung, aus der tbl_prestudent und tbl_prestudentstatus zu löschen.
 * Der elegantere Weg ist vermutlich, einen neuen Status "gelöscht" oder "abgesagt" (ev. mit Begründung) einzufügen.
 * Dann lassen sich auch schönere Statistiken fahren und wir haben eine Historie.
 * Ich lasse die Methode vorerst stehen, um eventuell später daran anzuknüpfen.
 * if($method=='deleteBewerbung' && isset($_GET['prestudent_id']))
 * {
 * $prestudent_id = filter_input(INPUT_GET, 'prestudent_id', FILTER_VALIDATE_INT);
 * $prestudent_status = new prestudent();
 * $prestudent_status->getLastStatus($prestudent_id);
 * $statusbestaetigt = $prestudent_status->bestaetigtam != '' || $prestudent_status->bestaetigtvon != ''?true:false;
 * if ($prestudent_status->status_kurzbz == 'Interessent' && $statusbestaetigt == false)
 * {
 * $prestudent = new prestudent();
 * if($prestudent->delete_rolle($prestudent_id, 'Interessent', $prestudent_status->studiensemester_kurzbz, $prestudent_status->ausbildungssemester, false))
 * {
 * // Wenn es keinen weiteren PrestudentStatus-Eintrag gibt, auch den Prestudenten löschen
 * $prestudent_status = new prestudent();
 * if (!$prestudent_status->getLastStatus($prestudent_id))
 * {
 * if($prestudent->deletePrestudent($prestudent_id, false))
 * {
 * $message = $p->t('global/erfolgreichgelöscht');
 * }
 * else
 * {
 * $message = $p->t('global/fehlerBeimLoeschenDesEintrags');
 * }
 * }
 * }
 * else
 * {
 * $message = $p->t('global/fehlerBeimLoeschenDesEintrags');
 * }
 * }
 * }
 */

if (isset($_GET['rt_id']))
{
	
	$rt_id = filter_input(INPUT_GET, 'rt_id', FILTER_VALIDATE_INT);
	$pre_id = filter_input(INPUT_GET, 'pre', FILTER_VALIDATE_INT);
	
	if (isset($_GET['delete']))
	{
		$prestudent = new prestudent();
		if (! $prestudent->getPrestudenten($person_id))
		{
			die($p->t('global/fehlerBeimLadenDesDatensatzes'));
		}
		
		foreach ($prestudent->result as $row)
		{
			if ($row->prestudent_id == $pre_id)
			{
				$prest = new prestudent();
				$prest->load($pre_id);
				$prest->reihungstest_id = '';
				$prest->anmeldungreihungstest = '';
				$prest->updateamum = date("Y-m-d H:m:s");
				$prest->updatevon = 'online';
				$prest->new = false;
				
				if (! $prest->save())
				{
					echo $p->t('global/fehlerBeimSpeichernDerDaten');
				}
			}
		}
	}
	else
	{
		$reihungstest = new reihungstest();
		$reihungstest->load($rt_id);
		
		if ($reihungstest->max_teilnehmer && $reihungstest->getTeilnehmerAnzahl($rt_id) >= $reihungstest->max_teilnehmer)
		{
			die($p->t('bewerbung/maxAnzahlTeilnehmer'));
		}
		
		$timestamp = time();
		
		$prestudent = new prestudent();
		if (! $prestudent->getPrestudenten($person_id))
		{
			die($p->t('global/fehlerBeimLadenDesDatensatzes'));
		}
		
		foreach ($prestudent->result as $row)
		{
			if ($row->prestudent_id == $pre_id)
			{
				$prest = new prestudent();
				$prest->load($pre_id);
				$prest->reihungstest_id = $rt_id;
				$prest->anmeldungreihungstest = date('Y-m-d', $timestamp);
				$prest->updateamum = date("Y-m-d H:m:s");
				$prest->updatevon = 'online';
				$prest->new = false;
				
				if (! $prest->save())
				{
					echo $p->t('global/fehlerBeimSpeichernDerDaten');
				}
			}
		}
	}
}
$save_error_abschicken = '';
if (isset($_POST['btn_bewerbung_abschicken']))
{
	// Mail an zuständige Assistenz schicken
	$pr_id = isset($_POST['prestudent_id']) ? $_POST['prestudent_id'] : '';
	$sendmail = false; // Damit das Mail beim Seitenreload nicht nochmal geschickt wird
	$bewerbungszeitraum_gueltig = true;
	
	if ($pr_id != '')
	{
		// Status Bewerber anlegen
		$prestudent_status = new prestudent();
		$prestudent_status->load($pr_id);
		
		$alterstatus = new prestudent();
		$alterstatus->getLastStatus($pr_id);
		
		// check ob es status schon gibt
		if ($prestudent_status->load_rolle($pr_id, 'Interessent', $alterstatus->studiensemester_kurzbz, '1'))
		{
			// Check, ob Bewerbungsfrist schon begonnen hat, bzw abgelaufen ist
			$bewerbungsfristen = new bewerbungstermin();
			$bewerbungsfristen->getBewerbungstermine($prestudent_status->studiengang_kz, $prestudent_status->studiensemester_kurzbz, 'insertamum DESC', $prestudent_status->studienplan_id);
			
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
		if ($sendmail == true && $bewerbungszeitraum_gueltig == true)
		{
			if (sendBewerbung($pr_id, $prestudent_status->studiensemester_kurzbz, $prestudent_status->orgform_kurzbz, $prestudent_status->studienplan_id))
			{
				$message = $p->t('bewerbung/erfolgreichBeworben', array(
					$studiengang->bezeichnung_arr[$sprache]
				));
				// echo '<script type="text/javascript">alert("'.$p->t('bewerbung/erfolgreichBeworben',array($studiengang->bezeichnung_arr[$sprache])).'");</script>';
				// echo '<script type="text/javascript">window.location="'.$_SERVER['PHP_SELF'].'?active=abschicken";</script>';
				$save_error_abschicken = false;
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
				// Logeintrag schreiben
				$log->log($person_id, 'Action', array(
					'name' => $_POST['dok_kurzbz'] . ' set to nachgereicht',
					'success' => true,
					'message' => 'Document ' . $_POST['dok_kurzbz'] . ' has been set to nachgereicht'
				), 'bewerbung', 'bewerbung', null, 'online');
			}
		}
	}
}

// gibt an welcher Tab gerade aktiv ist
$active = filter_input(INPUT_GET, 'active');

if (! $active)
{
	$active = 'allgemein';
}
$save_error_daten = '';
// Persönliche Daten speichern
if (isset($_POST['btn_person']) && ! $eingabegesperrt)
{
	$person->titelpre = $_POST['titel_pre'];
	$person->vorname = $_POST['vorname'];
	$person->nachname = $_POST['nachname'];
	$person->titelpost = $_POST['titel_post'];
	$person->gebdatum = $datum->formatDatum($_POST['geburtsdatum'], 'Y-m-d');
	$person->staatsbuergerschaft = $_POST['staatsbuergerschaft'];
	$person->geschlecht = $_POST['geschlecht'];
	$person->anrede = ($_POST['geschlecht'] == 'm' ? 'Herr' : 'Frau');
	$person->svnr = isset($_POST['svnr']) ? $_POST['svnr'] : '';
	$person->gebort = $_POST['gebort'];
	$person->geburtsnation = $_POST['geburtsnation'];
	
	$person->new = false;
	
	if (! $person->save())
	{
		$message = $person->errormsg;
		$save_error_daten = true;
		// Logeintrag schreiben
		$log->log($person_id, 'Action', array(
			'name' => 'Personal data saved',
			'success' => false,
			'message' => 'Error saving personal data. Error message says: ' . $person->errormsg
		), 'bewerbung', 'bewerbung', null, 'online');
	}
	else
	{
		$save_error_daten = false;
		// Logeintrag schreiben
		$log->log($person_id, 'Action', array(
			'name' => 'Personal data saved',
			'success' => true,
			'message' => 'Personal data has been saved or changed'
		), 'bewerbung', 'bewerbung', null, 'online');
	}
	
	if (! $save_error_daten && $person->checkSvnr($person->svnr, $person_id))
	{
		$message = $p->t('bewerbung/svnrBereitsVorhanden');
		$save_error_daten = true;
		// Logeintrag schreiben
		$log->log($person_id, 'Action', array(
			'name' => 'Error saving Sozialversicherungsnummer',
			'success' => false,
			'message' => 'Sozialversicherungsnummer ' . $person->svnr . ' already present in database'
		), 'bewerbung', 'bewerbung', null, 'online');
	}
	
	$berufstaetig = filter_input(INPUT_POST, 'berufstaetig');
	
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
		$notiz->text = 'Berufstätig: ' . $berufstaetig . '; Dienstgeber: ' . $berufstaetig_dienstgeber . '; Art der Tätigkeit: ' . $berufstaetig_art;
		$notiz->save(true);
		$notiz->saveZuordnung();
	}
	
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
		 * Eine bestehende Mailadress darf nicht bearbeitet oder entfernt werden
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
					$gemeinde_obj->getGemeinde($_POST['ort'], '', $_POST['plz']);
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
				$adresse->plz = $_POST['plz'];
				$adresse->ort = $_POST['ort'];
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
// Rechnjungsdaten speichern
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
					$gemeinde_obj->getGemeinde($_POST['re_ort'], '', $_POST['re_plz']);
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
				$adresse->plz = $_POST['re_plz'];
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
			if (CAMPUS_NAME != 'FH Technikum Wien')
			{
				$prestudent_eintrag->zgvdatum = ($prestudent_eintrag->zgvdatum == '' ? $datum_bachelor : $prestudent_eintrag->zgvdatum);
			}
			$prestudent_eintrag->zgvnation = ($prestudent_eintrag->zgvnation == '' ? filter_input(INPUT_POST, 'bachelor_zgv_nation') : $prestudent_eintrag->zgvnation);
			$prestudent_eintrag->updateamum = date('Y-m-d H:i:s');
			$prestudent_eintrag->updatevon = 'online';
			
			$prestudent_eintrag->zgvmas_code = ($prestudent_eintrag->zgvmas_code == '' ? filter_input(INPUT_POST, 'master_zgv_art', FILTER_VALIDATE_INT) : $prestudent_eintrag->zgvmas_code);
			$prestudent_eintrag->zgvmaort = ($prestudent_eintrag->zgvmaort == '' ? filter_input(INPUT_POST, 'master_zgv_ort') : $prestudent_eintrag->zgvmaort);
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
	
	if ($anmerkung != '')
	{
		$notiz = new notiz();
		$notiz->person_id = $person_id;
		$notiz->verfasser_uid = '';
		$notiz->erledigt = false;
		$notiz->insertvon = 'online_notiz'; // Nicht aendern, da in notiz.class.php nach insertvon abgefragt wird
		$notiz->insertamum = date('c');
		$notiz->start = date('Y-m-d');
		$notiz->titel = 'Anmerkung zur Bewerbung';
		$notiz->text = $anmerkung;
		$notiz->save(true);
		$notiz->saveZuordnung();
		
		// Logeintrag schreiben
		$log->log($person_id, 'Action', array(
			'name' => 'New notiz saved',
			'success' => true,
			'message' => 'New notiz has been saved'
		), 'bewerbung', 'bewerbung', null, 'online');
	}
}

$addStudiengang = filter_input(INPUT_POST, 'addStudiengang', FILTER_VALIDATE_BOOLEAN);

if ($addStudiengang)
{
	$return = BewerbungPersonAddStudiengang($_POST['stgkz'], $_POST['anm'], $person, $_POST['studiensemester'], (isset($_POST['orgform']) ? $_POST['orgform'] : ''), (isset($_POST['sprache']) ? $_POST['sprache'] : ''));
	if ($return === true)
		echo json_encode(array(
			'status' => 'ok'
		));
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

// Abfrage ob ein Punkt schon vollständig ist
if ($person->vorname && $person->nachname && $person->gebdatum && $person->staatsbuergerschaft && $person->geschlecht)
{
	$status_person = true;
	$status_person_text = $vollstaendig;
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

if (isset($adresse->result[0]) && count($kontakt->result) && count($adresse->result[0]->strasse) && count($adresse->result[0]->plz) && count($adresse->result[0]->ort) && count($adresse->result[0]->nation) && count($kontakttel->result))
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

$prestudent = new prestudent();
if (! $prestudent->getPrestudenten($person->person_id))
{
	die($p->t('global/fehlerBeimLadenDesDatensatzes'));
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

$dokumente_abzugeben = getAllDokumenteBewerbungstoolForPerson($person_id);
// $dokumente_abzugeben = new dokument();
// $dokumente_abzugeben->getAllDokumenteForPerson($person_id, true);

// $akte_person = new akte();
// $akte_person->getAkten($person_id);

$missing_document = false;
$status_dokumente = false;
$akzeptierte_dokumente = array();

/*
 * foreach($akte_person->result as $akte)
 * {
 * $akzeptierte_dokumente[] = $akte->dokument_kurzbz;
 * }
 */

foreach ($dokumente_abzugeben as $dok)
{
	if ($dok->anzahl_akten_formal_geprueft > 0 || $dok->anzahl_akten_formal_geprueft > 0 || $dok->anzahl_dokumente_akzeptiert > 0 || $dok->anzahl_akten_nachgereicht > 0)
		$akzeptierte_dokumente[] = $dok->dokument_kurzbz;
}

foreach ($dokumente_abzugeben as $dok)
{
	if ($dok->pflicht && ! in_array($dok->dokument_kurzbz, $akzeptierte_dokumente, true) && $dok->anzahl_akten_vorhanden == 0)
	{
		$missing_document = true;
	}
	/*
	 * if(CAMPUS_NAME=='FH Technikum Wien' && !in_array($dok->dokument_kurzbz, $akzeptierte_dokumente, true))
	 * {
	 * $missing = true;
	 * }
	 */
}

if ($missing_document && (! defined('BEWERBERTOOL_DOKUMENTE_ANZEIGEN') || BEWERBERTOOL_DOKUMENTE_ANZEIGEN == true))
{
	/*
	 * if(CAMPUS_NAME == 'FH Technikum Wien' && !check_person_statusbestaetigt($person_id,'Interessent'))
	 * {
	 * $status_dokumente = true;
	 * $status_dokumente_text = $vollstaendig;
	 * }
	 */
	/*
	 * if(CAMPUS_NAME == 'FH Technikum Wien' && count($akzeptierte_dokumente) > 0)
	 * {
	 * $status_dokumente = true;
	 * $status_dokumente_text = $teilvollstaendig;
	 * }
	 * else
	 */
	{
		$status_dokumente = false;
		$status_dokumente_text = $unvollstaendig;
	}
}
else
{
	$status_dokumente = true;
	$status_dokumente_text = $vollstaendig;
}

$konto = new konto();

$status_zahlungen = true;
$status_zahlungen_text = $vollstaendig;

if (! defined('BEWERBERTOOL_ZAHLUNGEN_ANZEIGEN') || BEWERBERTOOL_ZAHLUNGEN_ANZEIGEN == true)
{
	if (! $konto->checkKontostand($person_id))
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
if (! defined('BEWERBERTOOL_REIHUNGSTEST_ANZEIGEN') || BEWERBERTOOL_REIHUNGSTEST_ANZEIGEN == true)
{
	foreach ($prestudent->result as $row)
	{
		if ($row->reihungstest_id != '')
		{
			$status_reihungstest = true;
			$status_reihungstest_text = $vollstaendig;
		}
		else
		{
			// Wenn keine Reihungstesttermine vorhanden sind ist die Bewerbung auch vollstaendig
			if (! $prestudent->getPrestudenten($person_id))
				die($p->t('global/fehlerBeimLadenDesDatensatzes'));
			
			$anzahl_reihungstests = 0;
			foreach ($prestudent->result as $row)
			{
				$reihungstest = new reihungstest();
				if (! $reihungstest->getStgZukuenftige($row->studiengang_kz))
					die($p->t('global/fehleraufgetreten') . ': ' . $reihungstest->errormsg);
				
				$anzahl_reihungstests += count($reihungstest->result);
			}
			if ($anzahl_reihungstests == 0)
			{
				$status_reihungstest = true;
				$status_reihungstest_text = $vollstaendig;
			}
		}
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
		<title><?php echo $p->t('bewerbung/menuBewerbungFuerStudiengang') ?></title>
		<link rel="stylesheet" type="text/css" href="../../../vendor/components/bootstrap/css/bootstrap.min.css">
		<script type="text/javascript" src="../../../vendor/components/jquery/jquery.min.js"></script>
		<script type="text/javascript" src="../../../vendor/components/bootstrap/js/bootstrap.min.js"></script>
		<script src="../include/js/bewerbung.js"></script>
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
			});
		</script>
		<style type="text/css">
		dokument a:hover
		{
			text-decoration:none;
		}
		.glyphicon
		{
			font-size: 16px;
		}
		.navbar-default .navbar-nav>.active>a,
		.navbar-default .navbar-nav>.active>a:focus,
		.navbar-default .navbar-nav>.active>a:hover
		{
			font-weight: bold
		}
		.popover
		{
			max-width: 400px;
		}
		.list-unstyled li
		{
			margin-bottom: 10px;
		}
		.list-unstyled
		{
			margin-top: 10px;
		}
		</style>
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
						<li>
							<a href="#allgemein" aria-controls="allgemein" role="tab" data-toggle="tab">
								<?php echo $p->t('bewerbung/menuAllgemein') ?> <br> &nbsp;
							</a>
						</li>
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
							// An der FHTW wird der Punkt Dokumente erst angezeigt, wenn der Status bestätigt ist (außer im Mail-Debug-Mode)
							/*if(CAMPUS_NAME=='FH Technikum Wien' && MAIL_DEBUG == '')
							{
								if(check_person_statusbestaetigt($person_id,'Interessent'))
								{
									echo '	<li>
												<a href="#dokumente" aria-controls="dokumente" role="tab" data-toggle="tab" '.($status_dokumente_text == $unvollstaendig?'style="background-color: #F2DEDE !important"':($status_dokumente_text == $teilvollstaendig?'style="background-color: #FCF8E3 !important"':'style="background-color: #DFF0D8 !important"')).'>
													'.$p->t('bewerbung/menuDokumente').' <br> '.$status_dokumente_text.'
												</a>
											</li>';
								}
							}
							else*/
							{
								echo '	<li>
											<a href="#dokumente" aria-controls="dokumente" role="tab" data-toggle="tab" '.($status_dokumente_text == $unvollstaendig?'style="background-color: #F2DEDE !important"':'style="background-color: #DFF0D8 !important"').'>
												'.$p->t('bewerbung/menuDokumente').' <br> '.$status_dokumente_text.'
											</a>
										</li>';
							}
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
						if(!defined('BEWERBERTOOL_REIHUNGSTEST_ANZEIGEN') || BEWERBERTOOL_REIHUNGSTEST_ANZEIGEN):
						?>
						<li>
							<a href="#aufnahme" aria-controls="aufnahme" role="tab" data-toggle="tab" <?php echo ($status_reihungstest_text == $unvollstaendig?'style="background-color: #F2DEDE !important"':'style="background-color: #DFF0D8 !important"');?>>
								<?php echo $p->t('bewerbung/menuReihungstest') ?> <br> <?php echo $status_reihungstest_text;?>
							</a>
						</li>
						<?php endif; ?>
						<li>
							<a href="#abschicken" aria-controls="abschicken" role="tab" data-toggle="tab">
								<?php echo $p->t('bewerbung/menuAbschließen') ?> <br> &nbsp;
							</a>
						</li>
						<li>
							<a href="bewerbung.php?logout=true">
								<?php echo $p->t('bewerbung/logout') ?> <br> <span class="glyphicon glyphicon-log-out"></span>
							</a>
						</li>
					</ul>
				</div>
			</div>
		</nav>
		<div class="container">
			<div class="tab-content">
				<?php
				$tabs = array(
					'allgemein',
					'daten',
					'kontakt'
				);
				if(!defined('BEWERBERTOOL_DOKUMENTE_ANZEIGEN') || BEWERBERTOOL_DOKUMENTE_ANZEIGEN)
				{
					if(CAMPUS_NAME=='FH Technikum Wien' && MAIL_DEBUG == '')
					{
						if(check_person_statusbestaetigt($person_id,'Interessent'))
							$tabs[]='dokumente';
					}
					else
						$tabs[]='dokumente';
				}
				if(defined('BEWERBERTOOL_AUSBILDUNG_ANZEIGEN') && BEWERBERTOOL_AUSBILDUNG_ANZEIGEN)
					$tabs[]='ausbildung';
				if(!defined('BEWERBERTOOL_ZGV_ANZEIGEN') || BEWERBERTOOL_ZGV_ANZEIGEN)
					$tabs[]='zgv';
				if(!defined('BEWERBERTOOL_ZAHLUNGEN_ANZEIGEN') || BEWERBERTOOL_ZAHLUNGEN_ANZEIGEN)
					$tabs[]='zahlungen';
				if(defined('BEWERBERTOOL_RECHNUNGSKONTAKT_ANZEIGEN') && BEWERBERTOOL_RECHNUNGSKONTAKT_ANZEIGEN)
					$tabs[]='rechnungskontakt';
				if(!defined('BEWERBERTOOL_REIHUNGSTEST_ANZEIGEN') || BEWERBERTOOL_REIHUNGSTEST_ANZEIGEN)
					$tabs[]='aufnahme';

				$tabs[]='abschicken';

				foreach($tabs as $tab)
				{
					require('views/' . $tab . '.php');
				}
				?>
			</div>
		</div>
	</body>
</html>

<?php
	
	// sendet eine Email an die Assistenz dass die Bewerbung abgeschlossen ist
function sendBewerbung($prestudent_id, $studiensemester_kurzbz, $orgform_kurzbz, $studienplan_id = '')
{
	global $person_id;
	$p = new phrasen(DEFAULT_LANGUAGE);
	
	// Array fuer Mailempfaenger. Vorruebergehende Loesung. Kindlm am 28.10.2015
	$empf_array = array();
	if (defined('BEWERBERTOOL_BEWERBUNG_EMPFAENGER'))
		$empf_array = unserialize(BEWERBERTOOL_BEWERBUNG_EMPFAENGER);
	
	$person = new person();
	$person->load($person_id);
	
	$studienplan_bezeichnung = '';
	if ($studienplan_id != '')
	{
		$studienplan = new studienplan();
		$studienplan->loadStudienplan($studienplan_id);
		$studienplan_bezeichnung = $studienplan->bezeichnung;
	}
	
	$prestudent = new prestudent();
	if (! $prestudent->load($prestudent_id))
		die($p->t('global/fehlerBeimLadenDesDatensatzes'));
	
	$studiengang = new studiengang();
	if (! $studiengang->load($prestudent->studiengang_kz))
		die($p->t('global/fehlerBeimLadenDesDatensatzes'));
	
	$typ = new studiengang();
	$typ->getStudiengangTyp($studiengang->typ);
	
	if (CAMPUS_NAME == 'FH Technikum Wien')
	{
		$kontakt = new kontakt();
		$kontakt->load_persKontakttyp($person->person_id, 'email');
		$mailadresse = isset($kontakt->result[0]->kontakt) ? $kontakt->result[0]->kontakt : '';
		
		$kontakt_t = new kontakt();
		$kontakt_t->load_persKontakttyp($person->person_id, 'telefon');
		$telefon = isset($kontakt_t->result[0]->kontakt) ? $kontakt_t->result[0]->kontakt : '';
		// Wenn Telefonnumer leer, alternativ Mobilnummer abfragen
		if ($telefon == '')
		{
			$kontakt_t->load_persKontakttyp($person->person_id, 'mobil');
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
		$notiz->getBewerbungstoolNotizen($person_id);
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
		
		$email = $p->t('bewerbung/emailBodyStart');
		$email .= '<br><table style="font-size:small"><tbody>';
		$email .= '<tr><td><b>' . $p->t('global/studiengang') . '</b></td><td>' . $typ->bezeichnung . ' ' . $studiengang->bezeichnung . ($orgform_kurzbz != '' ? ' (' . $orgform_kurzbz . ')' : '') . '</td></tr>';
		$email .= '<tr><td><b>' . $p->t('global/studiensemester') . '</b></td><td>' . $studiensemester_kurzbz . '</td></tr>';
		if ($studienplan_bezeichnung != '')
			$email .= '<tr><td><b>' . $p->t('studienplan/studienplan') . '</b></td><td>' . $studienplan_bezeichnung . '</td></tr>';
		else
			$email .= '<tr><td><b>' . $p->t('studienplan/studienplan') . '</b></td><td><span style="color: red">Es konnte kein passender Studienplan ermittelt werden</span></td></tr>';
		$email .= '<tr><td><b>' . $p->t('global/geschlecht') . '</b></td><td>' . ($person->geschlecht == 'm' ? $p->t('bewerbung/maennlich') : $p->t('bewerbung/weiblich')) . '</td></tr>';
		$email .= '<tr><td><b>' . $p->t('global/titel') . '</b></td><td>' . $person->titelpre . '</td></tr>';
		$email .= '<tr><td><b>' . $p->t('global/postnomen') . '</b></td><td>' . $person->titelpost . '</td></tr>';
		$email .= '<tr><td><b>' . $p->t('global/vorname') . '</b></td><td>' . $person->vorname . '</td></tr>';
		$email .= '<tr><td><b>' . $p->t('global/nachname') . '</b></td><td>' . $person->nachname . '</td></tr>';
		$email .= '<tr><td><b>' . $p->t('global/geburtsdatum') . '</b></td><td>' . date('d.m.Y', strtotime($person->gebdatum)) . '</td></tr>';
		$email .= '<tr><td><b>' . $p->t('global/adresse') . '</b></td><td>' . $strasse . '</td></tr>';
		$email .= '<tr><td><b>' . $p->t('global/plz') . '</b></td><td>' . $plz . '</td></tr>';
		$email .= '<tr><td><b>' . $p->t('global/ort') . '</b></td><td>' . $ort . '</td></tr>';
		$email .= '<tr><td><b>' . $p->t('incoming/nation') . '</b></td><td>' . $nation->langtext . '</td></tr>';
		$email .= '<tr><td><b>' . $p->t('global/emailAdresse') . '</b></td><td>' . $mailadresse . '</td></tr>';
		$email .= '<tr><td><b>' . $p->t('global/telefon') . '</b></td><td>' . $telefon . '</td></tr>';
		$email .= '<tr><td style="vertical-align:top"><b>' . $p->t('global/anmerkungen') . '</b></td><td>' . $anmerkungen . '</td></tr>';
		$email .= '<tr><td><b>' . $p->t('bewerbung/prestudentID') . '</b></td><td>' . $prestudent_id . '</td></tr>';
		$email .= '<tr><td style="vertical-align:top"><b>' . $p->t('tools/dokumente') . '</b></td><td>';
		$akte = new akte();
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
					$email .= '- <a href="' . APP_ROOT . 'cms/dms.php?id=' . $row->dms_id . '">' . $dokument->bezeichnung_mehrsprachig[DEFAULT_LANGUAGE] . '_' . $row->bezeichnung . '</a><br>';
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
		$email .= $p->t('bewerbung/emailBodyEnde');
	}
	else
	{
		$email = $p->t('bewerbung/emailBodyStart');
		$email .= '<br>';
		$email .= $p->t('global/studiengang') . ': ' . $typ->bezeichnung . ' ' . $studiengang->bezeichnung . ($orgform_kurzbz != '' ? ' (' . $orgform_kurzbz . ')' : '') . ' <br>';
		$email .= $p->t('global/studiensemester') . ': ' . $studiensemester_kurzbz . '<br>';
		$email .= $p->t('global/name') . ': ' . $person->vorname . ' ' . $person->nachname . '<br>';
		$email .= $p->t('bewerbung/prestudentID') . ': ' . $prestudent_id . '<br><br>';
		$email .= $p->t('bewerbung/emailBodyEnde');
	}
	
	$email = wordwrap($email, 70); // Bricht den Code um, da es sonst zu Anzeigefehlern im Mail kommen kann
	if (defined('BEWERBERTOOL_MAILEMPFANG') && BEWERBERTOOL_MAILEMPFANG != '')
		$empfaenger = BEWERBERTOOL_MAILEMPFANG;
	elseif (isset($empf_array[$prestudent->studiengang_kz]))
		$empfaenger = $empf_array[$prestudent->studiengang_kz];
	else
		$empfaenger = $studiengang->email;
	
	// Pfuschloesung fur BIF Dual
	if (CAMPUS_NAME == 'FH Technikum Wien' && $prestudent->studiengang_kz == 257 && $orgform_kurzbz == 'DUA')
		$empfaenger = 'info.bid@technikum-wien.at';
	
	// $email.= $empfaenger;
	$mail = new mail($empfaenger, 'no-reply', $p->t('bewerbung/bewerbung') . ' ' . $person->vorname . ' ' . $person->nachname . ($orgform_kurzbz != '' ? ' (' . $orgform_kurzbz . ')' : ''), 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Link vollständig darzustellen.');
	$mail->setHTMLContent($email);
	
	// send mail to Interessent
	if (defined('BEWERBERTOOL_ERFOLGREICHBEWORBENMAIL') && BEWERBERTOOL_ERFOLGREICHBEWORBENMAIL == true)
	{
		$kontakt = new kontakt();
		$kontakt->load_persKontakttyp($person->person_id, 'email');
		$mailadresse = isset($kontakt->result[0]->kontakt) ? $kontakt->result[0]->kontakt : '';
		
		$mail_bewerber = new mail($mailadresse, 'no-reply', 'Bewerbung erfolgreich abgeschickt', 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Inhalt vollständig darzustellen.');
		$email_bewerber = $p->t('bewerbung/erfolgreichBeworbenMail');
		$mail_bewerber->setHTMLContent($email_bewerber);
		if (! $mail_bewerber->send())
			return false;
	}
	
	if (! $mail->send())
		return false;
	else
		return true;
}
// sendet eine Email an die Assistenz, wenn nachträglich eine Bewerbung hinzugefügt wird
function sendAddStudiengang($prestudent_id, $studiensemester_kurzbz, $orgform_kurzbz)
{
	global $person_id;
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
	$kontakt->load_persKontakttyp($person->person_id, 'email');
	$mailadresse = isset($kontakt->result[0]->kontakt) ? $kontakt->result[0]->kontakt : '';
	
	$kontakt_t = new kontakt();
	$kontakt_t->load_persKontakttyp($person->person_id, 'telefon');
	$telefon = isset($kontakt_t->result[0]->kontakt) ? $kontakt_t->result[0]->kontakt : '';
	// Wenn Telefonnumer leer, alternativ Mobilnummer abfragen
	if ($telefon == '')
	{
		$kontakt_t->load_persKontakttyp($person->person_id, 'mobil');
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
	$notiz->getBewerbungstoolNotizen($person_id);
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
	
	$email = 'Es hat sich ' . ($person->geschlecht == 'm' ? 'ein Bewerber' : 'eine Bewerberin') . ' am System registriert<br>';
	$email .= '<br><table style="font-size:small"><tbody>';
	$email .= '<tr><td><b>' . $p->t('global/studiengang') . '</b></td><td>' . $typ->bezeichnung . ' ' . $studiengang->bezeichnung . ($orgform_kurzbz != '' ? ' (' . $orgform_kurzbz . ')' : '') . '</td></tr>';
	$email .= '<tr><td><b>' . $p->t('global/studiensemester') . '</b></td><td>' . $studiensemester_kurzbz . '</td></tr>';
	$email .= '<tr><td><b>' . $p->t('global/geschlecht') . '</b></td><td>' . ($person->geschlecht == 'm' ? $p->t('bewerbung/maennlich') : $p->t('bewerbung/weiblich')) . '</td></tr>';
	// $email.= '<tr><td><b>'.$p->t('global/titel').'</b></td><td>'.$person->titelpre.'</td></tr>';
	// $email.= '<tr><td><b>'.$p->t('global/postnomen').'</b></td><td>'.$person->titelpost.'</td></tr>';
	$email .= '<tr><td><b>' . $p->t('global/vorname') . '</b></td><td>' . $person->vorname . '</td></tr>';
	$email .= '<tr><td><b>' . $p->t('global/nachname') . '</b></td><td>' . $person->nachname . '</td></tr>';
	$email .= '<tr><td><b>' . $p->t('global/geburtsdatum') . '</b></td><td>' . date('d.m.Y', strtotime($person->gebdatum)) . '</td></tr>';
	$email .= '<tr><td><b>' . $p->t('global/emailAdresse') . '</b></td><td>' . $mailadresse . '</td></tr>';
	$email .= '<tr><td><b>' . $p->t('bewerbung/prestudentID') . '</b></td><td>' . $prestudent_id . '</td></tr>';
	$email .= '</td></tr></tbody></table>';
	$email .= '<br>';
	$email .= $p->t('bewerbung/emailBodyEnde');
	
	$email = wordwrap($email, 70); // Bricht den Code um, da es sonst zu Anzeigefehlern im Mail kommen kann
	if (defined('BEWERBERTOOL_MAILEMPFANG') && BEWERBERTOOL_MAILEMPFANG != '')
		$empfaenger = BEWERBERTOOL_MAILEMPFANG;
	elseif (isset($empf_array[$prestudent->studiengang_kz]))
		$empfaenger = $empf_array[$prestudent->studiengang_kz];
	else
		$empfaenger = $studiengang->email;
	// $email.= $empfaenger;
	$mail = new mail($empfaenger, 'no-reply', ($person->geschlecht == 'm' ? 'Neuer Bewerber ' : 'Neue Bewerberin ') . $person->vorname . ' ' . $person->nachname . ' registriert', 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Inhalt vollständig darzustellen.');
	$mail->setHTMLContent($email);
	if (! $mail->send())
		return false;
	else
		return true;
}
