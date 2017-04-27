<?php
$this->phrasen['bewerbung/ungueltigerZugriff']='ungültiger Zugriff';
$this->phrasen['bewerbung/welcome']='Willkommen bei der Online Bewerbung';
$this->phrasen['bewerbung/registration']='Zugangscode für Ihre Bewerbung';
$this->phrasen['bewerbung/registrieren']='Anmelden';
$this->phrasen['bewerbung/abschicken']='Abschicken';
$this->phrasen['bewerbung/registrierenOderZugangscode']='<a href="'.$_SERVER['PHP_SELF'].'?method=registration">Hier registrieren</a> oder Zugangscode eingeben';
$this->phrasen['bewerbung/einleitungstext']='Bitte füllen Sie das Formular aus, wählen Sie Ihren bevorzugten Studiengang und klicken Sie auf "Abschicken". <br>
		Danach erhalten Sie eine E-Mail mit Zugangscode an die angegebene Adresse.
		Mit dem Zugangscode können Sie sich jederzeit einloggen, Ihre Daten vervollständigen, Studienrichtungen hinzufügen und sich unverbindlich bewerben.<br><br>
		Falls Sie Interesse an mehreren Studiengängen haben, können Sie bis zu 
		'.(defined('BEWERBERTOOL_MAX_STUDIENGAENGE') && BEWERBERTOOL_MAX_STUDIENGAENGE != ''?BEWERBERTOOL_MAX_STUDIENGAENGE:'').' 
		Studienrichtungen anklicken. <br>Wenn Sie mehr Informationen benötigen, 
		steht Ihnen unsere <a href=\'https://www.technikum-wien.at/studieninformationen/studienberatung-kontaktieren/\' target=\'_blank\'>Studienberatung</a> 
		gerne persönlich, telefonisch oder per E-Mail zur Verfügung.';
$this->phrasen['bewerbung/login']='Login';
$this->phrasen['bewerbung/zugangscode']='Zugangscode';
$this->phrasen['bewerbung/fallsVorhanden']='(falls vorhanden)';
$this->phrasen['bewerbung/mailtextHtml']='Bitte sehen Sie sich die Nachricht in der HTML-Ansicht an um den Link vollständig darzustellen.';
$this->phrasen['bewerbung/anredeMaennlich']='geehrter Herr';
$this->phrasen['bewerbung/anredeWeiblich']='geehrte Frau';
$this->phrasen['bewerbung/mailtext']='Sehr %4$s %1$s %2$s!<br><br>
        Vielen Dank für Ihr Interesse an einem Studiengang oder Lehrgang der '.CAMPUS_NAME.'. <br>
        Verwenden Sie für Ihre Bewerbung bitte folgenden Link und Zugangscode: <br><br>
        <a href="'.APP_ROOT.'addons/bewerbung/cis/registration.php?code=%3$s">Link zur Bewerbung</a><br>
        Zugangscode: %3$s<br><br>
        Mit freundlichen Grüßen, <br>
        '.CAMPUS_NAME;
