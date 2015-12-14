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
?>

<div class="modal fade" id="prio-dialog"><div class="modal-dialog"><div class="modal-content">
	<div class="modal-header">
		<button type="button" class="close cancel-prio" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
		<h4 class="modal-title"><?php echo $p->t('bewerbung/priowaehlen') ?></h4>
	</div>
	<div class="modal-body">
		<div class="row">
			<div class="col-sm-12">
				<p><?php echo $p->t('bewerbung/prioBeschreibungstext') ?></p>
			</div>
		</div>
	<?php 
	
	$orgform_sprache = getOrgformSpracheForOnlinebewerbung();
	
	foreach(array('topprio', 'alternative') as $prio): ?>
		<div class="row" id="<?php echo $prio ?>">
			<div class="col-sm-12">
				<h4><?php echo $p->t('bewerbung/prioUeberschrift' . $prio) ?></h4>
			</div>
			<!--<h4><?php echo $p->t('bewerbung/orgform') ?></h4>-->
			<div class="col-sm-6 priogroup">
			<?php if($prio!='topprio') //Es muss eine Topprio gewaehlt werden
					echo '	<div class="radio">
								<label>
									<input type="radio" name="'.$prio.'Orgform" value="keine">
									'.$p->t('bewerbung/egal').'
								</label>
							</div>'; ?>
			<?php foreach($orgform_sprache as $row): ?>
			
				<div class="radio">
					<label>
						<input type="radio" name="<?php echo $prio ?>Orgform" value="<?php echo $row->orgform_kurzbz.'_'.$row->sprache; ?>">
						<?php echo $p->t('bewerbung/orgform/'.$row->orgform_kurzbz) ?> - <?php echo $p->t('bewerbung/'.$row->sprache) ?>
					</label>
				</div>
			<?php endforeach; ?>
			</div>
		</div>
	<?php endforeach; ?>
	</div>
	<div class="modal-footer">
		<button class="btn btn-default cancel-prio" data-dismiss="modal"><?php echo $p->t('global/abbrechen') ?></button>
		<button class="btn btn-primary ok-prio" data-dismiss="modal"><?php echo $p->t('global/ok') ?></button>
	</div>
</div></div></div>

<script type="text/javascript">
	function checkPrios(slideDuration) {

		var anm = 'keine Prio';

		if($('#topprio input:checked').length === 0) {

			$('#alternative')
				.addClass('inactive')
				.slideUp(slideDuration);

		} else {

			$('#alternative.inactive')
				.removeClass('inactive')
				.slideDown(slideDuration);

			anm = 'Prio: ' + $('#topprio input[name="topprioOrgform"]:checked').val();

			if($('#alternative input:checked').length !== 0) {
				anm += '; Alt: ' + $('#alternative input[name="alternativeOrgform"]:checked').val();
			}
		}

		return anm;
	}

	function getPrioOrgform() 
	{

		var orgform = '';
		orgform = $('#topprio input[name="topprioOrgform"]:checked').val();
		
		if(orgform == undefined)
			orgform = '';

		if(orgform!='')
			orgform = orgform.split('_')[0];

		return orgform;
	}

	function prioAvailable(modal_orgformsprache) {

		var prios = {
				egal:'keine',
				German: 'De',
				English: 'En'
			};

		modal_orgformsprache.push('keine');
		$('#prio-dialog input').prop('disabled', true);
		$('#prio-dialog input').parent().prop({
			hidden: true
		});

		for(var i = 0; i < modal_orgformsprache.length; i++)
		{
			$('#prio-dialog input[value="' + modal_orgformsprache[i] + '"]').parent().prop({
				hidden:false
			});

			$('#prio-dialog input[value="' + modal_orgformsprache[i] + '"]').prop({
				disabled: false,
				checked: false
			});
		}
	}

	//Orig
	/*
	function prioAvailable(modal_orgform, modal_sprache) {
		var prios = {
				egal:'keine',
				German: 'De',
				English: 'En'
			};
		modal_orgform.push('egal');
		modal_sprache.push('egal');
		$('#prio-dialog input').prop('disabled', true);
		$('#prio-dialog input').parent().prop({
			hidden:true
		});
		for(var i = 0; i < modal_orgform.length; i++)
		{
			$('#prio-dialog input[value="' + modal_orgform[i] + '"]').parent().prop({
				hidden:false
			});
			$('#prio-dialog input[value="' + modal_orgform[i] + '"]').prop({
				disabled: false,
				checked: true
			});
		}
		for(var i = 0; i < modal_sprache.length; i++)
		{
			$('#prio-dialog input[value="' + prios[modal_sprache[i]] + '"]').prop({
				disabled: false,
				checked: true
			});
			$('#prio-dialog input[value="' + prios[modal_sprache[i]] + '"]').parent().prop({
				hidden:false,
			});
		}
		
		$('.priogroup').each(function(i, value) {
			var disabled_inputs = $(value).find('input:disabled').length;

			if(disabled_inputs === 1)
			{
				$(value).find('input:disabled').prop({
					disabled: false,
					checked: true
				});
			}
		});*/
</script>
