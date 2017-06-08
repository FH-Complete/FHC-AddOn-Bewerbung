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
//echo '<p>'.$p->t('bewerbung/erklaerungBewerbungAbschicken').'</p>';

$disabled = 'disabled';
if($status_person==true && $status_kontakt==true && $status_zahlungen==true && $status_reihungstest==true && $status_zgv_bak==true && $status_dokumente==true && $status_ausbildung == true)
	$disabled = '';

if(CAMPUS_NAME=='FH Technikum Wien' && $status_person==true && $status_kontakt==true && $status_zahlungen==true && $status_reihungstest==true && $status_zgv_bak==true && $status_dokumente==false)
	$disabled = '';

$prestudent_help= new prestudent();
$prestudent_help->getPrestudenten($person->person_id);
$stg = new studiengang();
$typ = new studiengang();

foreach($prestudent_help->result as $prest)
{
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
		$bewerbungsfrist = '';
		$tage_bis_fristablauf = '';
		$class = '';
		$button_class = 'btn-primary';
		$bewerbungsfristen = new bewerbungstermin();
		$bewerbungsfristen->getBewerbungstermine($prest->studiengang_kz, $row->studiensemester_kurzbz, 'insertamum DESC', $row->studienplan_id);
			
		if (isset($bewerbungsfristen->result[0]))
		{
			$bewerbungsfristen = $bewerbungsfristen->result[0];
			// Wenn Nachfrist gesetzt und das Nachfrist-Datum befuellt ist, gilt die Nachfrist
			// sonst das Endedatum, wenn eines gesetzt ist
			if ($bewerbungsfristen->nachfrist == true && $bewerbungsfristen->nachfrist_ende != '')
			{
				$bewerbungsfrist = '<br>'.$p->t('bewerbung/bewerbungsfrist').' '.$datum->formatDatum($bewerbungsfristen->nachfrist_ende, 'd.m.Y');
				$tage_bis_fristablauf = ((strtotime($bewerbungsfristen->nachfrist_ende) - time())/86400);
			}
			elseif ($bewerbungsfristen->ende != '')
			{
				$bewerbungsfrist = '<br>'.$p->t('bewerbung/bewerbungsfrist').' '.$datum->formatDatum($bewerbungsfristen->ende, 'd.m.Y');
				$tage_bis_fristablauf = ((strtotime($bewerbungsfristen->ende) - time())/86400);
			}
			
			// Wenn die Frist in weniger als 7 Tagen ablaeuft, hervorheben
			if ($tage_bis_fristablauf <= 7)
			{
				$class = 'class="alert-warning"';
			}
			if ($tage_bis_fristablauf <= 0)
			{
				$class = 'class="alert-danger"';
				$button_class = 'btn-default';
			}
			
			// Wenn die Frist abgelaufen ist, Button deaktivieren
			if ($tage_bis_fristablauf != '' && $tage_bis_fristablauf <= 0)
				$disabled_bewerbung = 'disabled';
		}

		if ($stg->typ=='m' && $status_zgv_mas==false)
			$disabled = 'disabled';
		
		$buttontext = $p->t('bewerbung/buttonBewerbungAbschicken').' ('.$stg->kurzbzlang.' '.$row->studiensemester_kurzbz.')';
		
		if ($disabled_bewerbung == 'disabled')
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
										<span '.$class.'>'.$bewerbungsfrist.'</span>
								</label>
								<button class="btn btn-default form-control" disabled type="button">'.$p->t('bewerbung/BewerbungBereitsVerschickt').'</button>
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
										<span '.$class.'>'.$bewerbungsfrist.'</span>
								</label>
								<button id="'.$stg->kurzbzlang.'" class="btn '.$button_class.' form-control" '.$disabled.' '.$disabled_bewerbung.' type="submit" name="btn_bewerbung_abschicken">
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

echo '<br><br>
	<button class="btn-nav btn btn-default" type="button" data-jump-tab="'.$tabs[array_search('abschicken', $tabs)-1].'">
		'.$p->t('global/zurueck').'
	</button>
	<button class="btn-nav btn btn-warning" type="button" onclick="window.location.href=\'bewerbung.php?logout=true\';">
		'.$p->t('bewerbung/logout').'
	</button><br/><br/>
</div>';
?>