$this->phrasen['bewerbung/zugangsdatenFalsch']='Der eingegebene Zugangscode ist falsch oder Sie haben sich noch nicht registriert.';
$this->phrasen['bewerbung/fehlerBeimSenden']='Beim Senden der E-Mail ist ein Fehler aufgetreten.';
$this->phrasen['bewerbung/emailgesendetan']='Die E-Mail mit Ihrem Zugangscode wurde erfolgreich an %s verschickt.';
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
$this->phrasen['bewerbung/egal']='gleichgültig';
$this->phrasen['bewerbung/orgform']='Organisationsform';
$this->phrasen['bewerbung/orgform/BB']='Berufsbegleitend';
$this->phrasen['bewerbung/orgform/VZ']='Vollzeit';
$this->phrasen['bewerbung/orgform/teilzeit']='Teilzeit';
$this->phrasen['bewerbung/orgform/DL']='Fernstudium';
$this->phrasen['bewerbung/orgform/DDP']='Double Degree Program';
$this->phrasen['bewerbung/orgform/PT']='Part time';
$this->phrasen['bewerbung/orgform/ZGS']='Zielgruppenspezifisch';
$this->phrasen['bewerbung/orgform/DUA']='Dual';
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
$this->phrasen['bewerbung/allgemeineErklaerung']='Wir freuen uns, dass Sie sich für einen oder mehrere unserer Studiengänge oder Lehrgänge interessieren.<br>
	Sie können online bis zu '.(defined('BEWERBERTOOL_MAX_STUDIENGAENGE') && BEWERBERTOOL_MAX_STUDIENGAENGE != ''?BEWERBERTOOL_MAX_STUDIENGAENGE:'').' Studienrichtungen auswählen.<br>
	Klicken Sie auf den grünen Button, um Ihrer Bewerbung einen Studiengang oder Lehrgang hinzuzufügen.<br><br>
	Wenn Sie mehr Informationen benötigen, steht Ihnen unsere <a href=\'https://www.technikum-wien.at/studieninformationen/studienberatung-kontaktieren/\' target=\'_blank\'>Studienberatung</a> gerne persönlich, telefonisch oder per E-Mail zur Verfügung.<br><br>
	Bitte füllen Sie das Formular vollständig aus. Sind alle Werte eingetragen, können Sie unter "Abschließen" Ihre Bewerbung an die zuständige Assistenz schicken.';
$this->phrasen['bewerbung/erklaerungStudierende']='Wir freuen uns über Ihr Interesse an unseren Weiterbildungsprogrammen.<br><br>
	Bitte klicken Sie auf "Neue Bewerbung für Studiengang/Lehrgang hinzufügen" um Ihrer Bewerbung einen Studiengang oder Lehrgang hinzuzufügen.
	Gegebenenfalls sind danach weitere Daten zu ergänzen, bevor Sie die Bewerbung im letzten Schritt abschicken können.';
