<?php
$this->phrasen['bewerbung/ungueltigerZugriff']='ungültiger Zugriff';
$this->phrasen['bewerbung/welcome']='Willkommen bei der Online Bewerbung';
$this->phrasen['bewerbung/welcomeHeaderLogin']='
								<div class="col-xs-3">
									<div style="text-align: center;">
										<img style="margin: 30px 10px; width: 100%%" src="' . APP_ROOT . 'skin/styles/' . DEFAULT_STYLE . '/logo_academy.png"/>
									</div>
								</div>
								<div style="text-align: center;" class="col-xs-6">
									<h1 style="margin: 30px 10px;">Willkommen bei der Online Bewerbung</h1>
								</div>
								<div class="col-xs-3">
									<div style="text-align: center;">
										<img style="margin: 30px 10px; width: 100%%" src="' . APP_ROOT . 'skin/styles/' . DEFAULT_STYLE . '/logo.png"/>
									</div>
								</div>';
$this->phrasen['bewerbung/welcomeHeaderRegistration']='
					<div class="row hidden-md hidden-lg">
						<div class="col-xs-5">
							<img style="width:150px;" class="center-block img-responsive" src="' . APP_ROOT . 'skin/styles/' . DEFAULT_STYLE . '/logo_academy.png">
						</div>
						<div class="col-xs-2">

						</div>
						<div class="col-xs-5">
							<img style="width:150px;" class="center-block img-responsive" src="' . APP_ROOT . 'skin/styles/' . DEFAULT_STYLE . '/logo.png">
						</div>
					</div>
					<div class="row hidden-md hidden-lg">
						<div class="col-xs-12">
							<h2 class="text-center">Willkommen bei der Online Bewerbung</h2>
						</div>
					</div>
					<div class="row hidden-xs hidden-sm">
						<div class="col-xs-2">
							<div style="text-align: center;">
								<img style="margin: 30px 10px; width: 100%%" src="' . APP_ROOT . 'skin/styles/' . DEFAULT_STYLE . '/logo_academy.png"/>
							</div>
						</div>
						<div style="text-align: center;" class="col-xs-8">
							<h1 style="margin: 30px 10px;">Willkommen bei der Online Bewerbung</h1>
						</div>
						<div class="col-xs-2">
							<div style="text-align: center;">
								<img style="margin: 30px 10px; width: 100%%" src="' . APP_ROOT . 'skin/styles/' . DEFAULT_STYLE . '/logo.png"/>
							</div>
						</div>
					</div>
					';
$this->phrasen['bewerbung/registration']='Zugangscode für Ihre Bewerbung';
$this->phrasen['bewerbung/registrieren']='Anmelden';
$this->phrasen['bewerbung/abschicken']='Abschicken';
$this->phrasen['bewerbung/registrierenOderZugangscode']='<a href="'.$_SERVER['PHP_SELF'].'?method=registration">Hier registrieren</a> oder Zugangscode eingeben';
$this->phrasen['bewerbung/einleitungstext']='Bitte füllen Sie das Formular aus, wählen Sie Ihren bevorzugten Studiengang oder Lehrgang und klicken Sie auf "Abschicken". <br>
		Danach erhalten Sie eine E-Mail mit Zugangscode an die angegebene Adresse.
		Mit dem Zugangscode können Sie sich jederzeit einloggen, Ihre Daten vervollständigen, Studienrichtungen hinzufügen und sich unverbindlich bewerben.<br><br>
		Falls Sie Interesse an mehreren Studiengängen haben, können Sie bis zu
		'.(defined('BEWERBERTOOL_MAX_STUDIENGAENGE') && BEWERBERTOOL_MAX_STUDIENGAENGE != ''?BEWERBERTOOL_MAX_STUDIENGAENGE:'').'
		Studienrichtungen auswählen. <br>Wenn Sie mehr Informationen benötigen,
		steht Ihnen unsere <a href=\'https://www.technikum-wien.at/studieninformationen/studienberatung-kontaktieren/\' target=\'_blank\'>Studienberatung</a>
		gerne persönlich, telefonisch, per E-Mail oder WhatsApp zur Verfügung.<br><br>
		<a href="#datenschutzText" data-toggle="collapse">Datenschutz-Hinweis: <span class="glyphicon glyphicon-collapse-down"></span></a>

		<div id="datenschutzText" class="collapse">
		Die uns von Ihnen zum Zwecke der Bewerbung bekanntgegebenen Daten werden von uns ausschließlich zur Abwicklung der Bewerbung auf der Grundlage von vor- bzw vertraglichen Zwecken verarbeitet und mit der unten beschriebenen Ausnahme bei Unklarheiten betreffend die Zugangsvoraussetzungen nicht an Dritte weitergegeben. Kommt es zu keinem weiteren Kontakt bzw zu keiner Aufnahme, löschen wir Ihre Daten nach drei Jahren.<br><br>
		Informationen zu Ihren Betroffenenrechten finden Sie hier: <a href=\'https://www.technikum-wien.at/information-ueber-ihre-rechte-gemaess-datenschutz-grundverordnung/\' target=\'_blank\'>https://www.technikum-wien.at/information-ueber-ihre-rechte-gemaess-datenschutz-grundverordnung/</a><br><br>
		Bei Fragen stehen wir Ihnen jederzeit unter <a href=\'mailto:datenschutz@technikum-wien.at\'>datenschutz@technikum-wien.at</a> zur Verfügung.<br><br>
		Verantwortlich für die Datenverarbeitung:<br>
		Fachhochschule Technikum Wien<br>
		Höchstädtplatz 6<br>
		1200 Wien
		</div> ';
$this->phrasen['bewerbung/login']='Login';
$this->phrasen['bewerbung/zugangscode']='Zugangscode';
$this->phrasen['bewerbung/fallsVorhanden']='(falls vorhanden)';
$this->phrasen['bewerbung/mailtextHtml']='Bitte sehen Sie sich die Nachricht in der HTML-Ansicht an um den Link vollständig darzustellen.';
$this->phrasen['bewerbung/anredeMaennlich']='geehrter Herr';
$this->phrasen['bewerbung/anredeWeiblich']='geehrte Frau';
$this->phrasen['bewerbung/anredeNeutral']='geehrte/r Herr/Frau';
$this->phrasen['bewerbung/mailtext']='
<html>
	<head>
		<title>Sancho Mail</title>
	</head>
	<body>
		<center>
			<table cellpadding="0" cellspacing="0" style="border: 2px solid #000000; padding: 0px; max-width: 850px;
				border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;">
				<tr>
					<td align="center">
						<img src="cid:sancho_header" alt="header_image" style="width: 100%%; display: block"/>
					</td>
				</tr>
				<tr>
					<td align="center">
						<table cellpadding="0" cellspacing="0" width="100%%" style="font-family: courier, verdana, sans-serif; font-size: 0.95em; border-bottom: 2px solid #000000;">
							<tr>
								<td style="padding-left: 8%%; padding-right: 8%%; padding-top: 5%%; padding-bottom: 5%%;">
									Sehr %4$s %1$s %2$s!<br><br>
									Vielen Dank für Ihr Interesse an einem Studiengang oder Lehrgang der '.CAMPUS_NAME.'. <br>
									Verwenden Sie für Ihre Bewerbung bitte folgenden Link und Zugangscode: <br><br>
									<a href="'.APP_ROOT.'addons/bewerbung/cis/registration.php?code=%3$s&emailAdresse=%5$s">Link zur Bewerbung</a><br>
									Zugangscode: %3$s<br><br>
									Wir empfehlen Ihnen aus Sicherheitsgründen, sich einen neuen Zugangscode nach dem Login generieren zu lassen (Menüpunkt "Sicherheit").<br><br>
									Mit freundlichen Grüßen, <br>
									'.CAMPUS_NAME.'
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td align="center">
						<img src="cid:sancho_footer" alt="footer_image" style="width: 100%%; display: block"/>
					</td>
				</tr>
			</table>
		</center>
	</body>
