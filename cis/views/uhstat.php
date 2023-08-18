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

// wenn Eingabe gesperrt (z.B. Bewerbung schon abgeschickt), keine Einträge mehr möglich
$readOnly = $eingabegesperrt ? 'readOnly' : '';
// UHSTAT Daten gerade gespeichert
$saved = isset($_POST['uhstat_saved']);

echo '<div role="tabpanel" class="tab-pane active" id="uhstat">';

// success alert
if ($saved)
{
	echo '
			<div class="alert alert-success" id="success-alert_uhstat1">
				<button type="button" class="close" data-dismiss="alert">x</button>
				<strong>'.$p->t('global/erfolgreichgespeichert').'</strong>
			</div>';
}

// div zum reinladen des uhstat Formulars
echo '<div id="uhstatDiv"></div>';

echo	'<div class="alert alert-danger" id="danger-alert_uhstat1" hidden>
			<button type="button" class="close" data-dismiss="alert">x</button>
			<strong>'.$p->t('global/fehleraufgetreten').' </strong>
		</div>';

// Formular, das zum neu Laden der Seite abgeschickt wird
echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'?active=uhstat" class="form-horizontal" id="uhstatFormBewerbung">
			<div class="form-group">';

if (array_key_exists(array_search('uhstat', $tabs)-1, $tabs))
{
	echo '	<button class="btn-nav btn btn-default" type="button" data-jump-tab="'.$tabs[array_search('uhstat', $tabs)-1].'">
				'.$p->t('global/zurueck').'
			</button>';
}
echo '<input type="hidden" value="uhstat_saved" name="uhstat_saved"/>';

echo	'&nbsp;<button class="btn btn-success" type="submit" name="btn_uhstat" id="submit_uhstat">'
			.$p->t('global/speichern').
		'</button>';

if (array_key_exists(array_search('uhstat', $tabs)+1, $tabs))
{
	echo '&nbsp;<button class="btn-nav btn btn-default" type="button" data-jump-tab="'.$tabs[array_search('uhstat', $tabs)+1].'">
				'.$p->t('bewerbung/weiter').'
			</button>';
}

echo '</div></form></div>';
?>

<script type="text/javascript">

// readOnly wenn Bewerbung schon abgeschickt
let readOnly = "?<?php echo $readOnly ?>";

// Laden des Formulars
$("#uhstatDiv").load('../../../index.ci.php/codex/UHSTAT1'+readOnly+' #uhstat1Subcontainer', function() {

	// Animation für success message
	window.setTimeout(function() {
		$("#success-alert_uhstat1").fadeTo(500, 0).slideUp(500, function(){
			$(this).hide();
		});
	}, 1500);

	// wenn submit button geklickt
	$("#submit_uhstat").click(function(e) {
		e.preventDefault();

		// UHSTAT Formular abschicken
		let form = $("#uhstat1Form");
		let actionUrl = form.attr('action');

		$.ajax({
			type: "POST",
			url: actionUrl+readOnly,
			data: form.serialize(),
			success: function(data)
			{
				// Antwort html ins div laden
				$("#uhstatDiv").html($(data).find("#uhstat1Subcontainer").html());

				// saved flag, ist 1 wenn erfolgreich gespeichert
				let saved = $('#uhstat1Saved').val();

				// wenn erfolgreich, seite neu laden durch submit des Formulars im Bewerbungstool
				if (saved == 1) {
					$("#uhstatFormBewerbung").submit();
				}
			},
			// wenn fehlgeschlagen, Fehlermeldungsalert anzeigen
			error: function(error) {
				$("#danger-alert_uhstat1").fadeTo(500, 1);
			}
		});
	});
});

</script>
