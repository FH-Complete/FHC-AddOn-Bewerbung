<?php
/*
 * Copyright (C) 2021 fhcomplete.org
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 * Authors: Andreas Oesterreicher <oesi@technikum-wien.at>
 *			Manuela Thamer <manuela.thamer@technikum-wien.at>
 */
require_once ('../../../include/student.class.php');
require_once ('../../../include/studienplan.class.php');
require_once ('../../../include/studienordnung.class.php');
require_once ('../../../include/studiengang.class.php');
require_once ('../../../include/personlog.class.php');
require_once ('../../../config/global.config.inc.php');
require_once ('../../../include/dokument.class.php');
require_once ('../../../include/prestudent.class.php');

// Fuegt einen Studiengang zu einem Bewerber hinzu
function BewerbungPersonAddStudiengang($studiengang_kz, $anmerkung, $person, $studiensemester_kurzbz, $orgform_kurzbz, $sprache)
{
	// PreStudent_id des aktuellsten PreStudenten (hoechste ID) ermitteln und Interessentenstatus zu diesem hinzufuegen,
	// sonst nach irgendeiner prestudent_id suchen, um dessen ZGV uebernehmen zu koennen.
	$student = new student();
	$std = $student->load_person($person->person_id, $studiengang_kz);
	$prestudent_id = 0;
	$zgv_code = '';
	$zgvort = '';
	$zgvdatum = '';
	$zgvnation = '';
	$zgvmas_code = '';
	$zgvmaort = '';
	$zgvmadatum = '';
	$zgvmanation = '';

	// Erstellen eines Array mit allen Studiengängen
	$studiengaenge_obj = new studiengang();
	$studiengaenge_obj->getAll();
	$studiengaenge_arr = array();

	foreach ($studiengaenge_obj->result as $row)
	{
		$studiengaenge_arr[$row->studiengang_kz]['kurzbz'] = $row->kurzbz;
		$studiengaenge_arr[$row->studiengang_kz]['bezeichnung'] = $row->bezeichnung;
		$studiengaenge_arr[$row->studiengang_kz]['english'] = $row->english;
		$studiengaenge_arr[$row->studiengang_kz]['typ'] = $row->typ;
		$studiengaenge_arr[$row->studiengang_kz]['orgform_kurzbz'] = $row->orgform_kurzbz;
		$studiengaenge_arr[$row->studiengang_kz]['oe_kurzbz'] = $row->oe_kurzbz;
	}

	// Wenn BEWERBERTOOL_ALWAYS_CREATE_NEW_PRESTUDENT_FOR_APPLICATION nicht definiert oder false ist,
	// wird ein bestehender PreStudent gesucht und ein neuer Status an diesen angefügt
	// Ansonsten wird immer ein neuer PreStudent-Datensatz erzeugt
	if(!defined('BEWERBERTOOL_ALWAYS_CREATE_NEW_PRESTUDENT_FOR_APPLICATION') || BEWERBERTOOL_ALWAYS_CREATE_NEW_PRESTUDENT_FOR_APPLICATION == false)
	{
		$pre = new prestudent();
		$pre->getPrestudenten($person->person_id); // Alle Prestudenten der Person laden
		foreach ($pre->result as $row)
		{
			// Wenn Person schon Prestudent in dem Studiengang war, hoechste prestudent_id ermitteln (die nicht jene des Studenten-Datensatzes war) und bei diesem spaeter einen neuen Status hinzufuegen
			if ($row->studiengang_kz == $studiengang_kz && $row->prestudent_id > $prestudent_id && $row->prestudent_id != $student->prestudent_id)
				$prestudent_id = $row->prestudent_id;
		}
		// Wenn die Person noch kein Student in diesem Studiengang war, nach irgendeiner prestudent_id suchen, um dessen ZGV uebernehmen zu koennen
		if ($prestudent_id == 0 && isset($pre->result[0]))
		{
			if ($pre->result[0]->prestudent_id != '')
			{
				$prestudent_help = $pre->result[0]->prestudent_id;

				$prestudent_zgv = new prestudent();
				$prestudent_zgv->load($prestudent_help);

				$zgv_code = $prestudent_zgv->zgv_code;
				$zgvort = $prestudent_zgv->zgvort;
				$zgvdatum = $prestudent_zgv->zgvdatum;
				$zgvnation = $prestudent_zgv->zgvnation;
				$zgvmas_code = $prestudent_zgv->zgvmas_code;
				$zgvmaort = $prestudent_zgv->zgvmaort;
				$zgvmadatum = $prestudent_zgv->zgvmadatum;
				$zgvmanation = $prestudent_zgv->zgvmanation;
			}
		}
	}
	else
	{
		$prestudent_id = 0;
	}


	if ($prestudent_id == 0) // Wenn kein PreStudent-Datensatz gefunden wurde, neuen Prestudenten anlegen
	{
		if ($std) // Wenn Person schon Student war, ZGV-Daten von dort holen
		{
			$prestudent_zgv = new prestudent();
			$prestudent_zgv->load($student->prestudent_id);

			$zgv_code = $prestudent_zgv->zgv_code;
			$zgvort = $prestudent_zgv->zgvort;
			$zgvdatum = $prestudent_zgv->zgvdatum;
			$zgvnation = $prestudent_zgv->zgvnation;
			$zgvmas_code = $prestudent_zgv->zgvmas_code;
			$zgvmaort = $prestudent_zgv->zgvmaort;
			$zgvmadatum = $prestudent_zgv->zgvmadatum;
			$zgvmanation = $prestudent_zgv->zgvmanation;
		}
		// An der FHTW werden seit dem Infocenter keine bestehenden ZGVs für Bachelor übernommen
		if (CAMPUS_NAME == 'FH Technikum Wien' && $studiengaenge_arr[$studiengang_kz]['typ'] == 'b')
		{
			$zgv_code = '';
			$zgvort = '';
			$zgvdatum = '';
			$zgvnation = '';
			$zgvmas_code = '';
			$zgvmaort = '';
			$zgvmadatum = '';
			$zgvmanation = '';
		}
		$prestudent = new prestudent();

		$prestudent->studiengang_kz = $studiengang_kz;
		$prestudent->person_id = $person->person_id;
		$prestudent->zgv_code = $zgv_code;
		$prestudent->zgvort = $zgvort;
		$prestudent->zgvdatum = $zgvdatum;
		$prestudent->zgvnation = $zgvnation;
		$prestudent->zgvmas_code = $zgvmas_code;
		$prestudent->zgvmaort = $zgvmaort;
		$prestudent->zgvmadatum = $zgvmadatum;
		$prestudent->zgvmanation = $zgvmanation;
		$prestudent->aufmerksamdurch_kurzbz = 'k.A.';
		$prestudent->insertamum = date('Y-m-d H:i:s');
		$prestudent->insertvon = 'online';
		$prestudent->updateamum = date('Y-m-d H:i:s');
		$prestudent->updatevon = 'online';
		$prestudent->reihungstestangetreten = false;
		$prestudent->new = true;

		if (! $prestudent->save())
		{
			return $prestudent->errormsg;
		}

		$prestudent_id = $prestudent->prestudent_id;
	}

	// Richtigen Studienplan ermitteln
	$studienplan = new studienplan();
	$studienplan->getStudienplaeneFromSem($studiengang_kz, $studiensemester_kurzbz, '1', $orgform_kurzbz, $sprache);

	// Wenn kein passender Studienplan gefunden wird, wird er NULL gesetzt
	if (isset($studienplan->result[0]))
		$studienplan_id = $studienplan->result[0]->studienplan_id;
	else
		$studienplan_id = '';

	// Interessenten Status anlegen
	$prestudent_status = new prestudent();
	$prestudent_status->load($prestudent_id);
	$prestudent_status->status_kurzbz = 'Interessent';
	$prestudent_status->studiensemester_kurzbz = $studiensemester_kurzbz;
	$prestudent_status->ausbildungssemester = '1';
	$prestudent_status->datum = date("Y-m-d H:i:s");
	$prestudent_status->insertamum = date("Y-m-d H:i:s");
	$prestudent_status->insertvon = 'online';
	$prestudent_status->updateamum = date("Y-m-d H:i:s");
	$prestudent_status->updatevon = 'online';
	$prestudent_status->new = true;
	$prestudent_status->anmerkung_status = $anmerkung;
	$prestudent_status->orgform_kurzbz = $orgform_kurzbz;
	$prestudent_status->studienplan_id = $studienplan_id;

	if (! $prestudent_status->save_rolle())
	{
		return $prestudent_status->errormsg;
	}
	else
	{
		// Logeintrag schreiben
		$log = new personlog();
		$log->log($person->person_id,
			'Action',
			array('name'=>'New application','success'=>true,'message'=>'New application for '.$studiengaenge_arr[$studiengang_kz]['bezeichnung'].' ('.$orgform_kurzbz.') Studienplan '.$studienplan_id.' saved'),
			'bewerbung',
			'bewerbung',
			$studiengaenge_arr[$studiengang_kz]['oe_kurzbz'],
			'online');
	}
	return true;
}
// Fuegt eine Bewerbung für einen Studienplan hinzu
function BewerbungPersonAddStudienplan($studienplan_id, $person, $studiensemester_kurzbz)
{
	// Studienplan laden
	$studienplan = new studienplan();
	$studienplan->loadStudienplan($studienplan_id);
	$studienordnung = new studienordnung();
	$studienordnung->loadStudienordnung($studienplan->studienordnung_id);
	$studiengang_kz = $studienordnung->studiengang_kz;

	// Wenn es für das gewählte Studiensemester schon eine Bewerbung gibt, kann man sich nicht mehr dafür bewerben
	// Es wird true zurückgegeben aber kein PreStudent erstellt
	$existPrestudentstatus = new prestudent();
	if ($existPrestudentstatus->existsPrestudentstatus($person->person_id, $studiengang_kz, $studiensemester_kurzbz, null, $studienplan_id))
	{
		return true;
	}

	// PreStudent_id des aktuellsten PreStudenten (hoechste ID) ermitteln und Interessentenstatus zu diesem hinzufuegen,
	// sonst nach irgendeiner prestudent_id suchen, um dessen ZGV uebernehmen zu koennen.
	$student = new student();
	$std = $student->load_person($person->person_id, $studiengang_kz);
	$prestudent_id = 0;
	$zgv_code = '';
	$zgvort = '';
	$zgvdatum = '';
	$zgvnation = '';
	$zgvmas_code = '';
	$zgvmaort = '';
	$zgvmadatum = '';
	$zgvmanation = '';

	// Erstellen eines Array mit allen Studiengängen
	$studiengaenge_obj = new studiengang();
	$studiengaenge_obj->getAll();
	$studiengaenge_arr = array();

	foreach ($studiengaenge_obj->result as $row)
	{
		$studiengaenge_arr[$row->studiengang_kz]['kurzbz'] = $row->kurzbz;
		$studiengaenge_arr[$row->studiengang_kz]['bezeichnung'] = $row->bezeichnung;
		$studiengaenge_arr[$row->studiengang_kz]['english'] = $row->english;
		$studiengaenge_arr[$row->studiengang_kz]['typ'] = $row->typ;
		$studiengaenge_arr[$row->studiengang_kz]['orgform_kurzbz'] = $row->orgform_kurzbz;
		$studiengaenge_arr[$row->studiengang_kz]['oe_kurzbz'] = $row->oe_kurzbz;
	}

	// Höchsten PreStudenten ermitteln, um ggf ZGV übernehmen zu können
	$pre = new prestudent();
	$pre->getPrestudenten($person->person_id); // Alle Prestudenten der Person laden
	foreach ($pre->result as $row)
	{
		// Wenn Person schon Prestudent in dem Studiengang war, hoechste prestudent_id ermitteln (die nicht jene des Studenten-Datensatzes war) und bei diesem spaeter einen neuen Status hinzufuegen
		if ($row->studiengang_kz == $studiengang_kz && $row->prestudent_id > $prestudent_id && $row->prestudent_id != $student->prestudent_id)
			$prestudent_id = $row->prestudent_id;
	}
	// Wenn die Person noch kein Student in diesem Studiengang war,
	// nach der höchsten prestudent_id mit ZGV suchen, um dessen ZGV uebernehmen zu koennen
	if ($prestudent_id == 0)
	{
		$prestudent_id_for_zgv = 0;
		foreach ($pre->result as $row)
		{
			// Hochste PreStudent_id bei einem bachelor oder master suchen, bei der die ZGV gesetzt ist
			if ($row->prestudent_id > $prestudent_id_for_zgv
				&& $row->zgv_code != ''
				&& ($studiengaenge_arr[$row->studiengang_kz]['typ'] == 'b'
					|| $studiengaenge_arr[$row->studiengang_kz]['typ'] == 'm'))
			{
				$prestudent_id_for_zgv = $row->prestudent_id;
			}
		}
		if ($prestudent_id_for_zgv != 0)
		{
			$prestudent_help = $prestudent_id_for_zgv;

			$prestudent_zgv = new prestudent();
			$prestudent_zgv->load($prestudent_help);

			$zgv_code = $prestudent_zgv->zgv_code;
			$zgvort = $prestudent_zgv->zgvort;
			$zgvdatum = $prestudent_zgv->zgvdatum;
			$zgvnation = $prestudent_zgv->zgvnation;
			$zgvmas_code = $prestudent_zgv->zgvmas_code;
			$zgvmaort = $prestudent_zgv->zgvmaort;
			$zgvmadatum = $prestudent_zgv->zgvmadatum;
			$zgvmanation = $prestudent_zgv->zgvmanation;
		}
	}

	// Wenn BEWERBERTOOL_ALWAYS_CREATE_NEW_PRESTUDENT_FOR_APPLICATION definiert und true ist,
	// wird ein neuer PreStudent-Datensatz erzeugt
	// Ansonsten wird ein bestehender PreStudent gesucht und ein neuer Status an diesen angefügt
	if(defined('BEWERBERTOOL_ALWAYS_CREATE_NEW_PRESTUDENT_FOR_APPLICATION') && BEWERBERTOOL_ALWAYS_CREATE_NEW_PRESTUDENT_FOR_APPLICATION == true)
	{
		$prestudent_id = 0;
	}

	if ($prestudent_id == 0) // Wenn kein PreStudent-Datensatz gefunden wurde, neuen Prestudenten anlegen
	{
		if ($std) // Wenn Person schon Student in diesem Studiengang war, ZGV-Daten von dort holen
		{
			$prestudent_zgv = new prestudent();
			$prestudent_zgv->load($student->prestudent_id);

			$zgv_code = $prestudent_zgv->zgv_code;
			$zgvort = $prestudent_zgv->zgvort;
			$zgvdatum = $prestudent_zgv->zgvdatum;
			$zgvnation = $prestudent_zgv->zgvnation;
			$zgvmas_code = $prestudent_zgv->zgvmas_code;
			$zgvmaort = $prestudent_zgv->zgvmaort;
			$zgvmadatum = $prestudent_zgv->zgvmadatum;
			$zgvmanation = $prestudent_zgv->zgvmanation;
		}
		elseif($student->load_person($person->person_id)) // Sonst prüfen, ob Person schon irgendwo Student war und ZGV-Daten von dort holen
		{
			// Checken, ob es ein Bachelor oder Master war
			if ($studiengaenge_arr[$student->studiengang_kz]['typ'] == 'b'
				|| $studiengaenge_arr[$student->studiengang_kz]['typ'] == 'm')
			{
				$prestudent_zgv = new prestudent();
				$prestudent_zgv->load($student->prestudent_id);

				$zgv_code = $prestudent_zgv->zgv_code;
				$zgvort = $prestudent_zgv->zgvort;
				$zgvdatum = $prestudent_zgv->zgvdatum;
				$zgvnation = $prestudent_zgv->zgvnation;
				$zgvmas_code = $prestudent_zgv->zgvmas_code;
				$zgvmaort = $prestudent_zgv->zgvmaort;
				$zgvmadatum = $prestudent_zgv->zgvmadatum;
				$zgvmanation = $prestudent_zgv->zgvmanation;
			}
		}

		// Wenn immer noch keine ZGV-Daten gefunden wurden, alle anderen PreStudenten der Person durchsuchen
		if ($zgv_code == '')
		{
			$prestudent_id_for_zgv = 0;
			foreach ($pre->result as $row)
			{
				if ($row->prestudent_id > $prestudent_id_for_zgv
					&& ($row->zgv_code != '' || $row->zgvmas_code != '')
					&& ($studiengaenge_arr[$row->studiengang_kz]['typ'] == 'b'
						|| $studiengaenge_arr[$row->studiengang_kz]['typ'] == 'm')) // Höchste Prestudent ID in einem bachelor oder master mit ZGV suchen
				{
					$prestudent_id_for_zgv = $row->prestudent_id;
				}
			}
			if ($prestudent_id_for_zgv != 0)
			{
				$prestudent_help = $prestudent_id_for_zgv;

				$prestudent_zgv = new prestudent();
				$prestudent_zgv->load($prestudent_help);

				$zgv_code = $prestudent_zgv->zgv_code;
				$zgvort = $prestudent_zgv->zgvort;
				$zgvdatum = $prestudent_zgv->zgvdatum;
				$zgvnation = $prestudent_zgv->zgvnation;
				$zgvmas_code = $prestudent_zgv->zgvmas_code;
				$zgvmaort = $prestudent_zgv->zgvmaort;
				$zgvmadatum = $prestudent_zgv->zgvmadatum;
				$zgvmanation = $prestudent_zgv->zgvmanation;
			}
		}
		// An der FHTW werden seit dem Infocenter keine bestehenden ZGVs für Bachelor übernommen
		if (CAMPUS_NAME == 'FH Technikum Wien' && $studiengaenge_arr[$studiengang_kz]['typ'] == 'b')
		{
			$zgv_code = '';
			$zgvort = '';
			$zgvdatum = '';
			$zgvnation = '';
			$zgvmas_code = '';
			$zgvmaort = '';
			$zgvmadatum = '';
			$zgvmanation = '';
		}
		// Höchste Priorität in diesem Studiensemester laden und ggf. um 1 erhöhen
		$prestudent = new prestudent();
		$hoechstePrio = new prestudent();
		$hoechstePrio->getPriorisierungPersonStudiensemester($person->person_id, $studiensemester_kurzbz);

		if ($hoechstePrio->priorisierung == '')
			$hoechstePrio->priorisierung = 0;

		$prestudent->studiengang_kz = $studiengang_kz;
		$prestudent->person_id = $person->person_id;
		$prestudent->zgv_code = $zgv_code;
		$prestudent->zgvort = $zgvort;
		$prestudent->zgvdatum = $zgvdatum;
		$prestudent->zgvnation = $zgvnation;
		$prestudent->zgvmas_code = $zgvmas_code;
		$prestudent->zgvmaort = $zgvmaort;
		$prestudent->zgvmadatum = $zgvmadatum;
		$prestudent->zgvmanation = $zgvmanation;
		$prestudent->aufmerksamdurch_kurzbz = 'k.A.';
		$prestudent->insertamum = date('Y-m-d H:i:s');
		$prestudent->insertvon = 'online';
		$prestudent->updateamum = date('Y-m-d H:i:s');
		$prestudent->updatevon = 'online';
		$prestudent->reihungstestangetreten = false;
		$prestudent->priorisierung = $hoechstePrio->priorisierung+1;
		$prestudent->new = true;

		if (! $prestudent->save())
		{
			return $prestudent->errormsg;
		}

		$prestudent_id = $prestudent->prestudent_id;
	}

	// Interessenten Status anlegen
	$prestudent_status = new prestudent();
	$prestudent_status->load($prestudent_id);
	$prestudent_status->status_kurzbz = 'Interessent';
	$prestudent_status->studiensemester_kurzbz = $studiensemester_kurzbz;
	$prestudent_status->ausbildungssemester = '1';
	$prestudent_status->datum = date("Y-m-d H:i:s");
	$prestudent_status->insertamum = date("Y-m-d H:i:s");
	$prestudent_status->insertvon = 'online';
	$prestudent_status->updateamum = date("Y-m-d H:i:s");
	$prestudent_status->updatevon = 'online';
	$prestudent_status->new = true;
	$prestudent_status->anmerkung_status = '';
	$prestudent_status->orgform_kurzbz = $studienplan->orgform_kurzbz;
	$prestudent_status->studienplan_id = $studienplan_id;

	if (! $prestudent_status->save_rolle())
	{
		return $prestudent_status->errormsg;
	}
	else
	{
		// Logeintrag schreiben
		$log = new personlog();
		$log->log($person->person_id,
			'Action',
			array('name'=>'New application','success'=>true,'message'=>'New application for '.$studiengaenge_arr[$studiengang_kz]['bezeichnung'].' ('.$studienplan->orgform_kurzbz.') Studienplan '.$studienplan_id.' saved'),
			'bewerbung',
			'bewerbung',
			$studiengaenge_arr[$studiengang_kz]['oe_kurzbz'],
			'online');
	}
	return true;
}
/**
 * Prueft, ob fuer die uebergebene Mailadresse, schon eine Person im System ist und laedt ggf.
 * den entsprechenden Personendatensatz.
 * Optional kann eine studiensemester_kurzbz uebergeben werden. Dann wird ueber PreStudentstatus gejoined und nur ein bestimmtes Studiensemester ueberprueft.
 *
 * @param string $mailadresse Zu pruefende E-Mail-Adresse.
 * @param string $studiensemester_kurzbz. Optional. Studiensemester fuer welches eine Bewerbung vorliegt.
 * @return integer person_id und zugangscode; False im Fehlerfall
 */
