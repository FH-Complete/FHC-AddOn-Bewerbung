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

session_cache_limiter('none'); //muss gesetzt werden sonst funktioniert der Download mit IE8 nicht
session_start();
if (!isset($_SESSION['bewerbung/user']) || $_SESSION['bewerbung/user']=='')
{
	$_SESSION['request_uri']=$_SERVER['REQUEST_URI'];

	header('Location: registration.php?method=allgemein');
	exit;
}

require_once('../../../include/konto.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/nation.class.php');
require_once('../../../include/person.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/kontakt.class.php');
require_once('../../../include/adresse.class.php');
require_once('../../../include/prestudent.class.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/zgv.class.php');
require_once('../../../include/dms.class.php');
require_once('../../../include/dokument.class.php');
require_once('../../../include/akte.class.php');
require_once('../../../include/mail.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/studienplan.class.php');
require_once('../../../include/basis_db.class.php');
require_once('../../../include/reihungstest.class.php');
require_once('../../../include/preinteressent.class.php');
require_once('../../../include/notiz.class.php');
require_once('../../../include/organisationseinheit.class.php');
require_once('../../../include/organisationsform.class.php');
require_once('../include/functions.inc.php');
require_once('../../../include/aufmerksamdurch.class.php');
require_once('../../../include/bisberufstaetigkeit.class.php');

if(isset($_GET['logout']))
{
	session_destroy();
	header('Location: registration.php');
}

$person_id = (int) $_SESSION['bewerbung/personId'];
$akte_id = isset($_GET['akte_id']) ? $_GET['akte_id'] : '';
$method = isset($_GET['method']) ? $_GET['method'] : '';
$datum = new datum();
$person = new person();

if(!$person->load($person_id))
{
	die($p->t('global/fehlerBeimLadenDesDatensatzes'));
}
//$sprache = DEFAULT_LANGUAGE;
$sprache = getSprache();
$p = new phrasen($sprache);

$eingabegesperrt=false;

// Wenn die eingeloggte Person bereits Student oder Mitarbeiter ist
// duerfen die Stammdaten nicht mehr geaendert werden
$benutzer = new benutzer();
if($benutzer->getBenutzerFromPerson($person->person_id,false))
{
	if(count($benutzer->result)>0)
	{
		$eingabegesperrt=true;

	}
}

//Wenn bereits eine Bewerbung abgeschickt wurde, duerfen die Stammdaten nicht mehr geaendert werden
if(check_person_bewerbungabgeschickt($person_id))
	$eingabegesperrt=true;

$message = '&nbsp;';

//$vollstaendig = '<span class="badge alert-success">'.$p->t('bewerbung/vollstaendig').' <span class="glyphicon glyphicon-ok"></span></span>';
//$unvollstaendig = '<span class="badge alert-danger">'.$p->t('bewerbung/unvollstaendig').' <span class="glyphicon glyphicon-remove"></span></span>';
$vollstaendig = '<span style="color: #3c763d;">'.$p->t('bewerbung/vollstaendig').'</span>';
$unvollstaendig = '<span style="color: #a94442;">'.$p->t('bewerbung/unvollstaendig').'</span>';

if($method=='delete')
{
	$akte= new akte();
	if(!$akte->load($akte_id))
	{
		$message = $p->t('global/fehlerBeiDerParameteruebergabe');
	}
	else
	{
		if($akte->person_id != $person_id)
		{
			die($p->t('global/fehlerBeimLadenDesDatensatzes'));
		}

		$dms_id = $akte->dms_id;
		$dms = new dms();

		if($akte->delete($akte_id))
		{
			if(!$dms->deleteDms($dms_id))
			{
				$message = $p->t('global/fehlerBeimLoeschenDesEintrags');
			}
			else
			{
				$message = $p->t('global/erfolgreichgelöscht');
			}
		}
		else
		{
			$message = $p->t('global/fehlerBeimLoeschenDesEintrags');
		}
	}

}


