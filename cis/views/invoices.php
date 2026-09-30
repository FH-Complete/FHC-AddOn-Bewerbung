<?php

/**
 * Copyright (C) 2023 fhcomplete.org
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

// If no person id is provided then just die
if (!isset($person_id)) die($p->t('bewerbung/ungueltigerZugriff'));

echo '<div role="tabpanel" class="tab-pane" id="invoices">';

echo '<h2>'.$p->t('bewerbung/menuInvoices').'</h2>';

echo '<p>'.$p->t('bewerbung/erklaerungInvoices').'</p>';

echo '<div id="responsiveDiv" class="embed-responsive" style="height: 4242px;">
		<iframe id="invoicesIframe"
        		class="embed-responsive-item"
			src="../../../index.ci.php/extensions/FHC-Core-SAP/bewerbung/Invoices">
		</iframe>
	</div>';

echo '</div>';

?>

