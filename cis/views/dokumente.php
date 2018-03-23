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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * Authors: Robert Hofer <robert.hofer@technikum-wien.at>
 * 			Manfred Kindl <manfred.kindl@technikum-wien.at>
 */
if (! isset($person_id))
{
	die($p->t('bewerbung/ungueltigerZugriff'));
};
?>

<div role="tabpanel" class="tab-pane" id="dokumente">
	<h2><?php echo $p->t('bewerbung/menuDokumente'); ?></h2>
	<p><?php echo $p->t('bewerbung/bitteDokumenteHochladen'); ?></p>
	<a href="dms_akteupload.php?person_id=<?php echo $person_id ?>"
		onclick="FensterOeffnen(this.href); return false;">
		<?php echo $p->t('bewerbung/linkDokumenteHochladen'); ?>
	</a>
	<p><?php echo $p->t('bewerbung/dokumenteZumHochladen'); ?></p>
	<?php
	
	if ($save_error_dokumente === false)
	{
		echo '	<div class="alert alert-success" id="success-alert_daten">
				<button type="button" class="close" data-dismiss="alert">x</button>
					<strong>'.$message.'</strong>
				</div>';
	}
	elseif ($save_error_dokumente === true)
	{
		echo '	<div class="alert alert-danger" id="danger-alert">
				<button type="button" class="close" data-dismiss="alert">x</button>
					<strong>'.$p->t('global/fehleraufgetreten').' </strong>'.$message.'
				</div>';
	}
	
	$db = new basis_db();
	
	// Sortiert die Dokumente je nach Sprache alphabetisch nach bezeichnung_mehrsprachig
	// Pflichtdokumente werden als erstes ausgegeben
	// Gepruefte Dokumente werden nach unten sortiert
	function sortDocuments($a, $b)
	{
		$c = $a->anzahl_akten_formal_geprueft - $b->anzahl_akten_formal_geprueft;
		$c .= $a->anzahl_dokumente_akzeptiert - $b->anzahl_dokumente_akzeptiert;
		$c .= $a->anzahl_akten_vorhanden - $b->anzahl_akten_vorhanden;
		$c .= $b->pflicht - $a->pflicht;
		$c .= strcmp(strtolower($a->bezeichnung_mehrsprachig[getSprache()]), strtolower($b->bezeichnung_mehrsprachig[getSprache()]));
		return $c;
	}
	if ($dokumente_abzugeben)
		usort($dokumente_abzugeben, "sortDocuments");
	
// 	$studiensemester = new studiensemester();
// 	$studiensemester->getStudiensemesterOnlinebewerbung();
// 	$stsem_array = array();
// 	foreach ($studiensemester->studiensemester as $s)
// 		$stsem_array[] = $s->studiensemester_kurzbz;
	?>

	<div class="">
		<table id="document_table" class="table table-striped">
			<thead>
				<tr>
					<th><?php echo $p->t('bewerbung/dokumentName'); ?></th>
