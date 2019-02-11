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

require_once('../../../config/global.config.inc.php');
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/person.class.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/mail.class.php');
require_once('../../../include/prestudent.class.php');
require_once('../../../include/preinteressent.class.php');
require_once('../../../include/kontakt.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/bewerbungstermin.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/sprache.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/konto.class.php');
require_once('../include/functions.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../bewerbung.config.inc.php');
require_once ('../../../include/personlog.class.php');
require_once '../../../include/securimage/securimage.php';
require_once('../../../include/studienplan.class.php');
require_once('../../../include/studienordnung.class.php');

session_cache_limiter('none'); //muss gesetzt werden sonst funktioniert der Download mit IE8 nicht
session_start();

$lang = filter_input(INPUT_GET, 'lang');
$log = new personlog();

if(isset($lang))
{
	setSprache($lang);
}

$method = filter_input(INPUT_GET, 'method');
$message = '';
$datum = new datum();

$sprache = filter_input(INPUT_GET, 'sprache');

if(isset($sprache))
{
	$sprache = new sprache();
	if($sprache->load($_GET['sprache']))
	{
		setSprache($_GET['sprache']);
	}
	else
		setSprache(DEFAULT_LANGUAGE);
}

$sprache = getSprache();
$p = new phrasen($sprache);
$db = new basis_db();
$userid = trim(filter_input(INPUT_POST, 'userid'));
$mailadresse = trim(filter_input(INPUT_POST, 'mailadresse'));
$username = trim(filter_input(INPUT_POST, 'username'));
$password = trim(filter_input(INPUT_POST, 'password'));
$codeGet = trim(filter_input(INPUT_GET, 'code'));
$emailAdresseGet = trim(filter_input(INPUT_GET, 'emailAdresse'));

// Erstellen eines Array mit allen Studiengängen
$studiengaenge_obj = new studiengang();
$studiengaenge_obj->getAll();

