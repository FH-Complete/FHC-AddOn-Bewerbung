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
?>

<div class="modal fade" id="prio-dialog"><div class="modal-dialog"><div class="modal-content">
	<div class="modal-header">
		<button type="button" class="close cancel-prio" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
		<h4 class="modal-title"><?php echo $p->t('bewerbung/priowaehlen') ?></h4>
	</div>
	<div class="modal-body">
	<?php foreach(array('topprio', 'alternative') as $prio): ?>
		<div class="row" id="<?php echo $prio ?>">
			<div class="col-sm-6">
				<h4><?php echo $p->t('bewerbung/orgform') ?></h4>
				<div class="radio">
					<label>
						<input type="radio" name="<?php echo $prio ?>Orgform" value="egal">
						<?php echo $p->t('bewerbung/egal') ?>
					</label>
				</div>
				<div class="radio">
					<label>
						<input type="radio" name="<?php echo $prio ?>Orgform" value="VZ">
						<?php echo $p->t('bewerbung/orgform/vollzeit') ?>
					</label>
				</div>
				<div class="radio">
					<label>
						<input type="radio" name="<?php echo $prio ?>Orgform" value="BB">
						<?php echo $p->t('bewerbung/orgform/berufsbegleitend') ?>
					</label>
				</div>
			</div>
			<div class="col-sm-6">
				<h4><?php echo $p->t('bewerbung/sprache') ?></h4>
				<div class="radio">
					<label>
						<input type="radio" name="<?php echo $prio ?>Sprache" value="egal">
						<?php echo $p->t('bewerbung/egal') ?>
					</label>
				</div>
				<div class="radio">
					<label>
						<input type="radio" name="<?php echo $prio ?>Sprache" value="De">
						<?php echo $p->t('bewerbung/sprache/deutsch') ?>
					</label>
				</div>
				<div class="radio">
					<label>
						<input type="radio" name="<?php echo $prio ?>Sprache" value="En">
						<?php echo $p->t('bewerbung/sprache/englisch') ?>
					</label>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
	</div>
	<div class="modal-footer">
		<button class="btn btn-default cancel-prio" data-dismiss="modal"><?php echo $p->t('bewerbung/abbrechen') ?></button>
		<button class="btn btn-primary ok-prio" data-dismiss="modal"><?php echo $p->t('bewerbung/ok') ?></button>
	</div>
</div></div></div>

<script type="text/javascript">
	function checkPrios(slideDuration) {

		var anm = 'keine Prio';

		if($('#topprio input:checked[value="egal"]').length === 2) {

			$('#alternative')
				.addClass('inactive')
				.slideUp(slideDuration);

		} else {

			$('#alternative.inactive')
				.removeClass('inactive')
				.slideDown(slideDuration);

			anm = 'Prio: ' + $('#topprio input[name="topprioOrgform"]:checked').val() + '/'
					+ $('#topprio input[name="topprioSprache"]:checked').val();

			if($('#alternative input:checked[value="egal"]').length !== 2) {
				anm += '; Alt: ' + $('#alternative input[name="alternativeOrgform"]:checked').val() + '/'
					+ $('#alternative input[name="alternativeSprache"]:checked').val();
			}
		}

		return anm;
	}
</script>
