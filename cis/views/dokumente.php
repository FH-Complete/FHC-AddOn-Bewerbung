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

<div role="tabpanel" class="tab-pane" id="dokumente">
	<h2><?php echo $p->t('bewerbung/menuDokumente'); ?></h2>
	<p><?php echo $p->t('bewerbung/bitteDokumenteHochladen'); ?></p>
	<a href="dms_akteupload.php?person_id=<?php echo $person_id ?>"
	   onclick="FensterOeffnen(this.href); return false;">
		<?php echo $p->t('bewerbung/linkDokumenteHochladen'); ?>
	</a>
	<p><?php echo $p->t('bewerbung/dokumenteZumHochladen'); ?></p>
	<?php
	$db = new basis_db();

	$dokumente_person = new dokument();
	$dokumente_person->getAllDokumenteForPerson($person_id, true);

	$studiensemester = new studiensemester();
	$studiensemester->getStudiensemesterOnlinebewerbung();
	$stsem_array = array();
	foreach($studiensemester->studiensemester AS $s)
		$stsem_array[] = $s->studiensemester_kurzbz;
	?>

	<div class="">
		<table class="table table-striped">
			<thead>
				<tr>
					<th><?php echo $p->t('bewerbung/dokumentName'); ?></th>
					<th><?php echo $p->t('bewerbung/details');?></th>
					<th><?php echo $p->t('bewerbung/status'); ?></th>
					<th><?php echo $p->t('global/aktion'); ?></th>
					<th></th>
					<th><?php echo $p->t('bewerbung/benoetigtFuer'); ?></th>
				</tr>
			</thead>
			<tbody>
		<?php
		foreach($dokumente_person->result as $dok):
			$akte = new akte;
			$akte->getAkten($person_id, $dok->dokument_kurzbz);

			$qry = "SELECT DISTINCT studiengang_kz,typ||kurzbz AS kuerzel FROM public.tbl_dokumentstudiengang
				JOIN public.tbl_prestudent USING (studiengang_kz)
				JOIN public.tbl_prestudentstatus USING (prestudent_id)
				JOIN public.tbl_studiengang USING (studiengang_kz)
				WHERE dokument_kurzbz = ".$db->db_add_param($dok->dokument_kurzbz)."
 				AND person_id =".$db->db_add_param($person_id, FHC_INTEGER)."
 				AND tbl_prestudentstatus.status_kurzbz = 'Interessent'
 				/*AND tbl_prestudentstatus.studiensemester_kurzbz IN (".$db->implode4SQL($stsem_array).") */
 				ORDER BY kuerzel";

			$ben = "";
			$ben_kz = array();
			$detailstring = '';
			if($result = $db->db_query($qry))
			{
				while($row = $db->db_fetch_object($result))
				{
					if($ben!='')
						$ben.=', ';

					$stg = new studiengang();
					$stg->load($row->studiengang_kz);

					$ben .= $stg->kuerzel;
					$ben_kz[] .= $row->studiengang_kz;
				}
			}

			$details = new dokument();
			$details->getBeschreibungenDokumente($ben_kz, $dok->dokument_kurzbz);
			$i=0;

			foreach($details->result AS $row)
			{
				$stg = new studiengang();
				$stg->load($row->studiengang_kz);

				if($detailstring!='' && ($row->beschreibung_mehrsprachig[getSprache()]!='' || ($row->dokumentbeschreibung_mehrsprachig[getSprache()]!='' && $i==0)))
					$detailstring .= '<br/><hr/>';
				if($row->dokumentbeschreibung_mehrsprachig[getSprache()]!='' && $i==0)
				{
					$detailstring .= $row->dokumentbeschreibung_mehrsprachig[getSprache()];
					//Allgemeine Dokumentbeschreibung nur einmal ausgeben
					$i++;
				}
				elseif ($row->beschreibung_mehrsprachig[getSprache()]!='')
				{
					$detailstring .= '<b>'.$stg->kuerzel.'</b>: '.$row->beschreibung_mehrsprachig[getSprache()];
				}
				else
					$detailstring .= '';
			}

			if($detailstring!='')
				$beschreibung = '<button class="btn btn-md btn-info" data-toggle="popover" title="'.$p->t('bewerbung/details').'" data-trigger="focus" data-content="'.$detailstring.'">Details</button>';
			else
				$beschreibung = '';

			$dokument = new dokument();
			$style = '';
			if($dok->pflicht==true)
				$style = 'danger';
			if(count($akte->result)>0)
			{
				$akte_id = isset($akte->result[0]->akte_id)?$akte->result[0]->akte_id:'';

				// check ob status "wird nachgereicht"
				if($akte->result[0]->nachgereicht == true)
				{
					// wird nachgereicht
					$style = '';
					$status = '<span class="glyphicon glyphicon-hourglass" aria-hidden="true" title="'.$p->t('bewerbung/dokumentWirdNachgereicht').'"></span>';
					$nachgereicht_help = 'checked';
					$div = "<form method='POST' action='".$_SERVER['PHP_SELF']."?active=dokumente'><span id='nachgereicht_".$dok->dokument_kurzbz."' style='display:true;'>".$akte->result[0]->anmerkung."</span></form>";
					$aktion = '	<button type="button" title="'.$p->t('global/löschen').'" class="btn btn-default" onclick="location.href=\''.$_SERVER['PHP_SELF'].'?method=delete&akte_id='.$akte_id.'&active=dokumente\'; return false;">
  									<span class="glyphicon glyphicon-remove" aria-hidden="true" title="'.$p->t('global/löschen').'"></span>
								</button>';
				}
				else
				{
					if($dokument->akzeptiert($akte->result[0]->dokument_kurzbz,$person->person_id))
					{
						// Dokument wurde bereits überprüft
						$status = '<span class="glyphicon glyphicon-ok" aria-hidden="true" title="'.$p->t('bewerbung/abgegeben').'"></span>';
						$nachgereicht_help = '';
						$div = "<form method='POST' action='".$_SERVER['PHP_SELF']."&active=dokumente'>
									<span id='nachgereicht_".$dok->dokument_kurzbz."' style='display:none;'>wird nachgereicht:<input type='checkbox' name='check_nachgereicht' ".$nachgereicht_help.">
										<input type='text' size='15' maxlength='128' name='txt_anmerkung'>
										<input type='submit' value='OK' name='submit_nachgereicht' class='btn btn-default'>
									</span><input type='hidden' name='dok_kurzbz' value='".$dok->dokument_kurzbz."'>
								<input type='hidden' name='akte_id' value='".$akte_id."'></form>";
						$aktion = '	<button type="button" title="'.$p->t('bewerbung/dokumentHerunterladen').'" class="btn btn-default" href="'.APP_ROOT.'cms/dms.php?id='.$akte->result[0]->dms_id.'"  onclick="FensterOeffnen(\''.APP_ROOT.'cms/dms.php?id='.$akte->result[0]->dms_id.'&akte_id='.$akte_id.'\'); return false;">
  										<span class="glyphicon glyphicon glyphicon-download-alt" aria-hidden="true" title="'.$p->t('bewerbung/dokumentHerunterladen').'"></span>
									</button>';
					}
					else
					{
						// Dokument hochgeladen ohne überprüfung der Assistenz*/
						$style = '';
						$status = '<span class="glyphicon glyphicon-eye-open" aria-hidden="true" title="'.$p->t('bewerbung/dokumentNichtUeberprueft').'"></span>';
						$nachgereicht_help = '';
						$div = "<form method='POST' action='".$_SERVER['PHP_SELF']."&active=dokumente'>
									<span id='nachgereicht_".$dok->dokument_kurzbz."' style='display:none;'>wird nachgereicht:<input type='checkbox' name='check_nachgereicht' ".$nachgereicht_help.">
										<input type='text' size='15' maxlength='128' name='txt_anmerkung'>
										<input type='submit' value='OK' name='submit_nachgereicht' class='btn btn-default'>
									</span>
									<input type='hidden' name='dok_kurzbz' value='".$dok->dokument_kurzbz."'><input type='hidden' name='akte_id' value='".$akte_id."'>
  								</form>";
						$aktion = '	<button type="button" title="'.$p->t('global/löschen').'" class="btn btn-default" onclick="location.href=\''.$_SERVER['PHP_SELF'].'?method=delete&akte_id='.$akte_id.'&active=dokumente\'; return false;">
  										<span class="glyphicon glyphicon-remove" aria-hidden="true" title="'.$p->t('global/löschen').'"></span>
									</button>';
						$aktion .= '	<button type="button" title="'.$p->t('bewerbung/dokumentHerunterladen').'" class="btn btn-default" href="'.APP_ROOT.'cms/dms.php?id='.$akte->result[0]->dms_id.'"  onclick="FensterOeffnen(\''.APP_ROOT.'cms/dms.php?id='.$akte->result[0]->dms_id.'&akte_id='.$akte_id.'\'); return false;">
  										<span class="glyphicon glyphicon glyphicon-download-alt" aria-hidden="true" title="'.$p->t('bewerbung/dokumentHerunterladen').'"></span>
									</button>';

					}
				}
			}
			//Fuer FHTW deaktiviert, damit Bewerber auch im Akzeptiert-Status Dokumente hochladen koennen, wenn noch keines hochgeladen war
			//Wenn kein Dokument hochgeladen ist und trotzdem akzeptiert wurde
			elseif(CAMPUS_NAME!='FH Technikum Wien' && $dokument->akzeptiert($dok->dokument_kurzbz,$person->person_id,$ben_kz))
			{
				$style = '';
				$status = '<span class="glyphicon glyphicon-ok" aria-hidden="true" title="'.$p->t('bewerbung/abgegeben').'"></span>';
				$nachgereicht_help = '';
				$div = "<form method='POST' action='".$_SERVER['PHP_SELF']."&active=dokumente'>
							<span id='nachgereicht_".$dok->dokument_kurzbz."' style='display:none;'>wird nachgereicht:
								<input type='checkbox' name='check_nachgereicht' ".$nachgereicht_help.">
								<input type='text' size='15' maxlength='128' name='txt_anmerkung'>
								<input type='submit' value='OK' name='submit_nachgereicht' class='btn btn-default'>
							</span><input type='hidden' name='dok_kurzbz' value='".$dok->dokument_kurzbz."'>
							<input type='hidden' name='akte_id' value='".$akte_id."'>
						</form>";
				$aktion = '';
			}
			else
			{
				// Dokument fehlt noch
				$status = ' - ';
				$aktion = '	<button title="'.$p->t('bewerbung/upload').'" type="button" class="btn btn-default" href="dms_akteupload.php?person_id='.$person_id.'&dokumenttyp='.$dok->dokument_kurzbz.'" onclick="FensterOeffnen(\'dms_akteupload.php?person_id='.$person_id.'&dokumenttyp='.$dok->dokument_kurzbz.'\'); return false;">
  								<span class="glyphicon glyphicon-upload" aria-hidden="true" title="'.$p->t('bewerbung/upload').'"></span>
							</button>';

				if(!defined('BEWERBERTOOL_DOKUMENTE_NACHREICHEN') || BEWERBERTOOL_DOKUMENTE_NACHREICHEN==true)
				{
					$aktion .='
							<button title="'.$p->t('bewerbung/dokumentWirdNachgereicht').'" type="button" class="btn btn-default" onclick="toggleDiv(\'nachgereicht_'.$dok->dokument_kurzbz.'\');return false;">
  								<span class="glyphicon glyphicon-hourglass" aria-hidden="true" title="'.$p->t('bewerbung/dokumentWirdNachgereicht').'"></span>
							</button>';
				}
				$div = "<form method='POST' action='".$_SERVER['PHP_SELF']."?active=dokumente'>
							<span id='nachgereicht_".$dok->dokument_kurzbz."' style='display:none;'>".$p->t('global/anmerkung').":
								<input type='checkbox' name='check_nachgereicht' checked=\"checked\" style='display:none'>
								<input id='anmerkung_".$dok->dokument_kurzbz."' type='text' size='15' maxlength='128' name='txt_anmerkung' onInput='zeichenCountdown(\"anmerkung_".$dok->dokument_kurzbz."\",128)'>
								<span style='color: grey; display: inline-block; width: 30px;' id='countdown_anmerkung_".$dok->dokument_kurzbz."'>128</span>
								<input type='submit' value='OK' name='submit_nachgereicht' class='btn btn-default'>
							</span>
							<input type='hidden' name='dok_kurzbz' value='".$dok->dokument_kurzbz."'>
						</form>";

			}

			 ?>

			<tr>
				<td style="vertical-align: middle" class="<?php echo $style ?>">
                    <?php
					echo $dok->bezeichnung_mehrsprachig[getSprache()];
                    ?>

                    <?php if($dok->pflicht): ?>
                        <span>*</span>
                    <?php endif; ?>
                </td>

                <td style="vertical-align: middle" class="<?php echo $style ?>"><?php echo $beschreibung ?></td>
				<td style="vertical-align: middle" class="<?php echo $style ?>"><?php echo $status ?></td>
				<td style="vertical-align: middle" nowrap class="<?php echo $style ?>"><?php echo $aktion ?></td>
				<td style="vertical-align: middle" class="<?php echo $style ?>"><?php echo $div ?></td>
				<td style="vertical-align: middle" class="<?php echo $style ?>"><?php echo $ben ?></td>
			</tr>
		<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<br>
	<h4><?php echo $p->t('bewerbung/legende'); ?></h4>
	<table class="table">
		<tr>
			<td class="danger">
				<span>&nbsp;*</span>
			</td>
			<td><?php echo $p->t('bewerbung/dokumentErforderlich'); ?></td>
		</tr>
		<tr>
			<td>
				<span class="glyphicon glyphicon-upload" aria-hidden="true" title="<?php echo $p->t('bewerbung/dokumentOffen'); ?>"></span>
			</td>
			<td><?php echo $p->t('bewerbung/dokumentOffen'); ?></td>
		</tr>
		<tr>
			<td>
				<span class="glyphicon glyphicon glyphicon-download-alt" aria-hidden="true" title="<?php echo $p->t('bewerbung/dokumentHerunterladen'); ?>"></span>
			</td>
			<td><?php echo $p->t('bewerbung/dokumentHerunterladen'); ?></td>
		</tr>

		<tr>
			<td>
				<span class="glyphicon glyphicon-eye-open" aria-hidden="true" title="<?php echo $p->t('bewerbung/dokumentNichtUeberprueft'); ?>"></span>
			</td>
			<td><?php echo $p->t('bewerbung/dokumentNichtUeberprueft'); ?></td>
		</tr>
		<?php
		if(!defined('BEWERBERTOOL_DOKUMENTE_NACHREICHEN') || BEWERBERTOOL_DOKUMENTE_NACHREICHEN==true)
		{
			echo '
			<tr>
				<td>
					<span class="glyphicon glyphicon-hourglass" aria-hidden="true" title="'.$p->t('bewerbung/dokumentWirdNachgereicht').'"></span>
				</td>
				<td>'.$p->t('bewerbung/dokumentWirdNachgereicht').'</td>
			</tr>';
		}
		?>
		<tr>
			<td>
				<span class="glyphicon glyphicon-ok" aria-hidden="true" title="<?php echo $p->t('bewerbung/dokumentWurdeUeberprueft'); ?>"></span>
			</td>
			<td><?php echo $p->t('bewerbung/dokumentWurdeUeberprueft'); ?></td>
		</tr>
		<tr>
			<td>
				<span class="glyphicon glyphicon-remove" aria-hidden="true" title="<?php echo $p->t('global/löschen'); ?>"></span>
			</td>
			<td><?php echo $p->t('global/löschen'); ?></td>
		</tr>
	</table>
	<button class="btn-nav btn btn-default" type="button" data-jump-tab="<?php echo $tabs[array_search('dokumente', $tabs)-1] ?>">
		<?php echo $p->t('global/zurueck') ?>
	</button>
	<button class="btn-nav btn btn-default" type="button" data-jump-tab="<?php echo $tabs[array_search('dokumente', $tabs)+1] ?>">
		<?php echo $p->t('bewerbung/weiter'); ?>
	</button>
	<br><?php //echo $message @todo: Braucht man das??><br/><br/>
</div>
