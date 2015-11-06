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

?>

<div role="tabpanel" class="tab-pane" id="daten">
	<h2><?php echo $p->t('bewerbung/menuPersDaten') ?></h2>
	<?php

	$nation = new nation();
	$nation->getAll($ohnesperre = true);
	$titelpre = ($person->titelpre != '')?$person->titelpre:'';
	$vorname = ($person->vorname != '')?$person->vorname:'';
	$nachname = ($person->nachname != '')?$person->nachname:'';
	$titelpost = ($person->titelpost != '')?$person->titelpost:'';
	$geburtstag = ($person->gebdatum != '')?$datum->formatDatum($person->gebdatum, 'd.m.Y'):'';
	$gebort =  ($person->gebort != '')?$person->gebort:'';

	$svnr = ($person->svnr != '')?$person->svnr:'';

	$disabled='';
	if($eingabegesperrt)
	{
		$disabled='disabled="disabled"';
		echo $p->t('bewerbung/accountVorhanden');
	}

	/*if($save_error)
	{
		echo '<div class="bg-danger">
		    <h4>'.$p->t('global/fehlerBeimSpeichernDerDaten').'</h4>
		    <p>'.$message.'</p>
		  </div>';
	}*/
	if($save_error===false)
	{
		echo '	<div class="alert alert-success" id="success-alert">
				<button type="button" class="close" data-dismiss="alert">x</button>
					<strong>'.$p->t('global/erfolgreichgespeichert').'</strong>
				</div>';
	}
	elseif($save_error===true)
	{
		echo '	<div class="alert alert-danger" id="danger-alert">
			<button type="button" class="close" data-dismiss="alert">x</button>
				<strong>'.$p->t('global/fehleraufgetreten').' </strong>'.$message.'
			</div>';
	}
	

	?>

	<form method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>?active=daten" class="form-horizontal">
		<div class="form-group">
			<label for="titel_pre" class="col-sm-3 control-label"><?php echo $p->t('global/titel') ?></label>
			<div class="col-sm-9">
				<input type="text" name="titel_pre" id="titel_pre" <?php echo $disabled; ?> value="<?php echo $titelpre ?>" class="form-control">
			</div>
		</div>
		<div class="form-group">
			<label for="vorname" class="col-sm-3 control-label"><?php echo $p->t('global/vorname') ?>*</label>
			<div class="col-sm-9">
				<input type="text" name="vorname" id="vorname"  <?php echo $disabled; ?> value="<?php echo $vorname ?>" class="form-control">
			</div>
		</div>
		<div class="form-group">
			<label for="nachname" class="col-sm-3 control-label"><?php echo $p->t('global/nachname') ?>*</label>
			<div class="col-sm-9">
				<input type="text" name="nachname" id="nachname"  <?php echo $disabled; ?> value="<?php echo $nachname ?>" class="form-control">
			</div>
		</div>
		<div class="form-group">
			<label for="titel_post" class="col-sm-3 control-label"><?php echo $p->t('global/postnomen') ?></label>
			<div class="col-sm-9">
				<input type="text" name="titel_post" id="titel_post"  <?php echo $disabled; ?> value="<?php echo $titelpost ?>" class="form-control">
			</div>
		</div>
		<div class="form-group">
			<label for="gebdatum" class="col-sm-3 control-label"><?php echo $p->t('global/geburtsdatum') ?>* (<?php echo $p->t('bewerbung/datumFormat') ?>)</label>
			<div class="col-sm-9">
				<input type="text" name="geburtsdatum" id="gebdatum"  <?php echo $disabled; ?> value="<?php echo $geburtstag ?>" class="form-control">
			</div>
		</div>
		<div class="form-group">
			<label for="gebort" class="col-sm-3 control-label"><?php echo $p->t('global/geburtsort') ?></label>
			<div class="col-sm-9">
				<input type="text" name="gebort" id="gebort"  <?php echo $disabled; ?> value="<?php echo $gebort ?>" class="form-control">
			</div>
		</div>
		<div class="form-group">
			<label for="geburtsnation" class="col-sm-3 control-label"><?php echo $p->t('bewerbung/geburtsnation') ?></label>
			<div class="col-sm-9">
				<select name="geburtsnation" id="geburtsnation"  <?php echo $disabled; ?> class="form-control">
					<option value=""><?php echo $p->t('bewerbung/bitteAuswaehlen') ?></option>
					<?php $selected = '';
					foreach($nation->nation as $nat):
						$selected = ($person->geburtsnation == $nat->code) ? 'selected' : ''; ?>
						<option value="<?php echo $nat->code ?>" <?php echo $selected ?>>
							<?php
							if($sprache=='German')
								echo $nat->langtext;
							else
								echo $nat->engltext;
							?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<div class="form-group">
			<label for="staatsbuergerschaft" class="col-sm-3 control-label"><?php echo $p->t('global/staatsbuergerschaft') ?>*</label>
			<div class="col-sm-9">
				<select name="staatsbuergerschaft" id="staatsbuergerschaft"  <?php echo $disabled; ?> class="form-control">
					<option value=""><?php echo $p->t('bewerbung/bitteAuswaehlen') ?></option>
					<?php $selected = '';
					foreach($nation->nation as $nat):
						$selected = ($person->staatsbuergerschaft == $nat->code) ? 'selected' : ''; ?>
						<option value="<?php echo $nat->code ?>" <?php echo $selected ?>>
							<?php
								if($sprache=='German')
									echo $nat->langtext;
								else
									echo $nat->engltext;
							?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<div class="form-group">
			<label for="svnr" class="col-sm-3 control-label"><?php echo $p->t('bewerbung/svnr').' '.$p->t('bewerbung/fallsVorhanden') ?></label>
			<div class="col-sm-9">
				<input type="text" name="svnr" id="svnr"  <?php echo $disabled; ?> value="<?php echo $svnr ?>" class="form-control">
			</div>
		</div>
		<div class="form-group">
			<label for="geschlecht" class="col-sm-3 control-label"><?php echo $p->t('global/geschlecht') ?></label>
			<div class="col-sm-9">
				<?php
				$geschl_m = ($person->geschlecht == 'm') ? 'checked' : '';
				$geschl_w = ($person->geschlecht == 'w') ? 'checked' : '';
				?>
				<?php echo $p->t('bewerbung/maennlich') ?>: <input type="radio" name="geschlecht"  <?php echo $disabled; ?> value="m" <?php echo $geschl_m ?>>
				<?php echo $p->t('bewerbung/weiblich') ?>: <input type="radio" name="geschlecht"  <?php echo $disabled; ?> value="w" <?php echo $geschl_w ?>>
			</div>
		</div>
		<?php if(isset($prestudent->result[0])): ?>
        <div class="form-group">
			<label for="aufmerksamdurch" class="col-sm-3 control-label"><?php echo $p->t('bewerbung/aufmerksamdurch') ?></label>
			<div class="col-sm-9">
				<select name="aufmerksamdurch" id="aufmerksamdurch"  <?php echo $disabled; ?> class="form-control">
					<option value=""><?php echo $p->t('bewerbung/bitteAuswaehlen') ?></option>
					<?php
					$aufmerksamdurch = new aufmerksamdurch();
					$aufmerksamdurch->getAll();

					$selected = '';

					if(isset($prestudent->result[0]) && $prestudent->result[0]->aufmerksamdurch_kurzbz!='')
						$aufmerksamdurch_kurzbz = $prestudent->result[0]->aufmerksamdurch_kurzbz;
					else
						$aufmerksamdurch_kurzbz ='';

					foreach($aufmerksamdurch->result as $row_aufm):
						if($row_aufm->aktiv):
						$selected = ($aufmerksamdurch_kurzbz == $row_aufm->aufmerksamdurch_kurzbz) ? 'selected' : ''; ?>
						<option value="<?php echo $row_aufm->aufmerksamdurch_kurzbz; ?>" <?php echo $selected ?>>
							<?php echo $row_aufm->bezeichnung[$sprache]; ?>
						</option>
					<?php endif; endforeach; ?>
				</select>
			</div>
		</div>
        <?php endif; ?>
		<?php
		if(!defined('BEWERBERTOOL_BERUFSTAETIGKEIT_ANZEIGEN') || BEWERBERTOOL_BERUFSTAETIGKEIT_ANZEIGEN):
		?>
        <fieldset>
            <legend><?php echo $p->t('bewerbung/berufstaetigkeit') ?></legend>
            <?php
            $notiz = new notiz;
            $notiz->getBewerbungstoolNotizen($person_id);
            if(count($notiz->result)):
                foreach($notiz->result as $berufstaetig): ?>
                    <div class="form-group">
                        <label for="berufstaetig" class="col-sm-3 control-label">
                            <?php echo $p->t('bewerbung/eintragVom') ?> <?php echo date('j.n.y H:i', strtotime($berufstaetig->insertamum)) ?>
                        </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" disabled value="<?php echo $berufstaetig->text ?>">
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="form-group">
                    <label for="berufstaetig" class="col-sm-3 control-label"><?php echo $p->t('bewerbung/berufstaetig') ?></label>
                    <div class="col-sm-9">
                        <?php echo $p->t('bewerbung/orgform/vollzeit') ?>: <input type="radio" name="berufstaetig" value="Vollzeit">
                        <?php echo $p->t('bewerbung/orgform/teilzeit') ?>: <input type="radio" name="berufstaetig" value="Teilzeit">
                        <?php echo $p->t('global/nein') ?>: <input type="radio" name="berufstaetig" value="n" checked>
                    </div>
                </div>
                <div class="form-group">
                    <label for="berufstaetig_dienstgeber" class="col-sm-3 control-label"><?php echo $p->t('bewerbung/dienstgeber') ?></label>
                    <div class="col-sm-9">
                        <input type="text" name="berufstaetig_dienstgeber" id="berufstaetig_dienstgeber" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label for="berufstaetig_art" class="col-sm-3 control-label"><?php echo $p->t('bewerbung/artDerTaetigkeit') ?></label>
                    <div class="col-sm-9">
                        <input type="text" name="berufstaetig_art" id="berufstaetig_art" class="form-control">
                    </div>
                </div>
            <?php endif; ?>
        </fieldset>
		<?php
		endif;
		?>
		<button class="btn-nav btn btn-default" type="button" data-jump-tab="allgemein">
			<?php echo $p->t('global/zurueck') ?>
		</button>
		<button class="btn btn-default" type="submit"  <?php echo $disabled; ?> name="btn_person">
			<?php echo $p->t('global/speichern') ?>
		</button>
		<button class="btn-nav btn btn-default" type="button" data-jump-tab="kontakt">
			<?php echo $p->t('bewerbung/weiter') ?>
		</button>
	</form>
</div>
