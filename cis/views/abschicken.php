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

if(!isset($person_id))
{
	die($p->t('bewerbung/ungueltigerZugriff'));
}

echo '<div role="tabpanel" class="tab-pane" id="abschicken">
	<h2>'.$p->t('bewerbung/menuBewerbungAbschicken').'</h2>';


	if($save_error_abschicken===false)
	{
		echo '	<div class="alert alert-success" id="success-alert_abschicken">
				<button type="button" class="close" data-dismiss="alert">x</button>
					<strong>'.$message.'</strong>
				</div>';
	}
	elseif($save_error_abschicken===true)
	{
		echo '	<div class="alert alert-danger" id="danger-alert">
			<button type="button" class="close" data-dismiss="alert">x</button>
				<strong>'.$p->t('global/fehleraufgetreten').' </strong>'.$message.'
			</div>';
	}

echo '<p>'.$p->t('bewerbung/erklaerungBewerbungAbschicken').'</p>
	<div class="row">';

$notiz = new notiz;
$notiz->getBewerbungstoolNotizen($person_id);
$count_notizen = 0;
if(count($notiz->result))
{
	foreach($notiz->result as $note)
	{
		if($note->insertvon == 'online_notiz')
		{
			$count_notizen ++;
			echo '	<div class="col-sm-3">
						<b>'.$p->t('bewerbung/notizVom').' '.date('j.n.y H:i', strtotime($note->insertamum)).'</b>
					</div>
					<div class="col-sm-9">
						'.htmlspecialchars($note->text).'
					</div><br>';
		}
	}
}
if(!defined('BEWERBERTOOL_ABSCHICKEN_ANMERKUNG') || BEWERBERTOOL_ABSCHICKEN_ANMERKUNG)
{
	if($count_notizen == 0)
	{
		echo '	</div><form method="POST" action="'.$_SERVER['PHP_SELF'].'?active=abschicken">
				<div class="form-group">
					<label for="anmerkung">'.$p->t('bewerbung/anmerkung').'</label>
					<textarea class="form-control" name="anmerkung" rows="4" maxlength="1024" id="anmerkung" style="width:80%" placeholder="'.$p->t('bewerbung/anmerkungPlaceholder').'" onInput="zeichenCountdown(\'anmerkung\',1024)"></textarea>
				</div>
				<button class="btn btn-default" type="submit" name="btn_notiz">
					'.$p->t('global/speichern').'
				</button>
				<span style="color: grey; display: inline-block; width: 30px;" id="countdown_anmerkung"></span>
			</form><br>';
	}
	else
		echo '	</div><br>';
}

$stg = new studiengang();
$typ = new studiengang();

