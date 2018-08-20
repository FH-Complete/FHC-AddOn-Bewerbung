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

<div role="tabpanel" class="tab-pane" id="kontakt">
	<h2><?php echo $p->t('bewerbung/menuKontaktinformationen'); ?></h2>
	<?php
	$nation = new nation();
	$nation->getAll($ohnesperre=true);

	$kontakt = new kontakt();
	$kontakt->load_persKontakttyp($person->person_id, 'email', 'updateamum DESC');
	$email = isset($kontakt->result[0]->kontakt)?$kontakt->result[0]->kontakt:'';

	$kontakt_t = new kontakt();
	$kontakt_t->load_persKontakttyp($person->person_id, 'telefon', 'updateamum DESC');
	$telefon = isset($kontakt_t->result[0]->kontakt)?$kontakt_t->result[0]->kontakt:'';
	//Wenn Telefonnumer leer, alternativ Mobilnummer abfragen
	if($telefon=='')
	{
		$kontakt_t->load_persKontakttyp($person->person_id, 'mobil');
		$telefon = isset($kontakt_t->result[0]->kontakt)?$kontakt_t->result[0]->kontakt:'';
	}	
		
	$adresse = new adresse();
	$adresse->load_pers($person->person_id);
	$strasse = isset($adresse->result[0]->strasse)?$adresse->result[0]->strasse:'';
	$plz = isset($adresse->result[0]->plz)?$adresse->result[0]->plz:'';
	$ort = isset($adresse->result[0]->ort)?$adresse->result[0]->ort:'';
	$gemeinde = isset($adresse->result[0]->gemeinde)?$adresse->result[0]->gemeinde:'';
	$adr_nation = isset($adresse->result[0]->nation)?$adresse->result[0]->nation:'';

	$disabled = '';
	if($eingabegesperrt)
	{
		$disabled='disabled="disabled"';
		echo '<div class="alert alert-info">'.$p->t('bewerbung/accountVorhanden').'</div>';
	}
	?>


	<form method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>?active=kontakt" class="form-horizontal">
		<fieldset>
			<legend><?php echo $p->t('bewerbung/kontakt') ?></legend>
			<div class="form-group <?php echo ($email==''?'has-error':'') ?>">
				<label for="email" class="col-sm-2 control-label"><?php echo $p->t('global/emailAdresse') ?>*</label>
				<div class="col-sm-10">
					<input type="text" name="email" id="email" value="<?php echo $email ?>" <?php echo ($email != ''?'disabled="disabled"':''); ?> size="32" class="form-control">
				</div>
			</div>
			<div class="form-group <?php echo ($telefon==''?'has-error':'') ?>">
				<label for="telefonnummer" class="col-sm-2 control-label"><?php echo $p->t('global/telefonnummer') ?>*</label>
				<div class="col-sm-10">
					<input type="text" name="telefonnummer" id="telefonnummer" value="<?php echo $telefon ?>"  <?php echo $disabled; ?> size="32" class="form-control">
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label"></label>
				<div class="col-sm-10">
				* <?php echo $p->t('bewerbung/pflichtfelder') ?>
				</div>
			</div>
		</fieldset>

		<fieldset>
			<legend><?php echo $p->t('bewerbung/adresse') ?></legend>
			<div class="form-group <?php echo ($adr_nation==''?'has-error':'') ?>">
				<label for="nation" class="col-sm-2 control-label"><?php echo $p->t('bewerbung/nation') ?>*</label>
				<div class="col-sm-10">
					<select name="nation" id="nation" class="form-control" <?php echo $disabled; ?> >
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
			<div class="form-group <?php echo ($strasse==''?'has-error':'') ?>">
				<label for="strasse" class="col-sm-2 control-label"><?php echo $p->t('global/strasse') ?>*</label>
				<div class="col-sm-10">
					<input type="text" name="strasse" id="strasse" maxlength="256" value="<?php echo $strasse ?>" <?php echo $disabled ?> class="form-control">
				</div>
			</div>
			<div class="form-group <?php echo ($plz==''?'has-error':'') ?>">
				<label for="plz" class="col-sm-2 control-label"><?php echo $p->t('global/plz') ?>*</label>
				<div class="col-sm-10">
					<input type="text" name="plz" id="plz" maxlength="16" value="<?php echo $plz ?>" <?php echo $disabled ?> class="form-control">
				</div>
			</div>
			<div class="form-group <?php echo ($ort==''?'has-error':'') ?>">
				<label for="ort" class="col-sm-2 control-label"><?php echo $p->t('global/ort') ?>*</label>
				<div class="col-sm-10">
					<input type="text" name="ort" id="ort_input" maxlength="256" value="<?php echo $ort ?>" <?php echo $disabled.' '.($ort == ''?'disabled="disabled"':'') ?> class="form-control">
					<select id="ort_dropdown" name="ort" class="form-control" <?php echo $disabled.' '.($ort == ''?'':'') ?>></select>
					<input type="hidden" name="gemeinde" id="gemeinde_input" value="<?php echo $gemeinde ?>" class="form-control">
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label"></label>
				<div class="col-sm-10">
				* <?php echo $p->t('bewerbung/pflichtfelder') ?>
				</div>
			</div>
		</fieldset>
		<button class="btn-nav btn btn-default" type="submit" name="btn_kontakt" data-jump-tab="<?php echo $tabs[array_search('kontakt', $tabs)-1] ?>" onclick="this.form.action='<?php echo $_SERVER['PHP_SELF'] ?>?active=<?php echo $tabs[array_search('kontakt', $tabs)-1] ?>'">
			<?php echo $p->t('global/zurueck') ?>
		</button>
		<button class="btn btn-success" type="submit"  <?php echo $disabled; ?> name="btn_kontakt">
			<?php echo $p->t('global/speichern') ?>
		</button>
		<button class="btn-nav btn btn-default" type="submit" name="btn_kontakt" data-jump-tab="<?php echo $tabs[array_search('kontakt', $tabs)+1] ?>" onclick="this.form.action='<?php echo $_SERVER['PHP_SELF'] ?>?active=<?php echo $tabs[array_search('kontakt', $tabs)+1] ?>'">
			<?php echo $p->t('bewerbung/weiter') ?>
		</button><br/><br/>
	</form>
	<script type="text/javascript">
		$(function() 
		{
			$(document).ready(function()
			{
				if($('#nation').val() == "A")
				{
					$("#ort_input").hide();
					$("#ort_dropdown").show();
					var plz = $("#plz").val();
					loadOrtData(plz, $("#ort_dropdown"));
				}
				else
				{
					$("#ort_dropdown").hide();
					$("#ort_input").show();
					if ($("#ort_input").val() == '')
						$("#ort_input").prop('disabled', false);
				}
			});
			$('#nation').change(function() 
			{
				if($('#nation').val() != "")
				{
					$('#strasse').prop('disabled', false);
					$('#plz').prop('disabled', false);
					if($('#nation').val() == "A")
					{
						$("#ort_input").hide();
						$("#ort_dropdown").show();
						if($('#ort_dropdown').val() == null)
							$("#ort_dropdown").html("<option value=''><?php echo $p->t('bewerbung/bitteGueltigeOesterreichischePlzEingeben') ?></option>");
					}
					else
					{
						$('#ort_input').prop('disabled', false);
						$('#ort_dropdown').empty();
						$("#ort_dropdown").hide();
						$("#ort_input").show();
					}
				}
				else
				{
					$('#strasse').prop('disabled', true);
					$('#plz').prop('disabled', true);
				}
			});
			$('#plz').on("input", function() 
			{
				if ($('#nation').val() == "A")
				{
					var plz = $("#plz").val();
					if ($.isNumeric(plz))
						loadOrtData(plz, $("#ort_dropdown"));
					else
						$("#ort_dropdown").html("<option value=''><?php echo $p->t('bewerbung/plzMussGueltigSein') ?></option>");
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
						$('#ort_input').empty();
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
									$('#gemeinde_input').val(v.gemeindename);
								}
								/*if ($(select).attr("name") === "ort_dd") 
								{
									if (v.ortschaftskennziffer === '')
									{
										$(element).find("select").append("<option value='" + v.ortschaftskennziffer + "' selected>" + v.ortschaftsname + "</option>");
									}
									else
									{
										$(element).find("select").append("<option value='" + v.ortschaftskennziffer + "'>" + v.ortschaftsname + "</option>");
									}
								}
								else
								{
									if (v.ortschaftskennziffer === '')
									{
										$(element).find("select").append("<option value='" + v.ortschaftskennziffer + "' selected>" + v.ortschaftsname + "</option>");
									}
									else
									{
										$(element).find("select").append("<option value='" + v.ortschaftskennziffer + "'>" + v.ortschaftsname + "</option>");
									}
								}*/
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
