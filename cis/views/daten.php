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
		echo '<div class="alert alert-info">'.$p->t('bewerbung/accountVorhanden').'</div>';
	}

	/*if($save_error)
	{
		echo '<div class="bg-danger">
		    <h4>'.$p->t('global/fehlerBeimSpeichernDerDaten').'</h4>
		    <p>'.$message.'</p>
		  </div>';
	}*/
	if($save_error_daten===false)
	{
		echo '	<div class="alert alert-success" id="success-alert_daten">
				<button type="button" class="close" data-dismiss="alert">x</button>
					<strong>'.$p->t('global/erfolgreichgespeichert').'</strong>
				</div>';
	}
	elseif($save_error_daten===true)
	{
		echo '	<div class="alert alert-danger" id="danger-alert">
			<button type="button" class="close" data-dismiss="alert">x</button>
				<strong>'.$p->t('global/fehleraufgetreten').' </strong>'.$message.'
			</div>';
	}


	?>

	<form method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>?active=daten" class="form-horizontal">
		<?php
		if(!defined('BEWERBERTOOL_DATEN_TITEL_ANZEIGEN') || BEWERBERTOOL_DATEN_TITEL_ANZEIGEN):
		?>
		<div class="form-group">
			<label for="titel_pre" class="col-sm-3 control-label"><?php echo $p->t('bewerbung/akademischeTitel') ?>
				<a href="#" data-toggle="tooltip" data-placement="auto" title="" data-original-title="<?php echo $p->t('bewerbung/beschreibungTitelPre') ?>">
					<span style="font-size: 1em;" class="glyphicon glyphicon-info-sign glyph" aria-hidden="true"></span>
				</a>
			</label>
			<div class="col-sm-9">
				<input type="text" name="titel_pre" id="titel_pre" <?php echo $disabled; ?> value="<?php echo $titelpre ?>" class="form-control">
			</div>
		</div>
		<?php else: ?>
			<input type="hidden" name="titel_pre" id="titel_pre">
		<?php endif; ?>
		<div class="form-group <?php echo ($vorname==''?'has-error':'') ?>">
			<label for="vorname" class="col-sm-3 control-label"><?php echo $p->t('global/vorname') ?>*</label>
			<div class="col-sm-9">
				<input type="text" name="vorname" id="vorname"  <?php echo $disabled; ?> value="<?php echo $vorname ?>" class="form-control">
			</div>
		</div>
		<div class="form-group <?php echo ($nachname==''?'has-error':'') ?>">
			<label for="nachname" class="col-sm-3 control-label"><?php echo $p->t('global/nachname') ?>*</label>
			<div class="col-sm-9">
				<input type="text" name="nachname" id="nachname"  <?php echo $disabled; ?> value="<?php echo $nachname ?>" class="form-control">
			</div>
		</div>
		<?php
		if(!defined('BEWERBERTOOL_DATEN_TITEL_ANZEIGEN') || BEWERBERTOOL_DATEN_TITEL_ANZEIGEN):
		?>
		<div class="form-group">
			<label for="titelPost" class="col-sm-3 control-label"><?php echo $p->t('global/postnomen') ?>
				<a href="#" data-toggle="tooltip" data-placement="auto" title="" data-original-title="<?php echo $p->t('bewerbung/beschreibungTitelPost') ?>">
					<span style="font-size: 1em;" class="glyphicon glyphicon-info-sign glyph" aria-hidden="true"></span>
				</a>
			</label>
			<div class="col-sm-9">
				<input type="text" name="titelPost" id="titelPost"  <?php echo $disabled; ?> value="<?php echo $titelpost ?>" class="form-control">
			</div>
		</div>
		<?php else: ?>
			<input type="hidden" name="titelPost" id="titelPost">
		<?php endif; ?>
		<div class="form-group <?php echo ($geburtstag==''?'has-error':'') ?>">
			<label for="gebdatum" class="col-sm-3 control-label"><?php echo $p->t('global/geburtsdatum') ?>*(<?php echo $p->t('bewerbung/datumFormat') ?>)</label>
			<div class="col-sm-9">
				<input type="text" name="geburtsdatum" id="gebdatum"  <?php echo $disabled; ?> value="<?php echo $geburtstag ?>" class="form-control">
			</div>			
		</div>

		<div>
			<div class="col-sm-3">
				<div></div>
			</div>	
			<div id="danger-alert" class="col-sm-9 font-weight-bold">
				<div id="response"></div>
			</div>			
		</div>
			
		<div class="form-group <?php echo (defined('BEWERBERTOOL_GEBURTSORT_PFLICHT') && BEWERBERTOOL_GEBURTSORT_PFLICHT === true && $gebort == '' ?'has-error':'') ?>">
			<label for="gebort" class="col-sm-3 control-label"><?php echo $p->t('global/geburtsort');echo (defined('BEWERBERTOOL_GEBURTSORT_PFLICHT') && BEWERBERTOOL_GEBURTSORT_PFLICHT === true?'*':''); ?></label>
			<div class="col-sm-9">
				<input type="text" name="gebort" id="gebort"  <?php echo $disabled; ?> value="<?php echo $gebort ?>" class="form-control">
			</div>
		</div>
		<div class="form-group <?php echo (defined('BEWERBERTOOL_GEBURTSNATION_PFLICHT') && BEWERBERTOOL_GEBURTSNATION_PFLICHT === true && $person->geburtsnation == '' ?'has-error':'') ?>">
			<label for="geburtsnation" class="col-sm-3 control-label"><?php echo $p->t('bewerbung/geburtsnation');echo (defined('BEWERBERTOOL_GEBURTSNATION_PFLICHT') && BEWERBERTOOL_GEBURTSNATION_PFLICHT === true?'*':''); ?></label>
			<div class="col-sm-9">
				<select name="geburtsnation" id="geburtsnation"  <?php echo $disabled; ?> class="form-control">
					<option value=""><?php echo $p->t('bewerbung/bitteAuswaehlen') ?></option>
					<option value="A"><?php	echo ($sprache=='German'? 'Österreich':'Austria'); ?></option>
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
		<div class="form-group <?php echo ($person->staatsbuergerschaft==''?'has-error':'') ?>">
			<label for="staatsbuergerschaft" class="col-sm-3 control-label"><?php echo $p->t('global/staatsbuergerschaft') ?>*</label>
			<div class="col-sm-9">
				<select name="staatsbuergerschaft" id="staatsbuergerschaft"  <?php echo $disabled; ?> class="form-control">
					<option value=""><?php echo $p->t('bewerbung/bitteAuswaehlen') ?></option>
					<option value="A"><?php	echo ($sprache=='German'? 'Österreich':'Austria'); ?></option>
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
		<?php
		if(!defined('BEWERBERTOOL_SOZIALVERSICHERUNGSNUMMER_ANZEIGEN') || BEWERBERTOOL_SOZIALVERSICHERUNGSNUMMER_ANZEIGEN == true || is_string(BEWERBERTOOL_SOZIALVERSICHERUNGSNUMMER_ANZEIGEN)):
			$svnrDisabled = 'disabled="disabled"';
			if ($svnr == '')
			{
				$svnrDisabled = '';
			}
		?>
		<div id="input_svnr" class="form-group" <?php echo ($svnr == '' && !in_array($person->staatsbuergerschaft, explode(";", BEWERBERTOOL_SOZIALVERSICHERUNGSNUMMER_ANZEIGEN)) && is_string(BEWERBERTOOL_SOZIALVERSICHERUNGSNUMMER_ANZEIGEN)?'style="display: none;"':'') ?>>
			<label for="svnr" class="col-sm-3 control-label"><?php echo $p->t('bewerbung/svnr').' '.$p->t('bewerbung/fallsVorhanden') ?></label>
			<div class="col-sm-9">
				<input type="text" name="svnr" id="svnr"  <?php echo $svnrDisabled; ?> value="<?php echo $svnr ?>" class="form-control">
			</div>
		</div>
		<?php endif; ?>
		<div class="form-group">
			<label for="geschlecht" class="col-sm-3 control-label"><?php echo $p->t('global/geschlecht') ?></label>
			<div class="col-sm-9">
				<?php
				$geschlechter = new geschlecht();
				$geschlechter->getAll();

				foreach ($geschlechter->result AS $gsch)
				{
					if ($gsch->geschlecht == 'u')
					{
						continue;
					}
					$checked = '';
					if ($gsch->geschlecht == $person->geschlecht)
					{
						$checked = 'checked';
					}
					echo '	<label class="radio-inline">
								<input type="radio" name="geschlecht" class="radio-inline" '.$disabled.' value="'.$gsch->geschlecht.'" '.$checked.'>
								'.$gsch->bezeichnung_mehrsprachig_arr[$sprache].'
							</label>';
				}
				?>
			</div>
		</div>
		<?php 
		$prestudent = new prestudent();
		$prestudent->getPrestudenten($person->person_id);
		if(isset($prestudent->result[0])): ?>
		<div class="form-group <?php echo (defined('BEWERBERTOOL_AUFMERKSAMDURCH_PFLICHT') && BEWERBERTOOL_AUFMERKSAMDURCH_PFLICHT === true && $prestudent->result[0]->aufmerksamdurch_kurzbz == ''?'has-error':'') ?>">
			<label for="aufmerksamdurch" class="col-sm-3 control-label"><?php echo $p->t('bewerbung/aufmerksamdurch');echo (defined('BEWERBERTOOL_AUFMERKSAMDURCH_PFLICHT') && BEWERBERTOOL_AUFMERKSAMDURCH_PFLICHT === true?'*':''); ?></label>
			<div class="col-sm-9">
				<select name="aufmerksamdurch" id="aufmerksamdurch"  <?php echo $disabled; ?> class="form-control">
					<?php
					$aufmerksamdurch = new aufmerksamdurch();
					$aufmerksamdurch->getAll();
					
					//Sortiert aufmerksamdurch je nach Sprache alphabetisch nach bezeichnung_mehrsprachig
					function sortAufmerksamdurch($a, $b)
					{
						return strcmp(strtolower($a->bezeichnung[getSprache()]), strtolower($b->bezeichnung[getSprache()]));
					}
					usort($aufmerksamdurch->result, "sortAufmerksamdurch");

					$selected = '';

					if(isset($prestudent->result[0]) && $prestudent->result[0]->aufmerksamdurch_kurzbz!='')
						$aufmerksamdurch_kurzbz = $prestudent->result[0]->aufmerksamdurch_kurzbz;
					else
						$aufmerksamdurch_kurzbz ='';?>
						
					<option value=""><?php echo $p->t('bewerbung/bitteAuswaehlen');?></option>

					<?php 
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
		<div class="form-group">
			<label class="col-sm-3 control-label"></label>
			<div class="col-sm-9">
			* <?php echo $p->t('bewerbung/pflichtfelder') ?>
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
			$notiz->getBewerbungstoolNotizen($person_id, null, 'tbl_notiz.insertamum DESC');
			$counter = 0;
			if(count($notiz->result) > 0):
				foreach($notiz->result as $berufstaetig)
				{
					if($berufstaetig->insertvon == 'online')
					{
						$counter++;
						echo '	<div class="form-group">
									<label for="berufstaetig" class="col-sm-3 control-label">
										'.$p->t('bewerbung/eintragVom').' '.date('d.m.Y', strtotime($berufstaetig->insertamum)).'
									</label>
									<div class="col-sm-9">
										<input type="text" class="form-control" disabled value="'.htmlspecialchars($berufstaetig->text).'">
									</div>
								</div>';
						break;
					}
				}
				$berufstaetigkeit_code='';
				//if($counter == 0)
				{
					foreach($prestudent->result AS $row)
					{
						if($row->berufstaetigkeit_code!='')
						{
							$berufstaetigkeit_code = $row->berufstaetigkeit_code;
							$counter++;
						}
					}
					if(CAMPUS_NAME != 'FH Technikum Wien' && $berufstaetigkeit_code!='')
					{
						$berufstaetigkeit = new bisberufstaetigkeit();
						$berufstaetigkeit->load($berufstaetigkeit_code);

						echo '<div class="form-group">
								<label for="berufstaetig" class="col-sm-3 control-label">
									'.$p->t('bewerbung/berufstaetigkeit').'
								</label>
								<div class="col-sm-9">
									<input type="text" class="form-control" disabled value="'.($berufstaetigkeit->berufstaetigkeit_bez).'">
								</div>
							</div>';
					}
				}
			endif;	?>

				<div class="form-group">
					<label for="berufstaetig" class="col-sm-3 control-label"><?php echo $p->t('bewerbung/berufstaetig') ?></label>
					<div class="col-sm-9">
						<label class="radio-inline"><input type="radio" class="inputBerufstaetig" name="berufstaetig" value="Vollzeit"><?php echo $p->t('bewerbung/vollzeit') ?></label>
						<label class="radio-inline"><input type="radio" class="inputBerufstaetig" name="berufstaetig" value="Teilzeit"><?php echo $p->t('bewerbung/teilzeit') ?></label>
						<label class="radio-inline"><input type="radio" class="inputBerufstaetig" name="berufstaetig" value="Nein"><?php echo $p->t('global/nein') ?></label>
						<label class="radio-inline"></label>

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
				<div class="form-group">
					<label class="col-sm-3 control-label"></label>
					<div class="col-sm-9">
						** <?php echo $p->t('bewerbung/anmerkungBerufstaetigkeit') ?>
					</div>
				</div>

		</fieldset>
		<?php
		endif;
		?>
		<button class="btn-nav btn btn-default" type="submit" name="btn_person" data-jump-tab="<?php echo $tabs[array_search('daten', $tabs)-1] ?>" onclick="this.form.action='<?php echo $_SERVER['PHP_SELF'] ?>?active=<?php echo $tabs[array_search('daten', $tabs)-1] ?>'">
			<?php echo $p->t('global/zurueck') ?>
		</button>
		<button class="btn btn-success" type="submit"  <?php /*echo ($svnrDisabled == '' ? '' : $disabled);*/ ?> name="btn_person">
			<?php echo $p->t('global/speichern') ?>
		</button>
		<button class="btn-nav btn btn-default" type="submit" name="btn_person" data-jump-tab="<?php echo $tabs[array_search('daten', $tabs)+1] ?>" onclick="this.form.action='<?php echo $_SERVER['PHP_SELF'] ?>?active=<?php echo $tabs[array_search('daten', $tabs)+1] ?>'">
			<?php echo $p->t('bewerbung/weiter') ?>
		</button><br/><br/>
	</form>
	<script type="text/javascript">

		$(function()
		{
			<?php
			if(defined('BEWERBERTOOL_SOZIALVERSICHERUNGSNUMMER_ANZEIGEN') && is_string(BEWERBERTOOL_SOZIALVERSICHERUNGSNUMMER_ANZEIGEN)):
			?>
			$('#staatsbuergerschaft').change(function() {
				var arrayFromPHP = <?php echo json_encode(explode(";", BEWERBERTOOL_SOZIALVERSICHERUNGSNUMMER_ANZEIGEN)) ?>;
				if(jQuery.inArray($('#staatsbuergerschaft').val(), arrayFromPHP) > -1 ) {
					$('#input_svnr').show();
				}
				else {
					$('#input_svnr').hide();
				}
			});
			<?php endif; ?>

			$('.inputBerufstaetig').change(function()
			{
				if($(this).val() == 'Nein')
				{
					$('#berufstaetig_dienstgeber').attr("disabled", true);
					$('#berufstaetig_art').attr("disabled", true);
				}
				else
				{
					$('#berufstaetig_dienstgeber').attr("disabled", false);
					$('#berufstaetig_art').attr("disabled", false);
				}
			});
	
		});

	//Test Manu
	var validateDate = document.getElementById('gebdatum');
	var response = document.getElementById('response');

	/**
	 * Prueft, ob es sich um ein gültiges Datum handelt
	 * @return true wenn gültig, false wenn nicht gültig (zum Beispiel 30.2.2020)
	 */
	function checkValidDate(datum)
	{

		// mit bootstrap-format
		if (datum.toString() == 'Invalid Date')
		{
			response.innerHTML="<?php echo $p->t('bewerbung/datumUngueltig');?>";
			$('#danger-alert').addClass('alert');
			$('#danger-alert').addClass('alert-danger');

		}
		else
		{
			response.innerHTML='';
			$('#danger-alert').removeClass('alert');
			$('#danger-alert').removeClass('alert-danger');
		}
	}

	validateDate.onchange = function() 
	{
		var testDate = validateDate.value;
		var regex1 = new RegExp("([0-9]{2}).([0-9]{2}).([0-9]{4})$");
		var regex2 = new RegExp("([0-9]{4})-([0-9]{2})-([0-9]{2})$");


		if (regex1.test(testDate))
		{
			var day = testDate.substr(0,2);
			var month = testDate.substr(3,2);
			var year = testDate.substr(6,4);
			//console.log("DATE Test: " + year + "-" + month + "-" + day);
			var d = new Date (year + '-' + month + '-'+ day);

			//console.log(d);
			
			checkValidDate(d);		


		}

		else if (regex2.test(testDate))
		{
			var d = new Date (testDate);
			checkValidDate(d);

		}
		else
		{
			response.innerHTML="<?php echo $p->t('bewerbung/datumsformatUngueltig');?>";
			$('#danger-alert').addClass('alert');
			$('#danger-alert').addClass('alert-danger');
		}
	}

	</script>
</div>
