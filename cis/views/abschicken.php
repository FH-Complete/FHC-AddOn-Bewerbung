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
 */

if(!isset($person_id))
{
	die($p->t('bewerbung/ungueltigerZugriff'));
}

echo '<div role="tabpanel" class="tab-pane" id="abschicken">
	<h2>'.$p->t('bewerbung/menuBewerbungAbschicken').'</h2>
	<p>'.$p->t('bewerbung/erklaerungBewerbungAbschicken').'</p>';

$disabled = 'disabled';
if($status_person == true && $status_kontakt == true && $status_dokumente == true && $status_zahlungen == true && $status_reihungstest == true)
	$disabled = '';
$prestudent_help= new prestudent();
$prestudent_help->getPrestudenten($person->person_id);
$stg = new studiengang();

foreach($prestudent_help->result as $prest)
{
	$stg->load($prest->studiengang_kz);

	if($sprache!='German' && $stg->english!='')
		$stg_bezeichnung = $stg->english;
	else
		$stg_bezeichnung = $stg->bezeichnung;

	$prestudent_help2 = new prestudent();
	$prestudent_help2->getPrestudentRolle($prest->prestudent_id,'Bewerber');
	if(count($prestudent_help2->result)>0)
	{
		// Bewerbung bereits geschickt
		echo '
		<div class="row">
			<div class="col-md-6 col-sm-8 col-xs-10">
					<div class="form-group">
						<label for="'.$stg->kurzbzlang.'">'.$p->t('bewerbung/bewerbungAbschickenFuer').' '.$stg_bezeichnung.'</label>
						<input class="btn btn-default form-control" type="button" value="'.$p->t('bewerbung/BewerbungBereitsVerschickt').'" disabled="disabled">
					</div>
			</div>
		</div>';
	}
	else
	{
		// Bewerbung noch nicht geschickt
		echo '
		<div class="row">
			<div class="col-md-6 col-sm-8 col-xs-10">
				<form method="POST"  action="'.$_SERVER['PHP_SELF'].'?active=abschicken">
					<div class="form-group">
						<label for="'.$stg->kurzbzlang.'">'.$p->t('bewerbung/bewerbungAbschickenFuer').' '.$stg_bezeichnung.'</label>
						<input id="'.$stg->kurzbzlang.'" class="btn btn-default form-control" type="submit" value="'.$p->t('bewerbung/buttonBewerbungAbschicken').' ('.$stg->kurzbzlang.')" name="btn_bewerbung_abschicken" '.$disabled.'>
						<input type="hidden" name="prestudent_id" value="'.$prest->prestudent_id.'">
					</div>
				</form>
			</div>
		</div>';
	}
}

echo '
	<button class="btn-nav btn btn-default" type="button" onclick="window.location.href=\'bewerbung.php?logout=true\';">
		'.$p->t('bewerbung/logout').'
	</button>
	<button class="btn-nav btn btn-default" type="button" data-jump-tab="aufnahme">
		'.$p->t('global/zurueck').'
	</button>
</div>';
?>