$this->phrasen['bewerbung/aktuelleBewerbungen']='Aktuelle Bewerbungen:';
$this->phrasen['bewerbung/status']='Status';
$this->phrasen['bewerbung/legende']='Legende';
$this->phrasen['bewerbung/bewerbungsstatus']='Bewerbungsstatus';
$this->phrasen['bewerbung/keinStatus']='Noch kein Status vorhanden';
$this->phrasen['bewerbung/bestaetigt']='bestätigt';
$this->phrasen['bewerbung/nichtBestaetigt']='noch nicht bestätigt';
$this->phrasen['bewerbung/studiengangHinzufuegen']='Neue Bewerbung für Studiengang/Lehrgang hinzufügen';
$this->phrasen['bewerbung/weiter']='Weiter';
$this->phrasen['bewerbung/geburtsnation']='Geburtsnation';
$this->phrasen['bewerbung/svnr']='Österr. Sozialversicherungsnr.';
$this->phrasen['bewerbung/maennlich']='männlich';
$this->phrasen['bewerbung/weiblich']='weiblich';
$this->phrasen['bewerbung/berufstaetigkeit']='Berufstätigkeit';
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
$this->phrasen['bewerbung/bitteDokumenteHochladen']='Die Studiengangsassistenz wird Sie darüber in Kenntnis setzen, welche Dokumente für Ihr Studium benötigt werden. Sie haben außerdem die Möglichkeit, Dokumente nachzureichen. Klicken Sie dazu das Symbol für "Dokument wird nachgereicht" (Sanduhr). Danach haben Sie die Möglichkeit, eine kurze Begründung einzutragen.';
$this->phrasen['bewerbung/linkDokumenteHochladen']='Dokumente hochladen';
$this->phrasen['bewerbung/dokumenteZumHochladen']='Benötigte Dokumente:';
$this->phrasen['bewerbung/dokumentName']='Name';
$this->phrasen['bewerbung/benoetigtFuer']='Benötigt für';
$this->phrasen['bewerbung/dokumentErforderlich']='Dokument erforderlich';
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
$this->phrasen['bewerbung/fuerReihungstestAnmelden']='Sie können sich für folgende Reihungstests anmelden:';
$this->phrasen['bewerbung/fehler']='Es ist ein Fehler aufgetreten';
$this->phrasen['bewerbung/angemeldetPlaetze']='angemeldet / Plätze';
$this->phrasen['bewerbung/uhrzeit']='Uhrzeit';
$this->phrasen['bewerbung/stornieren']='stornieren';
$this->phrasen['bewerbung/menuBewerbungAbschicken']='Bewerbung abschicken';
$this->phrasen['bewerbung/erklaerungBewerbungAbschicken']='Wenn Sie alle Daten vervollständigt haben, können Sie Ihre Bewerbung abschicken.	Die jeweilige Assistenz wird sich in den folgenden Tagen bezüglich Ihrer Bewerbung bei Ihnen melden. <br>
		Falls Sie innerhalb von 10 Werktagen keine Reaktion erhalten, nehmen Sie bitte Kontakt mit uns auf.';
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
$this->phrasen['bewerbung/buchungsnummerNichtVorhanden']='Buchungsnummer %s nicht vorhanden';
$this->phrasen['bewerbung/teilweiseVollstaendig']='teilweise vollständig';
$this->phrasen['bewerbung/vollstaendig']='vollständig';
$this->phrasen['bewerbung/unvollstaendig']='unvollständig';
$this->phrasen['bewerbung/teilweiseVollstaendig']='teilweise vollständig';
$this->phrasen['bewerbung/maxAnzahlTeilnehmer']='max. Teilnehmeranzahl erreicht';
$this->phrasen['bewerbung/erfolgreichBeworben']='Sie haben sich erfolgreich für %s beworben. Die zuständige Assistenz wird sich in den nächsten Tagen bei Ihnen melden. Falls Sie innerhalb von 10 Werktagen keine Reaktion erhalten, nehmen Sie bitte Kontakt mit uns auf.';
$this->phrasen['bewerbung/fehlerBeimVersendenDerBewerbung']='Es ist ein Fehler beim Versenden der Bewerbung aufgetreten. Bitte versuchen Sie es nocheinmal.';
$this->phrasen['bewerbung/svnrBereitsVorhanden']='SVNR bereits vorhanden';
$this->phrasen['bewerbung/menuBewerbungFuerStudiengang']='Bewerbung für einen Studiengang';
$this->phrasen['bewerbung/emailBodyStart']='Es gibt eine neue Bewerbung mit folgenden Daten: <br>';
$this->phrasen['bewerbung/emailDokumentuploadStart']='Das folgende Dokument wurde hochgeladen: <br>';
$this->phrasen['bewerbung/emailBodyEnde']='Für mehr Details öffnen Sie bitte den Personendatensatz im FAS.';
$this->phrasen['bewerbung/fileUpload']='File-Upload';
$this->phrasen['bewerbung/fehlerKeinePersonId']='Es wurde keine Person_id angegeben';
$this->phrasen['bewerbung/woWurdeUrkundeAusgestellt']='Wo wurde die Urkunde ausgestellt?';
$this->phrasen['bewerbung/ausbildungstyp']='Ausbildungstyp';
$this->phrasen['bewerbung/lehrgang']='Lehrgang';
$this->phrasen['bewerbung/keineStgAngeboten']='Es werden derzeit keine Studiengänge angeboten';
$this->phrasen['bewerbung/keineLehrgAngeboten']='Es werden derzeit keine Lehrgänge angeboten';
$this->phrasen['bewerbung/aufmerksamdurch']='Wie sind Sie auf uns aufmerksam geworden?';
$this->phrasen['bewerbung/BewerbungBereitsVerschickt']='Ihre Bewerbung wird bearbeitet';
$this->phrasen['bewerbung/bitteZuerstStudiengangWaehlen']='Bitte wählen Sie zuerst den gewünschten Studiengang im Bereich "Allgemein" aus.';
$this->phrasen['bewerbung/ExtensionInformation']='Maximale Dateigröße je Dokument: 15 MB.<br>Unterstützte Dateiformate:';
$this->phrasen['bewerbung/falscherDateityp']='Dieses Dateiformat wird nicht unterstützt';
$this->phrasen['bewerbung/mailadresseBereitsGenutzt']='Die E-Mail Adresse %s wurde bereits für eine Bewerbung genutzt und es ist ein Zugangscode vorhanden. Sie können sich den Zugangscode noch einmal an diese Adresse schicken lassen und nach dem Login weitere Bewerbungen hinzufügen.';
$this->phrasen['bewerbung/mailadresseBereitsVorhanden']='Die E-Mail Adresse %s ist bereits im System vorhanden und kann nicht gespeichert werden. Bitte geben Sie eine andere E-Mail Adresse ein.';
$this->phrasen['bewerbung/buttonBewerbungUnvollstaendig']='Pflichtfelder unvollständig';
$this->phrasen['bewerbung/prestudentID']='Prestudent ID';
$this->phrasen['bewerbung/bewerbung']='Bewerbung';
$this->phrasen['bewerbung/dokumentuploadZuBewerbung']='Dokumentupload %s zu Bewerbung';
$this->phrasen['bewerbung/maennlich']='Männlich';
$this->phrasen['bewerbung/weiblich']='Weiblich';
$this->phrasen['bewerbung/maturazeugnis']='Maturazeugnis';
$this->phrasen['bewerbung/details']='Details';
$this->phrasen['bewerbung/codeZuschicken']='Code zuschicken';
$this->phrasen['bewerbung/codeZuschickenAnleitung']='Wenn Sie sich schon einmal registriert aber den Zugangscode verloren oder vergessen haben, können Sie sich hier den Zugangscode erneut zuschicken lassen. Bitte geben Sie dazu Ihre Mailadresse ein und drücken Sie auf "Code zuschicken"';
$this->phrasen['bewerbung/keinCodeVorhanden']='Für diese E-Mail Adresse ist noch kein Zugangscode vorhanden. Bitte <a href="'.$_SERVER['PHP_SELF'].'?method=registration">melden Sie sich vorher an</a>.';
$this->phrasen['bewerbung/zgvDatumNichtZukunft']='Das Datum der Zugangsvoraussetzung darf nicht in der Zukunft liegen. Wenn Sie die Zugangsvoraussetzung erst später erbringen, lassen Sie das Datum bitte leer.';
$this->phrasen['bewerbung/ab']='ab';
$this->phrasen['bewerbung/adresse']='Adresse (Hauptwohnsitz)';
$this->phrasen['bewerbung/notizVom']='Notiz vom';
$this->phrasen['bewerbung/anmerkung']='Anmerkung';
$this->phrasen['bewerbung/anmerkungPlaceholder']='Hier haben Sie noch die Möglichkeit, zusätzliche Anmerkungen einzutragen, die Sie der Assistenz mitteilen möchten';
$this->phrasen['bewerbung/orgformMussGewaehltWerden']='Es muss eine Organisationsform gewählt werden';
$this->phrasen['bewerbung/hierUnverbindlichAnmelden']='Hier unverbindlich anmelden';
$this->phrasen['bewerbung/keineOrgformVorhanden']='Für das gewähle Studiensemester ist noch keine Organisationsform hinterlegt';
$this->phrasen['bewerbung/bitteOrgformWaehlen']='Wenn Sie einen Studiengang mit mehreren Organisationsformen wählen, müssen Sie eine Priorität angeben';
$this->phrasen['bewerbung/orgformWaehlen']='Organisationsform wählen';
$this->phrasen['bewerbung/orgformBeschreibungstext']='Bitte geben Sie an, für welche Organisationsform Sie sich interessieren. Für den Fall, dass alle Plätze in Ihrer gewünschten Organisationsform vergeben sind, können Sie optional eine Alternative angeben';
$this->phrasen['bewerbung/menuAbschließen']='Abschließen';
$this->phrasen['bewerbung/sieHabenNochKeinenZugangscode']='Sie haben noch keinen Zugangscode oder Account an der FH Technikum Wien?';
$this->phrasen['bewerbung/habenSieBereitsEinenZugangscode']='Sie haben bereits einen Zugangscode?';
$this->phrasen['bewerbung/studierenOderArbeitenSieBereits']='Studieren oder arbeiten Sie bereits an der FH Technikum Wien?';
$this->phrasen['bewerbung/zugangscodeVergessen']='Zugangscode vergessen?';
$this->phrasen['bewerbung/dannHiermitAccountEinloggen']='Dann loggen Sie sich hier mit Ihrem CIS-Account ein';
$this->phrasen['bewerbung/dannHierEinloggen']='Dann loggen Sie sich hier ein';
$this->phrasen['bewerbung/dokumentHerunterladen']='Dokument herunterladen';
$this->phrasen['bewerbung/hinweisZGVdatenaenderung']='<b>Hinweis:</b> Aus organisatorischen Gründen können gespeicherte Angaben hier nicht mehr verändert werden. Sollten Angaben fehlerhaft sein, verwenden Sie bitte das Notizfeld im Schritt "Abschließen" oder kontaktieren Sie die Assistenz per E-Mail.';
$this->phrasen['bewerbung/statusBestaetigen']='Status direkt bestätigen';
$this->phrasen['bewerbung/footerText']='';
$this->phrasen['bewerbung/vorbehaltlichAkkreditierung']='Vorbehaltlich der Akkreditierung durch die AQ Austria';
$this->phrasen['bewerbung/auswahlmöglichkeitenImNaechstenSchritt']='Auswahlmöglichkeiten im nächsten Schritt';
$this->phrasen['bewerbung/sieKoennenMaximalXStudiengaengeWaehlen']='Sie können sich online für maximal %s Studiengänge gleichzeitig bewerben. Wenn Sie mehr Informationen benötigen, steht Ihnen unsere <a href=\'https://www.technikum-wien.at/studieninformationen/studienberatung-kontaktieren/\' target=\'_blank\'>Studienberatung</a> gerne persönlich, telefonisch oder per E-Mail zur Verfügung.'; // Link muss mit einfachen Hochkomma maskiert werden, das es sonst im Bewerbungstool zu Anzeigefehlern kommt
$this->phrasen['bewerbung/bitteEineStudienrichtungWaehlen']='Bitte eine Studienrichtung auswählen.';
$this->phrasen['bewerbung/beschreibungTitelPre']='Akademische Titel, die dem Namen vorangestellt werden. ZB.: DI, Mag., Dr.';
$this->phrasen['bewerbung/beschreibungTitelPost']='Akademische Titel, die dem Namen nachgestellt werden. ZB.: BA, MA, PhD';
$this->phrasen['bewerbung/BilduploadInfotext']='Sie können Bilder in den Formaten JPG, PNG oder GIF mit einer Maximalgröße von 15MB hochladen.<br><br><b>Bitte beachten Sie die <a href=\''.APP_ROOT.'cms/content.php?content_id=%s\' target=\'_blank\'>Richtlinien für den Bildupload</a></b>';
$this->phrasen['bewerbung/fotoAuswaehlen']='Klicken Sie auf die Grafik um ein Foto hochzuladen und zuzuschneiden<br>Falls der Upload fehlschlägt, oder ihr Browser den Bildzuschnitt nicht unterstützt, <br>können Sie <a href="dms_akteupload.php?person_id=%s&dokumenttyp=Lichtbil"><b>hier</b></a> die Datei direkt hochladen';
$this->phrasen['bewerbung/akademischeTitel']='Akademische(r) Titel';
$this->phrasen['bewerbung/pflichtfelder']='Pflichtfelder';
$this->phrasen['bewerbung/bitteGueltigeOesterreichischePlzEingeben']='Bitte geben Sie eine gültige österreichische Postleitzahl ein';
$this->phrasen['bewerbung/plzMussGueltigSein']='Postleitzahl muss eine gültige Nummer sein';
$this->phrasen['bewerbung/plzUnbekannt']='Postleitzahl unbekannt';
?>
