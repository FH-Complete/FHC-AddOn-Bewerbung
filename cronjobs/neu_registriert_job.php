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
require_once('../include/functions.inc.php');
require_once('../bewerbung.config.inc.php');

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

// An der FHTW werden alle Bachelor-Studiengänge über das Infocenter abgewickelt
$qry = "
SELECT 
	person_id,
	studiengang_kz,
	tbl_prestudentstatus.orgform_kurzbz,
	tbl_prestudent.insertamum,
	vorname,
	nachname,
	gebdatum,
	geschlecht,
	studiensemester_kurzbz,
	tbl_studienplan.bezeichnung AS studienplan
FROM 
	public.tbl_prestudent 
JOIN
	public.tbl_person USING (person_id)
JOIN
	public.tbl_prestudentstatus USING (prestudent_id)
JOIN
	lehre.tbl_studienplan USING (studienplan_id)
JOIN
	public.tbl_studiengang USING (studiengang_kz)
WHERE 
	tbl_prestudent.insertvon='online' 
AND (tbl_prestudent.insertamum >= (SELECT (CURRENT_DATE -1||' '||'03:00:00')::timestamp)
	OR tbl_prestudentstatus.insertamum >= (SELECT (CURRENT_DATE -1||' '||'03:00:00')::timestamp))
AND tbl_prestudentstatus.status_kurzbz = 'Interessent'
AND tbl_prestudentstatus.bewerbung_abgeschicktamum IS NULL
AND tbl_studiengang.typ != 'b' AND tbl_studiengang.typ != 'm'
AND get_rolle_prestudent (tbl_prestudent.prestudent_id, studiensemester_kurzbz) != 'Abgewiesener'
ORDER BY studiengang_kz, studiensemester_kurzbz, orgform_kurzbz, nachname, vorname";

$db = new basis_db();
$studiengaenge = array();
$stg_kz = '';
$orgform = '';
$mailcontent = '';
$studiensemester = '';
$mail_alle = '';

