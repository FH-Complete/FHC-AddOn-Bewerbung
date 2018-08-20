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
$username = trim(filter_input(INPUT_POST, 'username'));
$password = trim(filter_input(INPUT_POST, 'password'));
$code = trim(filter_input(INPUT_GET, 'code'));

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

// Login gestartet
if ($userid)
{
	$person = new person();

	$person_id = $person->checkZugangscodePerson($userid);

	//Zugangscode wird überprüft
	if($person_id != false)
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

		header('Location: bewerbung.php');
		exit;
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

				header('Location: bewerbung.php');
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
				$geschlecht = filter_input(INPUT_POST, 'geschlecht');
				$email = filter_input(INPUT_POST, 'email');
				$anmerkungen = filter_input(INPUT_POST, 'anmerkung', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
 				$orgform = filter_input(INPUT_POST, 'orgform', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
				$orgform_alt = filter_input(INPUT_POST, 'orgform_alt', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
				$orgform_kurzbz = filter_input(INPUT_GET, 'orgform_kurzbz');

				if(BEWERBERTOOL_STUDIENAUSWAHL_ANZEIGEN)
				{
					$studiengaenge = filter_input(INPUT_POST, 'studiengaenge', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
					$std_semester = filter_input(INPUT_POST, 'studiensemester_kurzbz');

					// Wenn kein Studiensemester uebergeben wird, das Erste aus getStudiensemesterOnlinebewerbung nehmen, wenn vorhanden
					if ($std_semester == '' && isset($stsem->studiensemester[0]))
						$std_semester = $stsem->studiensemester[0]->studiensemester_kurzbz;

					$stg_auswahl = filter_input(INPUT_POST, 'stg');

					if(!is_array($studiengaenge))
					{
						$studiengaenge = array();
					}
					if(filter_input(INPUT_GET, 'stg_kz')!='')
						$studiengaenge[] = filter_input(INPUT_GET, 'stg_kz'); //Wenn die stg_kz als Parameter von der Homepage uebergeben wird, wird dieser vorausgewaehlt
				}
				else
				{
					if (isset($stsem->studiensemester[0]))
						$std_semester = $stsem->studiensemester[0]->studiensemester_kurzbz;
					else
						$std_semester = null;
				}

				$studiengaengeBaMa = array(); // Nur Bachelor oder Master Studiengaenge
				$studiengaengeBaMa = array_filter($studiengaenge, function ($v)
				{
					return $v > 0 && $v < 10000;
				});

				if($geb_datum)
				{
					$geb_datum = date('Y-m-d', strtotime($geb_datum));
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

							$zugangscode = substr(md5(openssl_random_pseudo_bytes(20)), 0, 10);

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
						if (isset($resend_code))
						{
							$zugangscode = $return->zugangscode;
							echo '<p class="alert alert-success">'.resendMail($zugangscode, $email).'</p>';
							// Logeintrag schreiben
							$log->log($return->person_id,
								'Action',
								array('name'=>'Access code requested','success'=>true,'message'=>'User requested for access code'),
								'bewerbung',
								'bewerbung',
								null,
								'online'
							);
							exit();
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
						elseif (BEWERBERTOOL_STUDIENAUSWAHL_ANZEIGEN && count($studiengaenge)==0)
						{
							$message = '<p class="bg-danger padding-10">'.$p->t('bewerbung/bitteStudienrichtungWaehlen').'</p>';
						}
						elseif (BEWERBERTOOL_STUDIENAUSWAHL_ANZEIGEN && defined('BEWERBERTOOL_MAX_STUDIENGAENGE') && BEWERBERTOOL_MAX_STUDIENGAENGE != '' && count($studiengaengeBaMa) > BEWERBERTOOL_MAX_STUDIENGAENGE)
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

							$zugangscode = substr(md5(openssl_random_pseudo_bytes(20)), 0, 10);

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

							if(BEWERBERTOOL_STUDIENAUSWAHL_ANZEIGEN && count($studiengaenge) < ANZAHL_PREINTERESSENT)
							{
								$anzStg = count($studiengaenge);

								// Prestudenten anlegen
								for($i = 0; $i<$anzStg; $i++)
								{
									$prestudent = new prestudent();
									$prestudent->person_id = $person->person_id;
									$prestudent->studiengang_kz = $studiengaenge[$i];
									$prestudent->aufmerksamdurch_kurzbz = 'k.A.';
									$prestudent->insertamum = date('Y-m-d H:i:s');
									$prestudent->insertvon = 'online';
									$prestudent->updateamum = date('Y-m-d H:i:s');
									$prestudent->reihungstestangetreten = false;
									$prestudent->new = true;

									if(!$prestudent->save())
									{
										die($p->t('global/fehlerBeimSpeichernDerDaten'));
									}

									// Richtigen Studienplan ermitteln
									$studienplan = new studienplan();
									$studienplan->getStudienplaeneFromSem($studiengaenge[$i], $std_semester, '1', $orgform[$studiengaenge[$i]]);

									// Wenn kein passender Studienplan gefunden wird, wird er NULL gesetzt
									if (isset($studienplan->result[0]))
										$studienplan_id = $studienplan->result[0]->studienplan_id;
									else
										$studienplan_id = '';
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
									$prestudent_status->anmerkung_status = $anmerkungen[$studiengaenge[$i]];
									$prestudent_status->orgform_kurzbz = $orgform[$studiengaenge[$i]];
									$prestudent_status->studienplan_id = $studienplan_id;

									if(!$prestudent_status->save_rolle())
									{
										die($p->t('global/fehlerBeimSpeichernDerDaten'));
									}
									else 
									{
										// Logeintrag schreiben
										$log->log($person->person_id,
											'Action',
											array('name'=>'New PreStudent','success'=>true,'message'=>'New PreStudent for '.$studiengaenge_arr[$studiengaenge[$i]]['bezeichnung'].' ('.$orgform[$studiengaenge[$i]].') Studienplan '.$studienplan_id.' saved'),
											'bewerbung',
											'bewerbung',
											$studiengaenge_arr[$studiengaenge[$i]]['oe_kurzbz'],
											'online');
									}
									if (defined('BEWERBERTOOL_KONTOBELASTUNG_BUCHUNGSTYP') && BEWERBERTOOL_KONTOBELASTUNG_BUCHUNGSTYP != '')
									{
											//TODO: Betrag aus dem Buchungstyp rausholen
											$konto = new konto();
											$konto->person_id = $person->person_id;
											$konto->studiengang_kz = $studiengaenge[$i];
											$konto->studiensemester_kurzbz = $std_semester;
											$konto->betrag = -650;
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
							else
							{
								// Preinteressent anlegen
								$timestamp = time();
								$preInteressent = new preinteressent();
								$preInteressent->person_id = $person->person_id;
								$preInteressent->studiensemester_kurzbz = $std_semester;
								$preInteressent->aufmerksamdurch_kurzbz = 'k.A.';
								$preInteressent->kontaktmedium_kurzbz = 'bewerbungonline';
								$preInteressent->erfassungsdatum = date('Y-m-d', $timestamp);
								$preInteressent->insertamum = date('Y-m-d H:i:s');
								$preInteressent->insertvon = 'online';
								$preInteressent->updateamum = date('Y-m-d H:i:s');
								$preInteressent->updatevon ='online';
								$preInteressent->new = true;

								if(!$preInteressent->save())
								{
									die($p->t('global/fehlerBeimSpeichernDerDaten'));
								}

								if(BEWERBERTOOL_STUDIENAUSWAHL_ANZEIGEN)
								{
									// Zuordnungen anlegen
									$anzStg = count($studiengaenge);
									for($i = 0; $i<$anzStg; $i++)
									{
										$preIntZuordnung = new preinteressent();
										$preIntZuordnung->preinteressent_id = $preInteressent->preinteressent_id;
										$preIntZuordnung->studiengang_kz = $studiengaenge[$i];
										$preIntZuordnung->prioritaet = '1';
										$preIntZuordnung->insertamum = date('Y-m-d H:i:s');
										$preIntZuordnung->insertvon = 'online';
										$preIntZuordnung->updateamum = date('Y-m-d H:i:s');
										$preIntZuordnung->updatevon = 'online';
										$preIntZuordnung->new = true;

										if(!$preIntZuordnung->saveZuordnung())
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
				<form method="post" action="<?php echo basename(__FILE__) ?>?method=registration" id="RegistrationLoginForm" name="RegistrationLoginForm" class="form-horizontal">
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
						<label for="studiensemester_kurzbz" class="col-sm-3 control-label">
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
							// Zuerst sollen Bachelor- und Master-Studiengänge angezeigt werden, danach alle Anderen
							if ($sprache == DEFAULT_LANGUAGE)
								$order = "	CASE tbl_studiengang.typ
												WHEN 'b' THEN 1
												WHEN 'm' THEN 2
												ELSE 3
											END, tbl_lgartcode.bezeichnung ASC, studiengangbezeichnung";
							else
								$order = "	CASE tbl_studiengang.typ
												WHEN 'b' THEN 1
												WHEN 'm' THEN 2
												ELSE 3
											END, tbl_lgartcode.bezeichnung ASC, studiengangbezeichnung_englisch";
							$stg = new studiengang();
							$stg->getAllForOnlinebewerbung($order);

							$stghlp = new studiengang();
							$stghlp->getLehrgangstyp();
							$lgtyparr = array();
							foreach($stghlp->result as $row)
								$lgtyparr[$row->lgartcode]=$row->bezeichnung;

							//In der Stg-Auswahl verwendete Typen
							$typen = array();
							$anzStg = count($studiengaenge);

							for($i = 0; $i<$anzStg; $i++)
							{
								$stgtypen = new studiengang();
								$stgtypen->load($studiengaenge[$i]);
								$typen[] .= $stgtypen->typ;
							}

							$lasttyp = '';
							$last_lgtyp = '';
							$bewerbungszeitraum = '';
							$typ_bezeichung = '';

							foreach($stg->result as $result)
							{
								if($lasttyp != $result->typ)
								{
									// Hack um typ_bezeichung mit Phrasen zu überschreiben
									if ($result->typ == 'l' && $p->t('bewerbung/hackTypBezeichnungLehrgeange') != '')
										$typ_bezeichung = $p->t('bewerbung/hackTypBezeichnungLehrgeange');
									else
										$typ_bezeichung = $result->typ_bezeichnung;
											
									if($lasttyp != '')
										echo '</div></div></div>';

									if(in_array($result->typ, $typen))
										$collapse = 'collapse in';
									else
										$collapse = 'collapse';
									echo '<div class="panel-group"><div class="panel panel-default">';
									echo '<div class="panel-heading">
											<a href="#'.$result->typ_bezeichnung.'" data-toggle="collapse">
												<h4>'.$typ_bezeichung.'  <small><span class="glyphicon glyphicon-collapse-down"></span></small></h4>
											</a>
											</div>';
									echo '<div id="'.$result->typ_bezeichnung.'" class="panel-collapse '.$collapse.'">';
									if ($result->typ!='l')
										echo '<div name="checkboxInfoDiv"></div>';
									$lasttyp = $result->typ;
								}
								if($last_lgtyp != $result->lehrgangsart && $result->lehrgangsart != '')
								{
									echo '<div class="panel-heading"><b>'.$p->t('bewerbung/lehrgangsArt/'.$result->lgartcode).'</b></div>';
									$last_lgtyp = $result->lehrgangsart;
								}

								$checked = '';
								$disabled = '';

								// Checkboxen deaktivieren, wenn BEWERBERTOOL_MAX_STUDIENGAENGE gesetzt ist und mehr als oder genau BEWERBERTOOL_MAX_STUDIENGAENGE uebergeben werden.
								if(defined('BEWERBERTOOL_MAX_STUDIENGAENGE') && BEWERBERTOOL_MAX_STUDIENGAENGE != '')
								{
									if (count($studiengaengeBaMa) >= BEWERBERTOOL_MAX_STUDIENGAENGE && $result->typ!='l')
										$disabled = 'disabled';
								}

								$studienplan = getStudienplaeneForOnlinebewerbung($result->studiengang_kz, $studiensemester_array, '1', ''); //@todo: ausbildungssemester dynamisch

								$orgformen_sprachen = array();
								$modal = false;
								$fristAbgelaufen = false;
								$class = '';
								$stg_bezeichnung = '';

								// @todo: Was machen wir mit den Lehrgängen? Die sollten auch einen gültigen Studienplan haben, haben ihn aber nicht immer.
								// Angezeigt werden müssen sie auf jeden Fall. Soll der Name dort immer aus dem Studiengang oder auch aus dem Studienplan kommen?
								// Speichern wir sie auch ohne gültigen Studienplan?
								if($studienplan != '')
								{
									foreach ($studienplan as $row)
									{
										$orgformen_sprachen[$row->orgform_kurzbz] = $row->sprache;
									}
									if(count($orgformen_sprachen) > 1)
									{
										$modal = true;

										// Wenn mehr als 1 gueltiger Studienplan gefunden wird, Bezeichnung des Studiengangs laden
										$bezeichnung_studiengang = new studiengang($result->studiengang_kz);
										if($sprache != 'German' && $bezeichnung_studiengang->english != '')
											$stg_bezeichnung = $bezeichnung_studiengang->english;
										else
											$stg_bezeichnung = $bezeichnung_studiengang->bezeichnung;
									}
									elseif ($result->typ != 'l' && !isset($lgtyparr[$result->lgartcode]))
									{
										// Wenn es nur einen gueltigen Studienplan gibt, kommt der Name des Studiengangs aus dem Studienplan
										if($sprache != 'German' && $studienplan[0]->studiengangbezeichnung_englisch != '')
											$stg_bezeichnung = $studienplan[0]->studiengangbezeichnung_englisch;
										else
											$stg_bezeichnung = $studienplan[0]->studiengangbezeichnung;

										$stg_bezeichnung .= ' | <i>'.$p->t('bewerbung/orgform/'.$studienplan[0]->orgform_kurzbz).' - '.$p->t('bewerbung/'.$studienplan[0]->sprache).'</i>';

										// Bewerbungsfristen laden
										$bewerbungszeitraum = getBewerbungszeitraum($result->studiengang_kz, $std_semester, $studienplan[0]->studienplan_id);
										$stg_bezeichnung .= ' '.$bewerbungszeitraum['bewerbungszeitraum'];
										$fristAbgelaufen = $bewerbungszeitraum['frist_abgelaufen'];
									}
									else
									{
										// Bei Lehrgaengen kommt der Name des Lehrgangs aus der Studiengangsbezeichnung
										$bezeichnung_studiengang = new studiengang($result->studiengang_kz);
										if($sprache != 'German' && $bezeichnung_studiengang->english != '')
											$stg_bezeichnung = $bezeichnung_studiengang->english;
										else
											$stg_bezeichnung = $bezeichnung_studiengang->bezeichnung;
									}
								}
								elseif ($result->typ != 'l' && !isset($lgtyparr[$result->lgartcode]))
								{
									// Wenn kein gueltiger Studienplan gefunden wird, Bezeichnung des Studiengangs laden
									$bezeichnung_studiengang = new studiengang($result->studiengang_kz);
									if($sprache != 'German' && $bezeichnung_studiengang->english != '')
										$stg_bezeichnung = $bezeichnung_studiengang->english;
									else
										$stg_bezeichnung = $bezeichnung_studiengang->bezeichnung;

									// Wenn kein gueltiger Studienplan gefunden wird, ist die Registration nicht moeglich und es wird ein Infotext angezeigt
									$fristAbgelaufen = true;
									
									// An der FHTW werden alle Mails von Bachelor-Studiengängen an das Infocenter geschickt, solange die Bewerbung noch nicht bestätigt wurde
									if (CAMPUS_NAME == 'FH Technikum Wien')
									{
										if(	defined('BEWERBERTOOL_MAILEMPFANG') && 
											BEWERBERTOOL_MAILEMPFANG != '' && 
											$result->typ == 'b')
										{
											$empfaenger = BEWERBERTOOL_MAILEMPFANG;
										}
										else
											$empfaenger = getMailEmpfaenger($result->studiengang_kz);
									}
									else 
									{
										$empfaenger = getMailEmpfaenger($result->studiengang_kz);
									}

									$stg_bezeichnung .= '<br><span style="color:orange"><i>'.$p->t('bewerbung/bewerbungDerzeitNichtMoeglich',array($empfaenger)).'</i></span>';
								}
								else
								{
									// Wenn kein gueltiger Studienplan gefunden wird und es ein Lehrgang ist, die Bezeichnung des Studiengangs laden
									$bezeichnung_studiengang = new studiengang($result->studiengang_kz);
									if($sprache != 'German' && $bezeichnung_studiengang->english != '')
										$stg_bezeichnung = $bezeichnung_studiengang->english;
									else
										$stg_bezeichnung = $bezeichnung_studiengang->bezeichnung;
								}

								if(in_array($result->studiengang_kz, $studiengaenge) || $result->studiengang_kz == $stg_auswahl)
								{
									$checked = 'checked';
									$disabled = '';
								}
								if ($result->typ!='l')
									$class = 'checkbox_stg';
								else
									$class = 'checkbox_lg';

								if (!$fristAbgelaufen)
								{
									echo '<div class="panel-body">
									<div class="checkbox">
										<label data-toggle="collapse" data-target="#prio-dropown'.$result->studiengang_kz.'">
											<input class="'.$class.'" type="checkbox" name="studiengaenge[]" value="'.$result->studiengang_kz.'" '.$checked.' '.$disabled.'>
											'.$stg_bezeichnung;
								}
								else
								{
									echo '<div class="panel-body">
									<div class="checkbox disabled">
										<label class="text-muted" data-toggle="collapse" data-target="#prio-dropown'.$result->studiengang_kz.'">
											<input class="" type="checkbox" name="" value="" disabled>
											'.$stg_bezeichnung;
								}

								if (!isset($anmerkungen[$result->studiengang_kz]) && in_array($result->studiengang_kz, $studiengaenge) && $orgform_kurzbz != '')
									$anmerkungen[$result->studiengang_kz] = 'Prio: '.$orgform_kurzbz;

								if (!isset($orgform[$result->studiengang_kz]) && in_array($result->studiengang_kz, $studiengaenge) && $orgform_kurzbz != '')
									$orgform[$result->studiengang_kz] = substr($orgform_kurzbz, 0, strpos($orgform_kurzbz, '_'));

								if(!isset($orgform[$result->studiengang_kz]) && count($orgformen_sprachen) == 1)
									$orgform[$result->studiengang_kz] = key($orgformen_sprachen);

								echo '
										<span class="badge" id="badge'.$result->studiengang_kz.'">'.(isset($anmerkungen[$result->studiengang_kz])?$anmerkungen[$result->studiengang_kz]:'').'</span>
										<input type="hidden" id="anmerkung'.$result->studiengang_kz.'" name="anmerkung['.$result->studiengang_kz.']" value="'.(isset($anmerkungen[$result->studiengang_kz])?$anmerkungen[$result->studiengang_kz]:'').'">
										<input type="hidden" id="orgform'.$result->studiengang_kz.'" name="orgform['.$result->studiengang_kz.']" value="'.(isset($orgform[$result->studiengang_kz])?$orgform[$result->studiengang_kz]:'').'">
										<input type="hidden" id="orgform_alt'.$result->studiengang_kz.'" name="orgform_alt['.$result->studiengang_kz.']" value="'.$orgform_alt[$result->studiengang_kz].'">
									</label>
								</div></div>
								';

								if(in_array($result->studiengang_kz, $studiengaenge))
									$collapse = 'collapse in';
								else
									$collapse = 'collapse';

								if($modal)
								{
									echo'
										<div id="prio-dropown'.$result->studiengang_kz.'" class="'.$collapse.'">
										<div class="modal-dialog" style="margin: 10px 0 10px 20px;" data-stgkz="'.$result->studiengang_kz.'">
										<div class="modal-content" style="box-shadow: none;">
										<div class="modal-header">
											<h4 class="modal-title">'.$p->t('bewerbung/orgformWaehlen').'</h4>
										</div>
										<div class="modal-body">
											<div class="row">
												<div class="col-sm-12">
													<p>'.$p->t('bewerbung/orgformBeschreibungstext').'</p>
												</div>
											</div>';

									echo '<div class="row" id="topprio'.$result->studiengang_kz.'">
										<div class="col-sm-12 priogroup">';
									if(count($orgformen_sprachen) > 0)
									{
										foreach($studienplan as $row)
										{
											$fristAbgelaufen = false;

											// Bewerbungsfristen laden
											$bewerbungszeitraum = '';
											$bewerbungszeitraum_result = getBewerbungszeitraum($result->studiengang_kz, $std_semester, $row->studienplan_id);
											$bewerbungszeitraum .= ' '.$bewerbungszeitraum_result['bewerbungszeitraum'];
											$fristAbgelaufen = $bewerbungszeitraum_result['frist_abgelaufen'];

											$checked_orgform = '';
											if (
												((in_array($result->studiengang_kz, $studiengaenge) || $result->studiengang_kz == $stg_auswahl)
													&& isset($orgform[$result->studiengang_kz]) && $orgform[$result->studiengang_kz] == $row->orgform_kurzbz)
												||
												((in_array($result->studiengang_kz, $studiengaenge) && $orgform_kurzbz == $row->orgform_kurzbz.'_'.$row->sprache))
												)
											{
												$checked_orgform = 'checked="checked"';
											}

											if (!$fristAbgelaufen)
											{
												echo '<div class="radio" onchange="changePrio('.$result->studiengang_kz.')">
												<label>
													<input type="radio" name="topprioOrgform'.$result->studiengang_kz.'" value="'.$row->orgform_kurzbz.'_'.$row->sprache.'" '.$checked_orgform.'>
													'.$p->t('bewerbung/orgform/'.$row->orgform_kurzbz).' - '.$p->t('bewerbung/'.$row->sprache).$bewerbungszeitraum;
												echo '</label>';
											}
											else
											{
												echo '<div class="radio disabled">
												<label>
													<input type="radio" name="" value="" disabled>
													'.$p->t('bewerbung/orgform/'.$row->orgform_kurzbz).' - '.$p->t('bewerbung/'.$row->sprache).$bewerbungszeitraum;
												echo '</label>';
											}
											echo '</div>';
										}
									}
									else
										echo '<div>
												'.$p->t('bewerbung/keineOrgformVorhanden').'
											</div>';
									echo'</div></div>';
									echo '<div class="row" id="alternative'.$result->studiengang_kz.'">
									<div class="col-sm-12">
										<label data-toggle="collapse" data-target="#alternative-dropown'.$result->studiengang_kz.'"><h5><b>'.$p->t('bewerbung/prioUeberschriftalternative').'</b> <span class="glyphicon glyphicon-collapse-down"></span></h5></label>
									</div>
									<div class="col-sm-12 priogroup collapse" id="alternative-dropown'.$result->studiengang_kz.'">';

									if(count($orgformen_sprachen) > 0)
									{
										echo '	<div class="radio" onchange="changePrio('.$result->studiengang_kz.')">
													<label>
														<input type="radio" name="alternativeOrgform'.$result->studiengang_kz.'" value="keine">
														'.$p->t('bewerbung/egal').'
													</label>
												</div>';

										foreach($studienplan as $row)
										{
											$fristAbgelaufen = false;

											// Bewerbungsfristen laden
											$bewerbungszeitraum = '';
											$bewerbungszeitraum_result = getBewerbungszeitraum($result->studiengang_kz, $std_semester, $row->studienplan_id);
											$bewerbungszeitraum .= ' '.$bewerbungszeitraum_result['bewerbungszeitraum'];
											$fristAbgelaufen = $bewerbungszeitraum_result['frist_abgelaufen'];

											$checked_orgform_alternativ = '';
											if ((in_array($result->studiengang_kz, $studiengaenge) || $result->studiengang_kz == $stg_auswahl) && $orgform_alt[$result->studiengang_kz] == $row->orgform_kurzbz)
											{
												$checked_orgform_alternativ = 'checked="checked"';
											}

											if (!$fristAbgelaufen)
											{
												echo '<div class="radio" onchange="changePrio('.$result->studiengang_kz.')">
												<label>
													<input type="radio" name="alternativeOrgform'.$result->studiengang_kz.'" value="'.$row->orgform_kurzbz.'_'.$row->sprache.'" '.$checked_orgform_alternativ.'>
													'.$p->t('bewerbung/orgform/'.$row->orgform_kurzbz).' - '.$p->t('bewerbung/'.$row->sprache).$bewerbungszeitraum;
												echo '</label>';
											}
											else
											{
												echo '<div class="radio disabled">
												<label>
													<input type="radio" name="" value="" disabled>
													'.$p->t('bewerbung/orgform/'.$row->orgform_kurzbz).' - '.$p->t('bewerbung/'.$row->sprache).$bewerbungszeitraum;
												echo '</label>';
											}
											echo '</div>';
										}
									}
									else
										echo '<div>
											'.$p->t('bewerbung/keineOrgformVorhanden').'
										</div>';
									echo'</div></div></div></div></div></div>';
								}
							}
							?></div></div>
						</div>
					</div>
					<?php endif; ?>

					<div class="form-group">
						<div class="col-xs-10 col-xs-offset-1 col-sm-9 col-sm-offset-3">
							<label class="checkbox-inline">
								<input type="checkbox" name="zustimmung_datenuebermittlung" id="checkbox_zustimmung_datenuebermittlung" value="" required="required">
								<?php echo $p->t('bewerbung/zustimmungDatenuebermittlung') ?>
							</label>
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
				if($email!='')
				{
					if ($return)
					{
						if (isset($resend_code))
						{
							//Wenn es noch keinen Zugangscode für die Person gibt, generiere einen
							if($return->zugangscode=='')
							{
								$person = new person($return->person_id);

								$zugangscode = substr(md5(openssl_random_pseudo_bytes(20)), 0, 10);

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
							if($return)
							{
								$zugangscode = $return->zugangscode;
								echo '<p class="alert alert-success"><button type="button" class="close" data-dismiss="alert">x</button>'.sendMail($zugangscode, $email, $return->person_id).'</p>';
								// Logeintrag schreiben
								$log->log($return->person_id,
									'Action',
									array('name'=>'Access code sent','success'=>true,'message'=>'User requested his access code'),
									'bewerbung',
									'bewerbung',
									null,
									'online'
								);
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
						<form action ="<?php echo basename(__FILE__) ?>" method="POST" id="lp" class="form-horizontal">
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
							<div class="panel panel-info">
								<div class="panel-heading text-center">
									<h3 class="panel-title"><?php echo $p->t('bewerbung/sieHabenNochKeinenZugangscode') ?></h3>
								</div>
								<div class="panel-body text-center">
									<br>
									<a class="btn btn-primary btn-lg" href="<?php echo basename(__FILE__) ?>?method=registration&stg_kz=<?php echo filter_input(INPUT_GET, 'stg_kz') ?>&orgform_kurzbz=<?php echo filter_input(INPUT_GET, 'orgform_kurzbz') ?>" role="button"><?php echo $p->t('bewerbung/hierUnverbindlichAnmelden') ?></a>
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
									<div class="input-group col-sm-6 col-sm-offset-3">
										<p class="text-center"><input class="form-control" type="text" placeholder="<?php echo $p->t('bewerbung/zugangscode') ?>" name="userid" autofocus="autofocus" value="<?php echo $code ?>"></p>
										<span class="input-group-btn">
											<button class="btn btn-primary" type="submit" name="submit_btn">
												<?php echo $p->t('bewerbung/login') ?>
											</button>
										</span>
									</div>
									<br>
									<div class="col-sm-4 col-sm-offset-4">
										<a href="<?php echo basename(__FILE__) ?>?method=resendcode"><?php echo $p->t('bewerbung/zugangscodeVergessen') ?></a>
									</div>
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
										<button class="btn btn-primary btn-lg" type="submit" name="submit_btn">
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

			function checkPrios(stgkz)
			{
				var anm = 'keine Prio';

				if($('#topprio'+stgkz+' input:checked').length !== 0)
				{
					anm = 'Prio: ' + $('#topprio'+stgkz+' input[name="topprioOrgform'+stgkz+'"]:checked').val();

					if($('#alternative'+stgkz+' input:checked').length !== 0)
					{
						anm += '; Alt: ' + $('#alternative'+stgkz+' input[name="alternativeOrgform'+stgkz+'"]:checked').val();
					}
				}

				return anm;
			}

			function getPrioOrgform(stgkz)
			{
				var orgform = '';
				orgform = $('#topprio'+stgkz+' input[name="topprioOrgform'+stgkz+'"]:checked').val();

				if(orgform == undefined)
					orgform = '';

				if(orgform!='')
					orgform = orgform.split('_')[0];

				return orgform;
			}
			function getAltOrgform(stgkz)
			{
				var orgform_alt = '';
				orgform_alt = $('#alternative'+stgkz+' input[name="alternativeOrgform'+stgkz+'"]:checked').val();

				if(orgform_alt == undefined)
					orgform_alt = '';

				if(orgform_alt!='')
					orgform_alt = orgform_alt.split('_')[0];

				return orgform_alt;
			}
			function changePrio(stgkz)
			{
				var anm, orgform;

				anm = checkPrios(stgkz);
				orgform = getPrioOrgform(stgkz);
				orgform_alt = getAltOrgform(stgkz);

				$('#anmerkung' + stgkz).val(anm);
				$('#badge' + stgkz).html(anm);
				$('#orgform' + stgkz).val(orgform);
				$('#orgform_alt' + stgkz).val(orgform_alt);

			};
			function submitPrio(stg_kz)
			{
				inputs = document.getElementsByName('studiengaenge[]');

				if (inputs!=null)
				{
					for(i=0;i<inputs.length;i++)
					{
						if (inputs[i].checked==true)
						{
							exists = $('#topprio'+inputs[i].value+' input[name="topprioOrgform'+inputs[i].value+'"]').val();
							if(typeof exists != 'undefined')
							{
								orgform = getPrioOrgform(inputs[i].value);
								if(orgform == '')
								{
									alert('<?php echo $p->t('bewerbung/bitteOrgformWaehlen') ?>');
									return false;
									break;
								}
							}
						}
					}
				}
			};

			$(function() {

				$('#sprache-dropdown a').on('click', function() {

					var sprache = $(this).attr('data-sprache');
					changeSprache(sprache);
				});

				<?php if (defined('BEWERBERTOOL_MAX_STUDIENGAENGE') && BEWERBERTOOL_MAX_STUDIENGAENGE != ''): ?>
					//Disabled die Checkboxen, wenn mehr als 3 Studiengaenge gewaehlt werden
					$("input[type=checkbox][class=checkbox_stg]").click(function()
					{
						var bol = $("input[type=checkbox][class=checkbox_stg]:checked").length >= <?php echo BEWERBERTOOL_MAX_STUDIENGAENGE; ?>;
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
	$text = $p->t('bewerbung/mailtext',array($vorname, $nachname, $zugangscode, $anrede));
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
	$text = $p->t('bewerbung/mailtext',array($vorname, $nachname, $zugangscode, $anrede));
	$mail->setHTMLContent($text);
	if(!$mail->send())
		$msg= '<span class="error">'.$p->t('bewerbung/fehlerBeimSenden').'</span><br /><a href='.$_SERVER['PHP_SELF'].'?method=registration>'.$p->t('bewerbung/zurueckZurAnmeldung').'</a>';
	else
		$msg= $p->t('bewerbung/emailgesendetan', array($email))."<br><br><a href=".$_SERVER['PHP_SELF'].">".$p->t('bewerbung/zurueckZurAnmeldung')."</a>";

	return $msg;
}
