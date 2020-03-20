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

<div role="tabpanel" class="tab-pane" id="zahlungen">
	<?php
	$datum_obj = new datum();
	$studiengang = new studiengang();
	$studiengang->getAll(null, null);

	$stg_arr = array();
	foreach ($studiengang->result as $row)
	{
		$typ = new studiengang();
		$typ->getStudiengangTyp($row->typ);
		$stg_arr[$row->studiengang_kz]['kuerzel'] = $row->kuerzel;
		$stg_arr[$row->studiengang_kz]['typ'] = $typ->bezeichnung;
		$stg_arr[$row->studiengang_kz]['German'] = $row->bezeichnung_arr['German'];
		$stg_arr[$row->studiengang_kz]['English'] = $row->bezeichnung_arr['English'];
	}

	echo '<h2>'.$p->t('tools/zahlungen').'</h2>';

	$konto = new konto();
	$konto->getBuchungstyp();
	$buchungstyp = array();

	foreach ($konto->result as $row)
		$buchungstyp[$row->buchungstyp_kurzbz]=$row->beschreibung;

	$konto = new konto();
	$konto->getBuchungen($person_id);
	if(count($konto->result) > 0)
	{
		echo '<div class="panel-group" id="accordionZahlungen">';
		foreach ($konto->result as $row)
		{
			$betrag = $row['parent']->betrag;

			if(isset($row['childs']))
			{
				foreach ($row['childs'] as $row_child)
				{
					$betrag += $row_child->betrag;
				}
			}

			if($betrag < 0)
			{
				$class = 'danger';
			}
			elseif($betrag >= 0)
			{
				$class = 'success';
			}
			else
			{
				$class = '';
			}
			echo '
			
			<div class="panel panel-'.$class.'">
				<div class="panel-heading" data-toggle="collapse" data-parent="#accordionZahlungen" href="#zahlung'.$row['parent']->buchungsnr.'">
					<h4 class="panel-title">
					<div class="row">
						<div class="col-sm-6">
							€'.($betrag < 0? '-' : '').sprintf('%.2f',abs($row['parent']->betrag)).' 
							'.$buchungstyp[$row['parent']->buchungstyp_kurzbz].' - 
							'.$stg_arr[$row['parent']->studiengang_kz]['German'].' - 
							'.$row['parent']->studiensemester_kurzbz.'</div>
					</div>
					<div class="row details-arrow">
						<div class="col-xs-12 text-center">
							<span class="glyphicon glyphicon-chevron-down text-muted"></span>
							<span class="text-muted small">'.$p->t('bewerbung/zahlungsdetails').'</span>
						</div>
					</div>
					</h4>
				</div>
				<div id="zahlung'.$row['parent']->buchungsnr.'" class="panel-collapse collapse">
			        <div class="panel-body">
						<div class="row">
							<div class="col-xs-12">
								<form class="form-horizontal">
									<div class="form-group">
										<label for="" class="col-sm-3 col-md-5 text-right">'.$p->t('tools/zahlungstyp').'</label>
										<div class="col-sm-9 col-md-7">'.$buchungstyp[$row['parent']->buchungstyp_kurzbz].'</div>
									</div>
									<div class="form-group">
										<label for="" class="col-sm-3 col-md-5 text-right">'.$p->t('bewerbung/offenerBetrag').'</label>
										<div class="col-sm-9 col-md-7">€'.$betrag.'</div>
									</div>
									<div class="form-group">
										<label for="" class="col-sm-3 col-md-5 text-right">'.$p->t('buchungen/buchungsdatum').'</label>
										<div class="col-sm-9 col-md-7">'.date('d.m.Y',$datum_obj->mktime_fromdate($row['parent']->buchungsdatum)).'</div>
									</div>
									<div class="form-group">
										<label for="" class="col-sm-3 col-md-5 text-right">'.$p->t('global/studiengang').'</label>
										<div class="col-sm-9 col-md-7">'.$stg_arr[$row['parent']->studiengang_kz]['typ'].' '.$stg_arr[$row['parent']->studiengang_kz]['German'].'</div>
									</div>
									<div class="form-group">
										<label for="" class="col-sm-3 col-md-5 text-right">'.$p->t('global/studiensemester').'</label>
										<div class="col-sm-9 col-md-7">'.$row['parent']->studiensemester_kurzbz.'
										</div>
									</div>
									<div class="form-group">
										<label for="" class="col-sm-3 col-md-5 text-right">'.$p->t('tools/buchungstext').'</label>
										<div class="col-sm-9 col-md-7">'.($row['parent']->buchungstext != '' ? $row['parent']->buchungstext : '-').'</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>';
		}
		echo '</div>';
	}
	else
	{
		echo '<p>'.$p->t('tools/keineZahlungenVorhanden').'</p>';
	}
	?>

	<?php if(defined('BEWERBERTOOL_PAYMENT_ANZEIGEN') && BEWERBERTOOL_PAYMENT_ANZEIGEN==true && $status_zahlungen==false): ?>
		<p>
			<?php echo $p->t('bewerbung/paymentInfoText') ?>
		</p>
		<p>
			<a href="../../payment/cis/mpay24/pay.php" target="_self">Zur Onlinebezahlung</a>
		</p>

	<?php endif;?>
	<button class="btn-nav btn btn-default" type="button" data-jump-tab="<?php echo $tabs[array_search('zahlungen', $tabs)-1] ?>">
		<?php echo $p->t('global/zurueck') ?>
	</button>
	<button class="btn-nav btn btn-default" type="button" data-jump-tab="<?php echo $tabs[array_search('zahlungen', $tabs)+1] ?>">
		<?php echo $p->t('bewerbung/weiter'); ?>
	</button><br/><br/>
</div>
