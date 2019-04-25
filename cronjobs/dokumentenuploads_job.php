<?php
/* Copyright (C) 2015 FH Technikum-Wien
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
 * Authors: Manfred kindl <manfred.kindl@technikum-wien.at>
 */

require_once('../../../config/global.config.inc.php');
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/mail.class.php');
require_once('../../../include/kontakt.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../include/functions.inc.php');
require_once('../bewerbung.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');

$db = new basis_db();
$studiengaenge = array();
$stg_kz = '';
$orgform = '';
$mailcontent = '';
$person = '';
$dokument = '';
$zeile = '';

// Wenn das Script ueber die Kommandozeile aufgerufen wird, erfolgt keine Authentifizierung
if (php_sapi_name() != 'cli')
{
	$uid = get_uid();
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($uid);

	if(!$rechte->isBerechtigt('admin'))
	{
		exit($rechte->errormsg);
	}
}

// Prueft, ob das Logverzeichnis existiert.
// Wenn nicht, wird versucht, eines anzulegen.
// Falls dies fehl schlaegt, wird kein Logfile erstellt.
$write_log = true;
if(!is_dir(LOG_PATH.'bewerbungstool/dokumentenuploads/'))
{
	if (mkdir(LOG_PATH.'bewerbungstool',0777,true))
	{
		if(!is_dir(LOG_PATH.'bewerbungstool/dokumentenuploads/'))
		{
			if (mkdir(LOG_PATH.'bewerbungstool/dokumentenuploads',0777,true))
				$write_log = true;
			else
				$write_log = false;
		}
	}
	else
		$write_log = false;
}
// Aus Datenschutzgründen werden Logfiles älter als 3 Monate gelöscht
$dateLess3Months = date("Y_m", strtotime("-3 months"));

if (file_exists(LOG_PATH.'bewerbungstool/dokumentenuploads/'.$dateLess3Months.'_log.html'))
{
	unlink(LOG_PATH.'bewerbungstool/dokumentenuploads/'.$dateLess3Months.'_log.html');
}

$logfile = LOG_PATH.'bewerbungstool/dokumentenuploads/'.date('Y_m').'_log.html';
$logcontent = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
				<h2 style="text-align: center">'.date('Y-m-d').'</h2><hr>';

$studiensemester = new studiensemester();
$studiensemester->getPlusMinus(10, 2);

$studiensemester_arr = array();
foreach ($studiensemester->studiensemester AS $row)
	$studiensemester_arr[] = $row->studiensemester_kurzbz;

$empf_array = array();
if(defined('BEWERBERTOOL_BEWERBUNG_EMPFAENGER'))
	$empf_array = unserialize(BEWERBERTOOL_BEWERBUNG_EMPFAENGER);

$qry = "
SELECT DISTINCT
	studiengang_kz,
	tbl_prestudentstatus.orgform_kurzbz,
	person_id,
	tbl_prestudent.insertamum,
	vorname,
	nachname,
	gebdatum,
	geschlecht,
	dokument_kurzbz,
	tbl_dokument.bezeichnung AS dokumentbezeichnung,
	tbl_akte.bezeichnung AS dateiname,
	tbl_akte.titel,
	tbl_akte.akte_id,
	dms_id,
	nachgereicht,
	tbl_akte.anmerkung
FROM
	public.tbl_prestudent
JOIN
	public.tbl_person USING (person_id)
JOIN
	public.tbl_prestudentstatus USING (prestudent_id)
JOIN
	public.tbl_akte USING (person_id)
JOIN
	public.tbl_dokument USING (dokument_kurzbz)
WHERE
	tbl_akte.insertvon='online'
AND (tbl_akte.insertamum >= (SELECT (CURRENT_DATE -1||' '||'03:00:00')::timestamp))
AND tbl_prestudentstatus.bestaetigtam IS NOT NULL
AND studiensemester_kurzbz IN (".$db->implode4SQL($studiensemester_arr).")
AND (SELECT get_rolle_prestudent(tbl_prestudent.prestudent_id, NULL)) NOT IN ('Abgewiesener', 'Abbrecher', 'Absolvent')
AND dokument_kurzbz NOT IN ('zgv_bakk', 'identity', 'SprachB2')