// Login gestartet
if ($userid)
{
	$person = new person();

	$person_id = $person->checkZugangscodePerson($userid);

	//Zugangscode wird überprüft
	if($person_id != false)
	{
		$validMail = false;
		// Wenn eine Mailadresse der Person mit der eingegebenen Mailadresse übereinstimmt, ist die Anmeldung gültig
		$kontakte = new kontakt();
		$kontakte->load_persKontakttyp($person_id, 'email');
		foreach ($kontakte->result AS $kontakt)
		{
			if (strtolower($kontakt->kontakt) == strtolower($mailadresse))
			{
				$validMail = true;
				break;
			}
		}
		
		if ($validMail)
		{
			$_SESSION['bewerbung/user'] = $userid;
			$_SESSION['bewerbung/personId'] = $person_id;
			
			$log->log(	$person_id,
				'Action',
				array('name'=>'Login with code','success'=>true,'message'=>'Login with access code'),
				'bewerbung',
				'bewerbung',
				null,
				'online'
				);
			
			header('Location: bewerbung.php?active='.filter_input(INPUT_POST, 'active'));
			exit;
		}
		else 
		{
			$message = '<script type="text/javascript">alert("'.$p->t('bewerbung/mailFalsch').'")</script>';
		}
	}
	else
	{
		$message = '<script type="text/javascript">alert("'.$p->t('bewerbung/zugangsdatenFalsch').'")</script>';
	}
}
elseif($username && $password)
{
	$benutzer = new benutzer();
	if($benutzer->load($username))
	{
		$auth = new authentication();
		if($auth->checkpassword($username, $password))
		{
			$person_id = $benutzer->person_id;
			$userid='Login';

			if($person_id != false)
			{
				$_SESSION['bewerbung/user'] = $userid;
				$_SESSION['bewerbung/personId'] = $person_id;
				
				$log->log($person_id,
					'Action',
					array('name'=>'Login with user','success'=>true,'message'=>'Login with username and password'),
					'bewerbung',
					'bewerbung',
					null,
					'online'
				);

				header('Location: bewerbung.php?active='.filter_input(INPUT_POST, 'active'));
				exit;
			}
			else
			{
				$message = '<script type="text/javascript">alert("'.$p->t('bewerbung/zugangsdatenFalsch').'")</script>';
			}
		}
		else
		{
			$message = '<script type="text/javascript">alert("'.$p->t('bewerbung/zugangsdatenFalsch').'")</script>';
		}
	}
	else
	{
		$message = '<script type="text/javascript">alert("'.$p->t('bewerbung/zugangsdatenFalsch').'")</script>';
	}

}
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo $p->t('bewerbung/bewerbung') ?></title>
		<meta http-equiv="X-UA-Compatible" content="chrome=1">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<meta name="robots" content="noindex">
		<link href="../../../vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" type="text/css" href="../../../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
		<link href="../include/css/registration.css" rel="stylesheet" type="text/css">
	</head>
	<body class="main">
		<div class="container">
			<?php
			$sprache2 = new sprache();
			$sprache2->getAll(true);
			?>
			<div class="dropdown pull-right">
				<button class="btn btn-default dropdown-toggle" type="button" id="sprache-label" data-toggle="dropdown" aria-expanded="true">
					<?php echo $sprache2->getBezeichnung(getSprache(), getSprache()) ?>
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu" role="menu" aria-labelledby="sprache-label" id="sprache-dropdown">
					<?php foreach($sprache2->result as $row): ?>
						<li role="presentation">
							<a href="#" role="menuitem" tabindex="-1" data-sprache="<?php echo $row->sprache ?>">
								<?php echo $row->bezeichnung_arr[getSprache()] ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<ol class="breadcrumb">
				<?php if($method === 'registration' || $method === 'resendcode'): ?>
					<li>
						<a href="<?php echo basename(__FILE__) ?>">
							<?php echo $p->t('bewerbung/login') ?>
						</a>
					</li>
					<li class="active">
						<?php echo $p->t('bewerbung/registrieren') ?>
					</li>
				<?php else: ?>
					<li class="active">
						<?php echo $p->t('bewerbung/login') ?>
					</li>
				<?php endif; ?>
			</ol>
			<?php
			/**
			 * Maske zum Registrieren wird angezeigt
			 * Nach erfolgreicher Registration wird eine Benutzer ID erstellt und an den Benutzer geschickt
			 */
			if($method == 'registration'):
				// Falls Sicherheitscode falsch ist - übergebene Werte speichern und vorausfüllen
				$date = new datum();
				$stsem = new studiensemester();
				$stsem->getStudiensemesterOnlinebewerbung();

				$vorname = filter_input(INPUT_POST, 'vorname');
				$nachname = filter_input(INPUT_POST, 'nachname');
				$geb_datum = filter_input(INPUT_POST, 'geb_datum');
				if($geb_datum)
				{
					$geb_datum = date('Y-m-d', strtotime($geb_datum));
				}
				$geschlecht = filter_input(INPUT_POST, 'geschlecht');
				$email = filter_input(INPUT_POST, 'email');
				$anmerkungen = filter_input(INPUT_POST, 'anmerkung', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
				$orgform_get = filter_input(INPUT_GET, 'orgform_kurzbz');
				$studiengang_get = filter_input(INPUT_GET, 'stg_kz');
				$prioritaeten = filter_input(INPUT_POST, 'prioritaet', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
				$studienplaene = filter_input(INPUT_POST, 'studienplaene', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
				$std_semester = filter_input(INPUT_POST, 'studiensemester_kurzbz');
				$typen = array();

				//Prioritäten neu soritieren falls Lücken sind (zB 1,3,4 ändern auf 1,2,3)
				if (isset($prioritaeten))
				{
					function empty_sort ($a, $b)
					{
						if ($a == '' && $b != '') return 1;
						if ($b == '' && $a != '') return -1;
						return ($a < $b) ? -1 : 1;
					}
					uasort($prioritaeten, 'empty_sort');
					
					$i = 1;
					foreach ($prioritaeten AS $key => $value)
					{
						if ($value != '' && $value != $i)
							$prioritaeten[$key] = strval($i);
						
						$i++;
					}
				}

				if(BEWERBERTOOL_STUDIENAUSWAHL_ANZEIGEN)
				{
					// Wenn kein Studiensemester uebergeben wird, das erste Wintersemester aus getStudiensemesterOnlinebewerbung nehmen, wenn vorhanden, 
					// sonst das Erste, das zurück kommt
					if ($std_semester == '' && isset($stsem->studiensemester[0]))
					{
						$std_semester = $stsem->studiensemester[0]->studiensemester_kurzbz;
						foreach ($stsem->studiensemester AS $row)
						{
							if (substr($row->studiensemester_kurzbz, 0, 2) == 'WS')
							{
								$std_semester = $row->studiensemester_kurzbz;
								break;
							}
						}
					}

					if(!is_array($studienplaene))
					{
						$studienplaene = array();
					}
					// Richtigen Studienplan ermitteln, wenn Studiengang und Orgform als GET-Parameter übergeben werden
					if($studiengang_get != '')
					{
						$studienplan = new studienplan();
						if($orgform_get != '')
						{
							$studienplan->getStudienplaeneFromSem($studiengang_get, $std_semester, '1', $orgform_get);
						}
						else
						{
							$studienplan->getStudienplaeneFromSem($studiengang_get, $std_semester, '1');
						}

						// Wenn kein passender Studienplan gefunden wird, wird er NULL gesetzt
						foreach ($studienplan->result AS $row)
						{
							$studienplaene[] = $row->studienplan_id;
						}
					}
					
					
				}
				else
				{
					if (isset($stsem->studiensemester[0]))
						$std_semester = $stsem->studiensemester[0]->studiensemester_kurzbz;
					else
						$std_semester = null;
				}
				
				$studienplaeneBaMa = array(); // Nur Bachelor oder Master Studienplaene fuer korrekte zaehlung
				foreach ($studienplaene AS $value)
				{
					$studienordnung = new studienordnung();
					$studienordnung->getStudienordnungFromStudienplan($value);
					$studiengang = new studiengang($studienordnung->studiengang_kz);
					
					if ($studiengang->typ == 'b' || $studiengang->typ == 'm')
					{
						$studienplaeneBaMa[] = $value;
					}
					//In der Stg-Auswahl verwendete Typen
					$typen[] .= $studiengang->typ;
					
				}

				$submit = filter_input(INPUT_POST, 'submit_btn');
				$resend_code = filter_input(INPUT_GET, 'ReSendCode');

				if(isset($submit) || isset($resend_code))
				{
					// Pruefen, ob schon eine Bewerbung fuer diese Mailadresse existiert->Wenn ja, Code nochmal dorthin schicken
					$return = check_load_bewerbungen(trim($email));
					if($return)
					{
						//Wenn es noch keinen Zugangscode für die Person gibt, generiere einen
						if($return->zugangscode == '')
						{
							$person = new person($return->person_id);

							$zugangscode = substr(md5(openssl_random_pseudo_bytes(20)), 0, 15);

							$person->zugangscode = $zugangscode;
							$person->updateamum = date('Y-m-d H:i:s');
							$person->updatevon = 'online';
							$person->new = false;

							if(!$person->save())
							{
								die($p->t('global/fehlerBeimSpeichernDerDaten'));
							}
							else 
							{
								// Logeintrag schreiben
								$log->log($return->person_id,
									'Action',
									array('name'=>'Access code generated','success'=>true,'message'=>'Access code has been generated because none was present'),
									'bewerbung',
									'bewerbung',
									null,
									'online'
								);
							}
						}
						// Beim erneuten Zuschicken des Zugangscodes, wird aus Sicherheitsgründen ein Neuer generiert
						if (isset($resend_code))
						{
							$person = new person($return->person_id);
							
							$zugangscode = substr(md5(openssl_random_pseudo_bytes(20)), 0, 15);
							
							$person->zugangscode = $zugangscode;
							$person->updateamum = date('Y-m-d H:i:s');
							$person->updatevon = 'online';
							$person->new = false;
							
							if(!$person->save())
							{
								die($p->t('global/fehlerBeimSpeichernDerDaten'));
							}
							else
							{
								// Logeintrag schreiben
								$log->log($return->person_id,
									'Action',
									array('name'=>'Access code requested','success'=>true,'message'=>'User requested for access code. New access code was generated'),
									'bewerbung',
									'bewerbung',
									null,
									'online'
									);
								echo '<p class="alert alert-success">'.resendMail($zugangscode, $email).'</p>';
								exit();
							}
						}
						else
						{
							$message = '<p class="alert alert-danger" id="danger-alert">'.$p->t('bewerbung/mailadresseBereitsGenutzt',array($email)).'</p>
							<button type="submit" class="btn btn-primary" value="Ja" onclick="document.RegistrationLoginForm.action=\''.basename(__FILE__).'?method=registration&ReSendCode\'; document.getElementById(\'RegistrationLoginForm\').submit();">'.$p->t('bewerbung/codeZuschicken').'</button>
							<button type="submit" class="btn btn-primary" value="Nein" onclick="document.RegistrationLoginForm.email.value=\'\'; document.getElementById(\'RegistrationLoginForm\').submit();">'.$p->t('global/abbrechen').'</button>';
							// Logeintrag schreiben
							$log->log($return->person_id,
								'Action',
								array('name'=>'Attempt to register with existing mailadress','success'=>false,'message'=>'User tried to register with the existing mail adress '.$email),
								'bewerbung',
								'bewerbung',
								null,
								'online'
							);
						}

					}
					else
					{
						$securimage = new Securimage();
						// Sicherheitscode wurde falsch eingegeben
						if ($securimage->check($_POST['captcha_code']) == false)
						{
							$message = '<p class="bg-danger padding-10">'.$p->t('bewerbung/sicherheitscodeFalsch').'</p>';
						}
						elseif (BEWERBERTOOL_STUDIENAUSWAHL_ANZEIGEN && count($studienplaene) == 0)
						{
							$message = '<p class="bg-danger padding-10">'.$p->t('bewerbung/bitteStudienrichtungWaehlen').'</p>';
						}
						elseif (BEWERBERTOOL_STUDIENAUSWAHL_ANZEIGEN && defined('BEWERBERTOOL_MAX_STUDIENGAENGE') && BEWERBERTOOL_MAX_STUDIENGAENGE != '' && count($studienplaeneBaMa) > BEWERBERTOOL_MAX_STUDIENGAENGE)
						{
							$message = '<p class="bg-danger padding-10">'.$p->t('bewerbung/sieKoennenMaximalXStudiengaengeWaehlen', array(BEWERBERTOOL_MAX_STUDIENGAENGE)).'</p>';
						}
						elseif (BEWERBERTOOL_SHOW_ZUSTIMMUNGSERKLAERUNG_REGISTRATION && !isset($_POST['zustimmung_datenuebermittlung']))
						{
							$message = '<p class="bg-danger padding-10">'.$p->t('bewerbung/bitteDatenuebermittlungZustimmen').'</p>';
						}
						else
						{
							// Person anlegen
							$person = new person();

							$zugangscode = substr(md5(openssl_random_pseudo_bytes(20)), 0, 15);

							$person->nachname = $nachname;
							$person->vorname = $vorname;
							$person->gebdatum = $geb_datum;
							$person->geschlecht = $geschlecht;
							$person->anrede = ($geschlecht=='m'?'Herr':'Frau');
							$person->aktiv = true;
							$person->zugangscode = $zugangscode;
							$person->insertamum = date('Y-m-d H:i:s');
							$person->insertvon = 'online';
							$person->updateamum = date('Y-m-d H:i:s');
							$person->updatevon = 'online';
							$person->new = true;

							if(!$person->save())
							{
								die($p->t('global/fehlerBeimSpeichernDerDaten'));
							}

							// Email Kontakt zu Person speichern
							$kontakt = new kontakt();
							$kontakt->person_id = $person->person_id;
							$kontakt->kontakttyp = 'email';
							$kontakt->kontakt = $email;
							$kontakt->zustellung = true;
							$kontakt->insertamum = date('Y-m-d H:i:s');
							$kontakt->insertvon = 'online';
							$kontakt->updateamum = date('Y-m-d H:i:s');
							$kontakt->updatevon = 'online';
							$kontakt->new = true;

							if(!$kontakt->save())
							{
								die($p->t('global/fehlerBeimSpeichernDerDaten'));
							}

							// Logeintrag schreiben
							$log->log($person->person_id,
								'Processstate',
								array('name'=>'New registration','message'=>'Person registered in application tool'),
								'bewerbung',
								'bewerbung',
								null,
								'online');

							if(BEWERBERTOOL_STUDIENAUSWAHL_ANZEIGEN && count($studienplaene) < ANZAHL_PREINTERESSENT)
							{
								// Prestudenten anlegen
								for($i = 0; $i < count($studienplaene); $i++)
								{
									$studienordnung = new studienordnung();
									$studienordnung->getStudienordnungFromStudienplan($studienplaene[$i]);
									
									$prestudent = new prestudent();
									$prestudent->person_id = $person->person_id;
									$prestudent->studiengang_kz = $studienordnung->studiengang_kz;
									$prestudent->aufmerksamdurch_kurzbz = '';
									$prestudent->insertamum = date('Y-m-d H:i:s');
									$prestudent->insertvon = 'online';
									$prestudent->updateamum = date('Y-m-d H:i:s');
									$prestudent->reihungstestangetreten = false;
									$prestudent->priorisierung = $prioritaeten[$studienplaene[$i]];
									$prestudent->new = true;

									if(!$prestudent->save())
									{
										die($p->t('global/fehlerBeimSpeichernDerDaten'));
									}

									$studienplan = new studienplan();
									$studienplan->loadStudienplan($studienplaene[$i]);
									
									$studiengang = new studiengang($studienordnung->studiengang_kz);

									// Interessenten Status anlegen
									$prestudent_status = new prestudent();
									$prestudent_status->load($prestudent->prestudent_id);
									$prestudent_status->status_kurzbz = 'Interessent';
									$prestudent_status->studiensemester_kurzbz = $std_semester;
									$prestudent_status->ausbildungssemester = '1';
									$prestudent_status->datum = date("Y-m-d H:i:s");
									$prestudent_status->insertamum = date("Y-m-d H:i:s");
									$prestudent_status->insertvon = 'online';
									$prestudent_status->updateamum = date("Y-m-d H:i:s");
									$prestudent_status->updatevon = 'online';
									$prestudent_status->new = true;
									$prestudent_status->anmerkung_status = '';
									$prestudent_status->orgform_kurzbz = $studienplan->orgform_kurzbz;
									$prestudent_status->studienplan_id = $studienplaene[$i];

									if(!$prestudent_status->save_rolle())
									{
										die($p->t('global/fehlerBeimSpeichernDerDaten'));
									}
									else 
									{
										// Logeintrag schreiben
										$log->log($person->person_id,
											'Action',
											array('name'=>'New PreStudent','success'=>true,'message'=>'New PreStudent for '.$studienordnung->studiengangbezeichnung.' ('.$studienplan->orgform_kurzbz.') Studienplan '.$studienplaene[$i].' saved'),
											'bewerbung',
											'bewerbung',
											$studiengang->oe_kurzbz,
											'online');
									}
									if (defined('BEWERBERTOOL_KONTOBELASTUNG_BUCHUNGSTYP') && BEWERBERTOOL_KONTOBELASTUNG_BUCHUNGSTYP != '')
									{
											//TODO: Betrag aus dem Buchungstyp rausholen
											$konto = new konto();
											$konto->getBuchungstyp(null, BEWERBERTOOL_KONTOBELASTUNG_BUCHUNGSTYP);
											if (isset($konto->result[0]->standardbetrag))
												$standardbetrag = $konto->result[0]->standardbetrag;
											else 
												$standardbetrag = '';
											$konto->person_id = $person->person_id;
											$konto->studiengang_kz = $studienordnung->studiengang_kz;
											$konto->studiensemester_kurzbz = $std_semester;
											$konto->betrag = $standardbetrag;
											$konto->buchungsdatum = date('Y-m-d');
											$konto->buchungstext = BEWERBERTOOL_KONTOBELASTUNG_BUCHUNGSTYP;
											$konto->buchungstyp_kurzbz = BEWERBERTOOL_KONTOBELASTUNG_BUCHUNGSTYP;
											$konto->mahnspanne = 30;

											if (!$konto->save(true))
											{
													die($p->t('global/fehlerBeimSpeichernDerDaten'));
											}
									}
								}
							}

							//Email schicken
							echo '<p class="alert alert-success">'.sendMail($zugangscode, $email).'</p>';
							exit();
						}
					}
				} ?>

				<?php echo $message ?>
				<form method="post" action="<?php echo basename(__FILE__) ?>?method=registration#label_studiensemester_kurzbz" id="RegistrationLoginForm" name="RegistrationLoginForm" class="form-horizontal">
					<img style="width:150px;" class="center-block img-responsive" src="../../../skin/styles/<?php echo DEFAULT_STYLE ?>/logo.png">
					<h2 class="text-center">
						<?php echo $p->t('bewerbung/welcome') ?>
					</h2>
					<p class="infotext">
						<?php echo $p->t('bewerbung/einleitungstext') ?>
					</p>
					<div class="form-group">
						<label for="vorname" class="col-sm-3 control-label">
							<?php echo $p->t('global/vorname') ?>
						</label>
						<div class="col-sm-4">
							<input type="text" maxlength="32" name="vorname" id="vorname" value="<?php echo $vorname ?>" class="form-control">
						</div>
					</div>

					<div class="form-group">
						<label for="nachname" class="col-sm-3 control-label">
							<?php echo $p->t('global/nachname') ?>
						</label>
						<div class="col-sm-4">
							<input type="text" maxlength="64" name="nachname" id="nachname" value="<?php echo $nachname ?>" class="form-control">
						</div>
					</div>

					<div class="form-group">
						<label for="geburtsdatum" class="col-sm-3 control-label">
							<?php echo $p->t('global/geburtsdatum') ?>
						</label>
						<div class="col-sm-4">
							<input type="text" name="geb_datum" id="geburtsdatum"
								   value="<?php echo isset($geb_datum) && $geb_datum != ''? date('d.m.Y', strtotime($geb_datum)) : '' ?>"
								   class="form-control" placeholder="<?php echo $p->t('bewerbung/datumFormat') ?>">
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-3 control-label">
							<?php echo $p->t('global/geschlecht') ?>
						</label>
						<div class="col-sm-4 text-center">
							<label class="radio-inline">
								<input type="radio" name="geschlecht" id="geschlechtm" value="m" <?php echo $geschlecht == 'm' ? 'checked' : '' ?>>
								<?php echo $p->t('bewerbung/maennlich'); ?>
							</label>
							<label class="radio-inline">
								<input type="radio" name="geschlecht" id="geschlechtw" value="w" <?php echo $geschlecht == 'w' ? 'checked' : '' ?>>
								<?php echo $p->t('bewerbung/weiblich') ?>
							</label>
						</div>
					</div>

					<div class="form-group">
						<label for="email" class="col-sm-3 control-label">
							<?php echo $p->t('global/emailAdresse') ?>
						</label>
						<div class="col-sm-4">
							<input type="email" maxlength="128" name="email" id="email" value="<?php echo $email ?>" class="form-control">
						</div>
					</div>

					<?php if(BEWERBERTOOL_STUDIENAUSWAHL_ANZEIGEN): ?>
					<div class="form-group">
						<label for="studiensemester_kurzbz" class="col-sm-3 control-label" id="label_studiensemester_kurzbz">
							<?php echo $p->t('bewerbung/geplanterStudienbeginn') ?>
						</label>
						<div class="col-sm-4 dropdown">
							<select id="studiensemester_kurzbz" name="studiensemester_kurzbz" class="form-control" onChange="changeStudiensemester()">
								<!--<option value=""><?php echo $p->t('bewerbung/bitteAuswaehlen') ?></option>-->
								<?php
								$studiensemester_array = array();
								$studiensemester_array[] = $std_semester;

								foreach($stsem->studiensemester as $row): ?>
									<option value="<?php echo $row->studiensemester_kurzbz ?>"
										<?php echo $std_semester == $row->studiensemester_kurzbz ? 'selected' : '' ?>>
										<?php echo $row->bezeichnung.' ('.$p->t('bewerbung/ab').' '.$datum->formatDatum($stsem->convert_html_chars($row->start),'d.m.Y').')' ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">
							<?php echo $p->t('bewerbung/studienrichtung') ?>
						</label>
						<div class="col-sm-7" id="liste-studiengaenge">
							<?php
							
							//Umbau auf Studienpläne (ohne Modal für Orgformen) und Priorisierung
							
							// Zuerst sollen Bachelor- und Master-Studiengänge angezeigt werden, danach alle Anderen
							if ($sprache == DEFAULT_LANGUAGE)
							{
								$order = "	CASE tbl_studiengang.typ
												WHEN 'b' THEN 1
												WHEN 'm' THEN 2
												ELSE 3
											END, tbl_lgartcode.bezeichnung ASC, studiengangbezeichnung";
							}
							else
							{
								$order = "	CASE tbl_studiengang.typ
												WHEN 'b' THEN 1
												WHEN 'm' THEN 2
												ELSE 3
											END, tbl_lgartcode.bezeichnung ASC, studiengangbezeichnung_englisch";
							}
							
							$studienplan = getStudienplaeneForOnlinebewerbung($studiensemester_array, '1', '', $order); //@todo: ausbildungssemester dynamisch

							$lasttyp = '';
							$last_lgtyp = '';
							$bewerbungszeitraum = '';
							$typ_bezeichung = '';
							
							// Wenn es gar keine Studiengänge/Lehrgänge zum gewählten Studiensemester gibt, Info anzeigen
							if ($studienplan == '')
							{
								echo '<div class="alert alert-info">' . $p->t('bewerbung/keineStudienrichtungenFuerStudiensemesterZurAuswahl') . '</div>';
							}
							else 
							{
								foreach ($studienplan as $row)
								{
									if($lasttyp != $row->typ)
									{
										// Hack um typ_bezeichung mit Phrasen zu überschreiben
										if ($row->typ == 'l' && $p->t('bewerbung/hackTypBezeichnungLehrgeange') != '')
										{
											$typ_bezeichung = $p->t('bewerbung/hackTypBezeichnungLehrgeange');
										}
										elseif (($row->typ == 'b' && $p->t('bewerbung/hackTypBezeichnungBachelor') != ''))
										{
											$typ_bezeichung = $p->t('bewerbung/hackTypBezeichnungBachelor');
										}
										elseif (($row->typ == 'm' && $p->t('bewerbung/hackTypBezeichnungMaster') != ''))
										{
											$typ_bezeichung = $p->t('bewerbung/hackTypBezeichnungMaster');
										}
										else
										{
											$typ_bezeichung = $row->typ_bezeichnung;
										}
												
										if($lasttyp != '')
											echo '</div></div></div>';
													
										if(in_array($row->typ, $typen))
											$collapse = 'collapse in';
										else
											$collapse = 'collapse';
															
										echo '<div class="panel-group"><div class="panel panel-default">';
										echo '	<div class="panel-heading">
												<a href="#'.$row->typ_bezeichnung.'" data-toggle="collapse">
													<h4>'.$typ_bezeichung.'  <small><span class="glyphicon glyphicon-collapse-down"></span></small></h4>
												</a>
												</div>';
										echo '<div id="'.$row->typ_bezeichnung.'" class="panel-collapse '.$collapse.'">';
										if ($row->typ!='l')
											echo '<div name="checkboxInfoDiv" style="position: fixed; top: 0; z-index: 10; left: 5%; padding-right: 15px; padding-left: 15px; right: 5%;"></div>';
																
										$lasttyp = $row->typ;
									}
									
									if($last_lgtyp != $row->lehrgangsart && $row->lehrgangsart != '')
									{
										echo '<div class="panel-heading"><b>'.$p->t('bewerbung/lehrgangsArt/'.$row->lgartcode).'</b></div>';
										$last_lgtyp = $row->lehrgangsart;
									}
									
									$checked = '';
									$disabled = '';
									$style = '';
									
									// Checkboxen deaktivieren, wenn BEWERBERTOOL_MAX_STUDIENGAENGE gesetzt ist und mehr als oder genau BEWERBERTOOL_MAX_STUDIENGAENGE uebergeben werden.
									if(defined('BEWERBERTOOL_MAX_STUDIENGAENGE') && BEWERBERTOOL_MAX_STUDIENGAENGE != '')
									{
										if (count($studienplaeneBaMa) >= BEWERBERTOOL_MAX_STUDIENGAENGE && $row->typ != 'l')
											$disabled = 'disabled';
									}
	
									// Wenn es nur einen gueltigen Studienplan gibt, kommt der Name des Studiengangs aus dem Studienplan
									// Wenn der Name des Studiengangs aus dem Studienplan leer ist -> Fallback auf Studiengangsname vom Studiengang
									if($sprache != 'German' && $row->studiengangbezeichnung_englisch != '')
									{
										$stg_bezeichnung = $row->studiengangbezeichnung_englisch;
									}
									elseif ($row->studiengangbezeichnung != '')
									{
										$stg_bezeichnung = $row->studiengangbezeichnung;
									}
									else 
									{
										$studiengang = new studiengang($row->studiengang_kz);
										$stg_bezeichnung = $studiengang->bezeichnung_arr[$sprache];
									}
											
									$stg_bezeichnung .= ' | <i>'.$p->t('bewerbung/orgform/'.$row->orgform_kurzbz).' - '.$p->t('bewerbung/'.$row->sprache).'</i>';
											
									// Bewerbungsfristen laden
									$bewerbungszeitraum = getBewerbungszeitraum($row->studiengang_kz, $std_semester, $row->studienplan_id);
									$stg_bezeichnung .= ' '.$bewerbungszeitraum['infoDiv'];
									$fristAbgelaufen = $bewerbungszeitraum['frist_abgelaufen'];
																
									if(in_array($row->studienplan_id, $studienplaene))
									{
										$checked = 'checked';
										$disabled = '';
										$anchor = '#studiensemester_kurzbz'; // Seite springt nach Submit zum Studiensemester-DropDown
										$style = 'style="background-color: #D1ECF1"';
									}
									// Unterschiedliche Checkbox-Klassen um BEWERBERTOOL_MAX_STUDIENGAENGE richtig zu zählen
									if ($row->typ != 'l')
									{
										$class = 'checkbox_stg';
									}
									else
									{
										$class = 'checkbox_lg';
									}
									
									if (!$fristAbgelaufen)
									{
										echo '<div class="panel-body" '.$style.'>
												<div class="checkbox">
													<label>
														<input class="'.$class.'" id="checkbox_'.$row->studienplan_id.'" type="checkbox" name="studienplaene[]" value="'.$row->studienplan_id.'" '.$checked.' '.$disabled.'>
														'.$stg_bezeichnung;
									}
									else
									{
										echo '<div class="panel-body">
												<div class="checkbox disabled">
													<label class="text-muted">
														<input class="" type="checkbox" name="" value="" disabled>
														'.$stg_bezeichnung;
									}
	
									if (isset($prioritaeten[$row->studienplan_id]) && $prioritaeten[$row->studienplan_id] != '')
									{
										$prioValue = $prioritaeten[$row->studienplan_id];
										$badge = $p->t('bewerbung/prioritaet').': '.$prioritaeten[$row->studienplan_id];
									}
									elseif (in_array($row->studienplan_id, $studienplaene))
									{
										$prioValue = 1;
										if ($row->typ == 'l')
										{
											$prioValue = '';
										}
										$badge = '';
									}
									else
									{
										$prioValue = '';
										$badge = '';
									}
									echo '				<span class="badge" id="badge_'.$row->studienplan_id.'">'.$badge.'</span>';
									echo '				<input class="prioInput" type="hidden" id="prioritaet_'.$row->studienplan_id.'" name="prioritaet['.$row->studienplan_id.']" value="'.$prioValue.'">';
									echo '			</label>
												</div>
											</div>';
								}
								echo '</div></div></div>';
							}
							?>
						</div>
					</div>
					<?php endif; ?>

					<div class="form-group">
						<div class="col-xs-12 col-sm-7 col-sm-offset-3 col-md-7 col-md-offset-3 ">
							<div class="checkbox-inline">
								<input type="checkbox" name="zustimmung_datenuebermittlung" id="checkbox_zustimmung_datenuebermittlung" value="" required="required">
								<?php echo $p->t('bewerbung/zustimmungDatenuebermittlung') ?>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label for="captcha_code" class="col-sm-3 control-label">
							<img id="captcha" class="center-block img-responsive" src="<?php echo APP_ROOT ?>include/securimage/securimage_show.php" alt="CAPTCHA Image" />
							<a href="#" onclick="document.getElementById('captcha').src = '<?php echo APP_ROOT ?>include/securimage/securimage_show.php?' + Math.random(); return false">
								<?php echo $p->t('bewerbung/andereGrafik') ?>
							</a>
						</label>
						<div class="col-sm-4">
							<?php echo $p->t('bewerbung/captcha') ?>
							<input type="text" name="captcha_code" maxlength="6" id="captcha_code" class="form-control">
							<input type="hidden" name="zugangscode" value="<?php echo uniqid() ?>">
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-4 col-sm-offset-3">
							<input type="submit" name="submit_btn" value="<?php echo $p->t('bewerbung/abschicken') ?>" onclick="return checkRegistration() && validateEmail(document.RegistrationLoginForm.email.value) && submitPrio()" class="btn btn-primary">
						</div>
					</div>
				</form>
			<?php
			/**
			 * Maske zum erneuten Zusenden des Zugangscodes
			 *
			 */
			elseif($method == 'resendcode'): ?>
				<?php echo $message;

				$email = filter_input(INPUT_POST, 'email');
				$return = check_load_bewerbungen(trim($email));
				$resend_code = filter_input(INPUT_POST, 'resend_code');
				$orgform_kurzbz = filter_input(INPUT_GET, 'orgform_kurzbz');
				if($email != '')
				{
					if ($return)
					{
						if (isset($resend_code))
						{
							//Wenn es noch keinen Zugangscode für die Person gibt, generiere einen
							if($return->zugangscode == '')
							{
								$person = new person($return->person_id);

								$zugangscode = substr(md5(openssl_random_pseudo_bytes(20)), 0, 15);

								$person->zugangscode = $zugangscode;
								$person->updateamum = date('Y-m-d H:i:s');
								$person->updatevon = 'online';
								$person->new = false;

								if(!$person->save())
								{
									die($p->t('global/fehlerBeimSpeichernDerDaten'));
								}
								else 
								{
									// Logeintrag schreiben
									$log->log($return->person_id,
										'Action',
										array('name'=>'Access code generated','success'=>true,'message'=>'Access code has been generated because none was present'),
										'bewerbung',
										'bewerbung',
										null,
										'online'
									);
								}
							}
							// Beim erneuten Zuschicken des Zugangscodes, wird aus Sicherheitsgründen ein Neuer generiert
							if($return)
							{
								$person = new person($return->person_id);
								
								$zugangscode = substr(md5(openssl_random_pseudo_bytes(20)), 0, 15);
								
								$person->zugangscode = $zugangscode;
								$person->updateamum = date('Y-m-d H:i:s');
								$person->updatevon = 'online';
								$person->new = false;
								
								if(!$person->save())
								{
									die($p->t('global/fehlerBeimSpeichernDerDaten'));
								}
								else
								{
									// Logeintrag schreiben
									$log->log($return->person_id,
										'Action',
										array('name'=>'Access code sent','success'=>true,'message'=>'User requested his access code. New code was generated and sent.'),
										'bewerbung',
										'bewerbung',
										null,
										'online'
										);
									echo '<p class="alert alert-success"><button type="button" class="close" data-dismiss="alert">x</button>'.sendMail($zugangscode, $email, $return->person_id).'</p>';
								}
							}
							else
								echo '<p class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">x</button>'.$p->t('bewerbung/keinCodeVorhanden').'</p>';
						}
						else
							$message = '<p class="alert alert-danger" id="danger-alert">'.$p->t('global/fehleraufgetreten').'</p>';
					}
					else
						echo '<p class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">x</button>'.$p->t('bewerbung/keinCodeVorhanden').'</p>';
				}
				?>
				<div class="row">
					<div class="col-sm-8 col-sm-offset-2">
						<form method="post" action="<?php echo basename(__FILE__) ?>?method=resendcode" id="ResendCodeForm" name="ResendCodeForm" class="form-horizontal">
							<div style="border-bottom: 1px solid #eee; margin-bottom: 30px;" class="row">
								<div class="col-md-4">
									<div style="text-align: center;">
										<img style="margin: 30px 10px;" src="../../../skin/styles/<?php echo DEFAULT_STYLE ?>/logo.png"/>
									</div>
								</div>
								<div style="text-align: center;" class="col-md-8">
									<h1 style="margin: 30px 10px;"><?php echo $p->t('bewerbung/welcome') ?></h1>
								</div>
							</div>
							<p class="text-center"><?php echo $p->t('bewerbung/codeZuschickenAnleitung') ?></p><br>
							<div class="form-group">
								<label for="email" class="col-sm-4 control-label">
									<?php echo $p->t('global/emailAdresse') ?>
								</label>
								<div class="col-sm-8">
									<div class="input-group">
										<input type="email" maxlength="128" name="email" id="email" value="<?php echo $email ?>" class="form-control" autofocus="autofocus">
										<span class="input-group-btn">
											<button type="submit" class="btn btn-primary" name="resend_code" value="<?php echo $p->t('bewerbung/codeZuschicken') ?>" onclick="return validateEmail(document.ResendCodeForm.email.value)">
												<?php echo $p->t('bewerbung/codeZuschicken') ?>
											</button>
										</span>
									</div>
								</div>
							</div>
							<br><br><br><br><br><br><br>
							<br><br><br><br><br><br><br>
							<br><br><br><br><br><br><br>
							<?php
							if(isset($errormsg))
							{
								echo $errormsg;
							}
							?>
						</form>
					</div>
				</div>
			<?php else: ?>
				<?php echo $message ?>
				<div class="row">
					<!--<div class="col-xs-10 col-xs-offset-1 col-sm-6 col-sm-offset-3">-->
					<div class="col-sm-8 col-sm-offset-2">
						<form action="<?php echo basename(__FILE__);?>" method="POST" id="lp" class="form-horizontal">
							<div style="border-bottom: 1px solid #eee; margin-bottom: 30px;" class="row">
								<div class="col-md-4">
									<div style="text-align: center;">
										<img style="margin: 30px 10px;" src="../../../skin/styles/<?php echo DEFAULT_STYLE ?>/logo.png"/>
									</div>
								</div>
								<div style="text-align: center;" class="col-md-8">
									<h1 style="margin: 30px 10px;"><?php echo $p->t('bewerbung/welcome'); ?></h1>
								</div>
							</div>
							<div class="panel panel-info">
								<div class="panel-heading text-center">
									<h3 class="panel-title"><?php echo $p->t('bewerbung/sieHabenNochKeinenZugangscode') ?></h3>
								</div>
								<div class="panel-body text-center">
									<br>
									<a class="btn btn-primary btn-lg" href="<?php echo basename(__FILE__) ?>?
										method=registration
										&stg_kz=<?php echo filter_input(INPUT_GET, 'stg_kz') ?>
										&orgform_kurzbz=<?php echo filter_input(INPUT_GET, 'orgform_kurzbz') ?>" role="button">
											<?php echo $p->t('bewerbung/hierUnverbindlichAnmelden') ?>
									</a>
									<br><br>
								</div>
							</div>
							<div class="panel panel-info">
								<div class="panel-heading text-center">
									<h3 class="panel-title"><?php echo $p->t('bewerbung/habenSieBereitsEinenZugangscode') ?></h3>
								</div>
								<div class="panel-body text-center">
									<p><?php echo $p->t('bewerbung/dannHierEinloggen') ?></p>
									<div class="form-group">
										<label for="mailadresse" class="col-sm-3 control-label">
											<?php echo $p->t('global/emailAdresse') ?>
										</label>
										<div class="col-sm-8">
											<input class="form-control" 
													type="text" 
													placeholder="<?php echo $p->t('global/emailAdresse') ?>" 
													name="mailadresse"
													autofocus="autofocus" 
													value="<?php echo $emailAdresseGet ?>">
										</div>
									</div>
									<div class="form-group">
										<label for="userid" class="col-sm-3 control-label">
											<?php echo $p->t('bewerbung/zugangscode') ?>
										</label>
										<div class="col-sm-8">
											<input class="form-control" 
													type="text" 
													placeholder="<?php echo $p->t('bewerbung/zugangscode') ?>" 
													name="userid" 
													autofocus="autofocus" 
													value="<?php echo $codeGet ?>">
										</div>
									</div>
									<div class="form-group">
										<div class="col-sm-offset-2 col-sm-8">
											<button type="submit" class="btn btn-primary" name="submit_btn"><?php echo $p->t('bewerbung/login') ?></button>
										</div>
									</div>
									<div class="col-sm-4 col-sm-offset-4">
										<a href="<?php echo basename(__FILE__) ?>?method=resendcode"><?php echo $p->t('bewerbung/zugangscodeVergessen') ?></a>
									</div>
								</div>
							</div>
							<div class="panel panel-info">
								<div class="panel-heading text-center">
									<h3 class="panel-title"><?php echo $p->t('bewerbung/studierenOderArbeitenSieBereits') ?></h3>
								</div>
								<div class="panel-body text-center">
									<p class="text-center"><?php echo $p->t('bewerbung/dannHiermitAccountEinloggen') ?></p>
								<div class="form-group">
									<label for="username" class="col-sm-3 control-label">
										<?php echo $p->t('global/username') ?>
									</label>
									<div class="col-sm-8">
										<input class="form-control" type="text" placeholder="<?php echo $p->t('global/username') ?>" name="username">
									</div>
								</div>
								<div class="form-group">
									<label for="password" class="col-sm-3 control-label">
										<?php echo $p->t('global/passwort') ?>
									</label>
									<div class="col-sm-8">
										<input class="form-control" type="password" placeholder="<?php echo $p->t('global/passwort') ?>" name="password">
									</div>
								</div>
								<div class="form-group">
									<span class="col-sm-4 col-sm-offset-4">
										<button class="btn btn-primary" type="submit" name="submit_btn">
											<?php echo $p->t('bewerbung/login') ?>
										</button>
									</span>
								</div>
							  </div>
							</div>
							<br><br><br><br><br><br>
							<div style="text-align:center; color:gray;"><center><?php echo $p->t('bewerbung/footerText')?></center></div>
							<br><br><br><br><br><br><br>
							<br><br><br><br><br><br><br>
							<br><br><br><br><br><br><br>
							<?php
							if(isset($errormsg))
							{
								echo $errormsg;
							}
							?>
						</form>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<?php
		//if(BEWERBERTOOL_STUDIENAUSWAHL_ANZEIGEN)
			//require('views/modal_sprache_orgform.php');
		?>
		<script type="text/javascript" src="../../../vendor/jquery/jqueryV1/jquery-1.12.4.min.js"></script>
		<script type="text/javascript" src="../../../vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
		<script type="text/javascript">

		function changeSprache(sprache)
		{
			var method = '<?php echo $db->convert_html_chars($method);?>';

			window.location.href = "registration.php?sprache=" + sprache + "&method=" + method + "&stg_kz=<?php echo filter_input(INPUT_GET, 'stg_kz') ?>&orgform_kurzbz=<?php echo filter_input(INPUT_GET, 'orgform_kurzbz') ?>";
		}

		function changeStudiensemester()
		{
			if (document.RegistrationLoginForm.captcha_code.value != '')
				document.RegistrationLoginForm.captcha_code.value = '';
			document.RegistrationLoginForm.submit();
		}

		function checkRegistration()
		{
			if(document.RegistrationLoginForm.vorname.value == "")
			{
				alert("<?php echo $p->t('bewerbung/bitteVornameAngeben')?>");
				return false;
			}
			if(document.RegistrationLoginForm.nachname.value == "")
			{
				alert("<?php echo $p->t('bewerbung/bitteNachnameAngeben')?>");
				return false;
			}
			if(document.RegistrationLoginForm.geb_datum.value == "")
			{
				alert("<?php echo $p->t('bewerbung/bitteGeburtsdatumEintragen')?>");
				return false;
			}
			else
			{
				var gebDat = document.RegistrationLoginForm.geburtsdatum.value;
				gebDat = gebDat.split(".");

				if(gebDat.length !== 3)
				{
					alert("<?php echo $p->t('bewerbung/bitteGeburtsdatumEintragen')?>");
					return false;
				}

				if(gebDat[0].length !==2 && gebDat[1].length !== 2 && gebDat[2].length !== 4)
				{
					alert("<?php echo $p->t('bewerbung/bitteGeburtsdatumEintragen')?>");
					return false;
				}

				var date = new Date(gebDat[2], gebDat[1]-1, gebDat[0]);

				gebDat[0] = parseInt(gebDat[0], 10);
				gebDat[1] = parseInt(gebDat[1], 10);
				gebDat[2] = parseInt(gebDat[2], 10);

				if(!(date.getFullYear() === gebDat[2] && (date.getMonth()+1) === gebDat[1] && date.getDate() === gebDat[0]))
				{
					alert("<?php echo $p->t('bewerbung/bitteGeburtsdatumEintragen')?>");
					return false;
				}

				var heute = new Date();
				var jahr = heute.getFullYear();

				if(date.getFullYear()>=jahr)
				{
					alert("<?php echo $p->t('bewerbung/bitteGeburtsdatumEintragen')?>");
					return false;
				}


			}
			if((document.getElementById('geschlechtm').checked == false) && (document.getElementById('geschlechtw').checked == false))
			{
				alert("<?php echo $p->t('bewerbung/bitteGeschlechtWaehlen')?>");
				return false;
			}
			if(document.RegistrationLoginForm.email.value == "")
			{
				alert("<?php echo $p->t('bewerbung/bitteEmailAngeben')?>");
				return false;
			}
			<?php if(BEWERBERTOOL_STUDIENAUSWAHL_ANZEIGEN): ?>
			if(document.RegistrationLoginForm.studiensemester_kurzbz.value == "")
			{
				alert("<?php echo $p->t('bewerbung/bitteStudienbeginnWaehlen')?>");
				return false;
			}
			<?php endif; ?>
			<?php if(BEWERBERTOOL_SHOW_ZUSTIMMUNGSERKLAERUNG_REGISTRATION): ?>
			if(document.getElementById('checkbox_zustimmung_datenuebermittlung').checked == false)
			{
				alert("<?php echo $p->t('bewerbung/bitteDatenuebermittlungZustimmen')?>");
				return false;
			}
			<?php endif; ?>
			return true;
		}

		function validateEmail(email)
		{
			//var email = document.ResendCodeForm.email.value;
			var re = /^([\w-+]+(?:\.[\w-+]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
			if(re.test(email)===false)
			{
				alert("<?php echo $p->t('bewerbung/bitteEmailAngeben')?>");
				return false;
			}
			else
				return true;
		}
		
		$(function()
		{

			$('#sprache-dropdown a').on('click', function() {

				var sprache = $(this).attr('data-sprache');
				changeSprache(sprache);
			});

			//Scrollt nach oben, wenn ein Fehler-Div angezeigt wird
			if($("#danger-alert").length != 0) 
			{
				window.scrollTo(0, 0);
			}

			//Zeigt das Prio-Badge, wenn mehr als ein Bachelor- oder Master-Studiengang gewählt wird
			// Und zaehlt in der Reihenfolge hoch, in der die Checkbox angeklickt wird
			$("input[type=checkbox][class=checkbox_stg]").click(function()
			{
				// Alle angeklickten Checkboxen
				var checkedInputs = $("input[type=checkbox][class=checkbox_stg]:checked");
				var studienplanId = $(this).attr("value");
				if ($(this).is(':checked'))
				{
					// Hintergrundfarbe anpassen, wenn angeklickt
					$(this).parents(".panel-body").css("background-color", "#D1ECF1");
					// Prio immer hochzählen
					$("#prioritaet_"+studienplanId).val(checkedInputs.length);
					// Badge nur anzeigen, wenn mehr als 1 augewählt
					if(checkedInputs.length > 1)
					{
						$("input[type=checkbox][class=checkbox_stg]:checked").each(function ()
						{
							var studienplanId = $(this).attr("value");
							var prio = $("#prioritaet_"+studienplanId).val();
							$("#badge_"+studienplanId).html('<?php echo $p->t('bewerbung/prioritaet'); ?>: '+prio);
						});
					}
				}
				else
				{
					// Hintergrundfarbe anpassen, wenn angeklickt
					$(this).parents(".panel-body").css("background-color", "unset");
					var oldValue =  $("#prioritaet_"+studienplanId).val();
					if(checkedInputs.length >= 0)
						$("#badge_"+studienplanId).empty();
					
					$("#prioritaet_"+studienplanId).val('');

					$("input[type=checkbox][class=checkbox_stg]").each(function ()
					{
						var studienplanId = $(this).attr("value");
						var currentVal = $("#prioritaet_"+studienplanId).val();
						if (currentVal > oldValue)
						{
							var newVal = parseInt(currentVal) - 1;
							$("#badge_"+studienplanId).html('<?php echo $p->t('bewerbung/prioritaet'); ?>: '+newVal);
							$("#prioritaet_"+studienplanId).val(newVal);
						}
					});
				}
				
				//Badge immer hochzählen
				//var studienplanId = $(this).attr("value");
				//$("#badge_"+studienplanId).html(checkedInputs.length);
				//$("#prioritaet_"+studienplanId).val(checkedInputs.length);
				
				/*var InputId = "prioInput_"+$(this).attr("value");
				$("#"+InputId).val(checkedInputs.length);

				// Alle angeklickten Checkboxen
				var checkedInputs = $("input[type=checkbox][class=checkbox_stg]:checked");
				// Alle nicht angeklickten Checkboxen
				var uncheckedInputs = $("input[type=checkbox][class=checkbox_stg]").not(":checked");
				
				// Value der angeklickten Prio auf Anzahl ausgewählte setzen (Erste angeklickte ist 1, zweiter ist 2, usw.)
				var InputId = "prioInput_"+$(this).attr("value");
				$("#"+InputId).val(checkedInputs.length);

				// Value der nicht angeklickten Prio auf 0 setzen und div ausblenden
				$("#"+InputId).val("0");*/
				
				// Badge nur anzeigen, wenn mehr als ein Studiengang angeklickt wird
				/*if(checkedInputs.length > 1)
				{
					checkedInputs.each(function ()
					{
						var divId = $(this).attr("value");
						$("#prioDiv_"+divId).show();
					});
				}
				else
				{
					$(".prioDiv").hide();
				}*/
				
				/*
				$("input[type=checkbox][class=checkbox_stg]").not(":checked").attr("disabled",bol);
				if ($("input[type=checkbox][class=checkbox_stg]:checked").length >= <?php echo BEWERBERTOOL_MAX_STUDIENGAENGE; ?>)
				{
					$("div[name=checkboxInfoDiv]").html("<?php echo $p->t('bewerbung/sieKoennenMaximalXStudiengaengeWaehlen',  array(BEWERBERTOOL_MAX_STUDIENGAENGE)) ?>");
					$("div[name=checkboxInfoDiv]").addClass("alert alert-warning");
				}
				else
				{
					$("div[name=checkboxInfoDiv]").html("");
					$("div[name=checkboxInfoDiv]").removeClass();
				}*/
			});

			<?php if (defined('BEWERBERTOOL_MAX_STUDIENGAENGE') && BEWERBERTOOL_MAX_STUDIENGAENGE != ''): ?>
				//Disabled die Checkboxen, wenn mehr als BEWERBERTOOL_MAX_STUDIENGAENGE Studiengaenge gewaehlt werden
				$("input[type=checkbox][class=checkbox_stg]").click(function()
				{
					var bol = $("input[type=checkbox][class=checkbox_stg]:checked").length >= <?php echo BEWERBERTOOL_MAX_STUDIENGAENGE; ?>;
					$("input[type=checkbox][class=checkbox_stg]").not(":checked").attr("disabled",bol);
					if ($("input[type=checkbox][class=checkbox_stg]:checked").length >= <?php echo BEWERBERTOOL_MAX_STUDIENGAENGE; ?>)
					{
						$("div[name=checkboxInfoDiv]").html("<?php echo $p->t('bewerbung/sieKoennenMaximalXStudiengaengeWaehlen',  array(BEWERBERTOOL_MAX_STUDIENGAENGE)) ?>");
						$("div[name=checkboxInfoDiv]").addClass("alert alert-warning alert-dismissible");
					}
					else
					{
						$("div[name=checkboxInfoDiv]").html("");
						$("div[name=checkboxInfoDiv]").removeClass();
					}
				});
			<?php endif; ?>
		});

		window.setTimeout(function() {
			$("#success-alert").fadeTo(500, 0).slideUp(500, function(){
				$(this).remove();
			});
		}, 1500);

		</script>

	</body>
</html>

<?php
function sendMail($zugangscode, $email, $person_id=null)
{
	global $p, $vorname, $nachname, $geschlecht;

	if($person_id!='')
	{
		$person = new person();
		$person->load($person_id);
		$vorname = $person->vorname;
		$nachname  = $person->nachname;
		$geschlecht = $person->geschlecht;
	}
	if($geschlecht=='m')
		$anrede=$p->t('bewerbung/anredeMaennlich');
	else
		$anrede=$p->t('bewerbung/anredeWeiblich');

	$mail = new mail($email, 'no-reply', $p->t('bewerbung/registration'), $p->t('bewerbung/mailtextHtml'));
	$text = $p->t('bewerbung/mailtext',array($vorname, $nachname, $zugangscode, $anrede, $email));
	$mail->addEmbeddedImage('../../../skin/images/sancho/sancho_header_DEFAULT.jpg', 'image/jpg', 'header_image', 'sancho_header');
	$mail->addEmbeddedImage('../../../skin/images/sancho/sancho_footer.jpg', 'image/jpg', 'footer_image', 'sancho_footer');
	$mail->setHTMLContent($text);
	if(!$mail->send())
		$msg = '<span class="error">'.$p->t('bewerbung/fehlerBeimSenden').'</span><br /><a href='.$_SERVER['PHP_SELF'].'?method=registration>'.$p->t('bewerbung/zurueckZurAnmeldung').'</a>';
	else
		$msg = $p->t('bewerbung/emailgesendetan', array($email))."<br><br><a href=".$_SERVER['PHP_SELF'].">".$p->t('bewerbung/zurueckZurAnmeldung')."</a>";

	if(defined('MAIL_DEBUG') && MAIL_DEBUG!='')
		$msg .= "<br><br>Zugangscode: ".$zugangscode;

	return $msg;
}
function resendMail($zugangscode, $email, $person_id=null)
{
	global $p, $vorname, $nachname, $geschlecht;
	if($person_id!='')
	{
		$person = new person();
		$person->load($person_id);
		$vorname = $person->vorname;
		$nachname  = $person->nachname;
		$geschlecht = $person->geschlecht;
	}
	if($geschlecht=='m')
		$anrede=$p->t('bewerbung/anredeMaennlich');
	else
		$anrede=$p->t('bewerbung/anredeWeiblich');

	$mail = new mail($email, 'no-reply', $p->t('bewerbung/registration'), $p->t('bewerbung/mailtextHtml'));
	$text = $p->t('bewerbung/mailtext',array($vorname, $nachname, $zugangscode, $anrede, $email));
	$mail->addEmbeddedImage('../../../skin/images/sancho/sancho_header_DEFAULT.jpg', 'image/jpg', 'header_image', 'sancho_header');
	$mail->addEmbeddedImage('../../../skin/images/sancho/sancho_footer.jpg', 'image/jpg', 'footer_image', 'sancho_footer');
	$mail->setHTMLContent($text);
	if(!$mail->send())
		$msg= '<span class="error">'.$p->t('bewerbung/fehlerBeimSenden').'</span><br /><a href='.$_SERVER['PHP_SELF'].'?method=registration>'.$p->t('bewerbung/zurueckZurAnmeldung').'</a>';
	else
		$msg= $p->t('bewerbung/emailgesendetan', array($email))."<br><br><a href=".$_SERVER['PHP_SELF'].">".$p->t('bewerbung/zurueckZurAnmeldung')."</a>";

	return $msg;
}