if(isset($_GET['rt_id']))
{

	$rt_id = filter_input(INPUT_GET, 'rt_id', FILTER_VALIDATE_INT);
	$pre_id = filter_input(INPUT_GET, 'pre', FILTER_VALIDATE_INT);

	if(isset($_GET['delete']))
	{
		$prestudent = new prestudent();
		if(!$prestudent->getPrestudenten($person_id))
		{
			die($p->t('global/fehlerBeimLadenDesDatensatzes'));
		}

		foreach($prestudent->result as $row)
		{
			if($row->prestudent_id == $pre_id)
			{
				$prest = new prestudent();
				$prest->load($pre_id);
				$prest->reihungstest_id = '';
				$prest->anmeldungreihungstest = '';
				$prest->updateamum = date("Y-m-d H:m:s");
				$prest->updatevon = 'online';
				$prest->new = false;

				if(!$prest->save())
				{
					echo $p->t('global/fehlerBeimSpeichernDerDaten');
				}
			}
		}
	}
	else
	{
		$reihungstest = new reihungstest;
		$reihungstest->load($rt_id);

		if($reihungstest->max_teilnehmer && $reihungstest->getTeilnehmerAnzahl($rt_id) >= $reihungstest->max_teilnehmer)
		{
			die($p->t('bewerbung/maxAnzahlTeilnehmer'));
		}

		$timestamp = time();

		$prestudent = new prestudent();
		if(!$prestudent->getPrestudenten($person_id))
		{
			die($p->t('global/fehlerBeimLadenDesDatensatzes'));
		}

		foreach($prestudent->result as $row)
		{
			if($row->prestudent_id == $pre_id)
			{
				$prest = new prestudent();
				$prest->load($pre_id);
				$prest->reihungstest_id = $rt_id;
				$prest->anmeldungreihungstest = date('Y-m-d', $timestamp);
				$prest->updateamum = date("Y-m-d H:m:s");
				$prest->updatevon = 'online';
				$prest->new = false;

				if(!$prest->save())
				{
					echo $p->t('global/fehlerBeimSpeichernDerDaten');
				}
			}
		}
	}
}
$save_error_abschicken='';
if(isset($_POST['btn_bewerbung_abschicken']))
{
   // Mail an zuständige Assistenz schicken
	$pr_id = isset($_POST['prestudent_id']) ? $_POST['prestudent_id'] : '';

	if($pr_id != '')
	{
		// Status Bewerber anlegen
		$prestudent_status = new prestudent();
		$prestudent_status->load($pr_id);

		$alterstatus = new prestudent();
		$alterstatus->getLastStatus($pr_id);

		// check ob es status schon gibt
		if($prestudent_status->load_rolle($pr_id, 'Interessent', $alterstatus->studiensemester_kurzbz, '1'))
		{
			/*$prestudent_status->status_kurzbz = 'Bewerber';
			$prestudent_status->studiensemester_kurzbz = $alterstatus->studiensemester_kurzbz;
			$prestudent_status->ausbildungssemester = '1';
			$prestudent_status->datum = date('Y-m-d H:i:s');
			$prestudent_status->insertamum = date('Y-m-d H:i:s');
			$prestudent_status->insertvon = '';
			$prestudent_status->updateamum = date('Y-m-d H:i:s');
			$prestudent_status->updatevon = '';
			$prestudent_status->studienplan_id = $alterstatus->studienplan_id;
			$prestudent_status->new = true;
			*/
			$prestudent_status->bestaetigtam=date('Y-m-d H:i:s');
			$prestudent_status->new=false;
			$prestudent_status->updateamum = date('Y-m-d H:i:s');
			$prestudent_status->updatevon = 'online';
			
			if(!$prestudent_status->save_rolle())
				die($p->t('global/fehlerBeimSpeichernDerDaten'));
		}
		
		$prestudent = new prestudent();
		$prestudent->load($pr_id);
		$studiengang = new studiengang();
		$studiengang->load($prestudent->studiengang_kz);
		if(sendBewerbung($pr_id,$prestudent_status->studiensemester_kurzbz,$prestudent_status->orgform_kurzbz))
		{
			$message = $p->t('bewerbung/erfolgreichBeworben',array($studiengang->bezeichnung_arr[$sprache]));
			//echo '<script type="text/javascript">alert("'.$p->t('bewerbung/erfolgreichBeworben',array($studiengang->bezeichnung_arr[$sprache])).'");</script>';
			//echo '<script type="text/javascript">window.location="'.$_SERVER['PHP_SELF'].'?active=abschicken";</script>';
			$save_error_abschicken=false;
		}
		else
		{
			echo '<script type="text/javascript">alert("'.$p->t('bewerbung/fehlerBeimVersendenDerBewerbung').'");</script>';
			$save_error_abschicken==true;
		}
	}
}

if(isset($_POST['submit_nachgereicht']))
{
	$akte = new akte;

	// gibt es schon einen eintrag?
	if(isset($_POST['akte_id']))
	{
		// Update
	}
	else
	{
		// Insert
		$akte->dokument_kurzbz = $_POST['dok_kurzbz'];
		$akte->person_id = $person_id;
		$akte->erstelltam = date('Y-m-d H:i:s');
		$akte->gedruckt = false;
		$akte->titel = '';
		$akte->anmerkung = $_POST['txt_anmerkung'];
		$akte->updateamum = date('Y-m-d H:i:s');
		$akte->updatevon = 'online';
		$akte->insertamum = date('Y-m-d H:i:s');
		$akte->insertvon = 'online';
		$akte->uid = '';
		$akte->new = true;
		$akte->nachgereicht = (isset($_POST['check_nachgereicht'])) ? true : false;
		if(!$akte->save())
		{
			echo $p->t('global/fehlerBeimSpeichernDerDaten').' '.$akte->errormsg;
		}
	}
}

// gibt an welcher Tab gerade aktiv ist
$active = filter_input(INPUT_GET, 'active');