function check_load_bewerbungen($mailadresse, $studiensemester_kurzbz = null)
{
	$mailadresse = strtolower(trim($mailadresse));
	$db = new basis_db();

	$qry = "SELECT DISTINCT tbl_person.person_id,tbl_person.zugangscode,tbl_person.insertamum
				FROM public.tbl_kontakt
					JOIN public.tbl_person USING (person_id)
					LEFT JOIN public.tbl_benutzer USING (person_id) ";
	if ($studiensemester_kurzbz != '')
		$qry .= "	JOIN public.tbl_prestudent USING (person_id)
							JOIN public.tbl_prestudentstatus USING (prestudent_id) ";
	$qry .= "
				WHERE kontakttyp='email'
				AND (	LOWER(kontakt)=" . $db->db_add_param($mailadresse, FHC_STRING) . "
						OR LOWER(alias||'@" . DOMAIN . "')=" . $db->db_add_param($mailadresse, FHC_STRING) . "
			 			OR LOWER(uid||'@" . DOMAIN . "')=" . $db->db_add_param($mailadresse, FHC_STRING) . ")";
	if ($studiensemester_kurzbz != '')
		$qry .= " AND studiensemester_kurzbz=" . $db->db_add_param($studiensemester_kurzbz, FHC_STRING);

	$qry .= " ORDER BY tbl_person.insertamum DESC LIMIT 1;";

	if ($db->db_query($qry))
	{
		if ($row = $db->db_fetch_object())
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

/**
 * Prueft, ob eine Person schon einen Bewerbung abgeschickt hat.
 * Notwendig um herauszufinden, ob die Eingabe der Stammdaten gesperrt werden soll.
 * Optional kann eine studiensemester_kurzbz uebergeben werden, ob speziell dafuer schon eine Bewerbung abgeschickt wurde.
 *
 * @param integer $person_id Zu pruefende Person.
 * @param string $studiensemester_kurzbz. Optional. Studiensemester fuer welches eine abgeschickte Bewerbung vorliegt.
 * @return true, wenn vorhanden, false im Fehlerfall
 */
function check_person_bewerbungabgeschickt($person_id, $studiensemester_kurzbz = null)
{
	$db = new basis_db();

	$qry = "SELECT *
			FROM PUBLIC.tbl_person
			JOIN PUBLIC.tbl_prestudent USING (person_id)
			JOIN PUBLIC.tbl_prestudentstatus USING (prestudent_id)
			WHERE person_id=" . $db->db_add_param($person_id, FHC_INTEGER) . "
				AND status_kurzbz = 'Interessent'
				AND bewerbung_abgeschicktamum IS NOT NULL";

	if ($studiensemester_kurzbz != '')
		$qry .= " AND studiensemester_kurzbz=" . $db->db_add_param($studiensemester_kurzbz, FHC_STRING);

	if ($result = $db->db_query($qry))
	{
		if ($db->db_num_rows($result) > 0)
			return true;
		else
			return false;
	}
	else
	{
		$db->errormsg = 'Fehler beim Laden der Daten';
		return false;
	}
}

/**
 * Prueft, ob der uebergebene Status der Person bestaetigt ist.
 * Optional kann eine studiensemester_kurzbz uebergeben werden, ob speziell dafuer schon eine Bestaetigung vorliegt.
 * Optional kann eine studiengang_kz uebergeben werden, ob speziell in diesem Studiengang der Status bestaetigt wurde
 *
 * @param integer $person_id Zu pruefende Person.
 * @param string $status_kurzbz Status_kurzbz, welche geprueft werden soll zB "Interessent".
 * @param string $studiensemester_kurzbz. Optional. Studiensemester fuer welches eine Bewerbung vorliegt.
 * @param integer $studiengang_kz. Optional. Kennzahl des Studiengangs in dem der Bestaetigt-Status geprueft werden soll
 * @return true, wenn vorhanden, false im Fehlerfall
 */
function check_person_statusbestaetigt($person_id, $status_kurzbz, $studiensemester_kurzbz = null, $studiengang_kz = null)
{
	$db = new basis_db();

	$qry = "SELECT *
			FROM PUBLIC.tbl_person
			JOIN PUBLIC.tbl_prestudent USING (person_id)
			JOIN PUBLIC.tbl_prestudentstatus USING (prestudent_id)
			WHERE person_id=" . $db->db_add_param($person_id, FHC_INTEGER) . "
				AND status_kurzbz = " . $db->db_add_param($status_kurzbz, FHC_STRING) . "
				AND bestaetigtam IS NOT NULL";

	if ($studiensemester_kurzbz != '')
		$qry .= " AND studiensemester_kurzbz=" . $db->db_add_param($studiensemester_kurzbz, FHC_STRING);

	if ($studiengang_kz != '')
		$qry .= " AND tbl_prestudent.studiengang_kz=" . $db->db_add_param($studiengang_kz, FHC_INTEGER);

	if ($result = $db->db_query($qry))
	{
		if ($db->db_num_rows($result) > 0)
			return true;
		else
			return false;
	}
	else
	{
		$db->errormsg = 'Fehler beim Laden der Daten';
		return false;
	}
}

/**
 * Holt die aktiven Studienplaene, bei denen das Attribut onlinebewerbung_studienplan TRUE ist.
 *
 * @param integer $studiengang_kz Optional. Studiengang_kz eines bestimmten Studiengangs.
 * @param array $studiensemester_kurzbz Optional. Array von Studiensemestern, in deren Gueltigkeit die Studienplaene liegen.
 * @param string $ausbildungssemester Optional. Kommaseparierter String mit Ausbildungssemestern, in deren Gueltigkeit die Studienplaene liegen.
 * @param string $orgform_kurzbz Optional. Orgform_kurzbz einer bestimmten Orgform.
 */
function alt_getStudienplaeneForOnlinebewerbung($studiengang_kz = null, $studiensemester_kurzbz = null, $ausbildungssemester = null, $orgform_kurzbz = null)
{
	$db = new basis_db();
	$qry = "SELECT DISTINCT
					tbl_studienplan.*,
					tbl_studienordnung.bezeichnung AS bezeichnung_studienordnung,
					tbl_studienordnung.ects,
					tbl_studienordnung.studiengangbezeichnung,
					tbl_studienordnung.studiengangbezeichnung_englisch,
					tbl_studienordnung.studiengangkurzbzlang,
					tbl_studienordnung.akadgrad_id
				FROM
					lehre.tbl_studienplan
					JOIN lehre.tbl_studienordnung USING(studienordnung_id)
					JOIN lehre.tbl_studienplan_semester USING(studienplan_id)
				WHERE
					tbl_studienplan.aktiv
				AND
					tbl_studienplan.onlinebewerbung_studienplan=TRUE ";

	if ($studiengang_kz != '')
	{
		$qry .= " AND tbl_studienordnung.studiengang_kz=" . $db->db_add_param($studiengang_kz, FHC_INTEGER);
	}
	if ($studiensemester_kurzbz != '')
	{
		$qry .= " AND tbl_studienplan_semester.studiensemester_kurzbz IN (" . $db->implode4SQL($studiensemester_kurzbz) . ")";
	}
	if ($ausbildungssemester != '')
	{
		$ausbildungssemester = explode(',', $ausbildungssemester);
		$qry .= " AND tbl_studienplan_semester.semester IN (" . $db->implode4SQL($ausbildungssemester) . ")";
	}
	if ($orgform_kurzbz != '')
	{
		$qry .= " AND orgform_kurzbz=" . $db->db_add_param($orgform_kurzbz);
	}

	if ($result = $db->db_query($qry))
	{
		$db->result = '';
		while ($row = $db->db_fetch_object($result))
		{
			$obj = new studienplan();

			$obj->studienplan_id = $row->studienplan_id;
			$obj->studienordnung_id = $row->studienordnung_id;
			$obj->orgform_kurzbz = $row->orgform_kurzbz;
			$obj->version = $row->version;
			$obj->bezeichnung = $row->bezeichnung;
			$obj->regelstudiendauer = $row->regelstudiendauer;
			$obj->sprache = $row->sprache;
			$obj->aktiv = $db->db_parse_bool($row->aktiv);
			$obj->semesterwochen = $row->semesterwochen;
			$obj->testtool_sprachwahl = $db->db_parse_bool($row->testtool_sprachwahl);
			$obj->updateamum = $row->updateamum;
			$obj->updatevon = $row->updatevon;
			$obj->insertamum = $row->insertamum;
			$obj->insertvon = $row->insertvon;
			$obj->bezeichnung_studienordnung = $row->bezeichnung_studienordnung;
			$obj->ects = $row->ects;
			$obj->studiengangbezeichnung = $row->studiengangbezeichnung;
			$obj->studiengangbezeichnung_englisch = $row->studiengangbezeichnung_englisch;
			$obj->studiengangkurzbzlang = $row->studiengangkurzbzlang;
			$obj->akadgrad_id = $row->akadgrad_id;

			$obj->new = true;

			$db->result[] = $obj;
		}
		return $db->result;
	}
	else
		return false;
}

/**
 * Holt die aktiven Studienplaene, bei denen das Attribut onlinebewerbung_studienplan TRUE ist.
 * Wenn keiner vorhanden ist, wird ein Studiengang ohne Studienplan geliefert.
 *
 * @param integer $studiengang_kz Optional. Studiengang_kz eines bestimmten Studiengangs.
 * @param array $studiensemester_kurzbz Optional. Array von Studiensemestern, in deren Gueltigkeit die Studienplaene liegen.
 * @param string $ausbildungssemester Optional. Kommaseparierter String mit Ausbildungssemestern, in deren Gueltigkeit die Studienplaene liegen.
 * @param string $orgform_kurzbz Optional. Orgform_kurzbz einer bestimmten Orgform.
 */
function getStudienplaeneForOnlinebewerbung($studiensemester_kurzbz = null, $ausbildungssemester = null, $orgform_kurzbz = null, $order = 'tbl_studiengang.typ, tbl_lgartcode.bezeichnung ASC, tbl_studiengang.bezeichnung')
{
	$db = new basis_db();
	$qry = "SELECT 	tbl_studienplan.*,
					tbl_studienordnung.bezeichnung AS bezeichnung_studienordnung,
					tbl_studienordnung.ects,
					tbl_studienordnung.studiengangbezeichnung,
					tbl_studienordnung.studiengangbezeichnung_englisch,
					tbl_studienordnung.studiengangkurzbzlang,
					tbl_studienordnung.akadgrad_id,
					tbl_studienordnung.studiengang_kz,
					tbl_studiengang.typ,
					tbl_studiengangstyp.bezeichnung AS typ_bezeichnung,
					tbl_studiengang.lgartcode,
					tbl_lgartcode.bezeichnung AS lehrgangsart
				FROM
					lehre.tbl_studienplan
					JOIN lehre.tbl_studienordnung USING(studienordnung_id)
					JOIN lehre.tbl_studienplan_semester USING(studienplan_id)
					JOIN public.tbl_studiengang ON (tbl_studienordnung.studiengang_kz = tbl_studiengang.studiengang_kz)
					LEFT JOIN bis.tbl_lgartcode USING (lgartcode)
					JOIN public.tbl_studiengangstyp USING (typ)
				WHERE
					tbl_studienplan.aktiv
				AND
					tbl_studienplan.onlinebewerbung_studienplan = TRUE
				AND
					tbl_studiengang.onlinebewerbung = TRUE
				AND
					tbl_studiengang.aktiv = TRUE ";

	if ($studiensemester_kurzbz != '')
	{
		$qry .= " AND tbl_studienplan_semester.studiensemester_kurzbz IN (" . $db->implode4SQL($studiensemester_kurzbz) . ")";
	}
	if ($ausbildungssemester != '')
	{
		$ausbildungssemester = explode(',', $ausbildungssemester);
		$qry .= " AND tbl_studienplan_semester.semester IN (" . $db->implode4SQL($ausbildungssemester) . ")";
	}
	if ($orgform_kurzbz != '')
	{
		$qry .= " AND orgform_kurzbz=" . $db->db_add_param($orgform_kurzbz);
	}

	$qry .= " ORDER BY ".$order;

//echo $qry;

	if ($result = $db->db_query($qry))
	{
		$db->result = '';
		while ($row = $db->db_fetch_object($result))
		{
			$obj = new studienplan();

			$obj->studienplan_id = $row->studienplan_id;
			$obj->studienordnung_id = $row->studienordnung_id;
			$obj->orgform_kurzbz = $row->orgform_kurzbz;
			$obj->version = $row->version;
			$obj->bezeichnung = $row->bezeichnung;
			$obj->regelstudiendauer = $row->regelstudiendauer;
			$obj->sprache = $row->sprache;
			$obj->aktiv = $db->db_parse_bool($row->aktiv);
			$obj->typ = $row->typ;
			$obj->typ_bezeichnung = $row->typ_bezeichnung;
			$obj->semesterwochen = $row->semesterwochen;
			$obj->testtool_sprachwahl = $db->db_parse_bool($row->testtool_sprachwahl);
			$obj->updateamum = $row->updateamum;
			$obj->updatevon = $row->updatevon;
			$obj->insertamum = $row->insertamum;
			$obj->insertvon = $row->insertvon;
			$obj->bezeichnung_studienordnung = $row->bezeichnung_studienordnung;
			$obj->ects = $row->ects;
			$obj->studiengangbezeichnung = $row->studiengangbezeichnung;
			$obj->studiengangbezeichnung_englisch = $row->studiengangbezeichnung_englisch;
			$obj->studiengangkurzbzlang = $row->studiengangkurzbzlang;
			$obj->studiengang_kz = $row->studiengang_kz;
			$obj->akadgrad_id = $row->akadgrad_id;
			$obj->lgartcode = $row->lgartcode;
			$obj->lehrgangsart = $row->lehrgangsart;

			$obj->new = true;

			$db->result[] = $obj;
		}
		return $db->result;
	}
	else
		return false;
}

/**
 * Holt die vorkommenden Orgform/Sprache Kombinationen aus den aktiven Studienplänen, bei denen das Attribut onlinebewerbung_studienplan TRUE ist.
 *
 * @param integer $studiengang_kz optional
 * @param array $studiensemester_kurzbz Array von Studiensemestern, in deren Gueltigkeit die Studienplaene liegen
 * @param string $ausbildungssemester Kommaseparierter String mit Ausbildungssemestern, in deren Gueltigkeit die Studienplaene liegen
 * @param string $orgform_kurzbz optional
 */
function getOrgformSpracheForOnlinebewerbung($studiengang_kz = null, $studiensemester_kurzbz = null, $ausbildungssemester = null, $orgform_kurzbz = null)
{
	$db = new basis_db();
	$qry = "SELECT DISTINCT
				    tbl_studienplan.orgform_kurzbz,tbl_studienplan.sprache
			    FROM
				    lehre.tbl_studienplan
				    JOIN lehre.tbl_studienordnung USING(studienordnung_id)
				    JOIN lehre.tbl_studienplan_semester USING(studienplan_id)
			    WHERE
				    tbl_studienplan.orgform_kurzbz IS NOT NULL
				AND
					tbl_studienplan.aktiv
				AND
					tbl_studienplan.onlinebewerbung_studienplan=TRUE ";

	if ($studiengang_kz != '')
	{
		$qry .= " AND tbl_studienordnung.studiengang_kz=" . $db->db_add_param($studiengang_kz, FHC_INTEGER);
	}
	if ($studiensemester_kurzbz != '')
	{
		$studiensemester_kurzbz = $db->db_implode4SQL($studiensemester_kurzbz);
		$qry .= " AND tbl_studienplan_semester.studiensemester_kurzbz IN (" . $studiensemester_kurzbz . ")";
	}
	if ($ausbildungssemester != '')
	{
		$ausbildungssemester = explode(',', $ausbildungssemester);
		$qry .= " AND tbl_studienplan_semester.semester IN (" . $db->implode4SQL($ausbildungssemester) . ")";
	}
	if ($orgform_kurzbz != '')
	{
		$qry .= " AND orgform_kurzbz=" . $db->db_add_param($orgform_kurzbz);
	}
	$qry .= " ORDER by orgform_kurzbz,sprache";

	if ($result = $db->db_query($qry))
	{
		$db->result = '';
		while ($row = $db->db_fetch_object($result))
		{
			$obj = new studienplan();
			$obj->orgform_kurzbz = $row->orgform_kurzbz;
			$obj->sprache = $row->sprache;

			$obj->new = true;

			$db->result[] = $obj;
		}
		return $db->result;
	}
	else
		return false;
}
/**
 * Laedt alle Gemeinden zu einer PLZ
 *
 * @param integer $plz PLZ
 * @return boolean Objekt mit den Gemeinden, sonst false
 */
function BewerbungGetGemeinden($plz)
{
	$db = new basis_db();
	$qry = "SELECT DISTINCT
					plz, name, ortschaftskennziffer, ortschaftsname, bulacode, bulabez,
					(
						SELECT count(ort) FROM public.tbl_adresse WHERE gemeinde=a.name AND plz=a.plz::varchar AND TRIM(ort)=a.ortschaftsname LIMIT 1
					) AS anzahl
				FROM
					bis.tbl_gemeinde a
				WHERE
					plz = " . $db->db_add_param($plz, FHC_INTEGER) . "
				 ORDER BY anzahl DESC";

	if ($result = $db->db_query($qry))
	{
		$db->result = '';
		while ($row = $db->db_fetch_object($result))
		{
			$obj = new stdClass();
			$obj->plz = $row->plz;
			$obj->gemeindename = $row->name;
			$obj->ortschaftskennziffer = $row->ortschaftskennziffer;
			$obj->ortschaftsname = $row->ortschaftsname;
			$obj->bulacode = $row->bulacode;
			$obj->bulabez = $row->bulabez;

			$obj->new = true;

			$db->result[] = $obj;
		}
		return $db->result;
	}
	else
		return false;
}
function getBewerbungszeitraum($studiengang_kz, $studiensemester, $studienplan_id, $nationengruppe_kurzbz = null, $person_id = null)
{
	global $p, $datum;
	$tage_bis_fristablauf = '';
	$fristAbgelaufen = false;
	$infoDiv = '';
	$bewerbungszeitraum = '';
	$class = '';
	$bewerbungsfrist = '';

	//wenn eine interne ZGV vorliegt, Bewerbungsfrist für EU-Nation nehmen
	$prestudent = new prestudent();
	if ($prestudent->existsZGVIntern($person_id))
	{
		$nationengruppe_kurzbz = 'eu';
	}

	$bewerbungsfristen = new bewerbungstermin();
	$bewerbungsfristen->getBewerbungstermine($studiengang_kz, $studiensemester, 'nationengruppe_kurzbz NULLS FIRST, insertamum DESC', $studienplan_id, $nationengruppe_kurzbz);

	// Wenn eine Nationengruppe übergeben wurde und kein Ergebnis zurück kommt, nationengruppe nochmal mit Parameter 0 (alle NULL-Werte) probieren
	if ($nationengruppe_kurzbz != '' && !isset($bewerbungsfristen->result[0]))
	{
		$bewerbungsfristen->getBewerbungstermine($studiengang_kz, $studiensemester, 'nationengruppe_kurzbz NULLS LAST, insertamum DESC', $studienplan_id, 0);
	}

	if (isset($bewerbungsfristen->result[0]))
	{
		$bewerbungsfristen = $bewerbungsfristen->result[0];
		$bewerbungsbeginn = '';
		if ($bewerbungsfristen->beginn != '')
		{
			$bewerbungsbeginn = $datum->formatDatum($bewerbungsfristen->beginn, 'd.m.Y').' - ';
		}
		else
		{
			if (CAMPUS_NAME == 'FH Technikum Wien')
				$bewerbungsbeginn = '';
			else
				$bewerbungsbeginn = $p->t('bewerbung/unbegrenzt');
		}

		// Wenn eine Ende-Datum gesetzt ist, wird dieses angezeigt, bis es erreicht ist
		// Danach wird die Nachfrist angezeigt, falls eine gesetzt ist.

		if ($bewerbungsfristen->ende != '' && strtotime($bewerbungsfristen->ende) >= time())
		{
			$tage_bis_fristablauf = ((strtotime($bewerbungsfristen->ende) - time()) / 86400);
			// Wenn die Frist in weniger als 7 Tagen ablaeuft oder vorbei ist, hervorheben
			if ($tage_bis_fristablauf > 7)
			{
				$infoDiv = '<span class="text-muted small">| ' . $p->t('bewerbung/bewerbungsfrist') . ': ' . $datum->formatDatum($bewerbungsfristen->ende, 'd.m.Y').'</span>';
			}
			if ($tage_bis_fristablauf <= 7)
			{
				$infoDiv = '| ' . $p->t('bewerbung/bewerbungsfrist') . ': ' . $datum->formatDatum($bewerbungsfristen->ende, 'd.m.Y');
				// Alternativer Text wenn Frist heute endet
				if (floor($tage_bis_fristablauf) == 0)
				{
					$infoDiv .= '<br/><div class="label label-warning">
											<span class="glyphicon glyphicon-warning-sign"></span>
											&nbsp;&nbsp;' . $p->t('bewerbung/bewerbungsfristEndetHeute') . '</div>';
				}
				else
				{
					$infoDiv .= '<br/><div class="label label-warning">
											<span class="glyphicon glyphicon-warning-sign"></span>
											&nbsp;&nbsp;' . $p->t('bewerbung/bewerbungsfristEndetInXTagen', array(floor($tage_bis_fristablauf))) . '</div>';
				}
				$class = 'class="alert-warning"';
			}
			if ($tage_bis_fristablauf <= 0)
			{
				$infoDiv = '<br/><div class="label label-danger">
											<span class="glyphicon glyphicon-warning-sign"></span>
											&nbsp;&nbsp;' . $p->t('bewerbung/bewerbungsfristFuerStudiensemesterXAbgelaufen', array(
						$studiensemester
					)) . '</div>';
				$fristAbgelaufen = true;
				$class = 'class="alert-danger"';
			}
			$bewerbungszeitraum = $bewerbungsbeginn.'<span '.$class.'>'.$datum->formatDatum($bewerbungsfristen->ende, 'd.m.Y').'</span>';
			$bewerbungsfrist = $datum->formatDatum($bewerbungsfristen->ende, 'd.m.Y');
		}
		elseif ($bewerbungsfristen->nachfrist == true && $bewerbungsfristen->nachfrist_ende != '')
		{
			$tage_bis_fristablauf = ((strtotime($bewerbungsfristen->nachfrist_ende) - time()) / 86400);
			// Wenn die Frist in weniger als 7 Tagen ablaeuft oder vorbei ist, hervorheben
			if ($tage_bis_fristablauf > 7)
			{
				$infoDiv = '| ' . $p->t('bewerbung/bewerbungsfrist') . ': ' . $datum->formatDatum($bewerbungsfristen->nachfrist_ende, 'd.m.Y');
			}
			if ($tage_bis_fristablauf <= 7)
			{
				$infoDiv = '| ' . $p->t('bewerbung/bewerbungsfrist') . ': ' . $datum->formatDatum($bewerbungsfristen->nachfrist_ende, 'd.m.Y');
				// Alternativer Text wenn Frist heute endet
				if (floor($tage_bis_fristablauf) == 0)
				{
					$infoDiv .= '<br/><div class="label label-warning">
											<span class="glyphicon glyphicon-warning-sign"></span>
											&nbsp;&nbsp;' . $p->t('bewerbung/bewerbungsfristEndetHeute') . '</div>';
				}
				else
				{
					$infoDiv .= '<br/><div class="label label-warning">
											<span class="glyphicon glyphicon-warning-sign"></span>
											&nbsp;&nbsp;' . $p->t('bewerbung/bewerbungsfristEndetInXTagen', array(floor($tage_bis_fristablauf))) . '</div>';
				}

				$class = 'class="alert-warning"';
			}
			if ($tage_bis_fristablauf <= 0)
			{
				$infoDiv = '<br/><div class="label label-danger">
											<span class="glyphicon glyphicon-warning-sign"></span>
											&nbsp;&nbsp;' . $p->t('bewerbung/bewerbungsfristFuerStudiensemesterXAbgelaufen', array(
						$studiensemester
					)) . '</div>';
				$fristAbgelaufen = true;
				$class = 'class="alert-danger"';
			}

			$bewerbungszeitraum = $bewerbungsbeginn.'<span '.$class.'>'.$datum->formatDatum($bewerbungsfristen->nachfrist_ende, 'd.m.Y').'</span>';
			$bewerbungsfrist = $datum->formatDatum($bewerbungsfristen->nachfrist_ende, 'd.m.Y');
		}
		elseif ($bewerbungsfristen->ende != '')
		{
			$tage_bis_fristablauf = ((strtotime($bewerbungsfristen->ende) - time()) / 86400);
			if ($tage_bis_fristablauf <= 0)
			{
				$infoDiv = '<br/><div class="label label-danger">
											<span class="glyphicon glyphicon-warning-sign"></span>
											&nbsp;&nbsp;' . $p->t('bewerbung/bewerbungsfristFuerStudiensemesterXAbgelaufen', array(
						$studiensemester
					)) . '</div>';
				$fristAbgelaufen = true;
				$class = 'class="alert-danger"';
			}
			$bewerbungszeitraum = $bewerbungsbeginn.'<span '.$class.'>'.$datum->formatDatum($bewerbungsfristen->ende, 'd.m.Y').'</span>';
			$bewerbungsfrist = $datum->formatDatum($bewerbungsfristen->ende, 'd.m.Y');
		}
		else
		{
			$bewerbungszeitraum = $p->t('bewerbung/unbegrenzt');
		}
		// Wenn der Beginn der Bewerbungfrist in der Zukunft liegt
		if ($bewerbungsfristen->beginn != '' && strtotime($bewerbungsfristen->beginn) > time())
		{
			$infoDiv = '<br><div class="label label-success">
										&nbsp;&nbsp;' . $p->t('bewerbung/bewerbungenFuerAb', array(
					$studiensemester,
					$datum->formatDatum($bewerbungsfristen->beginn, 'd.m.Y')
				)) . '</div>';
			$fristAbgelaufen = true;
			$bewerbungszeitraum = $bewerbungsbeginn.$p->t('bewerbung/unbegrenzt');
		}
	}
	else
	{
		$infoDiv = '';
		$bewerbungszeitraum = $p->t('bewerbung/unbegrenzt');
	}

	// Wenn es eine Anmerkung zur Bewerbungsfrist gibt, diese trotzdem Anzeigen
	if ($bewerbungsfristen->anmerkung != '')
		$infoDiv .= '<br><div class="panel panel-info"><div class="panel-heading">' . nl2br($bewerbungsfristen->anmerkung) . '</div></div>';

	return array(
		'infoDiv' => $infoDiv,
		'frist_abgelaufen' => $fristAbgelaufen,
		'bewerbungszeitraum' => $bewerbungszeitraum,
		'bewerbungsfrist' => $bewerbungsfrist
	);
}