-- Upload nach Nachreichung
UNION

SELECT DISTINCT
	studiengang_kz,
	tbl_prestudentstatus.orgform_kurzbz,
	person_id,
	tbl_prestudent.insertamum,
	vorname,
	nachname,
	gebdatum,
	geschlecht,
	dokument_kurzbz,
	tbl_dokument.bezeichnung AS dokumentbezeichnung,
	tbl_akte.bezeichnung AS dateiname,
	tbl_akte.titel,
	tbl_akte.akte_id,
	dms_id,
	nachgereicht,
	tbl_akte.anmerkung 
FROM
	public.tbl_prestudent
JOIN
	public.tbl_person USING (person_id)
JOIN
	public.tbl_prestudentstatus USING (prestudent_id)
JOIN
	public.tbl_akte USING (person_id)
JOIN
	public.tbl_dokument USING (dokument_kurzbz)
WHERE
	tbl_akte.updatevon='online'
AND (tbl_akte.updateamum >= (SELECT (CURRENT_DATE -1||' '||'03:00:00')::timestamp))
AND tbl_prestudentstatus.bestaetigtam IS NOT NULL
AND nachgereicht = FALSE
/*AND nachgereicht_am IS NOT NULL*/
AND (inhalt IS NOT NULL OR dms_id IS NOT NULL)
AND studiensemester_kurzbz IN (".$db->implode4SQL($studiensemester_arr).")
AND (SELECT get_rolle_prestudent(tbl_prestudent.prestudent_id, NULL)) NOT IN ('Abgewiesener', 'Abbrecher', 'Absolvent')

ORDER BY studiengang_kz, orgform_kurzbz, nachname, vorname, person_id";
//echo $qry;exit;
$mailtext = '
		<style type="text/css">
		.table1
		{
			font-size: small;
			cellpadding: 3px;
		}
		.table1 th
		{
			background: #DCE4EF;
			border: 1px solid #FFF;
			padding: 4px;
			text-align: left;
		}
		.table1 td
		{
			background-color: #EEEEEE;
			padding: 4px;
		 	vertical-align: top;
		}
		/* Optional mit Hover-Effekt. Ist eventuell unpraktisch
		.popup
		{
			display: none;
		}
		.hover
		{
			position: relative;
		}
		.hover:hover .popup
		{
			display: initial;
			z-index: 1;
		}*/
		</style>
		Folgende Personen haben gestern Dokumente hochgeladen:<br><br>
		<table class="table1">
		<thead>
		<tr>
			<th>Anrede</th>
			<th>Nachname</th>
			<th>Vorname</th>
			<th>Geburtsdatum</th>
			<th>Mailadresse</th>
			<th>Dokumente</th>
			<!--<th>DropDown</th>-->
		</tr>
		</thead>
		<tbody>';