if(!$active)
{
	$active = 'allgemein';
}
$save_error_daten='';
// Persönliche Daten speichern
if(isset($_POST['btn_person']) && !$eingabegesperrt)
{
	$person->titelpre = $_POST['titel_pre'];
	$person->vorname = $_POST ['vorname'];
	$person->nachname = $_POST['nachname'];
	$person->titelpost = $_POST['titel_post'];
	$person->gebdatum = $datum->formatDatum($_POST['geburtsdatum'], 'Y-m-d');
	$person->staatsbuergerschaft = $_POST['staatsbuergerschaft'];
	$person->geschlecht = $_POST['geschlecht'];
	$person->anrede = ($_POST['geschlecht']=='m'?'Herr':'Frau');
	$person->svnr = $_POST['svnr'];
	$person->gebort = $_POST['gebort'];
	$person->geburtsnation = $_POST['geburtsnation'];

	$person->new = false;
	
	if(!$person->save())
	{
		$message = $person->errormsg;
		$save_error_daten=true;
	}
	else
		$save_error_daten=false;
	
	if(!$save_error_daten && $person->checkSvnr($person->svnr, $person_id))
	{
		$message = $p->t('bewerbung/svnrBereitsVorhanden');
		$save_error_daten=true;
	}
	
	$berufstaetig = filter_input(INPUT_POST, 'berufstaetig');

	if(in_array($berufstaetig, array('Vollzeit', 'Teilzeit'), true))
	{

		$berufstaetig_art = filter_input(INPUT_POST, 'berufstaetig_art');
		$berufstaetig_dienstgeber = filter_input(INPUT_POST, 'berufstaetig_dienstgeber');

		$notiz = new notiz;
		$notiz->person_id = $person_id;
		$notiz->verfasser_uid = '_DummyStudent';
		$notiz->erledigt = false;
		$notiz->insertvon = 'online'; //Nicht aendern, da in notiz.class.php nach insertvon abgefragt wird
		$notiz->insertamum = date('c');
		$notiz->titel = 'Berufstätigkeit';
		$notiz->text = 'Berufstätig: ' . $berufstaetig . '; Dienstgeber: ' . $berufstaetig_dienstgeber
				. '; Art der Tätigkeit: ' . $berufstaetig_art;
		$notiz->save(true);
		$notiz->saveZuordnung();
	}

	$aufmerksamdurch = filter_input(INPUT_POST,'aufmerksamdurch');

	// Aufmerksamdurch speichern
	$prestudent = new prestudent();
	$prestudent->getPrestudenten($person_id);

	foreach($prestudent->result as $prestudent_eintrag)
	{
		$prestudent_eintrag->new=false;
		$prestudent_eintrag->aufmerksamdurch_kurzbz = $aufmerksamdurch;
		$prestudent_eintrag->save();
	}
}
$save_error_kontakt='';
// Kontaktdaten speichern
if(isset($_POST['btn_kontakt']))
{
	$save_error_kontakt=false;
	$kontakt = new kontakt();
	$kontakt->load_persKontakttyp($person->person_id, 'email');
	// gibt es schon kontakte von user
	if(count($kontakt->result)>0)
	{
		// Es gibt bereits einen Emailkontakt
		$kontakt_id = $kontakt->result[0]->kontakt_id;

		if($_POST['email'] == '')
		{
			// löschen
			$kontakt->delete($kontakt_id);
		}
		else
		{
			$kontakt->person_id = $person->person_id;
			$kontakt->kontakt_id = $kontakt_id;
			$kontakt->zustellung = true;
			$kontakt->kontakttyp = 'email';
			$kontakt->kontakt = $_POST['email'];
			$kontakt->new = false;

			if(!$kontakt->save())
			{
				$message = $kontakt->errormsg;
				$save_error_kontakt=true;
			}
			else
				$save_error_kontakt=false;
		}
	}
	else
	{
		// neuen Kontakt anlegen
		$kontakt->person_id = $person->person_id;
		$kontakt->zustellung = true;
		$kontakt->kontakttyp = 'email';
		$kontakt->kontakt = $_POST['email'];
		$kontakt->new = true;

		if(!$kontakt->save())
		{
			$message = $kontakt->errormsg;
			$save_error_kontakt=true;
		}
		else
			$save_error_kontakt=false;
	}
	
	if($save_error_kontakt===false)
	{
		$kontakt_t = new kontakt();
		$kontakt_t->load_persKontakttyp($person->person_id, 'telefon');
		
		// gibt es schon kontakte von user
		if(count($kontakt_t->result)>0)
		{
			// Es gibt bereits eine Telefonnummer
			$kontakt_id = $kontakt_t->result[0]->kontakt_id;
			
			// Telefonnummer validieren
			$telefonnummer = preg_replace("/[^0-9+]/", '', $_POST['telefonnummer']);
			if($_POST['telefonnummer'] == '')
			{
				// löschen
				$kontakt_t->delete($kontakt_id);
			}
			else
			{
				$kontakt_t->person_id = $person->person_id;
				$kontakt_t->kontakt_id = $kontakt_id;
				$kontakt_t->zustellung = true;
				$kontakt_t->kontakttyp = 'telefon';
				$kontakt_t->kontakt = $telefonnummer;
				$kontakt_t->new = false;
	
				if(!$kontakt_t->save())
				{
					$message = $kontakt_t->errormsg;
					$save_error_kontakt=true;
				}
				else
					$save_error_kontakt=false;
			}
		}
		else
		{
			if($_POST['telefonnummer']!='')
			{
				// Telefonnummer validieren
				$telefonnummer = preg_replace("/[^0-9+]/", '', $_POST['telefonnummer']);
				// neuen Kontakt anlegen
				$kontakt_t->person_id = $person->person_id;
				$kontakt_t->zustellung = true;
				$kontakt_t->kontakttyp = 'telefon';
				$kontakt_t->kontakt = $telefonnummer;
				$kontakt_t->new = true;
				
				if(!$kontakt_t->save())
				{
					$message = $kontakt_t->errormsg;
					$save_error_kontakt=true;
				}
				else
					$save_error_kontakt=false;
			}
		}
	}
	if($save_error_kontakt===false)
	{
		// Adresse Speichern
		if($_POST['strasse']!='' || $_POST['plz']!='' || $_POST['ort']!='')
		{
			$adresse = new adresse();
			$adresse->load_pers($person->person_id);
			if(count($adresse->result)>0)
			{
				// gibt es schon eine adresse, wird die erste adresse genommen und upgedatet
				$adresse_help = new adresse();
				$adresse_help->load($adresse->result[0]->adresse_id);
	
				// gibt schon eine Adresse
				$adresse_help->strasse = $_POST['strasse'];
				$adresse_help->plz = $_POST['plz'];
				$adresse_help->ort = $_POST['ort'];
				$adresse_help->nation = $_POST['nation'];
				$adresse_help->updateamum = date('Y-m-d H:i:s');
				$adresse_help->updatevon = 'online';
				$adresse_help->new = false;
				if(!$adresse_help->save())
				{
					$message = $adresse_help->errormsg;
					$save_error_kontakt=true;
				}
				else
					$save_error_kontakt=false;
			}
			else
			{
				// adresse neu anlegen
				$adresse->typ = 'h';
				$adresse->strasse = $_POST['strasse'];
				$adresse->plz = $_POST['plz'];
				$adresse->ort = $_POST['ort'];
				$adresse->nation = $_POST['nation'];
				$adresse->insertamum = date('Y-m-d H:i:s');
				$adresse->insertvon = 'online';
				$adresse->updateamum = date('Y-m-d H:i:s');
				$adresse->updatevon = 'online';
				$adresse->person_id = $person->person_id;
				$adresse->zustelladresse = true;
				$adresse->heimatadresse = true;
				$adresse->new = true;
				if(!$adresse->save())
				{
					$message = $adresse->errormsg;
					$save_error=true;
				}
				else
					$save_error=false;
			}
		}
	}
}
$save_error_zgv='';
if(isset($_POST['btn_zgv']))
{
	// Zugangsvoraussetzungen speichern
	$prestudent = new prestudent();
	$prestudent->getPrestudenten($person_id);

	//$master_zgv_art = filter_input(INPUT_POST, 'master_zgv_art', FILTER_VALIDATE_INT);
	$datum_bachelor = $datum->formatDatum(filter_input(INPUT_POST, 'bachelor_zgv_datum'), 'Y-m-d');
	$datum_master = $datum->formatDatum(filter_input(INPUT_POST, 'master_zgv_datum'), 'Y-m-d');
	$save_error_zgv=false;
	if($datum_bachelor>date('Y-m-d'))
	{
		$message = $p->t('bewerbung/zgvDatumNichtZukunft');
		$save_error_zgv=true;
		$datum_bachelor = '';
	}
	if($datum_master>date('Y-m-d'))
	{
		$message = $p->t('bewerbung/zgvDatumNichtZukunft');
		$save_error_zgv=true;
		$datum_master = '';
	}

	foreach($prestudent->result as $prestudent_eintrag)
	{
	
		$prestudent_eintrag->new = false;
		$prestudent_eintrag->zgv_code = filter_input(INPUT_POST, 'bachelor_zgv_art', FILTER_VALIDATE_INT);
		$prestudent_eintrag->zgvort = filter_input(INPUT_POST, 'bachelor_zgv_ort');
		$prestudent_eintrag->zgvdatum = $datum_bachelor;
		$prestudent_eintrag->zgvnation = filter_input(INPUT_POST, 'bachelor_zgv_nation');
		$prestudent_eintrag->updateamum = date('Y-m-d H:i:s');
		$prestudent_eintrag->updatevon = 'online';
	
		$prestudent_eintrag->zgvmas_code = filter_input(INPUT_POST, 'master_zgv_art', FILTER_VALIDATE_INT);
		$prestudent_eintrag->zgvmaort = filter_input(INPUT_POST, 'master_zgv_ort');
		$prestudent_eintrag->zgvmadatum = $datum_master;
		$prestudent_eintrag->zgvmanation = filter_input(INPUT_POST, 'master_zgv_nation');
		$prestudent_eintrag->updateamum = date('Y-m-d H:i:s');
		$prestudent_eintrag->updatevon = 'online';
	
		$prestudent_eintrag->updateamum = date('c');
	
		if(!$prestudent_eintrag->save())
		{
			die($p->t('global/fehlerBeimSpeichernDerDaten'));
		}
	}
}

