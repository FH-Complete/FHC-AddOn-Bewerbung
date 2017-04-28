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
require_once('../../../include/student.class.php');
require_once('../../../include/studienplan.class.php');

// Fuegt einen Studiengang zu einem Bewerber hinzu
function BewerbungPersonAddStudiengang($studiengang_kz, $anmerkung, $person, $studiensemester_kurzbz, $orgform_kurzbz)
{
	//PreStudent_id des aktuellsten PreStudenten (hoechste ID) ermitteln und Interessentenstatus zu diesem hinzufuegen,
	//sonst nach irgendeiner prestudent_id suchen, um dessen ZGV uebernehmen zu koennen.
	$student = new student();
	$std = $student->load_person($person->person_id, $studiengang_kz);
	$prestudent_id=0;
	$zgv_code = '';
	$zgvort = '';
	$zgvdatum = '';
	$zgvnation = '';
	$zgvmas_code = '';
	$zgvmaort = '';
	$zgvmadatum = '';
	$zgvmanation = '';
	
	$pre = new prestudent();
	$pre->getPrestudenten($person->person_id); //Alle Prestudenten der Person laden
	foreach ($pre->result AS $row)
	{
		//Wenn Person schon Prestudent in dem Studiengang war, hoechste prestudent_id ermittel (die nicht jene des Studenten-Datensatzes war) und bei diesem spaeter einen neuen Status hinzufuegen
		if($row->studiengang_kz==$studiengang_kz && $row->prestudent_id > $prestudent_id && $row->prestudent_id!=$student->prestudent_id)
			$prestudent_id=$row->prestudent_id;
	}
	//Wenn die Person noch kein Student in diesem Studiengang war, nach irgendeiner prestudent_id suchen, um dessen ZGV uebernehmen zu koennen
	if($prestudent_id==0)
	{
		if ($pre->result[0]->prestudent_id!='')
		{
			$prestudent_help=$pre->result[0]->prestudent_id;
		
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
	
	if($prestudent_id==0) //Wenn kein PreStudent-Datensatz gefunden wurde, neuen Prestudenten anlegen
	{
		if($std) //Wenn Person schon Student war, ZGV-Daten von dort holen
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
		$prestudent = new prestudent();
		
		$prestudent->studiengang_kz=$studiengang_kz;
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

		if(!$prestudent->save())
		{
			return $prestudent->errormsg;
		}
		
		$prestudent_id=$prestudent->prestudent_id;
	}

	// Richtigen Studienplan ermitteln
	// Wenn kein passender Studienplan gefunden wird, wird es weiter mit weniger Optionen probiert,
	// bis ein passender gefunden wurde. Wird gar kein Studienplan gefunden, wird er NULL gesetzt.
	$studienplan = new studienplan();
	$studienplan->getStudienplaeneFromSem($studiengang_kz, $studiensemester_kurzbz, '1', $orgform_kurzbz);
	if (!isset($studienplan->result[0]))
		$studienplan->getStudienplaeneFromSem($studiengang_kz, $studiensemester_kurzbz, '', $orgform_kurzbz);
	if (!isset($studienplan->result[0]))
		$studienplan->getStudienplaeneFromSem($studiengang_kz, '', '', $orgform_kurzbz);
	if (!isset($studienplan->result[0]))
		$studienplan->getStudienplaeneFromSem($studiengang_kz);
	
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

	if(!$prestudent_status->save_rolle())
	{
		return $prestudent_status->errormsg;
	}
	
	/* Durch das Einrichten des Cronjobs sollte sich das erledigt haben
	if(CAMPUS_NAME=='FH Technikum Wien')
	{
		if(!sendAddStudiengang($prestudent_id, $studiensemester_kurzbz, $orgform_kurzbz))
			return 'Senden der Mail fehlgeschlagen';
	}*/
	
	return true;
}
/**
 * Prueft, ob fuer die uebergebene Mailadresse, schon eine Person im System ist und laedt ggf. den entsprechenden Personendatensatz.
 * Optional kann eine studiensemester_kurzbz uebergeben werden. Dann wird ueber PreStudentstatus gejoined und nur ein bestimmtes Studiensemester ueberprueft.
 * @param string $mailadresse Zu pruefende E-Mail-Adresse.
 * @param string $studiensemester_kurzbz. Optional. Studiensemester fuer welches eine Bewerbung vorliegt.
 * @return person_id und zugangscode; False im Fehlerfall
 */
function check_load_bewerbungen($mailadresse,$studiensemester_kurzbz=null)
{
	$mailadresse = strtolower(trim($mailadresse));
	$db = new basis_db();
	
	$qry = "SELECT DISTINCT tbl_person.person_id,tbl_person.zugangscode,tbl_person.insertamum
				FROM public.tbl_kontakt 
					JOIN public.tbl_person USING (person_id) 
					LEFT JOIN public.tbl_benutzer USING (person_id) ";
			if ($studiensemester_kurzbz!='')
				$qry .= "	JOIN public.tbl_prestudent USING (person_id) 
							JOIN public.tbl_prestudentstatus USING (prestudent_id) ";
			$qry .= "
				WHERE kontakttyp='email' 
				AND (	LOWER(kontakt)=".$db->db_add_param($mailadresse, FHC_STRING)." 
						OR LOWER(alias||'@".DOMAIN."')=".$db->db_add_param($mailadresse, FHC_STRING)."
			 			OR LOWER(uid||'@".DOMAIN."')=".$db->db_add_param($mailadresse, FHC_STRING).")";
				if ($studiensemester_kurzbz!='')
					$qry .= " AND studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz, FHC_STRING);
				
				$qry .= " ORDER BY tbl_person.insertamum DESC LIMIT 1;";

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

/**
 * Prueft, ob eine Person schon einen Bewerbung abgeschickt hat. Notwendig um herauszufinden, ob die Eingabe der Stammdaten gesperrt werden soll.
 * Optional kann eine studiensemester_kurzbz uebergeben werden, ob speziell dafuer schon eine Bewerbung abgeschickt wurde.
 * @param integer $person_id Zu pruefende Person.
 * @param string $studiensemester_kurzbz. Optional. Studiensemester fuer welches eine abgeschickte Bewerbung vorliegt.
 * @return true, wenn vorhanden, false im Fehlerfall
 */
function check_person_bewerbungabgeschickt($person_id,$studiensemester_kurzbz=null)
{
	$db = new basis_db();

	$qry = "SELECT *
			FROM PUBLIC.tbl_person
			JOIN PUBLIC.tbl_prestudent USING (person_id)
			JOIN PUBLIC.tbl_prestudentstatus USING (prestudent_id)
			WHERE person_id=".$db->db_add_param($person_id, FHC_INTEGER)."
				AND status_kurzbz = 'Interessent'
				AND bewerbung_abgeschicktamum IS NOT NULL";

	if($studiensemester_kurzbz!='')
		$qry .= " AND studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz, FHC_STRING);

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>0)
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
 * Prueft, ob der uebergebene Status der Person (irgendeines PreStudenten) bestaetigt ist.
 * Optional kann eine studiensemester_kurzbz uebergeben werden, ob speziell dafuer schon eine Bestaetigung vorliegt.
 * @param integer $person_id Zu pruefende Person.
 * @param string $status_kurzbz Status_kurzbz, welche geprueft werden soll zB "Interessent".
 * @param string $studiensemester_kurzbz. Optional. Studiensemester fuer welches eine Bewerbung vorliegt.
 * @return true, wenn vorhanden, false im Fehlerfall
 */
function check_person_statusbestaetigt($person_id,$status_kurzbz,$studiensemester_kurzbz=null)
{
	$db = new basis_db();

	$qry = "SELECT *
			FROM PUBLIC.tbl_person
			JOIN PUBLIC.tbl_prestudent USING (person_id)
			JOIN PUBLIC.tbl_prestudentstatus USING (prestudent_id)
			WHERE person_id=".$db->db_add_param($person_id, FHC_INTEGER)."
				AND status_kurzbz = ".$db->db_add_param($status_kurzbz, FHC_STRING)."
				AND bestaetigtam IS NOT NULL";

	if($studiensemester_kurzbz!='')
		$qry .= " AND studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz, FHC_STRING);

		if($result = $db->db_query($qry))
		{
			if($db->db_num_rows($result)>0)
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
 * Holt die aktiven Studienplaene. Optional $studiengang_kz eines bestimmten Studiengangs, optional $studiensemester_kurzbz eines bestimmten Studiensemesters
 * optional $ausbildungssemester eines bestimmten Ausbildungssemesters, optional $orgform_kurzbz einer bestimmten Orgform
 * @param integer $studiengang_kz optional
 * @param array $studiensemester_kurzbz Array von Studiensemestern, in deren Gueltigkeit die Studienplaene liegen
 * @param string $ausbildungssemester Kommaseparierter String mit Ausbildungssemestern, in deren Gueltigkeit die Studienplaene liegen
 * @param string $orgform_kurzbz optional
 */
function getStudienplaeneForOnlinebewerbung($studiengang_kz=null, $studiensemester_kurzbz=null, $ausbildungssemester=null, $orgform_kurzbz=null)
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
					tbl_studienplan.aktiv";

	if($studiengang_kz!='')
	{
		$qry.=" AND tbl_studienordnung.studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER);
	}
	if($studiensemester_kurzbz!='')
	{
		$qry.=" AND tbl_studienplan_semester.studiensemester_kurzbz IN (".$db->implode4SQL($studiensemester_kurzbz).")";
	}
	if($ausbildungssemester!='')
	{
		$ausbildungssemester = explode(',', $ausbildungssemester);
		$qry.=" AND tbl_studienplan_semester.semester IN (".$db->implode4SQL($ausbildungssemester).")";
	}	
	if($orgform_kurzbz!='')
	{
		$qry.=" AND orgform_kurzbz=".$db->db_add_param($orgform_kurzbz);
	}

	if($result = $db->db_query($qry))
	{
		$db->result = '';
		while($row = $db->db_fetch_object($result))
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
			
			$obj->new=true;

			$db->result[] = $obj;
		}
		return $db->result;
	}
	else
		return false;
}
/**
 * Holt die vorkommenden Orgform/Sprache Kombinationen aus den aktiven Studienplänen
 * @param integer $studiengang_kz optional
 * @param array $studiensemester_kurzbz Array von Studiensemestern, in deren Gueltigkeit die Studienplaene liegen
 * @param string $ausbildungssemester Kommaseparierter String mit Ausbildungssemestern, in deren Gueltigkeit die Studienplaene liegen
 * @param string $orgform_kurzbz optional
 */
function getOrgformSpracheForOnlinebewerbung($studiengang_kz=null, $studiensemester_kurzbz=null, $ausbildungssemester=null, $orgform_kurzbz=null)
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
					tbl_studienplan.aktiv";

	if($studiengang_kz!='')
	{
		$qry.=" AND tbl_studienordnung.studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER);
	}
	if($studiensemester_kurzbz!='')
	{
		$studiensemester_kurzbz = $db->db_implode4SQL($studiensemester_kurzbz);
		$qry.=" AND tbl_studienplan_semester.studiensemester_kurzbz IN (".$studiensemester_kurzbz.")";
	}
	if($ausbildungssemester!='')
	{
		$ausbildungssemester = explode(',', $ausbildungssemester);
		$qry.=" AND tbl_studienplan_semester.semester IN (".$db->implode4SQL($ausbildungssemester).")";
	}
	if($orgform_kurzbz!='')
	{
		$qry.=" AND orgform_kurzbz=".$db->db_add_param($orgform_kurzbz);
	}
	$qry .= " ORDER by orgform_kurzbz,sprache";
	
	if($result = $db->db_query($qry))
	{
		$db->result='';
		while($row = $db->db_fetch_object($result))
		{
			$obj = new studienplan();
			$obj->orgform_kurzbz = $row->orgform_kurzbz;
			$obj->sprache = $row->sprache;
				
			$obj->new=true;

			$db->result[] = $obj;
		}
		return $db->result;
	}
	else
		return false;
}
/**
 * Laedt alle Gemeinden zu einer PLZ
 * @param integer $plz PLZ
 * @return Objekt mit den Gemeinden, sonst false 
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
					plz = ".$db->db_add_param($plz, FHC_INTEGER)."
				 ORDER BY anzahl DESC";
			
	if($result = $db->db_query($qry))
	{
		$db->result='';
		while($row = $db->db_fetch_object($result))
		{
			$obj = new stdClass();
			$obj->plz = $row->plz;
			$obj->gemeindename = $row->name;
			$obj->ortschaftskennziffer = $row->ortschaftskennziffer;
			$obj->ortschaftsname = $row->ortschaftsname;
			$obj->bulacode = $row->bulacode;
			$obj->bulabez = $row->bulabez;

			$obj->new=true;

			$db->result[] = $obj;
		}
		return $db->result;
	}
	else
		return false;
}
?>
