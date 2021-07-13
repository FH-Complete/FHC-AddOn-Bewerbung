<?php
/*
 * Copyright (C) 2021 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * Authors: Robert Hofer <robert.hofer@technikum-wien.at>
 * 			Manfred Kindl <manfred.kindl@technikum-wien.at>
 * 			Manuela Thamer <manuela.thamer@technikum-wien.at>
 */
if (! isset($person_id))
{
	die($p->t('bewerbung/ungueltigerZugriff'));
};
?>
<div role="tabpanel" class="tab-pane" id="dokumente">
	<h2><?php echo $p->t('bewerbung/menuDokumente'); ?></h2>
	<div class="" id="dokumente_message_div"></div>
	<?php

	if ($save_error_dokumente === false)
	{
		echo '	<div class="alert alert-success" id="success-alert_daten">
				<button type="button" class="close" data-dismiss="alert">x</button>
					<strong>'.$message.'</strong>
				</div>';
	}
	elseif ($save_error_dokumente === true)
	{
		echo '	<div class="alert alert-danger" id="danger-alert">
				<button type="button" class="close" data-dismiss="alert">x</button>
					<strong>'.$p->t('global/fehleraufgetreten').' </strong>'.$message.'
				</div>';
	}

	$db = new basis_db();

	// Sortiert die Dokumente je nach Sprache alphabetisch nach bezeichnung_mehrsprachig
	// Pflichtdokumente werden als erstes ausgegeben
	// Gepruefte Dokumente werden nach unten sortiert
	function sortDocuments($a, $b)
	{
		$c = $a->studiengang_kz - $b->studiengang_kz;
		$c .= $a->anzahl_akten_vorhanden - $b->anzahl_akten_vorhanden;
		$c .= $a->anzahl_dokumente_akzeptiert - $b->anzahl_dokumente_akzeptiert;
		$c .= $b->pflicht - $a->pflicht;
		$c .= $a->stufe - $b->stufe;
		$c .= strcmp(strtolower($a->bezeichnung_mehrsprachig[getSprache()]), strtolower($b->bezeichnung_mehrsprachig[getSprache()]));
		$c .= $a->anzahl_akten_formal_geprueft - $b->anzahl_akten_formal_geprueft;

		return $c;
	}
	if ($dokumente_abzugeben)
		usort($dokumente_abzugeben, "sortDocuments");

	$anzahlOffeneDokumente = 0;
	if ($dokumente_abzugeben)
	{
		$currentStudiengangKz = '';
		$stufePrestudent = 0;
		$dokumentKurzbz = '';
		$anzahlDokumente = 0;
		foreach ($dokumente_abzugeben as $dok)
		{
			// Stufe des PreStudenten ermitteln. Wenn Stufe < Dokumentstufe, zum nächsten weitergehen.
			$stufePrestudent = getStufeBewerberFuerDokumente($dok->prestudent_id);
			if ($stufePrestudent < $dok->stufe && $dokumentKurzbz != $dok->dokument_kurzbz)
			{
				continue;
			}

			// Wenn es 2 Orgformen in einem Studiengang gibt, werden die Dokumente doppelt ausgegeben,
			// da auch die Prestudent_id geliefert wird. Diese wird jedoch für die Ermittlung der Stufe benötigt.
			// Deshalb werden doppelte Dokumente hier übersprungen
			if ($dok->dokument_kurzbz == $dokumentKurzbz)
			{
				continue;
			}
			$anzahlDokumente++;

			if ($anzahlDokumente == 1)
			{
				echo '<p>'.$p->t('bewerbung/bitteDokumenteHochladen').'</p>';
			}

			$dokumentKurzbz = $dok->dokument_kurzbz;

			if ($dok->studiengang_kz != $currentStudiengangKz)
			{
				if ($currentStudiengangKz != '')
				{
					$dokumentKurzbz = '';
					echo '	</div></fieldset>';
				}
				$currentStudiengangKz = $dok->studiengang_kz;
				echo '	<fieldset>';
				// Allgemeine Dokumente als solche kennzeichnen
				if ($dok->studiengang_kz == '0')
				{
					echo '<legend>'.$p->t('bewerbung/allgemeineDokumente').'</legend>';
				}
				else
				{
					$studiengang = new studiengang($dok->studiengang_kz);
					$studiengang->getStudiengangTyp($studiengang->typ);
					echo '<legend>'.$p->t('bewerbung/dokumenteFuer').' '.$studiengang->bezeichnung.' '.$studiengang->bezeichnung_arr[getSprache()].'</legend>';
				}
				echo '<div class="panel-group">';
			}

			// Fallback Dokumentbezeichnung auf DEFAULT_LANGUAGE
			$dokumentbezeichnung = $dok->bezeichnung_mehrsprachig[getSprache()];
			if ($dokumentbezeichnung == '')
			{
				$dokumentbezeichnung = $dok->bezeichnung_mehrsprachig[DEFAULT_LANGUAGE];
			}

			// Detailbeschreibungen zu Dokumenten holen. Diese werden mit den allgemeinen Beschreibungen zusammengeführt
			$details = new dokument();
			$details->getBeschreibungenDokumente(array($dok->studiengang_kz), $dok->dokument_kurzbz);

			$detailstring_htmlspecialchars = '';
			$detailstring_original = '';

			if ($dok->dokumentbeschreibung_mehrsprachig[getSprache()] != '')
			{
				$detailstring_htmlspecialchars .= htmlspecialchars($dok->dokumentbeschreibung_mehrsprachig[getSprache()]);
				$detailstring_original .= $dok->dokumentbeschreibung_mehrsprachig[getSprache()];
			}
			foreach ($details->result as $studiengangdetail)
			{
				$stg = new studiengang();
				$stg->load($studiengangdetail->studiengang_kz);

				if ($studiengangdetail->beschreibung_mehrsprachig[getSprache()] != '')
				{
					if ($detailstring_htmlspecialchars != '')
					{
						$detailstring_htmlspecialchars .= '<br/><hr/>';
						$detailstring_original .= '<br/><hr/>';
					}
					$detailstring_htmlspecialchars .= '<b>' . $stg->kuerzel . '</b>: ' . htmlspecialchars($studiengangdetail->beschreibung_mehrsprachig[getSprache()]);
					$detailstring_original .= '<b>' . $stg->kuerzel . '</b>: ' . $studiengangdetail->beschreibung_mehrsprachig[getSprache()];
				}
				else
				{
					$detailstring_htmlspecialchars .= '';
					$detailstring_original .= '';
				}
			}

			$collapseStatus = 'collapse in';
			$statusInfotext = '';
			$displayDetailsArrow = true;
			// Panel-Header unterschiedlich stylen
			// Wenn akten vorhanden sind oder das Dokument bereits akzeptiert wurde
			if ($dok->anzahl_akten_vorhanden > 0
				|| (defined('BEWERBERTOOL_UPLOAD_DOKUMENT_WENN_AKZEPTIERT')
					&& BEWERBERTOOL_UPLOAD_DOKUMENT_WENN_AKZEPTIERT === false
					&& $dok->anzahl_dokumente_akzeptiert > 0))
			{
				echo '<div class="panel panel-success">';
				$collapseStatus = 'collapse';
				$statusInfotext = '';
			}
			elseif ($dok->anzahl_akten_wird_nachgereicht > 0)
			{
				echo '<div class="panel panel-warning">';
				$statusInfotext = '<div class="label label-warning">'.$p->t('bewerbung/dokumentWirdNachgereicht').'</div>';
				$displayDetailsArrow = false;
			}
			elseif ($dok->pflicht && $dok->anzahl_dokumente_akzeptiert == 0)
			{
				echo '<div class="panel panel-danger">';
				$statusInfotext = '<div class="label label-danger">'.$p->t('bewerbung/dokumentErforderlich').'</div>';
				$displayDetailsArrow = false;
				$anzahlOffeneDokumente ++;
			}
			elseif ($dok->pflicht && $dok->anzahl_dokumente_akzeptiert > 0) //Fall: bereits akzeptiertes Dokument
			{
				echo '<div class="panel panel-default">';
				$statusInfotext = '<div class="label label-default">'.$p->t('bewerbung/dokumentNichtErforderlich').'</div>';
				$displayDetailsArrow = false;
				//$anzahlOffeneDokumente ++;
			}
			else
			{
				echo '<div class="panel panel-default">';
				//$statusInfotext = '<div class="label label-default">'.$p->t('bewerbung/dokumentNichtErforderlich').'</div>';
				$displayDetailsArrow = false;
			}

			// Invitation-Letter an der FHTW immer anzeigen
			if (CAMPUS_NAME == 'FH Technikum Wien' && $dok->dokument_kurzbz == 'InvitLet')
			{
				$collapseStatus = 'collapse in';
			}

			echo '	<div style="cursor: pointer" class="panel-heading" data-toggle="collapse" data-target="#panelcollapse_'.$dok->studiengang_kz.'_'.$dok->dokument_kurzbz.'">
						<div class="row">
							<div class="col-sm-6">'.$dokumentbezeichnung.'</div>
							<div class="col-sm-6 hidden-xs text-right">'.$statusInfotext.'</div>
							<div class="col-sm-6 visible-xs">'.$statusInfotext.'</div>
						</div>
						<div class="row details-arrow '.($displayDetailsArrow == true ? '':'hidden').'">
							<div class="col-xs-12 text-center">
								<span class="glyphicon glyphicon-chevron-down text-muted"></span>
								<span class="text-muted small">'.$p->t('bewerbung/details').'</span>
							</div>
						</div>
					</div>';
			/*echo '  <div class="row">
						<div class="col-sm-6">
							<p class="list-group-item-heading">
								'.$dokumentbezeichnung.'
							</p>';*/

			echo ' <div id="panelcollapse_'.$dok->studiengang_kz.'_'.$dok->dokument_kurzbz.'" class="panel-collapse '.$collapseStatus.'">';

			// Bei nachreichbaren Dokumenten wird ein Formular zur Eingabe der Nachreich-Daten angezeigt
			if ($dok->nachreichbar)
			{
				echo '	<div class="panel-body" id="nachreichdaten_'.$dok->studiengang_kz.'_'.$dok->dokument_kurzbz.'" style="display: none">';
				echo '		<div class="col-sm-12">';
				echo		    getNachreichForm($dok->dokument_kurzbz, $dok->studiengang_kz);
				echo '		</div>';
				echo '  </div>';
			}
			echo '      <div class="panel-body" id="panelbody_'.$dok->studiengang_kz.'_'.$dok->dokument_kurzbz.'">';
			echo '			<div class="col-sm-6" style="padding-bottom: 5px">';
			if ($detailstring_original != '')
			{
					echo '      <div id="details_'.$dok->studiengang_kz.'_'.$dok->dokument_kurzbz.'"
									class="dokumentdetails fade-out"
									onclick="showDetails(\'details_'.$dok->studiengang_kz.'_'.$dok->dokument_kurzbz.'\')">
									'.$detailstring_original.'
								</div>
								<div class="text-center"
										onclick="showDetails(\'details_'.$dok->studiengang_kz.'_'.$dok->dokument_kurzbz.'\')">
									<span class="glyphicon glyphicon-chevron-down text-muted"></span>
									<span class="text-muted small">'.$p->t('bewerbung/mehr').'</span>
								</div>';
			}
			echo '			</div>';

			//$akten = new akte();
			//$akten->getAkten($person_id, $dok->dokument_kurzbz);

			// Wenn mindestens eine Akte vorhanden ist, oder nachgereicht wird
			if ($dok->anzahl_akten_vorhanden > 0 || $dok->anzahl_akten_wird_nachgereicht > 0)
			{
				// Dokument aus $status_dokumente_arr entfernen, um zu wissen, ob dieser Studiengang abgeschickt werden darf
				if (isset($ben_kz[$dok->dokument_kurzbz]))
				{
					foreach ($ben_kz[$dok->dokument_kurzbz] as $kennzahl)
					{
						if (array_key_exists($kennzahl, $status_dokumente_arr))
						{
							if (($key = array_search($dok->dokument_kurzbz, $status_dokumente_arr[$kennzahl])) !== false)
							{
								unset($status_dokumente_arr[$kennzahl][$stufePrestudent][$key]);
							}
						}
					}
				}

				// Wenn mehr als 1 Dokument hochgeladen werden darf, Spalte mit Fileselect und Upload-Button
				if (!defined('BEWERBERTOOL_ANZAHL_DOKUMENTPLOAD_JE_TYP') || is_numeric(BEWERBERTOOL_ANZAHL_DOKUMENTPLOAD_JE_TYP))
				{
					$uploadButtonVisible = true;
					$offsetAktenListe = '';
					if (defined('BEWERBERTOOL_ANZAHL_DOKUMENTPLOAD_JE_TYP') && $dok->anzahl_akten_vorhanden >= BEWERBERTOOL_ANZAHL_DOKUMENTPLOAD_JE_TYP)
					{
						// Wenn ANZAHL_DOKUMENTPLOAD_JE_TYP erreicht ist, Upload-Button ausblenden
						$uploadButtonVisible = false;
						$offsetAktenListe = 'col-sm-offset-3';
					}

					// Upload-Button ausblenden wenn FHTW und Invitation-Letter und offset auf 3
					if (CAMPUS_NAME == 'FH Technikum Wien' && $dok->dokument_kurzbz == 'InvitLet')
					{
						$uploadButtonVisible = false;
						$offsetAktenListe = 'col-sm-offset-3';
					}

					// Akten ausgeben
					echo '	<div class="col-sm-3 aktenliste_'.$dok->dokument_kurzbz.' '.$offsetAktenListe.'" style="padding-bottom: 5px">';
						echo getAktenListe($person_id, $dok->dokument_kurzbz);
					echo '	</div>';

					// Upload-Button ausgeben
					echo '	<div class="col-sm-3 text-right">';
						echo getUploadButton($dok->dokument_kurzbz, false, $uploadButtonVisible, $dok->studiengang_kz, $dok->ausstellungsdetails);
					echo '	</div>';
				}
				else
				{
					// Akten ausgeben
					echo '	<div class="col-sm-3 col-sm-offset-3 aktenliste_'.$dok->dokument_kurzbz.'" style="padding-bottom: 5px">';
						echo getAktenListe($person_id, $dok->dokument_kurzbz);
					echo '	</div>';
				}

			}
			// Wenn das Dokument bereits akzeptiert aber noch nichts hochgeladen wurde
			elseif (defined('BEWERBERTOOL_UPLOAD_DOKUMENT_WENN_AKZEPTIERT')
				&& BEWERBERTOOL_UPLOAD_DOKUMENT_WENN_AKZEPTIERT === false
				&& $dok->anzahl_dokumente_akzeptiert > 0)
			{
				// Akten ausgeben
				echo '	<div class="col-sm-3 col-sm-offset-3 aktenliste_'.$dok->dokument_kurzbz.'" style="padding-bottom: 5px">';
				echo getAktenListe($person_id, $dok->dokument_kurzbz);
				echo '	</div>';
			}
			// Noch keine Akte vorhanden
			else
			{
				// Spalte mit Fileselect und Upload-Button
				echo '	<div class="col-sm-6 text-right">';
					echo getUploadButton($dok->dokument_kurzbz, $dok->nachreichbar, true, $dok->studiengang_kz, $dok->ausstellungsdetails);
				echo '	</div>';
			}

			// Bei Lichtbildern wird zusätzlich ein Modal für den Bildzuschnitt mit Croppie angezeigt
			if ($dok->dokument_kurzbz == 'LichtbilXXX')
			{
				echo '  <div class="modal fade"
							id="zuschnittLichtbildModal"
							tabindex="-1"
							role="dialog"
							aria-labelledby="zuschnittModalLabel"
							aria-hidden="true">
							<div class="modal-dialog">
								<div class="modal-content">
									 <div class="modal-header">
									    Lichtbild hochladen -> Phrase
								    </div>
								    <div class="modal-body">

								        <img id="croppie-container" src="#" />
							        </div>
							        <div class="modal-footer">
							            <button type="button" class="btn btn-default" data-dismiss="modal">' . $p->t('global/abbrechen') . '</button>
							            <button id="submitimage"
												type="submit"
												name="submitimage"
												class="btn btn-labeled btn-primary">
											'.$p->t('bewerbung/upload').'
										</button>
							        </div>
								</div>
							</div>
						</div>';
			}

			echo '
					</div><!--Ende Body --></div><!--Ende Collapsive--></div><!--Ende Panel -->

			';
		}

		if ($anzahlDokumente == 0)
		{
			echo '<div class="alert alert-info">'.$p->t('bewerbung/keineDokumenteErforderlich').'</div>';
			echo '<div id="anzahlOffeneDokumente" style="display: none"></div>';
		}
		else
		{
			echo '	</div></fieldset>';
		}
	}
	else
	{
		echo '<div class="alert alert-info">'.$p->t('bewerbung/keineDokumenteErforderlich').'</div>';
		echo '<div id="anzahlOffeneDokumente" style="display: none"></div>';
	}
	// Hier wird ein unsichtbares div mit der Anzahl an erforderlichen Dokumenten ausgegeben
	// Auf dieses wird dann mit jquery abgefragt um den Menüpunkt einfärben zu können.
	echo '<div id="anzahlOffeneDokumente" style="display: none">'.$anzahlOffeneDokumente.'</div>';
	?>

	<button class="btn-nav btn btn-default" type="button"
		data-jump-tab="<?php echo $tabs[array_search('dokumente', $tabs)-1] ?>">
		<?php echo $p->t('global/zurueck') ?>
	</button>
	<?php
	echo '	<button class="btn-nav btn btn-default" type="button"
				data-jump-tab="'.$tabs[array_search('dokumente', $tabs)+1].'">
				'.$p->t('bewerbung/weiter').'
			</button>';
	?>

	<br /><br/><br/>
	<script type="text/javascript">
		function checkNachgereicht(dokument)
		{
			var zgvDat = document.getElementById('nachreichungam_'+dokument).value;
			zgvDat = zgvDat.split(".");

			if(zgvDat.length !== 3)
			{
				alert("<?php echo $p->t('bewerbung/datumsformatUngueltig')?>");
				return false;
			}

			if(zgvDat[0].length !==2 && zgvDat[1].length !== 2 && zgvDat[2].length !== 4)
			{
				alert("<?php echo $p->t('bewerbung/datumsformatUngueltig')?>");
				return false;
			}

			var dateZgv = new Date(zgvDat[2], zgvDat[1]-1, zgvDat[0]);
			var now = new Date();

			zgvDat[0] = parseInt(zgvDat[0], 10);
			zgvDat[1] = parseInt(zgvDat[1], 10);
			zgvDat[2] = parseInt(zgvDat[2], 10);

			if(!(dateZgv.getFullYear() === zgvDat[2] && (dateZgv.getMonth()+1) === zgvDat[1] && dateZgv.getDate() === zgvDat[0]))
			{
				alert("<?php echo $p->t('bewerbung/datumsformatUngueltig')?>");
				return false;
			}

			// Check ob ZGV-Datum in der Vergangenheit liegt
			if(dateZgv < now)
			{
				alert("<?php echo $p->t('bewerbung/nachreichDatumNichtVergangenheit')?>");
				return false;
			}

			var anmerkung = document.getElementById('anmerkung_'+dokument).value;
			if(anmerkung.length == 0)
			{
				alert("<?php echo $p->t('bewerbung/bitteAnmerkungEintragen')?>");
				return false;
			}
			// Check ob File ausgewählt wurde
			if(document.getElementById('filenachgereicht_'+dokument).value == "")
			{
				alert("<?php echo $p->t('bewerbung/bitteDateiAuswaehlen')?>");
				return false;
			}
		};
		$('.linkPopover').on('click', function (event) {
			//Set timeout to wait for popup div to redraw(animation)
			setTimeout(function(){
				if($('div.popover').css('top').charAt(0) === '-'){
					 $('div.popover').css('top', '0px');
					 var buttonTop = $(event.currentTarget).position().top;
					 var buttonHeight = $(event.currentTarget).height();
					 $('.popover.right>.arrow').css('top', buttonTop + (buttonHeight/2));
				}
			},100);
		});
		function checkAusstellungsnation()
		{
			alert($(this).closest("form").find(".ausstellungsnation").length);
			return false;
			if ($(this).closest("form").find(".ausstellungsnation").length)
			{
				alert("Ja");
				$(this).parents("form").find(".ausstellungsnation").addClass("errorAusstellungsnation");
				return false;
			}
			else
			{
				alert("Nein");
				$("#ausstellungsnation").removeClass("errorAusstellungsnation");
				return false;
			}
		};
		function showDetails(id)
		{
			if ($('#'+id).hasClass('fade-out'))
				$('#'+id).removeClass('fade-out');
			else
				$('#'+id).addClass('fade-out');
		}
		// Farbe des Tabs abschließend mit jQuery stylen
		$(document).ready(function()
		{
			var anzahlOffeneDokumente = $('#anzahlOffeneDokumente').html();

			if (anzahlOffeneDokumente != '')
			{
				anzahlOffeneDokumente = parseInt(anzahlOffeneDokumente);

				if (anzahlOffeneDokumente != 0)
				{
					$('#tabDokumenteLink').css('background-color','#F2DEDE');
					$('#tabDokumenteLink').hover(
						function()
						{
							$(this).css('background-color','#F2DEDE');
						}
					);
					$('#tabDokumenteStatustext').text('<?php echo $p->t('bewerbung/unvollstaendig')?>');
					$('#tabDokumenteStatustext').addClass('text-danger');
				}
				else
				{
					$('#tabDokumenteLink').css('background-color','#DFF0D8');
					$('#tabDokumenteLink').hover(
						function()
						{
							$(this).css('background-color','#DFF0D8');
						}
					);
					$('#tabDokumenteStatustext').text('<?php echo $p->t('bewerbung/vollstaendig')?>');
					$('#tabDokumenteStatustext').addClass('text-success');
				}
			}
			else
			{
				$('#tabDokumenteStatustext').html('&nbsp;');
			}

			$('.panel-collapse').on('show.bs.collapse', function ()
			{
				$(this).siblings('.panel-heading').find('div.details-arrow').addClass('hidden');
			})
			$('.panel-collapse').on('hide.bs.collapse', function ()
			{
				$(this).siblings('.panel-heading').find('div.details-arrow').removeClass('hidden');
			})

			$('.documentUploadForm').submit(function()
			{
				var ausstellungsNationSelect = $(this).find(".ausstellungsnation");
				if (ausstellungsNationSelect.length == 1 && ausstellungsNationSelect.val() == '')
				{
					ausstellungsNationSelect.addClass("errorAusstellungsnation");
					return false;
				}
				else
				{
					ausstellungsNationSelect.removeClass("errorAusstellungsnation");
					return true;
				}
			});
		});
	</script>
</div>
