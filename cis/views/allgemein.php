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

<div role="tabpanel" class="tab-pane" id="allgemein">
	<h2><?php echo $p->t('bewerbung/menuAllgemein'); ?></h2>
	<p><?php echo $p->t('bewerbung/allgemeineErklaerung'); ?></p>
	<br><br>
	<p><b><?php echo $p->t('bewerbung/aktuelleBewerbungen'); ?></b></p>
	<?php

        // Zeige Stati der aktuellen Bewerbungen an
		$prestudent = new prestudent();
		if(!$prestudent->getPrestudenten($person_id))
		{
			die('Konnte Prestudenten nicht laden');
		} ?>

		<div class="table-responsive">
			<table class="table">
				<tr>
					<th><?php echo $p->t('global/studiengang'); ?></th>
					<th><?php echo $p->t('bewerbung/status'); ?></th>
					<th><?php echo $p->t('global/datum'); ?></th>
					<th><?php echo $p->t('global/aktion'); ?></th>
					<th><?php echo $p->t('bewerbung/bewerbungsstatus'); ?></th>
				</tr>
				<?php
				$bereits_angemeldet = array();

				foreach($prestudent->result as $row):
					$stg = new studiengang();
					if(!$stg->load($row->studiengang_kz))
						die('Konnte Studiengang nicht laden');

					$bereits_angemeldet[] = $stg->studiengang_kz;

					$prestudent_status = new prestudent();
					$prestatus_help= ($prestudent_status->getLastStatus($row->prestudent_id))?$prestudent_status->status_kurzbz:$p->t('bewerbung/keinStatus');
					$bewerberstatus =($prestudent_status->bestaetigtam != '' || $prestudent_status->bestaetigtvon != '')?$p->t('bewerbung/bestaetigt'):$p->t('bewerbung/nichtBestaetigt'); ?>
					<tr>
						<td><?php echo $stg->bezeichnung ?></td>
						<td><?php echo $prestatus_help ?></td>
						<td><?php echo $datum->formatDatum($prestudent_status->datum, 'd.m.Y') ?></td>
						<td></td>
						<td><?php echo $bewerberstatus ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	<br>
	<button class="btn-nav btn btn-default" type="button" data-toggle="modal" data-target="#liste-studiengaenge">
		<?php echo $p->t('bewerbung/studiengangHinzufuegen'); ?>
	</button>
	<button class="btn-nav btn btn-default" type="button" data-jump-tab="daten">
		<?php echo $p->t('bewerbung/weiter'); ?>
	</button>

	<div class="modal fade" id="liste-studiengaenge"><div class="modal-dialog"><div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close cancel-studiengang" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
			<h4 class="modal-title"><?php echo $p->t('bewerbung/neuerStudiengang') ?></h4>
		</div>
		<div class="modal-body">
			<div class="form-group">
				<label for="studiensemester_kurzbz" class="control-label">
					<?php echo $p->t('bewerbung/geplanterStudienbeginn') ?>
				</label>
				<div class="dropdown">
					<select id="studiensemester_kurzbz" name="studiensemester_kurzbz" class="form-control">
						<option value=""><?php echo $p->t('bewerbung/bitteWaehlen') ?></option>
						<?php
						$stsem = new studiensemester();
						$stsem->getFutureStudiensemester('',4);

						foreach($stsem->studiensemester as $row)
						{
							echo '<option value="'.$row->studiensemester_kurzbz.'">'.$stsem->convert_html_chars($row->bezeichnung).'</option>';
						}
						?>
					</select>
				</div>
			</div>
			<div class="form-group">
				<label for="studiensemester_kurzbz" class="control-label">
					<?php echo $p->t('bewerbung/geplanteStudienrichtung') ?>
				</label>
			<?php
			$stg = new studiengang();
			$stg->getAll('typ,bezeichnung',true);

			foreach($stg->result as $result)
			{
				if(!$result->onlinebewerbung)
					continue;
				if($result->studiengang_kz > 0)
				{
					$typ = new studiengang();
					$typ->getStudiengangTyp($result->typ);
					if(in_array($result->studiengang_kz, $bereits_angemeldet))
					{
						continue;
					} 

					$orgform = $stg->getOrgForm($result->studiengang_kz);
					$sprache = $stg->getSprache($result->studiengang_kz);

					$modal = false;

					if(count($orgform) !== 1 || count($sprache) !== 1)
					{
						$modal = true;
					}

					echo '
					<div class="radio">
						<label>
							<input type="radio" name="studiengaenge[]" value="'.$result->studiengang_kz.'"
								data-modal="'.$modal.'"
								data-modal-sprache="'.implode(',', $sprache).'"
								data-modal-orgform="'.implode(',', $orgform).'">
							'.$result->bezeichnung.'
							<input type="hidden" id="anmerkung'.$result->studiengang_kz.'">
						</label>
					</div>
					';
				}
			}			
			?>
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

				var	modal = item.attr('data-modal'),
					modal_orgform = item.attr('data-modal-orgform').split(','),
					modal_sprache = item.attr('data-modal-sprache').split(',');
				$('#prio-dialog').data({stgkz: stgkz});

				if(modal) 
				{
					$('#prio-dialog input[value="egal"]').prop('checked', true);
					prioAvailable(modal_orgform, modal_sprache);
					checkPrios(0);
					$('#prio-dialog').data({stgkz: stgkz, stsem: stsem});
					$('#prio-dialog').modal('show');
					$('#liste-studiengaenge').modal('hide');
				}
				else
				{
					saveStudiengang(stgkz, '', stsem);
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
					stsem = $('#prio-dialog').data('stsem');

				anm = checkPrios(0);

				saveStudiengang(stgkz, anm, stsem);
			});

		});
		function saveStudiengang(stgkz, anm, stsem)
		{
			data = {
				anm: anm,
				stgkz: stgkz,
				addStudiengang: true,
				studiensemester: stsem
			};

			$.ajax({
				url: basename,
				data: data,
				type: 'POST',
				dataType: "json",
				success: function(data) 
				{
					if(data.status!='ok')
						alert('Fehler'+data.msg);
					else
						window.location.reload();
				},
				error: function(data) 
				{
					alert('Fehler beim Speichern der Daten')
				}
			});
			
		}
	</script>
</div>