/**
 * Liefert alle Dokumente die eine Person im Bewerbungstool abzugeben hat.
 *
 * @param integer $person_id
 * @param array $studiensemester_array
 */
function getAllDokumenteBewerbungstoolForPerson($person_id, $studiensemester_array = null)
{
	$db = new basis_db();
	$sprache = new sprache();
	$bezeichnung_mehrsprachig = $sprache->getSprachQuery('bezeichnung_mehrsprachig');
	$dokumentbeschreibung_mehrsprachig = $sprache->getSprachQuery('dokumentbeschreibung_mehrsprachig');

	if(isset($studiensemester_array) && !is_array($studiensemester_array))
	{
		$db->errormsg = '$studiensemester_array is not an array';
		return false;
	}

	// $beschreibung_mehrsprachig = $sprache->getSprachQuery('beschreibung_mehrsprachig');
	$qry = "SELECT DISTINCT
			tbl_prestudent.prestudent_id,
			dok_stg.studiengang_kz,
			dok_stg.stufe,
			dok_stg.dokument_kurzbz,
			tbl_dokument.bezeichnung,
			dok_stg.pflicht,
			dok_stg.nachreichbar,
			tbl_dokument.ausstellungsdetails,
			(
				SELECT count(*)
				FROM PUBLIC.tbl_akte
				WHERE dokument_kurzbz = dok_stg.dokument_kurzbz
					AND person_id = " . $db->db_add_param($person_id, FHC_INTEGER) . "
					AND (inhalt IS NOT NULL OR dms_id IS NOT NULL)
				) AS anzahl_akten_vorhanden,
			(
				SELECT count(*)
				FROM PUBLIC.tbl_akte
				WHERE dokument_kurzbz = dok_stg.dokument_kurzbz
					AND person_id = " . $db->db_add_param($person_id, FHC_INTEGER) . "
					AND formal_geprueft_amum IS NOT NULL
				) AS anzahl_akten_formal_geprueft,
			(
				SELECT count(*)
				FROM PUBLIC.tbl_akte
				WHERE dokument_kurzbz = dok_stg.dokument_kurzbz
					AND person_id = " . $db->db_add_param($person_id, FHC_INTEGER) . "
					AND nachgereicht = true
				) AS anzahl_akten_wird_nachgereicht,
			(
				SELECT count(*)
				FROM PUBLIC.tbl_dokumentprestudent
				WHERE dokument_kurzbz = dok_stg.dokument_kurzbz
					AND prestudent_id IN (
						SELECT prestudent_id
						FROM PUBLIC.tbl_prestudent
						WHERE person_id = " . $db->db_add_param($person_id, FHC_INTEGER) . "
						)
				) AS anzahl_dokumente_akzeptiert,
			$bezeichnung_mehrsprachig, $dokumentbeschreibung_mehrsprachig
			FROM PUBLIC.tbl_dokumentstudiengang dok_stg
			JOIN PUBLIC.tbl_prestudent USING (studiengang_kz)
			JOIN PUBLIC.tbl_dokument USING (dokument_kurzbz)
			WHERE tbl_prestudent.person_id = " . $db->db_add_param($person_id, FHC_INTEGER) . "
				AND dok_stg.onlinebewerbung IS true ";

			if (isset($studiensemester_array) && !empty($studiensemester_array))
			{
				$i = 0;
				$qry .= " AND (";
				foreach ($studiensemester_array as $studiensemester)
				{
					if ($i > 0)
						$qry .= " OR ";
					$qry .= " get_rolle_prestudent (tbl_prestudent.prestudent_id, " . $db->db_add_param($studiensemester, FHC_STRING) . ") NOT IN ('Abgewiesener','Abbrecher')";
					$i ++;
				}
				$qry .= " ) ";
			}
			else
				$qry .= " AND get_rolle_prestudent (tbl_prestudent.prestudent_id, null) NOT IN ('Abgewiesener','Abbrecher')";

			$qry .= " ORDER BY studiengang_kz,stufe,dokument_kurzbz,
				pflicht DESC";
	//echo $qry;
	if ($result = $db->db_query($qry))
	{
		while ($row = $db->db_fetch_object($result))
		{
			$dok = new stdClass();
			$dok->prestudent_id = $row->prestudent_id;
			$dok->studiengang_kz = $row->studiengang_kz;
			$dok->stufe = $row->stufe;
			$dok->dokument_kurzbz = $row->dokument_kurzbz;
			$dok->bezeichnung = $row->bezeichnung;
			$dok->pflicht = $db->db_parse_bool($row->pflicht);
			$dok->nachreichbar = $db->db_parse_bool($row->nachreichbar);
			$dok->ausstellungsdetails = $db->db_parse_bool($row->ausstellungsdetails);
			$dok->anzahl_akten_vorhanden = $row->anzahl_akten_vorhanden;
			$dok->anzahl_akten_formal_geprueft = $row->anzahl_akten_formal_geprueft;
			$dok->anzahl_dokumente_akzeptiert = $row->anzahl_dokumente_akzeptiert;
			$dok->anzahl_akten_wird_nachgereicht = $row->anzahl_akten_wird_nachgereicht;
			$dok->bezeichnung_mehrsprachig = $sprache->parseSprachResult('bezeichnung_mehrsprachig', $row);
			$dok->dokumentbeschreibung_mehrsprachig = $sprache->parseSprachResult('dokumentbeschreibung_mehrsprachig', $row);
			$dok->beschreibung_mehrsprachig = $sprache->parseSprachResult('beschreibung_mehrsprachig', $row);

			$dok->new = true;

			$db->result[] = $dok;
		}
		if (isset($db->result))
			return $db->result;
		else
			return false;
	}
	else
	{
		$db->errormsg = "Fehler bei der Abfrage aufgetreten";
		return false;
	}
}

