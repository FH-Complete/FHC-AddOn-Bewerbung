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
 * Authors: Manfred Kindl <manfred.kindl@technikum-wien.at>
 			Manuela Thamer <manuela.thamer@technikum-wien.at>
 */

if (!isset($person_id))
{
	die($p->t('bewerbung/ungueltigerZugriff'));
}
?>

<div role="tabpanel" class="tab-pane" id="akten">
	<?php
	$datum_obj = new datum();
	$studiengang = new studiengang();
	$studiengang->getAll(null, null);

	$stg_arr = array();
	foreach ($studiengang->result as $row)
	{
		$typ = new studiengang();
		$typ->getStudiengangTyp($row->typ);
		$oe = new organisationseinheit();
		$oe->load($row->oe_kurzbz);
		$stg_arr[$row->studiengang_kz]['kuerzel'] = $row->kuerzel;
		$stg_arr[$row->studiengang_kz]['typ'] = $typ->bezeichnung;
		$stg_arr[$row->studiengang_kz]['German'] = $row->bezeichnung_arr['German'];
		$stg_arr[$row->studiengang_kz]['English'] = $row->bezeichnung_arr['English'];
		$stg_arr[$row->studiengang_kz]['OE'] = $oe->organisationseinheittyp_kurzbz.' '.$oe->bezeichnung;
		$stg_arr[$row->studiengang_kz]['oe_kurzbz'] = $oe->oe_kurzbz;
	}

	echo '<h2>'.$p->t('bewerbung/akten').'</h2>';

	$akten = new akte();
	$akten->getArchiv($person_id, null, true);
	// Sortiert die Akten alphabetisch
	function sortAkten($a, $b)
	{
		$c = strcmp(strtolower($a->bezeichnung), strtolower($b->bezeichnung));
		return $c;
	}
	if ($akten)
	{
		usort($akten->result, "sortAkten");
	}
	$anzahlZuAkzeptieren = 0;

	//manu2
	$pres = new prestudent();
	$existsOffeneBewerbung = $pres->existsOffeneBewerbung($person_id);
	$isStudent = $pres->isStudent($person_id);
	count($akten->result) > 1 ? $showAktentext = true : $showAktentext = false;
	echo '<div id="existsOffeneBewerbung" style="display: none">'. $existsOffeneBewerbung. '</div>';
	echo '<div id="isStudent" style="display: none">'. $isStudent. '</div>';
	echo '<div id="showAktentext" style="display: none">'. $showAktentext. '</div>';

	if (count($akten->result) > 0)
	{
		echo '<ul class="list-group">';

		foreach ($akten->result as $row)
		{
			$class = '';
			if ($row->dokument_kurzbz == 'Ausbvert' && $row->akzeptiertamum == '')
			{
				$class = 'style="background-color: #f2dede;"';
				echo '<div id="Aktentext"></div>';
			}
			elseif ($row->dokument_kurzbz == 'Ausbvert' && $row->akzeptiertamum != '')
			{
				$class = '';
				echo '<div id="Aktentext2"></div>';
			}
			echo '
				<li id="output" class="list-group-item" '.$class.'>
					<div class="row">
						<div class="col-sm-12">
							<form method="POST" action="'.$_SERVER['PHP_SELF'].'?active=akten" class="form-horizontal pull-right" >
								<input type="hidden" name="action" value="downloadAkte">
								<input type="hidden" name="akte_id" value="'.$row->akte_id.'">
								<button type="submit" title="'.$p->t('bewerbung/dokumentHerunterladen').'"
										class="btn btn-default btn-sm">
									<span class="glyphicon glyphicon glyphicon-download-alt" aria-hidden="true" title="'.$p->t('bewerbung/dokumentHerunterladen').'"></span>
									'.$p->t('bewerbung/herunterladen', array($row->bezeichnung)).'
								</button>
							</form>
							<h4>'.$row->bezeichnung.'</h4>';
			if ($row->dokument_kurzbz == 'Ausbvert')
			{
				if ($row->akzeptiertamum == '')
				{
					$anzahlZuAkzeptieren++;
				}
				echo '
				<br>
				'.$p->t('bewerbung/informationDatenverwendungStudierende').'
				<br><br>
				<div class="checkbox">
					<label><input id="checkbox1ausbildungsvertrag'.$row->akte_id.'" type="checkbox" class="checkboxAusbildungsvertrag"
					name="'.$row->akte_id.'"
					'.($row->akzeptiertamum != '' ? 'checked="checked" disabled="disabled"' : '').'>
					'.$p->t('bewerbung/textAusbildungsvertrag').'</label>
				</div>';

				/*echo '<div class="checkbox">
					<label>
						<input id="checkbox2ausbildungsvertrag" type="checkbox" class="checkboxAusbildungsvertrag"
						'.($row->akzeptiertamum != '' ? 'checked="checked" disabled="disabled"' : '').'>
						'.$p->t('bewerbung/textRuecktrittsrecht').'
					</label>
				</div>';*/
				echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'?active=akten" class="form-horizontal">
					<input type="hidden" name="action" value="acceptAkte">
					<input type="hidden" name="akte_id" value="'.$row->akte_id.'">
					<button type="submit" id="acceptAkteButton'.$row->akte_id.'" title=""
							class="btn '.($row->akzeptiertamum != '' ? 'btn-success' : 'btn-primary').' btn-sm" disabled>
							'.($row->akzeptiertamum != '' ?  $p->t('bewerbung/akzeptiert', array($row->bezeichnung)) : $p->t('bewerbung/akzeptieren', array($row->bezeichnung))).'
					</button>
				</form>';
			}
					echo '		</div>
		</div>
	</li>';
		}
		echo '</ul> ';
		// Hier wird ein unsichtbares div mit der Anzahl der akzeptierten Akten ausgegeben
		// Auf dieses wird dann mit jquery abgefragt um den Menüpunkt einfärben zu können.
		echo '<div id="anzahlAktenZuAkzeptieren" style="display: none">'.$anzahlZuAkzeptieren.'</div>';
	}
	else
	{
		echo '<p>'.$p->t('bewerbung/keineAktenVorhanden').'</p>';
	}
	?>

