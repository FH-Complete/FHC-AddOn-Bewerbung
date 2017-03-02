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
		$stg_arr[$row->studiengang_kz]=$row->kuerzel;

	echo '<h2>'.$p->t('tools/zahlungen').' - '.$person->vorname.' '.$person->nachname.'</h2>';

	$konto = new konto();
	$konto->getBuchungstyp();
	$buchungstyp = array();

	foreach ($konto->result as $row)
		$buchungstyp[$row->buchungstyp_kurzbz]=$row->beschreibung;

	$konto = new konto();
	$konto->getBuchungen($person_id);
	if(count($konto->result)>0): ?>
		<div class="table-responsive">
			<table class="table table-striped">
				<thead>
					<tr>
						<th><?php echo $p->t('global/datum') ?></th>
						<th><?php echo $p->t('tools/zahlungstyp') ?></th>
						<th><?php echo $p->t('lvplan/stg') ?></th>
						<th><?php echo $p->t('global/studiensemester') ?></th>
						<th><?php echo $p->t('tools/buchungstext') ?></th>
						<th><?php echo $p->t('tools/betrag') ?></th>
						<th><?php echo $p->t('bewerbung/zahlungsinformation') ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($konto->result as $row):
						$betrag = $row['parent']->betrag;

						if(isset($row['childs']))
						{
							foreach ($row['childs'] as $row_child)
							{
								$betrag += $row_child->betrag;
							}
						}

						if($betrag<0)
						{
							$class = 'danger';
						}
						elseif($betrag>0)
						{
							$class = 'success';
						}
						else
						{
							$class = '';
						}
						?>
						<tr class="<?php echo $class ?>">
							<td><?php echo date('d.m.Y',$datum_obj->mktime_fromdate($row['parent']->buchungsdatum)) ?></td>
							<td><?php echo $buchungstyp[$row['parent']->buchungstyp_kurzbz] ?></td>
							<td><?php echo $stg_arr[$row['parent']->studiengang_kz] ?></td>
							<td><?php echo $row['parent']->studiensemester_kurzbz ?></td>

							<td nowrap><?php echo $row['parent']->buchungstext ?></td>
							<td align="right" nowrap><?php echo ($betrag<0?'-':($betrag>0?'+':'')).sprintf('%.2f',abs($row['parent']->betrag)) ?> €</td>
							<td align="center">
							<?php if($betrag==0 && $row['parent']->betrag<0): ?>
								<?php echo $p->t('bewerbung/bezahlt') ?>
							<?php else: ?>
									<a onclick="window.open('zahlungen_details.php?buchungsnr=<?php echo $row['parent']->buchungsnr ?>',
												'<?php echo $p->t('bewerbung/zahlungsdetails') ?>',
												'height=320,width=550,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=no,toolbar=no,location=no,menubar=no,dependent=yes');
										return false;" href="#">
											<?php echo $p->t('tools/offen') ?>
									</a>
								</td>
							<?php endif; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php else: ?>
		<p><?php echo $p->t('tools/keineZahlungenVorhanden') ?></p>
	<?php endif; ?>
	<?php if(defined('BEWERBERTOOL_PAYMENT_ANZEIGEN') && BEWERBERTOOL_PAYMENT_ANZEIGEN==true && $status_zahlungen==false): ?>
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