<!--					<th><?php echo $p->t('bewerbung/details');?></th>-->
					<th></th>
					<th><?php echo $p->t('bewerbung/dateien'); ?></th>
					<th><?php echo $p->t('global/aktion'); ?></th>
					<th></th>
					<?php 
					// An der FHTW werden die benötigten Studiengänge im ersten Schritt ausgeblendet
					if (CAMPUS_NAME == 'FH Technikum Wien' && !check_person_statusbestaetigt($person_id, 'Interessent', '', ''))
						echo '';
					else 
						echo '<th>'.$p->t('bewerbung/benoetigtFuer').'</th>';
					?>
				</tr>
			</thead>
			<tbody>
		<?php
		if ($dokumente_abzugeben)
		:
			foreach ($dokumente_abzugeben as $dok)
			:
				if ($dok->pflicht === true || check_person_statusbestaetigt($person_id, 'Interessent', '', ''))
				:
					
					// An der FHTW ist das Dokument "Sprachkenntnisse B2" nicht verpflichtend, soll aber im ersten Schritt angezeigt werden
					if (CAMPUS_NAME == 'FH Technikum Wien')
					{
						if ($dok->dokument_kurzbz == 'SprachB2')
							$dok->pflicht = false;
					}
					$beschreibung = '';
					$aktenliste = '';
					$aktion = '';
					$div = '';
					// $zeilenumbruch = '<br>';
					
					// Detailbeschreibungen zu Dokumenten holen
					$details = new dokument();
					$details->getBeschreibungenDokumente($ben_kz[$dok->dokument_kurzbz], $dok->dokument_kurzbz);
					$detailstring_short = array();
					$detailstring_htmlspecialchars = '';
					$detailstring_original = '';
					$zaehlerBeschreibungAllg = 0;
					
					foreach ($details->result as $row)
					{
						$stg = new studiengang();
						$stg->load($row->studiengang_kz);
						
						if ($row->dokumentbeschreibung_mehrsprachig[getSprache()] != '' && $zaehlerBeschreibungAllg == 0)
						{
							// Wenn im dokumentbeschreibung_mehrsprachig ein string "<span style="display: none;">Text<span>" vorkommt,
							// entferne den span-Tag und verwende diesen als $detailstring_short
							$regex_pattern = '#^.*?display: none.*?<\/span>#i';
							if (preg_match($regex_pattern, $row->dokumentbeschreibung_mehrsprachig[getSprache()], $detailstring_short) == 1)
							{
								$detailstring_short = preg_replace('#^<span style="display: none;">#i', '', $detailstring_short);
								$detailstring_short = preg_replace('#<\/span>#i', '', $detailstring_short);
							}
							$detailstring_htmlspecialchars .= htmlspecialchars($row->dokumentbeschreibung_mehrsprachig[getSprache()]);
							$detailstring_original .= $row->dokumentbeschreibung_mehrsprachig[getSprache()];
							
							// Allgemeine Dokumentbeschreibung nur einmal ausgeben
							$zaehlerBeschreibungAllg ++;
						}
						if ($row->beschreibung_mehrsprachig[getSprache()] != '')
						{
							if ($detailstring_htmlspecialchars != '')
							{
								$detailstring_htmlspecialchars .= '<br/><hr/>';
								$detailstring_original .= '<br/><hr/>';
							}
							$detailstring_htmlspecialchars .= '<b>' . $stg->kuerzel . '</b>: ' . htmlspecialchars($row->beschreibung_mehrsprachig[getSprache()]);
							$detailstring_original .= '<b>' . $stg->kuerzel . '</b>: ' . $row->beschreibung_mehrsprachig[getSprache()];
						}
						else
						{
							$detailstring_htmlspecialchars .= '';
							$detailstring_original .= '';
						}
					}
					
					if ($detailstring_htmlspecialchars != '')
						$beschreibung = '<a href="#" 
									class="linkPopover" 
									data-toggle="popover" 
									data-trigger="focus" 
									title="' . $p->t('bewerbung/details') . '" 
									data-content="' . $detailstring_htmlspecialchars . '">' . $p->t('bewerbung/mehrDetails') . '</a>';
					else
						$beschreibung = '';
					
					$akten = new akte();
					$akten->getAkten($person_id, $dok->dokument_kurzbz);
					
					// Wenn mindestens eine Akte vorhanden ist, zeige die Akten mit den Optionen "Löschen" und "Heruterladen"
					if ($dok->anzahl_akten_vorhanden > 0 || (isset($akten->result[0]) && $akten->result[0]->nachgereicht === true))
					{
						// Dokument aus $status_dokumente_arr entfernen, um zu wissen, ob dieser Studiengang abgeschickt werden darf
						foreach ($ben_kz[$dok->dokument_kurzbz] as $kennzahl)
						{
							if (array_key_exists($kennzahl, $status_dokumente_arr))
							{
								unset($status_dokumente_arr[$kennzahl][array_search($dok->dokument_kurzbz, $status_dokumente_arr[$kennzahl])]);
							}
						}
						
						$aktenliste = '<ul class="list-unstyled">';
						foreach ($akten->result as $akte)
						{
							// Wenn mindestens eine Akte im Status "wird nachgereicht" ist, dann Button mit Sanduhr anzeigen.
							// Nachreichen nur moeglich, wenn noch keine Akten vorhanden sind
							if ($akte->nachgereicht === true && $akte->inhalt == '' && $akte->dms_id == '')
							{
								// wird nachgereicht
								$aktion = '	<button type="button" 
												title="' . $p->t('bewerbung/upload') . '" 
												class="btn btn-default" onclick="FensterOeffnen(\'dms_akteupload.php?person_id=' . $person_id . '&dokumenttyp=' . $dok->dokument_kurzbz . '\'); return false;">
											<span class="glyphicon glyphicon-upload" aria-hidden="true" title="' . $p->t('bewerbung/upload') . '"></span>
										</button>';
								$aktenliste .= '<li>
											<span class="glyphicon glyphicon-hourglass" aria-hidden="true" title="' . $p->t('bewerbung/dokumentWirdNachgereicht') . '"></span>
										</li>';
								$div = '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '?active=dokumente">
									<span id="nachgereicht_' . $akte->dokument_kurzbz . '" style="display:true;">
										' . $p->t('bewerbung/wirdNachgreichtAm') . ': ' . $datum->formatDatum($akte->nachgereicht_am, 'd.m.Y') . '<br/>
										' . $p->t('bewerbung/ausstellendeInstitution') . ': ' . $akte->anmerkung . '</span>
								</form>';
							}
							else
							{
								if (akteAkzeptiert($akte->akte_id))
								{
									// Dokument wurde bereits überprüft. Nur Download zur Ansicht oder Upload eines neuen Dokuments (außer Lichtbild) moeglich
									
									// Beim Lichtbild wird aus cis/public/bild.php geladen und nicht aus dem DMS
									if ($akte->dokument_kurzbz == 'Lichtbil')
									{
										$aktion = '';
										$aktenliste .= '<li title="' . $akte->titel . '">
												<span class="glyphicon glyphicon-file" aria-hidden="true"></span>
												  ' . cutString($akte->titel, 25, '...') . '  
												<button type="button" 
														title="' . $p->t('bewerbung/dokumentHerunterladen') . '" 
														class="btn btn-default btn-xs" href="' . APP_ROOT . 'cis/public/bild.php?src=person&person_id=' . $person_id . '" 
														onclick="FensterOeffnen(\'' . APP_ROOT . 'cis/public/bild.php?src=person&person_id=' . $person_id . '\'); return false;">
													<span class="glyphicon glyphicon glyphicon-download-alt" aria-hidden="true" title="' . $p->t('bewerbung/dokumentHerunterladen') . '"></span>
												</button><br>
												<span class="label label-success">' . $p->t('bewerbung/dokumentUeberprueft') . '</span>
											</li>';
									}
									else
									{
										// Auskommentiert, da BewerberInnen vorerst nur EIN Dokument pro Typ hochladen sollen
										/*
										 * $aktion = ' <button type="button"
										 * title="'.$p->t('bewerbung/upload').'"
										 * class="btn btn-default" href="dms_akteupload.php?person_id='.$person_id.'&dokumenttyp='.$dok->dokument_kurzbz.'"
										 * onclick="FensterOeffnen(\'dms_akteupload.php?person_id='.$person_id.'&dokumenttyp='.$dok->dokument_kurzbz.'\'); return false;">
										 * <span class="glyphicon glyphicon-upload" aria-hidden="true" title="'.$p->t('bewerbung/upload').'"></span>
										 * </button>';
										 */
										$aktion = '';
										$aktenliste .= '<li title="' . $akte->titel . '">
												<span class="glyphicon glyphicon-file" aria-hidden="true"></span>
												  ' . cutString($akte->titel, 25, '...') . '  
												<button type="button" 
														title="' . $p->t('bewerbung/dokumentHerunterladen') . '" 
														class="btn btn-default btn-xs" href="' . APP_ROOT . 'cms/dms.php?id=' . $akte->dms_id . '" 
														onclick="FensterOeffnen(\'' . APP_ROOT . 'cms/dms.php?id=' . $akte->dms_id . '&akte_id=' . $akte->akte_id . '\'); return false;">
													<span class="glyphicon glyphicon glyphicon-download-alt" aria-hidden="true" title="' . $p->t('bewerbung/dokumentHerunterladen') . '"></span>
												</button><br>
												<span class="label label-success">' . $p->t('bewerbung/dokumentUeberprueft') . '</span> 
											</li>';
									}
									$div = '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '&active=dokumente">
										<span id="nachgereicht_' . $dok->dokument_kurzbz . '" style="display:none;">wird nachgereicht:
											<input type="checkbox" name="check_nachgereicht">
											<div class="input-group">
												<input type="text" size="15" maxlength="128" name="txt_anmerkung">
												<div class="input-group-btn">												
												<input type="submit" value="OK" name="submit_nachgereicht" class="btn btn-default">
												</div>
											</div>
										</span><input type="hidden" name="dok_kurzbz" value="' . $dok->dokument_kurzbz . '">
									<input type="hidden" name="akte_id" value="' . $akte->akte_id . '"></form>';
								}
								else
								{
									// Dokument hochgeladen ohne Ueberprüfung. Download moeglich, loeschen moeglich, neuer Upload moeglich (außer Lichtbild)
									if ($akte->dokument_kurzbz == 'Lichtbil')
									{
										$aktion = '';
									}
									else
									{
										// Auskommentiert, da BewerberInnen vorerst nur EIN Dokument pro Typ hochladen sollen
										/*
										 * $aktion = ' <button type="button"
										 * title="'.$p->t('bewerbung/upload').'"
										 * class="btn btn-default" href="dms_akteupload.php?person_id='.$person_id.'&dokumenttyp='.$dok->dokument_kurzbz.'"
										 * onclick="FensterOeffnen(\'dms_akteupload.php?person_id='.$person_id.'&dokumenttyp='.$dok->dokument_kurzbz.'\'); return false;">
										 * <span class="glyphicon glyphicon-upload" aria-hidden="true" title="'.$p->t('bewerbung/upload').'"></span>
										 * </button>';
										 */
										$aktion = '';
									}
									$aktenliste .= '<li title="' . $akte->titel . '">
												<span class="glyphicon glyphicon-file" aria-hidden="true"></span>
												  ' . cutString($akte->titel, 25, '...') . '  ';
									// Beim Lichtbild wird aus cis/public/bild.php geladen und nicht aus dem DMS
									if ($akte->dokument_kurzbz == 'Lichtbil')
									{
										$aktenliste .= '<button type="button" 
														title="' . $p->t('bewerbung/dokumentHerunterladen') . '" 
														class="btn btn-default btn-xs" href="' . APP_ROOT . 'cis/public/bild.php?src=person&person_id=' . $person_id . '" 
														onclick="FensterOeffnen(\'' . APP_ROOT . 'cis/public/bild.php?src=person&person_id=' . $person_id . '\'); return false;">
													<span class="glyphicon glyphicon glyphicon-download-alt" aria-hidden="true" title="' . $p->t('bewerbung/dokumentHerunterladen') . '"></span>
												</button>';
									}
									else
									{
										$aktenliste .= '<button type="button" 
														title="' . $p->t('bewerbung/dokumentHerunterladen') . '" 
														class="btn btn-default btn-xs" href="' . APP_ROOT . 'cms/dms.php?id=' . $akte->dms_id . '" 
														onclick="FensterOeffnen(\'' . APP_ROOT . 'cms/dms.php?id=' . $akte->dms_id . '&akte_id=' . $akte->akte_id . '\'); return false;">
													<span class="glyphicon glyphicon glyphicon-download-alt" aria-hidden="true" title="' . $p->t('bewerbung/dokumentHerunterladen') . '"></span>
												</button>';
									}
									$aktenliste .= '
											<form id="delete_akte_' . $akte->akte_id . '" method="POST" action="' . $_SERVER['PHP_SELF'] . '?active=dokumente" style="display: inline">
											<button type="submit" 
													title="' . $p->t('global/löschen') . '" 
													class="btn btn-default btn-xs" 
													>
												<span class="glyphicon glyphicon-remove" aria-hidden="true" title="' . $p->t('global/löschen') . '"></span>
												<input type="hidden" name="method" value="delete">
												<input type="hidden" name="akte_id" value="' . $akte->akte_id . '">
											</button></form><br> 
											<span class="label label-warning">' . $p->t('bewerbung/dokumentWirdGeprueft') . '</span>
											</li>';
									$div = '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '&active=dokumente">
										<span id="nachgereicht_' . $dok->dokument_kurzbz . '" style="display:none;">wird nachgereicht:
											<input type="checkbox" name="check_nachgereicht">
											<div class="input-group">
												<input type="text" size="15" maxlength="128" name="txt_anmerkung">
												<div class="input-group-btn">												
												<input type="submit" value="OK" name="submit_nachgereicht" class="btn btn-default">
												</div>
											</div>
										</span>
										<input type="hidden" name="dok_kurzbz" value="' . $dok->dokument_kurzbz . '"><input type="hidden" name="akte_id" value="' . $akte->akte_id . '">
									</form>';
								}
							}
						}
						$aktenliste .= '</ul>';
					}
					// Fuer FHTW deaktiviert, damit Bewerber auch im Akzeptiert-Status Dokumente hochladen koennen, wenn noch keines hochgeladen war
					// Wenn kein Dokument hochgeladen ist und trotzdem akzeptiert wurde
					elseif (CAMPUS_NAME != 'FH Technikum Wien' && $dok->anzahl_dokumente_akzeptiert > 0)
					{
						// $status = "<span class='glyphicon glyphicon-ok' aria-hidden='true' title='".$p->t("bewerbung/abgegeben")."'></span>";
						$div = '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '&active=dokumente">
							<span id="nachgereicht_' . $dok->dokument_kurzbz . '" style="display:none;">wird nachgereicht:
								<input type="checkbox" name="check_nachgereicht">
								<div class="input-group">
									<input type="text" size="15" maxlength="128" name="txt_anmerkung">
									<div class="input-group-btn">												
									<input type="submit" value="OK" name="submit_nachgereicht" class="btn btn-default">
									</div>
								</div>
							</span><input type="hidden" name="dok_kurzbz" value="' . $dok->dokument_kurzbz . '">
						</form>';
						$aktion = '';
						$aktenliste .= '<ul class="list-unstyled"><li>-</li></ul>';
					}
					else
					{
						// Dokument fehlt noch
						// $status = ' - ';
						$aktenliste .= '<ul class="list-unstyled"><li>-</li></ul>';
						
						$aktion = '	<button type="button" 
										title="' . $p->t('bewerbung/upload') . '" 
										class="btn btn-default" onclick="FensterOeffnen(\'dms_akteupload.php?person_id=' . $person_id . '&dokumenttyp=' . $dok->dokument_kurzbz . '\'); return false;">
	  								<span class="glyphicon glyphicon-upload" aria-hidden="true" title="' . $p->t('bewerbung/upload') . '"></span>
								</button>';
						
						if (! defined('BEWERBERTOOL_DOKUMENTE_NACHREICHEN') || BEWERBERTOOL_DOKUMENTE_NACHREICHEN == true)
						{
							// Nachreichbar nur, wenn das DB-Attribut "nachreichbar" true ist
							if ($dok->nachreichbar === true)
							{
								$aktion .= '
								<button type="button" 
										title="' . $p->t('bewerbung/dokumentWirdNachgereicht') . '" 
										class="btn btn-default" onclick="toggleDiv(\'' . $dok->dokument_kurzbz . '\');return false;">
	  								<span class="glyphicon glyphicon-hourglass" aria-hidden="true" title="' . $p->t('bewerbung/dokumentWirdNachgereicht') . '"></span>
								</button>';
							}
							else
								$aktion .= '';
						}
						$div = '<form class="form-horizontal" method="POST" action="' . $_SERVER["PHP_SELF"] . '?active=dokumente">
							<span id="nachgereicht_' . $dok->dokument_kurzbz . '" style="display:none;">' . $p->t('bewerbung/placeholderAnmerkungNachgereicht') . ':
							<input type="checkbox" name="check_nachgereicht" checked=\'checked\' style="display:none">
							<div class="form-group">
								<nobr>
								<div class="row col-xs-12 col-sm-12 col-lg-12">
									<input type="checkbox" name="check_nachgereicht" checked=\'checked\' style="display:none">
									<div class="col-xs-8 col-sm-8 col-lg-9">

										<div class="input-group">
										<input type="text" 
													class="form-control" 
													id="anmerkung_' . $dok->dokument_kurzbz . '" 
													name="txt_anmerkung"
													onInput="zeichenCountdown(\'anmerkung_' . $dok->dokument_kurzbz . '\',128)" 
													placeholder="' . $p->t('bewerbung/placeholderOrtNachgereicht') . '">
										<span class="input-group-addon" style="color: grey;" id="countdown_anmerkung_' . $dok->dokument_kurzbz . '">128</span>
										</div>
									</div>
									<div class="col-xs-3 col-sm-3 col-lg-3">
										<div class="input-group">
											
											<input type="text" 
													class="form-control" 
													id="nachreichungam_' . $dok->dokument_kurzbz . '" 
													name="nachreichungam"
													autofocus="autofocus"
													placeholder="' . $p->t('bewerbung/datumFormat') . '">
											
											
												<input type="submit" value="OK" name="submit_nachgereicht" class="btn btn-default" onclick="return checkNachgereicht(\'' . $dok->dokument_kurzbz . '\')">
											
										</div>
									</div>
								</div>
								</nobr>
							</div>
							<input type="hidden" name="dok_kurzbz" value="' . $dok->dokument_kurzbz . '">
						</form>';
					}
					
					$style = '';
					
					echo '<tr id="row_' . $dok->dokument_kurzbz . '">
					<td style="vertical-align: middle"	class="' . $style . '">' . $dok->bezeichnung_mehrsprachig[getSprache()];
					
					if ($dok->pflicht)
					{
						echo '<span style="color: red"> *</span>';
					}
					// Wenn $detailstring_short oder $beschreibung gesetzt ist, ausklapp-div erstellen
					if (isset($detailstring_short[0]) || $beschreibung != '')
					{
						if (isset($detailstring_short[0]))
							echo '<br>';
						
						echo '<span style="font-style: italic; font-size: 0.9em">';
						if (isset($detailstring_short[0]))
							echo $detailstring_short[0];
						
						echo '<div id="toggle_detailstring_' . $dok->dokument_kurzbz . '" class="collapse">' . $detailstring_original . '</div>';
					}
					echo '</td>

					<td style="vertical-align: top"	class="' . $style . '">';
					if ($detailstring_original != '')
						echo '<button class="btn btn-default" data-toggle="collapse" data-target="#toggle_detailstring_' . $dok->dokument_kurzbz . '"><span class="glyphicon glyphicon-collapse-down"></span> ' . $p->t('bewerbung/details') . '</button>';
					echo '
					</td>
					<td style="vertical-align: middle"	nowrap class="' . $style . '">' . $aktenliste . '</td>
					<td style="vertical-align: middle"	nowrap class="' . $style . '">' . $aktion . '</td>
					<td id="anmerkung_row_' . $dok->dokument_kurzbz . '" style="vertical-align: middle"	class="' . $style . '">' . $div . '</td>';
					
					// An der FHTW werden die benötigten Studiengänge im ersten Schritt ausgeblendet
					if (CAMPUS_NAME == 'FH Technikum Wien' && ! check_person_statusbestaetigt($person_id, 'Interessent', '', ''))
					{
						echo '';
					}
					else
					{
						echo '<td style="vertical-align: middle" class="' . $style . '">';
						foreach ($ben_bezeichnung[getSprache()][$dok->dokument_kurzbz] as $value)
						{
							if ($value != '')
								echo '-&nbsp;' . $value . '<br/>';
						}
						echo '</td>';
					}
					
					echo '</tr>';
				
		endif;
				
			endforeach
			;
		endif;
		
		?>
			</tbody>
		</table>
	</div>
	<br>
	<h4><?php echo $p->t('bewerbung/legende'); ?></h4>
	<table class="table">
		<tr>
			<td><span style="color: red">&nbsp;*</span></td>
			<td><?php echo $p->t('bewerbung/dokumentErforderlich'); ?></td>
		</tr>
		<tr>
			<td><span class="glyphicon glyphicon-upload" aria-hidden="true"
				title="<?php echo $p->t('bewerbung/dokumentOffen'); ?>;"></span>
			</td>
			<td><?php echo $p->t('bewerbung/dokumentOffen'); ?></td>
		</tr>
		<tr>
			<td><span class="glyphicon glyphicon glyphicon-download-alt"
				aria-hidden="true"
				title="<?php echo $p->t('bewerbung/dokumentHerunterladen'); ?>;"></span>
			</td>
			<td><?php echo $p->t('bewerbung/dokumentHerunterladen'); ?></td>
		</tr>

		<!--<tr>
			<td><span class="glyphicon glyphicon-eye-open" aria-hidden="true"
				title="<?php echo $p->t('bewerbung/dokumentNichtUeberprueft'); ?>;"></span>
			</td>
			<td><?php echo $p->t('bewerbung/dokumentNichtUeberprueft'); ?></td>
		</tr>-->
		<?php
		if (! defined('BEWERBERTOOL_DOKUMENTE_NACHREICHEN') || BEWERBERTOOL_DOKUMENTE_NACHREICHEN == true)
		{
			echo '
			<tr>
				<td>
					<span class="glyphicon glyphicon-hourglass" aria-hidden="true" title="'.$p->t('bewerbung/dokumentWirdNachgereicht').'"></span>
				</td>
				<td>'.$p->t('bewerbung/dokumentWirdNachgereicht').'</td>
			</tr>';
		}
		?>
		<!--<tr>
			<td><span class="glyphicon glyphicon-ok" aria-hidden="true"
				title="<?php echo $p->t('bewerbung/dokumentWurdeUeberprueft'); ?>;"></span>
			</td>
			<td><?php echo $p->t('bewerbung/dokumentWurdeUeberprueft'); ?></td>
		</tr>-->
		<tr>
			<td><span class="glyphicon glyphicon-remove" aria-hidden="true"
				title="<?php echo $p->t('global/löschen'); ?>;"></span></td>
			<td><?php echo $p->t('global/löschen'); ?></td>
		</tr>
	</table>
	<button class="btn-nav btn btn-default" type="button"
		data-jump-tab="<?php echo $tabs[array_search('dokumente', $tabs)-1] ?>">
		<?php echo $p->t('global/zurueck') ?>
	</button>
	<button class="btn-nav btn btn-default" type="button"
		data-jump-tab="<?php echo $tabs[array_search('dokumente', $tabs)+1] ?>">
		<?php echo $p->t('bewerbung/weiter'); ?>
	</button>
	<!--<br><?php echo $message ?><br />-->
	<br /><br/><br/>
	<script type="text/javascript">
	function checkNachgereicht(dokument)
	{
		var gebDat = document.getElementById('nachreichungam_'+dokument).value;
		gebDat = gebDat.split(".");

		if(gebDat.length !== 3)
		{
			alert("<?php echo $p->t('bewerbung/datumsformatUngueltig')?>");
			return false;
		}

		if(gebDat[0].length !==2 && gebDat[1].length !== 2 && gebDat[2].length !== 4)
		{
			alert("<?php echo $p->t('bewerbung/datumsformatUngueltig')?>");
			return false;
		}

		var date = new Date(gebDat[2], gebDat[1]-1, gebDat[0]);

		gebDat[0] = parseInt(gebDat[0], 10);
		gebDat[1] = parseInt(gebDat[1], 10);
		gebDat[2] = parseInt(gebDat[2], 10);

		if(!(date.getFullYear() === gebDat[2] && (date.getMonth()+1) === gebDat[1] && date.getDate() === gebDat[0]))
		{
			alert("<?php echo $p->t('bewerbung/datumsformatUngueltig')?>");
			return false;
		}

		var anmerkung = document.getElementById('anmerkung_'+dokument).value;
		if(anmerkung.length == 0)
		{
			alert("<?php echo $p->t('bewerbung/bitteAnmerkungEintragen')?>");
			return false;
		}
	};
	$('.linkPopover').on('click', function (event) {
		//Set timeout to wait for popup div to redraw(animation)
		setTimeout(function(){
			if($('div.popover').css('top').charAt(0) === '-'){
				 $('div.popover').css('top', '0px');
				 var buttonTop = $(event.currentTarget).position().top;
				 var buttonHeight = $(event.currentTarget).height();
				 $('.popover.right>.arrow').css('top', buttonTop + (buttonHeight/2));
			}
		},100);
	});
	</script>
</div>