</html>';
$this->phrasen['bewerbung/zugangsdatenFalsch']='Der eingegebene Zugangscode ist falsch oder Sie haben sich noch nicht registriert.';
$this->phrasen['bewerbung/mailFalsch']='Die eingegebene E-Mailadresse ist falsch oder Sie haben sich noch nicht registriert.';
$this->phrasen['bewerbung/fehlerBeimSenden']='Beim Senden der E-Mail ist ein Fehler aufgetreten.';
$this->phrasen['bewerbung/emailgesendetan']='
<style type="text/css">
	#mail_icon
		{
			position:relative;
			animation:mymove 2s  ;
			animation-iteration-count:2;
			/* Safari and Chrome */
			-webkit-animation:mailsend_small 2s;
			-webkit-animation-iteration-count:2;
		}
	@media (min-width: 576px)
	{
		#mail_icon
		{
			position:relative;
			animation:mymove 2s  ;
			animation-iteration-count:2;
			/* Safari and Chrome */
			-webkit-animation:mailsend_small 2s;
			-webkit-animation-iteration-count:2;
		}
	}
	@media (min-width: 768px)
	{
		#mail_icon
		{
			position:relative;
			animation:mymove 2s  ;
			animation-iteration-count:2;
			/* Safari and Chrome */
			-webkit-animation:mailsend_medium 2s;
			-webkit-animation-iteration-count:2;
		}
	}
	@media (min-width: 992px)
	{
		#mail_icon
		{
			position:relative;
			animation:mymove 2s  ;
			animation-iteration-count:2;
			/* Safari and Chrome */
			-webkit-animation:mailsend_large 2s;
			-webkit-animation-iteration-count:2;
		}
	}
	@keyframes mailsend_small
	{
		0%%,30%%
		{
			opacity: 1;
			transform: translate(0, 0);
		}
		100%%
		{
			opacity: 0;
			transform: translate(200px, 0);
		}
	}
	@keyframes mailsend_medium
	{
		0%%,30%%
		{
			opacity: 1;
			transform: translate(0, 0);
		}
		100%%
		{
			opacity: 0;
			transform: translate(300px, 0);
		}
	}
	@keyframes mailsend_large
	{
		0%%,30%%
		{
			opacity: 1;
			transform: translate(0, 0);
		}
		100%%
		{
			opacity: 0;
			transform: translate(400px, 0);
		}
	}

</style>
<span id="mail_icon" class="glyphicon glyphicon-envelope" style="font-size:50px;"></span>
<br><br>
Die E-Mail mit Ihrem Zugangscode wurde erfolgreich an %s verschickt.
<br><br>In der Regel erhalten Sie das Mail in wenigen Minuten. Wenn Sie nach <b>24 Stunden</b> noch kein Mail erhalten haben,
kontaktieren Sie bitte unsere <a href=\'https://www.technikum-wien.at/studieninformationen/studienberatung-kontaktieren/\' target=\'_blank\'>Studienberatung</a>';
$this->phrasen['bewerbung/zurueckZurAnmeldung']='Zurück zur Login-Seite.';
$this->phrasen['bewerbung/sicherheitscodeFalsch']='Der eingegebene Sicherheitscode ist falsch.';
$this->phrasen['bewerbung/geplanterStudienbeginn']='Geplanter Studienbeginn';
$this->phrasen['bewerbung/studienrichtung']='Gewünschte Studienrichtung(en)';
$this->phrasen['bewerbung/bitteStudienrichtungWaehlen']='Bitte mindestens eine Studienrichtung auswählen.';
$this->phrasen['bewerbung/bitteVornameAngeben']='Bitte geben Sie Ihren Vornamen ein.';
$this->phrasen['bewerbung/bitteNachnameAngeben']='Bitte geben Sie Ihren Nachnamen ein.';
$this->phrasen['bewerbung/bitteGeburtsdatumEintragen']='Bitte geben Sie Ihr Geburtsdatum ein.';
$this->phrasen['bewerbung/bitteGeschlechtWaehlen']='Bitte geben Sie Ihr Geschlecht ein.';
$this->phrasen['bewerbung/bitteEmailAngeben']='Bitte geben Sie eine gültige E-Mail-Adresse ein.';
$this->phrasen['bewerbung/bitteStudienbeginnWaehlen']='Bitte wählen Sie den gewünschten Studienbeginn.';
$this->phrasen['bewerbung/captcha']='Geben Sie bitte hier die Zeichen aus der Grafik ein (Spamschutz).';
$this->phrasen['bewerbung/andereGrafik']='Andere Grafik';
$this->phrasen['bewerbung/datumFormat']='tt.mm.jjjj';
$this->phrasen['bewerbung/datumUngueltig']='Das Datumsformat ist ungültig oder liegt außerhalb des gültigen Bereichs. Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj ein.';
$this->phrasen['bewerbung/datumsformatUngueltig']='Das Datumsformat ist ungültig. Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj ein.';
$this->phrasen['bewerbung/egal']='gleichgültig';
$this->phrasen['bewerbung/orgform']='Organisationsform';
$this->phrasen['bewerbung/orgform/BB']='Abendform';
$this->phrasen['bewerbung/orgform/VZ']='Tagesform';
$this->phrasen['bewerbung/orgform/teilzeit']='Teilzeit';
$this->phrasen['bewerbung/orgform/DL']='Fernstudium';
$this->phrasen['bewerbung/orgform/DDP']='Double Degree Program';
$this->phrasen['bewerbung/orgform/PT']='Part time';
$this->phrasen['bewerbung/orgform/ZGS']='Zielgruppenspezifisch';
$this->phrasen['bewerbung/orgform/DUA']='Dual';
$this->phrasen['bewerbung/vollzeit']='Vollzeit';
$this->phrasen['bewerbung/teilzeit']='Teilzeit';
$this->phrasen['bewerbung/German']='Deutsch';
$this->phrasen['bewerbung/English']='Englisch';
$this->phrasen['bewerbung/Italian']='Italienisch';
$this->phrasen['bewerbung/topprio']='Oberste Priorität';
$this->phrasen['bewerbung/alternative']='Alternative';
$this->phrasen['bewerbung/priowaehlen']='Primäre und alternative Variante auswählen';
$this->phrasen['bewerbung/prioBeschreibungstext'] = 'Bitte wählen Sie die Organisationsform und Sprache. Für den Fall, dass es keine Plätze mehr gibt, können Sie auch eine Alternative auswählen.';
$this->phrasen['bewerbung/prioUeberschrifttopprio'] = 'Oberste Priorität';
$this->phrasen['bewerbung/prioUeberschriftalternative'] = 'Alternative (optional)';
$this->phrasen['bewerbung/neuerStudiengang'] = 'Bitte wählen Sie einen Studiengang für den Sie sich bewerben möchten';
$this->phrasen['bewerbung/geplanteStudienrichtung']='Geplante Studienrichtung';
$this->phrasen['bewerbung/menuAllgemein']='Allgemein';
$this->phrasen['bewerbung/loginmitAccount']='Wenn Sie bereits einen Account haben, können sie sich mit Ihrem Usernamen / Passwort anmelden';
$this->phrasen['bewerbung/allgemeineErklaerung']='Wir freuen uns, dass Sie sich für unser Bildungsangebot interessieren.<br>
	Sie können online bis zu '.(defined('BEWERBERTOOL_MAX_STUDIENGAENGE') && BEWERBERTOOL_MAX_STUDIENGAENGE != ''?BEWERBERTOOL_MAX_STUDIENGAENGE:'').' Studienrichtungen und beliebig viele Lehrgänge zur Weiterbildung auswählen.<br>
	Klicken Sie auf den grünen Button, um Ihrer Bewerbung einen Studiengang oder Lehrgang hinzuzufügen. Bitte beachten Sie, dass Sie bei der Auswahl Ihrer Studiengänge eine Priorität abgeben müssen. Nach Absolvierung des Reihungstests kann die Priorisierung <b>NICHT</b> mehr geändert werden.
	<br><br>
	Sind alle erforderlichen Daten ausgefüllt, können Sie durch einen Klick auf "Bewerbung abschicken", Ihre Bewerbung an uns übermitteln.
	<br><br>
	Wenn Sie mehr Informationen benötigen, steht Ihnen unsere <a href=\'https://www.technikum-wien.at/studieninformationen/studienberatung-kontaktieren/\' target=\'_blank\'>Studienberatung</a> gerne persönlich, telefonisch, per E-Mail oder WhatsApp zur Verfügung.
	<br><br>
	Bewerbungsfristen für Drittstaatenbewerbungen (außerhalb der EU) siehe:<br>
	<a href=\'https://www.technikum-wien.at/bewerbungsfristen\' target=\'_blank\'>https://www.technikum-wien.at/bewerbungsfristen</a><br><br>
	An dieser Stelle möchten wir Sie auf unsere <a href=\'https://cis.technikum-wien.at/cms/dms.php?id=77605\' target=\'_blank\'><span class="glyphicon glyphicon-file"></span> Information über die Verwendung personenbezogener Daten von BewerberInnen</a> hinweisen.';