/**
 * Prueft ob eine Akte schon formal geprueft oder der Dokumenttyp der Akte schon akzeptiert wurde.
 *
 * @param $dokument_kurzbz
 * @param $person_id
 * @param $studiengang_kz integer oder array aus mehreren studiengang_kz
 * @return boolean true wenn akzeptiert, false wenn noch nicht akzeptiert
 */
function akteAkzeptiert($akte_id)
{
	$db = new basis_db();
	$qry = "SELECT akte_id
			FROM PUBLIC.tbl_akte
			WHERE tbl_akte.akte_id = " . $db->db_add_param($akte_id) . "
				AND tbl_akte.formal_geprueft_amum IS NOT NULL

			UNION

			SELECT akte_id
			FROM PUBLIC.tbl_akte akte
			WHERE EXISTS (
					SELECT *
					FROM PUBLIC.tbl_dokumentprestudent
					WHERE dokument_kurzbz = akte.dokument_kurzbz
						AND prestudent_id IN (
							SELECT prestudent_id
							FROM PUBLIC.tbl_prestudent
							WHERE person_id = akte.person_id
							)
					)
				AND akte.akte_id = " . $db->db_add_param($akte_id);

	if ($result = $db->db_query($qry))
	{
		if ($db->db_num_rows($result) > 0)
		{
			return true;
		}
	}
}

