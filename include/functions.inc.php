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
			return false;
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
			return false;
		}

		return true;
	}
}
/**
 * Prueft, ob fuer die uebergebene Mailadresse und studiensemester, schon eine Person mit PreStudentstatus im System ist und laedt ggf. den entsprechenden Personendatensatz
 * @param string $mailadresse Zu pruefende E-Mail-Adresse.
 * @param string $studiensemester_kurzbz. Studiensemester fuer die Bewerbung
 * @return person_id und zugangscode; Ffalse im Fehlerfall
 */
function check_load_bewerbungen($mailadresse,$studiensemester_kurzbz)
{
	$mailadresse = trim($mailadresse);
	$db = new basis_db();
	
	$qry = "SELECT DISTINCT tbl_person.person_id,tbl_person.zugangscode,tbl_person.insertamum
				FROM public.tbl_kontakt 
					JOIN public.tbl_person USING (person_id) 
					JOIN public.tbl_prestudent USING (person_id) 
					JOIN public.tbl_prestudentstatus USING (prestudent_id) 
				WHERE kontakttyp='email' 
				AND kontakt=".$db->db_add_param($mailadresse, FHC_STRING)." 
				AND studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz, FHC_STRING)." 
				ORDER BY tbl_person.insertamum DESC 
				LIMIT 1;";

	if($db->db_query($qry))
	{
		if($row = $db->db_fetch_object())
		{		
			$obj = new stdClass();
			
			$obj->person_id = $row->person_id;
			$obj->zugangscode = $row->zugangscode;
			
			return $obj;
		}
		else
		{
			$db->errormsg = 'Datensatz wurde nicht gefunden';
			return false;
		}
	}
	else
	{
		$db->errormsg = 'Fehler beim Laden der Daten';
		return false;
	}
}
?>