$this->phrasen['bewerbung/erklaerungStudierende']='Wir freuen uns über Ihr Interesse an unserem Bildungsangebot.<br>
	Klicken Sie auf den grünen Button, um Ihrer Bewerbung einen Studiengang oder Lehrgang hinzuzufügen.';
$this->phrasen['bewerbung/aktuelleBewerbungen']='Aktuelle Bewerbungen:';
$this->phrasen['bewerbung/status']='Status';
$this->phrasen['bewerbung/legende']='Legende';
$this->phrasen['bewerbung/bewerbungsstatus']='Bewerbungsstatus';
$this->phrasen['bewerbung/keinStatus']='Keine aktuelle Bewerbung vorhanden. Klicken Sie auf "<i>Neue Bewerbung für Studiengang/Lehrgang hinzufügen</i>" um eine Bewerbung hinzuzufügen';
$this->phrasen['bewerbung/bestaetigt']='Bestätigt, in Bearbeitung';
$this->phrasen['bewerbung/nichtBestaetigt']='Abgeschickt, in Bearbeitung';
$this->phrasen['bewerbung/nichtAbgeschickt']='Bewerbung abschicken zur weiteren Bearbeitung';
$this->phrasen['bewerbung/bewerbungAbgeschickt']='Bewerbung abgeschickt';
$this->phrasen['bewerbung/datenPruefung']='Datenprüfung';
$this->phrasen['bewerbung/datenWerdenGeprueft']='Daten werden geprüft';
$this->phrasen['bewerbung/freigegebenAnStudiengang']='Freigegeben an den Studiengang';
$this->phrasen['bewerbung/freigabeAnStudiengang']='Freigabe an Studiengang';
$this->phrasen['bewerbung/kontaktaufnahmeDurchStudiengang']='Kontaktaufnahme durch Studiengang';
$this->phrasen['bewerbung/datenVollstaendig']='Daten vollständig';
$this->phrasen['bewerbung/datenUnvollstaendig']='Daten vervollständigen';
$this->phrasen['bewerbung/studiengangHinzufuegen']='Neue Bewerbung für Studiengang/Lehrgang hinzufügen';
$this->phrasen['bewerbung/weiter']='Weiter';
$this->phrasen['bewerbung/geburtsnation']='Geburtsnation';
$this->phrasen['bewerbung/svnr']='Österr. Sozialversicherungsnr.';
$this->phrasen['bewerbung/maennlich']='männlich';
$this->phrasen['bewerbung/weiblich']='weiblich';
$this->phrasen['bewerbung/berufstaetigkeit']='Aktuelle Berufstätigkeit **';
$this->phrasen['bewerbung/berufstaetig']='berufstätig';
$this->phrasen['bewerbung/dienstgeber']='Dienstgeber';
$this->phrasen['bewerbung/artDerTaetigkeit']='Art der Tätigkeit';
$this->phrasen['bewerbung/weiter']='Weiter';
$this->phrasen['bewerbung/eintragVom']='Eintrag vom';
$this->phrasen['bewerbung/menuPersDaten']='Persönliche Daten';
$this->phrasen['bewerbung/accountVorhanden']='Da Sie bereits als InteressentIn bestätigt wurden oder Sie bereits einen Account an der FHTW haben, können Sie Ihre Stammdaten nicht mehr ändern. Sollten hier Daten fehlerhaft sein, wenden Sie sich bitte an die zuständige Assistenz.';
$this->phrasen['bewerbung/bitteAuswaehlen']='-- Bitte auswählen --';
$this->phrasen['bewerbung/menuKontaktinformationen']='Kontaktinformationen';
$this->phrasen['bewerbung/kontakt']='Kontakt';
$this->phrasen['bewerbung/nation']='Nation';
$this->phrasen['bewerbung/menuDokumente']='Dokumente';
$this->phrasen['bewerbung/dokument']='Dokument';
$this->phrasen['bewerbung/linkDokumenteHochladen']='Dokumente hochladen';
$this->phrasen['bewerbung/dokumenteZumHochladen']='Benötigte Dokumente:';
$this->phrasen['bewerbung/dokumentName']='Name';
$this->phrasen['bewerbung/benoetigtFuer']='Benötigt für';
$this->phrasen['bewerbung/dokumenteFuer']='Dokumente für';
$this->phrasen['bewerbung/dokumentErforderlich']='Dokument erforderlich';
$this->phrasen['bewerbung/dokumentNichtErforderlich']='Dokument nicht erforderlich';
$this->phrasen['bewerbung/dokumentOffen']='Dokument hochladen';
$this->phrasen['bewerbung/dokumentNichtUeberprueft']='Dokument wurde abgegeben aber noch nicht überprüft';
$this->phrasen['bewerbung/dokumentWirdNachgereicht']='Dokument wird nachgereicht';
$this->phrasen['bewerbung/dokumentWurdeUeberprueft']='Dokument wurde bereits überprüft';
$this->phrasen['bewerbung/dokumentHerunterladen']='Dokument herunterladen';
$this->phrasen['bewerbung/abgegeben']='abgegeben';
$this->phrasen['bewerbung/upload']='Upload';
$this->phrasen['bewerbung/menuZgv']='ZGV';
$this->phrasen['bewerbung/menuZugangsvoraussetzungen']='Zugangsvoraussetzungen';
$this->phrasen['bewerbung/fuer']='für';
$this->phrasen['bewerbung/artDerVoraussetzung']='Art der Voraussetzung';
$this->phrasen['bewerbung/menuZahlungen']='Zahlungen';
$this->phrasen['bewerbung/zahlungsinformation']='Zahlungsinformation';
$this->phrasen['bewerbung/bezahlt']='bezahlt';
$this->phrasen['bewerbung/zahlungsdetails']='Zahlungsdetails';
$this->phrasen['bewerbung/menuReihungstest']='Reihungstest';
$this->phrasen['bewerbung/fuerReihungstestAnmelden']='Es werden Ihnen die nächsten verfügbaren Online-Reihungstesttermine angezeigt. Bitte melden Sie sich für <b>einen</b> dieser an.<br>Unabhängig von der Anzahl Ihrer Bachelor-Bewerbungen brauchen Sie den Reihungstest nur <b>einmal</b> zu absolvieren.<br>Die Anmeldung zum Reihungstest <b>fixiert die Priorisierung</b> Ihrer gewählten Studiengänge.';
$this->phrasen['bewerbung/fehler']='Es ist ein Fehler aufgetreten';
$this->phrasen['bewerbung/angemeldetPlaetze']='angemeldet / Plätze';
$this->phrasen['bewerbung/uhrzeit']='Uhrzeit';
$this->phrasen['bewerbung/stornieren']='stornieren';
$this->phrasen['bewerbung/menuBewerbungAbschicken']='Bewerbung abschicken';
$this->phrasen['bewerbung/erklaerungBewerbungAbschicken']='Wenn Sie alle Daten vervollständigt haben, können Sie Ihre Bewerbung hier abschicken. Sie werden in der Regel innerhalb von 5 Werktagen kontaktiert.';
$this->phrasen['bewerbung/erklaerungBewerbungAbschicken2']='Bitte überprüfen Sie nochmals Ihre Daten.<br>
		Um Ihre Bewerbung abzuschließen klicken Sie auf die entsprechende Schaltfläche:';