/**
 * Liefert die interne Empfangsadresse des Studiengangs fuer den Mailversand.
 * Wenn BEWERBERTOOL_MAILEMPFANG gesetzt ist, wird diese genommen,
 * sonst diejenige aus BEWERBERTOOL_BEWERBUNG_EMPFAENGER,
 * sonst die Mailadresse des Studiengangs
 *
 * @param integer $studiengang_kz
 * @param integer $studienplan_id
 * @param string $orgform_kurzbz
 * @return string mit den Mailadressen sonst false
 */
function getMailEmpfaenger($studiengang_kz, $studienplan_id = null, $orgform_kurzbz = null)
{
	$studiengang = new studiengang($studiengang_kz);

	if ($studienplan_id != '')
	{
		$studienplan = new studienplan();
		$studienplan->loadStudienplan($studienplan_id);
	}

	$empf_array = array();
	$empfaenger = '';
	if(defined('BEWERBERTOOL_BEWERBUNG_EMPFAENGER'))
		$empf_array = unserialize(BEWERBERTOOL_BEWERBUNG_EMPFAENGER);

	// Umgehung für FHTW. Ausprogrammiert im Code
	if(CAMPUS_NAME != 'FH Technikum Wien' && defined('BEWERBERTOOL_MAILEMPFANG') && BEWERBERTOOL_MAILEMPFANG != '')
	{
		$empfaenger = BEWERBERTOOL_MAILEMPFANG;
	}
	elseif(isset($empf_array[$studiengang_kz]))
	{
		// Pfuschloesung, damit bei BIF Dual die Mail an info.bid geht
		if (CAMPUS_NAME == 'FH Technikum Wien' && $studiengang_kz == 257)
		{
			if ((isset($studienplan) && $studienplan->orgform_kurzbz == 'DUA') ||
				($orgform_kurzbz != '' && $orgform_kurzbz == 'DUA'))
			{
				$empfaenger = 'info.bid@technikum-wien.at';
			}
			else
			{
				$empfaenger = 'info.bif@technikum-wien.at';
			}
		}
		else
			$empfaenger = $empf_array[$studiengang_kz];
	}
	else
	{
		// Pfuschloesung, damit bei BIF Dual die Mail an info.bid geht
		if (CAMPUS_NAME == 'FH Technikum Wien' && $studiengang_kz == 257)
		{
			if ((isset($studienplan) && $studienplan->orgform_kurzbz == 'DUA') ||
				($orgform_kurzbz != '' && $orgform_kurzbz == 'DUA'))
			{
				$empfaenger = 'info.bid@technikum-wien.at';
			}
			else
			{
				$empfaenger = 'info.bif@technikum-wien.at';
			}
		}
		else
			$empfaenger = $studiengang->email;
	}

	if ($empfaenger != '')
		return $empfaenger;
	else
		return false;
}

/**
 * Laedt alle Bewerbungen einer Person
 * @param integer $person_id
 * @param boolean $aktive. 	Wenn true werden nur aktive Bewerbungen (Interessenten, Bewerber, ...) geliefert
 * 							Wenn false werden nur inaktive Bewerbungen mit Endstatus (Abgewiesene, Abbrecher, Absolvent, ...) geliefert
 * 							Wenn null werden alle Bewerbungen geliefert
 * @return true wenn ok, false wenn Fehler
 */
