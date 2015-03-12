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
	die('Ungültiger Zugriff');
}
?>

<div role="tabpanel" class="tab-pane" id="abschicken">
	<h2>Bewerbung abschicken</h2>
	<p>
		Haben Sie alle Daten korrekt ausgefüllt bzw. alle Dokumente auf das System hochgeladen, können Sie Ihre Bewerbung abschicken.<br>
		Die jeweilige Studiengangsassistenz wird sich in den folgenden Tagen, bezüglich der Bewerbung, bei Ihnen Melden.
		<br><br>
		Bitte überprüfen Sie nochmals Ihre Daten.<br>
		Um Ihre Bewerbung jetzt abzuschließen klicken auf folgenden Link:
	</p>
	<?php

	$disabled = 'disabled';
	if($status_person == true && $status_kontakt == true && $status_dokumente == true && $status_zahlungen == true && $status_reihungstest == true)
		$disabled = '';

	$prestudent_help= new prestudent();
	$prestudent_help->getPrestudenten($person->person_id);
	$stg = new studiengang();


	foreach($prestudent_help->result as $prest):
		$stg->load($prest->studiengang_kz); ?>
		<div class="row">
			<div class="col-md-6 col-sm-8 col-xs-10">
				<form method="POST" action="<?php echo basename(__FILE__) ?>">
					<div class="form-group">
						<label for="<?php echo $stg->kurzbzlang ?>">Bewerbung abschicken für <?php echo $stg->bezeichnung ?></label>
						<input id="<?php echo $stg->kurzbzlang ?>" class="btn btn-default form-control" type="submit" value="Bewerbung abschicken (<?php echo $stg->kurzbzlang ?>)" name="btn_bewerbung_abschicken" <?php echo $disabled ?>>
						<input type="hidden" name="prestudent_id" value="<?php echo $prest->prestudent_id ?>">
					</div>
				</form>
			</div>
		</div>
	<?php endforeach; ?>
	<button class="btn-nav btn btn-default" type="button" data-jump-tab="aufnahme">
		Zurück
	</button>
</div>