$this->phrasen['bewerbung/bewerbungAbschickenFuer']='Bewerbung abschicken für';
$this->phrasen['bewerbung/buttonBewerbungAbschicken']='Bewerbung abschicken';
$this->phrasen['bewerbung/logout']='Logout';
$this->phrasen['bewerbung/einzahlungFuer']='Einzahlung für';
$this->phrasen['bewerbung/zahlungsinformationen']='Zahlungsinformationen';
$this->phrasen['bewerbung/buchungstyp']='Buchungstyp';
$this->phrasen['bewerbung/buchungstext']='Buchungstext';
$this->phrasen['bewerbung/betrag']='Betrag';
$this->phrasen['bewerbung/zahlungAn']='Zahlung an';
$this->phrasen['bewerbung/empfaenger']='Empfänger';
$this->phrasen['bewerbung/iban']='IBAN';
$this->phrasen['bewerbung/bic']='BIC';
$this->phrasen['bewerbung/zahlungsreferenz']='Zahlungsreferenz';
$this->phrasen['bewerbung/offenerBetrag']='Offener Betrag';
$this->phrasen['bewerbung/buchungsnummerNichtVorhanden']='Buchungsnummer %s nicht vorhanden';
$this->phrasen['bewerbung/teilweiseVollstaendig']='teilweise vollständig';
$this->phrasen['bewerbung/vollstaendig']='vollständig';
$this->phrasen['bewerbung/unvollstaendig']='unvollständig';
$this->phrasen['bewerbung/teilweiseVollstaendig']='teilweise vollständig';
$this->phrasen['bewerbung/maxAnzahlTeilnehmer']='max. Teilnehmeranzahl erreicht';
$this->phrasen['bewerbung/erfolgreichBeworben']='Sie haben sich erfolgreich für %s beworben. In der Regel werden Sie innerhalb von 5 Werktagen kontaktiert.';
$this->phrasen['bewerbung/fehlerBeimVersendenDerBewerbung']='Es ist ein Fehler beim Versenden der Bewerbung aufgetreten. Bitte versuchen Sie es nocheinmal.';
$this->phrasen['bewerbung/svnrBereitsVorhanden']='SVNR bereits vorhanden';
$this->phrasen['bewerbung/menuBewerbungFuerStudiengang']='Bewerbung für einen Studiengang';
$this->phrasen['bewerbung/dokumentOhneUploadGeprueft'] = 'Dokument ohne Upload überprüft.';
$this->phrasen['bewerbung/emailBodyStart']='
<html>
	<head>
		<title>Sancho Mail</title>
	</head>
	<body>
		<center>
			<table cellpadding="0" cellspacing="0" style="border: 2px solid #000000; padding: 0px; max-width: 850px;
				border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;">
				<tr>
					<td align="center">
						<table cellpadding="0" cellspacing="0" width="100%%" border="0">
							<tr>
								<td>
									<img src="data:image/jpg;base64,%2$s" alt="sancho_header" width="100%%"/>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td style="padding-left: 8%%; padding-right: 8%%; padding-top: 5%%; padding-bottom: 5%%; font-family: courier, verdana, sans-serif; font-size: 0.95em; border-bottom: 2px solid #000000;">
						Es gibt eine neue Bewerbung.<br>
						Für mehr Details öffnen Sie bitte den <a href="%1$s" target="_blank">Personendatensatz</a> im FAS.';
$this->phrasen['bewerbung/emailDokumentuploadStart']='Das folgende Dokument wurde hochgeladen: <br>';
$this->phrasen['bewerbung/emailBodyEnde']='
					</td>
				</tr>
				<tr>
					<td align="center">
						<table cellpadding="0" cellspacing="0" width="100%%">
							<tr>
								<td>
									<img src="data:image/jpg;base64,%1$s" alt="sancho_footer" width="100%%"/>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</center>
	</body>
</html>
';
$this->phrasen['bewerbung/fileUpload']='File-Upload';
$this->phrasen['bewerbung/fehlerKeinePersonId']='Es wurde keine Person_id angegeben';
$this->phrasen['bewerbung/woWurdeUrkundeAusgestellt']='Wo wurde die Urkunde ausgestellt?';
$this->phrasen['bewerbung/ausbildungstyp']='Ausbildungstyp';
$this->phrasen['bewerbung/lehrgang']='Lehrgang';
$this->phrasen['bewerbung/keineStgAngeboten']='Es werden derzeit keine Studiengänge angeboten';
$this->phrasen['bewerbung/keineLehrgAngeboten']='Es werden derzeit keine Lehrgänge angeboten';
$this->phrasen['bewerbung/aufmerksamdurch']='Wie sind Sie auf uns aufmerksam geworden?';
$this->phrasen['bewerbung/BewerbungBereitsVerschickt']='Ihre Bewerbung wird bearbeitet';
$this->phrasen['bewerbung/bitteZuerstStudiengangWaehlen']='Bitte wählen Sie zuerst den gewünschten Studiengang im Bereich "Übersicht" aus.';
$this->phrasen['bewerbung/ExtensionInformation']='Maximale Dateigröße je Dokument: 15 MB.<br>Unterstützte Dateiformate:';
$this->phrasen['bewerbung/falscherDateityp']='Dieses Dateiformat wird nicht unterstützt';
$this->phrasen['bewerbung/mailadresseBereitsGenutzt']='Die E-Mail Adresse %s wurde bereits für eine Bewerbung genutzt. Sie können sich einen neuen Zugangscode an diese Adresse schicken lassen und nach dem Login weitere Bewerbungen hinzufügen.';
$this->phrasen['bewerbung/mailadresseBereitsVorhanden']='Die E-Mail Adresse %s ist bereits im System vorhanden und kann nicht gespeichert werden. Bitte geben Sie eine andere E-Mail Adresse ein.';
$this->phrasen['bewerbung/buttonBewerbungUnvollstaendig']='Pflichtfelder unvollständig';
$this->phrasen['bewerbung/prestudentID']='Prestudent ID';
$this->phrasen['bewerbung/bewerbung']='Bewerbung';
$this->phrasen['bewerbung/dokumentuploadZuBewerbung']='Dokumentupload %s zu Bewerbung';
$this->phrasen['bewerbung/maennlich']='Männlich';
$this->phrasen['bewerbung/weiblich']='Weiblich';
$this->phrasen['bewerbung/maturazeugnis']='Maturazeugnis';
$this->phrasen['bewerbung/details']='Details';
$this->phrasen['bewerbung/mehrDetails']='Mehr Details...';
$this->phrasen['bewerbung/codeZuschicken']='Neuen Code zuschicken';
$this->phrasen['bewerbung/codeZuschickenAnleitung']='Wenn Sie sich schon einmal registriert aber den Zugangscode verloren oder vergessen haben, können Sie sich hier den Zugangscode erneut zuschicken lassen. Bitte geben Sie dazu Ihre Mailadresse ein und drücken Sie auf "Code zuschicken"';
$this->phrasen['bewerbung/keinCodeVorhanden']='Für diese E-Mail Adresse ist noch kein Zugangscode vorhanden. Bitte <a href="'.$_SERVER['PHP_SELF'].'?method=registration">melden Sie sich vorher an</a>.';
$this->phrasen['bewerbung/zgvDatumNichtZukunft']='Das Datum der Zugangsvoraussetzung darf nicht in der Zukunft liegen. Wenn Sie die Zugangsvoraussetzung erst später erbringen, lassen Sie das Datum bitte leer.';
$this->phrasen['bewerbung/ab']='ab';
$this->phrasen['bewerbung/adresse']='Adresse (Hauptwohnsitz)';
$this->phrasen['bewerbung/notizVom']='Notiz vom';
$this->phrasen['bewerbung/anmerkung']='Anmerkung';
$this->phrasen['bewerbung/anmerkungPlaceholder']='Anmerkungen zu Ihrer Bewerbung bitte hier eintragen und speichern. (1 Anmerkung pro Bewerbung, 1024 Zeichen)';
$this->phrasen['bewerbung/orgformMussGewaehltWerden']='Es muss eine Organisationsform gewählt werden';
$this->phrasen['bewerbung/hierUnverbindlichAnmelden']='Hier unverbindlich anmelden';
$this->phrasen['bewerbung/keineOrgformVorhanden']='Für das gewähle Studiensemester ist noch keine Organisationsform hinterlegt';
$this->phrasen['bewerbung/bitteOrgformWaehlen']='Wenn Sie einen Studiengang mit mehreren Organisationsformen wählen, müssen Sie eine Priorität angeben';
$this->phrasen['bewerbung/orgformWaehlen']='Organisationsform wählen';
$this->phrasen['bewerbung/orgformBeschreibungstext']='Bitte geben Sie an, für welche Organisationsform Sie sich interessieren. Für den Fall, dass alle Plätze in Ihrer gewünschten Organisationsform vergeben sind, können Sie optional eine Alternative angeben';
$this->phrasen['bewerbung/menuAbschließen']='Abschließen';
$this->phrasen['bewerbung/habenSieBereitsEinenZugangscode']='<b>Registrierte*r</b> Bewerber*in? <br>Sie haben sich bereits registriert und einen Zugangscode zu unserem Online-Bewerbungsportal erhalten?';

