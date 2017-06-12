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
 * Authors: Gerald Raab <raab@technikum-wien.at>
 */

if(!isset($person_id))
{
	die($p->t('bewerbung/ungueltigerZugriff'));
}
if($save_error_kontakt===false)
{
	echo '	<div class="alert alert-success" id="success-alert_kontakt">
			<button type="button" class="close" data-dismiss="alert">x</button>
			<strong>'.$p->t('global/erfolgreichgespeichert').'</strong>
			</div>';
}
elseif($save_error_kontakt===true)
{
	echo '	<div class="alert alert-danger" id="danger-alert_kontakt">
			<button type="button" class="close" data-dismiss="alert">x</button>
				<strong>'.$p->t('global/fehleraufgetreten').' </strong>'.$message.'
			</div>';
}
?>

<div role="tabpanel" class="tab-pane" id="rechnungskontakt">
	<h2><?php echo $p->t('bewerbung/menuRechnungsKontaktinformationen'); ?></h2>

	<b><?php echo $p->t('bewerbung/rechnungsadresseInfoText') ?></b><br><br>

	<?php
	$nation = new nation();
	$nation->getAll($ohnesperre=true);

	$kontakt = new kontakt();
	$kontakt->load_persKontakttyp($person->person_id, 're_email', 'updateamum DESC');
	$email = isset($kontakt->result[0]->kontakt)?$kontakt->result[0]->kontakt:'';

	$kontakt_t = new kontakt();
	$kontakt_t->load_persKontakttyp($person->person_id, 're_telefon', 'updateamum DESC');
	$telefon = isset($kontakt_t->result[0]->kontakt)?$kontakt_t->result[0]->kontakt:'';


	$adresse = new adresse();
	$adresse->load_rechnungsadresse($person->person_id);
	$strasse = isset($adresse->result[0]->strasse)?$adresse->result[0]->strasse:'';
	$plz = isset($adresse->result[0]->plz)?$adresse->result[0]->plz:'';
	$ort = isset($adresse->result[0]->ort)?$adresse->result[0]->ort:'';
	$gemeinde = isset($adresse->result[0]->gemeinde)?$adresse->result[0]->gemeinde:'';
	$adr_nation = isset($adresse->result[0]->nation)?$adresse->result[0]->nation:'';
	$name = isset($adresse->result[0]->name)?$adresse->result[0]->name:'|||';
	$name_arr = explode('|', $name);
	$re_anrede = $name_arr[0];
	$re_titel = $name_arr[1];
	$re_vorname = $name_arr[2];
	$re_nachname = $name_arr[3];

	$disabled='';
	/*
	if($eingabegesperrt)
	{
		$disabled='disabled="disabled"';
		echo '<div class="alert alert-info">'.$p->t('bewerbung/accountVorhanden').'</div>';
	}
	*/
	?>


	<form method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>?active=rechnungskontakt" class="form-horizontal">
		<fieldset>
			<legend><?php echo $p->t('bewerbung/rechnungsKontakt') ?></legend>

			<div class="form-group">
				<label for="email" class="col-sm-2 control-label"><?php echo $p->t('global/emailAdresse') ?></label>
				<div class="col-sm-10">
					<input type="text" name="re_email" id="re_email" value="<?php echo $email ?>" size="32" class="form-control">
				</div>
			</div>
			<div class="form-group">
				<label for="telefonnummer" class="col-sm-2 control-label"><?php echo $p->t('global/telefonnummer') ?></label>
				<div class="col-sm-10">
					<input type="text" name="re_telefonnummer" id="re_telefonnummer" value="<?php echo $telefon ?>" size="32" class="form-control">
				</div>
			</div>
		</fieldset>

		<fieldset>
			<legend><?php echo $p->t('bewerbung/rechnungsAdresse') ?></legend>
			<div class="form-group">
				<label for="re_anrede" class="col-sm-2 control-label"><?php echo $p->t('bewerbung/re_anrede') ?></label>
				<div class="col-sm-10">
					<select name="re_anrede">
						<option value="">--</option>
						<?php $selected = ($re_anrede == 'Frau')?' selected':''; ?>
						<option value="Frau"<?php echo $selected; ?>>Frau</option>
						<?php $selected = ($re_anrede == 'Herr')?' selected':''; ?>
					  	<option value="Herr"<?php echo $selected; ?>>Herr</option>
					</select>
				</div>
			</div>
			<div class="form-group">
				<label for="re_titel" class="col-sm-2 control-label"><?php echo $p->t('bewerbung/re_titel') ?></label>
				<div class="col-sm-10">
					<input type="text" name="re_titel" id="re_titel" value="<?php echo $re_titel; ?>">
				</div>
			</div>
			<div class="form-group">
				<label for="re_vorname" class="col-sm-2 control-label"><?php echo $p->t('bewerbung/re_vorname') ?></label>
				<div class="col-sm-10">
					<input type="text" name="re_vorname" id="re_vorname" value="<?php echo $re_vorname; ?>">
				</div>
			</div>
			<div class="form-group">
				<label for="re_namename" class="col-sm-2 control-label"><?php echo $p->t('bewerbung/re_nachname') ?></label>
				<div class="col-sm-10">
					<input type="text" name="re_nachname" id="re_nachname" value="<?php echo $re_nachname; ?>">
				</div>
			</div>

			<div class="form-group">
				<label for="nation" class="col-sm-2 control-label"><?php echo $p->t('bewerbung/nation') ?></label>
				<div class="col-sm-10">
					<select name="re_nation" id="re_nation" class="form-control" >
						<option value=""><?php echo $p->t('bewerbung/bitteAuswaehlen') ?></option>
						<option value="A"><?php	echo ($sprache=='German'? 'Österreich':'Austria'); ?></option>
						<?php
						foreach($nation->nation as $nat):
							$selected = ($adr_nation == $nat->code)?'selected':''; ?>
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
				<label for="strasse" class="col-sm-2 control-label"><?php echo $p->t('global/strasse') ?></label>
				<div class="col-sm-10">
					<input type="text" name="re_strasse" id="re_strasse" maxlength="256" value="<?php echo $strasse ?>" class="form-control">
				</div>
			</div>
			<div class="form-group">
				<label for="plz" class="col-sm-2 control-label"><?php echo $p->t('global/plz') ?></label>
				<div class="col-sm-10">
					<input type="text" name="re_plz" id="re_plz" maxlength="16" value="<?php echo $plz ?>">
				</div>
			</div>
			<div class="form-group">
				<label for="ort" class="col-sm-2 control-label"><?php echo $p->t('global/ort') ?></label>
				<div class="col-sm-10">
					<input type="text" name="re_ort" id="re_ort_input" maxlength="256" value="<?php echo $ort ?>"  class="form-control">
					<select id="re_ort_dropdown" name="re_ort" class="form-control"></select>
					<input type="hidden" name="re_gemeinde" id="re_gemeinde_input" value="<?php echo $gemeinde ?>" class="form-control">
				</div>
			</div>
		</fieldset>
		<button class="btn-nav btn btn-default" type="submit" name="btn_rechnungskontakt" data-jump-tab="<?php echo $tabs[array_search('rechnungskontakt', $tabs)-1] ?>" onclick="this.form.action='<?php echo $_SERVER['PHP_SELF'] ?>?active=<?php echo $tabs[array_search('rechnungskontakt', $tabs)-1] ?>'">
			<?php echo $p->t('global/zurueck') ?>
		</button>
		<button class="btn btn-success" type="submit"  <?php echo $disabled; ?> name="btn_rechnungskontakt">
			<?php echo $p->t('global/speichern') ?>
		</button>
		<button class="btn-nav btn btn-default" type="submit" name="btn_rechnungskontakt" data-jump-tab="<?php echo $tabs[array_search('rechnungskontakt', $tabs)+1] ?>" onclick="this.form.action='<?php echo $_SERVER['PHP_SELF'] ?>?active=<?php echo $tabs[array_search('rechnungskontakt', $tabs)+1] ?>'">
			<?php echo $p->t('bewerbung/weiter') ?>
		</button><br/><br/>
	</form>
	<script type="text/javascript">
		$(function()
		{
			$(document).ready(function()
			{
				if($('#re_nation').val() == "A")
				{
					$("#re_ort_input").hide();
					$("#re_ort_dropdown").show();
					var plz = $("#re_plz").val();
					loadOrtData(plz, $("#re_ort_dropdown"));
				}
				else
				{
					$("#re_ort_dropdown").hide();
					$("#re_ort_input").show();
				}
			});
			$('#re_nation').change(function()
			{
				if($('#re_nation').val() != "")
				{
					$('#re_strasse').prop('disabled', false);
					$('#re_plz').prop('disabled', false);
					if($('#re_nation').val() == "A")
					{
						$("#re_ort_input").hide();
						$("#re_ort_dropdown").show();
						if($('#re_ort_dropdown').val() == null)
							$("#re_ort_dropdown").html("<option value=''><?php echo $p->t('bewerbung/bitteGueltigeOesterreichischePlzEingeben') ?></option>");
					}
					else
					{
						$('#re_ort_input').prop('disabled', false);
						$('#re_ort_dropdown').empty();
						$("#re_ort_dropdown").hide();
						$("#re_ort_input").show();
					}
				}
				else
				{
					$('#re_strasse').prop('disabled', true);
					$('#re_plz').prop('disabled', true);
				}
			});
			$('#re_plz').on("input", function()
			{
				if ($('#re_nation').val() == "A")
				{
					var plz = $("#re_plz").val();
					if ($.isNumeric(plz))
						loadOrtData(plz, $("#re_ort_dropdown"));
					else
						$("#re_ort_dropdown").html("<option value=''><?php echo $p->t('bewerbung/plzMussGueltigSein') ?></option>");
				}
			});
		});
		function loadOrtData(plz, element)
		{
			postdata = {
				plz: plz,
				getGemeinden: true,
			};

			$.ajax({
				url: basename,
				data: postdata,
				type: 'POST',
				dataType: "json",
				success: function(data)
				{
					if(data.status=='error')
						alert(data.msg);
					else
					{
						//$(element).prop('disabled', false);
						$(element).empty();
						$('#re_ort_input').empty();
						if (data.gemeinden != '')
						{
							$.each(data.gemeinden, function (i, v)
							{
								if (v.ortschaftsname === '<?php echo $ort ?>')
								{
									$(element).append("<option value='" + v.ortschaftsname + "' selected='selected'>" + v.ortschaftsname + "</option>");
									$('#gemeinde_input').val(v.gemeindename);
								}
								else
								{
									$(element).append("<option value='" + v.ortschaftsname + "'>" + v.ortschaftsname + "</option>");
									$('#re_gemeinde_input').val(v.gemeindename);
								}

							});
						}
						else
							$(element).html("<option value=''><?php echo $p->t('bewerbung/plzUnbekannt') ?></option>");
					}
				},
				error: function(data)
				{
					alert(data.msg)
				}
			});
		}
	</script>
</div>
