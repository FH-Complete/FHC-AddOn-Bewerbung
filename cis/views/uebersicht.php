<?php
/*
 * Copyright (C) 2015 fhcomplete.org
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * Authors: Robert Hofer <robert.hofer@technikum-wien.at>
 * Manfred Kindl <manfred.kindl@technikum-wien.at>
 */
require_once('../../../config/global.config.inc.php');
require_once('../bewerbung.config.inc.php');
require_once('../../../include/statusgrund.class.php');

if (!isset($person_id))
{
	die($p->t('bewerbung/ungueltigerZugriff'));
}
$studiensemester_array = array();
?>

<div role="tabpanel" class="tab-pane" id="uebersicht">
	<h2><?php echo $p->t('bewerbung/menuUebersicht'); ?></h2>
	<?php

	if ($save_error_abschicken === false)
	{
		echo '	<div class="alert alert-success" id="success-alert_abschicken">
				<button type="button" class="close" data-dismiss="alert">x</button>
					<strong>'.$message.'</strong>
				</div>';
	}
	elseif ($save_error_abschicken === true)
	{
		echo '	<div class="alert alert-danger" id="danger-alert">
			<button type="button" class="close" data-dismiss="alert">x</button>
				<strong>'.$p->t('global/fehleraufgetreten').' </strong>'.$message.'
			</div>';
	}

	if ($_SESSION['bewerbung/user'] == 'Login')
	{
		echo '<p>'.$p->t('bewerbung/erklaerungStudierende').'</p>';
	}
	else
	{
		echo '<p>'.$p->t('bewerbung/allgemeineErklaerung').'</p>';
	}

	// Button zum hinzufügen neuer Studiengänge
	if (BEWERBERTOOL_MAX_STUDIENGAENGE > 1 || BEWERBERTOOL_MAX_STUDIENGAENGE == '')
	{
		echo '<button id="open-modal-studiengaenge-button"
		class="btn-nav btn btn-success" type="button" data-toggle="modal"
		data-target="#modal-studiengaenge">
		'.$p->t('bewerbung/studiengangHinzufuegen').'
	</button><br><br>';
	}

	$bereits_angemeldet = array();
	$anzahl_studiengaenge = array();
	$stsem_bewerbung_arr = array();
	$studiengaengeBaMa = array(); // Nur Bachelor oder Master Studiengaenge

	$stsem = new studiensemester();
	$stsem->getStudiensemesterOnlinebewerbung();
	foreach ($stsem->studiensemester as $row)
	{
		$stsem_bewerbung_arr[] = $row->studiensemester_kurzbz;
	}

	if (!$prestudent = getBewerbungen($person_id, true))
	{
		echo '<div class="alert alert-warning">'.$p->t('bewerbung/keinStatus').'</div>';
	}
	else
	{
		usort($prestudent, "sortPrestudents");
		$studsemester = '';
		$prioIndex = 0;
		foreach ($prestudent as $row)
		{
			$prioIndex++;
			if ($studsemester != $row->laststatus_studiensemester_kurzbz)
			{
				if ($studsemester != '' && $studsemester != $row->laststatus_studiensemester_kurzbz)
				{
					echo '</div>';
					$disabledPrioUp = 'disabled="disabled"';
					$disabledPrioDown = '';
					$prioIndex = 1;
				}
				echo '<p><b>'.$p->t('bewerbung/bewerbungenFuerStudiensemesterXX', array($row->laststatus_studiensemester_kurzbz)).'</b></p>';
				echo '<div class="row" style="padding: 5px 15px;">
						<div class="col-xs-2 col-sm-2 col-md-1 text-center">
							'.$p->t('bewerbung/prioritaet').'
						</div>
						<div class="col-xs-10 col-sm-10 col-md-11">&nbsp;</div>
					</div>';
				echo '<div class="panel-group panel_bewerbungen" id="accordionBewerbungen'.$row->laststatus_studiensemester_kurzbz.'">';
			}
			else
			{
				$disabledPrioDown = 'disabled="disabled"';
			}
			$studsemester = $row->laststatus_studiensemester_kurzbz;

			?>

			<?php
			$stg = new studiengang();
			if (!$stg->load($row->studiengang_kz))
			{
				die($p->t('global/fehlerBeimLadenDesDatensatzes'));
			}

			// Pruefen, ob alle Punkte vollständig sind
			$disabledAbschicken = true;
			if ($status_person == true &&
				$status_kontakt == true &&
				$status_zahlungen == true &&
				$status_zgv_bak == true &&
				$status_ausbildung == true)
			{
				// Das Abschicken der Bewerbung ist an keiner FH abhängig vom Reihungstest
				/*if ($status_reihungstest == true)
				{
					$disabledAbschicken = false;
				}*/
				$disabledAbschicken = false;
			}

			if ($stg->typ == 'm' && $status_zgv_mas == false)
			{
				$disabledAbschicken = true;
			}

			// Die Vollständigkeit der Dokumente wird extra für jeden Studiengang gecheckt
			// Stufe des Bewerbers ermitteln
			$stufe = getStufeBewerberFuerDokumente($row->prestudent_id, $row->laststatus_studiensemester_kurzbz);

			// Wenn für dieses Stufe alle Dokumente abgebeben sind, wird nochmal für Dokumente ohne Stufe gecheckt
			if (!empty($status_dokumente_arr[$row->studiengang_kz][$stufe]))
			{
				$disabledAbschicken = true;
			}
			if (!empty($status_dokumente_arr[$row->studiengang_kz]['']))
			{
				$disabledAbschicken = true;
			}

			$prestudent_status = new prestudent();
			$prestatus_help = ($prestudent_status->getLastStatus($row->prestudent_id)) ? $prestudent_status->status_mehrsprachig[$sprache] : $p->t('bewerbung/keinStatus');

			$bereits_angemeldet[$prestudent_status->studiensemester_kurzbz][] = $stg->studiengang_kz;

			// Zaehlt die Anzahl an Bewerbungen in einem Studiensemester
			// Wenn ein Status Abgewiesen oder Abbrecher ist, zaehlt er nicht zu der Anzahl an Bewerbungen mit
			if (in_array($prestudent_status->studiensemester_kurzbz, $stsem_bewerbung_arr) && $prestudent_status->status_kurzbz != 'Abbrecher')
			{
				if (!array_key_exists($prestudent_status->studiensemester_kurzbz, $anzahl_studiengaenge))
				{
					$anzahl_studiengaenge[$prestudent_status->studiensemester_kurzbz] = 0;
				}

				$anzahl_studiengaenge[$prestudent_status->studiensemester_kurzbz]++;

				if ($row->studiengang_kz > 0 && $row->studiengang_kz < 10000)
				{
					$studiengaengeBaMa[$prestudent_status->studiensemester_kurzbz][] = $row->studiengang_kz;
				}
			}

			// Bezeichnung des Studiengangs über den Studienplan laden wenn vorhanden
			if (isset($prestudent_status->studienplan_id) && $prestudent_status->studienplan_id != '')
			{
				$studienordnung = new studienordnung();
				$studienordnung->getStudienordnungFromStudienplan($prestudent_status->studienplan_id);
				if ($sprache != 'German')
				{
					if ($studienordnung->studiengangbezeichnung_englisch != '')
					{
						$stg_bezeichnung = $studienordnung->studiengangbezeichnung_englisch;
					}
					else
					{
						$stg_bezeichnung = $stg->english;
					}
				}
				else
				{
					if ($studienordnung->studiengangbezeichnung != '')
					{
						$stg_bezeichnung = $studienordnung->studiengangbezeichnung;
					}
					else
					{
						$stg_bezeichnung = $stg->bezeichnung;
					}
				}
			}
			else
			{
				if ($sprache != 'German' && $stg->english != '')
				{
					$stg_bezeichnung = $stg->english;
				}
				else
				{
					$stg_bezeichnung = $stg->bezeichnung;
				}
			}

			$typ = new studiengang();
			$typ->getStudiengangTyp($stg->typ);

			$empf_array = array();
			if (defined('BEWERBERTOOL_BEWERBUNG_EMPFAENGER'))
			{
				$empf_array = unserialize(BEWERBERTOOL_BEWERBUNG_EMPFAENGER);
			}

			// Organisationsform und Sprache aus Studienplan laden sonst aus prestudentstatus
			$studienplan_orgform = '';
			$studienplan_sprache = '';
			if ($prestudent_status->studienplan_id != '')
			{
				$studienplan = new studienplan();
				$studienplan->loadStudienplan($prestudent_status->studienplan_id);
				$studienplan_orgform = $studienplan->orgform_kurzbz;
				$studienplan_sprache = $studienplan->sprache;
			}
			else
			{
				$studienplan_orgform = $prestudent_status->orgform_kurzbz;
			}

			// An der FHTW werden alle Mails von Bachelor-Studiengängen an das Infocenter geschickt, solange die Bewerbung noch nicht bestätigt wurde
			if (CAMPUS_NAME == 'FH Technikum Wien')
			{
				if (defined('BEWERBERTOOL_MAILEMPFANG') && BEWERBERTOOL_MAILEMPFANG != '' && $stg->typ == 'b' && $prestudent_status->bestaetigtam == '')
				{
					$empfaenger = BEWERBERTOOL_MAILEMPFANG;
				}
				else
				{
					$empfaenger = getMailEmpfaenger($stg->studiengang_kz, $prestudent_status->studienplan_id);
				}
			}
			else
			{
				$empfaenger = getMailEmpfaenger($stg->studiengang_kz);
			}

			// Bei Lehrgängen auch den Lehrgangstyp anzeigen
			if ($row->typ == 'l')
			{
				$stgBeschriftungPanel = '<p>'.$p->t('bewerbung/lehrgangsArt/'.$row->lgartcode).' '.$stg_bezeichnung.'</p>';
			}
			else
			{
				$stgBeschriftungPanel = '<p>'.$typ->bezeichnung.' '.$stg_bezeichnung.'</p>';
			}

			// An der FHTW bei den Qualifikationskursen keine typ->bezeichnung anzeigen
			if (CAMPUS_NAME == 'FH Technikum Wien' && $stg->studiengang_kz == 10002)
			{
				$stgBeschriftungPanel = '<p>'.$stg_bezeichnung.'</p>';
			}

			if ($studienplan_orgform != '')
			{
				$organisationsform = new organisationsform($studienplan_orgform);
				$stgBeschriftungPanel .= '<p><i>'.$organisationsform->bezeichnung_mehrsprachig[$sprache];
				if ($studienplan_sprache != '')
				{
					$stgBeschriftungPanel .= ' - '.$p->t('bewerbung/'.$studienplan_sprache);
				}

				$stgBeschriftungPanel .= '</i></p>';
			}

			// Nation für die Anzeige der richtigen Bewerbungsfrist laden
			// Wenn für den aktuellen PreStudenten keine zgvnation gefunden wird, in anderen PreStudenten suchen
			if ($row->typ == 'm')
			{
				$zgv_nation = $row->zgvmanation;
			}
			else
			{
				$zgv_nation = $row->zgvnation;
			}

			$nation = new nation($zgv_nation);
			$nationengruppe = $nation->nationengruppe_kurzbz;

			if ($nationengruppe == '')
			{
				$nationengruppe = 0;
			}

			// Bewerbungsfristen laden
			$bewerbungszeitraum = getBewerbungszeitraum($stg->studiengang_kz, $prestudent_status->studiensemester_kurzbz, $prestudent_status->studienplan_id, $nationengruppe);
			$fristAbgelaufen = $bewerbungszeitraum['frist_abgelaufen'];

			echo '	<div class="panel panel-default" id="panel_'.$row->prestudent_id.'" data-prestudent_id="'.$row->prestudent_id.'">
					<div class="panel-heading" '.($prestudent_status->bewerbung_abgeschicktamum == '' && $fristAbgelaufen ? 'style="background-color: white"' : '').'>
						<h4 class="panel-title">
							<div class="row">';

			echo '				<div class="col-xs-2 col-sm-2 col-md-1 text-center">
								<!--<div class="text-center">Priorisierung</div>-->
								<label style="padding-right: 2px" class="prioIndex">'.$prioIndex.'</label>';
			// Priorisierung deaktivieren, wenn Bewerbung abgeschickt
			if (!check_person_bewerbungabgeschickt($person_id, $row->laststatus_studiensemester_kurzbz))
			{
				echo '				<div class="btn-group-vertical">
									
									<button class="btn btn-default button_up btn-block" type="button"
										onclick="changePriority(\''.$row->prestudent_id.'\', \''.$row->laststatus_studiensemester_kurzbz.'\', \'up\')">
										<span class="glyphicon glyphicon-triangle-top"></span>
									</button>
									<input type="hidden" class="form-control text-center" value="'.$row->priorisierung.'" disabled="disabled">
									<button class="btn btn-default button_down btn-block" type="button"
										onclick="changePriority(\''.$row->prestudent_id.'\', \''.$row->laststatus_studiensemester_kurzbz.'\', \'down\')">
										<span class="glyphicon glyphicon-triangle-bottom"></span>
									</button>
								</div>';
			}
			echo '				</div>';

			// Letzten Interessentenstatus laden
			$lastInteressentenStatus = new prestudent();
			$lastInteressentenStatus->getLastStatus($row->prestudent_id, '', 'Interessent');
			// Stornieren nur moeglich, wenn letzter Status "Interessent" ist oder noch nicht abgeschickt oder bestätigt wurde
			$buttonStornierenEnabled = false;
			if ($prestudent_status->status_kurzbz == 'Interessent'
				&& $prestudent_status->bewerbung_abgeschicktamum == ''
				&& $prestudent_status->bestaetigtam == ''
				&& $prestudent_status->bestaetigtvon == '')
			{
				$buttonStornierenEnabled = true;
			}
			// An der FHTW kann man den Studiengang EQK (Qualifikationskurse) nicht abschicken oder stornieren
			if (CAMPUS_NAME == 'FH Technikum Wien' && $stg->studiengang_kz == 10002)
			{
				$buttonStornierenEnabled = false;
			}

			// Abschicken nur möglich, wenn die Frist nicht abgelaufen ist und die Bewerbung noch nicht geschickt oder bestätigt wurde
			$buttonAbschickenEnabled = false;
			if ($lastInteressentenStatus->bewerbung_abgeschicktamum == ''
				&& $fristAbgelaufen == false
				&& $lastInteressentenStatus->bestaetigtam == ''
				&& $lastInteressentenStatus->bestaetigtvon == '')
			{
				$buttonAbschickenEnabled = true;
			}
			// An der FHTW kann man den Studiengang EQK (Qualifikationskurse) nicht abschicken oder stornieren
			if (CAMPUS_NAME == 'FH Technikum Wien' && $stg->studiengang_kz == 10002)
			{
				$buttonAbschickenEnabled = false;
			}

			echo '					<a 	data-toggle="collapse" 
									data-parent="#accordionBewerbungen'.$row->laststatus_studiensemester_kurzbz.'" 
									href="#panelCollapse'.$row->prestudent_id.'"
									style="color: inherit">
									<div class="col-xs-6 col-sm-7 col-md-6 panel-header-stgbez '.($lastInteressentenStatus->bewerbung_abgeschicktamum == '' && $fristAbgelaufen ? 'text-muted' : '').'">
										'.$stgBeschriftungPanel.'
									</div>
								</a>';
			// Abschicken nur möglich, wenn die Frist nicht abgelaufen ist und die Bewerbung noch nicht geschickt wurde
			// Ansonsten Statusinfo anzeigen
			if ($buttonAbschickenEnabled)
			{
				echo '				<div class="col-xs-4 col-sm-3 col-md-5 text-right action-buttons">
									<button class="btn-nav btn btn-sm btn-success '.($buttonAbschickenEnabled ? '' : 'disabled hidden').'" 
										type="button"
										data-toggle="modal"
										data-target="#abschickenModal_'.$row->prestudent_id.'"
										style="vertical-align: top">
										<span class="glyphicon glyphicon-send hidden-md hidden-lg"></span>
										<span class="hidden-sm hidden-xs">'.$p->t('bewerbung/bewerbungAbschicken').'</span>
									</button>
									<button class="btn-nav btn btn-sm btn-warning '.($buttonStornierenEnabled ? '' : 'disabled hidden').'" 
										type="button"
										data-toggle="modal"
										data-target="#stornierenModalNeu_'.$row->prestudent_id.'"
										style="vertical-align: top">
										<span class="glyphicon glyphicon-remove hidden-md hidden-lg"></span>
										<span class="hidden-sm hidden-xs">'.$p->t('bewerbung/bewerbungStornieren').'</span>
									</button>
								</div>';
			}
			else
			{
				echo '				<div class="col-xs-4 col-sm-3 col-md-5 text-right action-buttons">';
				if ($lastInteressentenStatus->bewerbung_abgeschicktamum != '' || $lastInteressentenStatus->bestaetigtam != '')
				{
					echo '	<div class="label label-info hidden-md hidden-lg bg-danger"><span class="glyphicon glyphicon-info-sign hidden-md hidden-lg"></div>';
					echo '	<div class="label label-info hidden-sm hidden-xs">'.$p->t('bewerbung/BewerbungBereitsVerschickt').'</div>';
				}
				elseif ($bewerbungszeitraum['frist_abgelaufen'] == true)
				{
					echo '	<div class="label label-danger hidden-md hidden-lg"><span class="glyphicon glyphicon-alert hidden-md hidden-lg"></div>';
					echo '	<div class="label label-danger hidden-sm hidden-xs">
								'.$p->t('bewerbung/bewerbungsfristFuerStudiensemesterXAbgelaufen', array($lastInteressentenStatus->studiensemester_kurzbz)).'
							</div>';
				}
				echo '				</div>';
			}
			echo '				</div>
							<div class="row">
								<a 	data-toggle="collapse" 
									data-parent="#accordionBewerbungen'.$row->laststatus_studiensemester_kurzbz.'" 
									href="#panelCollapse'.$row->prestudent_id.'"
									style="color: inherit">
									<div class="col-xs-12 text-center">
										<span class="glyphicon glyphicon-chevron-down text-muted"></span>
										<span class="text-muted small">'.$p->t('bewerbung/details').'</span>
									</div>
								</a>
							</div>
						</h4>
					</div>';
			echo '		<div id="panelCollapse'.$row->prestudent_id.'" class="panel-collapse collapse">
						<div class="panel-body">
							<div class="row">
								<div class="col-xs-12">
									<form class="form-horizontal">';
			// Status anzeigen
			if ($lastInteressentenStatus->bewerbung_abgeschicktamum != '' || $lastInteressentenStatus->bestaetigtam != '')
			{
				echo '	<div class="alert alert-info">'.$p->t('bewerbung/BewerbungBereitsVerschickt').'</div>';
			}
			elseif ($bewerbungszeitraum['frist_abgelaufen'] == true)
			{
				echo '	<div class="" style="display: table; margin-right: auto; margin-left: auto; padding-bottom: 15px;">
							<p class="alert alert-danger">'.$p->t('bewerbung/bewerbungsfristFuerStudiensemesterXAbgelaufen', array($lastInteressentenStatus->studiensemester_kurzbz)).'</p>
						</div>';
			}
			else
			{
				echo '					<div class="" style="display: table; margin-right: auto; margin-left: auto; padding-bottom: 15px;">';
											if (!$status_person)
												echo '<p class="alert alert-danger">'.$p->t('bewerbung/menuPersDaten').' '.$p->t('bewerbung/unvollstaendig').'</p>';
											if (!$status_kontakt)
												echo '<p class="alert alert-danger">'.$p->t('bewerbung/menuKontaktinformationen').' '.$p->t('bewerbung/unvollstaendig').'</p>';
											if (!$status_zahlungen)
												echo '<p class="alert alert-danger">'.$p->t('bewerbung/menuZahlungen').' '.$p->t('bewerbung/unvollstaendig').'</p>';
											if (!$status_zgv_bak)
												echo '<p class="alert alert-danger">'.$p->t('bewerbung/menuZugangsvoraussetzungen').' '.$p->t('bewerbung/unvollstaendig').'</p>';
											if (!$status_ausbildung)
												echo '<p class="alert alert-danger">'.$p->t('bewerbung/menuAusbildung').' '.$p->t('bewerbung/unvollstaendig').'</p>';
											//Derzeit bei keiner FH benötigt
											//if (!$status_reihungstest)
												//echo '<p class="alert alert-danger">'.$p->t('bewerbung/menuReihungstest').' '.$p->t('bewerbung/unvollstaendig').'</p>';

											// Wenn für dieses Stufe alle Dokumente abgebeben sind, wird nochmal für Dokumente ohne Stufe gecheckt
											if (!empty($status_dokumente_arr[$row->studiengang_kz][$stufe]))
												echo '<p class="alert alert-danger">'.$p->t('bewerbung/menuDokumente').' '.$p->t('bewerbung/unvollstaendig').'</p>';
											elseif (!empty($status_dokumente_arr[$row->studiengang_kz]['']))
												echo '<p class="alert alert-danger">'.$p->t('bewerbung/menuDokumente').' '.$p->t('bewerbung/unvollstaendig').'</p>';
				echo '					</div>';
			}
			echo '						<div class="" style="display: table; margin-right: auto; margin-left: auto; padding-bottom: 15px;">
											<button class="btn-nav btn btn-sm btn-success '.($buttonAbschickenEnabled ? '' : 'disabled hidden').'" type="button"
												data-toggle="modal"
												data-target="#abschickenModal_'.$row->prestudent_id.'">
												<span class="">'.$p->t('bewerbung/bewerbungAbschicken').'</span>
											</button>
											<button class="btn-nav btn btn-sm btn-warning '.($buttonStornierenEnabled ? '' : 'disabled hidden').'" type="button"
												data-toggle="modal"
												data-target="#stornierenModalNeu_'.$row->prestudent_id.'">
												<span class="">'.$p->t('bewerbung/bewerbungStornieren').'</span>
											</button>
										</div>
										<div class="form-group">
											<label for="empfaenger" class="col-sm-3 col-md-5 text-right">'.$p->t('bewerbung/kontakt').':</label>
											<div class="col-sm-9 col-md-7" id="empfaenger">
												<a href="mailto:'.$empfaenger.'"><span
													class="glyphicon glyphicon-envelope"></span>&nbsp;'.$empfaenger.'</a>
											</div>
										</div>
										<div class="form-group">
											<label for="status" class="col-sm-3 col-md-5 text-right">'.$p->t('bewerbung/status').':</label>
											<div class="col-sm-9 col-md-7" id="status">'.$prestatus_help.'</div>
										</div>
										<!--<div class="form-group">
											<label for="datum" class="col-sm-3 col-md-5 text-right">'.$p->t('global/datum').':</label>
											<div class="col-sm-9 col-md-7" id="datum">'.$datum->formatDatum($prestudent_status->datum, 'd.m.Y').'</div>
										</div>-->
										<div class="form-group">
											<label for="zeitraum" class="col-sm-3 col-md-5 text-right">'.$p->t('bewerbung/bewerbungszeitraumFuer', array(($sprache == 'English' ? $nation->engltext : $nation->langtext))).':
											</label>
											<div class="col-sm-9 col-md-7" id="zeitraum">'.$bewerbungszeitraum['bewerbungszeitraum'].'</div>
										</div>
										<div class="form-group">
											<label for="notiz" class="col-sm-3 col-md-5 text-right">'.$p->t('bewerbung/anmerkung').':</label>
											<div class="col-sm-9 col-md-7" id="notizen_'.$row->prestudent_id.'">
										';

			// Zeige Notizen an
			$notiz = new notiz;
			$notiz->getBewerbungstoolNotizen($person_id, $row->prestudent_id);
			$count_notizen = 0;
			if (count($notiz->result))
			{
				foreach ($notiz->result as $note)
				{
					if ($note->insertvon == 'online_notiz')
					{
						$count_notizen++;
						echo '	<div><b>'.date('d.m.Y', strtotime($note->insertamum)).'</b><br>'.htmlspecialchars($note->text).'</div>';
					}
				}
			}
			if (!defined('BEWERBERTOOL_ABSCHICKEN_ANMERKUNG') || BEWERBERTOOL_ABSCHICKEN_ANMERKUNG)
			{
				if ($count_notizen == 0)
				{
					// Wenn Bewerbung schon abgeschickt wurde, anmerkung disablen
					if ($lastInteressentenStatus->bewerbung_abgeschicktamum != '' || $lastInteressentenStatus->bestaetigtam != '')
					{
						$anmerkungDisabled = 'disabled';
					}
					else
					{
						$anmerkungDisabled = '';
					}
					echo '	<div id="notizForm_'.$row->prestudent_id.'">
														<textarea   class="form-control" 
																	name="anmerkung" 
																	style="resize:none" 
																	rows="3" 
																	maxlength="1024" 
																	id="anmerkungUebersicht_'.$row->prestudent_id.'" 
																	style="width:80%" 
																	placeholder="'.$p->t('bewerbung/anmerkungPlaceholder').'" 
																	onInput="zeichenCountdown(\'anmerkungUebersicht_'.$row->prestudent_id.'\',1024)"
																	'.$anmerkungDisabled.'></textarea>
														<span   class="btn btn-primary '.$anmerkungDisabled.'" 
																id="anmerkungSubmitButton" 
																'.($anmerkungDisabled != '' ? '' : 'onclick="saveNotiz('.$person_id.','.$row->prestudent_id.')"').'>'.$p->t('global/speichern').'</span>
														<span style="color: grey; display: inline-block; width: 30px;" id="countdown_anmerkungUebersicht_'.$row->prestudent_id.'"></span>
													</div>';
				}
			}
			echo '			</div>
										</div>';
			echo '		</form></div>
							</div>
						</div>
					</div>';

			// Wenn stornieren möglich, Modal anzeigen
			if ($buttonStornierenEnabled)
			{
				echo '	<div class="modal fade"
						id="stornierenModalNeu_'.$row->prestudent_id.'"
						tabindex="-1"
						role="dialog"
						aria-labelledby="stornierenModalNeuLabel"
						aria-hidden="true">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal"
										aria-hidden="true">&times;</button>
									<h4 class="modal-title" id="stornierenModalNeuLabel">
												'.$p->t('bewerbung/bewerbungStornieren').'</h4>
								</div>
								<div class="modal-body">
											'.$p->t('bewerbung/bewerbungStornierenInfotext', array($lastInteressentenStatus->studiensemester_kurzbz, $stgBeschriftungPanel)).'
										</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-default" data-dismiss="modal">'.$p->t('global/abbrechen').'
											</button>
										
									<button type="button" class="btn btn-warning"
										onclick="bewerbungStornieren(\''.$row->prestudent_id.'\',\''.$lastInteressentenStatus->studiensemester_kurzbz.'\')">
													'.$p->t('bewerbung/bewerbungStornierenBestaetigen').'
												</button>
				
								</div>
							</div>
						</div>
					</div>';
			}

			// Wenn abschicken möglich, Abschicken-Modal anzeigen
			if (!$fristAbgelaufen)
			{
				echo '	<div class="modal fade"
						id="abschickenModal_'.$row->prestudent_id.'"
						tabindex="-1"
						role="dialog"
						aria-labelledby="abschickenModalLabel"
						aria-hidden="true">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal"
										aria-hidden="true">&times;</button>
									<h4 class="modal-title">'.$p->t('bewerbung/buttonBewerbungAbschicken').'</h4>
								</div>
								<div class="modal-body">
											'.$p->t('bewerbung/erklaerungBewerbungAbschickenFuerStudiengang', array($stgBeschriftungPanel)).'
										</div>
								<div class="modal-footer">';
				if ($disabledAbschicken)
				{
					echo '				<div class="alert alert-info">'.$p->t('bewerbung/vervollstaendigenSieIhreDaten').'</div>
									<button type="button" class="btn btn-default" data-dismiss="modal">'.$p->t('global/abbrechen').'</button>
									<button class="btn btn-success disabled" type="button">
										'.$p->t('bewerbung/buttonBewerbungAbschicken').'
									</button>';
				}
				else
				{
					echo '				<form method="POST"  action="'.$_SERVER['PHP_SELF'].'?active=uebersicht">
										<button type="button" class="btn btn-default" data-dismiss="modal">'.$p->t('global/abbrechen').'</button>
										<button class="btn btn-success" type="submit" name="btn_bewerbung_abschicken">
												'.$p->t('bewerbung/buttonBewerbungAbschicken').'
										</button>
										<input type="hidden" name="prestudent_id" value="'.$row->prestudent_id.'">
									</form>';
				}
				echo '				</div>
							</div>
						</div>
					</div>';
			}

			echo '	</div>';

		}
		echo '</div>';
	}

	?>

	<?php
	if ($prestudent = getBewerbungen($person_id, false))
		:
		?>

		<!-- Zeige Status der abgelaufenen Bewerbungen an -->
		<p>
			<b><?php echo $p->t('bewerbung/vergangeneBewerbungen'); ?></b>
		</p>
		<div class="table-responsive" style="color: grey">
			<table class="table">
				<thead>
				<tr>
					<th><?php echo $p->t('global/studiengang'); ?></th>
					<th><?php echo $p->t('bewerbung/kontakt'); ?></th>
					<th><?php echo $p->t('global/datum'); ?></th>
					<th><?php echo $p->t('bewerbung/bewerbungStorniert'); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php
				foreach ($prestudent as $row)
					:
					$stg = new studiengang();
					if (!$stg->load($row->studiengang_kz))
					{
						die($p->t('global/fehlerBeimLadenDesDatensatzes'));
					}

					$prestudent_status = new prestudent();
					$prestatus_help = ($prestudent_status->getLastStatus($row->prestudent_id)) ? $prestudent_status->status_mehrsprachig[$sprache] : $p->t('bewerbung/keinStatus');

					// Statusgrund anzeigen
					if ($prestudent_status->statusgrund_id != '')
					{
						$statusgrund = new statusgrund($prestudent_status->statusgrund_id);
						$bewerberstatus = $statusgrund->bezeichnung_mehrsprachig[$sprache];
					}
					else
					{
						$bewerberstatus = '';
					}

					$bereits_angemeldet[$prestudent_status->studiensemester_kurzbz][] = $stg->studiengang_kz;

					// Zaehlt die Anzahl an Bewerbungen in einem Studiensemester
					// Wenn ein Status Abgewiesen oder Abbrecher ist, zaehlt er nicht zu der Anzahl an Bewerbungen mit
					if (in_array($prestudent_status->studiensemester_kurzbz, $stsem_bewerbung_arr) && $prestudent_status->status_kurzbz != 'Abgewiesener' && $prestudent_status->status_kurzbz != 'Abbrecher')
					{
						if (!array_key_exists($prestudent_status->studiensemester_kurzbz, $anzahl_studiengaenge))
						{
							$anzahl_studiengaenge[$prestudent_status->studiensemester_kurzbz] = 0;
						}

						$anzahl_studiengaenge[$prestudent_status->studiensemester_kurzbz]++;

						if ($row->studiengang_kz > 0 && $row->studiengang_kz < 10000)
						{
							$studiengaengeBaMa[$prestudent_status->studiensemester_kurzbz][] = $row->studiengang_kz;
						}
					}

					// Bezeichnung des Studiengangs über den Studienplan laden wenn vorhanden. Sonst Fallback auf Name des Studiengangs
					if (isset($prestudent_status->studienplan_id) && $prestudent_status->studienplan_id != '')
					{
						$studienordnung = new studienordnung();
						$studienordnung->getStudienordnungFromStudienplan($prestudent_status->studienplan_id);
						if ($sprache != 'German')
						{
							if ($studienordnung->studiengangbezeichnung_englisch != '')
							{
								$stg_bezeichnung = $studienordnung->studiengangbezeichnung_englisch;
							}
							else
							{
								$stg_bezeichnung = $stg->english;
							}
						}
						else
						{
							if ($studienordnung->studiengangbezeichnung != '')
							{
								$stg_bezeichnung = $studienordnung->studiengangbezeichnung;
							}
							else
							{
								$stg_bezeichnung = $stg->bezeichnung;
							}
						}
					}
					else
					{
						if ($sprache != 'German' && $stg->english != '')
						{
							$stg_bezeichnung = $stg->english;
						}
						else
						{
							$stg_bezeichnung = $stg->bezeichnung;
						}
					}

					$typ = new studiengang();
					$typ->getStudiengangTyp($stg->typ);

					$empf_array = array();
					if (defined('BEWERBERTOOL_BEWERBUNG_EMPFAENGER'))
					{
						$empf_array = unserialize(BEWERBERTOOL_BEWERBUNG_EMPFAENGER);
					}

					$orgform = new organisationsform();
					$orgform->load($prestudent_status->orgform_kurzbz);

					// An der FHTW werden alle Mails von Bachelor-Studiengängen an das Infocenter geschickt, solange die Bewerbung noch nicht bestätigt wurde
					if (CAMPUS_NAME == 'FH Technikum Wien')
					{
						if (defined('BEWERBERTOOL_MAILEMPFANG') && BEWERBERTOOL_MAILEMPFANG != '' && $stg->typ == 'b' && $prestudent_status->bestaetigtam == '')
						{
							$empfaenger = BEWERBERTOOL_MAILEMPFANG;
						}
						else
						{
							$empfaenger = getMailEmpfaenger($stg->studiengang_kz, $prestudent_status->studienplan_id);
						}
					}
					else
					{
						$empfaenger = getMailEmpfaenger($stg->studiengang_kz);
					}

					?>
					<tr>
						<td><?php
							echo $typ->bezeichnung.' '.$stg_bezeichnung.($orgform->bezeichnung != '' ? ' ('.$orgform->bezeichnung.')' : '');
							?></td>
						<td><a href="mailto:<?php echo $empfaenger ?>"><span
										class="glyphicon glyphicon-envelope"></span></a></td>
						<td><?php echo $datum->formatDatum($prestudent_status->datum, 'd.m.Y') ?></td>
						<td><?php echo $bewerberstatus ?></td>
					</tr>

				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
	<br>
	<?php
	// Zeige mögliche Studierendendaten an, wenn vorhanden
	$benutzer = new benutzer();
	$benutzer->getBenutzerFromPerson($person_id, true);
	if (count($benutzer->result) > 0)
	{
		echo '<p><b>'.$p->t('bewerbung/studierendenDaten').'</b></p>';
		echo '<div class="table-responsive">
						<table class="table">
							<thead>
							<tr>
								<th>'.$p->t('global/studiengang').'</th>
								<th>'.$p->t('bewerbung/status').'</th>
								<th>'.$p->t('global/datum').'</th>
							</tr>
						</thead>
						<tbody>';
		foreach ($benutzer->result as $ben)
		{
			$student = new student($ben->uid);
			if ($student)
			{
				$stg = new studiengang();
				$stg->load($student->studiengang_kz);

				$typ = new studiengang();
				$typ->getStudiengangTyp($stg->typ);

				$prestudent_status = new prestudent();
				$last_prestudentstatus = ($prestudent_status->getLastStatus($student->prestudent_id)) ? $prestudent_status->status_mehrsprachig[$sprache] : $p->t('bewerbung/keinStatus');

				$orgform = new organisationsform();
				$orgform->load($prestudent_status->orgform_kurzbz);

				// Bezeichnung des Studiengangs über den Studienplan laden wenn vorhanden. Sonst Fallback auf Name des Studiengangs
				if (isset($prestudent_status->studienplan_id) && $prestudent_status->studienplan_id != '')
				{
					$studienordnung = new studienordnung();
					$studienordnung->getStudienordnungFromStudienplan($prestudent_status->studienplan_id);
					if ($sprache != 'German')
					{
						if ($studienordnung->studiengangbezeichnung_englisch != '')
						{
							$stg_bezeichnung = $studienordnung->studiengangbezeichnung_englisch;
						}
						else
						{
							$stg_bezeichnung = $stg->english;
						}
					}
					else
					{
						if ($studienordnung->studiengangbezeichnung != '')
						{
							$stg_bezeichnung = $studienordnung->studiengangbezeichnung;
						}
						else
						{
							$stg_bezeichnung = $stg->bezeichnung;
						}
					}
				}
				else
				{
					if ($sprache != 'German' && $stg->english != '')
					{
						$stg_bezeichnung = $stg->english;
					}
					else
					{
						$stg_bezeichnung = $stg->bezeichnung;
					}
				}

				$empfaenger = getMailEmpfaenger($stg->studiengang_kz, $prestudent_status->studienplan_id);

				echo '<tr>
						<td><a href="mailto:'.$empfaenger.'"><span class="glyphicon glyphicon-envelope"></span>  '.$typ->bezeichnung.' '.$stg_bezeichnung.($orgform->bezeichnung != '' ? ' ('.$orgform->bezeichnung.')' : '').'</a></td>
						<td>'.$last_prestudentstatus.' '.$prestudent_status->ausbildungssemester.'. Semester ('.$prestudent_status->studiensemester_kurzbz.')</td>
						<td>'.$datum->formatDatum($prestudent_status->datum, 'd.m.Y').'</td>
					</tr>';
			}
		}
		echo '</tbody></table></div><br>';
	}

	?>
	<button class="btn-nav btn btn-default" type="button"
	        data-jump-tab="<?php echo $tabs[array_search('uebersicht', $tabs) + 1] ?>">
		<?php echo $p->t('bewerbung/weiter'); ?>
	</button>
	<br/>
	<br/>
	<div class="modal fade" id="modal-studiengaenge">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close cancel-studiengang"
					        data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
					<h4 class="modal-title"><?php echo $p->t('bewerbung/neuerStudiengang') ?></h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label for="studiensemester_kurzbz" class="control-label">
							<?php echo $p->t('bewerbung/geplanterStudienbeginn') ?>
						</label>
						<select id="studiensemester_kurzbz" name="studiensemester_kurzbz"
						        class="form-control">
							<option value=""><?php echo $p->t('bewerbung/bitteAuswaehlen') ?></option>
							<?php
							foreach ($stsem->studiensemester as $row)
							{
								echo '<option value="'.$row->studiensemester_kurzbz.'">'.$stsem->convert_html_chars($row->bezeichnung).' ('.$p->t('bewerbung/ab').' '.$datum->formatDatum($stsem->convert_html_chars($row->start), 'M. Y').')</option>';
							}
							?>
						</select>
					</div>
					<div class="loaderIcon center-block" style="display: none"></div>
					<div id="form-group-stg" class="form-group">

					</div><!-- B -->
				</div><!-- C -->
				<div class="modal-footer">
					<button class="btn btn-default cancel-studiengang"
					        data-dismiss="modal"><?php echo $p->t('global/abbrechen') ?></button>
					<button class="btn btn-primary ok-studiengang"
					        data-dismiss="modal"><?php echo $p->t('global/ok') ?></button>
				</div>
			</div>
		</div>
	</div>

	<!-- Liste der Studiengänge. Diese wird mit Ajax in die "form-group-stg" nachgeladen, wenn das Studiensemester geändert wird -->

	<div class="" id="liste-studiengaenge" style="display: none">
		<?php

		//Umbau auf Studienpläne (ohne Modal für Orgformen) und Priorisierung
		$std_semester = filter_input(INPUT_POST, 'studiensemester_kurzbz');
		if ($std_semester == '' && isset($stsem_bewerbung_arr[0]))
		{
			$std_semester = $stsem_bewerbung_arr[0];
		}
		$studiensemester_array[] = $std_semester;

		// Zuerst sollen Bachelor- und Master-Studiengänge angezeigt werden, danach alle Anderen
		if ($sprache == DEFAULT_LANGUAGE)
		{
			$order = "	CASE tbl_studiengang.typ
						WHEN 'b' THEN 1
						WHEN 'm' THEN 2
						ELSE 3
					END, 
					CASE lgartcode
						WHEN '1'
							THEN 1
						WHEN '2'
							THEN 2
						WHEN '4'
							THEN 3
					ELSE 4
					END,
					studiengangbezeichnung";
		}
		else
		{
			$order = "	CASE tbl_studiengang.typ
						WHEN 'b' THEN 1
						WHEN 'm' THEN 2
						ELSE 3
					END,
					CASE lgartcode
						WHEN '1'
							THEN 1
						WHEN '2'
							THEN 2
						WHEN '4'
							THEN 3
						ELSE 4
					END,
					studiengangbezeichnung_englisch";
		}

		$studienplan = getStudienplaeneForOnlinebewerbung($studiensemester_array, '1', '', $order); //@todo: ausbildungssemester dynamisch
		$lasttyp = '';
		$last_lgtyp = '';
		$bewerbungszeitraum = '';
		$typ_bezeichung = '';

		// Wenn es gar keine Studiengänge/Lehrgänge zum gewählten Studiensemester gibt, Info anzeigen
		if ($studienplan == '')
		{
			echo '<div class="alert alert-info">'.$p->t('bewerbung/keineStudienrichtungenFuerStudiensemesterZurAuswahl').'</div>';
		}
		else
		{
			foreach ($studienplan as $row)
			{
				if ($lasttyp != $row->typ)
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

					if ($lasttyp != '')
					{
						echo '</div></div></div>';
					}

					echo '<div class="panel-group"><div class="panel panel-default">';
					echo '	<div class="panel-heading">
						<a href="#'.$row->typ_bezeichnung.'" data-toggle="collapse">
							<h4>'.$typ_bezeichung.'  <small><span class="glyphicon glyphicon-collapse-down"></span></small></h4>
						</a>
						</div><!-- class="panel-heading" Überschrift Panel Studiengangstyp -->';
					echo '<div id="'.$row->typ_bezeichnung.'" class="panel-collapse collapse">';

					if (defined('BEWERBERTOOL_MAX_STUDIENGAENGE')
						&& BEWERBERTOOL_MAX_STUDIENGAENGE != ''
						&& isset($studiengaengeBaMa[$std_semester])
						&& count($studiengaengeBaMa[$std_semester]) >= BEWERBERTOOL_MAX_STUDIENGAENGE
						&& $row->studiengang_kz > 0
						&& $row->studiengang_kz < 10000
						&& $row->typ != 'l')
					{
						echo '<div class="alert alert-warning" name="checkboxInfoDiv">'.$p->t('bewerbung/sieKoennenMaximalXStudiengaengeWaehlen', array(BEWERBERTOOL_MAX_STUDIENGAENGE)).'</div>';
					}

					$lasttyp = $row->typ;
				}

				if ($last_lgtyp != $row->lehrgangsart && $row->lehrgangsart != '')
				{
					echo '<div class="panel-heading"><b>'.$p->t('bewerbung/lehrgangsArt/'.$row->lgartcode).'</b></div>';
					$last_lgtyp = $row->lehrgangsart;
				}

				$checked = '';
				$disabled = '';

				// Checkboxen deaktivieren, wenn BEWERBERTOOL_MAX_STUDIENGAENGE gesetzt ist und mehr als oder genau BEWERBERTOOL_MAX_STUDIENGAENGE uebergeben werden.
				if (defined('BEWERBERTOOL_MAX_STUDIENGAENGE')
					&& BEWERBERTOOL_MAX_STUDIENGAENGE != ''
					&& isset($studiengaengeBaMa[$std_semester])
					&& count($studiengaengeBaMa[$std_semester]) >= BEWERBERTOOL_MAX_STUDIENGAENGE
					&& $row->studiengang_kz > 0
					&& $row->studiengang_kz < 10000)
				{
					$disabled = 'disabled';
				}

				// Wenn es nur einen gueltigen Studienplan gibt, kommt der Name des Studiengangs aus dem Studienplan
				// Wenn der Name des Studiengangs aus dem Studienplan leer ist -> Fallback auf Studiengangsname vom Studiengang
				if ($sprache != 'German' && $row->studiengangbezeichnung_englisch != '')
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
				$organisationsform = new organisationsform($row->orgform_kurzbz);
				$stg_bezeichnung .= ' | <i>'.$organisationsform->bezeichnung_mehrsprachig[$sprache].' - '.$p->t('bewerbung/'.$row->sprache).'</i>';

				// Bewerbungsfristen laden
				// Aktuellste ZGV-Nation suchen. Master > Bachelor
				$zgv_nation = '';
				$pstID = 0;
				$prestudenten = new prestudent();
				$prestudenten->getPrestudenten($person_id);
				foreach ($prestudenten->result as $pst)
				{
					if ($pst->prestudent_id > $pstID)
					{
						if ($pst->zgvmanation != '')
						{
							$zgv_nation = $pst->zgvmanation;
						}
						elseif ($pst->zgvnation != '')
						{
							$zgv_nation = $pst->zgvnation;
						}
						$pstID = $pst->prestudent_id;
					}
				}
				$nation = new nation($zgv_nation);
				$nationengruppe = $nation->nationengruppe_kurzbz;

				if ($nationengruppe == '')
				{
					$nationengruppe = 0;
				}

				$bewerbungszeitraum = getBewerbungszeitraum($row->studiengang_kz, $std_semester, $row->studienplan_id, $nationengruppe);
				$stg_bezeichnung .= ' '.$bewerbungszeitraum['infoDiv'];
				$fristAbgelaufen = $bewerbungszeitraum['frist_abgelaufen'];

				// Wenn es für das gewählte Studiensemester schon eine Bewerbung gibt, kann man sich nicht mehr dafür bewerben
				$disabledExistsPrestudentstatus = '';
				$textMuted = '';
				$prestudent_status = new prestudent();
				if ($prestudent_status->existsPrestudentstatus($person_id, $row->studiengang_kz, $std_semester, null, $row->studienplan_id))
				{
					$disabledExistsPrestudentstatus = 'disabled';
					$textMuted = 'text-muted';
					$stg_bezeichnung .= '<div class="alert alert-warning" style="margin-bottom: 0">'.$p->t('bewerbung/infotextDisabled', array($std_semester)).'</div>';
				}
				else
				{
					$disabledExistsPrestudentstatus = '';
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
					echo '<div class="panel-body">
						<div class="radio '.$disabledExistsPrestudentstatus.'">
							<label class="'.$textMuted.'">
								<input class="'.$class.'" id="checkbox_'.$row->studienplan_id.'" type="radio" name="studienplaene[]" value="'.$row->studienplan_id.'" '.$checked.' '.$disabled.' '.$disabledExistsPrestudentstatus.'>
								'.$stg_bezeichnung;
				}
				else
				{
					echo '<div class="panel-body">
						<div class="radio disabled">
							<label class="text-muted">
								<input class="" type="radio" name="" value="" disabled>
								'.$stg_bezeichnung;
				}

				echo '			</label>
						</div><!-- Radiobutton -->
					</div><!-- Zeile mit Studiengang -->';
			}
		}
		?>
	</div><!-- Ende letztes Panel mit Studiengängen -->
