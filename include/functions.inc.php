<?php
/* Copyright (C) 2015 fhcomplete.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Andreas Oesterreicher <oesi@technikum-wien.at>
 */

// Fuegt einen Studiengang zu einem Bewerber hinzu
function BewerbungPersonAddStudiengang($studiengang_kz, $anmerkung, $person, $studiensemester_kurzbz)
{
	$prestudent = new prestudent();

	if(!$prestudent->exists($person->person_id, $studiengang_kz))
	{
		$prestudent->studiengang_kz=$studiengang_kz;
		$prestudent->person_id = $person->person_id;
		$prestudent->aufmerksamdurch_kurzbz = 'k.A.';
		$prestudent->insertamum = date('Y-m-d H:i:s');
		$prestudent->updateamum = date('Y-m-d H:i:s');
		$prestudent->reihungstestangetreten = false;
		$prestudent->new = true;

		if(!$prestudent->save())
		{
			return 'Fehler beim Anlegen des Prestudenten';
		}

		// Interessenten Status anlegen
		$prestudent_status = new prestudent();
		$prestudent_status->load($prestudent->prestudent_id);
		$prestudent_status->status_kurzbz = 'Interessent';
		$prestudent_status->studiensemester_kurzbz = $studiensemester_kurzbz;
		$prestudent_status->ausbildungssemester = '1';
		$prestudent_status->datum = date("Y-m-d H:i:s");
		$prestudent_status->insertamum = date("Y-m-d H:i:s");
		$prestudent_status->insertvon = '';
		$prestudent_status->updateamum = date("Y-m-d H:i:s");
		$prestudent_status->updatevon = '';
		$prestudent_status->new = true;
		$prestudent_status->anmerkung_status = $anmerkung;

		if(!$prestudent_status->save_rolle())
		{
			return 'Fehler beim Anlegen der Rolle';
		}

		return true;
	}
}
?>
