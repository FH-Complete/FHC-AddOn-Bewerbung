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
 * 			Gerald Raab <raab@technikum-wien.at>
 *
 * Speichern der Schulbildung zur ZGV
 * Wird als Erstlösung in eine Notiz zur Person gespeichert
 * TODO: Umbau in UDF
 */

if(!isset($person_id))
{
	die($p->t('bewerbung/ungueltigerZugriff'));
}

?>

<div role="tabpanel" class="tab-pane" id="ausbildung">
	<h2><?php echo $p->t('bewerbung/menuAusbildung') ?></h2>
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
		echo '<div class="alert alert-info">'.$p->t('bewerbung/accountVorhanden').'</div>';
	}

	/*if($save_error)
	{
		echo '<div class="bg-danger">
		    <h4>'.$p->t('global/fehlerBeimSpeichernDerDaten').'</h4>
		    <p>'.$message.'</p>
		  </div>';
	}*/
	if($save_error_ausbildung===false)
	{
		echo '	<div class="alert alert-success" id="success-alert_daten">
				<button type="button" class="close" data-dismiss="alert">x</button>
					<strong>'.$p->t('global/erfolgreichgespeichert').'</strong>
				</div>';
	}
	elseif($save_error_ausbildung===true)
	{
		echo '	<div class="alert alert-danger" id="danger-alert">
			<button type="button" class="close" data-dismiss="alert">x</button>
				<strong>'.$p->t('global/fehleraufgetreten').' </strong>'.$message.'
			</div>';
	}


	?>

	<form method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>?active=ausbildung" class="form-horizontal">

		<?php
		if(defined('BEWERBERTOOL_AUSBILDUNG_ANZEIGEN') || BEWERBERTOOL_AUSBILDUNG_ANZEIGEN):
		?>
        <fieldset>
            <legend><?php echo $p->t('bewerbung/ausbildung') ?></legend>
            <?php
            $notiz = new notiz;
            $notiz->getBewerbungstoolNotizenAusbildung($person_id);
            $counter = 0;
            if(count($notiz->result)>0):
                foreach($notiz->result as $ausbildung): ?>
                	<?php if($ausbildung->insertvon == 'online_ausbildung'): $counter++ ?>
						<?php
						$ausbildung_arr = explode(';', $ausbildung->text);
						$schulename = explode(':',$ausbildung_arr[0])[1];
						$schuleadresse = explode(':',$ausbildung_arr[1])[1];
						?>
						<div class="form-group">
		                    <label for="ausbildung_schule" class="col-sm-3 control-label"><?php echo $p->t('bewerbung/ausbildungSchule') ?></label>
		                    <div class="col-sm-9">
		                        <input type="text" name="ausbildung_schule" id="ausbildung_schule" class="form-control" value='<?php echo $schulename ?>' disabled>
		                    </div>
		                </div>
		                <div class="form-group">
		                    <label for="ausbildung_schuleadresse" class="col-sm-3 control-label"><?php echo $p->t('bewerbung/ausbildungSchuleAdresse') ?></label>
		                    <div class="col-sm-9">
		                        <textarea name="ausbildung_schuleadresse" id="ausbildung_schuleadresse" class="form-control" rows="5" disabled><?php echo $schuleadresse ?></textarea>
		                    </div>
		                </div>
                    <?php endif; ?>
                <?php endforeach;
				if ($counter > 0) $disabled = 'disabled';
                ?>
            <?php else: ?>
                <div class="form-group">
                    <label for="ausbildung_schule" class="col-sm-3 control-label"><?php echo $p->t('bewerbung/ausbildungSchule') ?></label>
                    <div class="col-sm-9">
                        <input type="text" name="ausbildung_schule" id="ausbildung_schule" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label for="ausbildung_schuleadresse" class="col-sm-3 control-label"><?php echo $p->t('bewerbung/ausbildungSchuleAdresse') ?></label>
                    <div class="col-sm-9">
                        <textarea name="ausbildung_schuleadresse" id="ausbildung_schuleadresse" class="form-control" rows="5"></textarea>
                    </div>
                </div>
            <?php endif; ?>
        </fieldset>
		<?php
		endif;
		?>
		<button class="btn-nav btn btn-default" type="button" data-jump-tab="<?php echo $tabs[array_search('ausbildung', $tabs)-1] ?>">
			<?php echo $p->t('global/zurueck') ?>
		</button>
		<button class="btn btn-success" type="submit"  <?php echo $disabled; ?> name="btn_ausbildung">
			<?php echo $p->t('global/speichern') ?>
		</button>
		<button class="btn-nav btn btn-default" type="button" data-jump-tab="<?php echo $tabs[array_search('ausbildung', $tabs)+1] ?>">
			<?php echo $p->t('bewerbung/weiter') ?>
		</button><br/><br/>
	</form>
</div>
