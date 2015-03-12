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
	<h2>Allgemein</h2>
	<p>Wir freuen uns dass Sie sich für einen oder mehrere unserer Studiengänge bewerben. <br><br>
	Bitte füllen Sie das Formular vollständig aus und schicken Sie es danach ab.<br><br>
	<b>Bewerbungsmodus:</b><br>
	<p style="text-align:justify;">Füllen Sie alle Punkte aus. Sind alle Werte vollständig eingetragen, können Sie unter "Bewerbung abschicken" Ihre Bewerbung and die zuständige Assistenz schicken.<br>
	Diese wird sich in den nächsten Tagen bei Ihnen melden.</p>
	<br><br>
	<p><b>Aktuelle Bewerbungen: </b></p>
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
					<th>Studiengang</th>
					<th>Status</th>
					<th>Datum</th>
					<th>Aktion</th>
					<th>Bewerbungsstatus</th>
				</tr>
				<?php
				$bereits_angemeldet = array();

				foreach($prestudent->result as $row):
					$stg = new studiengang();
					if(!$stg->load($row->studiengang_kz))
						die('Konnte Studiengang nicht laden');

					$bereits_angemeldet[] = $stg->studiengang_kz;

					$prestudent_status = new prestudent();
					$prestatus_help= ($prestudent_status->getLastStatus($row->prestudent_id))?$prestudent_status->status_kurzbz:'Noch kein Status vorhanden';
					$bewerberstatus =($prestudent_status->bestaetigtam != '' || $prestudent_status->bestaetigtvon != '')?'bestätigt':'noch nicht bestätigt'; ?>
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
<!--	<button class="btn-nav btn btn-danger" type="button" data-toggle="modal" data-target="#liste-studiengaenge">
		Studiengang hinzufügen
	</button>-->
	<button class="btn-nav btn btn-default" type="button" data-jump-tab="daten">
		Weiter
	</button>

	<div class="modal fade" id="liste-studiengaenge"><div class="modal-dialog"><div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close cancel-studiengang" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
			<h4 class="modal-title"><?php echo $p->t('bewerbung/neuerStudiengang') ?></h4>
		</div>
		<div class="modal-body">
			<?php
			$stg = new studiengang();
			$stg->getAll('typ,bezeichnung',true);

			foreach($stg->result as $result):
				if($result->studiengang_kz > 0):
					$typ = new studiengang();
					$typ->getStudiengangTyp($result->typ);
					if(in_array($result->studiengang_kz, $bereits_angemeldet))
					{
						continue;
					} ?>
					<div class="radio">
						<label>
							<input type="radio" name="studiengaenge[]" value="<?php echo $result->studiengang_kz ?>">
							<?php echo $result->bezeichnung ?>
							<input type="hidden" id="anmerkung<?php echo $result->studiengang_kz ?>">
						</label>
					</div>
				<?php endif;
			endforeach; ?>
		</div>
		<div class="modal-footer">
			<button class="btn btn-default cancel-studiengang" data-dismiss="modal"><?php echo $p->t('bewerbung/abbrechen') ?></button>
			<button class="btn btn-primary ok-studiengang" data-dismiss="modal"><?php echo $p->t('bewerbung/ok') ?></button>
		</div>
	</div></div></div>
	<?php require 'modal_sprache_orgform.php'; ?>
	<script type="text/javascript">
		$(function() {

			$('#liste-studiengaenge button.ok-studiengang').on('click', function() {

				var stgkz = $('#liste-studiengaenge input:checked').val();

				$('#prio-dialog input[value="egal"]').prop('checked', true);
				checkPrios(0);
				$('#prio-dialog').data({stgkz: stgkz});
				$('#liste-studiengaenge').modal('hide');
				$('#prio-dialog').modal('show');

			});

			$('#prio-dialog button.ok-prio').on('click', function() {

				var stgkz = $('#prio-dialog').data('stgkz'),
					anm,
					data;

				anm = checkPrios(0);

				data = {
					anm: anm,
					stgkz: stgkz,
					ajax: true
				};

				$.ajax({
					url: basename,
					data: data,
					type: 'POST',
					success: function(data) {
						
					}
				});

			});

		});
	</script>
</div>