function getBewerbungen($person_id, $aktive = null)
{
	$db = new basis_db();
	if(!is_numeric($person_id) || $person_id=='')
	{
		$db->errormsg='ID ist ungueltig';
		return false;
	}

	$qry = "SELECT tbl_prestudent.prestudent_id,
			tbl_prestudent.studiengang_kz,
			tbl_prestudent.priorisierung,
			(
				SELECT tbl_status.bezeichnung_mehrsprachig
				FROM public.tbl_prestudentstatus
				JOIN public.tbl_status USING (status_kurzbz)
				WHERE tbl_status.status_kurzbz = tbl_prestudentstatus.status_kurzbz
				AND prestudent_id=tbl_prestudent.prestudent_id
				ORDER BY datum DESC, tbl_prestudentstatus.insertamum DESC LIMIT 1
			) AS laststatus,
			(
				SELECT tbl_status.status_kurzbz
				FROM public.tbl_prestudentstatus
				JOIN public.tbl_status USING (status_kurzbz)
				WHERE tbl_status.status_kurzbz = tbl_prestudentstatus.status_kurzbz
				AND prestudent_id=tbl_prestudent.prestudent_id
				ORDER BY datum DESC, tbl_prestudentstatus.insertamum DESC LIMIT 1
			) AS laststatus_kurzbz,
			(
				SELECT studiensemester_kurzbz
				FROM public.tbl_prestudentstatus
				JOIN public.tbl_status USING (status_kurzbz)
				WHERE tbl_status.status_kurzbz = tbl_prestudentstatus.status_kurzbz
				AND prestudent_id=tbl_prestudent.prestudent_id
				ORDER BY datum DESC, tbl_prestudentstatus.insertamum DESC LIMIT 1
			) AS laststatus_studiensemester_kurzbz,
			tbl_studiengang.bezeichnung,
			tbl_studiengang.english,
			tbl_studiengang.typ,
			tbl_prestudent.zgvnation,
			tbl_prestudent.zgvmanation,
			tbl_studiengang.lgartcode
			FROM public.tbl_prestudent
			JOIN public.tbl_studiengang USING (studiengang_kz)
			WHERE person_id=".$db->db_add_param($person_id, FHC_INTEGER)."
			ORDER BY priorisierung ASC NULLS LAST, tbl_prestudent.insertamum DESC";

	if($db->db_query($qry))
	{
		while($row = $db->db_fetch_object())
		{
			$obj = new stdClass();
			// Status Diplomand,Incoming,Outgoing,Student und Unterbrecher werden nicht ausgegeben
			if ($aktive === true && in_array($row->laststatus_kurzbz, array('Diplomand','Incoming','Outgoing','Student','Unterbrecher')))
				continue;
			elseif ($aktive === true && in_array($row->laststatus_kurzbz, array('Abbrecher','Abgewiesener','Absolvent')))
				continue;
			elseif ($aktive === false && !in_array($row->laststatus_kurzbz, array('Abbrecher','Abgewiesener','Absolvent')))
				continue;

			$obj->prestudent_id = $row->prestudent_id;
			$obj->studiengang_kz = $row->studiengang_kz;
			$obj->priorisierung = $row->priorisierung;
			$obj->studiengang_typ = $row->typ;
			$obj->laststatus = $db->db_parse_lang_array($row->laststatus);
			$obj->laststatus_kurzbz = $row->laststatus_kurzbz;
			$obj->laststatus_studiensemester_kurzbz = $row->laststatus_studiensemester_kurzbz;
			$obj->bezeichnung_arr['German'] = $row->bezeichnung;
			$obj->bezeichnung_arr['English'] = $row->english;
			$obj->typ = $row->typ;
			$obj->zgvnation = $row->zgvnation;
			$obj->zgvmanation = $row->zgvmanation;
			$obj->lgartcode = $row->lgartcode;

			$db->result[] = $obj;
		}
		if (isset($db->result))
			return $db->result;
		else
			return false;
	}
	else
	{
		$db->errormsg = "Fehler beim Laden";
		return false;
	}
}

/**
 * Lädt den Studienplan der Bewerbung mit der höchsten Priorität, der für das übergebene Studiensemester abgeschickt und bestätigt ist.
 * Wenn keine Prio gesetzt ist, wird nach tbl_prestudent.insertamum absteigend (letzthinzugefügter zuerst) sortiert
 *
 * @param integer $person_id
 * @param string $studiensemester_kurzbz Studiensemester des Termins
 *
 * @return $studienplan_id oder FALSE im Fehlerfall
 */
function getPrioStudienplanForReihungstest($person_id, $studiensemester_kurzbz)
{
	$db = new basis_db();
	$qry = "
			(SELECT studienplan_id
			FROM PUBLIC.tbl_person
			JOIN PUBLIC.tbl_prestudent USING (person_id)
			JOIN PUBLIC.tbl_prestudentstatus USING (prestudent_id)
			JOIN lehre.tbl_studienplan USING (studienplan_id)
			JOIN lehre.tbl_studienordnung USING (studienordnung_id)
			JOIN PUBLIC.tbl_studiengang ON (tbl_studienordnung.studiengang_kz = tbl_studiengang.studiengang_kz)
			WHERE person_id = " . $db->db_add_param($person_id, FHC_INTEGER) . "
				AND studiensemester_kurzbz = " . $db->db_add_param($studiensemester_kurzbz) . "
				AND tbl_studiengang.typ = 'm'
				/*AND bewerbung_abgeschicktamum IS NOT NULL*/ /* Auskommentiert, da nicht immer verlaesslich */
				AND bestaetigtam IS NOT NULL
				/*AND bestaetigtvon != ''*/ /* Auskommentiert, da nicht immer verlaesslich */
				AND (
					SELECT status_kurzbz
					FROM PUBLIC.tbl_prestudentstatus
					WHERE prestudent_id = tbl_prestudent.prestudent_id
						AND studiensemester_kurzbz = tbl_prestudentstatus.studiensemester_kurzbz
					ORDER BY datum DESC,
						tbl_prestudentstatus.insertamum DESC LIMIT 1
				) IN ('Interessent')
			ORDER BY priorisierung ASC NULLS LAST, tbl_prestudent.insertamum DESC)

			UNION ALL

			(SELECT studienplan_id
			FROM PUBLIC.tbl_person
			JOIN PUBLIC.tbl_prestudent USING (person_id)
			JOIN PUBLIC.tbl_prestudentstatus USING (prestudent_id)
			JOIN lehre.tbl_studienplan USING (studienplan_id)
			JOIN lehre.tbl_studienordnung USING (studienordnung_id)
			JOIN PUBLIC.tbl_studiengang ON (tbl_studienordnung.studiengang_kz = tbl_studiengang.studiengang_kz)
			WHERE person_id = " . $db->db_add_param($person_id, FHC_INTEGER) . "
				AND studiensemester_kurzbz = " . $db->db_add_param($studiensemester_kurzbz) . "
				AND tbl_studiengang.typ = 'b'
				/*AND bewerbung_abgeschicktamum IS NOT NULL*/ /* Auskommentiert, da nicht immer verlaesslich */
				AND bestaetigtam IS NOT NULL
				/*AND bestaetigtvon != ''*/ /* Auskommentiert, da nicht immer verlaesslich */
				AND (
					SELECT status_kurzbz
					FROM PUBLIC.tbl_prestudentstatus
					WHERE prestudent_id = tbl_prestudent.prestudent_id
						AND studiensemester_kurzbz = tbl_prestudentstatus.studiensemester_kurzbz
					ORDER BY datum DESC,
						tbl_prestudentstatus.insertamum DESC LIMIT 1
					) IN ('Interessent')";
			// An der FHTW werden die Qualifikationskurse ausgenommen
			if (CAMPUS_NAME == 'FH Technikum Wien')
			{
				$qry .= " AND tbl_studiengang.studiengang_kz != 10002 ";
			}
	$qry .= " ORDER BY priorisierung ASC NULLS LAST, tbl_prestudent.insertamum DESC LIMIT 1)";

	if ($result = $db->db_query($qry))
	{
		$studienplaene = array();
		while ($row = $db->db_fetch_object($result))
		{
			$studienplaene[] = $row->studienplan_id;
		}
		
		return $studienplaene;
	}
	else
	{
		$db->errormsg = 'Fehler beim Laden der Daten';
		return false;
	}
}

/**
 * Holt die nächsten Reihungstesttermine mit dem passenden Studienplan, für die sich ein Bewerber anmelden kann.
 * Der Termin muss das Attribut "öffentlich" auf TRUE haben und die Anmeldefrist muss <= dem heutigen Datum liegen.
 *
 * @param array $studienplan_id Studienplan ID eines zugeteilten Studienplans.
 * @param string $studiensemester_kurzbz Studiensemester des Termins
 * @param integer $stufe Optional. Default 1. Stufe, die der Termin haben soll.
 * @param array $excludedStudienplans. Array mit Studienplan_ids, deren Reihungstests von der Abfrage ausgenommen werden sollen
 *
 * @return TRUE, FALSE im Fehlerfall
 */
function getReihungstestsForOnlinebewerbung($studienplan_id, $studiensemester_kurzbz, $stufe = 1, $excludedStudienplans = null)
{
	$db = new basis_db();
	if ($excludedStudienplans != '' && !is_array($excludedStudienplans))
	{
		$db->errormsg='$excludedStudienplans ist kein Array';
		return false;
	}

	if (count($studienplan_id) === 0)
		$studienplan_id = [''];

	$qry = "
			SELECT (
					CASE
						WHEN (
								SELECT max_teilnehmer
								FROM PUBLIC.tbl_reihungstest
								WHERE reihungstest_id = rt.reihungstest_id
								) IS NOT NULL
							THEN (
									SELECT max_teilnehmer
									FROM PUBLIC.tbl_reihungstest
									WHERE reihungstest_id = rt.reihungstest_id
									)
						ELSE (	";
				if (defined('REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND') && is_numeric(REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND))
				{
					$qry .= "	SELECT sum(arbeitsplaetze)
								FROM (
									SELECT (
											SELECT arbeitsplaetze - ceil((arbeitsplaetze::FLOAT / 100) * ".REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND.")
											FROM PUBLIC.tbl_ort
											WHERE ort_kurzbz = rt_ort.ort_kurzbz
											) AS arbeitsplaetze
									FROM PUBLIC.tbl_rt_ort rt_ort
									JOIN PUBLIC.tbl_ort USING (ort_kurzbz)
									WHERE rt_id = rt.reihungstest_id
									) plaetze";
				}
				else
				{
					$qry .= "	SELECT sum(arbeitsplaetze) AS arbeitsplaetze
								FROM PUBLIC.tbl_rt_ort
								JOIN PUBLIC.tbl_ort USING (ort_kurzbz)
								WHERE rt_id = rt.reihungstest_id";
				}

				$qry .= "	)
						END
					) AS anzahl_plaetze,
				(
					SELECT count(*)
					FROM PUBLIC.tbl_rt_person
					WHERE rt_id = rt.reihungstest_id
					) AS anzahl_anmeldungen,
				rt.*,
				studienplan_id,
				typ,
				UPPER(typ::varchar(1) || kurzbz) AS stg_kuerzel
			FROM PUBLIC.tbl_reihungstest rt
			JOIN PUBLIC.tbl_rt_studienplan USING (reihungstest_id)
			LEFT JOIN public.tbl_studiengang ON rt.studiengang_kz = tbl_studiengang.studiengang_kz
			WHERE studienplan_id IN (" . $db->db_implode4SQL($studienplan_id) . ")
				AND studiensemester_kurzbz = " . $db->db_add_param($studiensemester_kurzbz);

			if ($stufe != 1 && $stufe != '')
			{
				$qry .= "	AND stufe = " . $db->db_add_param($stufe, FHC_INTEGER);
			}
			else
			{
				$qry .= " 	AND (
								stufe = 1
								OR stufe IS NULL
								)";
			}

			if (!empty($excludedStudienplans))
			{
				$excludedStudienplans = $db->implode4SQL($excludedStudienplans);
				$qry .= "	AND rt.reihungstest_id NOT IN (
								SELECT reihungstest_id
								FROM PUBLIC.tbl_reihungstest
								JOIN PUBLIC.tbl_rt_studienplan USING (reihungstest_id)
								WHERE studiensemester_kurzbz = " . $db->db_add_param($studiensemester_kurzbz) . "
									AND studienplan_id IN (" . $excludedStudienplans . ")
								)";
			}
	$qry .= "	AND oeffentlich = true
				AND anmeldefrist >= now()::date

			ORDER BY datum,
				uhrzeit ";
// @todo: (stufe = 1 OR stufe IS NULL) ???
	if ($result = $db->db_query($qry))
	{
		$db->result = [];
		while ($row = $db->db_fetch_object($result))
		{
			$obj = new stdClass();

			$obj->anzahl_plaetze = $row->anzahl_plaetze;
			$obj->anzahl_anmeldungen = $row->anzahl_anmeldungen;
			$obj->reihungstest_id = $row->reihungstest_id;
			$obj->studiengang_kz = $row->studiengang_kz;
			$obj->anmerkung = $row->anmerkung;
			$obj->datum = $row->datum;
			$obj->uhrzeit = $row->uhrzeit;
			$obj->updateamum = $row->updateamum;
			$obj->updatevon = $row->updatevon;
			$obj->insertamum = $row->insertamum;
			$obj->insertvon = $row->insertvon;
			$obj->freigeschaltet = $db->db_parse_bool($row->freigeschaltet);
			$obj->max_teilnehmer = $row->max_teilnehmer;
			$obj->oeffentlich = $db->db_parse_bool($row->oeffentlich);
			$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
			$obj->aufnahmegruppe_kurzbz = $row->aufnahmegruppe_kurzbz;
			$obj->stufe = $row->stufe;
			$obj->anmeldefrist = $row->anmeldefrist;
			$obj->studienplan_id = $row->studienplan_id;
			$obj->typ = $row->typ;
			$obj->stg_kuerzel = $row->stg_kuerzel;
			$obj->rt_id = $row->reihungstest_id;
			$obj->new = true;

			$db->result[] = $obj;
		}
		return $db->result;
	}
	else
		return false;
}
// Sortiert die PreStudenten nach der Spalte laststatus_studiensemester_kurzbz und priorität
function sortPrestudents($a, $b)
{
	global $sprache;
	$c = strcmp(strtolower(substr($a->laststatus_studiensemester_kurzbz, 2, 4).substr($a->laststatus_studiensemester_kurzbz, 0, 2)), strtolower(substr($b->laststatus_studiensemester_kurzbz, 2, 4).substr($b->laststatus_studiensemester_kurzbz, 0, 2)));
	if ($a->priorisierung != '' && $b->priorisierung != '')
		$c .= $a->priorisierung - $b->priorisierung;
	else
		$c .= $b->priorisierung - $a->priorisierung;

	$c .= strcmp(strtolower($a->typ), strtolower($b->typ));
	$c .= strcmp(strtolower($a->bezeichnung_arr[$sprache]), strtolower($b->bezeichnung_arr[$sprache]));
	return $c;
}

