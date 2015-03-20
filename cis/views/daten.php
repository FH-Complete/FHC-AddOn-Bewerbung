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

<div role="tabpanel" class="tab-pane" id="daten">
	<h2>Persönliche Daten</h2>
	<?php

	$nation = new nation();
	$nation->getAll($ohnesperre = true);
	$titelpre = ($person->titelpre != '')?$person->titelpre:'';
	$vorname = ($person->vorname != '')?$person->vorname:'';
	$nachname = ($person->nachname != '')?$person->nachname:'';
	$titelpost = ($person->titelpost != '')?$person->titelpost:'';
	$geburtstag = ($person->gebdatum != '')?$datum->formatDatum($person->gebdatum, 'd.m.Y'):'';
	$gebort =  ($person->gebort != '')?$person->gebort:'';

	$svnr = ($person->svnr != '')?$person->svnr:''; ?>

	<form method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>?active=daten" class="form-horizontal">
		<div class="form-group">
			<label for="titel_pre" class="col-sm-3 control-label">Titel vorgestellt</label>
			<div class="col-sm-9">
				<input type="text" name="titel_pre" id="titel_pre" value="<?php echo $titelpre ?>" class="form-control">
			</div>
		</div>
		<div class="form-group">
			<label for="vorname" class="col-sm-3 control-label">Vorname*</label>
			<div class="col-sm-9">
				<input type="text" name="vorname" id="vorname" value="<?php echo $vorname ?>" class="form-control">
			</div>
		</div>
		<div class="form-group">
			<label for="nachname" class="col-sm-3 control-label">Nachname*</label>
			<div class="col-sm-9">
				<input type="text" name="nachname" id="nachname" value="<?php echo $nachname ?>" class="form-control">
			</div>
		</div>
		<div class="form-group">
			<label for="titel_post" class="col-sm-3 control-label">Titel nachgestellt</label>
			<div class="col-sm-9">
				<input type="text" name="titel_post" id="titel_post" value="<?php echo $titelpost ?>" class="form-control">
			</div>
		</div>
		<div class="form-group">
			<label for="gebdatum" class="col-sm-3 control-label">Geburtsdatum* (dd.mm.yyyy)</label>
			<div class="col-sm-9">
				<input type="text" name="geburtsdatum" id="gebdatum" value="<?php echo $geburtstag ?>" class="form-control">
			</div>
		</div>
		<div class="form-group">
			<label for="gebort" class="col-sm-3 control-label">Geburtsort</label>
			<div class="col-sm-9">
				<input type="text" name="gebort" id="gebort" value="<?php echo $gebort ?>" class="form-control">
			</div>
		</div>
		<div class="form-group">
			<label for="geburtsnation" class="col-sm-3 control-label">Geburtsnation</label>
			<div class="col-sm-9">
				<select name="geburtsnation" id="geburtsnation" class="form-control">
					<option value="">-- Bitte auswählen -- </option>
					<?php $selected = '';
					foreach($nation->nation as $nat):
						$selected = ($person->geburtsnation == $nat->code) ? 'selected' : ''; ?>
						<option value="<?php echo $nat->code ?>" <?php echo $selected ?>>
							<?php echo $nat->kurztext ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<div class="form-group">
			<label for="staatsbuergerschaft" class="col-sm-3 control-label">Staatsbürgerschaft*</label>
			<div class="col-sm-9">
				<select name="staatsbuergerschaft" id="staatsbuergerschaft" class="form-control">
					<option value="">-- Bitte auswählen -- </option>";
					<?php $selected = '';
					foreach($nation->nation as $nat):
						$selected = ($person->staatsbuergerschaft == $nat->code) ? 'selected' : ''; ?>
						<option value="<?php echo $nat->code ?>" <?php echo $selected ?>>
							<?php echo $nat->kurztext ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<div class="form-group">
			<label for="svnr" class="col-sm-3 control-label">Österr. Sozialversicherungsnr</label>
			<div class="col-sm-9">
				<input type="text" name="svnr" id="svnr" value="<?php echo $svnr ?>" class="form-control">
			</div>
		</div>
		<div class="form-group">
			<label for="geschlecht" class="col-sm-3 control-label">Geschlecht</label>
			<div class="col-sm-9">
				<?php
				$geschl_m = ($person->geschlecht == 'm') ? 'checked' : '';
				$geschl_w = ($person->geschlecht == 'w') ? 'checked' : '';
				?>
				m: <input type="radio" name="geschlecht" value="m" <?php echo $geschl_m ?>>
				w: <input type="radio" name="geschlecht" value="w" <?php echo $geschl_w ?>>
			</div>
		</div>
        <fieldset>
            <legend>Berufstätigkeit</legend>
            <?php
            $notiz = new notiz;
            $notiz->getBewerbungstoolNotizen($person_id);
            if(count($notiz->result)):
                foreach($notiz->result as $berufstaetig): ?>
                    <div class="form-group">
                        <label for="berufstaetig" class="col-sm-3 control-label">
                            Eintrag vom <?php echo date('j.n.y H:i', strtotime($berufstaetig->insertamum)) ?>
                        </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" disabled value="<?php echo $berufstaetig->text ?>">
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="form-group">
                    <label for="berufstaetig" class="col-sm-3 control-label">berufstätig</label>
                    <div class="col-sm-9">
                        Ja: <input type="radio" name="berufstaetig" value="j">
                        Nein: <input type="radio" name="berufstaetig" value="n" checked>
                    </div>
                </div>
                <div class="form-group">
                    <label for="berufstaetig_dienstgeber" class="col-sm-3 control-label">Dienstgeber</label>
                    <div class="col-sm-9">
                        <input type="text" name="berufstaetig_dienstgeber" id="berufstaetig_dienstgeber" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label for="berufstaetig_art" class="col-sm-3 control-label">Art der Tätigkeit</label>
                    <div class="col-sm-9">
                        <input type="text" name="berufstaetig_art" id="berufstaetig_art" class="form-control">
                    </div>
                </div>
            <?php endif; ?>
        </fieldset>
		<button class="btn-nav btn btn-default" type="button" data-jump-tab="allgemein">
			Zurück
		</button>
		<button class="btn btn-default" type="submit" name="btn_person">
			Speichern
		</button>
		<button class="btn-nav btn btn-default" type="button" data-jump-tab="kontakt">
			Weiter
		</button>
	</form>
	<?php echo $message; ?>
</div>