// Allgemeine Phrasen
$this->phrasen['bewerbung/sieHabenNochKeinenZugangscode']='<b>Neue*r</b> Bewerber*in? <br>Sie haben noch keinen Zugangscode oder Account an der FH?';
$this->phrasen['bewerbung/dannHiermitAccountEinloggen']='Dann loggen Sie sich hier mit Ihrem CIS-Account ein';
$this->phrasen['bewerbung/dannHierEinloggen']='Dann loggen Sie sich hier ein';
$this->phrasen['bewerbung/studierenOderArbeitenSieBereits']='Neue*r Bewerber*in <b>und</b> bereits Student*in? <br>Sie haben bereits einen aktiven CIS Account an der FH (Student*in).';
$this->phrasen['bewerbung/bitteDokumenteHochladen']='Um Ihre Bewerbung abschicken zu können, müssen Sie Dokumente, die als „erforderlich“ markiert sind, hochladen.<br>
	Sollte das Dokument zum gegenwärtigen Zeitpunkt noch nicht verfügbar sein, haben Sie die <b>Möglichkeit</b>, das <b>Dokument nachzureichen</b>.<br>
	Klicken Sie dazu auf "Dokument wird nachgereicht" und geben Sie an, bis zu welchem Zeitpunkt Sie das Dokument nachreichen werden und welche Institution das Dokument ausstellen wird.<br>
	Gegebenenfalls werden Sie im weiteren Verlauf der Bewerbung aufgefordert, hier weitere Dokumente hochzuladen.<br><br>
	Bitte beachten Sie, dass überprüfte Dokumente nicht erneut hochgeladen werden können. Sollte ein aktuelleres Dokument vorliegen, so wenden Sie sich bitte an die Studiengangsassistenz.<br>
	<b>Tipp:</b> Um mehrere Einzelseiten zu einer Datei zusammenfügen zu können, empfehlen wir Ihnen kostenlose Programme wie bspw. pdf Merge.';