if($result = $db->db_query($qry))
{
	if ($db->db_num_rows($result) > 0)
	{
		$mailcontent = $mailtext;
		$anzahl = $db->db_num_rows($result);
		$dokumentenliste = '';
		
		while($row = $db->db_fetch_object($result))
		{
			if ($person != '' && $person != $row->person_id)
			{
				$mailcontent .= $zeile;
				$dokumentenliste = '';
				$dokument = '';
				$zeile = '';
			}

			if (($stg_kz != '' && $stg_kz != $row->studiengang_kz) || ($orgform != '' && $orgform != $row->orgform_kurzbz))
			{
				$mailcontent .= $zeile;
				$dokumentenliste = '';
				$dokument = '';
				$mailcontent .= '</tbody></table>';
	
				$person = '';
				$dokument = '';

				$studiengang = new studiengang();
				if(!$studiengang->load($stg_kz))
					die($p->t('global/fehlerBeimLadenDesDatensatzes'));
				
				$bezeichnung = strtoupper($studiengang->typ.$studiengang->kurzbz);
				if(defined('BEWERBERTOOL_MAILEMPFANG') && BEWERBERTOOL_MAILEMPFANG!='')
					$empfaenger = BEWERBERTOOL_MAILEMPFANG;
				elseif(isset($empf_array[$stg_kz]))
					$empfaenger = $empf_array[$stg_kz];
				else
					$empfaenger = $studiengang->email;
				
				//Pfuschloesung fur BIF Dual
				if (CAMPUS_NAME=='FH Technikum Wien' && $stg_kz == 257 && $orgform == 'DUA')
					$empfaenger = 'info.bid@technikum-wien.at';

				if ($empfaenger == '')
				{
					if (defined('MAIL_ADMIN') && MAIL_ADMIN != '')
					{
						$empfaenger = MAIL_ADMIN;
						$mailcontentWithWarning = '<p style="color: red; font-weight: bold; padding: 10px 0">Kein Empfänger für diese Mail gefunden</p>';
						$mailcontentWithWarning .= $mailcontent;
						$mailcontent = $mailcontentWithWarning;
					}
					else
					{
						continue;
					}
				}

				$mailcontent = wordwrap($mailcontent,70);

				$mail = new mail($empfaenger, 'no-reply', 'Neue Dokumentenuploads '.$bezeichnung.' '.$orgform, 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Inhalt vollständig darzustellen.');
				//$mail->setBCCRecievers('kindlm@technikum-wien.at');
				$mail->setHTMLContent($mailcontent);
				$mail->send();
				
				if ($write_log)
				{
					$logcontent .= '<h3>Studiengang: '.$stg_kz.'</h3>';
					$logcontent .= 'Empfänger: '.$empfaenger.'<br>';
					$logcontent .= 'Betreff: Neue Dokumentenuploads '.$bezeichnung.' '.$orgform.'<br><br>';
					$logcontent .= $mailcontent;
					$logcontent .= '<hr>';
						
					// Schreibt den Inhalt in die Datei
					// unter Verwendung des Flags FILE_APPEND, um den Inhalt an das Ende der Datei anzufügen
					// und das Flag LOCK_EX, um ein Schreiben in die selbe Datei zur gleichen Zeit zu verhindern
					file_put_contents($logfile, $logcontent, FILE_APPEND | LOCK_EX);
						
					$logcontent = '';
				}
	
				$mailcontent = $mailtext;
			}
			
			$dateiname = '';
			if ($row->dateiname == '')
			{
				if ($row->titel != '')
					$dateiname = $row->titel;
			}
			else 
				$dateiname = $row->dateiname;
			
			if ($dokument != $row->dokument_kurzbz)
			{
				if ($db->db_parse_bool($row->nachgereicht) == true)
					$dokumentenliste .= $row->dokumentbezeichnung.' (Wird nachgereicht: '.$row->anmerkung.')<br>';
				else
				{
					if ($row->dokument_kurzbz == 'Lichtbil')
						$dokumentenliste .= '<a href="'.APP_ROOT.'content/bild.php?src=person&person_id='.$row->person_id.'">'.$row->dokumentbezeichnung.' ['.$dateiname.']</a><br>';
					else 
						$dokumentenliste .= '<a href="'.APP_ROOT.'content/akte.php?akte_id='.$row->akte_id.'">'.$row->dokumentbezeichnung.' ['.$dateiname.']</a><br>';
				}
				
				$dokument = $row->dokument_kurzbz;
			}
			
			$kontakt = new kontakt();
			$kontakt->load_persKontakttyp($row->person_id, 'email', 'zustellung DESC, updateamum DESC, insertamum DESC NULLS LAST');
			$mailadresse = isset($kontakt->result[0]->kontakt)?$kontakt->result[0]->kontakt:'';
			
			$zeile = '<tr class="hover">';
			$zeile .= '<td>'.($row->geschlecht=='m'?'Herr ':'Frau ').'</td>';
			$zeile .= '<td>'.$row->nachname.'</td>';
			$zeile .= '<td>'.$row->vorname.'</td>';
			$zeile .= '<td>'.date('d.m.Y', strtotime($row->gebdatum)).'</td>';
			$zeile .= '<td><a href="mailto:'.$mailadresse.'">'.$mailadresse.'</a></td>';
			$zeile .= '<td><div class="popup">';
			$zeile .= $dokumentenliste;
			$zeile .= '</div></td>';
			//$zeile .= '<td><select><option>Foo</option><option>Bar</option><option>FooBar</option></select></td>';
			$zeile .= '</tr>';
	
			$person = $row->person_id;
			$stg_kz = $row->studiengang_kz;
			$orgform = $row->orgform_kurzbz;
		}
		$mailcontent .= $zeile;
		$mailcontent .= '</tbody></table>';
			
		$studiengang = new studiengang();
		if(!$studiengang->load($stg_kz))
			die($p->t('global/fehlerBeimLadenDesDatensatzes'));

		$bezeichnung = strtoupper($studiengang->typ.$studiengang->kurzbz);
		if(defined('BEWERBERTOOL_MAILEMPFANG') && BEWERBERTOOL_MAILEMPFANG!='')
			$empfaenger = BEWERBERTOOL_MAILEMPFANG;
		elseif(isset($empf_array[$stg_kz]))
			$empfaenger = $empf_array[$stg_kz];
		else
			$empfaenger = $studiengang->email;

		//Pfuschloesung fur BIF Dual
		if (CAMPUS_NAME=='FH Technikum Wien' && $stg_kz == 257 && $orgform == 'DUA')
			$empfaenger = 'info.bid@technikum-wien.at';

		if ($empfaenger == '')
		{
			if (defined('MAIL_ADMIN') && MAIL_ADMIN != '')
			{
				$empfaenger = MAIL_ADMIN;
				$mailcontentWithWarning = '<p style="color: red; font-weight: bold; padding: 10px 0">Kein Empfänger für diese Mail gefunden</p>';
				$mailcontentWithWarning .= $mailcontent;
				$mailcontent = $mailcontentWithWarning;
			}
			else
			{
				exit();
			}
		}

		$mailcontent = wordwrap($mailcontent,70);

		$mail = new mail($empfaenger, 'no-reply', 'Neue Dokumentenuploads '.$bezeichnung.' '.$orgform, 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Inhalt vollständig darzustellen.');
		//$mail->setBCCRecievers('kindlm@technikum-wien.at');
		$mail->setHTMLContent($mailcontent);
		$mail->send();
		
		if ($write_log)
		{
			$logcontent .= '<h3>Studiengang: '.$stg_kz.'</h3>';
			$logcontent .= 'Empfänger: '.$empfaenger.'<br>';
			$logcontent .= 'Betreff: Neue Dokumentenuploads '.$bezeichnung.' '.$orgform.'<br><br>';
			$logcontent .= $mailcontent;
			$logcontent .= '<hr>';
		
			// Schreibt den Inhalt in die Datei
			// unter Verwendung des Flags FILE_APPEND, um den Inhalt an das Ende der Datei anzufügen
			// und das Flag LOCK_EX, um ein Schreiben in die selbe Datei zur gleichen Zeit zu verhindern
			file_put_contents($logfile, $logcontent, FILE_APPEND | LOCK_EX);
		
			$logcontent = '';
		}
	}
}
else
{
	$mailcontent = '<h3>Fehler in Cronjob "addons/bewerbung/cronjobs/dokumentenuploads_job.php"</h3><br><br><b>'.$db->errormsg.'</b>';
	$mail = new mail(MAIL_ADMIN, 'no-reply', 'Fehler in Cronjob "addons/bewerbung/cronjobs/dokumentenuploads_job.php"', 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Inhalt vollständig darzustellen.');
	$mail->setHTMLContent($mailcontent);
	$mail->send();
}
?>