</div><!-- Ende letztes Panel Typ -->
</div><!-- Ende letzter Panel-Group -->


<script type="text/javascript">
	$(function () {
		$('#open-modal-studiengaenge-button').on('click', function () {
			if ($('#studiensemester_kurzbz').val() == "")
				$("#liste-studiengaenge").hide();
			else
				$("#liste-studiengaenge").show(1000);
		});
		$('#modal-studiengaenge button.ok-studiengang').on('click', function () {
			var item = $('#modal-studiengaenge input[name="studienplaene[]"]:checked');
			var studienplan_id = item.val();
			var stsem = $('#studiensemester_kurzbz').val();

			if (undefined == studienplan_id || studienplan_id == '') {
				alert('<?php echo $p->t('bewerbung/bitteEineStudienrichtungWaehlen')?>');
				return false;
			}
			if (stsem == '') {
				alert('<?php echo $p->t('bewerbung/bitteStudienbeginnWaehlen')?>');
				return false;
			}
			saveStudienplan(studienplan_id, stsem);
		});

		$('#studiensemester_kurzbz').change(function () {
			var studiensemester = $('#studiensemester_kurzbz').val();
			$("#liste-studiengaenge").hide();
			$(".loaderIcon").show();

			$("#form-group-stg").load
			(
				document.URL + ' #liste-studiengaenge',
				{studiensemester_kurzbz: studiensemester},
				function () {
					$(".loaderIcon").hide();
					if ($('#studiensemester_kurzbz').val() != "") {
						$("#liste-studiengaenge").show(500);
					}
				}
			);
		});

		//Ersten Up- und letzten Down-Button in der Liste der Bewerbungen disablen
		$(".panel_bewerbungen").each(function () {
			$(this).find("div.panel").first().find(".button_up").prop("disabled", true);
			$(this).find("div.panel").last().find(".button_down").prop("disabled", true);

			// Wenn es jeweils nur eine Zeile gibt (Titelzeile zählt als 1), Sortierbutton ausblenden
			var rowCount = $(this).find("div.panel").length;
			if (rowCount == 1) {
				$(this).find("div.panel").find(".button_down").hide();
				$(this).find("div.panel").find(".button_up").hide();
			}
		});

		//Wenn ausgeklappt, Buttons im Header ausblenden
		$(".panel").each(function () {
			$(this).on('show.bs.collapse', function () {
				$(this).find('.action-buttons').hide();
				$(this).find('.panel-header-stgbez').removeClass('col-xs-6 col-sm-7 col-md-6');
				$(this).find('.panel-header-stgbez').addClass('col-xs-10 col-sm-10 col-md-11');
			}).on('hide.bs.collapse', function () {
				$(this).find('.action-buttons').show();
				$(this).find('.panel-header-stgbez').removeClass('col-xs-10 col-sm-10 col-md-11');
				$(this).find('.panel-header-stgbez').addClass('col-xs-6 col-sm-7 col-md-6');
			})
		});

		// Notiz speichern
		$("#anmerkungSubmitButton").click(function () {
			$("#anmerkungForm").submit();
		});
	});

	function saveStudienplan(studienplan_id, stsem) {
		data = {
			studienplan_id: studienplan_id,
			addStudienplan: true,
			studiensemester: stsem
		};

		$.ajax({
			url: basename,
			data: data,
			type: 'POST',
			dataType: "json",
			success: function (data) {
				if (data.status != 'ok') {
					alert(JSON.stringify(data.msg));
				}
				else
					window.location.href = 'bewerbung.php?active=uebersicht';
			},
			error: function (data) {
				alert(data.msg)
			}
		});
	}

	function bewerbungStornieren(prestudent_id, studiensemester_kurzbz) {
		data = {
			prestudent_id: prestudent_id,
			studiensemester_kurzbz: studiensemester_kurzbz,
			bewerbungStornieren: true
		};

		$.ajax({
			url: basename,
			data: data,
			type: 'POST',
			dataType: "json",
			success: function (data) {
				if (data.status != 'ok')
					alert(data.msg);
				else
					window.location.href = 'bewerbung.php?active=uebersicht';
			},
			error: function (data) {
				alert(data.msg)
			}
		});
	}

	function changePriority(prestudent_id, studiensemester_kurzbz, direction) {
		this_val = $("#panel_" + prestudent_id).find("input").val();
		this_prioIndex = $("#panel_" + prestudent_id).find(".prioIndex").html();

		if (direction == "up") {
			new_val = $("#panel_" + prestudent_id).prev().find("input").val();
			new_prioIndex = $("#panel_" + prestudent_id).prev().find(".prioIndex").html();
			ziel_prestudent_id = $("#panel_" + prestudent_id).prev().data("prestudent_id");
		}
		else {
			new_val = $("#panel_" + prestudent_id).next("div.panel").find("input").val();
			new_prioIndex = $("#panel_" + prestudent_id).next("div.panel").find(".prioIndex").html();
			ziel_prestudent_id = $("#panel_" + prestudent_id).next().data("prestudent_id");
		}

		data = {
			ausgang_prestudent_id: prestudent_id,
			ziel_prestudent_id: ziel_prestudent_id,
			ausgang_prioritaet: this_val,
			ziel_prioritaet: new_val,
			studiensemester_kurzbz: studiensemester_kurzbz,
			changePriority: true
		};

		$.ajax({
			url: basename,
			data: data,
			type: 'POST',
			dataType: "json",
			success: function (data) {
				if (data.status != 'ok') {
					alert(data.msg);
				}
				else {
					if (direction == "up") {
						// Hidden-Value anpassen
						$("#panel_" + prestudent_id).prev("div.panel").find("input").val(this_val);
						$("#panel_" + prestudent_id).find("input").val(new_val);

						// Zeilennnummerierung anpassen
						$("#panel_" + prestudent_id).prev("div.panel").find(".prioIndex").html(this_prioIndex);
						$("#panel_" + prestudent_id).find(".prioIndex").html(new_prioIndex);

						//Zeilen tauschen
						$("#panel_" + prestudent_id).prev("div.panel").before($("#panel_" + prestudent_id));
					}
					else {
						// Hidden-Value anpassen
						$("#panel_" + prestudent_id).next("div.panel").find("input").val(this_val);
						$("#panel_" + prestudent_id).find("input").val(new_val);

						// Zeilennnummerierung anpassen
						$("#panel_" + prestudent_id).next("div.panel").find(".prioIndex").html(this_prioIndex);
						$("#panel_" + prestudent_id).find(".prioIndex").html(new_prioIndex);

						//Zeilen tauschen
						$("#panel_" + prestudent_id).next("div.panel").after($("#panel_" + prestudent_id));
					}

					// Ersten und letzten Sortier-Button disablen
					$(".panel_bewerbungen").find(".button_up").prop("disabled", false);
					$(".panel_bewerbungen").find(".button_down").prop("disabled", false);
					$(".panel_bewerbungen").each(function () {
						$(this).find("div.panel").first().find(".button_up").prop("disabled", true);
						$(this).find("div.panel").last().find(".button_down").prop("disabled", true);
					});
				}
			},
			error: function (data) {
				alert(data.msg)
			}
		});
	}

	function saveNotiz(person_id, prestudent_id) {
		anmerkungstext = $("#anmerkungUebersicht_" + prestudent_id).val();
		data = {
			person_id: person_id,
			prestudent_id: prestudent_id,
			anmerkungstext: anmerkungstext,
			saveNotiz: true
		};

		$.ajax({
			url: basename,
			data: data,
			type: 'POST',
			dataType: "json",
			success: function (data) {
				if (data.status != 'ok') {
					alert(data.msg);
				}
				else {
					$("#notizForm_" + prestudent_id).hide();
					$("#notizen_" + prestudent_id).append("<div><b>" + data.insertamum + "</b><br>" + data.anmerkung + "</div>");
				}
			},
			error: function (data) {
				alert(data.msg)
			}
		});
	}
</script>
</div>
