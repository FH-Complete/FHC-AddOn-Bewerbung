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
 * Authors: Alexei Karpenko <karpenko@technikum-wien.at>
 * 			Manfred Kindl <manfred.kindl@technikum-wien.at>
 */

if(!isset($person_id))
{
	die($p->t('bewerbung/ungueltigerZugriff'));
}

echo '<div role="tabpanel" class="tab-pane active" id="uhstat">';

echo '
		<div id="responsiveDiv" class="embed-responsive" style="padding-bottom: 100%;">
		  <iframe id="uhstatIframe"
					class="embed-responsive-item"
					src="../../../index.ci.php/codex/UHSTAT1"
					></iframe>
		</div>
		<div class="form-group">';

	if (array_key_exists(array_search('uhstat', $tabs)-1, $tabs))
	{
		echo '	<button class="btn-nav btn btn-default" type="button" data-jump-tab="'.$tabs[array_search('uhstat', $tabs)-1].'">
					'.$p->t('global/zurueck').'
				</button>';
	}
	if (array_key_exists(array_search('uhstat', $tabs)+1, $tabs))
	{
		echo '	<button class="btn-nav btn btn-default" type="button" data-jump-tab="'.$tabs[array_search('uhstat', $tabs)+1].'">
					'.$p->t('bewerbung/weiter').'
				</button>';
	}
	else
	{
		echo '	<button class="btn-nav btn btn-default" type="button" data-jump-tab="'.$tabs[0].'">
					'.$p->t('bewerbung/menuUebersicht').'
				</button>';
	}

echo '</div></div>';
?>

<script type="text/javascript">
$('#uhstatIframe').load(function()
{
	// remove left and right space of form container
	$('#uhstatIframe').contents().find('#uhstat1Container').removeClass('container');

	// space between iframe and rest
	$('#responsiveDiv').css('padding-bottom', '90%');

	let saved = $('#uhstatIframe').contents().find('#uhstat1Saved').val();

	if (saved == 1) {
		$("#tab_uhstat a").css('background-color', '#DFF0D8');
		$("#uhstatVollstaendig").html('<?php echo $vollstaendig ?>');
	}
});

</script>
