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
?>

<div role="tabpanel" class="tab-pane" id="kontakt">
	<h2>Kontaktinformationen</h2>
	<?php
	$nation = new nation();
	$nation->getAll($ohnesperre=true);

	$kontakt = new kontakt();
	$kontakt->load_persKontakttyp($person->person_id, 'email');
	$email = isset($kontakt->result[0]->kontakt)?$kontakt->result[0]->kontakt:'';

	$kontakt_t = new kontakt();
	$kontakt_t->load_persKontakttyp($person->person_id, 'telefon');
	$telefon = isset($kontakt_t->result[0]->kontakt)?$kontakt_t->result[0]->kontakt:'';

	$adresse = new adresse();
	$adresse->load_pers($person->person_id);
	$strasse = isset($adresse->result[0]->strasse)?$adresse->result[0]->strasse:'';
	$plz = isset($adresse->result[0]->plz)?$adresse->result[0]->plz:'';
	$ort = isset($adresse->result[0]->ort)?$adresse->result[0]->ort:'';
	$adr_nation = isset($adresse->result[0]->nation)?$adresse->result[0]->nation:'';
	?>


	<form method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>?active=dokumente" class="form-horizontal">
		<fieldset>
			<legend>Kontakt</legend>
			<div class="form-group">
				<label for="email" class="col-sm-2 control-label">Email*</label>
				<div class="col-sm-10">
					<input type="text" name="email" id="email" value="<?php echo $email ?>" size="32" class="form-control">
				</div>
			</div>
			<div class="form-group">
				<label for="telefonnummer" class="col-sm-2 control-label">Telefonnummer*</label>
				<div class="col-sm-10">
					<input type="text" name="telefonnummer" id="telefonnummer" value="<?php echo $telefon ?>" size="32" class="form-control">
				</div>
			</div>
		</fieldset>

		<fieldset>
			<legend>Adresse</legend>
			<div class="form-group">
				<label for="strasse" class="col-sm-2 control-label">Straße*</label>
				<div class="col-sm-10">
					<input type="text" name="strasse" id="strasse" value="<?php echo $strasse ?>" class="form-control">
				</div>
			</div>
			<div class="form-group">
				<label for="plz" class="col-sm-2 control-label">Postleitzahl*</label>
				<div class="col-sm-10">
					<input type="text" name="plz" id="plz" value="<?php echo $plz ?>" class="form-control">
				</div>
			</div>
			<div class="form-group">
				<label for="ort" class="col-sm-2 control-label">Ort*</label>
				<div class="col-sm-10">
					<input type="text" name="ort" id="ort" value="<?php echo $ort ?>" class="form-control">
				</div>
			</div>
			<div class="form-group">
				<label for="nation" class="col-sm-2 control-label">Nation*</label>
				<div class="col-sm-10">
					<select name="nation" class="form-control">
						<option>--Bitte auswählen --</option>
						<?php
						foreach($nation->nation as $nat):
							$selected = ($adr_nation == $nat->code)?'selected':''; ?>
							<option value="<?php echo $nat->code ?>" <?php echo $selected ?>>
								<?php echo $nat->kurztext ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</fieldset>
		<button class="btn-nav btn btn-default" type="button" data-jump-tab="daten">
			Zurück
		</button>
		<button class="btn btn-default" type="submit" name="btn_kontakt">
			Speichern
		</button>
		<button class="btn-nav btn btn-default" type="button" data-jump-tab="dokumente">
			Weiter
		</button>
	</form>
</div>