// Persönliche Daten speichern
if(isset($_POST['btn_notiz']))
{
	$anmerkung = filter_input(INPUT_POST, 'anmerkung');

	if($anmerkung!='')
	{
		$notiz = new notiz;
		$notiz->person_id = $person_id;
		$notiz->verfasser_uid = '_DummyStudent';
		$notiz->erledigt = false;
		$notiz->insertvon = 'online_notiz'; //Nicht aendern, da in notiz.class.php nach insertvon abgefragt wird
		$notiz->insertamum = date('c');
		$notiz->titel = 'Anmerkung zur Bewerbung';
		$notiz->text = $anmerkung;
		$notiz->save(true);
		$notiz->saveZuordnung();
	}
}

$addStudiengang = filter_input(INPUT_POST, 'addStudiengang', FILTER_VALIDATE_BOOLEAN);

if($addStudiengang)
{
	$return = BewerbungPersonAddStudiengang($_POST['stgkz'], $_POST['anm'], $person, $_POST['studiensemester'], (isset($_POST['orgform'])?$_POST['orgform']:''));
	if($return===true)
		echo json_encode(array('status'=>'ok'));
	else
		echo json_encode(array('status'=>'fehler','msg'=>$return));
	exit;
}

// Abfrage ob ein Punkt schon vollständig ist
if($person->vorname && $person->nachname && $person->gebdatum && $person->staatsbuergerschaft && $person->geschlecht)
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
if ($eingabegesperrt==true)
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