/**
 * Prüft, ob die Person im übergebenen Studiensemester bei einem PreStudent-Status den übergebenen Statusgrund hat
 *
 * @param integer $person_id
 * @param string $studiensemester_kurzbz Studiensemester der Bewerbung
 *
 * @return boolean True wenn Teilnehmer, FALSE wenn nicht
 */
function hasPersonStatusgrund($person_id, $studiensemester_kurzbz, $status_grund_id)
{
	$db = new basis_db();
	$qry = "
			SELECT count(*) AS anzahl
			FROM PUBLIC.tbl_prestudent
			JOIN PUBLIC.tbl_prestudentstatus USING (prestudent_id)
			WHERE status_kurzbz = 'Interessent'
				AND studiensemester_kurzbz = " . $db->db_add_param($studiensemester_kurzbz) . "
				AND statusgrund_id = " . $db->db_add_param($status_grund_id, FHC_INTEGER) . "
				AND person_id = " . $db->db_add_param($person_id, FHC_INTEGER);

	if ($db->db_query($qry))
	{
		if ($row = $db->db_fetch_object())
		{
			if ($row->anzahl > 0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			$db->errormsg = 'Fehler bei der Abfrage';
			return false;
		}
	}
	else
	{
		$db->errormsg = 'Fehler beim Laden der Daten';
		return false;
	}
}

/**
 * Liefert die Stufe eines Prestudenten
 * Kein passender Status -> 0
 * Interessent -> 10
 * Interessent Status bestätigt -> 15
 * Bewerber -> 20
 * Wartender -> 30
 * Aufgenommener -> 40
 * Student -> 50
 *
 * @param integer $prestudent_id
 * @param string $studiensemester_kurzbz Optional. Studiensemester der Bewerbung
 *
 * @return integer Stufe, in der sich der PreStudent befindet oder false im Fehlerfall
 */
function getStufeBewerberFuerDokumente($prestudent_id, $studiensemester_kurzbz = null)
{
	$db = new basis_db();
	$qry = "
			SELECT status_kurzbz, bestaetigtam
			FROM public.tbl_prestudent
			JOIN public.tbl_prestudentstatus USING (prestudent_id)
			WHERE prestudent_id = ".$db->db_add_param($prestudent_id, FHC_INTEGER);

	if ($studiensemester_kurzbz != '')
	{
		$qry .= " AND studiensemester_kurzbz = ".$db->db_add_param($studiensemester_kurzbz);
	}

	$qry .= " ORDER BY tbl_prestudentstatus.insertamum DESC LIMIT 1";

	if ($db->db_query($qry))
	{
		if ($row = $db->db_fetch_object())
		{
			switch ($row->status_kurzbz)
			{
				case 'Interessent':
					if ($row->bestaetigtam == '')
					{
						return 10;
						break;
					}
					else
					{
						return 15;
						break;
					}
				case 'Bewerber':
					return 20;
					break;
				case 'Wartender':
					return 30;
					break;
				case 'Aufgenommener':
					return 40;
					break;
				case 'Student':
					return 50;
					break;
				default:
					return 0;
			}
		}
		else
		{
			$db->errormsg = 'Fehler bei der Abfrage';
			return false;
		}
	}
	else
	{
		$db->errormsg = 'Fehler beim Laden der Daten';
		return false;
	}
}

/**
 * Liefert die Liste den Akten. Wenn eine Akte im Status "Wir nachgereicht" ist, werden die Nachreich.Daten geliefert. Ansonsten die Aktenliste.
 *
 * @param integer $person_id
 * @param string $dokument_kurzbz
 *
 * @return string HTML-String mit den Nachreichdaten
 */
function getAktenListe($person_id, $dokument_kurzbz)
{
	global $p, $datum;
	$anzahlDokumenteJeTyp = 100;
	if (defined('BEWERBERTOOL_ANZAHL_DOKUMENTPLOAD_JE_TYP') && is_numeric(BEWERBERTOOL_ANZAHL_DOKUMENTPLOAD_JE_TYP))
	{
		$anzahlDokumenteJeTyp = BEWERBERTOOL_ANZAHL_DOKUMENTPLOAD_JE_TYP;
	}

	$akten = new akte();
	$akten->getAkten($person_id, $dokument_kurzbz);
	$returnstring = '<div class="list-group list_'.$dokument_kurzbz.'" style="margin-bottom: 0">';
	foreach ($akten->result as $akte)
	{
		// Wenn Akte im Status "wird nachgereicht" ist und noch nichts hochgeladen wurde
		// zeige die Daten der Nachreichung und den Upload
		if ($akte->nachgereicht === true && $akte->inhalt == '' && $akte->dms_id == '')
		{
			//$returnstring = '';
			$returnstring .= '		<div class="list-group-item listItem_'.$akte->akte_id.'">

											'.$p->t('bewerbung/wirdNachgreichtAm').' '.$datum->formatDatum($akte->nachgereicht_am, 'd.m.Y').'
										';
			// An der FHTW wird beim Dokument "zgv_bakk" das vorläufiges ZGV-Dokument (ZgvBaPre) angezeigt, wenn eines vorhanden ist
			// und das Dokument "ZgvMaPre" bei "zgv_mast"
			// und das Dokument "VorlSpB2" bei "SprachB2"
			if (CAMPUS_NAME == 'FH Technikum Wien')
			{
				if ($akte->dokument_kurzbz == 'zgv_bakk')
				{
					// Checken, ob der Dokumenttyp ZgvBaPre in der DB vorhanden ist
					$checkZgvBaPre = new dokument();
					if ($checkZgvBaPre->loadDokumenttyp('ZgvBaPre'))
					{
						// Laden des vorläufigen ZGV Dokuments der Person
						$zgvBaPre = new akte();
						$zgvBaPre->getAkten($person_id, 'ZgvBaPre');
						if (isset($zgvBaPre->result[0]))
						{
							$returnstring .= '

														<br><span>'.$p->t('bewerbung/vorlaeufigesDokument').':<br>
														<span class="glyphicon glyphicon-file" aria-hidden="true"></span>'.cutString($zgvBaPre->result[0]->titel, 25, '...').'</span>
														<button type="button" title="'.$p->t('bewerbung/dokumentHerunterladen').'"
																class="btn btn-default btn-sm"
																href="'.APP_ROOT.'cms/dms.php?id='.$zgvBaPre->result[0]->dms_id.'"
																onclick="FensterOeffnen(\''.APP_ROOT.'cms/dms.php?id='.$zgvBaPre->result[0]->dms_id.'&akte_id='.$zgvBaPre->result[0]->akte_id.'\'); return false;">
															<span class="glyphicon glyphicon glyphicon-download-alt" aria-hidden="true" title="'.$p->t('bewerbung/dokumentHerunterladen').'"></span>
														</button>';
						}
					}
				}
				elseif ($akte->dokument_kurzbz == 'zgv_mast')
				{
					// Checken, ob der Dokumenttyp ZgvMaPre in der DB vorhanden ist
					$checkZgvMaPre = new dokument();
					if ($checkZgvMaPre->loadDokumenttyp('ZgvMaPre'))
					{
						// Laden des vorläufigen ZGV Dokuments der Person
						$zgvMaPre = new akte();
						$zgvMaPre->getAkten($person_id, 'ZgvMaPre');
						if (isset($zgvMaPre->result[0]))
						{
							$returnstring .= '

														<br><span>'.$p->t('bewerbung/vorlaeufigesDokument').':<br>
														<span class="glyphicon glyphicon-file" aria-hidden="true"></span>'.cutString($zgvMaPre->result[0]->titel, 25, '...').'</span>
														<button type="button" title="'.$p->t('bewerbung/dokumentHerunterladen').'"
																class="btn btn-default btn-sm"
																href="'.APP_ROOT.'cms/dms.php?id='.$zgvMaPre->result[0]->dms_id.'"
																onclick="FensterOeffnen(\''.APP_ROOT.'cms/dms.php?id='.$zgvMaPre->result[0]->dms_id.'&akte_id='.$zgvMaPre->result[0]->akte_id.'\'); return false;">
															<span class="glyphicon glyphicon glyphicon-download-alt" aria-hidden="true" title="'.$p->t('bewerbung/dokumentHerunterladen').'"></span>
														</button>';
						}
					}
				}
				elseif ($akte->dokument_kurzbz == 'SprachB2')
				{
					// Checken, ob der Dokumenttyp SprachB2 in der DB vorhanden ist
					$checkVorlSpB2 = new dokument();
					if ($checkVorlSpB2->loadDokumenttyp('VorlSpB2'))
					{
						// Laden des vorläufigen ZGV Dokuments der Person
						$vorlSpB2 = new akte();
						$vorlSpB2->getAkten($person_id, 'VorlSpB2');
						if (isset($vorlSpB2->result[0]))
						{
							$returnstring .= '

														<br><span>'.$p->t('bewerbung/vorlaeufigesDokument').':<br>
														<span class="glyphicon glyphicon-file" aria-hidden="true"></span>'.cutString($vorlSpB2->result[0]->titel, 25, '...').'</span>
														<button type="button" title="'.$p->t('bewerbung/dokumentHerunterladen').'"
																class="btn btn-default btn-sm"
																href="'.APP_ROOT.'cms/dms.php?id='.$vorlSpB2->result[0]->dms_id.'"
																onclick="FensterOeffnen(\''.APP_ROOT.'cms/dms.php?id='.$vorlSpB2->result[0]->dms_id.'&akte_id='.$vorlSpB2->result[0]->akte_id.'\'); return false;">
															<span class="glyphicon glyphicon glyphicon-download-alt" aria-hidden="true" title="'.$p->t('bewerbung/dokumentHerunterladen').'"></span>
														</button>';
						}
					}
				}
			}
			$returnstring .= '</div>';
		}
		// Liste mit den Akten und Download und Lösch-Button
		else
		{
			// Beim Lichtbild wird aus cis/public/bild.php geladen und nicht aus dem DMS
			if ($akte->dokument_kurzbz == 'Lichtbil')
			{
				$downloadlink = APP_ROOT.'cis/public/bild.php?src=person&person_id='.$person_id;
			}
			else
			{
				$downloadlink = APP_ROOT.'cms/dms.php?id='.$akte->dms_id.'&akte_id='.$akte->akte_id;
			}
			$returnstring .= '	<div class="list-group-item listItem_'.$akte->akte_id.'" title="'.$akte->titel.'">
									<span><span class="glyphicon glyphicon-file" aria-hidden="true"></span>'.cutString($akte->titel, 25, '...').'</span>
									<br>
									<button type="button"
											title="'.$p->t('bewerbung/dokumentHerunterladen').'"
											class="btn btn-default btn-sm"
											onclick="FensterOeffnen(\''.$downloadlink.'\'); return false;">
										<span class="glyphicon glyphicon glyphicon-download-alt" aria-hidden="true" title="'.$p->t('bewerbung/dokumentHerunterladen').'"></span>
									</button>';
			// Löschen nur bei nicht-akzeptierten Dokumenten möglich
			// Invitation letter dürfen nie gelöscht werden
			if (!akteAkzeptiert($akte->akte_id) && ($akte->dokument_kurzbz != 'InvitLet' && $akte->dokument_kurzbz != 'ZeitBest'))
			{
				$returnstring .= '	<button type="button"
											title="'.$p->t('global/löschen').'"
											class="btn btn-default btn-sm"
											onclick="deleteAkte(\''.$akte->akte_id.'\',\''.$akte->dokument_kurzbz.'\', '.$anzahlDokumenteJeTyp.')">
										<span class="glyphicon glyphicon-remove" aria-hidden="true" title="'.$p->t('global/löschen').'"></span>
									</button>
									<span class="label label-warning">'.$p->t('bewerbung/dokumentWirdGeprueft').'</span>';
			}
			else
			{
				$returnstring .= '<br><span class="label label-success">'.$p->t('bewerbung/dokumentUeberprueft').'</span>';
			}
			$returnstring .= '	</div>';
		}
	}
	$returnstring .= '</div>';
	return $returnstring;
}
/**
 * Liefert den String mit Upload-Button und ggf. Nachreich-Button
 *
 * @param string $dokument_kurzbz
 * @param boolean $nachreichbutton. Default false
 * @param boolean $visible. Default true. Wenn false, wird der Upload-Bereich ausgeblendet
 * @param integer $studiengang
 * @param boolean $ausstellungsdetails. Wenn true, wird das DropDown mit der Ausstellungsnation angezeigt
 *
 * @return string HTML-String mit Upload-Button und ggf. Nachreich-Button
 */
function getUploadButton($dokument_kurzbz, $nachreichbutton = false, $visible = true, $studiengang, $ausstellungsdetails = false)
{
	global $p, $sprache;
	$display = '';
	if ($visible === false)
	{
		$display = 'hidden';
	}
	$returnstring = '<form method="POST" enctype="multipart/form-data" action="'.$_SERVER['PHP_SELF'].'?active=dokumente&fileupload=true" class="form-horizontal documentUploadForm">';
	$returnstring .= '  <div class="dokumentUploadDiv_'.$dokument_kurzbz.' '.$display.'" >';
	// Lichtbilder werden gesondert behandelt
	if ($dokument_kurzbz == 'LichtbilXXX')
	{
		$returnstring .= '	<p><input class="imageselect" type="file" name="file" class="file" accept=".jpg, .jpeg" style="display: inline">
							<input type="hidden" name="dokumenttyp" value="'.$dokument_kurzbz.'"></p>';
	}
	else
	{
		$returnstring .= '	<p>
								<div class="input-group">
									<div class="input-group-btn">
										<span class="btn btn-primary btn-file">
											'.$p->t('bewerbung/durchsuchen').' <input type="file" name="file" class="fileselect" accept=".jpg, .jpeg, .pdf" style="display: inline">
										</span>
									</div>
									<input type="text" class="form-control selectedFile" readonly="">
									<input type="hidden" name="dokumenttyp" value="'.$dokument_kurzbz.'">
									<div class="input-group-btn">
										<button type="submit"
											name="submitfile"
											class="btn btn-labeled btn-default"
											disabled>
											'.$p->t('bewerbung/upload').'
										</button>
									</div>

								</div>
							</p>';
	}
		//$returnstring .= '	<p class="help-block" >'.$p->t('bewerbung/ExtensionInformation').' JPG, PDF </p>';

	// Wenn das Attribut "Ausstellungsdetails" beim Dokument gesetzt ist, Länderdropdown anzeigen
	if ($ausstellungsdetails)
	{
		$nation = new nation();
		$nation->getAll($ohnesperre = true);
		$returnstring .= '	<p>
								<select name="ausstellungsnation" class="form-control ausstellungsnation" style="margin-top: 1em; display: block;" >
									<option value="">'. $p->t('bewerbung/bitteAusstellungsnationAuswaehlen') .'</option>
									<option value="A">'.($sprache=='German'? 'Österreich':'Austria').'</option>';
									$selected = '';
									foreach ($nation->nation as $nat)
									{
										//$selected = ($ausstellungsnation == $nat->code) ? 'selected="selected"' : '';
										$returnstring .= '<option value="'.$nat->code.'" '.$selected.'>';

										if ($sprache == 'German')
											$returnstring .= $nat->langtext;
										else
											$returnstring .= $nat->engltext;

										$returnstring .= '</option>';
									}
			$returnstring .= '</select>
							</p>';
	}

	if ((!defined('BEWERBERTOOL_DOKUMENTE_NACHREICHEN') || BEWERBERTOOL_DOKUMENTE_NACHREICHEN == true) && $nachreichbutton)
	{
		$returnstring .= '	<p class="text-muted">
								'.$p->t('bewerbung/dokumentNochNichtVorhanden').'
								<a href="#" onclick="toggleNachreichdaten(\''.$studiengang.'_'.$dokument_kurzbz.'\');return false;">
									'.$p->t('bewerbung/dokumentWirdNachgereicht').'
								</a>
							</p>';
	}
	$returnstring .= '	</div>';
	$returnstring .= '</form>';
	return $returnstring;
}

/**
 * Liefert den String mit den Optionen zum Nachreichen
 *
 * @param string $dokument_kurzbz
 * @param integer $studiengang
 *
 * @return string HTML-String mit Upload-Button und ggf. Nachreich-Button
 */
function getNachreichForm($dokument_kurzbz, $studiengang)
{
	global $p, $datum;

	$returnstring = '<form method="POST" enctype="multipart/form-data" action="'.$_SERVER['PHP_SELF'].'?active=dokumente" class="form-horizontal">';
	$returnstring .=    $p->t('bewerbung/placeholderAnmerkungNachgereicht').'
						';

	$returnstring .= '		<div class="form-group">
								<div class="row">
									<div class="col-sm-12">
										<input type="checkbox" name="check_nachgereicht" checked="checked" style="display:none">
										<div class="col-sm-8">
											<div class="input-group">
												<input  type="text"
														class="form-control"
														id="anmerkung_'.$studiengang.'_'.$dokument_kurzbz.'"
														name="txt_anmerkung"
														onInput="zeichenCountdown(\'anmerkung_'.$studiengang.'_'.$dokument_kurzbz.'\',128)"
														placeholder="'.$p->t('bewerbung/placeholderOrtNachgereicht').'">
												<span class="input-group-addon" style="color: grey;" id="countdown_anmerkung_'.$studiengang.'_'.$dokument_kurzbz.'">128</span>
											</div>
										</div>
										<div class="col-sm-4">
											<input  type="text"
													class="form-control"
													id="nachreichungam_'.$studiengang.'_'.$dokument_kurzbz.'"
													name="nachreichungam"
													autofocus="autofocus"
													placeholder="'.$p->t('bewerbung/datumFormat').'">
										</div>
									</div>
								</div><br>
								<div class="row">
									<div class="col-sm-12">';

	// An der FHTW wird beim nachreichen des Dokuments "zgv_bakk", "zgv_mast" und "SprachB2" ein vorläufiges ZGV-Dokument verlangt
	// Die Spaltenbreite wird daher angepasst
	$colspan = 12;
	if (CAMPUS_NAME == 'FH Technikum Wien' && ($dokument_kurzbz == 'zgv_bakk' || $dokument_kurzbz == 'zgv_mast' || $dokument_kurzbz == 'SprachB2'))
	{
		$returnstring .= '				<div class="col-sm-8">';

		if ($dokument_kurzbz == 'zgv_mast')
			$returnstring .= '<span>'.$p->t('bewerbung/infotextVorlaeufigesZgvDokumentMast').'</span>';
		else if ($dokument_kurzbz == 'SprachB2')
			$returnstring .= '<span>'.$p->t('bewerbung/infotextVorlaeufigesSprachB2').'</span>';
		else
			$returnstring .= '<span>'.$p->t('bewerbung/infotextVorlaeufigesZgvDokument').'</span>';

		$returnstring.= '<input  id="filenachgereicht_'.$studiengang.'_'.$dokument_kurzbz.'"
													type="file"
													name="filenachgereicht"
													class=""
													accept=".jpg, .jpeg, .pdf"
													style="display: inline">
										</div>';
		$colspan = 4;
	}

		$returnstring .= '				<div class="col-sm-'.$colspan.'">
											<div class="btn-group pull-right">
												<input  type="submit"
														value="OK"
														name="submit_nachgereicht"
														class="btn btn-primary"
														onclick="return checkNachgereicht(\''.$studiengang.'_'.$dokument_kurzbz.'\')">
												<input  type="button"
														value="'.$p->t('global/abbrechen').'"
														class="btn btn-default"
														onclick="toggleNachreichdaten(\''.$studiengang.'_'.$dokument_kurzbz.'\');return false;">
											</div>
										</div>
									</div>
								</div>
							</div>';

	$returnstring .= '		<input type="hidden" name="dok_kurzbz" value="'.$dokument_kurzbz.'">';

	$returnstring .= '	';
	$returnstring .= '</form>';
	return $returnstring;
}

function resize($filename, $width, $height)
{
	$ext = explode('.', $_FILES['file']['name']);
	$ext = mb_strtolower($ext[count($ext) - 1]);

	// Hoehe und Breite neu berechnen
	list ($width_orig, $height_orig) = getimagesize($filename);

	if ($width && ($width_orig < $height_orig))
	{
		$width = ($height / $height_orig) * $width_orig;
	}
	else
	{
		$height = ($width / $width_orig) * $height_orig;
	}

	$image_p = imagecreatetruecolor($width, $height);

	$image = imagecreatefromjpeg($filename);

	// Bild nur verkleinern aber nicht vergroessern
	if ($width_orig > $width || $height_orig > $height)
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
	else
		$image_p = $image;

	$tmpfname = tempnam(sys_get_temp_dir(), 'FHC');

	imagejpeg($image_p, $tmpfname, 80);

	imagedestroy($image_p);
	@imagedestroy($image);
	return $tmpfname;
}

/**
 * führt Aktionen für die Masterzentralisierung aus
 *
 * @param int $person_id PersonenID.
 * @return boolean true wenn Prüfung ob interne ZGV vorhanden ist bzw. Aktionen erfolgreich durchgeführt wurden,
 * false wenn nicht
 */
function setDokumenteMasterZGV($person_id)
{
	$prestudent = new prestudent();
	if (! $prestudent->getPrestudenten($person_id))
	{
		die($p->t('global/fehlerBeimLadenDesDatensatzes'));
	}

	//Prüfung ob es zur betreffenden $person_id bereits eine interne ZGV gibt
	$zgvFHTW = $prestudent->existsZGVIntern($person_id);

	if ($zgvFHTW)
	{
		$zgvMaster = new dokument();
		$person = new person();
		$person->load($person_id);

		//Dokumente akzeptieren
		$zgvMaster ->akzeptiereDokument('zgv_mast', $person_id);
		$zgvMaster ->akzeptiereDokument('zgv_bakk', $person_id);
		$zgvMaster ->akzeptiereDokument('identity', $person_id);
		$zgvMaster ->akzeptiereDokument('SprachB2', $person_id);
		$zgvMaster ->akzeptiereDokument('Statisti', $person_id);
		$zgvMaster ->akzeptiereDokument('ecard', $person_id);


		//Dokumente entakzeptieren
		$zgvMaster ->entakzeptiereDokument('Meldezet', $person_id);

		//ZGVMasterOrt abfragen
		$ort = 'FHTW ';
		$ort .= $prestudent ->getZGVMasterStg($person_id);

		//Masternation, -art und -ort befüllen
		$prestudent ->setZGVMasterFields($person_id, $ort);
	}
	return true;
}
