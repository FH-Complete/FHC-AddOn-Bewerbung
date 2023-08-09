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
 * 			Manfred Kindl <kindl.manfred@technikum-wien.at>
 */

if(!isset($person_id))
{
	die($p->t('bewerbung/ungueltigerZugriff'));
}
?>

<div role="tabpanel" class="tab-pane" id="aufnahme">
	<h2><?php echo $p->t('bewerbung/menuReihungstest') ?></h2>

	<?php
	$sprachindex = new sprache();
	$spracheIndex = $sprachindex->getIndexFromSprache($sprache);
	// Bachelor-Studienplan mit der höchsten Priorität laden, der abgeschickt und bestätigt ist
	$studienplanReihungstest = getPrioStudienplanForReihungstest($person_id, $nextWinterSemester->studiensemester_kurzbz);
	$angemeldeteRtArray = array();
	$reihungstestID = '';

	// Angemeldete Termine laden
	if (numberOfElements($angemeldeteReihungstests->result) > 0)
	{
		echo '<p>'.$p->t('bewerbung/sieHabenFolgendenTerminGewaehlt').'</p>';
		echo '<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-8">
					<div class="panel-group ">
					<div class="panel panel-default">
						<div class="panel-heading">
							<div class="row">
								<div class="col-xs-4 col-sm-3">'.$p->t('global/datum').'</div>
								<div class="col-xs-3 col-sm-1">'.$p->t('bewerbung/uhrzeit').'</div>
								<div class="col-xs-3 col-sm-2">'.$p->t('bewerbung/zeitzone').'</div>
								<div class="col-xs-5 col-sm-6">'.$p->t('bewerbung/ort').'</div>
							</div>
						</div>
						<div id="listeTesttermine" class="panel-collapse collapse-in">
							<ul class="list-group">';

		foreach($angemeldeteReihungstests->result as $row)
		{
			// Durch die Punkteübernahme kann es vorkommen, dass mehrere Ergebnisse für den gleichen RT zurückgegeben werden
			// Deshalb wird das hier geprüft
			if ($reihungstestID == $row->reihungstest_id)
			{
				continue;
			}
			$reihungstestID = $row->reihungstest_id;

			$fristVorbei = false;
			$uhrzeit = $datum->formatDatum($row->uhrzeit,'H:i');

			$datumStornierenBis = '';
			$buttonBeschriftungStornieren = $p->t('bewerbung/anmeldungStornieren');
			// Wenn BEWERBERTOOL_REIHUNGSTEST_STORNIERBAR_TAGE gesetzt ist oder die Anmeldefrist vorbei ist, kann der Termin nicht mehr storniert werden
			if (defined('BEWERBERTOOL_REIHUNGSTEST_STORNIERBAR_TAGE') && BEWERBERTOOL_REIHUNGSTEST_STORNIERBAR_TAGE != '')
			{
				$time = strtotime($row->datum.' 23:59:59 -'.BEWERBERTOOL_REIHUNGSTEST_STORNIERBAR_TAGE.'days');
				if ($time < time())
				{
					$fristVorbei = true;
				}
				$datumStornierenBis = date("d.m.Y", $time);
				$wochentag = substr($tagbez[$spracheIndex][date("N", $time)], 0, 2);
				$buttonBeschriftungStornieren = $p->t('bewerbung/anmeldungStornierenBis', array($wochentag.', '.$datumStornierenBis));
			}
			elseif ($row->anmeldefrist != '')
			{
				if (strtotime($row->anmeldefrist.' 23:59:59') <= time())
				{
					$fristVorbei = true;
				}
				$datumStornierenBis = $datum->formatDatum($row->anmeldefrist, 'd.m.Y');
				$wochentag = substr($tagbez[$spracheIndex][$datum->formatDatum($row->anmeldefrist, 'N')], 0, 2);
				$buttonBeschriftungStornieren = $p->t('bewerbung/anmeldungStornierenBis', array($wochentag.', '.$datumStornierenBis));
			}
			$ort = new ort($row->ort_kurzbz);
			$raumbezeichnung = $ort->bezeichnung.' '.$ort->planbezeichnung;
			if ($ort->lageplan != '')
			{
				$raumbezeichnung .= '<p>'.$ort->lageplan.'</p>';
			}
			echo '	<li class="list-group-item">
						<div class="row">
							<div class="col-xs-4 col-sm-3">'.substr($tagbez[$spracheIndex][$datum->formatDatum($row->datum, 'N')], 0, 2).', '.$datum->formatDatum($row->datum, 'd.m.Y').'</div>
							<div class="col-xs-3 col-sm-1">'.$uhrzeit.'</div>
							<div class="col-xs-3 col-sm-2">'.$p->t('bewerbung/zeitzoneMEZ').'</div>
							<div class="col-xs-5 col-sm-6">'.($row->ort_kurzbz != '' ? $raumbezeichnung : $p->t('bewerbung/raumzuteilungFolgt')).'</div>
						</div>
						</li>';
			$angemeldeteRtArray[] = $row->reihungstest_id;
		}
		echo '</ul></div></div></div>';
		echo '	<button type="button"
						class="btn btn-warning '.($fristVorbei ? 'disabled' : '').'"
						onclick="aktionReihungstest(\''.$row->reihungstest_id.'\', \''.$studienplanReihungstest.'\', \'delete\')">
					'.$buttonBeschriftungStornieren.'
				</button>';
		echo '	</div></div><br><br>';
		echo $p->t('bewerbung/reihungstestInfoTextAngemeldet');
	}

	// Wenn die Person TeilnehmerIn am Qualifikationskurs ist (den Statusgrund "Qualifikationskurs" hat),
	// Termine verbergen, bis ein Account im EQK vorhanden ist
	if (defined('STATUSGRUND_ID_QUALIFIKATIONKURSTEILNEHMER') && STATUSGRUND_ID_QUALIFIKATIONKURSTEILNEHMER != '')
	{
		$hasStatusgrundQuali = hasPersonStatusgrund($person_id, $nextWinterSemester->studiensemester_kurzbz, STATUSGRUND_ID_QUALIFIKATIONKURSTEILNEHMER);
	}
	else
	{
		$hasStatusgrundQuali = false;
	}

	// Wenn die Person Quereinsteiger ins Sommersemester ist (den Statusgrund "Einstieg Sommersemester" hat), Termine verbergen
	if (defined('STATUSGRUND_ID_EINSTIEG_SOMMERSEMESTER') && STATUSGRUND_ID_EINSTIEG_SOMMERSEMESTER != '')
	{
		$hasStatusgrundEinstiegSS = hasPersonStatusgrund($person_id, $nextWinterSemester->studiensemester_kurzbz, STATUSGRUND_ID_EINSTIEG_SOMMERSEMESTER);
	}
	else
	{
		$hasStatusgrundEinstiegSS = false;
	}


	$nextSummerSemester = new studiensemester();
	$nextSummerSemester = $nextSummerSemester->getaktorNext('SS');

	$prestudent = new prestudent();
	$isStudentQuali = false;

	// Wenn Studiengang KZ für Qualifikationskurse gesetzt ist, prüfen, ob Bewerber dort einen Status hat
	if (defined('STUDIENGANG_KZ_QUALIFIKATIONKURSE')
		&& STUDIENGANG_KZ_QUALIFIKATIONKURSE != ''
		&& STUDIENGANG_KZ_QUALIFIKATIONKURSE != null)
	{
		$isStudentQuali = $prestudent->existsPrestudentstatus($person_id, STUDIENGANG_KZ_QUALIFIKATIONKURSE, $nextSummerSemester);
	}

	$reihungstestTermine = '';

	//Reihungstesttermine der Qualifikationskurse laden, wenn STUDIENGANG_KZ_QUALIFIKATIONKURSE gesetzt ist
	$studienplanQualikurse_arr = array();
	if (defined('STUDIENGANG_KZ_QUALIFIKATIONKURSE')
	&& STUDIENGANG_KZ_QUALIFIKATIONKURSE != ''
	&& STUDIENGANG_KZ_QUALIFIKATIONKURSE != null)
	{
		$studienplanQualikurse = new studienplan();
		$studienplanQualikurse->getStudienplaeneFromSem(STUDIENGANG_KZ_QUALIFIKATIONKURSE, $nextWinterSemester->studiensemester_kurzbz);
		// Wenn für das übergbene Studiensemester kein Studienplan gefunden wird, wird nochmal ohne Studiensemester gesucht
		if (numberOfElements($studienplanQualikurse->result) == 0)
		{
			$studienplanQualikurse->getStudienplaeneFromSem(STUDIENGANG_KZ_QUALIFIKATIONKURSE);
		}
		foreach ($studienplanQualikurse->result AS $row)
		{
			$studienplanQualikurse_arr[] = $row->studienplan_id;
		}
	}
	$studienplanQualikurse_arr = array_unique($studienplanQualikurse_arr);
	// Qualifikationskursteilnehmer sehen keine Termine, bis sie einen Studenten-Account im Studiengang EQK haben
	if ($hasStatusgrundQuali == true)
	{
		if ($isStudentQuali == true)
		{
			if (numberOfElements($studienplanQualikurse->result) > 0)
			{
				foreach ($studienplanQualikurse->result AS $row)
				{
					if ($reihungstestTermine = getReihungstestsForOnlinebewerbung($row->studienplan_id, $nextWinterSemester->studiensemester_kurzbz))
					{
						break;
					}
				}
			}
		}
	}
	else
	{
		// Mögliche Termine zur Anmeldung laden, für die die Person noch nicht angemeldet ist
		// Studienpläne der Qualifikationskurse werden ausgenommen

		// Vorrübergehender Hack für FHTW
		// Wenn mindestens eine Bewerbung BEW DL ist, werden nur Termine mit diesem Studienplan zur Anmeldung angezeigt.
		if (CAMPUS_NAME == 'FH Technikum Wien')
		{
			$qry = "SELECT COUNT(*) as anzahl
			FROM PUBLIC.tbl_person
			JOIN PUBLIC.tbl_prestudent USING (person_id)
			JOIN PUBLIC.tbl_prestudentstatus USING (prestudent_id)
			JOIN lehre.tbl_studienplan USING (studienplan_id)
			JOIN lehre.tbl_studienordnung USING (studienordnung_id)
			JOIN PUBLIC.tbl_studiengang ON (tbl_studienordnung.studiengang_kz = tbl_studiengang.studiengang_kz)
			WHERE person_id = ".$db->db_add_param($person_id, FHC_INTEGER)."
				AND studiensemester_kurzbz = ".$db->db_add_param($nextWinterSemester->studiensemester_kurzbz)."
				AND tbl_studiengang.typ = 'b'
				AND bestaetigtam IS NOT NULL
				AND (
					SELECT status_kurzbz
					FROM PUBLIC.tbl_prestudentstatus
					WHERE prestudent_id = tbl_prestudent.prestudent_id
						AND studiensemester_kurzbz = tbl_prestudentstatus.studiensemester_kurzbz
					ORDER BY datum DESC,
						tbl_prestudentstatus.insertamum DESC LIMIT 1
					) IN ('Interessent')
				AND tbl_prestudentstatus.studienplan_id IN (5,486)";

			if($result = $db->db_query($qry))
			{
				if($row = $db->db_fetch_object($result))
				{
					if (intval($row->anzahl) > 0)
					{
						$reihungstestTermine = getReihungstestsForOnlinebewerbung(5, $nextWinterSemester->studiensemester_kurzbz, 1, $studienplanQualikurse_arr);
					}
					else
					{
						$reihungstestTermine = getReihungstestsForOnlinebewerbung($studienplanReihungstest, $nextWinterSemester->studiensemester_kurzbz, 1, $studienplanQualikurse_arr);
					}
				}
			}
		}
		else
		{
			$reihungstestTermine = getReihungstestsForOnlinebewerbung($studienplanReihungstest, $nextWinterSemester->studiensemester_kurzbz, 1, $studienplanQualikurse_arr);
		}
	}

	$terminauswahl = true;
	if($hasStatusgrundEinstiegSS == true)
	{
		echo '<div class="col-xs-12 alert alert-warning">'.$p->t('bewerbung/keineRtTermineZurAuswahl').'</div>';
		$terminauswahl = false;
	}
	elseif($hasStatusgrundQuali == true)
	{
		if ($isStudentQuali == false)
		{
			echo '<div class="col-xs-12 alert alert-info">'.$p->t('bewerbung/infoVorgemerktFuerQualifikationskurs').'</div>';
			$terminauswahl = false;
		}
		elseif ($reihungstestTermine == '' && numberOfElements($angemeldeteRtArray) == 0)
		{
			echo '<div class="col-xs-12 alert alert-warning">'.$p->t('bewerbung/keineRtTermineZurAuswahl').'</div>';
			$terminauswahl = false;
		}
	}
	elseif($reihungstestTermine == '' && numberOfElements($angemeldeteRtArray) == 0)
	{
		echo '<div class="col-xs-12 alert alert-info">'.$p->t('bewerbung/keineRtTermineZurAuswahl').'</div>';
		$terminauswahl = false;
	}

	if ($terminauswahl == true)
	{
		//Wenn bereits eine Anmeldung existiert, keine Terminauswahl anzeigen
		if (numberOfElements($angemeldeteRtArray) == 0)
		{
			echo '<p>'.$p->t('bewerbung/fuerReihungstestAnmelden').'</p>';
			echo '<div class="row">
					<div class="col-md-8 col-lg-6">
					<div class="panel-group ">
					<div class="panel panel-default">
						<div class="panel-heading">
							<div class="row">
								<div class="col-xs-3 ">'.$p->t('global/datum').'</div>
								<div class="col-xs-2">'.$p->t('bewerbung/uhrzeit').'</div>
								<!--<div class="col-xs-3">'.$p->t('bewerbung/anmeldefrist').'</div>-->
								<div class="col-xs-4"></div>
							</div>
						</div>
						<div id="listeTesttermine" class="panel-collapse collapse-in">
							<ul class="list-group">';

			foreach($reihungstestTermine as $row)
			{
				$angemeldet = false;
				if (in_array($row->reihungstest_id, $angemeldeteRtArray))
				{
					$angemeldet = true;
				}
				// Wenn alle Plätze vergeben sind, Termin nicht anzeigen
				if ($row->anzahl_plaetze - $row->anzahl_anmeldungen <= 0)
					continue;

				$plaetzeText = '';
				// Hervorheben, sobald weniger als 10% Plätze frei sind
				if ((($row->anzahl_plaetze / 100) * 10) >= ($row->anzahl_plaetze - $row->anzahl_anmeldungen))
				{
					$plaetzeText = 'Noch <span class="text-danger"><b>'.($row->anzahl_plaetze - $row->anzahl_anmeldungen).'</b></span> freie Plätze';
				}
				else
				{
					$plaetzeText = 'Noch '.($row->anzahl_plaetze - $row->anzahl_anmeldungen).' freie Plätze';
				}
				$anmeldeFristText = '';
				$tageBisFristablauf = ((strtotime($row->anmeldefrist) - time()) / 86400);
				if ($tageBisFristablauf <= 7)
				{
					$anmeldeFristText = '<br><div class="label label-warning">
											<span class="glyphicon glyphicon-warning-sign"></span>
											&nbsp;&nbsp;Anmeldefrist endet am ' . substr($tagbez[$spracheIndex][$datum->formatDatum($row->anmeldefrist, 'N')], 0, 2).', '.$datum->formatDatum($row->anmeldefrist, 'd.m.Y') . '</div>';
				}
				// Anzeigen der Uhrzeit des Tests
				$uhrzeit = $datum->formatDatum($row->uhrzeit,'H:i');
				echo '	<li class="list-group-item">
						<div class="row">
							<div class="col-xs-3 ">'.substr($tagbez[$spracheIndex][$datum->formatDatum($row->datum, 'N')], 0, 2).', '.$datum->formatDatum($row->datum, 'd.m.Y').'</div>
							<div class="col-xs-2 ">'.$uhrzeit.'</div>
							<!--<div class="col-xs-3 ">'.substr($tagbez[$spracheIndex][$datum->formatDatum($row->anmeldefrist, 'N')], 0, 2).', '.$datum->formatDatum($row->anmeldefrist, 'd.m.Y').' '.$anmeldeFristText.'</div>-->
							<div class="col-xs-4 ">
								<button type="button"
										class="btn btn-primary '.($angemeldet ? 'disabled' : '').'"
										onclick="aktionReihungstest(\''.$row->reihungstest_id.'\', \''.$studienplanReihungstest.'\', \'save\')">
									'.$p->t('global/anmelden').'
								</button>
								'.$anmeldeFristText.'
							</div>
						</div>
						</li>';
			}
			echo '</ul></div></div></div></div></div>';
		}
	}

	if (array_key_exists(array_search('aufnahme', $tabs)-1, $tabs))
	{
		echo '	<button class="btn-nav btn btn-default" type="button" data-jump-tab="'.$tabs[array_search('aufnahme', $tabs)-1].'">
					'.$p->t('global/zurueck').'
				</button>';
	}
	if (array_key_exists(array_search('aufnahme', $tabs)+1, $tabs))
	{
		echo '	<button class="btn-nav btn btn-default" type="button" data-jump-tab="'.$tabs[array_search('aufnahme', $tabs)+1].'">
					'.$p->t('bewerbung/weiter').'
				</button>';
	}
	else
	{
		echo '	<button class="btn-nav btn btn-default" type="button" data-jump-tab="'.$tabs[0].'">
					'.$p->t('bewerbung/menuUebersicht').'
				</button>';
	}
	?>
	<br/><br/>
</div>
<script type="text/javascript">
function aktionReihungstest(reihungstest_id, studienplan_id, aktion)
{
	data = {
		reihungstest_id: reihungstest_id,
		studienplan_id: studienplan_id,
		aktion: aktion,
		aktionReihungstest: true
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
				window.location.href='bewerbung.php?active=aufnahme';
		},
		error: function(data)
		{
			alert(data.msg)
		}
	});
}
</script>
