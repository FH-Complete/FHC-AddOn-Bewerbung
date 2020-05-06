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

$zgv_options = new zgv();
$zgv_options->getAll();

$zgvma_options = new zgv();
$zgvma_options->getAllMaster();

$zgv = $prestudent->getZgv();
$studiengaenge = array();
$types = array();
$studienplan = new studienplan();
$stg = new studiengang();

foreach ($prestudent->result as $prestudent_eintrag)
{
	$studiengaenge[] = $prestudent_eintrag->studiengang_kz;
}
$sprache = getSprache();
if(!empty($studiengaenge))
	$types = $stg->getTypes($studiengaenge);

if($save_error_zgv===false)
{
	echo '	<div class="alert alert-success" id="success-alert_zgv">
			<button type="button" class="close" data-dismiss="alert">x</button>
			<strong>'.$p->t('global/erfolgreichgespeichert').'</strong>
			</div>';
}
elseif($save_error_zgv===true)
{
	echo '	<div class="alert alert-danger" id="danger-alert_zgv">
			<button type="button" class="close" data-dismiss="alert">x</button>
				<strong>'.$p->t('global/fehleraufgetreten').' </strong>'.$message.'
			</div>';
}
?>
<div role="tabpanel" class="tab-pane" id="zgv">
	<h2><?php echo $p->t('bewerbung/menuZugangsvoraussetzungen'); ?></h2>
	<div class="alert alert-info">
		<?php echo $p->t('bewerbung/hinweisZGVdatenaenderung');
		if (CAMPUS_NAME!='FH Technikum Wien')
			echo '<br><br>'.$p->t('bewerbung/zgvDatumNichtZukunft');
		?>
	</div>
	<?php if(empty($prestudent->result)): ?>
	<p class="bg-danger" style="padding: 10px;">
		<?php echo $p->t('bewerbung/bitteZuerstStudiengangWaehlen'); ?>
	</p>
	<button class="btn-nav btn btn-default" type="button" data-jump-tab="<?php echo $tabs[array_search('zgv', $tabs)-1] ?>">
		<?php echo $p->t('global/zurueck') ?>
	</button>
	<button class="btn-nav btn btn-default" type="button" data-jump-tab="<?php echo $tabs[array_search('zgv', $tabs)+1] ?>">
		<?php echo $p->t('bewerbung/weiter'); ?>
	</button>

	<?php else: ?>
	<form method="POST" class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF'] ?>?active=zgv">
		<?php foreach ($zgv as $stufe => $attribute):
			if ($stufe === 'master' && !in_array('m', $types, true))
			{
				continue;
			}
			if (count($zgv[$stufe])>0)
				$gesperrt = true;

			// Hack um $stufe mit Phrasen zu überschreiben
			$stufe_bezeichung = '';

			if ($stufe == 'bachelor' && $p->t('bewerbung/hackStufeBezeichnungBachelor') != '')
				$stufe_bezeichung = $p->t('bewerbung/hackStufeBezeichnungBachelor');
			elseif ($stufe == 'master' && $p->t('bewerbung/hackStufeBezeichnungMaster') != '')
				$stufe_bezeichung = $p->t('bewerbung/hackStufeBezeichnungMaster');
			else
				$stufe_bezeichung = $stufe;
			?>
			<fieldset>
				<legend><?php echo $p->t('bewerbung/fuer'); ?> <?php echo ucfirst($stufe_bezeichung) ?></legend>
				<div class="form-group <?php echo (!isset($attribute['art'])?'has-error':'') ?>">
					<label for="<?php echo $stufe ?>_zgv_art" class="col-sm-3 control-label">
						<?php echo $p->t('bewerbung/artDerVoraussetzung'); ?>
					</label>

					<div class="col-sm-9">
						<select name="<?php echo $stufe ?>_zgv_art<?php echo ($stufe!=='master' && $eingabegesperrt==true || isset($attribute['art'])?'_disabled':'') ?>" id="<?php echo $stufe ?>_zgv_art" <?php echo ($stufe!=='master' && $eingabegesperrt==true || isset($attribute['art'])?'disabled':'') ?>
								class="form-control">
							<option value=""><?php echo $p->t('bewerbung/bitteAuswaehlen'); ?></option>
							<?php
							$selected = '';
							if($stufe==='master')
							{
								foreach ($zgvma_options->result as $zgvma_option)
								{
									$selected = (isset($attribute['art']) && $attribute['art'] == $zgvma_option->zgvmas_code) ? 'selected' : '';

									echo '<option value="'.$zgvma_option->zgvmas_code.'" '.$selected.'>'.$zgvma_option->convert_html_chars($zgvma_option->bezeichnung[$sprache]).'</option>';
								}

							}
							else
							{
								foreach ($zgv_options->result as $zgv_option)
								{
									$selected = (isset($attribute['art']) && $attribute['art'] == $zgv_option->zgv_code) ? 'selected' : '';

									echo '<option value="'.$zgv_option->zgv_code.'" '.$selected.'>'.$zgv_option->convert_html_chars($zgv_option->bezeichnung[$sprache]).'</option>';
								}
							}

							?>
						</select>
						<?php //Hidden inputfeld, wenn select=disabled, damit Daten per POST uebertragen werden
							echo ($stufe!=='master' && $eingabegesperrt==true || isset($attribute['art'])?'<input type="hidden" name="'.$stufe.'_zgv_art" value="'.(isset($attribute['art'])?$attribute['art']:'').'">':'') ?>
					</div>
				</div>
				<div class="form-group <?php echo (!isset($attribute['ort'])?'has-error':'') ?>">
					<label for="<?php echo $stufe ?>_zgv_ort" class="col-sm-3 control-label">
						<?php echo $p->t('global/ort'); ?>
					</label>

					<div class="col-sm-9">
						<input type="text" name="<?php echo $stufe ?>_zgv_ort" id="<?php echo $stufe ?>_zgv_ort"
							   class="form-control"
							   maxlength="64"
							   value="<?php echo isset($attribute['ort']) ? $attribute['ort'] : '' ?>"
							   placeholder="<?php echo $p->t('bewerbung/woWurdeUrkundeAusgestellt'); ?>" <?php echo ($stufe!=='master' && $eingabegesperrt==true || isset($attribute['ort'])?'readonly="readonly"':'') ?>>
					</div>
				</div>
				<div class="form-group <?php echo (!isset($attribute['nation'])?'has-error':'') ?>">
					<label for="<?php echo $stufe ?>_zgv_nation" class="col-sm-3 control-label"><?php echo $p->t('bewerbung/nation'); ?></label>

					<div class="col-sm-9">
						<select name="<?php echo $stufe ?>_zgv_nation" id="<?php echo $stufe ?>_zgv_nation" <?php echo ($stufe!=='master' && $eingabegesperrt==true || isset($attribute['nation'])?'disabled':'') ?>
								class="form-control">
							<option value=""><?php echo $p->t('bewerbung/bitteAuswaehlen'); ?></option>
							<?php $selected = '';
							foreach ($nation->nation as $nat):
								$selected = (isset($attribute['nation']) && $attribute['nation'] == $nat->code) ? 'selected' : ''; ?>
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
						<?php //Hidden inputfeld, wenn select=disabled, damit Daten per POST uebertragen werden
							echo ($stufe!=='master' && $eingabegesperrt==true || isset($attribute['nation'])?'<input type="hidden" name="'.$stufe.'_zgv_nation" value="'.(isset($attribute['nation'])?$attribute['nation']:'').'">':'') ?>
					</div>
				</div>
				<?php if (CAMPUS_NAME!='FH Technikum Wien'): ?>
				<div class="form-group <?php echo (!isset($attribute['datum'])?'has-error':'') ?>">
					<label for="<?php echo $stufe ?>_zgv_datum" class="col-sm-3 control-label">
						<?php echo $p->t('global/datum'); ?>
					</label>

					<div class="col-sm-9">
						<input type="text" name="<?php echo $stufe ?>_zgv_datum" id="<?php echo $stufe ?>_zgv_datum" <?php echo ($stufe!=='master' && $eingabegesperrt==true || isset($attribute['datum'])?'disabled':'') ?>
							   class="form-control"
							   value="<?php echo isset($attribute['datum']) ? date('d.m.Y', strtotime($attribute['datum'])) : '' ?>"
							   placeholder="<?php echo $p->t('bewerbung/datumFormat') ?>">
					</div>
				</div>
				<?php endif; ?>
			</fieldset>
		<?php endforeach ?>
		<button class="btn-nav btn btn-default" type="submit" name="btn_zgv" data-jump-tab="<?php echo $tabs[array_search('zgv', $tabs)-1] ?>" onclick="this.form.action='<?php echo $_SERVER['PHP_SELF'] ?>?active=<?php echo $tabs[array_search('zgv', $tabs)-1] ?>'">
			<?php echo $p->t('global/zurueck') ?>
		</button>
		<button class="btn btn-success" type="submit" name="btn_zgv">
			<?php echo $p->t('global/speichern') ?>
		</button>
		<button class="btn-nav btn btn-default" type="submit" name="btn_zgv" data-jump-tab="<?php echo $tabs[array_search('zgv', $tabs)+1] ?>" onclick="this.form.action='<?php echo $_SERVER['PHP_SELF'] ?>?active=<?php echo $tabs[array_search('zgv', $tabs)+1] ?>'">
			<?php echo $p->t('bewerbung/weiter'); ?>
		</button><br/><br/>
	</form>
	<?php endif; ?>
</div>
