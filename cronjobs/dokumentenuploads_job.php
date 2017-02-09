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
require_once('../../../include/phrasen.class.php');
require_once('../../../include/person.class.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/mail.class.php');
require_once('../../../include/prestudent.class.php');
require_once('../../../include/preinteressent.class.php');
require_once('../../../include/kontakt.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/sprache.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../include/functions.inc.php');
require_once('../bewerbung.config.inc.php');

$db = new basis_db();
$studiengaenge = array();
$stg_kz = '';
$mailcontent = '';
$person = '';
$dokument = '';
$zeile = '';

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
	person_id,
	tbl_prestudent.insertamum,
	vorname,
	nachname,
	gebdatum,
	geschlecht,
	dokument_kurzbz,
	tbl_dokument.bezeichnung AS dokumentbezeichnung,
	tbl_akte.bezeichnung AS dateiname,
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
AND (SELECT get_rolle_prestudent(tbl_prestudent.prestudent_id, NULL)) != 'Abgewiesener'
ORDER BY studiengang_kz, nachname, vorname, person_id";
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
			
			if ($stg_kz != '' && $stg_kz != $row->studiengang_kz)
			{
				$mailcontent .= $zeile;
				$dokumentenliste = '';
				$dokument = '';
				$mailcontent .= '</tbody></table>';
	
				$person = '';
				$dokument = '';
				$mailcontent = wordwrap($mailcontent,70);
				
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
	
				$mail = new mail($empfaenger, 'no-reply', 'Neue Dokumentenuploads '.$bezeichnung, 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Inhalt vollständig darzustellen.');
				$mail->setHTMLContent($mailcontent);
				$mail->send();
	
				$mailcontent = $mailtext;
			}
			
			if ($dokument != $row->dokument_kurzbz)
			{
				if ($db->db_parse_bool($row->nachgereicht) == true)
					$dokumentenliste .= $row->dokumentbezeichnung.' (Wird nachgereicht: '.$row->anmerkung.')<br>';
				else 
					$dokumentenliste .= '<a href="'.APP_ROOT.'cms/dms.php?id='.$row->dms_id.'">'.$row->dokumentbezeichnung.' ['.$row->dateiname.']</a><br>';
				
				$dokument = $row->dokument_kurzbz;
			}
			
			$kontakt = new kontakt();
			$kontakt->load_persKontakttyp($row->person_id, 'email');
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
		}
		$mailcontent .= $zeile;
		$mailcontent .= '</tbody></table>';
		$mailcontent = wordwrap($mailcontent,70);
			
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

		$mail = new mail($empfaenger, 'no-reply', 'Neue Dokumentenuploads '.$bezeichnung, 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Inhalt vollständig darzustellen.');
		$mail->setHTMLContent($mailcontent);
		$mail->send();
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
