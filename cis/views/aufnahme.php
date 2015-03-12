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
	die('Ungültiger Zugriff');
}
?>

<div role="tabpanel" class="tab-pane" id="aufnahme">
	<h2>Reihungstest</h2>
	<br>
	<p>Sie können sich für folgende Reihungstest anmelden: </p>
	<?php

	$prestudent = new prestudent();
	if(!$prestudent->getPrestudenten($person_id))
		die('Konnte Prestudenten nicht laden');

	foreach($prestudent->result as $row)
	{
		$reihungstest = new reihungstest();
		if(!$reihungstest->getStgZukuenftige($row->studiengang_kz))
			echo "Fehler aufgetreten";

		$stg = new studiengang();
		$stg->load($row->studiengang_kz); ?>
		<h3>Studiengang <?php echo $stg->bezeichnung ?></h3>

		<div class="table-responsive">
			<table class="reihungstest table">
				<tr>
					<th>angemeldet / Plätze</th>
					<th>Datum</th>
					<th>Uhrzeit</th>
					<th>Ort</th>
					<th title="<?php echo $row->studiengang_kz ?>">Studiengang</th>
					<th>&nbsp;</th>
				</tr>
			<?php
			foreach($reihungstest->result as $rt)
			{
				$teilnehmer_anzahl = $reihungstest->getTeilnehmerAnzahl($rt->reihungstest_id);
				$spalte1 = $rt->max_teilnehmer ? $teilnehmer_anzahl . '/' . $rt->max_teilnehmer : '';

				// bereits angenommen
				if($row->reihungstest_id == $rt->reihungstest_id)
				{
					$rt_help = true; ?>
					<tr style='background-color:lightgrey;'>
						<td><?php echo $spalte1 ?></td>
						<td><?php echo $rt->datum ?></td>
						<td><?php echo $rt->uhrzeit ?></td>
						<td><?php echo $rt->ort_kurzbz ?></td>
						<td><?php echo $stg->bezeichnung ?></td>
						<td>
							<input type='button' name='btn_stg'
								value='Stornieren'
								onclick='location.href="<?php echo $_SERVER['PHP_SELF'] ?>?active=aufnahme&rt_id=<?php echo $rt->reihungstest_id ?>&pre=<?php echo $row->prestudent_id ?>&delete"'>
						</td>
					</tr>
					<?php
				}
				else
				{
					?>
					<tr>
						<td><?php echo $spalte1 ?></td>
						<td><?php echo $rt->datum ?></td>
						<td><?php echo $rt->uhrzeit ?></td>
						<td><?php echo $rt->ort_kurzbz ?></td>
						<td><?php echo $stg->bezeichnung ?></td>
						<td>
							<input type='button' name='btn_stg'
								<?php echo isset($rt->max_teilnehmer) && $teilnehmer_anzahl >= $rt->max_teilnehmer ? 'disabled' : '' ?>
								value='Anmelden'
								onclick='location.href="<?php echo $_SERVER['PHP_SELF'] ?>?active=aufnahme&rt_id=<?php echo $rt->reihungstest_id ?>&pre=<?php echo $row->prestudent_id ?>"'>
						</td>
					</tr>
					<?php
				}
			}
			?>
			</table>
		</div><br>
		<?php
	}

	?>
	<button class="btn-nav btn btn-default" type="button" data-jump-tab="zahlungen">
		Zurück
	</button>
	<button class="btn-nav btn btn-default" type="button" data-jump-tab="abschicken">
		Weiter
	</button>
</div>