// Prueft, ob das Logverzeichnis existiert.
// Wenn nicht, wird versucht, eines anzulegen.
// Falls dies fehl schlaegt, wird kein Logfile erstellt.
$write_log = true;
if(!is_dir(LOG_PATH.'bewerbungstool/neu_registriert/'))
{
	if (mkdir(LOG_PATH.'bewerbungstool',0777,true))
	{
		if(!is_dir(LOG_PATH.'bewerbungstool/neu_registriert/'))
		{
			if (mkdir(LOG_PATH.'bewerbungstool/neu_registriert',0777,true))
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

if (file_exists(LOG_PATH.'bewerbungstool/neu_registriert/'.$dateLess3Months.'_log.html'))
{
	unlink(LOG_PATH.'bewerbungstool/neu_registriert/'.$dateLess3Months.'_log.html');
}

$logfile = LOG_PATH.'bewerbungstool/neu_registriert/'.date('Y_m').'_log.html';
$logcontent = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
				<h2 style="text-align: center">'.date('Y-m-d').'</h2><hr>';

$empf_array = array();
if(defined('BEWERBERTOOL_BEWERBUNG_EMPFAENGER'))
	$empf_array = unserialize(BEWERBERTOOL_BEWERBUNG_EMPFAENGER);

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
		}
		</style>
		Folgende Personen haben sich gestern registriert, ihre Bewerbung aber noch nicht abgeschickt:<br><br>';
if($result = $db->db_query($qry))
{
	if ($db->db_num_rows($result) > 0)
	{
		$mailcontent = $mailtext;
		$anzahl = $db->db_num_rows($result);
		while($row = $db->db_fetch_object($result))
		{
			if (($stg_kz != '' && $stg_kz != $row->studiengang_kz) || ($orgform != '' && $orgform != $row->orgform_kurzbz))
			{
				$mailcontent .= '</tbody></table>';
				$mailcontent .= '<a href="mailto:?BCC='.$mail_alle.'">Mail an alle</a>';
				$studiensemester = '';

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

				//Pfuschloesung fur BIF Dual
				if (CAMPUS_NAME=='FH Technikum Wien' && $stg_kz == 257 && $orgform == 'DUA')
					$empfaenger = 'info.bid@technikum-wien.at';
				
				$mail = new mail($empfaenger, 'no-reply', 'Neu registriert '.$bezeichnung.' '.$orgform, 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Inhalt vollständig darzustellen.');
				//$mail->setBCCRecievers('kindlm@technikum-wien.at');
				$mail->setHTMLContent($mailcontent);
				$mail->send();
				
				if ($write_log)
				{
					$logcontent .= '<h3>Studiengang: '.$stg_kz.'</h3>';
					$logcontent .= 'Empfänger: '.$empfaenger.'<br>';
					$logcontent .= 'Betreff: Neu registriert '.$bezeichnung.' '.$orgform.'<br><br>';
					$logcontent .= $mailcontent;
					$logcontent .= '<hr>';
					
					// Schreibt den Inhalt in die Datei
					// unter Verwendung des Flags FILE_APPEND, um den Inhalt an das Ende der Datei anzufügen
					// und das Flag LOCK_EX, um ein Schreiben in die selbe Datei zur gleichen Zeit zu verhindern
					file_put_contents($logfile, $logcontent, FILE_APPEND | LOCK_EX);
					
					$logcontent = '';
				}
	
				$mailcontent = $mailtext;
				$mail_alle = '';
			}

			if ($row->studiensemester_kurzbz != '' && $studiensemester != $row->studiensemester_kurzbz)
			{
				if ($studiensemester != '')
				{
					$mailcontent .= '</tbody></table>';
					$mailcontent .= '<a href="mailto:?BCC='.$mail_alle.'">Mail an alle</a>';
					$mail_alle = '';
				}

				$mailcontent .= '<h3>'.$row->studiensemester_kurzbz.' '.$row->orgform_kurzbz.'</h3>';
				$mailcontent .= '<table class="table1"><thead><tr>
								<th>Studienplan</th>
								<th>Anrede</th>
								<th>Nachname</th>
								<th>Vorname</th>
								<th>Geburtsdatum</th>
								<th>Mailadresse</th>
								</thead><tbody>';
				$studiensemester = $row->studiensemester_kurzbz;
			}
			$kontakt = new kontakt();
			$kontakt->load_persKontakttyp($row->person_id, 'email', 'zustellung DESC, updateamum DESC, insertamum DESC NULLS LAST');
			$mailadresse = isset($kontakt->result[0]->kontakt)?$kontakt->result[0]->kontakt:'';
			
			$mailcontent .= '<tr>';
			$mailcontent .= '<td>'.($row->studienplan != ''?$row->studienplan:'<span style="color: red">Es konnte kein passender Studienplan ermittelt werden</span>').'</td>';
			$mailcontent .= '<td>'.($row->geschlecht=='m'?'Herr ':'Frau ').'</td>';
			$mailcontent .= '<td>'.$row->nachname.'</td>';
			$mailcontent .= '<td>'.$row->vorname.'</td>';
			$mailcontent .= '<td>'.date('d.m.Y', strtotime($row->gebdatum)).'</td>';
			$mailcontent .= '<td><a href="mailto:'.$mailadresse.'">'.$mailadresse.'</a></td>';
			$mailcontent .= '</tr>';
			
			$mail_alle .= $mailadresse.';';
	
			$stg_kz = $row->studiengang_kz;
			$orgform = $row->orgform_kurzbz;
		}
		$mailcontent .= '</tbody></table>';
		$mailcontent .= '<a href="mailto:?BCC='.$mail_alle.'">Mail an alle</a>';
			
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
		
		//Pfuschloesung fur BIF Dual
		if (CAMPUS_NAME=='FH Technikum Wien' && $stg_kz == 257 && $orgform == 'DUA')
			$empfaenger = 'info.bid@technikum-wien.at';

		$mail = new mail($empfaenger, 'no-reply', 'Neu registriert '.$bezeichnung.' '.$orgform, 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Inhalt vollständig darzustellen.');
		//$mail->setBCCRecievers('kindlm@technikum-wien.at');
		$mail->setHTMLContent($mailcontent);
		$mail->send();
		
		if ($write_log)
		{
			$logcontent .= '<h3>Studiengang: '.$stg_kz.'</h3>';
			$logcontent .= 'Empfänger: '.$empfaenger.'<br>';
			$logcontent .= 'Betreff: Neu registriert '.$bezeichnung.' '.$orgform.'<br><br>';
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
	$mailcontent = '<h3>Fehler in Cronjob "addons/bewerbung/cronjobs/neu_registriert_job.php"</h3><br><br><b>'.$db->errormsg.'</b>';
	$mail = new mail(MAIL_ADMIN, 'no-reply', 'Fehler in Cronjob "addons/bewerbung/cronjobs/neu_registriert_job.php"', 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Inhalt vollständig darzustellen.');
	$mail->setHTMLContent($mailcontent);
	$mail->send();
}
?>
