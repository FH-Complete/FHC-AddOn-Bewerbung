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

    <?php if(empty($prestudent->result)): ?>
    <p class="bg-danger" style="padding: 10px;">
        <?php echo $p->t('bewerbung/bitteZuerstStudiengangWaehlen'); ?>
    </p>
    <button class="btn-nav btn btn-default" type="button" data-jump-tab="dokumente">
        <?php echo $p->t('global/zurueck') ?>
    </button>
    <button class="btn-nav btn btn-default" type="button" data-jump-tab="zahlungen">
        <?php echo $p->t('bewerbung/weiter'); ?>
    </button>

    <?php else: ?>
    <form method="POST" class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF'] ?>?active=zgv">
        <?php foreach ($zgv as $stufe => $attribute):
            if ($stufe === 'master' && !in_array('m', $types, true)) {
                continue;
            } ?>
            <fieldset>
                <legend><?php echo $p->t('bewerbung/fuer'); ?> <?php echo ucfirst($stufe) ?></legend>
                <div class="form-group <?php echo (!isset($attribute['art'])?'has-error':'') ?>">
                    <label for="<?php echo $stufe ?>_zgv_art" class="col-sm-3 control-label">
                        <?php echo $p->t('bewerbung/artDerVoraussetzung'); ?>
                    </label>

                    <div class="col-sm-9">
                        <select name="<?php echo $stufe ?>_zgv_art" id="<?php echo $stufe ?>_zgv_art"
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
                    </div>
                </div>
                <div class="form-group <?php echo (!isset($attribute['ort'])?'has-error':'') ?>">
                    <label for="<?php echo $stufe ?>_zgv_ort" class="col-sm-3 control-label">
                        <?php echo $p->t('global/ort'); ?>
                    </label>

                    <div class="col-sm-9">
                        <input type="text" name="<?php echo $stufe ?>_zgv_ort" id="<?php echo $stufe ?>_zgv_ort"
                               class="form-control"
                               value="<?php echo isset($attribute['ort']) ? $attribute['ort'] : '' ?>"
                               placeholder="<?php echo $p->t('bewerbung/woWurdeUrkundeAusgestellt'); ?>">
                    </div>
                </div>
                <div class="form-group <?php echo (!isset($attribute['nation'])?'has-error':'') ?>">
                    <label for="<?php echo $stufe ?>_zgv_nation" class="col-sm-3 control-label"><?php echo $p->t('bewerbung/nation'); ?></label>

                    <div class="col-sm-9">
                        <select name="<?php echo $stufe ?>_zgv_nation" id="<?php echo $stufe ?>_zgv_nation"
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
                    </div>
                </div>
                <div class="form-group <?php echo (!isset($attribute['datum'])?'has-error':'') ?>">
                    <label for="<?php echo $stufe ?>_zgv_datum" class="col-sm-3 control-label">
                        <?php echo $p->t('global/datum'); ?>
                    </label>

                    <div class="col-sm-9">
                        <input type="text" name="<?php echo $stufe ?>_zgv_datum" id="<?php echo $stufe ?>_zgv_datum"
                               class="form-control"
                               value="<?php echo isset($attribute['datum']) ? date('d.m.Y', strtotime($attribute['datum'])) : '' ?>"
                               placeholder="<?php echo $p->t('bewerbung/datumFormat') ?>">
                    </div>
                </div>
            </fieldset>
        <?php endforeach ?>
        <button class="btn-nav btn btn-default" type="button" data-jump-tab="<?php echo $tabs[array_search('zgv', $tabs)-1] ?>">
            <?php echo $p->t('global/zurueck') ?>
        </button>
        <button class="btn btn-success" type="submit" name="btn_zgv">
            <?php echo $p->t('global/speichern') ?>
        </button>
        <button class="btn-nav btn btn-default" type="button" data-jump-tab="<?php echo $tabs[array_search('zgv', $tabs)+1] ?>">
            <?php echo $p->t('bewerbung/weiter'); ?>
        </button><br/><br/>
    </form>
    <?php endif; ?>
</div>
