<?php
/*
 * Copyright (C) 2015 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Authors: Robert Hofer <robert.hofer@technikum-wien.at>
 * 			Manfred Kindl <manfred.kindl@technikum-wien.at>
 */

require_once('../../../config/global.config.inc.php');
require_once('../bewerbung.config.inc.php');
require_once('../../../include/statusgrund.class.php');

if(!isset($person_id))
{
	die($p->t('bewerbung/ungueltigerZugriff'));
}
$studiensemester_array = array();
?>

<div role="tabpanel" class="tab-pane" id="allgemein">
	<h2><?php echo $p->t('bewerbung/menuAllgemein'); ?></h2>
	<?php
	if($_SESSION['bewerbung/user']=='Login')
		echo '<p>'.$p->t('bewerbung/erklaerungStudierende').'</p>';
	else
		echo '<p>'.$p->t('bewerbung/allgemeineErklaerung').'</p>';
	?>

	<br><br>
	
	<!-- Zeige Stati der aktuellen Bewerbungen an -->
	<p><b><?php echo $p->t('bewerbung/aktuelleBewerbungen'); ?></b></p>
	<div class="">
		<table class="table">
			<tr>
				<th><?php echo $p->t('global/studiengang'); ?></th>
				<th><?php echo $p->t('bewerbung/kontakt'); ?></th>
				<th><?php echo $p->t('bewerbung/status'); ?></th>
				<th><?php echo $p->t('global/datum'); ?></th>
				<th><?php echo $p->t('bewerbung/bewerbungsstatus'); ?></th>
				<th><?php echo $p->t('bewerbung/bewerbungszeitraum'); ?></th>
				<th><?php echo $p->t('bewerbung/aktion'); ?></th>
			</tr>
			<?php
			$bereits_angemeldet = array();
			$anzahl_studiengaenge = array();
			$stsem_bewerbung = array();
			$studiengaengeBaMa = array(); // Nur Bachelor oder Master Studiengaenge

			$stsem = new studiensemester();
			$stsem->getStudiensemesterOnlinebewerbung();
			foreach ($stsem->studiensemester as $row)
				$stsem_bewerbung[] = $row->studiensemester_kurzbz;

			if(!$prestudent = getBewerbungen($person_id, true))
			{
				echo '<td class="warning" colspan="7">'.$p->t('bewerbung/keinStatus').'</td>';
			}
			else 
			{
				foreach($prestudent as $row):
					$stg = new studiengang();
					if(!$stg->load($row->studiengang_kz))
						die($p->t('global/fehlerBeimLadenDesDatensatzes'));
	
					$prestudent_status = new prestudent();
					$prestatus_help = ($prestudent_status->getLastStatus($row->prestudent_id))?$prestudent_status->status_mehrsprachig[$sprache]:$p->t('bewerbung/keinStatus');
					
					/* Neuer Versuch Bewerbungsstatus
					 * 
					$bewerberstatus = '<ul style="padding-left: 15px">';
					$bewerberstatus .= '<div class="statusverlauf_top">';
	
					// Bewerbungsstatus anzeigen
					$style = 'style="color: grey"';
	
					// Daten unvollständig
					if ( 
						$status_person == false || 
						$status_kontakt == false || 
						$status_zahlungen == false || 
						$status_reihungstest == false || 
						$status_zgv_bak == false || 
						$status_ausbildung == false
					)
					{
						$bewerberstatus .= '<li>'.$p->t('bewerbung/datenUnvollstaendig').'</li>';
						$style = 'style="color: grey"';
					}
					else 
					{
						$bewerberstatus .= '<li>'.$p->t('bewerbung/datenVollstaendig').' <span class="glyphicon glyphicon-ok"></span></li>';
						$style = '';
					}
					$bewerberstatus .= '</div><div>';
					// Bewerbung abschicken
					if ($prestudent_status->bewerbung_abgeschicktamum == '')
					{
						$bewerberstatus .= '<li '.$style.'>'.$p->t('bewerbung/nichtAbgeschickt').'</li>';
						$style = 'style="color: grey"';
						$bewerberstatus .= '<li '.$style.'>'.$p->t('bewerbung/datenWerdenGeprueft').'</li>';
					}
					else
					{
						$bewerberstatus .= '<li '.$style.'><nobr>'.$p->t('bewerbung/bewerbungAbgeschickt').' <span class="glyphicon glyphicon-ok"></span></nobr></li>';
						if ($prestudent_status->bestaetigtam == '' && $prestudent_status->bestaetigtvon == '')
							$bewerberstatus .= '<li '.$style.'>'.$p->t('bewerbung/datenPruefung').'</li>';
						else 
							$bewerberstatus .= '<li '.$style.'><nobr>'.$p->t('bewerbung/datenPruefung').' <span class="glyphicon glyphicon-ok"></span></nobr></li>';
						$style = 'style="color: grey"';
					}
					$bewerberstatus .= '</div><div class="statusverlauf_bottom">';
					// Status bestätigung
					if ($prestudent_status->bestaetigtam == '' && $prestudent_status->bestaetigtvon == '')
					{
						$bewerberstatus .= '<li '.$style.'>'.$p->t('bewerbung/freigabeAnStudiengang').'</li>';
						$bewerberstatus .= '<li '.$style.'>'.$p->t('bewerbung/kontaktaufnahmeDurchStudiengang').'</li>';
					}
					else 
					{
						$style = '';
						$bewerberstatus .= '<li '.$style.'><nobr>'.$p->t('bewerbung/freigabeAnStudiengang').' <span class="glyphicon glyphicon-ok"></span></nobr></li>';
						$bewerberstatus .= '<li '.$style.'>'.$p->t('bewerbung/kontaktaufnahmeDurchStudiengang').'</li>';
					}
					$bewerberstatus .= '</div>';
					$bewerberstatus .= '</ul>';
					
					
					$bewerberstatus = '<ul style="padding-left: 15px">';
					$bewerberstatus .= '<li>Daten vervollständigen</li>';
					$bewerberstatus .= '<li>Bewerbung abschicken</li>';
					$bewerberstatus .= '<li>Ihre Daten werden geprüft</li>';
					$bewerberstatus .= '<li>Bewerbung wurde an den Studiengang weitergegeben</li>';
					$bewerberstatus .= '</ul>';
					 */
					$bewerberstatus = '<ul style="padding-left: 15px">';
	
					// Bewerbungsstatus anzeigen
					$style = 'style="color: grey"';
	
					// Daten unvollständig
					if ( 
						$status_person == false || 
						$status_kontakt == false || 
						$status_zahlungen == false || 
						$status_reihungstest == false || 
						$status_zgv_bak == false || 
						$status_ausbildung == false
					)
					{
						$bewerberstatus .= '<li>'.$p->t('bewerbung/datenUnvollstaendig').'</li>';
						$style = 'style="color: grey"';
					}
					else 
					{
						$bewerberstatus .= '<li>'.$p->t('bewerbung/datenVollstaendig').' <span class="glyphicon glyphicon-ok"></span></li>';
						$style = '';
					}
					// Bewerbung abschicken
					if ($prestudent_status->bewerbung_abgeschicktamum == '')
					{
						$bewerberstatus .= '<li '.$style.'>'.$p->t('bewerbung/nichtAbgeschickt').'</li>';
						$style = 'style="color: grey"';
						$bewerberstatus .= '<li '.$style.'>'.$p->t('bewerbung/datenWerdenGeprueft').'</li>';
					}
					else
					{
						$bewerberstatus .= '<li '.$style.'><nobr>'.$p->t('bewerbung/bewerbungAbgeschickt').' <span class="glyphicon glyphicon-ok"></span></nobr></li>';
						if ($prestudent_status->bestaetigtam == '' && $prestudent_status->bestaetigtvon == '')
							$bewerberstatus .= '<li '.$style.'>'.$p->t('bewerbung/datenPruefung').'</li>';
						else 
							$bewerberstatus .= '<li '.$style.'><nobr>'.$p->t('bewerbung/datenPruefung').' <span class="glyphicon glyphicon-ok"></span></nobr></li>';
						$style = 'style="color: grey"';
					}
					// Status bestätigung
					if ($prestudent_status->bestaetigtam == '' && $prestudent_status->bestaetigtvon == '')
					{
						$bewerberstatus .= '<li '.$style.'>'.$p->t('bewerbung/freigabeAnStudiengang').'</li>';
						$bewerberstatus .= '<li '.$style.'>'.$p->t('bewerbung/kontaktaufnahmeDurchStudiengang').'</li>';
					}
					else 
					{
						$style = '';
						$bewerberstatus .= '<li '.$style.'><nobr>'.$p->t('bewerbung/freigabeAnStudiengang').' <span class="glyphicon glyphicon-ok"></span></nobr></li>';
						$bewerberstatus .= '<li '.$style.'>'.$p->t('bewerbung/kontaktaufnahmeDurchStudiengang').'</li>';
					}
					
					$bewerberstatus .= '</ul>';
	// 				if ($prestudent_status->bestaetigtam != '' || $prestudent_status->bestaetigtvon != '')
	// 					$bewerberstatus .= '<li>'.$p->t('bewerbung/kontaktaufnahmeDurchStudiengang').'</li>';
	// 				else
	// 					$bewerberstatus .= '<li style="color: grey">'.$p->t('bewerbung/kontaktaufnahmeDurchStudiengang').'</li>';
					
	// 				if ($prestudent_status->bestaetigtam != '' || $prestudent_status->bestaetigtvon != '')
	// 					$bewerberstatus .= '<li style="color: grey">'.$p->t('bewerbung/bestaetigt').'</li>';
	// 				else 
	// 					$bewerberstatus .= '<li>'.$p->t('bewerbung/bestaetigt').'</li>';
	
					$bereits_angemeldet[$prestudent_status->studiensemester_kurzbz][]= $stg->studiengang_kz;
	
					//Zaehlt die Anzahl an Bewerbungen in einem Studiensemester
					// Wenn ein Status Abgewiesen oder Abbrecher ist, zaehlt er nicht zu der Anzahl an Bewerbungen mit
					if (in_array($prestudent_status->studiensemester_kurzbz, $stsem_bewerbung) &&
						$prestudent_status->status_kurzbz != 'Abgewiesener' &&
						$prestudent_status->status_kurzbz != 'Abbrecher')
					{
						if (!array_key_exists($prestudent_status->studiensemester_kurzbz, $anzahl_studiengaenge))
							$anzahl_studiengaenge[$prestudent_status->studiensemester_kurzbz] = 0;
						
						$anzahl_studiengaenge[$prestudent_status->studiensemester_kurzbz] ++;
						
						if ($row->studiengang_kz > 0 && $row->studiengang_kz < 10000)
							$studiengaengeBaMa[$prestudent_status->studiensemester_kurzbz][] = $row->studiengang_kz;
					}
	
					// Bezeichnung des Studiengangs über den Studienplan laden wenn vorhanden
					if (isset($prestudent_status->studienplan_id) && $prestudent_status->studienplan_id != '')
					{
						$studienordnung = new studienordnung();
						$studienordnung->getStudienordnungFromStudienplan($prestudent_status->studienplan_id);
						if ($sprache != 'German' && $studienordnung->studiengangbezeichnung_englisch != '')
							$stg_bezeichnung = $studienordnung->studiengangbezeichnung_englisch;
						else
							$stg_bezeichnung = $studienordnung->studiengangbezeichnung;
					}
					else 
					{
						if($sprache!='German' && $stg->english!='')
							$stg_bezeichnung = $stg->english;
						else
							$stg_bezeichnung = $stg->bezeichnung;
					}
	
					$typ = new studiengang();
					$typ->getStudiengangTyp($stg->typ);
	
					$empf_array = array();
					if(defined('BEWERBERTOOL_BEWERBUNG_EMPFAENGER'))
						$empf_array = unserialize(BEWERBERTOOL_BEWERBUNG_EMPFAENGER);
	
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
						$studienplan_orgform = $prestudent_status->orgform_kurzbz;
					
					// An der FHTW werden alle Mails von Bachelor-Studiengängen an das Infocenter geschickt, solange die Bewerbung noch nicht bestätigt wurde
					if (CAMPUS_NAME == 'FH Technikum Wien')
					{
						if(	defined('BEWERBERTOOL_MAILEMPFANG') && 
							BEWERBERTOOL_MAILEMPFANG != '' && 
							$stg->typ == 'b' && 
							$prestudent_status->bestaetigtam == '')
						{
							$empfaenger = BEWERBERTOOL_MAILEMPFANG;
						}
						else
							$empfaenger = getMailEmpfaenger($stg->studiengang_kz, $prestudent_status->studienplan_id);
					}
					else 
					{
						$empfaenger = getMailEmpfaenger($stg->studiengang_kz);
					}
	
					// Bewerbungsfristen laden
					$bewerbungszeitraum = '';
					
					$tage_bis_fristablauf = '';
					$class = '';
					$bewerbungsfristen = new bewerbungstermin();
					$bewerbungsfristen->getBewerbungstermine($row->studiengang_kz, $prestudent_status->studiensemester_kurzbz, 'insertamum DESC', $prestudent_status->studienplan_id);
	
					if (isset($bewerbungsfristen->result[0]))
					{
						$bewerbungsfristen = $bewerbungsfristen->result[0];
						$bewerbungsbeginn = '';
						if ($bewerbungsfristen->beginn != '')
							$bewerbungsbeginn = $datum->formatDatum($bewerbungsfristen->beginn, 'd.m.Y').' - ';
						else
						{
							if (CAMPUS_NAME == 'FH Technikum Wien')
								$bewerbungsbeginn = '';
							else 
								$bewerbungsbeginn = $p->t('bewerbung/unbegrenzt');
						}
							
						// Wenn Nachfrist gesetzt und das Nachfrist-Datum befuellt ist, gilt die Nachfrist
						// sonst das Endedatum, wenn eines gesetzt ist
						if ($bewerbungsfristen->nachfrist == true && $bewerbungsfristen->nachfrist_ende != '')
						{
							$tage_bis_fristablauf = ((strtotime($bewerbungsfristen->nachfrist_ende) - time())/86400);
							// Wenn die Frist in weniger als 7 Tagen ablaeuft oder vorbei ist, hervorheben
							if ($tage_bis_fristablauf <= 7)
								$class = 'class="alert-warning"';
							if ($tage_bis_fristablauf <= 0)
								$class = 'class="alert-danger"';
							
							$bewerbungszeitraum = $bewerbungsbeginn.'<span '.$class.'>'.$datum->formatDatum($bewerbungsfristen->nachfrist_ende, 'd.m.Y').'</span>';
						}
						elseif ($bewerbungsfristen->ende != '')
						{
							$tage_bis_fristablauf = ((strtotime($bewerbungsfristen->ende) - time())/86400);
							// Wenn die Frist in weniger als 7 Tagen ablaeuft oder vorbei ist, hervorheben
							if ($tage_bis_fristablauf <= 7)
								$class = 'class="alert-warning"';
							if ($tage_bis_fristablauf <= 0)
								$class = 'class="alert-danger"';
							
							$bewerbungszeitraum = $bewerbungsbeginn.'<span '.$class.'>'.$datum->formatDatum($bewerbungsfristen->ende, 'd.m.Y').'</span>';
						}
						elseif ($bewerbungsfristen->beginn != '')
						{
							$bewerbungszeitraum = $bewerbungsbeginn.$p->t('bewerbung/unbegrenzt');
						}
						else 
							$bewerbungszeitraum = $p->t('bewerbung/unbegrenzt');
					}
					else 
						$bewerbungszeitraum = $p->t('bewerbung/unbegrenzt');
					?>
					<tr>
						<td><?php 
							$orgform_alternativ = '';
							$orgform_alt = '';
							// Alternative Orgform parsen falls vorhanden und anzeigen
							if ($prestudent_status->anmerkung != '')
							{
								$orgform_alternativ = strstr($prestudent_status->anmerkung, 'Alt: ');
								if ($orgform_alternativ != '')
									$orgform_alternativ = substr($orgform_alternativ, 5);
							}

							echo $typ->bezeichnung.' '.$stg_bezeichnung;
							if ($studienplan_orgform != '')
							{
								echo ' | <i>'.$p->t('bewerbung/orgform/'.$studienplan_orgform);
								if ($studienplan_sprache != '')
									echo ' - '.$p->t('bewerbung/'.$studienplan_sprache);
								echo '</i>';
								if ($orgform_alternativ != '')
								{
									echo '<br> Alternative Organisationform: <i>'.$p->t('bewerbung/orgform/'.$orgform_alternativ).'</i>';
								}
							}
							//Hinweis zum Fristablauf nur anzeigen, wenn die Bewerbung noch nicht abgeschickt wurde
							if ($prestudent_status->bewerbung_abgeschicktamum == '')
							{
								if ($tage_bis_fristablauf !='' && $tage_bis_fristablauf <= 0)
									echo '<br><div style="display: table-cell" class="label label-danger"><span class="glyphicon glyphicon-warning-sign"></span>&nbsp;&nbsp;'.$p->t('bewerbung/bewerbungsfristAbgelaufen').'</div>';
								elseif ($tage_bis_fristablauf !='' && $tage_bis_fristablauf <= 7 && $tage_bis_fristablauf >= 1)
									echo '<br><div style="display: table-cell" class="label label-warning"><span class="glyphicon glyphicon-warning-sign"></span>&nbsp;&nbsp;'.$p->t('bewerbung/bewerbungsfristEndetInXTagen', array(floor($tage_bis_fristablauf))).'</div>';
								elseif ($tage_bis_fristablauf !='' && $tage_bis_fristablauf <= 1)
									echo '<br><div style="display: table-cell" class="label label-warning"><span class="glyphicon glyphicon-warning-sign"></span>&nbsp;&nbsp;'.$p->t('bewerbung/bewerbungsfristEndetHeute').'</div>';
							}
							?></td>
						<td><a href="mailto:<?php echo $empfaenger ?>"><span class="glyphicon glyphicon-envelope"></span></a></td>
						<td><?php echo $prestatus_help.' ('.$prestudent_status->studiensemester_kurzbz.')' ?></td>
						<td><?php echo $datum->formatDatum($prestudent_status->datum, 'd.m.Y') ?></td>
						<td><?php echo $bewerberstatus ?></td>
						<td><?php echo $bewerbungszeitraum ?></td>
						<td><?php //Stornieren nur moeglich, wenn letzter Status "Interessent" ist oder noch nicht abgeschickt oder bestätigt wurde
							if ($prestudent_status->status_kurzbz == 'Interessent' 
								&& $prestudent_status->bewerbung_abgeschicktamum == ''
								&& $prestudent_status->bestaetigtam == '' 
								&& $prestudent_status->bestaetigtvon == ''): ?>
							<button class="btn-nav btn btn-sm btn-warning" 
									type="button" 
									name="btn_bewerbung_stornieren" 
									data-toggle="modal" 
									data-target="#stornierenModal_<?php echo $row->prestudent_id ?>">
									<?php echo $p->t('bewerbung/bewerbungStornieren'); ?></button>
							<div class="modal fade" id="stornierenModal_<?php echo $row->prestudent_id ?>" tabindex="-1"
								 role="dialog"
								 aria-labelledby="stornierenModalLabel" aria-hidden="true">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<button type="button" class="close"
													data-dismiss="modal"
													aria-hidden="true">&times;
											</button>
											<h4 class="modal-title" id="stornierenModalLabel">
												<?php echo $p->t('bewerbung/bewerbungStornieren'); ?></h4>
										</div>
										<div class="modal-body">
											<?php echo $p->t('bewerbung/bewerbungStornierenInfotext', array($prestudent_status->studiensemester_kurzbz, $typ->bezeichnung.' '.$stg_bezeichnung)); ?>
										</div>
										<div class="modal-footer">
											<button type="button" class="btn btn-default"
													data-dismiss="modal"><?php echo $p->t('global/abbrechen') ?>
											</button>
											
												<button type="button"
														class="btn btn-primary"
														onclick="bewerbungStornieren('<?php echo $row->prestudent_id ?>','<?php echo $prestudent_status->studiensemester_kurzbz ?>')">
													<?php echo $p->t('bewerbung/bewerbungStornierenBestaetigen'); ?>
												</button>
	
										</div>
									</div>
									<!-- /.modal-content -->
								</div>
								<!-- /.modal-dialog -->
							</div>
							<?php else: ?>
							<button class="btn-nav btn btn-sm btn-warning" 
									disabled	
									type="button" 
									title="<?php echo $p->t('bewerbung/buttonStornierenDisabled'); ?>">
									<?php echo $p->t('bewerbung/bewerbungStornieren'); ?></button>
							<?php endif ?>
						</td>
					</tr>
					
				<?php endforeach;
			} ?>
		</table>
	</div>
	<?php
	if($prestudent = getBewerbungen($person_id, false)): ?>
	
	<!-- Zeige Stati der abgelaufenen Bewerbungen an -->
	<p><b><?php echo $p->t('bewerbung/vergangeneBewerbungen'); ?></b></p>
	<div class="" style="color: grey">
		<table class="table">
			<tr>
				<th><?php echo $p->t('global/studiengang'); ?></th>
				<th><?php echo $p->t('bewerbung/kontakt'); ?></th>
				<th><?php echo $p->t('bewerbung/status'); ?></th>
				<th><?php echo $p->t('global/datum'); ?></th>
				<th><?php echo $p->t('bewerbung/bewerbungStorniert'); ?></th>
				<!--<th><?php echo $p->t('bewerbung/bewerbungszeitraum'); ?></th>-->
			</tr>
			<?php
			foreach($prestudent as $row):
				$stg = new studiengang();
				if(!$stg->load($row->studiengang_kz))
					die($p->t('global/fehlerBeimLadenDesDatensatzes'));

				$prestudent_status = new prestudent();
				$prestatus_help = ($prestudent_status->getLastStatus($row->prestudent_id))?$prestudent_status->status_mehrsprachig[$sprache]:$p->t('bewerbung/keinStatus');

				// Statusgrund anzeigen
				if ($prestudent_status->statusgrund_id != '')
				{
					$statusgrund = new statusgrund($prestudent_status->statusgrund_id);
					$bewerberstatus = $statusgrund->bezeichnung_mehrsprachig[$sprache];
				}
				else 
					$bewerberstatus = '';

				$bereits_angemeldet[$prestudent_status->studiensemester_kurzbz][]= $stg->studiengang_kz;

				//Zaehlt die Anzahl an Bewerbungen in einem Studiensemester
				// Wenn ein Status Abgewiesen oder Abbrecher ist, zaehlt er nicht zu der Anzahl an Bewerbungen mit
				if (in_array($prestudent_status->studiensemester_kurzbz, $stsem_bewerbung) &&
					$prestudent_status->status_kurzbz != 'Abgewiesener' &&
					$prestudent_status->status_kurzbz != 'Abbrecher')
				{
					if (!array_key_exists($prestudent_status->studiensemester_kurzbz, $anzahl_studiengaenge))
						$anzahl_studiengaenge[$prestudent_status->studiensemester_kurzbz] = 0;
					
					$anzahl_studiengaenge[$prestudent_status->studiensemester_kurzbz] ++;
					
					if ($row->studiengang_kz > 0 && $row->studiengang_kz < 10000)
						$studiengaengeBaMa[$prestudent_status->studiensemester_kurzbz][] = $row->studiengang_kz;
				}

				// Bezeichnung des Studiengangs über den Studienplan laden wenn vorhanden
				if (isset($prestudent_status->studienplan_id) && $prestudent_status->studienplan_id != '')
				{
					$studienordnung = new studienordnung();
					$studienordnung->getStudienordnungFromStudienplan($prestudent_status->studienplan_id);
					if ($sprache != 'German' && $studienordnung->studiengangbezeichnung_englisch != '')
						$stg_bezeichnung = $studienordnung->studiengangbezeichnung_englisch;
					else
						$stg_bezeichnung = $studienordnung->studiengangbezeichnung;
				}
				else 
				{
					if($sprache!='German' && $stg->english!='')
						$stg_bezeichnung = $stg->english;
					else
						$stg_bezeichnung = $stg->bezeichnung;
				}

				$typ = new studiengang();
				$typ->getStudiengangTyp($stg->typ);

				$empf_array = array();
				if(defined('BEWERBERTOOL_BEWERBUNG_EMPFAENGER'))
					$empf_array = unserialize(BEWERBERTOOL_BEWERBUNG_EMPFAENGER);

				$orgform = new organisationsform();
				$orgform->load($prestudent_status->orgform_kurzbz);
				
				// An der FHTW werden alle Mails von Bachelor-Studiengängen an das Infocenter geschickt, solange die Bewerbung noch nicht bestätigt wurde
				if (CAMPUS_NAME == 'FH Technikum Wien')
				{
					if(	defined('BEWERBERTOOL_MAILEMPFANG') && 
						BEWERBERTOOL_MAILEMPFANG != '' && 
						$stg->typ == 'b' && 
						$prestudent_status->bestaetigtam == '')
					{
						$empfaenger = BEWERBERTOOL_MAILEMPFANG;
					}
					else
						$empfaenger = getMailEmpfaenger($stg->studiengang_kz, $prestudent_status->studienplan_id);
				}
				else 
				{
					$empfaenger = getMailEmpfaenger($stg->studiengang_kz);
				}

				// Bewerbungsfristen laden
				$bewerbungszeitraum = '';
				
				$tage_bis_fristablauf = '';
				$class = '';
				$bewerbungsfristen = new bewerbungstermin();
				$bewerbungsfristen->getBewerbungstermine($row->studiengang_kz, $prestudent_status->studiensemester_kurzbz, 'insertamum DESC', $prestudent_status->studienplan_id);

				if (isset($bewerbungsfristen->result[0]))
				{
					$bewerbungsfristen = $bewerbungsfristen->result[0];
					$bewerbungsbeginn = '';
					if ($bewerbungsfristen->beginn != '')
						$bewerbungsbeginn = $datum->formatDatum($bewerbungsfristen->beginn, 'd.m.Y');
					else
					{
						if (CAMPUS_NAME == 'FH Technikum Wien')
							$bewerbungsbeginn = '';
						else
							$bewerbungsbeginn = $p->t('bewerbung/unbegrenzt');
					}
					// Wenn Nachfrist gesetzt und das Nachfrist-Datum befuellt ist, gilt die Nachfrist
					// sonst das Endedatum, wenn eines gesetzt ist
					if ($bewerbungsfristen->nachfrist == true && $bewerbungsfristen->nachfrist_ende != '')
					{
						$tage_bis_fristablauf = ((strtotime($bewerbungsfristen->nachfrist_ende) - time())/86400);
						// Wenn die Frist in weniger als 7 Tagen ablaeuft oder vorbei ist, hervorheben
						if ($tage_bis_fristablauf <= 7)
							$class = 'class="alert-warning"';
						if ($tage_bis_fristablauf <= 0)
							$class = 'class="alert-danger"';
						
						$bewerbungszeitraum = $bewerbungsbeginn.'<span '.$class.'>'.$datum->formatDatum($bewerbungsfristen->nachfrist_ende, 'd.m.Y').'</span>';
					}
					elseif ($bewerbungsfristen->ende != '')
					{
						$tage_bis_fristablauf = ((strtotime($bewerbungsfristen->ende) - time())/86400);
						// Wenn die Frist in weniger als 7 Tagen ablaeuft oder vorbei ist, hervorheben
						if ($tage_bis_fristablauf <= 7)
							$class = 'class="alert-warning"';
						if ($tage_bis_fristablauf <= 0)
							$class = 'class="alert-danger"';
						
						$bewerbungszeitraum = $bewerbungsbeginn.'<span '.$class.'>'.$datum->formatDatum($bewerbungsfristen->ende, 'd.m.Y').'</span>';
					}
					elseif ($bewerbungsfristen->beginn != '')
					{
						$bewerbungszeitraum = $bewerbungsbeginn.$p->t('bewerbung/unbegrenzt');
					}
					else 
						$bewerbungszeitraum = $p->t('bewerbung/unbegrenzt');
				}
				else 
					$bewerbungszeitraum = $p->t('bewerbung/unbegrenzt');
				?>
				<tr>
					<td><?php 
						echo $typ->bezeichnung.' '.$stg_bezeichnung.($orgform->bezeichnung!=''?' ('.$orgform->bezeichnung.')':'');
						//Hinweis zum Fristablauf nur anzeigen, wenn die Bewerbung noch nicht abgeschickt wurde
						if ($prestudent_status->bewerbung_abgeschicktamum == '')
						{
							if ($tage_bis_fristablauf !='' && $tage_bis_fristablauf <= 0)
								echo '<br><div style="display: table-cell" class="label label-danger"><span class="glyphicon glyphicon-warning-sign"></span>&nbsp;&nbsp;'.$p->t('bewerbung/bewerbungsfristAbgelaufen').'</div>';
							elseif ($tage_bis_fristablauf !='' && $tage_bis_fristablauf <= 7 && $tage_bis_fristablauf >= 1)
								echo '<br><div style="display: table-cell" class="label label-warning"><span class="glyphicon glyphicon-warning-sign"></span>&nbsp;&nbsp;'.$p->t('bewerbung/bewerbungsfristEndetInXTagen', array(floor($tage_bis_fristablauf))).'</div>';
							elseif ($tage_bis_fristablauf !='' && $tage_bis_fristablauf <= 1)
								echo '<br><div style="display: table-cell" class="label label-warning"><span class="glyphicon glyphicon-warning-sign"></span>&nbsp;&nbsp;'.$p->t('bewerbung/bewerbungsfristEndetHeute').'</div>';
						}
						?></td>
					<td><a href="mailto:<?php echo $empfaenger ?>"><span class="glyphicon glyphicon-envelope"></span></a></td>
					<td><?php echo $prestatus_help.' ('.$prestudent_status->studiensemester_kurzbz.')' ?></td>
					<td><?php echo $datum->formatDatum($prestudent_status->datum, 'd.m.Y') ?></td>
					<td><?php echo $bewerberstatus ?></td>
					<!--<td><?php echo $bewerbungszeitraum ?></td>-->
				</tr>
				
			<?php endforeach;?>
		</table>
	</div>
	<?php endif;?>
	<br>
	<?php if (BEWERBERTOOL_MAX_STUDIENGAENGE > 1 || BEWERBERTOOL_MAX_STUDIENGAENGE == ''): ?>
	<button id="open-modal-studiengaenge-button" class="btn-nav btn btn-success" type="button" data-toggle="modal" data-target="#liste-studiengaenge">
		<?php echo $p->t('bewerbung/studiengangHinzufuegen'); ?>
	</button>
	<?php endif; ?>
	<button class="btn-nav btn btn-default" type="button" data-jump-tab="<?php echo $tabs[array_search('allgemein', $tabs)+1] ?>">
		<?php echo $p->t('bewerbung/weiter'); ?>
	</button>
	<br/><br/>
	<div class="modal fade" id="liste-studiengaenge"><div class="modal-dialog"><div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close cancel-studiengang" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
			<h4 class="modal-title"><?php echo $p->t('bewerbung/neuerStudiengang') ?></h4>
		</div>
		<div class="modal-body">
			<div class="form-group">
				<label for="ausbildungstyp" class="control-label">
					<?php echo $p->t('bewerbung/ausbildungstyp') ?>
				</label>
				<div class="dropdown">
					<select id="ausbildungstyp" name="ausbildungstyp" class="form-control">
						<option value="stg"><?php echo $p->t('global/studiengang') ?></option>
						<option value="lehrg"><?php echo ($p->t('bewerbung/hackTypBezeichnungLehrgeange') != '' ? $p->t('bewerbung/hackTypBezeichnungLehrgeange') : $p->t('bewerbung/lehrgang')); ?></option>
					</select>
				</div>
				<label for="studiensemester_kurzbz" class="control-label">
					<?php echo $p->t('bewerbung/geplanterStudienbeginn') ?>
				</label>
				<div class="dropdown">
					<select id="studiensemester_kurzbz" name="studiensemester_kurzbz" class="form-control">
					<option value=""><?php echo $p->t('bewerbung/bitteAuswaehlen') ?></option>
						<?php
						foreach($stsem->studiensemester as $row)
						{
								echo '<option value="'.$row->studiensemester_kurzbz.'">'.$stsem->convert_html_chars($row->bezeichnung).' ('.$p->t('bewerbung/ab').' '.$datum->formatDatum($stsem->convert_html_chars($row->start),'d.m.Y').')</option>';
						}
						?>
					</select>
				</div>
			</div>
			<div id="form-group-stg" class="form-group">
				<?php 
				$std_semester = filter_input(INPUT_POST, 'studiensemester_kurzbz');
				$studiensemester_array[] = $std_semester;
				$orgeinheit = new organisationseinheit();
				$standorte = $orgeinheit->getAllStandorte();
				$optionsStg = null;
				$optionsLehrg = null;
				$options = array();
				$stg = new studiengang();
				$stg->getAllForOnlinebewerbung();

				$stghlp = new studiengang();
				$stghlp->getLehrgangstyp();
				$lgtyparr = array();
				foreach($stghlp->result as $row)
					$lgtyparr[$row->lgartcode]=$row->bezeichnung;

				$last_lgtyp = '';
				$lasttyp = '';
				$bewerbungszeitraum = '';
				foreach($stg->result as $result)
				{
					$typ = new studiengang();
					$typ->getStudiengangTyp($result->typ);
					
					$bezeichnung_studiengang = new studiengang($result->studiengang_kz);

					$typ = new studiengang();
					$typ->getStudiengangTyp($result->typ);

					$studienplan = getStudienplaeneForOnlinebewerbung($result->studiengang_kz, $studiensemester_array, '1', ''); //@todo: ausbildungssemester dynamisch

					$studiensemester = new studiensemester();
					$studiensemester->getPlusMinus(10,1);

					$studiensemester_kurzbz=array();
					foreach($studiensemester->studiensemester AS $row)
						$studiensemester_kurzbz[] .= $row->studiensemester_kurzbz;

					$studienplanIDs = array();
					$modal = false;
					$fristAbgelaufen = false;
					
					// Orgform und Sprache wenn es nur einen Studienplan gibt
					$orgformSingle = '';
					$spracheSingle = '';
					
					// Wenn mindestens ein Studienplan gefunden wird 
					if($studienplan != '')
					{
						foreach ($studienplan as $row)
						{
							$studienplanIDs[] = $row->studienplan_id;
						}
						//$orgformen_sprachen = array_unique($orgformen_sprachen);
						// Wenn mehrere Orgformen oder Sprachen vorhanden sind, wird ein Auswahl-Modal angezeigt
						if(count($studienplanIDs) > 1)
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
							
							$orgformSingle = $studienplan[0]->orgform_kurzbz;
							$spracheSingle = $studienplan[0]->sprache;
							
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
					// Wenn kein Studienplan gefunden wird und es kein Lehrgang ist
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
							$empfaenger = getMailEmpfaenger($stg->studiengang_kz);
						}
							
						$stg_bezeichnung .= '<br><span style="color:orange"><i>'.$p->t('bewerbung/bewerbungDerzeitNichtMoeglich',array($empfaenger)).'</i></span>';
					}
					// Wenn kein gueltiger Studienplan gefunden wird und es ein Lehrgang ist, die Bezeichnung des Lehrgangs laden
					else
					{
						$bezeichnung_studiengang = new studiengang($result->studiengang_kz);
						if($sprache != 'German' && $bezeichnung_studiengang->english != '')
							$stg_bezeichnung = $bezeichnung_studiengang->english;
						else
							$stg_bezeichnung = $bezeichnung_studiengang->bezeichnung;
					}

					$radioBtn = '';
					if($result->typ == 'l' && $last_lgtyp != $result->lehrgangsart && $result->lehrgangsart != '')
					{
						$radioBtn .= '<p style="padding-top: 20px;"><b>'.$p->t('bewerbung/lehrgangsArt/'.$result->lgartcode).'</b></p>';
						$last_lgtyp = $result->lehrgangsart;
					}
					if($result->typ != 'l' && $lasttyp != $result->typ)
					{
						$radioBtn .= '<p style="padding-top: 20px;"><b>'.$result->typ_bezeichnung.'</b></p>';
						$lasttyp = $result->typ;
					}

					if (defined('BEWERBERTOOL_MAX_STUDIENGAENGE') 
							&& BEWERBERTOOL_MAX_STUDIENGAENGE != '' 
							&& isset($studiengaengeBaMa[$std_semester])
							&& count($studiengaengeBaMa[$std_semester]) >= BEWERBERTOOL_MAX_STUDIENGAENGE
							&& $result->studiengang_kz > 0
							&& $result->studiengang_kz < 10000)
						$disabled = 'disabled';
					else
						$disabled = '';
					
					// Wenn es für das gewählte Studiensemester schon eine Bewerbung oder einen Status "Abgewiesen" oder "Abbrecher" gibt,
					// kann man sich nicht mehr dafür bewerben
					$disabledExistsPrestudentstatus = '';
					$prestudent_status = new prestudent();
					if ($prestudent_status->existsPrestudentstatus($person_id, $result->studiengang_kz, $std_semester))
						$disabledExistsPrestudentstatus = 'disabled';
					else 
						$disabledExistsPrestudentstatus = '';
					
					// Wenn disabled Infotext im Titel anzeigen
					$titel = '';
					if ($disabled != '' || $disabledExistsPrestudentstatus != '')
						$titel = 'title="'.$p->t('bewerbung/infotextDisabled', array($std_semester)).'"';
					else 
						$titel = '';
						
					if (!$fristAbgelaufen)
					{
						$radioBtn .= '
						<div class="radio '.$disabled.' '.$disabledExistsPrestudentstatus.'" '.$titel.'>
							<label data-toggle="collapse" data-target="#prio-dropown'.$result->studiengang_kz.'">
								<input '.$disabled.' '.$disabledExistsPrestudentstatus.' type="radio" name="studiengaenge[]" value="'.$result->studiengang_kz.'"
									data-modal="'.$modal.'"
									data-modal-sprache="'.$spracheSingle.'"
									data-modal-orgform="'.$orgformSingle.'">
								'.$stg_bezeichnung;
							$radioBtn .= '<input type="hidden" id="anmerkung'.$result->studiengang_kz.'">
							</label>
						</div>
						';
						if ($modal && $disabled == '' && $disabledExistsPrestudentstatus == '')
						{
							$radioBtn .= '
							<div id="prio-dropown'.$result->studiengang_kz.'" class="collapse">
								<div class="col-sm-12">
									<b>'.$p->t('bewerbung/orgformWaehlen').'</b>
								</div>
								<div class="col-sm-12">
									<p>'.$p->t('bewerbung/orgformBeschreibungstext').'</p>
								</div>
								<div class="row" id="topprio'.$result->studiengang_kz.'">
									<div class="col-sm-12">
										<div class="col-sm-12 priogroup">
											<b>'.$p->t('bewerbung/prioUeberschrifttopprio').'</b>';
											foreach ($studienplanIDs as $studienplan_id)
											{
												$studienplan = new studienplan();
												$studienplan->loadStudienplan($studienplan_id);
												// Bewerbungsfristen laden
												$bewerbungszeitraum = '';
												$bewerbungszeitraum_result = getBewerbungszeitraum($result->studiengang_kz, $std_semester, $studienplan->studienplan_id);
												$bewerbungszeitraum .= ' '.$bewerbungszeitraum_result['bewerbungszeitraum'];
												$fristAbgelaufen = $bewerbungszeitraum_result['frist_abgelaufen'];
												
												if (!$fristAbgelaufen)
												{
													$radioBtn .= '<div class="radio">
																	<label>
																	<input type="radio" name="topprioOrgform" value="'.$studienplan->orgform_kurzbz.'">
																	<input type="hidden" name="topprioSprache" value="'.$studienplan->sprache.'">
																	'.$p->t('bewerbung/orgform/'.$studienplan->orgform_kurzbz).' - '.$p->t('bewerbung/'.$studienplan->sprache).$bewerbungszeitraum.'
																	</label>
																</div>';
												}
												else 
												{
													$radioBtn .= '<div class="radio disabled">
																	<label>
																	<input type="radio" name="topprioOrgform" value="'.$studienplan->orgform_kurzbz.'" disabled>
																	<input type="hidden" name="topprioSprache" value="'.$studienplan->sprache.'" disabled>
																	'.$p->t('bewerbung/orgform/'.$studienplan->orgform_kurzbz).' - '.$p->t('bewerbung/'.$studienplan->sprache).$bewerbungszeitraum.'
																	</label>
																</div>';
												}
											}
											
						$radioBtn .= '	</div>
									</div>
								</div>
								<div class="row" id="alternative'.$result->studiengang_kz.'">
									<div class="col-sm-12">
										<div class="col-sm-12 priogroup">
											<b>'.$p->t('bewerbung/prioUeberschriftalternative').'</b>
											<div class="radio">
												<label>
												<input type="radio" name="alternativeOrgform" value="keine">
												'.$p->t('bewerbung/egal').'
												</label>
											</div>';
											foreach ($studienplanIDs as $studienplan_id)
											{
												$studienplan = new studienplan();
												$studienplan->loadStudienplan($studienplan_id);
												// Bewerbungsfristen laden
												$bewerbungszeitraum = '';
												$bewerbungszeitraum_result = getBewerbungszeitraum($result->studiengang_kz, $std_semester, $studienplan->studienplan_id);
												$bewerbungszeitraum .= ' '.$bewerbungszeitraum_result['bewerbungszeitraum'];
												$fristAbgelaufen = $bewerbungszeitraum_result['frist_abgelaufen'];
												
												if (!$fristAbgelaufen)
												{
													$radioBtn .= '<div class="radio">
																	<label>
																	<input type="radio" name="alternativeOrgform" value="'.$studienplan->orgform_kurzbz.'">
																	<input type="hidden" name="alternativeSprache" value="'.$studienplan->sprache.'">
																	'.$p->t('bewerbung/orgform/'.$studienplan->orgform_kurzbz).' - '.$p->t('bewerbung/'.$studienplan->sprache).$bewerbungszeitraum.'
																	</label>
																</div>';
												}
												else
												{
													$radioBtn .= '<div class="radio disabled">
																	<label>
																	<input type="radio" name="alternativeOrgform" value="'.$studienplan->orgform_kurzbz.'" disabled>
																	<input type="hidden" name="alternativeSprache" value="'.$studienplan->sprache.'" disabled>
																	'.$p->t('bewerbung/orgform/'.$studienplan->orgform_kurzbz).' - '.$p->t('bewerbung/'.$studienplan->sprache).$bewerbungszeitraum.'
																	</label>
																</div>';
												}
											}
							$radioBtn .= '</div>
									</div>
								</div>
							</div>';
						}
					}
					else
					{
						$radioBtn .= '
						<div class="radio disabled">
							<label>
								<input disabled type="radio" name="studiengaenge[]" value="'.$result->studiengang_kz.'"
									data-modal="false"
									data-modal-sprache="'.$spracheSingle.'"
									data-modal-orgform="'.$orgformSingle.'">
								'.$stg_bezeichnung;
							$radioBtn .= '<input type="hidden" id="anmerkung'.$result->studiengang_kz.'">
							</label>
						</div>
						';
					}
				

					if($result->organisationseinheittyp_kurzbz == "Studiengang")
					{
						if(BEWERBERTOOL_STANDORTAUSWAHL_ANZEIGEN)
						{
							if(isset($options["Stg"][$result->standort]))
								$options["Stg"][$result->standort] .= $radioBtn;
							else
								$options["Stg"][$result->standort] = $radioBtn;
						}
						else
						{
							$optionsStg .= $radioBtn;
						}
					}
					else if($result->organisationseinheittyp_kurzbz == "Lehrgang")
					{
						if(BEWERBERTOOL_STANDORTAUSWAHL_ANZEIGEN)
						{
							if(isset($options["Lehrg"][$result->standort]))
								$options["Lehrg"][$result->standort] .= $radioBtn;
							else
								$options["Lehrg"][$result->standort] = $radioBtn;
						}
						else
						{
							$optionsLehrg .= $radioBtn;
						}
					}
				}
				?>
				<label class="control-label">
					<?php echo $p->t('bewerbung/geplanteStudienrichtung') ?>
				</label>

				<?php if(BEWERBERTOOL_STANDORTAUSWAHL_ANZEIGEN): ?>
				<div id="auswahlStg">
					<div class="panel-group" id="accordionStg" role="tablist" aria-multiselectable="true">
						<div class="panel panel-default">

							<?php foreach($standorte as $standort): ?>
							<div class="panel-heading" role="tab" id="heading<?php echo $standort; ?>">
								<h4 class="panel-title">
								  <a class="collapsed" data-toggle="collapse" data-parent="#accordionStg" href="#collapse<?php echo $standort; ?>" aria-expanded="false" aria-controls="collapse<?php echo $standort; ?>">
									<?php echo $standort; ?>
								  </a>
								</h4>
							</div>
							<div id="collapse<?php echo $standort; ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading<?php echo $standort; ?>">
								<div class="panel-body">
									<?php
									if(isset($options["Stg"][$standort]))
										echo $options["Stg"][$standort];
									else
										echo $p->t('bewerbung/keineStgAngeboten');
									?>
								</div>
							</div>
							<?php endforeach; ?>

						</div>
					</div>
				</div>
				<div id="auswahlLehrg" style="display: none;">
					<div class="panel-group" id="accordionLehrg" role="tablist" aria-multiselectable="true">
						<div class="panel panel-default">

							<?php foreach($standorte as $standort): ?>
							<div class="panel-heading" role="tab" id="heading<?php echo $standort; ?>Lehrg">
								<h4 class="panel-title">
								  <a class="collapsed" data-toggle="collapse" data-parent="#accordionLehrg" href="#collapse<?php echo $standort; ?>Lehrg" aria-expanded="false" aria-controls="collapse<?php echo $standort; ?>Lehrg">
									<?php echo $standort; ?>
								  </a>
								</h4>
							</div>
							<div id="collapse<?php echo $standort; ?>Lehrg" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading<?php echo $standort; ?>Lehrg">
								<div class="panel-body">
									<?php
									if(isset($options["Lehrg"][$standort]))
										echo $options["Lehrg"][$standort];
									else
										echo $p->t('bewerbung/keineLehrgAngeboten');
									?>
								</div>
							</div>
							<?php endforeach; ?>

						</div>
					</div>
				</div>

				<?php else: ?>

				<div id="auswahlStg">
				<?php if (defined('BEWERBERTOOL_MAX_STUDIENGAENGE') && BEWERBERTOOL_MAX_STUDIENGAENGE != '' && isset($studiengaengeBaMa[$std_semester]) && count($studiengaengeBaMa[$std_semester]) >= BEWERBERTOOL_MAX_STUDIENGAENGE)
						echo '<p class="alert alert-warning">'.strip_tags($p->t('bewerbung/sieKoennenMaximalXStudiengaengeWaehlen', array(BEWERBERTOOL_MAX_STUDIENGAENGE))).'</p>';

					if(!empty($optionsStg))
						echo $optionsStg;
					else
						echo $p->t('bewerbung/keineStgAngeboten');
					?>
				</div>
				<div id="auswahlLehrg" style="display: none;">
					<?php
					if(!empty($optionsLehrg))
						echo $optionsLehrg;
					else
						echo $p->t('bewerbung/keineLehrgAngeboten');
					?>
				</div>
				<?php endif; ?>

			</div>
		</div>
		<div class="modal-footer">
			<button class="btn btn-default cancel-studiengang" data-dismiss="modal"><?php echo $p->t('global/abbrechen') ?></button>
			<button class="btn btn-primary ok-studiengang" data-dismiss="modal"><?php echo $p->t('global/ok') ?></button>
		</div>
	</div></div></div>
	<?php //require 'modal_sprache_orgform.php'; ?>
	<script type="text/javascript">
		$(function() {
			$('#open-modal-studiengaenge-button').on('click', function()
			{
				if($('#studiensemester_kurzbz').val() == "")
					$("#form-group-stg").hide();
				else
					$("#form-group-stg").show(1000);
			});
			$('#liste-studiengaenge button.ok-studiengang').on('click', function()
			{
				var item = $('#liste-studiengaenge input[name="studiengaenge[]"]:checked');
				var stgkz = item.val();
				var stsem = $('#studiensemester_kurzbz').val();
				var orgform = item.attr('data-modal-orgform');
				var sprache = item.attr('data-modal-sprache');
				var	modal = item.attr('data-modal');

				if (undefined == stgkz || stgkz == '')
				{
					alert('<?php echo $p->t('bewerbung/bitteEineStudienrichtungWaehlen')?>');
					return false;
				}
				if (stsem == '')
				{
					alert('<?php echo $p->t('bewerbung/bitteStudienbeginnWaehlen')?>');
					return false;
				}

				//Wenn es mehrere Orgformen zur Wahl gibt
				if(modal)
				{
					var orgform = $('#topprio'+stgkz+' input[name="topprioOrgform"]:checked').val();
					var sprache = $('#topprio'+stgkz+' input[name="topprioOrgform"]:checked').next().val();

					if(orgform == undefined)
						orgform = '';

					if(sprache == undefined)
						sprache = '';

					if(orgform != '' && sprache != '')
					{
						anm = checkPrios(0, stgkz);
						saveStudiengang(stgkz, anm, stsem, orgform, sprache);
					}
				}
				else
				{
					saveStudiengang(stgkz, '', stsem, orgform, sprache);
					$('#liste-studiengaenge').modal('hide');
				}
			});

			$('#ausbildungstyp').change(function() {
				if($('#ausbildungstyp').val() == "stg") {
					$('#auswahlLehrg').hide();
					$('#auswahlStg').show();
				}
				else {
					$('#auswahlLehrg').show();
					$('#auswahlStg').hide();
				}
			});

			$('#studiensemester_kurzbz').change(function() {
				var studiensemester= $('#studiensemester_kurzbz').val();
				if($('#studiensemester_kurzbz').val() != "")
				{
					$("#form-group-stg").hide();
					$("#form-group-stg").show(1000);
				}
				else
					$("#form-group-stg").hide();
				$("#form-group-stg").load
				(
					document.URL +  ' #form-group-stg', 
					{studiensemester_kurzbz: studiensemester}, 
					function()
					{
						if($('#ausbildungstyp').val() == "stg")
						{
							$('#auswahlLehrg').hide();
							$('#auswahlStg').show();
						}
						else 
						{
							$('#auswahlLehrg').show();
							$('#auswahlStg').hide();
						}
					}
				);
			});

		});
		function saveStudiengang(stgkz, anm, stsem, orgform, sprache)
		{
			data = {
				anm: anm,
				stgkz: stgkz,
				addStudiengang: true,
				studiensemester: stsem,
				orgform: orgform,
				sprache: sprache
			};

			$.ajax({
				url: basename,
				data: data,
				type: 'POST',
				dataType: "json",
				success: function(data)
				{
					if(data.status!='ok')
						alert(data.msg);
					else
						window.location.href='bewerbung.php?active=allgemein';
				},
				error: function(data)
				{
					alert(data.msg)
				}
			});
		}
		function bewerbungStornieren(prestudent_id, studiensemester_kurzbz)
		{
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
				success: function(data)
				{
					if(data.status!='ok')
						alert(data.msg);
					else
						window.location.href='bewerbung.php?active=allgemein';
				},
				error: function(data)
				{
					alert(data.msg)
				}
			});
		}
		
		function checkPrios(slideDuration, stgkz) 
		{
			var anm = 'keine Prio';

			if($('#topprio'+stgkz+' input:checked').length === 0) 
			{
				$('#alternative'+stgkz)
					.addClass('inactive')
					.slideUp(slideDuration);
			}
			else 
			{
				$('#alternative'+stgkz+'.inactive')
					.removeClass('inactive')
					.slideDown(slideDuration);

				anm = 'Prio: ' + $('#topprio'+stgkz+' input[name="topprioOrgform"]:checked').val();

				if($('#alternative'+stgkz+' input:checked').length !== 0) {
					anm += '; Alt: ' + $('#alternative'+stgkz+' input[name="alternativeOrgform"]:checked').val();
				}
			}

			return anm;
		}
	</script>
</div>