$this->phrasen['bewerbung/zugangscodeVergessen']='Zugangscode vergessen?';
$this->phrasen['bewerbung/hinweisZGVdatenaenderung']='<b>Hinweis:</b> Aus organisatorischen Gründen können gespeicherte Angaben hier nicht mehr verändert werden. Sollten Angaben fehlerhaft sein, verwenden Sie bitte das Notizfeld im Schritt "Abschließen" oder kontaktieren Sie die Assistenz per E-Mail.';
$this->phrasen['bewerbung/statusBestaetigen']='Status direkt bestätigen';
$this->phrasen['bewerbung/footerText']='';
$this->phrasen['bewerbung/vorbehaltlichAkkreditierung']='Vorbehaltlich der Akkreditierung durch die AQ Austria';
$this->phrasen['bewerbung/auswahlmöglichkeitenImNaechstenSchritt']='Auswahlmöglichkeiten im nächsten Schritt';
$this->phrasen['bewerbung/sieKoennenMaximalXStudiengaengeWaehlen']='Sie können sich online für maximal %s Studiengänge im gleichen Studiensemester bewerben. Wenn Sie mehr Informationen benötigen, steht Ihnen unsere <a href=\'https://www.technikum-wien.at/studieninformationen/studienberatung-kontaktieren/\' target=\'_blank\'>Studienberatung</a> gerne persönlich, telefonisch oder per E-Mail zur Verfügung.<br><br><a href=\'#\' class=\'alert-link\' data-dismiss=\'alert\' aria-label=\'close\'>Schließen</a>'; // Link muss mit einfachen Hochkomma maskiert werden, das es sonst im Bewerbungstool zu Anzeigefehlern kommt
$this->phrasen['bewerbung/bitteEineStudienrichtungWaehlen']='Bitte eine Studienrichtung auswählen.';
$this->phrasen['bewerbung/beschreibungTitelPre']='Akademische Titel, die dem Namen vorangestellt werden. ZB.: DI, Mag., Dr. <br><br>Im Zuge dieser Angabe ist ein entsprechender Nachweis ergänzend zu Ihren erforderlichen Dokumenten hochzuladen!';
$this->phrasen['bewerbung/beschreibungTitelPost']='Akademische Titel, die dem Namen nachgestellt werden. ZB.: BA, MA, PhD. <br><br>Im Zuge dieser Angabe ist ein entsprechender Nachweis ergänzend zu Ihren erforderlichen Dokumenten hochzuladen!';
$this->phrasen['bewerbung/BilduploadInfotext']='Sie können Bilder in den Formaten JPG, PNG oder GIF mit einer Maximalgröße von 15MB hochladen.<br><br><b>Bitte beachten Sie die <a href=\''.APP_ROOT.'cms/content.php?content_id=%s\' target=\'_blank\'>Richtlinien für den Bildupload</a></b>';
$this->phrasen['bewerbung/fotoAuswaehlen']='Klicken Sie auf die Grafik um ein Foto hochzuladen und zuzuschneiden<br>Falls der Upload fehlschlägt, oder ihr Browser den Bildzuschnitt nicht unterstützt, <br>können Sie <a href="dms_akteupload.php?person_id=%s&dokumenttyp=Lichtbil"><b>hier</b></a> die Datei direkt hochladen';
$this->phrasen['bewerbung/akademischeTitel']='Akademische(r) Titel';
$this->phrasen['bewerbung/pflichtfelder']='Pflichtfelder';
$this->phrasen['bewerbung/bitteGueltigeOesterreichischePlzEingeben']='Bitte geben Sie eine gültige österreichische Postleitzahl ein';
$this->phrasen['bewerbung/plzMussGueltigSein']='Postleitzahl muss eine gültige Nummer sein';
$this->phrasen['bewerbung/plzUnbekannt']='Postleitzahl unbekannt';
$this->phrasen['bewerbung/dateien']='Datei(en)';
$this->phrasen['bewerbung/dokumentWirdGeprueft']='Dokument vorhanden';
$this->phrasen['bewerbung/dokumentUeberprueft']='Dokument überprüft';
$this->phrasen['bewerbung/keineDateiAusgewaehlt']='Keine Datei zum Hochladen ausgewählt oder gefunden';
$this->phrasen['bewerbung/placeholderAnmerkungNachgereicht']='Bitte geben Sie an, welche Institution das Dokument ausstellen wird und bis wann Sie dieses voraussichtlich nachreichen können:';
$this->phrasen['bewerbung/bitteAusstellungsnationAuswaehlen']='-- Bitte wählen Sie, in welchem Land das Dokument ausgestellt wurde --';
$this->phrasen['bewerbung/sitzungAbgelaufen']='Die Sitzung ist abgelaufen. Bitte loggen Sie sich erneut ein';
$this->phrasen['bewerbung/placeholderOrtNachgereicht']='Institution des Ausstellers (zB: TGM Wien)';
$this->phrasen['bewerbung/wirdNachgreichtAm']='Wird nachgereicht bis';
$this->phrasen['bewerbung/ausstellendeInstitution']='Ausstellende Institution';
$this->phrasen['bewerbung/dokumenteVollstaendig']='Alle erforderlichen Dokumente wurden hochgeladen';
$this->phrasen['bewerbung/keinDokumententypUebergeben']='Es wurde kein Dokumenttyp übergeben';
$this->phrasen['bewerbung/aktion']='Aktion';
$this->phrasen['bewerbung/bewerbungStornieren']='Bewerbung stornieren';
$this->phrasen['bewerbung/bewerbungStorniert']='Bewerbung storniert';
$this->phrasen['bewerbung/bewerbungStornierenInfotext']='Wenn Sie die Bewerbung stornieren, können Sie sich im %1$s nicht wieder für <span style="text-align: center; font-weight: bold; display: block; padding-top: 10px">%2$s</span> bewerben.<br><br>Möchten Sie fortfahren?';
$this->phrasen['bewerbung/bewerbungStornierenBestaetigen']='Ja, Bewerbung stornieren';
$this->phrasen['bewerbung/vergangeneBewerbungen']='Vergangene Bewerbungen';
$this->phrasen['bewerbung/buttonStornierenDisabled']='Wenn die Bewerbung abgeschickt oder abgewiesen wurde, können Sie diese nicht mehr stornieren';
$this->phrasen['bewerbung/infotextDisabled']='Da bereits eine Bewerbung vorhanden ist oder war, können Sie sich im %s nicht mehr für diesen Studiengang bewerben';
$this->phrasen['bewerbung/bitteAnmerkungEintragen']='Bitte geben Sie den Namen der Institution ein, die das Dokument ausstellen wird';
$this->phrasen['bewerbung/nachreichDatumNichtVergangenheit']='Das Datum der Nachreichung darf nicht in der Vergangenheit liegen';
$this->phrasen['bewerbung/infotextVorlaeufigesZgvDokument']='Bitte laden Sie eine Datei hoch, welche glaubhaft vermittelt, dass Sie die Zugangsvoraussetzung für den gewählten Studiengang erlangen werden. Das kann das letzte Semester-, Teil-, bzw. ein Sammelzeugnis, eine (Anmelde-)Bestätigung, etc. sein.<br>';
$this->phrasen['bewerbung/bitteDateiAuswaehlen']='Bitte wählen Sie eine Datei aus';
$this->phrasen['bewerbung/zustimmungDatenuebermittlung']='Können in Ausnahmefällen die Zugangsvoraussetzungen von der FH Technikum Wien nicht abschließend abgeklärt werden, erteile ich die Zustimmung, dass die FH Technikum Wien die Dokumente zur Überprüfung an die zuständigen Behörden weiterleiten kann.<br>
Ich wurde darüber informiert, dass ich nicht verpflichtet bin, der Übermittlung meiner Daten zuzustimmen. Diese Zustimmung ist allerdings notwendig, um die Bewerbung berücksichtigen zu können.';
$this->phrasen['bewerbung/bitteDatenuebermittlungZustimmen']='Sie müssen der Datenübermittlung zustimmen, um Ihre Bewerbung abschicken zu können';
$this->phrasen['bewerbung/vorlaeufigesDokument']='Vorläufiges Dokument';
$this->phrasen['bewerbung/lehrgangsArt/1']='Master Lehrgang';
$this->phrasen['bewerbung/lehrgangsArt/2']='Akademischer Lehrgang';
$this->phrasen['bewerbung/lehrgangsArt/3']='International Programs';
$this->phrasen['bewerbung/lehrgangsArt/4']='Zertifikatslehrgang';
$this->phrasen['bewerbung/lehrgangsArt/5']='Postgradualer Lehrgang';
$this->phrasen['bewerbung/hackTypBezeichnungLehrgeange']='Lehrgänge zur Weiterbildung'; // Überschreibt die Typ-Bezeichnung "Lehrgang". Leer lassen, wenn nicht benötigt
$this->phrasen['bewerbung/hackTypBezeichnungBachelor']='Bachelor Studiengänge'; // Überschreibt die Typ-Bezeichnung "Bachelor". Leer lassen, wenn nicht benötigt
$this->phrasen['bewerbung/hackTypBezeichnungMaster']='Master Studiengänge'; // Überschreibt die Typ-Bezeichnung "Master". Leer lassen, wenn nicht benötigt
$this->phrasen['bewerbung/hackStufeBezeichnungBachelor']=''; // Überschreibt die ZGV Stufen-Bezeichnung "Bachelor". Leer lassen, wenn nicht benötigt
$this->phrasen['bewerbung/hackStufeBezeichnungMaster']=''; // Überschreibt die ZGV Stufen-Bezeichnung "Master". Leer lassen, wenn nicht benötigt
$this->phrasen['bewerbung/prioritaet']='Priorität';
$this->phrasen['bewerbung/studierendenDaten']='Aktuelle Studierendendaten';
$this->phrasen['bewerbung/keineRtTermineZurAuswahl']='Derzeit stehen keine Reihungstesttermine zur Auswahl.';
$this->phrasen['bewerbung/erfolgreichBeworbenMailBachelor']='
<html>
	<head>
		<title>Sancho Mail</title>
	</head>
	<body>
		<center>
			<table cellpadding="0" cellspacing="0" style="border: 2px solid #000000; padding: 0px; max-width: 850px;
				border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;">
				<tr>
					<td align="center">
						<img src="cid:sancho_header" alt="header_image" style="width: 100%%; display: block"/>

					</td>
				</tr>
				<tr>
					<td align="center">
						<table cellpadding="0" cellspacing="0" width="100%%" style="font-family: courier, verdana, sans-serif; font-size: 0.95em; border-bottom: 2px solid #000000;">
							<tr>
								<td style="padding-left: 8%%; padding-right: 8%%; padding-top: 5%%; padding-bottom: 5%%;">
									Sehr %3$s %1$s %2$s,<br><br>
									Sie haben sich erfolgreich für %4$s beworben. In der Regel werden Sie innerhalb von 5 Werktagen kontaktiert. Gegebenenfalls werden Sie dann aufgefordert, weitere Dokumente hochzuladen.<br><br>
									Den Status Ihrer Bewerbung können Sie jederzeit im <a href="'.APP_ROOT.'addons/bewerbung/cis/registration.php">Bewerbungstool</a> verfolgen.<br>
									Sollten Sie Fragen zur Bewerbung haben, kontaktieren Sie bitte unser Infocenter <a href="mailto:studienberatung@technikum-wien.at">studienberatung@technikum-wien.at</a>.<br><br>
									Mit freundlichen Grüßen<br>
									Fachhochschule Technikum Wien
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td align="center">
						<img src="cid:sancho_footer" alt="footer_image" style="width: 100%%; display: block"/>
					</td>
				</tr>
			</table>
		</center>
	</body>
