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

if(!isset($person_id))
{
	die($p->t('bewerbung/ungueltigerZugriff'));
}

echo '<div role="tabpanel" class="tab-pane" id="sicherheit">
	<h2>'.$p->t('bewerbung/menuSicherheit').'</h2>';

echo '<p>'.$p->t('bewerbung/erklaerungSicherheit').'</p>';

if($save_error_zugangscode===false)
{
	echo '	<div class="alert alert-success" id="success-alert_abschicken">
				<button type="button" class="close" data-dismiss="alert">x</button>
					<strong>'.$message.'</strong>
				</div>';
}
elseif($save_error_zugangscode===true)
{
	echo '	<div class="alert alert-danger" id="danger-alert">
			<button type="button" class="close" data-dismiss="alert">x</button>
				<strong>'.$p->t('global/fehleraufgetreten').' </strong>'.$message.'
			</div>';
}

echo '	<form method="POST" action="'.$_SERVER['PHP_SELF'].'?active=sicherheit">
		<button class="btn btn-primary" type="submit" name="btn_new_accesscode">
			'.$p->t('bewerbung/buttonNeuerZugangscode').'
		</button>
	</form><br>';


echo '<br><br>
	<button class="btn-nav btn btn-default" type="button" data-jump-tab="'.$tabs[0].'">
		'.$p->t('bewerbung/menuUebersichtBewerbungAbschicken').'
	</button>
</div>';
?>
