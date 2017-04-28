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
 * 			Manfred Kindl <manfred.kindl@technikum-wien.at>
 */

require_once('../../../config/global.config.inc.php');
require_once('../bewerbung.config.inc.php');

if(!isset($person_id))
{
	die($p->t('bewerbung/ungueltigerZugriff'));
}
$studiensemester_array = array();
?>

<div role="tabpanel" class="tab-pane" id="allgemein">
	<h2><?php echo $p->t('bewerbung/menuAllgemein'); ?></h2>
	<?php
	if($_SESSION['bewerbung/user']=='Login')
		echo '<p>'.$p->t('bewerbung/erklaerungStudierende').'</p>';
	else
		echo '<p>'.$p->t('bewerbung/allgemeineErklaerung').'</p>';
	?>

	<br><br>
	<p><b><?php echo $p->t('bewerbung/aktuelleBewerbungen'); ?></b></p>
	<?php

		// Zeige Stati der aktuellen Bewerbungen an
		$prestudent = new prestudent();
		if(!$prestudent->getPrestudenten($person_id))
		{
			die($p->t('global/fehlerBeimLadenDesDatensatzes'));
		} ?>

		<div class="">
			<table class="table">
				<tr>
					<th><?php echo $p->t('global/studiengang'); ?></th>
					<th><?php echo $p->t('bewerbung/kontakt'); ?></th>
					<th><?php echo $p->t('bewerbung/status'); ?></th>
					<th><?php echo $p->t('global/datum'); ?></th>
					<th><?php echo $p->t('bewerbung/bewerbungsstatus'); ?></th>
				</tr>
				<?php
				$bereits_angemeldet = array();
				$anzahl_studiengaenge = array();
				$stsem_bewerbung = array();
				$studiengaengeBaMa = array(); // Nur Bachelor oder Master Studiengaenge
				
				$stsem = new studiensemester();
				$stsem->getStudiensemesterOnlinebewerbung();
				foreach ($stsem->studiensemester as $row)
					$stsem_bewerbung[] = $row->studiensemester_kurzbz;

				foreach($prestudent->result as $row):
					$stg = new studiengang();
					if(!$stg->load($row->studiengang_kz))
						die($p->t('global/fehlerBeimLadenDesDatensatzes'));

					$prestudent_status = new prestudent();
					$prestatus_help = ($prestudent_status->getLastStatus($row->prestudent_id))?$prestudent_status->status_mehrsprachig[$sprache]:$p->t('bewerbung/keinStatus');
					$bewerberstatus = ($prestudent_status->bestaetigtam != '' || $prestudent_status->bestaetigtvon != '')?$p->t('bewerbung/bestaetigt'):$p->t('bewerbung/nichtBestaetigt');

					//$bereits_angemeldet[]= $stg->studiengang_kz;
					$bereits_angemeldet[$prestudent_status->studiensemester_kurzbz][]= $stg->studiengang_kz;
					//$bereits_angemeldet[]= $stg->studiengang_kz.$prestudent_status->studiensemester_kurzbz;

					//Zaehlt die Anzahl an Bewerbungen in einem Studiensemester
					if (in_array($prestudent_status->studiensemester_kurzbz, $stsem_bewerbung))
					{
						if (!array_key_exists($prestudent_status->studiensemester_kurzbz, $anzahl_studiengaenge))
							$anzahl_studiengaenge[$prestudent_status->studiensemester_kurzbz] = 0;
						
						$anzahl_studiengaenge[$prestudent_status->studiensemester_kurzbz] ++;
						
						if ($row->studiengang_kz > 0 && $row->studiengang_kz < 10000)
							$studiengaengeBaMa[] = $row->studiengang_kz;
					}

					if($sprache!='German' && $stg->english!='')
						$stg_bezeichnung = $stg->english;
					else
						$stg_bezeichnung = $stg->bezeichnung;

					$typ = new studiengang();
					$typ->getStudiengangTyp($stg->typ);

					$empf_array = array();
					if(defined('BEWERBERTOOL_BEWERBUNG_EMPFAENGER'))
						$empf_array = unserialize(BEWERBERTOOL_BEWERBUNG_EMPFAENGER);

					if(defined('BEWERBERTOOL_MAILEMPFANG') && BEWERBERTOOL_MAILEMPFANG!='')
						$empfaenger = BEWERBERTOOL_MAILEMPFANG;
					elseif(isset($empf_array[$stg->studiengang_kz]))
						$empfaenger = $empf_array[$stg->studiengang_kz];
					else
						$empfaenger = $stg->email;

					$orgform = new organisationsform();
					$orgform->load($prestudent_status->orgform_kurzbz);
					?>

					<tr>
						<td><?php echo $typ->bezeichnung.' '.$stg_bezeichnung.($orgform->bezeichnung!=''?' ('.$orgform->bezeichnung.')':'') ?></td>
						<td><a href="mailto:<?php echo $empfaenger ?>"><span class="glyphicon glyphicon-envelope"></span></a></td>
						<td><?php echo $prestatus_help.' ('.$prestudent_status->studiensemester_kurzbz.')' ?></td>
						<td><?php echo $datum->formatDatum($prestudent_status->datum, 'd.m.Y') ?></td>
						<td><?php echo $bewerberstatus ?></td>
					</tr>
				<?php endforeach;?>
			</table>
		</div>
	<br>
	<button class="btn-nav btn btn-success" type="button" data-toggle="modal" data-target="#liste-studiengaenge">
		<?php echo $p->t('bewerbung/studiengangHinzufuegen'); ?>
	</button>
	<button class="btn-nav btn btn-default" type="button" data-jump-tab="<?php echo $tabs[array_search('allgemein', $tabs)+1] ?>">
		<?php echo $p->t('bewerbung/weiter'); ?>
	</button>
	<br/><br/>

	<div class="modal fade" id="liste-studiengaenge"><div class="modal-dialog"><div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close cancel-studiengang" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
			<h4 class="modal-title"><?php echo $p->t('bewerbung/neuerStudiengang') ?></h4>
		</div>
		<div class="modal-body">
			<div class="form-group">
				<label for="ausbildungstyp" class="control-label">
					<?php echo $p->t('bewerbung/ausbildungstyp') ?>
				</label>
				<div class="dropdown">
					<select id="ausbildungstyp" name="ausbildungstyp" class="form-control">
						<option value="stg"><?php echo $p->t('global/studiengang') ?></option>
						<option value="lehrg"><?php echo $p->t('bewerbung/lehrgang') ?></option>
					</select>
				</div>
				<label for="studiensemester_kurzbz" class="control-label">
					<?php echo $p->t('bewerbung/geplanterStudienbeginn') ?>
				</label>
				<div class="dropdown">
					<select id="studiensemester_kurzbz" name="studiensemester_kurzbz" class="form-control">
					<option value=""><?php echo $p->t('bewerbung/bitteAuswaehlen') ?></option>
						<?php
						foreach($stsem->studiensemester as $row)
						{
							echo '<option value="'.$row->studiensemester_kurzbz.'">'.$stsem->convert_html_chars($row->bezeichnung).' ('.$p->t('bewerbung/ab').' '.$datum->formatDatum($stsem->convert_html_chars($row->start),'d.m.Y').')</option>';
							
							$studiensemester_array[] = $row->studiensemester_kurzbz;
							
						}
						?>
					</select>
				</div>
			</div>
			<div id="form-group-stg" class="form-group">
				<?php 
				$orgeinheit = new organisationseinheit();
				$standorte = $orgeinheit->getAllStandorte();
				$optionsStg = null;
				$optionsLehrg = null;
				$options = array();
				$stg = new studiengang();
				$stg->getAllForBewerbung('typ, tbl_lgartcode.bezeichnung ASC, studiengangbezeichnung');

				$stghlp = new studiengang();
				$stghlp->getLehrgangstyp();
				$lgtyparr=array();
				foreach($stghlp->result as $row)
					$lgtyparr[$row->lgartcode]=$row->bezeichnung;

				$last_lgtyp = '';
				foreach($stg->result as $result)
				{
					$typ = new studiengang();
					$typ->getStudiengangTyp($result->typ);
					if($sprache!='German' && $result->studiengangbezeichnung_englisch!='')
						$stg_bezeichnung = $typ->bezeichnung.' '.$result->studiengangbezeichnung_englisch;
					else
						$stg_bezeichnung =  $typ->bezeichnung.' '.$result->studiengangbezeichnung;

					$typ = new studiengang();
					$typ->getStudiengangTyp($result->typ);
					
					$orgform_stg = $stg->getOrgForm($result->studiengang_kz);
					$sprache_lv = $stg->getSprache($result->studiengang_kz);

					/*if(in_array($result->studiengang_kz, $bereits_angemeldet['WS2016']))
					{
						continue;
					} */

					$orgform = $stg->getOrgForm($result->studiengang_kz);
					$stgSprache = $stg->getSprache($result->studiengang_kz);
					$studienplan = getStudienplaeneForOnlinebewerbung($result->studiengang_kz, $studiensemester_array, '1', ''); //@todo: studiensemester und ausbildungssemester dynamisch

					$studiensemester = new studiensemester();
					$studiensemester->getPlusMinus(10,1);

					$studiensemester_kurzbz=array();
					foreach($studiensemester->studiensemester AS $row)
						$studiensemester_kurzbz[] .= $row->studiensemester_kurzbz;

					$orgform_sprache = getOrgformSpracheForOnlinebewerbung($result->studiengang_kz,$studiensemester_kurzbz,'1');

					$orgformen_sprachen = array();
					if($studienplan!='')
					{
						foreach ($studienplan as $row)
						{
							if (CAMPUS_NAME=='FH Technikum Wien' && $result->studiengang_kz==334 && $result->studiengangbezeichnung == 'Intelligent Transport Systems') //@todo: Pfuschloesung bis zum neuen Tool, damit MIT nicht mehr angezeigt wird
								continue;
							else
								$orgformen_sprachen[] = $row->orgform_kurzbz.'_'.$row->sprache;
						}
					}
					$orgformen_sprachen = array_unique($orgformen_sprachen);
					$modal = false;

					// Wenn mehrere Orgformen oder Sprachen vorhanden sind, wird ein Auswahl-Modal angezeigt
					if(count($orgform) !== 1 || count($stgSprache) !== 1)
					{
						$modal = true;
						if ($result->typ!='l' && !isset($lgtyparr[$result->lgartcode]) && $result->studiengang_kz!=334 && $result->studiengangbezeichnung != 'Intelligent Transport Systems')
							$stg_bezeichnung .= ' | <i>'.$p->t('bewerbung/auswahlmöglichkeitenImNaechstenSchritt').'</i>';
					}
					elseif ($result->typ!='l' && !isset($lgtyparr[$result->lgartcode]))
						$stg_bezeichnung .= ' | <i>'.$p->t('bewerbung/orgform/'.$orgform_stg[0]).' - '.$p->t('bewerbung/'.$sprache_lv[0]).'</i>';

					if (CAMPUS_NAME=='FH Technikum Wien' && $result->studiengang_kz==334 && $result->studiengangbezeichnung != 'Intelligent Transport Systems') //@todo: Pfuschloesung bis zum neuen Tool, damit MIT nicht mehr angezeigt wird
						$stg_bezeichnung .= ' | <i>'.$p->t('bewerbung/orgform/'.$orgform_stg[0]).' - '.$p->t('bewerbung/'.$sprache_lv[0]).'</i>';
					
					if (CAMPUS_NAME=='FH Technikum Wien' && $result->studiengang_kz==334) //@todo: Pfuschloesung bis zum neuen Tool, damit kein Modal bei MSC angezeigt wird
						$modal = false;

					$radioBtn = '';
					if($result->typ=='l' && $last_lgtyp != $result->bezeichnung && $result->bezeichnung != '')
					{
						$radioBtn .= '<p style="padding-top: 20px;"><b>'.$result->bezeichnung.'</b></p>';
						$last_lgtyp = $result->bezeichnung;
					}
					if (CAMPUS_NAME=='FH Technikum Wien' && $result->studiengang_kz==334 && $result->studiengangbezeichnung == 'Intelligent Transport Systems') //@todo: Pfuschloesung bis zum neuen Tool, damit MIT nicht mehr angezeigt wird
						continue;
					else
					{
						if (defined('BEWERBERTOOL_MAX_STUDIENGAENGE') 
								&& BEWERBERTOOL_MAX_STUDIENGAENGE != '' 
								&& count($studiengaengeBaMa) >= BEWERBERTOOL_MAX_STUDIENGAENGE
								&& $result->studiengang_kz > 0
								&& $result->studiengang_kz < 10000)
							$disabled = 'disabled="diabled"';
						else
							$disabled = '';
							
						$radioBtn .= '
						<div class="radio">
							<label>
								<input '.$disabled.' type="radio" name="studiengaenge[]" value="'.$result->studiengang_kz.'"
									data-modal="'.$modal.'"
									data-modal-sprache="'.implode(',', $stgSprache).'"
									data-modal-orgform="'.implode(',', $orgform).'"
									data-modal-orgformsprache="'.implode(',', $orgformen_sprachen).'">
								'.$stg_bezeichnung;
						/*if($result->typ=='l' && isset($lgtyparr[$result->lgartcode]))
						 {
						 $radioBtn .= ' ('.$lgtyparr[$result->lgartcode].')';
						 }*/
						$radioBtn .= '<input type="hidden" id="anmerkung'.$result->studiengang_kz.'">
							</label>
						</div>
						';
					}

					if($result->organisationseinheittyp_kurzbz == "Studiengang")
					{
						if(BEWERBERTOOL_STANDORTAUSWAHL_ANZEIGEN)
						{
							if(isset($options["Stg"][$result->standort]))
								$options["Stg"][$result->standort] .= $radioBtn;
							else
								$options["Stg"][$result->standort] = $radioBtn;
						}
						else
						{
							$optionsStg .= $radioBtn;
						}
					}
					else if($result->organisationseinheittyp_kurzbz == "Lehrgang")
					{
						if(BEWERBERTOOL_STANDORTAUSWAHL_ANZEIGEN)
						{
							if(isset($options["Lehrg"][$result->standort]))
								$options["Lehrg"][$result->standort] .= $radioBtn;
							else
								$options["Lehrg"][$result->standort] = $radioBtn;
						}
						else
						{
							$optionsLehrg .= $radioBtn;
						}
					}
				}
				?>
				<label class="control-label">
					<?php echo $p->t('bewerbung/geplanteStudienrichtung') ?>
				</label>

				<?php if(BEWERBERTOOL_STANDORTAUSWAHL_ANZEIGEN): ?>
				<div id="auswahlStg">
					<div class="panel-group" id="accordionStg" role="tablist" aria-multiselectable="true">
						<div class="panel panel-default">

							<?php foreach($standorte as $standort): ?>
							<div class="panel-heading" role="tab" id="heading<?php echo $standort; ?>">
								<h4 class="panel-title">
								  <a class="collapsed" data-toggle="collapse" data-parent="#accordionStg" href="#collapse<?php echo $standort; ?>" aria-expanded="false" aria-controls="collapse<?php echo $standort; ?>">
									<?php echo $standort; ?>
								  </a>
								</h4>
							</div>
							<div id="collapse<?php echo $standort; ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading<?php echo $standort; ?>">
								<div class="panel-body">
									<?php
									if(isset($options["Stg"][$standort]))
										echo $options["Stg"][$standort];
									else
										echo $p->t('bewerbung/keineStgAngeboten');
									?>
								</div>
							</div>
							<?php endforeach; ?>

						</div>
					</div>
				</div>
				<div id="auswahlLehrg" style="display: none;">
					<div class="panel-group" id="accordionLehrg" role="tablist" aria-multiselectable="true">
						<div class="panel panel-default">

							<?php foreach($standorte as $standort): ?>
							<div class="panel-heading" role="tab" id="heading<?php echo $standort; ?>Lehrg">
								<h4 class="panel-title">
								  <a class="collapsed" data-toggle="collapse" data-parent="#accordionLehrg" href="#collapse<?php echo $standort; ?>Lehrg" aria-expanded="false" aria-controls="collapse<?php echo $standort; ?>Lehrg">
									<?php echo $standort; ?>
								  </a>
								</h4>
							</div>
							<div id="collapse<?php echo $standort; ?>Lehrg" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading<?php echo $standort; ?>Lehrg">
								<div class="panel-body">
									<?php
									if(isset($options["Lehrg"][$standort]))
										echo $options["Lehrg"][$standort];
									else
										echo $p->t('bewerbung/keineLehrgAngeboten');
									?>
								</div>
							</div>
							<?php endforeach; ?>

						</div>
					</div>
				</div>

				<?php else: ?>

				<div id="auswahlStg">
				<?php if (defined('BEWERBERTOOL_MAX_STUDIENGAENGE') && BEWERBERTOOL_MAX_STUDIENGAENGE != '' && count($studiengaengeBaMa) >= BEWERBERTOOL_MAX_STUDIENGAENGE)
						echo '<p class="alert alert-warning">'.strip_tags($p->t('bewerbung/sieKoennenMaximalXStudiengaengeWaehlen', array(BEWERBERTOOL_MAX_STUDIENGAENGE))).'</p>';

					if(!empty($optionsStg))
						echo $optionsStg;
					else
						echo $p->t('bewerbung/keineStgAngeboten');
					?>
				</div>
				<div id="auswahlLehrg" style="display: none;">
					<?php
					if(!empty($optionsLehrg))
						echo $optionsLehrg;
					else
						echo $p->t('bewerbung/keineLehrgAngeboten');
					?>
				</div>
				<?php endif; ?>

			</div>
		</div>
		<div class="modal-footer">
			<button class="btn btn-default cancel-studiengang" data-dismiss="modal"><?php echo $p->t('global/abbrechen') ?></button>
			<button class="btn btn-primary ok-studiengang" data-dismiss="modal"><?php echo $p->t('global/ok') ?></button>
		</div>
	</div></div></div>
	<?php require 'modal_sprache_orgform.php'; ?>
	<script type="text/javascript">
		$(function() {

			$('#liste-studiengaenge button.ok-studiengang').on('click', function()
			{
				var item = $('#liste-studiengaenge input:checked');
				var stgkz = item.val();
				var stsem = $('#studiensemester_kurzbz').val();
				var orgform = item.attr('data-modal-orgform');

				if (undefined == stgkz || stgkz == '')
				{
					alert('<?php echo $p->t('bewerbung/bitteEineStudienrichtungWaehlen')?>');
					return false;
				}
				if (stsem == '')
				{
					alert('<?php echo $p->t('bewerbung/bitteStudienbeginnWaehlen')?>');
					return false;
				}

				var	modal = item.attr('data-modal'),
					modal_orgform = item.attr('data-modal-orgform').split(','),
					modal_sprache = item.attr('data-modal-sprache').split(','),
					modal_orgformsprache = item.attr('data-modal-orgformsprache').split(',');
				$('#prio-dialog').data({stgkz: stgkz});

				if(modal)
				{
					//$('#prio-dialog input[value="keine"]').prop('checked', true); Aktivieren, wenn keine als default ausgewaehlt sein soll
					prioAvailable(modal_orgformsprache);
					checkPrios(0);
					$('#prio-dialog').data({stgkz: stgkz, stsem: stsem});
					$('#prio-dialog').modal('show');
					$('#liste-studiengaenge').modal('hide');
				}
				else
				{
					saveStudiengang(stgkz, '', stsem, orgform);
					$('#liste-studiengaenge').modal('hide');
				}
			});

			$('#prio-dialog input').on('change', function() {

					var stgkz = $('#prio-dialog').data('stgkz'),
						anm;

					anm = checkPrios(200);

					$('#anmerkung' + stgkz).val(anm);
					$('#badge' + stgkz).html(anm);
			});

			$('#prio-dialog button.ok-prio').on('click', function() {

				var stgkz = $('#prio-dialog').data('stgkz'),
					anm,
					stsem = $('#prio-dialog').data('stsem'),
					orgform;

				anm = checkPrios(0);
				orgform = getPrioOrgform();

				if(orgform == '')
				{
					$('#liste-studiengaenge input[value="' + stgkz + '"]').prop('checked', false);
					$('#badge' + stgkz).html('');
					alert('<?php echo $p->t('bewerbung/orgformMussGewaehltWerden') ?>');
					return false;
				}
				else
				{
					$('#orgform' + stgkz).val(orgform);
					$('#anmerkung' + stgkz).val(anm);
					$('#badge' + stgkz).html(anm);
				}

				saveStudiengang(stgkz, anm, stsem, orgform);
			});

			$('#ausbildungstyp').change(function() {
				if($('#ausbildungstyp').val() == "stg") {
					$('#auswahlLehrg').hide();
					$('#auswahlStg').show();
				}
				else {
					$('#auswahlLehrg').show();
					$('#auswahlStg').hide();
				}
			});

			/*$('#studiensemester_kurzbz').change(function() {
				$("#form-group-stg").load(document.URL +  ' #form-group-stg');
			});*/

		});
		function saveStudiengang(stgkz, anm, stsem, orgform)
		{
			data = {
				anm: anm,
				stgkz: stgkz,
				addStudiengang: true,
				studiensemester: stsem,
				orgform: orgform
			};

			$.ajax({
				url: basename,
				data: data,
				type: 'POST',
				dataType: "json",
				success: function(data)
				{
					if(data.status!='ok')
						alert(data.msg);
					else
						window.location.href='bewerbung.php?active=allgemein';
				},
				error: function(data)
				{
					alert(data.msg)
				}
			});

		}
	</script>
</div>
