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
	$dokumente_person = new dokument();
	$dokumente_person->getAllDokumenteForPerson($person_id, true); ?>

	<div class="table-responsive">
		<table class="table table-striped">
			<thead>
				<tr>
					<th><?php echo $p->t('bewerbung/dokumentName'); ?></th>
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

			if(count($akte->result)>0)
			{
				$akte_id = isset($akte->result[0]->akte_id)?$akte->result[0]->akte_id:'';

				// check ob status "wird nachgereicht"
				if($akte->result[0]->nachgereicht == true)
				{
					// wird nachgereicht
					$status = '<img title="'.$p->t('bewerbung/dokumentWirdNachgereicht').'" src="'.APP_ROOT.'skin/images/hourglass.png" width="20px">';
					$nachgereicht_help = 'checked';
					$div = "<form method='POST' action='".$_SERVER['PHP_SELF']."?active=dokumente'><span id='nachgereicht_".$dok->dokument_kurzbz."' style='display:true;'>".$akte->result[0]->anmerkung."</span></form>";
					$aktion = '<a href="'.$_SERVER['PHP_SELF'].'?method=delete&akte_id='.$akte_id.'&active=dokumente"><img title="'.$p->t('global/löschen').'" src="'.APP_ROOT.'skin/images/delete.png" width="20px"></a>';
				}
				else
				{
					
					$dokument = new dokument();
					
					if($dokument->akzeptiert($akte->result[0]->dokument_kurzbz,$person->person_id))
					{
						// Dokument wurde bereits überprüft
						$status = '<img title="'.$p->t('bewerbung/abgegeben').'" src="'.APP_ROOT.'skin/images/true_green.png" width="20px">';
						$nachgereicht_help = '';
						$div = "<form method='POST' action='".$_SERVER['PHP_SELF']."&active=dokumente'><span id='nachgereicht_".$dok->dokument_kurzbz."' style='display:none;'>wird nachgereicht:<input type='checkbox' name='check_nachgereicht' ".$nachgereicht_help."><input type='text' size='15' name='txt_anmerkung'><input type='submit' value='OK' name='submit_nachgereicht' class='btn btn-default'></span><input type='hidden' name='dok_kurzbz' value='".$dok->dokument_kurzbz."'><input type='hidden' name='akte_id' value='".$akte_id."'></form>";
						$aktion = '';
					}
					else
					{
						// Dokument hochgeladen ohne überprüfung der Assistenz*/
						$status = '<img title="'.$p->t('bewerbung/abgegeben').'" src="'.APP_ROOT.'skin/images/check_black.png" width="20px">';
						$nachgereicht_help = '';
						$div = "<form method='POST' action='".$_SERVER['PHP_SELF']."&active=dokumente'><span id='nachgereicht_".$dok->dokument_kurzbz."' style='display:none;'>wird nachgereicht:<input type='checkbox' name='check_nachgereicht' ".$nachgereicht_help."><input type='text' size='15' name='txt_anmerkung'><input type='submit' value='OK' name='submit_nachgereicht' class='btn btn-default'></span><input type='hidden' name='dok_kurzbz' value='".$dok->dokument_kurzbz."'><input type='hidden' name='akte_id' value='".$akte_id."'></form>";
						$aktion = '<a href="'.$_SERVER['PHP_SELF'].'?method=delete&akte_id='.$akte_id.'&active=dokumente"><img title="'.$p->t('global/löschen').'" src="'.APP_ROOT.'skin/images/delete.png" width="20px"></a>';

					}
				}
			}
			else
			{
				// Dokument fehlt noch
				$status = ' - ';
				$aktion = '<img src="'.APP_ROOT.'skin/images/delete.png" width="20px" title="'.$p->t('global/löschen').'"> <a href="dms_akteupload.php?person_id='.$person_id.'&dokumenttyp='.$dok->dokument_kurzbz.'" onclick="FensterOeffnen(this.href); return false;"><img src="'.APP_ROOT.'skin/images/upload.png" width="20px" title="'.$p->t('bewerbung/upload').'"></a><a href="#" onclick="toggleDiv(\'nachgereicht_'.$dok->dokument_kurzbz.'\');return false;"><img src="'.APP_ROOT.'skin/images/hourglass.png" width="20px" title="'.$p->t('bewerbung/dokumentWirdNachgereicht').'"></a>';
				$div = "<form method='POST' action='".$_SERVER['PHP_SELF']."?active=dokumente'><span id='nachgereicht_".$dok->dokument_kurzbz."' style='display:none;'>Anmerkung:<input type='checkbox' name='check_nachgereicht' checked=\"checked\" style='display:none'><input type='text' size='15' name='txt_anmerkung'><input type='submit' value='OK' name='submit_nachgereicht' class='btn btn-default'></span><input type='hidden' name='dok_kurzbz' value='".$dok->dokument_kurzbz."'></form>";

			}

			$ben_stg = new basis_db();
			$qry = "SELECT studiengang_kz FROM public.tbl_dokumentstudiengang
				JOIN public.tbl_prestudent using (studiengang_kz)
				JOIN public.tbl_dokument using (dokument_kurzbz)
				WHERE dokument_kurzbz = ".$ben_stg->db_add_param($dok->dokument_kurzbz)." and person_id =".$ben_stg->db_add_param($person_id, FHC_INTEGER);

			$ben = "";
			if($result = $ben_stg->db_query($qry))
			{
				while($row = $ben_stg->db_fetch_object($result))
				{
					if($ben!='')
						$ben.=', ';

					$stg = new studiengang();
					$stg->load($row->studiengang_kz);

					$ben .= $stg->bezeichnung;
				}
			} ?>

			<tr>
				<td>
                    <?php echo $dok->bezeichnung ?>
                    <?php if($dok->pflicht): ?>
                        <span class="text-danger glyphicon glyphicon-asterisk"></span>
                    <?php endif; ?>
                </td>
				<td><?php echo $status ?></td>
				<td nowrap><?php echo $aktion ?></td>
				<td><?php echo $div ?></td>
				<td><?php echo $ben ?></td>
			</tr>
		<?php endforeach; ?>
			</tbody>
		</table>
	</div>
			<br>
	<h2><?php echo $p->t('bewerbung/status'); ?></h2>
	<table class="table">
		<tr>
			<td>
				<span class="text-danger glyphicon glyphicon-asterisk"></span>
			</td>
			<td><?php echo $p->t('bewerbung/dokumentErforderlich'); ?></td>
		</tr>
		<tr>
			<td>
				<img title="<?php echo $p->t('bewerbung/dokumentOffen'); ?>" src="<?php echo APP_ROOT ?>skin/images/upload.png" width="20px">
			</td>
			<td><?php echo $p->t('bewerbung/dokumentOffen'); ?></td>
		</tr>
		<tr>
			<td>
				<img title="<?php echo $p->t('bewerbung/dokumentNichtUeberprueft'); ?>" src="<?php echo APP_ROOT ?>skin/images/check_black.png" width="20px">
			</td>
			<td><?php echo $p->t('bewerbung/dokumentNichtUeberprueft'); ?></td>
		</tr>
		<tr>
			<td>
				<img title="<?php echo $p->t('bewerbung/dokumentWirdNachgereicht'); ?>" src="<?php echo APP_ROOT ?>skin/images/hourglass.png" width="20px">
			</td>
			<td><?php echo $p->t('bewerbung/dokumentWirdNachgereicht'); ?></td>
		</tr>
		<tr>
			<td>
				<img title="<?php echo $p->t('bewerbung/dokumentWurdeUeberprueft'); ?>" src="<?php echo APP_ROOT ?>skin/images/true_green.png" width="20px">
			</td>
			<td><?php echo $p->t('bewerbung/dokumentWurdeUeberprueft'); ?></td>
		</tr>
	</table>
	<button class="btn-nav btn btn-default" type="button" data-jump-tab="kontakt">
		<?php echo $p->t('global/zurueck') ?>
	</button>
	<button class="btn-nav btn btn-default" type="button" data-jump-tab="zgv">
		<?php echo $p->t('bewerbung/weiter'); ?>
	</button>
	<br><?php echo $message ?>
</div>