if(isset($adresse->result[0]) && count($kontakt->result) && count($adresse->result[0]->strasse) && count($adresse->result[0]->plz) && count($adresse->result[0]->ort) && count($adresse->result[0]->nation) && count($kontakttel->result))
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
if ($eingabegesperrt==true)
{
	$status_kontakt = true;
	$status_kontakt_text = $vollstaendig;
}

$prestudent = new prestudent();
if(!$prestudent->getPrestudenten($person->person_id))
{
	die($p->t('global/fehlerBeimLadenDesDatensatzes'));
}

$master_zgv_done = false;
$bachelor_zgv_done = false;
$stg = new studiengang;

foreach($prestudent->result as $prestudent_eintrag)
{
	$studiengaenge[] = $prestudent_eintrag->studiengang_kz;
}
foreach($prestudent->result as $prestudent_eintrag)
{
	$master_zgv_done = isset($prestudent_eintrag->zgvmas_code);
	if($master_zgv_done)
		break;
}
foreach($prestudent->result as $prestudent_eintrag)
{
	$bachelor_zgv_done = isset($prestudent_eintrag->zgv_code);
	if($bachelor_zgv_done)
		break;
}

$status_zgv_bak = false;
$status_zgv_bak_text = $unvollstaendig;
$status_zgv_mas = false;
$status_zgv_mas_text = $unvollstaendig;
if(!defined('BEWERBERTOOL_ZGV_ANZEIGEN') || BEWERBERTOOL_ZGV_ANZEIGEN==true)
{
	if(isset($studiengaenge))
	{
		$types = $stg->getTypes($studiengaenge);

		if($bachelor_zgv_done)
		{
			$status_zgv_bak = true;
			$status_zgv_bak_text = $vollstaendig;
		}
		if((!in_array('m', $types, true) || $master_zgv_done))
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

$dokument_help = new dokument();
$dokument_help->getAllDokumenteForPerson($person_id, true);
$akte_person= new akte();
$akte_person->getAkten($person_id);

$missing = false;
$help_array = array();

foreach($akte_person->result as $akte)
{
	$help_array[] = $akte->dokument_kurzbz;
}

foreach($dokument_help->result as $dok)
{
	$dokument = new dokument();
	if($dokument->akzeptiert($dok->dokument_kurzbz, $person_id))
		$help_array[] = $dok->dokument_kurzbz;
}

foreach($dokument_help->result as $dok)
{
	if($dok->pflicht && !in_array($dok->dokument_kurzbz, $help_array, true))
	{
		$missing = true;
	}
}

if($missing && (!defined('BEWERBERTOOL_DOKUMENTE_ANZEIGEN') || BEWERBERTOOL_DOKUMENTE_ANZEIGEN==true))
{
	$status_dokumente = false;
	$status_dokumente_text = $unvollstaendig;
}
else
{
	$status_dokumente = true;
	$status_dokumente_text = $vollstaendig;
}

$konto = new konto();

$status_zahlungen = true;
$status_zahlungen_text = $vollstaendig;

if(!defined('BEWERBERTOOL_ZAHLUNGEN_ANZEIGEN') || BEWERBERTOOL_ZAHLUNGEN_ANZEIGEN==true)
{
	if(!$konto->checkKontostand($person_id))
	{
		$status_zahlungen = false;
		$status_zahlungen_text = $unvollstaendig;
	}
}

$prestudent = new prestudent();
if(!$prestudent->getPrestudenten($person_id))
{
	die($p->t('global/fehlerBeimLadenDesDatensatzes'));
}

$status_reihungstest = false;
$status_reihungstest_text = $unvollstaendig;
if(!defined('BEWERBERTOOL_REIHUNGSTEST_ANZEIGEN') || BEWERBERTOOL_REIHUNGSTEST_ANZEIGEN==true)
{
	foreach($prestudent->result as $row)
	{
		if($row->reihungstest_id != '')
		{
			$status_reihungstest = true;
			$status_reihungstest_text = $vollstaendig;
		}
		else
		{
			// Wenn keine Reihungstesttermine vorhanden sind ist die Bewerbung auch vollstaendig
			if(!$prestudent->getPrestudenten($person_id))
				die($p->t('global/fehlerBeimLadenDesDatensatzes'));

			$anzahl_reihungstests=0;
			foreach($prestudent->result as $row)
			{
				$reihungstest = new reihungstest();
				if(!$reihungstest->getStgZukuenftige($row->studiengang_kz))
					die($p->t('global/fehleraufgetreten').': '.$reihungstest->errormsg);

				$anzahl_reihungstests+=count($reihungstest->result);
			}
			if($anzahl_reihungstests==0)
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
		<link href="../../../submodules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css">
		<script src="../../../include/js/jquery.min.1.11.1.js"></script>
		<script src="../../../submodules/bootstrap/dist/js/bootstrap.min.js"></script>
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
						if(!defined('BEWERBERTOOL_DOKUMENTE_ANZEIGEN') || BEWERBERTOOL_DOKUMENTE_ANZEIGEN):
						?>
						<li>
							<a href="#dokumente" aria-controls="dokumente" role="tab" data-toggle="tab" <?php echo ($status_dokumente_text == $unvollstaendig?'style="background-color: #F2DEDE !important"':'style="background-color: #DFF0D8 !important"');?>>
								<?php echo $p->t('bewerbung/menuDokumente') ?> <br> <?php echo $status_dokumente_text;?>
							</a>
						</li>
						<?php endif; ?>

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
						if(!defined('BEWERBERTOOL_ZAHLUNGEN_ANZEIGEN') || BEWERBERTOOL_ZAHLUNGEN_ANZEIGEN):
						?>
						<li>
							<a href="#zahlungen" aria-controls="zahlungen" role="tab" data-toggle="tab" <?php echo ($status_zahlungen_text == $unvollstaendig?'style="background-color: #F2DEDE !important"':'style="background-color: #DFF0D8 !important"');?>>
								<?php echo $p->t('bewerbung/menuZahlungen') ?> <br> <?php echo $status_zahlungen_text;?>
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
					$tabs[]='dokumente';
				if(!defined('BEWERBERTOOL_ZGV_ANZEIGEN') || BEWERBERTOOL_ZGV_ANZEIGEN)
					$tabs[]='zgv';
				if(!defined('BEWERBERTOOL_ZAHLUNGEN_ANZEIGEN') || BEWERBERTOOL_ZAHLUNGEN_ANZEIGEN)
					$tabs[]='zahlungen';
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
function sendBewerbung($prestudent_id, $studiensemester_kurzbz, $orgform_kurzbz)
{
	global $person_id;
	$p = new phrasen(DEFAULT_LANGUAGE);
	
	//Array fuer Mailempfaenger. Vorruebergehende Loesung. Kindlm am 28.10.2015
	$empf_array = array();
	if(defined('BEWERBERTOOL_BEWERBUNG_EMPFAENGER'))
		$empf_array = unserialize(BEWERBERTOOL_BEWERBUNG_EMPFAENGER);

	$person = new person();
	$person->load($person_id);

	$prestudent = new prestudent();
	if(!$prestudent->load($prestudent_id))
		die($p->t('global/fehlerBeimLadenDesDatensatzes'));

	$studiengang = new studiengang();
	if(!$studiengang->load($prestudent->studiengang_kz))
		die($p->t('global/fehlerBeimLadenDesDatensatzes'));
	
	$typ = new studiengang();
	$typ->getStudiengangTyp($studiengang->typ);

	if(CAMPUS_NAME=='FH Technikum Wien')
	{
		$kontakt = new kontakt();
		$kontakt->load_persKontakttyp($person->person_id, 'email');
		$mailadresse = isset($kontakt->result[0]->kontakt)?$kontakt->result[0]->kontakt:'';
		
		$kontakt_t = new kontakt();
		$kontakt_t->load_persKontakttyp($person->person_id, 'telefon');
		$telefon = isset($kontakt_t->result[0]->kontakt)?$kontakt_t->result[0]->kontakt:'';
		//Wenn Telefonnumer leer, alternativ Mobilnummer abfragen
		if($telefon=='')
		{
			$kontakt_t->load_persKontakttyp($person->person_id, 'mobil');
			$telefon = isset($kontakt_t->result[0]->kontakt)?$kontakt_t->result[0]->kontakt:'';
		}
		
		$adresse = new adresse();
		$adresse->load_pers($person->person_id);
		$strasse = isset($adresse->result[0]->strasse)?$adresse->result[0]->strasse:'';
		$plz = isset($adresse->result[0]->plz)?$adresse->result[0]->plz:'';
		$ort = isset($adresse->result[0]->ort)?$adresse->result[0]->ort:'';
		$adr_nation = isset($adresse->result[0]->nation)?$adresse->result[0]->nation:'';
		$nation = new nation($adr_nation);
		
		$notiz = new notiz;
		$notiz->getBewerbungstoolNotizen($person_id);
		$anmerkungen='';
		foreach($notiz->result as $note)
		{
			if($note->insertvon == 'online_notiz')
			{
				if($anmerkungen!='')
					$anmerkungen .= '<br>';
				$anmerkungen .= '- '.htmlspecialchars($note->text);
			}
		}
		
		$email = $p->t('bewerbung/emailBodyStart');
		$email.= '<br><table style="font-size:small"><tbody>';
		$email.= '<tr><td><b>'.$p->t('global/studiengang').'</b></td><td>'.$typ->bezeichnung.' '.$studiengang->bezeichnung.($orgform_kurzbz!=''?' ('.$orgform_kurzbz.')':'').'</td></tr>';
		$email.= '<tr><td><b>'.$p->t('global/studiensemester').'</b></td><td>'.$studiensemester_kurzbz.'</td></tr>';
		$email.= '<tr><td><b>'.$p->t('global/geschlecht').'</b></td><td>'.($person->geschlecht=='m'?$p->t('bewerbung/maennlich'):$p->t('bewerbung/weiblich')).'</td></tr>';
		$email.= '<tr><td><b>'.$p->t('global/titel').'</b></td><td>'.$person->titelpre.'</td></tr>';
		$email.= '<tr><td><b>'.$p->t('global/postnomen').'</b></td><td>'.$person->titelpost.'</td></tr>';
		$email.= '<tr><td><b>'.$p->t('global/vorname').'</b></td><td>'.$person->vorname.'</td></tr>';
		$email.= '<tr><td><b>'.$p->t('global/nachname').'</b></td><td>'.$person->nachname.'</td></tr>';
		$email.= '<tr><td><b>'.$p->t('global/geburtsdatum').'</b></td><td>'.date('d.m.Y', strtotime($person->gebdatum)).'</td></tr>';
		$email.= '<tr><td><b>'.$p->t('global/adresse').'</b></td><td>'.$strasse.'</td></tr>';
		$email.= '<tr><td><b>'.$p->t('global/plz').'</b></td><td>'.$plz.'</td></tr>';
		$email.= '<tr><td><b>'.$p->t('global/ort').'</b></td><td>'.$ort.'</td></tr>';
		$email.= '<tr><td><b>'.$p->t('incoming/nation').'</b></td><td>'.$nation->langtext.'</td></tr>';
		$email.= '<tr><td><b>'.$p->t('global/emailAdresse').'</b></td><td>'.$mailadresse.'</td></tr>';
		$email.= '<tr><td><b>'.$p->t('global/telefon').'</b></td><td>'.$telefon.'</td></tr>';
		$email.= '<tr><td style="vertical-align:top"><b>'.$p->t('global/anmerkungen').'</b></td><td>'.$anmerkungen.'</td></tr>';
		$email.= '<tr><td><b>'.$p->t('bewerbung/prestudentID').'</b></td><td>'.$prestudent_id.'</td></tr>';
		$email.= '<tr><td style="vertical-align:top"><b>'.$p->t('tools/dokumente').'</b></td><td>';
		$akte = new akte;
		$akte->getAkten($person_id);
		foreach($akte->result AS $row)
		{
			$dokument = new dokument();
			$dokument->loadDokumenttyp($row->dokument_kurzbz);
			if ($row->insertvon=='online')
			{
				if($row->nachgereicht==true)
					$email.= '- '.$dokument->bezeichnung_mehrsprachig[DEFAULT_LANGUAGE].' -> '.$p->t('bewerbung/dokumentWirdNachgereicht').'<br>';
				else
					$email.= '- <a href="https://vilesci.technikum-wien.at/content/akte.php?akte_id='.$row->akte_id.'">'.$dokument->bezeichnung_mehrsprachig[DEFAULT_LANGUAGE].'_'.$row->bezeichnung.'</a><br>';
			}
		}
		$email.= '</td></tr></tbody></table>';
		$email.= '<br>';
		$email.= $p->t('bewerbung/emailBodyEnde');
	}
	else 
	{
		$email = $p->t('bewerbung/emailBodyStart');
		$email.= '<br>';
		$email.= $p->t('global/studiengang').': '.$typ->bezeichnung.' '.$studiengang->bezeichnung.($orgform_kurzbz!=''?' ('.$orgform_kurzbz.')':'').' <br>';
		$email.= $p->t('global/studiensemester').': '.$studiensemester_kurzbz.'<br>';
		$email.= $p->t('global/name').': '.$person->vorname.' '.$person->nachname.'<br>';
		$email.= $p->t('bewerbung/prestudentID').': '.$prestudent_id.'<br><br>';
		$email.= $p->t('bewerbung/emailBodyEnde');
	}
	
	$email = wordwrap($email,70); //Bricht den Code um, da es sonst zu Anzeigefehlern im Mail kommen kann
	if(defined('BEWERBERTOOL_MAILEMPFANG') && BEWERBERTOOL_MAILEMPFANG!='')
		$empfaenger = BEWERBERTOOL_MAILEMPFANG;
	elseif(isset($empf_array[$prestudent->studiengang_kz]))
		$empfaenger = $empf_array[$prestudent->studiengang_kz];
	else
		$empfaenger = $studiengang->email;
	//$email.= $empfaenger;
	$mail = new mail($empfaenger, 'no-reply', $p->t('bewerbung/bewerbung').' '.$person->vorname.' '.$person->nachname, 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Link vollständig darzustellen.');
	$mail->setHTMLContent($email);
	if(!$mail->send())
		return false;
	else
		return true;

}
// sendet eine Email an die Assistenz, wenn nachträglich eine Bewerbung hinzugefügt wird
function sendAddStudiengang($prestudent_id, $studiensemester_kurzbz, $orgform_kurzbz)
{
	global $person_id;
	$p = new phrasen(DEFAULT_LANGUAGE);
	
	//Array fuer Mailempfaenger. Vorruebergehende Loesung. Kindlm am 28.10.2015
	$empf_array = array();
	if(defined('BEWERBERTOOL_BEWERBUNG_EMPFAENGER'))
		$empf_array = unserialize(BEWERBERTOOL_BEWERBUNG_EMPFAENGER);

	$person = new person();
	$person->load($person_id);

	$prestudent = new prestudent();
	if(!$prestudent->load($prestudent_id))
		die($p->t('global/fehlerBeimLadenDesDatensatzes'));

	$studiengang = new studiengang();
	if(!$studiengang->load($prestudent->studiengang_kz))
		die($p->t('global/fehlerBeimLadenDesDatensatzes'));
	
	$typ = new studiengang();
	$typ->getStudiengangTyp($studiengang->typ);

	$kontakt = new kontakt();
	$kontakt->load_persKontakttyp($person->person_id, 'email');
	$mailadresse = isset($kontakt->result[0]->kontakt)?$kontakt->result[0]->kontakt:'';
	
	$kontakt_t = new kontakt();
	$kontakt_t->load_persKontakttyp($person->person_id, 'telefon');
	$telefon = isset($kontakt_t->result[0]->kontakt)?$kontakt_t->result[0]->kontakt:'';
	//Wenn Telefonnumer leer, alternativ Mobilnummer abfragen
	if($telefon=='')
	{
		$kontakt_t->load_persKontakttyp($person->person_id, 'mobil');
		$telefon = isset($kontakt_t->result[0]->kontakt)?$kontakt_t->result[0]->kontakt:'';
	}
	
	$adresse = new adresse();
	$adresse->load_pers($person->person_id);
	$strasse = isset($adresse->result[0]->strasse)?$adresse->result[0]->strasse:'';
	$plz = isset($adresse->result[0]->plz)?$adresse->result[0]->plz:'';
	$ort = isset($adresse->result[0]->ort)?$adresse->result[0]->ort:'';
	$adr_nation = isset($adresse->result[0]->nation)?$adresse->result[0]->nation:'';
	$nation = new nation($adr_nation);
	
	$notiz = new notiz;
	$notiz->getBewerbungstoolNotizen($person_id);
	$anmerkungen='';
	foreach($notiz->result as $note)
	{
		if($note->insertvon == 'online_notiz')
		{
			if($anmerkungen!='')
				$anmerkungen .= '<br>';
			$anmerkungen .= '- '.htmlspecialchars($note->text);
		}
	}
	
	$email = 'Es hat sich '.($geschlecht=='m'?'ein Bewerber':'eine Bewerberin').' am System registriert<br>';
	$email.= '<br><table style="font-size:small"><tbody>';
	$email.= '<tr><td><b>'.$p->t('global/studiengang').'</b></td><td>'.$typ->bezeichnung.' '.$studiengang->bezeichnung.($orgform_kurzbz!=''?' ('.$orgform_kurzbz.')':'').'</td></tr>';
	$email.= '<tr><td><b>'.$p->t('global/studiensemester').'</b></td><td>'.$studiensemester_kurzbz.'</td></tr>';
	$email.= '<tr><td><b>'.$p->t('global/geschlecht').'</b></td><td>'.($person->geschlecht=='m'?$p->t('bewerbung/maennlich'):$p->t('bewerbung/weiblich')).'</td></tr>';
	//$email.= '<tr><td><b>'.$p->t('global/titel').'</b></td><td>'.$person->titelpre.'</td></tr>';
	//$email.= '<tr><td><b>'.$p->t('global/postnomen').'</b></td><td>'.$person->titelpost.'</td></tr>';
	$email.= '<tr><td><b>'.$p->t('global/vorname').'</b></td><td>'.$person->vorname.'</td></tr>';
	$email.= '<tr><td><b>'.$p->t('global/nachname').'</b></td><td>'.$person->nachname.'</td></tr>';
	$email.= '<tr><td><b>'.$p->t('global/geburtsdatum').'</b></td><td>'.date('d.m.Y', strtotime($person->gebdatum)).'</td></tr>';
	$email.= '<tr><td><b>'.$p->t('global/emailAdresse').'</b></td><td>'.$mailadresse.'</td></tr>';
	$email.= '<tr><td><b>'.$p->t('bewerbung/prestudentID').'</b></td><td>'.$prestudent_id.'</td></tr>';
	$email.= '</td></tr></tbody></table>';
	$email.= '<br>';
	$email.= $p->t('bewerbung/emailBodyEnde');
	
	$email = wordwrap($email,70); //Bricht den Code um, da es sonst zu Anzeigefehlern im Mail kommen kann
	if(defined('BEWERBERTOOL_MAILEMPFANG') && BEWERBERTOOL_MAILEMPFANG!='')
		$empfaenger = BEWERBERTOOL_MAILEMPFANG;
	elseif(isset($empf_array[$prestudent->studiengang_kz]))
		$empfaenger = $empf_array[$prestudent->studiengang_kz];
	else
		$empfaenger = $studiengang->email;
	//$email.= $empfaenger;
	$mail = new mail($empfaenger, 'no-reply', ($person->geschlecht=='m'?'Neuer Bewerber ':'Neue Bewerberin ').$person->vorname.' '.$person->nachname.' registriert', 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Inhalt vollständig darzustellen.');
	$mail->setHTMLContent($email);
	if(!$mail->send())
		return false;
	else
		return true;

}