</html>
';
$this->phrasen['bewerbung/erfolgreichBeworbenMail']='
<html>
	<head>
		<title>Sancho Mail</title>
	</head>
	<body>
		<center>
			<table cellpadding="0" cellspacing="0" style="border: 2px solid #000000; padding: 0px; max-width: 850px;
				border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;">
				<tr>
					<td align="center">
						<img src="cid:sancho_header" alt="header_image" style="width: 100%%; display: block"/>
					</td>
				</tr>
				<tr>
					<td align="center">
						<table cellpadding="0" cellspacing="0" width="100%%" style="font-family: courier, verdana, sans-serif; font-size: 0.95em; border-bottom: 2px solid #000000;">
							<tr>
								<td style="padding-left: 8%%; padding-right: 8%%; padding-top: 5%%; padding-bottom: 5%%;">
									Sehr %3$s %1$s %2$s,<br><br>
									Sie haben sich erfolgreich für %4$s beworben. In der Regel werden Sie innerhalb von 5 Werktagen kontaktiert. Gegebenenfalls werden Sie dann aufgefordert, weitere Dokumente hochzuladen.<br><br>
									Den Status Ihrer Bewerbung können Sie jederzeit im <a href="'.APP_ROOT.'addons/bewerbung/cis/registration.php">Bewerbungstool</a> verfolgen.<br>
									Sollten Sie Fragen zur Bewerbung haben, kontaktieren Sie uns bitte unter <a href="mailto:%5$s">%5$s</a>.<br><br>
									Mit freundlichen Grüßen<br>
									Fachhochschule Technikum Wien
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td align="center">
						<img src="cid:sancho_footer" alt="footer_image" style="width: 100%%; display: block"/>
					</td>
				</tr>
			</table>
		</center>
	</body>
</html>';
$this->phrasen['bewerbung/erfolgreichBeworbenMailBetreff']='Bewerbungsbestätigung Fachhochschule Technikum Wien';
$this->phrasen['bewerbung/bewerbungsfrist']='Bewerbungsfrist';
$this->phrasen['bewerbung/bewerbungszeitraum']='Bewerbungszeitraum (innerhalb der EU)';
$this->phrasen['bewerbung/bewerbungsfristAbgelaufen']='Bewerbungsfrist abgelaufen';
$this->phrasen['bewerbung/bewerbungAusserhalbZeitraum']='Ihre Bewerbung liegt außerhalb des Bewerbungszeitraums. Ihre Bewerbung wurde nicht verschickt.';
$this->phrasen['bewerbung/unbegrenzt']='offen';
$this->phrasen['bewerbung/bewerbungszeitraumStartetAm']='Bewerbungszeitraum startet am %s';
$this->phrasen['bewerbung/bewerbungsfristEndetInXTagen']='Bewerbungsfrist endet in %s Tagen';
$this->phrasen['bewerbung/bewerbungsfristEndetHeute']='Bewerbungsfrist endet heute';
$this->phrasen['bewerbung/bewerbungsfristFuerStudiensemesterXAbgelaufen']='Bewerbungsfrist für das %s ist abgelaufen';
$this->phrasen['bewerbung/bewerbungsfristFuerEinenStudiengangAbgelaufen']='Die Bewerbungsfrist für mindestens einen der gewählten Studiengänge ist abgelaufen';
$this->phrasen['bewerbung/bewerbungDerzeitNichtMoeglich']='Bewerbung derzeit nicht möglich. Bitte kontaktieren Sie <a href="mailto:%1$s">%1$s</a>';
$this->phrasen['bewerbung/bewerbungenFuerAb']='Bewerbungen fürs %s ab %s';
$this->phrasen['bewerbung/bewerbungenFuerStudiensemesterXX']='Bewerbungen für das Studiensemester %s';
$this->phrasen['bewerbung/bewerbungAbschicken']='Bewerbung abschicken';
$this->phrasen['bewerbung/erklaerungBewerbungAbschickenFuerStudiengang']='Wenn Sie alle Daten vervollständigt haben, können Sie Ihre Bewerbung für <span style="text-align: center; font-weight: bold; display: block; padding-top: 10px">%s</span> hier abschicken. Sie werden in der Regel innerhalb von 5 Werktagen kontaktiert.';
$this->phrasen['bewerbung/anmeldungStornieren']='Anmeldung stornieren';
$this->phrasen['bewerbung/anmeldungStornierenBis']='Stornieren (bis %s)';
$this->phrasen['bewerbung/menuUebersicht']='Übersicht';
$this->phrasen['bewerbung/menuSicherheit']='Sicherheit';
$this->phrasen['bewerbung/erklaerungSicherheit']='Aus Datenschutzgründen empfehlen wir Ihnen, sich einen neuen Zugangscode generieren zu lassen.<br>
													Klicken Sie dazu einfach auf den Button und notieren Sie sich den neu generierten Code.<br><br>
													Falls Sie den Code vergessen, klicken Sie auf der Login-Seite auf "Zugangscode vergessen?"';
$this->phrasen['bewerbung/buttonNeuerZugangscode']='Neuen Zugangscode generieren';
$this->phrasen['bewerbung/erfolgsMessageNeuerZugangscode']='<p>Ihr neuer Zugangscode lautet:</p><p>%s</p><p>Bitte notieren Sie sich den neuen Zugangscode.</p>';
$this->phrasen['bewerbung/keineStudienrichtungenFuerStudiensemesterZurAuswahl']='Für das gewählte Studiensemester stehen derzeit keine Studienrichtungen zur Auswahl';
$this->phrasen['bewerbung/studienberechtigungErlangtIn']='Studienberechtigung erlangt in';
$this->phrasen['bewerbung/studienberechtigungErlangtInErklaerung']='Hier ist jene Nation zu wählen, in der Sie Ihren Abschluss erlangt haben/erlangen werden, der Sie zu einem Studium berechtigt (optional für Lehrgänge)';
$this->phrasen['bewerbung/StatusSeitDatum']='%1$s seit %2$s';
$this->phrasen['bewerbung/bewerbungszeitraumFuer']='Bewerbungszeitraum für %1$s';
$this->phrasen['bewerbung/bitteAuswaehlenBaMa']='-- Für Bachelor und Master bitte auswählen --';
$this->phrasen['bewerbung/bitteZGVausweahlen']='Wenn Sie sich für einen Bachelor- oder Master-Studiengang bewerben, wählen Sie bitte das Land aus, in dem Sie die Studienberechtigung erlangt haben';
$this->phrasen['bewerbung/allgemeineDokumente']='Allgemeine Dokumente';
$this->phrasen['bewerbung/akteBereitsVorhanden']='Die maximal erlaubte Anzahl an Uploads für dieses Dokument ist erreicht';
$this->phrasen['bewerbung/vervollstaendigenSieIhreDaten']='Vervollständigen Sie Ihre Daten um die Bewerbung abschicken zu können';
$this->phrasen['bewerbung/datenUnvollstaendig']='Daten unvollständig';
$this->phrasen['bewerbung/durchsuchen']='Durchsuchen...';
$this->phrasen['bewerbung/infotextRegistrationBewerbungGesperrt']='Derzeit sind Bewerbungen ausschließlich für Lehrgänge möglich.<br>Für Bachelor- und Master-Studiengänge öffnet die Bewerbungsmöglichkeit wieder im September 2019';
$this->phrasen['bewerbung/mehr']='Mehr';
$this->phrasen['bewerbung/dokumentNochNichtVorhanden']='Dokument noch nicht vorhanden? Dann klicken Sie:';
$this->phrasen['bewerbung/keineDokumenteErforderlich']='Derzeit sind keine Dokumente erforderlich';
$this->phrasen['bewerbung/menuUebersichtBewerbungAbschicken']='Übersicht / Bewerbung abschicken';
$this->phrasen['bewerbung/logoutInfotext']='Sie haben Ihre Bewerbung noch nicht abgeschickt. Sind Sie sicher, dass Sie sich ausloggen möchten?';
$this->phrasen['bewerbung/menuMessages']='Nachrichten';
$this->phrasen['bewerbung/erklaerungMessages']='Hier können Sie Nachrichten abrufen';
$this->phrasen['bewerbung/dateiUploadLeer']='Die Datei konnte nicht hochgeladen werden. Möglicherweise wurde die Dateigröße von 15MB überschritten';
$this->phrasen['bewerbung/zustimmungAGB']='Zustimmung zu unseren allgemeinen Geschäftsbedingungen.';
$this->phrasen['bewerbung/bitteAGBZustimmen']='Sie müssen den AGB zustimmen, um Ihre Bewerbung abschicken zu können';
$this->phrasen['bewerbung/zahlungAusstaendig']='Es sind noch Zahlungen offen. Sie können die Bewerbung erst abschicken, wenn alle Zahlungen eingegangen sind.';
$this->phrasen['bewerbung/microsoftMailWarning']='<b>Achtung!</b> Derzeit kommt es bei E-Mail Zustellungen an @hotmail, @outlook und @live Adressen zu Empfangsproblemen seitens Microsoft. Eine Zustellung kann nicht garantiert werden! <br>Bitte verwenden Sie nach Möglichkeit eine andere E-Mail Adresse.';
$this->phrasen['bewerbung/herkunftDesBewerbers']='Herkunft';
$this->phrasen['bewerbung/ort']='Anmerkung';
$this->phrasen['bewerbung/zeitzone']='Zeitzone';
$this->phrasen['bewerbung/zeitzoneMEZ']='Wien (MEZ)';
$this->phrasen['bewerbung/akten']='Akten';
$this->phrasen['bewerbung/herunterladen']='%s herunterladen';
$this->phrasen['bewerbung/akzeptieren']='%s abschließen';
$this->phrasen['bewerbung/akzeptiert']='%s akzeptiert';
$this->phrasen['bewerbung/keineAktenVorhanden']='Derzeit sind keine Akten zum Herunterladen vorhanden';
$this->phrasen['bewerbung/textRuecktrittsrecht']='Ich nehme zur Kenntnis, dass mir das Recht zusteht, binnen vierzehn Tagen ohne Angabe von Gründen diesen Vertrag zu widerrufen.
								<br><p class="alert alert-info">
								<b>Information:</b><br>
								Um dieses Widerrufsrecht auszuüben, müssen Sie die Fachhochschule Technikum Wien innerhalb
								der vierzehn Tage ab Abschluss mittels einer eindeutigen Erklärung per E-Mail an Ihre
								Studiengangsassistenz über Ihren Entschluss, diesen Vertrag zu widerrufen, informieren.</p>';
