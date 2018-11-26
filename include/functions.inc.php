<?php
/*
 * Copyright (C) 2015 fhcomplete.org
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
 */
require_once ('../../../include/student.class.php');
require_once ('../../../include/studienplan.class.php');
require_once ('../../../include/studienordnung.class.php');
require_once ('../../../include/studiengang.class.php');
require_once ('../../../include/personlog.class.php');
require_once ('../../../config/global.config.inc.php');

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
		// Höchste Priorität in diesem Studiensemester laden und ggf. um 1 erhöhen
		$prestudent = new prestudent();
		$hoechstePrio = $prestudent->getHoechstePriorisierungPersonStudiensemester($person->person_id, $studiensemester_kurzbz);
		if ($hoechstePrio == '')
			$hoechstePrio = 0;
		
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
		$prestudent->priorisierung = $hoechstePrio+1;
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
function getBewerbungszeitraum($studiengang_kz, $studiensemester, $studienplan_id)
{
	global $p, $datum;
	$tage_bis_fristablauf = '';
	$fristAbgelaufen = false;
	$infoDiv = '';
	$bewerbungszeitraum = '';
	$class = '';
	$bewerbungsfrist = '';
	
	$bewerbungsfristen = new bewerbungstermin();
	$bewerbungsfristen->getBewerbungstermine($studiengang_kz, $studiensemester, 'insertamum DESC', $studienplan_id);
	
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
		// Wenn Nachfrist gesetzt und das Nachfrist-Datum befuellt ist, gilt die Nachfrist
		// sonst das Endedatum, wenn eines gesetzt ist
		if ($bewerbungsfristen->nachfrist == true && $bewerbungsfristen->nachfrist_ende != '')
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
				$infoDiv .= '<br/><div class="label label-warning">
											<span class="glyphicon glyphicon-warning-sign"></span>
											&nbsp;&nbsp;' . $p->t('bewerbung/bewerbungsfristEndetInXTagen', array(
					floor($tage_bis_fristablauf)
				)) . '</div>';
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
			// Wenn die Frist in weniger als 7 Tagen ablaeuft oder vorbei ist, hervorheben
			if ($tage_bis_fristablauf > 7)
			{
				$infoDiv = '| ' . $p->t('bewerbung/bewerbungsfrist') . ': ' . $datum->formatDatum($bewerbungsfristen->ende, 'd.m.Y');
			}
			if ($tage_bis_fristablauf <= 7)
			{
				$infoDiv = '| ' . $p->t('bewerbung/bewerbungsfrist') . ': ' . $datum->formatDatum($bewerbungsfristen->ende, 'd.m.Y');
				$infoDiv .= '<br/><div class="label label-warning">
											<span class="glyphicon glyphicon-warning-sign"></span>
											&nbsp;&nbsp;' . $p->t('bewerbung/bewerbungsfristEndetInXTagen', array(
					floor($tage_bis_fristablauf)
				)) . '</div>';
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
				) AS anzahl_akten_nachgereicht,
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
			
			$qry .= " ORDER BY dokument_kurzbz,
				pflicht DESC";

	if ($result = $db->db_query($qry))
	{
		while ($row = $db->db_fetch_object($result))
		{
			$dok = new stdClass();
			$dok->dokument_kurzbz = $row->dokument_kurzbz;
			$dok->bezeichnung = $row->bezeichnung;
			$dok->pflicht = $db->db_parse_bool($row->pflicht);
			$dok->nachreichbar = $db->db_parse_bool($row->nachreichbar);
			$dok->ausstellungsdetails = $db->db_parse_bool($row->ausstellungsdetails);
			$dok->anzahl_akten_vorhanden = $row->anzahl_akten_vorhanden;
			$dok->anzahl_akten_formal_geprueft = $row->anzahl_akten_formal_geprueft;
			$dok->anzahl_dokumente_akzeptiert = $row->anzahl_dokumente_akzeptiert;
			$dok->anzahl_akten_nachgereicht = $row->anzahl_akten_nachgereicht;
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
			$empfaenger = 'info.bid@technikum-wien.at';
		}
		else
			$empfaenger = $empf_array[$studiengang_kz];
	}
	else
		$empfaenger = $studiengang->email;

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
			tbl_studiengang.typ
			FROM public.tbl_prestudent 
			JOIN public.tbl_studiengang USING (studiengang_kz)
			WHERE person_id=".$db->db_add_param($person_id, FHC_INTEGER)." 
			ORDER BY priorisierung,tbl_prestudent.insertamum";

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
			SELECT studienplan_id
			FROM PUBLIC.tbl_person
			JOIN PUBLIC.tbl_prestudent USING (person_id)
			JOIN PUBLIC.tbl_prestudentstatus USING (prestudent_id)
			JOIN lehre.tbl_studienplan USING (studienplan_id)
			JOIN lehre.tbl_studienordnung USING (studienordnung_id)
			JOIN PUBLIC.tbl_studiengang ON (tbl_studienordnung.studiengang_kz = tbl_studiengang.studiengang_kz)
			WHERE person_id = " . $db->db_add_param($person_id, FHC_INTEGER) . "
				AND studiensemester_kurzbz = " . $db->db_add_param($studiensemester_kurzbz) . "
				AND tbl_studiengang.typ = 'b'
				AND bewerbung_abgeschicktamum IS NOT NULL
				AND bestaetigtam IS NOT NULL
				AND bestaetigtvon != ''
				AND (
					SELECT status_kurzbz
					FROM PUBLIC.tbl_prestudentstatus
					WHERE prestudent_id = tbl_prestudent.prestudent_id
						AND studiensemester_kurzbz = tbl_prestudentstatus.studiensemester_kurzbz
					ORDER BY datum DESC,
						tbl_prestudentstatus.insertamum DESC LIMIT 1
					) IN ('Interessent')
			ORDER BY priorisierung ASC LIMIT 1";

	if ($db->db_query($qry))
	{
		if ($row = $db->db_fetch_object())
		{
			return $row->studienplan_id;
		}
		else
		{
			$db->errormsg = 'Kein Studienplan gefunden';
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
 * Holt die nächsten Reihungstesttermine mit dem passenden Studienplan, für die sich ein Bewerber anmelden kann.
 * Der Termin muss das Attribut "öffentlich" auf TRUE haben und die Anmeldefrist muss <= dem heutigen Datum liegen.
 *
 * @param integer $studienplan_id Studienplan ID eines zugeteilten Studienplans.
 * @param string $studiensemester_kurzbz Studiensemester des Termins
 * @param string $stufe Optional. Default 1. Stufe, die der Termin haben soll.
 * 
 * @return TRUE, FALSE im Fehlerfall
 */
function getReihungstestsForOnlinebewerbung($studienplan_id, $studiensemester_kurzbz, $stufe = 1)
{
	$db = new basis_db();
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
						ELSE (
								SELECT sum(arbeitsplaetze) - (round((sum(arbeitsplaetze)::FLOAT / 100)::FLOAT * 5)) AS arbeitsplaetze
								FROM PUBLIC.tbl_rt_ort
								JOIN PUBLIC.tbl_ort USING (ort_kurzbz)
								WHERE rt_id = rt.reihungstest_id
								)
						END
					) AS anzahl_plaetze,
				(
					SELECT count(*)
					FROM PUBLIC.tbl_rt_person
					WHERE rt_id = rt.reihungstest_id
					) AS anzahl_anmeldungen,
				rt.*
			FROM PUBLIC.tbl_reihungstest rt
			JOIN PUBLIC.tbl_rt_studienplan USING (reihungstest_id)
			WHERE studienplan_id = " . $db->db_add_param($studienplan_id, FHC_INTEGER) . "
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
	$qry .= "	AND oeffentlich = true
				AND anmeldefrist >= now()
				
			ORDER BY datum,
				uhrzeit ";
// @todo: (stufe = 1 OR stufe IS NULL) ???
	if ($result = $db->db_query($qry))
	{
		$db->result = '';
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
 * Prüft, ob die Person im übergebenen Studiensemester bei einem PreStudent-Status den Statusgrund "Qualifikationskurs" hat (ID aus Config STATUSGRUND_ID_QUALIFIKATIONKURSTEILNEHMER)
 * Damit ist diese Person als Qualifikationskursteilnehmer gekennzeichnet
 *
 * @param integer $person_id
 * @param string $studiensemester_kurzbz Studiensemester der Bewerbung
 *
 * @return boolean True wenn Teilnehmer, FALSE wenn nicht
 */
function hasPersonStatusgrundQualikurs($person_id, $studiensemester_kurzbz)
{
	$db = new basis_db();
	if (!defined('STATUSGRUND_ID_QUALIFIKATIONKURSTEILNEHMER') || STATUSGRUND_ID_QUALIFIKATIONKURSTEILNEHMER == '')
	{
		$db->errormsg = 'Die Konstante STATUSGRUND_ID_QUALIFIKATIONKURSTEILNEHMER ist nicht definiert';
		return false;
	}
	$qry = "
			SELECT count(*) AS anzahl
			FROM PUBLIC.tbl_prestudent
			JOIN PUBLIC.tbl_prestudentstatus USING (prestudent_id)
			WHERE status_kurzbz = 'Interessent'
				AND studiensemester_kurzbz = " . $db->db_add_param($studiensemester_kurzbz) . "
				AND statusgrund_id = " . STATUSGRUND_ID_QUALIFIKATIONKURSTEILNEHMER . "
				AND person_id = " . $db->db_add_param($person_id, FHC_INTEGER);

	if ($db->db_query($qry))
	{
		if ($row = $db->db_fetch_object())
		{
			if ($row->anzahl >= 0)
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