</div>
<script type="text/javascript">
	$(function()
	{
		$(".checkboxAusbildungsvertrag").click(function()
		{
			//if ($("#checkbox1ausbildungsvertrag").is(':checked') && $("#checkbox2ausbildungsvertrag").is(':checked'))
			var akteId = $(this).attr('name');
			if ($("#checkbox1ausbildungsvertrag"+akteId).is(':checked'))
			{

				$("#acceptAkteButton"+akteId).attr("disabled", false);
			}
			else
			{
				$("#acceptAkteButton"+akteId).attr("disabled", true);
			}
		});

		var anzahlAktenZuAkzeptieren = $('#anzahlAktenZuAkzeptieren').html();
		var existsOffeneBewerbung = $('#existsOffeneBewerbung').html();
		var isStudent = $('#isStudent').html();
		var showAktentext = $('#showAktentext').html();

		if (anzahlAktenZuAkzeptieren != '')
		{
			anzahlAktenZuAkzeptieren = parseInt(anzahlAktenZuAkzeptieren);

			if ((anzahlAktenZuAkzeptieren > 0) && (existsOffeneBewerbung != ''))
			{
				$('#tabAktenLink').css('background-color','#F2DEDE');
				$('#tabAktenLink').hover(
					function()
					{
						$(this).css('background-color','#F2DEDE');
					}
				);
				$('#tabAktenStatustext').text('<?php echo $p->t('bewerbung/unvollstaendig')?>');
				$('#tabAktenStatustext').addClass('text-danger');
			}
			else if((anzahlAktenZuAkzeptieren > 0) && (existsOffeneBewerbung == ''))
			{
				$('#output42').text('<?php echo 'FALL anzahl zu akzeptieren > 0 und keine offene Bewerbung'?>');
				if(!isStudent)
				{
					$('#output').hide();
					$('#Aktentext').text('<?php echo $p->t('bewerbung/keineAktenVorhanden')?>');
					$('#output42').text('<?php echo "HIER"?>');
					if(showAktentext)
					{
						$('#tabAktenStatustext').html('&nbsp;');
						$('#Aktentext').text('<?php echo ""?>');
					}
				}
				else
				{
					$('#tabAktenLink').css('background-color','#F2DEDE');
					$('#tabAktenLink').hover(
						function()
						{
							$(this).css('background-color','#F2DEDE');
						}
					);
					$('#tabAktenStatustext').text('<?php echo $p->t('bewerbung/unvollstaendig')?>');
					$('#tabAktenStatustext').addClass('text-danger');
				}

			}
			else
			{
				if(existsOffeneBewerbung == '' && !isStudent)
				{
					$('#output').hide();
					$('#Aktentext2').text('<?php echo $p->t('bewerbung/keineAktenVorhanden')?>');
					if(showAktentext)
					{
						$('#tabAktenStatustext').html('&nbsp;');
						$('#Aktentext2').text('<?php echo ""?>');
					}
				}
				else
				{
				$('#tabAktenLink').css('background-color','#DFF0D8');
				$('#tabAktenLink').hover(
					function()
					{
						$(this).css('background-color','#DFF0D8');
					}
				);
				$('#tabAktenStatustext').text('<?php echo $p->t('bewerbung/vollstaendig')?>');
				$('#tabAktenStatustext').addClass('text-success');
				}
			}
		}
		else
		{
			$('#tabAktenStatustext').html('&nbsp;');
		}
	});
</script>