if(!$prestudent_help = getBewerbungen($person_id, true))
{
	echo '<div class="col-xs-12 alert alert-warning">'.$p->t('bewerbung/keinStatus').'</div>';
}
else 
{
	usort($prestudent_help, "sortPrestudents");
	foreach($prestudent_help as $prest)
	{
		$disabled = 'disabled';
		if(	$status_person == true && 
			$status_kontakt == true && 
			$status_zahlungen == true && 
			$status_zgv_bak == true && 
			$status_ausbildung == true)
		{
			// An der FHTW ist das Abschicken der Bewerbung nicht abhängig vom Reihungstest
			if (CAMPUS_NAME == 'FH Technikum Wien')
			{
				$disabled = '';
			}
			elseif ($status_reihungstest == true)
			{
				$disabled = '';
			}
		}
		
		$stg->load($prest->studiengang_kz);
		$typ->getStudiengangTyp($stg->typ);
	
		if($sprache!='German' && $stg->english!='')
			$stg_bezeichnung = $stg->english;
		else
			$stg_bezeichnung = $stg->bezeichnung;
	
		$prestudent_help2 = new prestudent();
		$prestudent_help2->getPrestudentRolle($prest->prestudent_id,'Interessent');
	
		$studiensemester = new studiensemester();
		$studiensemester->getStudiensemesterOnlinebewerbung();
		$stsem_array = array();
		foreach($studiensemester->studiensemester AS $s)
			$stsem_array[] = $s->studiensemester_kurzbz;
	
		foreach($prestudent_help2->result AS $row)
		{
			// Bewerbungsfristen laden
			$disabled_bewerbung = '';
			$bewerbungsfrist = '<span style="font-weight: normal"><br>'.$p->t('bewerbung/bewerbungszeitraum').': '.$p->t('bewerbung/unbegrenzt').'</span>';
			$tage_bis_fristablauf = '';
			$tage_bis_bewerbungsbeginn = '';
			$class = '';
			$button_class = 'btn-primary';
			$bewerbungsfristen = new bewerbungstermin();
			$bewerbungsfristen->getBewerbungstermine($prest->studiengang_kz, $row->studiensemester_kurzbz, 'insertamum DESC', $row->studienplan_id);
	
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
				
				$tage_bis_bewerbungsbeginn = ((time() - strtotime($bewerbungsfristen->beginn))/86400);
				// Wenn Nachfrist gesetzt und das Nachfrist-Datum befuellt ist, gilt die Nachfrist
				// sonst das Endedatum, wenn eines gesetzt ist
				if ($bewerbungsfristen->nachfrist == true && $bewerbungsfristen->nachfrist_ende != '')
				{
					$tage_bis_fristablauf = ((strtotime($bewerbungsfristen->nachfrist_ende) - time())/86400);
					// Wenn die Frist in weniger als 7 Tagen ablaeuft, hervorheben
					if ($tage_bis_fristablauf <= 7)
					{
						$class = 'class="bg-warning text-warning"';
					}
					if ($tage_bis_fristablauf <= 0)
					{
						$class = 'class="bg-danger text-danger"';
						$button_class = 'btn-default';
					}
					$bewerbungsfrist = '<span style="font-weight: normal"><br>'.$p->t('bewerbung/bewerbungszeitraum').': '.$bewerbungsbeginn.'<span '.$class.'>'.$datum->formatDatum($bewerbungsfristen->nachfrist_ende, 'd.m.Y').'</span></span>';
				}
				elseif ($bewerbungsfristen->ende != '')
				{
					$tage_bis_fristablauf = ((strtotime($bewerbungsfristen->ende) - time())/86400);
					// Wenn die Frist in weniger als 7 Tagen ablaeuft, hervorheben
					if ($tage_bis_fristablauf <= 7)
					{
						$class = 'class="bg-warning text-warning"';
					}
					if ($tage_bis_fristablauf <= 0)
					{
						$class = 'class="bg-danger text-danger"';
						$button_class = 'btn-default';
					}
					$bewerbungsfrist = '<span style="font-weight: normal"><br>'.$p->t('bewerbung/bewerbungszeitraum').': '.$bewerbungsbeginn.'<span '.$class.'>'.$datum->formatDatum($bewerbungsfristen->ende, 'd.m.Y').'</span></span>';
				}
				elseif ($bewerbungsfristen->beginn != '')
				{
					$bewerbungsfrist = '<span style="font-weight: normal"><br>'.$p->t('bewerbung/bewerbungszeitraum').': '.$bewerbungsbeginn.$p->t('bewerbung/unbegrenzt').'</span>';
				}
				else
				{
					$bewerbungsfrist = '<span style="font-weight: normal"><br>'.$p->t('bewerbung/unbegrenzt').'</span>';
				}
				
				// Wenn die Frist abgelaufen ist, Button deaktivieren
				if (($tage_bis_fristablauf != '' && $tage_bis_fristablauf <= 0) || ($tage_bis_bewerbungsbeginn != '' && $tage_bis_bewerbungsbeginn <= 0))
					$disabled_bewerbung = 'disabled';
			}
	
			if ($stg->typ == 'm' && $status_zgv_mas == false)
				$disabled = 'disabled';
	
			// Die Vollständigkeit der Dokumente wird extra für jeden Studiengang gecheckt 
			if (!empty($status_dokumente_arr[$prest->studiengang_kz]))
				$disabled = 'disabled';
			
			$buttontext = $p->t('bewerbung/buttonBewerbungAbschicken').' ('.$stg->kurzbzlang.' '.$row->studiensemester_kurzbz.')';
			
			if ($tage_bis_bewerbungsbeginn != '' && $tage_bis_bewerbungsbeginn <= 0)
				$buttontext = $p->t('bewerbung/bewerbungszeitraumStartetAm', array($bewerbungsbeginn));
			elseif ($tage_bis_fristablauf != '' && $tage_bis_fristablauf <= 0)
				$buttontext = $p->t('bewerbung/bewerbungsfristAbgelaufen');
			elseif ($disabled == 'disabled')
				$buttontext = $p->t('bewerbung/buttonBewerbungUnvollstaendig');
			
			if(in_array($row->studiensemester_kurzbz, $stsem_array)) //Fuer Studiensemester ohne Onlinebewerbung kann sich nicht mehr beworben werden @todo: Dies soll zukuenftig je Studiengang abgespeichert werden koennen
			{
				if($row->bewerbung_abgeschicktamum!='')
				{
					// Bewerbung bereits geschickt
					echo '
					<div class="row">
						<div class="col-md-6 col-sm-8 col-xs-10">
								<div class="form-group">
									<label for="'.$stg->kurzbzlang.'">
											'.$p->t('bewerbung/bewerbungAbschickenFuer').' 
											'.$typ->bezeichnung.' 
											'.$stg_bezeichnung.' ('.$row->studiensemester_kurzbz.') 
											'.$bewerbungsfrist.'
									</label>
									<button class="btn btn-default btn-block" disabled type="button">'.$p->t('bewerbung/BewerbungBereitsVerschickt').'</button>
								</div>
						</div>
					</div>';
				}
				else
				{
					// Bewerbung noch nicht geschickt
					$orgform = new organisationsform();
					$orgform->load($row->orgform_kurzbz);
					echo '
					<div class="row">
						<div class="col-md-6 col-sm-8 col-xs-10">
							<form method="POST"  action="'.$_SERVER['PHP_SELF'].'?active=abschicken">
								<div class="form-group">
									<label for="'.$stg->kurzbzlang.'">
											'.$p->t('bewerbung/bewerbungAbschickenFuer').' 
											'.$typ->bezeichnung.' 
											'.$stg_bezeichnung.($orgform->bezeichnung!=''?' '.$orgform->bezeichnung:'').' 
											('.$row->studiensemester_kurzbz.') 
											'.$bewerbungsfrist.'
									</label>
									<button id="'.$stg->kurzbzlang.'" class="btn '.$button_class.' btn-block" '.$disabled.' '.$disabled_bewerbung.' type="submit" name="btn_bewerbung_abschicken">
											'.$buttontext.' 
									</button>
									<input type="hidden" name="prestudent_id" value="'.$prest->prestudent_id.'">
								</div>
							</form>
						</div>
					</div>';
				}
			}
		}
	}
}

echo '<br><br>
	<button class="btn-nav btn btn-default" type="button" data-jump-tab="'.$tabs[array_search('abschicken', $tabs)-1].'">
		'.$p->t('global/zurueck').'
	</button>
	<button class="btn-nav btn btn-warning" type="button" onclick="window.location.href=\'bewerbung.php?logout=true\';">
		'.$p->t('bewerbung/logout').'
	</button><br/><br/>
</div>';
?>
