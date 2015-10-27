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
require_once('../../../include/datum.class.php');
require_once('../../../include/sprache.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../include/functions.inc.php');

require_once '../../../include/securimage/securimage.php';

session_start();
$lang = filter_input(INPUT_GET, 'lang');

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
		<title>Registration für Studiengänge</title>
		<meta http-equiv="X-UA-Compatible" content="chrome=1">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<meta name="robots" content="noindex">
		<link href="../../../submodules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css">
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
				<?php if($method === 'registration'): ?>
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

				$vorname = filter_input(INPUT_POST, 'vorname');
				$nachname = filter_input(INPUT_POST, 'nachname');
				$geb_datum = filter_input(INPUT_POST, 'geb_datum');
				$geschlecht = filter_input(INPUT_POST, 'geschlecht');
				$email = filter_input(INPUT_POST, 'email');
				$anmerkungen = filter_input(INPUT_POST, 'anmerkung', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
				$orgform = filter_input(INPUT_POST, 'orgform', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

                if(BEWERBERTOOL_STUDIENAUSWAHL_ANZEIGEN)
                {
                    $studiengaenge = filter_input(INPUT_POST, 'studiengaenge', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                    $std_semester = filter_input(INPUT_POST, 'studiensemester_kurzbz');
                    $stg_auswahl = filter_input(INPUT_POST, 'stg');

                    if(!is_array($studiengaenge))
                    {
                        $studiengaenge = array();
                    }
                }
                else
                {
                    $std_semester = null;
                }

				if($geb_datum)
				{
					$geb_datum = date('Y-m-d', strtotime($geb_datum));
				}

				$submit = filter_input(INPUT_POST, 'submit_btn');

				// Pruefen, ob fuer dieses Semester schon eine Bewerbung fuer diese Mailadresse existiert->Wenn ja, Code nochmal dorthin schicken
				$return = check_load_bewerbungen($email, $std_semester);
				if($return)
				{
					$resend_code = filter_input(INPUT_GET, 'ReSendCode');
					if (isset($resend_code))
					{					
						$zugangscode = $return->zugangscode;
						echo sendMail($zugangscode, $email);
						exit();
					}
					else 
						$message = '<p class="bg-danger padding-10">Diese E-Mail Adresse wurde bereits für eine Bewerbung genutzt. Möchten Sie den Zugangscode noch einmal an diese Adresse senden?</p>
								<button type="submit" class="btn btn-primary" value="Ja" onclick="document.RegistrationLoginForm.action=\''.basename(__FILE__).'?method=registration&ReSendCode\'; document.getElementById(\'RegistrationLoginForm\').submit();">Ja</button>
								<button type="submit" class="btn btn-primary" value="Nein" onclick="document.RegistrationLoginForm.email.value=\'\'; document.getElementById(\'RegistrationLoginForm\').submit();">Nein</button>'; //@todo: Phrasenmodul
					
				}
				else
				{
					if(isset($submit))
					{
						$securimage = new Securimage();
						// Sicherheitscode wurde falsch eingegeben
						if ($securimage->check($_POST['captcha_code']) == false)
						{
							$message = '<p class="bg-danger padding-10">'.$p->t('bewerbung/sicherheitscodeFalsch').'</p>';
						}
						if (BEWERBERTOOL_STUDIENAUSWAHL_ANZEIGEN && count($studiengaenge)==0)
						{
							$message = '<p class="bg-danger padding-10">'.$p->t('bewerbung/bitteStudienrichtungWaehlen').'</p>';
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
							$person->aktiv = true;
							$person->zugangscode = $zugangscode;
							$person->insertamum = date('Y-m-d H:i:s');
							$person->updateamum = date('Y-m-d H:i:s');
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
							$kontakt->insertamum = date('Y-m-d H:i:s');
							$kontakt->updateamum = date('Y-m-d H:i:s');
							$kontakt->new = true;
	
							if(!$kontakt->save())
							{
								die($p->t('global/fehlerBeimSpeichernDerDaten'));
							}
	
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
	                                $prestudent->updateamum = date('Y-m-d H:i:s');
	                                $prestudent->reihungstestangetreten = false;
	                                $prestudent->new = true;
	
	                                if(!$prestudent->save())
	                                {
	                                    die($p->t('global/fehlerBeimSpeichernDerDaten'));
	                                }
	
	                                // Interessenten Status anlegen
	                                $prestudent_status = new prestudent();
	                                $prestudent_status->load($prestudent->prestudent_id);
	                                $prestudent_status->status_kurzbz = 'Interessent';
	                                $prestudent_status->studiensemester_kurzbz = $std_semester;
	                                $prestudent_status->ausbildungssemester = '1';
	                                $prestudent_status->datum = date("Y-m-d H:m:s");
	                                $prestudent_status->insertamum = date("Y-m-d H:m:s");
	                                $prestudent_status->insertvon = '';
	                                $prestudent_status->updateamum = date("Y-m-d H:m:s");
	                                $prestudent_status->updatevon = '';
	                                $prestudent_status->new = true;
	                                $prestudent_status->anmerkung_status = $anmerkungen[$studiengaenge[$i]];
									$prestudent_status->orgform_kurzbz = $orgform[$studiengaenge[$i]];
	
	                                if(!$prestudent_status->save_rolle())
	                                {
	                                    die($p->t('global/fehlerBeimSpeichernDerDaten'));
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
	                            $preInteressent->updateamum = date('Y-m-d H:i:s');
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
	                                    $preIntZuordnung->updateamum = date('Y-m-d H:i:s');
	                                    $preIntZuordnung->new = true;
	
	                                    if(!$preIntZuordnung->saveZuordnung())
	                                    {
	                                        die($p->t('global/fehlerBeimSpeichernDerDaten'));
	                                    }
	                                }
	                            }
							}
	
	                        //Email schicken
							echo sendMail($zugangscode, $email);
							exit();
						}
					}
				} ?>

				<?php echo $message ?>
				<form method="post" action="<?php echo basename(__FILE__) ?>?method=registration" id="RegistrationLoginForm" name="RegistrationLoginForm" class="form-horizontal">
					<p class="infotext">
						<?php echo $p->t('bewerbung/einleitungstext') ?>
					</p>
					<div class="form-group">
						<label for="zugangscode" class="col-sm-3 control-label">
							<?php echo $p->t('bewerbung/zugangscode') ?> <?php echo $p->t('bewerbung/fallsVorhanden') ?>
						</label>
						<div class="col-sm-4">
							<div class="input-group">
								<input type="text" class="form-control" id="zugangscode" name="userid" placeholder="<?php echo $p->t('bewerbung/zugangscode') ?>">
								<span class="input-group-btn">
									<button type="submit" class="btn btn-primary" value="Login">
										<?php echo $p->t('bewerbung/login') ?>
									</button>
								</span>
							</div>
						</div>
					</div>

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
                            <input type="datetime" name="geb_datum" id="geburtsdatum"
                                   value="<?php echo isset($geb_datum) ? date('d.m.Y', strtotime($geb_datum)) : '' ?>"
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
								<?php echo $p->t('global/mann'); ?>
							</label>
							<label class="radio-inline">
								<input type="radio" name="geschlecht" id="geschlechtw" value="w" <?php echo $geschlecht == 'w' ? 'checked' : '' ?>>
								<?php echo $p->t('global/frau') ?>
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
							<select id="studiensemester_kurzbz" name="studiensemester_kurzbz" class="form-control">
								<option value=""><?php echo $p->t('bewerbung/bitteAuswaehlen') ?></option>
								<?php
								$stsem = new studiensemester();
								$stsem->getStudiensemesterOnlinebewerbung();

								foreach($stsem->studiensemester as $row): ?>
									<option value="<?php echo $row->studiensemester_kurzbz ?>"
										<?php echo $std_semester == $row->studiensemester_kurzbz ? 'selected' : '' ?>>
										<?php echo $row->bezeichnung ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-3 control-label">
							<?php echo $p->t('bewerbung/studienrichtung') ?>
						</label>
						<div class="col-sm-6" id="liste-studiengaenge">
							<?php
							$stg = new studiengang();
							$stg->getAllForBewerbung();


							$stghlp = new studiengang();
							$stghlp->getLehrgangstyp();
							$lgtyparr=array();
							foreach($stghlp->result as $row)
								$lgtyparr[$row->lgartcode]=$row->bezeichnung;

							$stgtyp = new studiengang();
							$stgtyp->getAllTypes();

							$lasttyp='';
							foreach($stg->result as $result)
							{
									if($lasttyp!=$result->typ)
									{
										echo '<h4>'.$stgtyp->studiengang_typ_arr[$result->typ].'</h4>';
										$lasttyp=$result->typ;
									}

									$checked = '';
									$typ = new studiengang();
									$typ->getStudiengangTyp($result->typ);

									if($sprache!='German' && $result->studiengangbezeichnung_englisch!='')
										$stg_bezeichnung = $result->studiengangbezeichnung_englisch;
									else
										$stg_bezeichnung = $result->studiengangbezeichnung;

									$orgform = $stg->getOrgForm($result->studiengang_kz);
									$sprache_lv = $stg->getSprache($result->studiengang_kz);

									$modal = false;

									if(count($orgform) !== 1 || count($sprache) !== 1)
									{
										$modal = true;
									}

									if(in_array($result->studiengang_kz, $studiengaenge) || $result->studiengang_kz == $stg_auswahl)
									{
										$checked = 'checked';
									}
									echo '
									<div class="checkbox">
										<label>
											<input type="checkbox" name="studiengaenge[]" value="'.$result->studiengang_kz.'" '.$checked.'
												   data-modal="'.$modal.'"
												   data-modal-sprache="'.implode(',', $sprache_lv).'"
												   data-modal-orgform="'.implode(',', $orgform).'">
											'.$stg_bezeichnung;
									if($result->typ=='l' && isset($lgtyparr[$result->lgartcode]))
									{
										echo ' ('.$lgtyparr[$result->lgartcode].')';
									}

									echo '
											<span class="badge" id="badge'.$result->studiengang_kz.'"></span>
											<input type="hidden" id="anmerkung'.$result->studiengang_kz.'" name="anmerkung['.$result->studiengang_kz.']">
											<input type="hidden" id="orgform'.$result->studiengang_kz.'" name="orgform['.$result->studiengang_kz.']">
										</label>
									</div>
									';
							}
							?>
						</div>
					</div>
                    <?php endif; ?>

					<div class="form-group">
						<div class="col-sm-3">
							<img id="captcha" class="center-block img-responsive" src="<?php echo APP_ROOT ?>include/securimage/securimage_show.php" alt="CAPTCHA Image" />
							<a href="#" onclick="document.getElementById('captcha').src = '<?php echo APP_ROOT ?>include/securimage/securimage_show.php?' + Math.random(); return false">
								<?php echo $p->t('bewerbung/andereGrafik') ?>
							</a>
						</div>
						<div class="col-sm-4">
							<?php echo $p->t('bewerbung/captcha') ?>
							<input type="text" name="captcha_code" maxlength="6" id="captcha" class="form-control">
							<input type="hidden" name="zugangscode" value="<?php echo uniqid() ?>">
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-4 col-sm-offset-3">
							<input type="submit" name="submit_btn" value="<?php echo $p->t('bewerbung/registrieren') ?>" onclick="return checkRegistration()" class="btn btn-primary">
						</div>
					</div>
				</form>
			<?php else: ?>
				<?php echo $message ?>
				<div class="row">
					<div class="col-xs-10 col-xs-offset-1 col-sm-6 col-sm-offset-3">
                        <form action ="<?php echo basename(__FILE__) ?>" method="POST" id="lp" class="form-horizontal">
							<h1 class="text-center">
								<?php echo $p->t('bewerbung/welcome') ?>
							</h1>
							<img class="center-block img-responsive" src="../../../skin/styles/<?php echo DEFAULT_STYLE ?>/logo.png">
							<p class="text-center"><?php echo $p->t('bewerbung/registrierenOderZugangscode') ?></p>
							<div class="form-group">
								<div class="input-group">
									<input class="form-control" type="text" placeholder="<?php echo $p->t('bewerbung/zugangscode') ?>" name="userid" autofocus="autofocus">
									<span class="input-group-btn">
										<button class="btn btn-primary" type="submit" name="submit_btn">
											Login
										</button>
									</span>
								</div>
							</div>
							<p class="text-center"><?php echo $p->t('bewerbung/loginmitAccount') ?></p>
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
                                <span class="col-sm-4 col-sm-offset-3">
                                    <button class="btn btn-primary" type="submit" name="submit_btn">
                                        Login
                                    </button>
                                </span>
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
			<?php endif; ?>
		</div>
		<?php
        if(BEWERBERTOOL_STUDIENAUSWAHL_ANZEIGEN)
            require('views/modal_sprache_orgform.php');
        ?>
		<script src="../../../include/js/jquery.min.1.11.1.js"></script>
		<script src="../../../submodules/bootstrap/dist/js/bootstrap.min.js"></script>
		<script type="text/javascript">

			function changeSprache(sprache)
			{
				var method = '<?php echo $db->convert_html_chars($method);?>';

				window.location.href = "registration.php?sprache=" + sprache + "&method=" + method;
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
					var gebDat = document.RegistrationLoginForm.geb_datum.value;
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
				}
				if((document.getElementById('geschlechtm').checked == false)&&(document.getElementById('geschlechtw').checked == false))
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
				return true;
			}

			$(function() {

				$('#sprache-dropdown a').on('click', function() {

					var sprache = $(this).attr('data-sprache');
					changeSprache(sprache);
				});

				<?php if(BEWERBERTOOL_STUDIENAUSWAHL_ANZEIGEN): ?>
                $('#liste-studiengaenge input').on('change', function() {

					var stgkz = $(this).val(),
						modal = $(this).attr('data-modal'),
						modal_orgform = $(this).attr('data-modal-orgform').split(','),
						modal_sprache = $(this).attr('data-modal-sprache').split(',');
					$('#prio-dialog').data({stgkz: stgkz});

					if($(this).prop('checked') && modal) {

						$('#prio-dialog input[value="egal"]').prop('checked', true);
						prioAvailable(modal_orgform, modal_sprache);
						checkPrios(0);

						$('#prio-dialog').modal('show');

					} else {

						$('#badge' + stgkz).html('');
					}
                });

				$('#prio-dialog button.cancel-prio').on('click', function() {

					var stgkz = $('#prio-dialog').data('stgkz');

					$('#liste-studiengaenge input[value="' + stgkz + '"]').prop('checked', false);
					$('#badge' + stgkz).html('');
				});

				$('#prio-dialog button.ok-prio').on('click', function() {

					var stgkz = $('#prio-dialog').data('stgkz'),
						anm;

					anm = checkPrios(0);
					orgform = getPrioOrgform();

					$('#orgform' + stgkz).val(orgform);
					$('#anmerkung' + stgkz).val(anm);
					$('#badge' + stgkz).html(anm);
				});

				$('#prio-dialog input').on('change', function() {

					var stgkz = $('#prio-dialog').data('stgkz'),
						anm;

					anm = checkPrios(200);

					$('#anmerkung' + stgkz).val(anm);
					$('#badge' + stgkz).html(anm);
				});
                <?php endif; ?>
			});

		</script>
	</body>
</html>

<?php
function sendMail($zugangscode, $email)
{
	global $p, $vorname, $nachname, $geschlecht;
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
		$msg= $p->t('global/emailgesendetan')." $email!<br><a href=".$_SERVER['PHP_SELF'].">".$p->t('bewerbung/zurueckZurAnmeldung')."</a>";

    // sende Nachricht an Assistenz

	return $msg;
}