$this->phrasen['bewerbung/textAusbildungsvertrag']='Ich habe den Ausbildungsvertrag gelesen und erkläre mich mit dem Vertragsinhalt einverstanden.';
$this->phrasen['bewerbung/informationDatenverwendungStudierende']='<a href=\''.APP_ROOT.'cms/dms.php?id=149169\' target=\'_blank\'><span class="glyphicon glyphicon-file"></span>Information über die Verwendung personenbezogener Daten von Studierenden</a>';

// Reihungstest
$this->phrasen['bewerbung/anmeldungReihungstestMailBetreff']='Bestätigung Reihungstestanmeldung Fachhochschule Technikum Wien';
$this->phrasen['bewerbung/anmeldungReihungstestMail']='Sehr %3$s %1$s %2$s,<br><br>
Sie haben sich erfolgreich für den Reihungstest am %4$s beworben. Der Test beginnt um %5$s und dauert etwa 3,5 Stunden.<br><br>
Bitte bringen Sie einen amtlichen Lichtbildausweis zur Überprüfung Ihrer Identität mit.<br>
Den Raum können Sie spätestens 2 Tage vor Testbeginn im Bewerbungstool sehen<br>
Sollten Sie Fragen haben, kontaktieren Sie bitte unser Infocenter <a href="mailto:studienberatung@technikum-wien.at">studienberatung@technikum-wien.at</a>.<br><br>
Mit freundlichen Grüßen<br>
Fachhochschule Technikum Wien';
$this->phrasen['bewerbung/reihungstestInfoTextAngemeldet']='<div class="alert alert-info">
<p>Wir starten pünktlich mit der Identitätskontrolle. Stellen Sie daher sicher, dass Sie zu diesem Zeitpunkt im Zoom Warteraum sind (Den Link erhalten Sie 2 Werktage vor Ihrem gewählten Reihungstesttermin)</p>
</div><br>';
$this->phrasen['bewerbung/anmeldefrist']='Anmeldefrist';
$this->phrasen['bewerbung/infoVorgemerktFuerQualifikationskurs']='Sie sind als TeilnehmerIn für die Qualifikationskurse vorgemerkt. Sobald sie dort bestätigt wurden, können Sie hier einen Termin für den Reihungstest wählen.';
$this->phrasen['bewerbung/raumzuteilungFolgt']='Details folgen 2 Werktage vor Testbeginn per E-Mail (Bitte auch Spamordner überprüfen!)';
$this->phrasen['bewerbung/sieHabenFolgendenTerminGewaehlt']='Danke für Ihre Anmeldung zum Reihungstest.<br>Ihre Priorisierung der Studiengänge ist fixiert und kann nur bis zur Anmeldefrist geändert werden.<br>Nach Absolvierung des Reihungstests kann die Priorisierung <b>NICHT</b> mehr geändert werden.';
$this->phrasen['bewerbung/loginReihungstest']='<h3>Online-Reihungstest</h3>Klicken Sie <b>am Tag Ihres Reihungstesttermins</b> auf den Button "Zum Reihungstest"<br>
												Bitte beachten Sie, dass Sie <u>Mozilla Firefox</u> als Browser verwenden, da es sonst zu Darstellungsproblemen kommen kann.<br><br>
												<a href="'.APP_ROOT.'cis/testtool/index.php?prestudent=%s" class="btn btn-primary" role="button" target="_blank">Zum Reihungstest</a>';
$this->phrasen['bewerbung/informationenRTvorhanden']='Informationen zum Reihungstest vorhanden';
$this->phrasen['bewerbung/anmerkungBerufstaetigkeit']='<b>NUR</b> für Studiengänge in Abendform (berufsbegleitend) und dualer Form verpflichtend<br>&nbsp;&nbsp;&nbsp;&nbsp;Wir weisen darauf hin, dass eine Berufstätigkeit nicht zwingend erforderlich ist!';
// Ausbildung
$this->phrasen['bewerbung/menuAusbildung']='Ausbildung';
$this->phrasen['bewerbung/ausbildung']='Ausbildung zu Ihrer Zugangsvoraussetzung';
$this->phrasen['bewerbung/ausbildungSchule']='Name der Schule';
$this->phrasen['bewerbung/ausbildungSchuleAdresse']='Adresse der Schule';
$this->phrasen['bewerbung/paymentInfoText']='';

// Rechnungskontakt
$this->phrasen['bewerbung/menuRechnungsKontaktinformationen']='Rechnungsdaten';
$this->phrasen['bewerbung/rechnungsadresseInfoText']='Geben Sie hier bitte eine etwaige von Ihrer Heimatadresse abweichende Rechnungsadresse an (bitte vollständig ausfüllen)';
$this->phrasen['bewerbung/rechnungsKontakt']='Rechnungskontakt';
$this->phrasen['bewerbung/rechnungsAdresse']='Rechnungsadresse';
$this->phrasen['bewerbung/re_anrede']='Anrede';
$this->phrasen['bewerbung/re_titel']='Titel';
$this->phrasen['bewerbung/re_vorname']='Vorname';
$this->phrasen['bewerbung/re_nachname']='Nachname';
$this->phrasen['bewerbung/staatsbuergerschaft']='Staatsangehörigkeit';
$this->phrasen['bewerbung/staatsbuergerschaftErklaerung']='Bitte geben Sie hier Ihre Staatsbürgerschaft an';
$this->phrasen['bewerbung/bitteAuswaehlenStaatsbuergerschaft']='-- Bitte auswählen --';
?>
