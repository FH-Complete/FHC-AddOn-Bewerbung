<?php
define('BEWERBERTOOL_STUDIENAUSWAHL_ANZEIGEN', true);
define('BEWERBERTOOL_STANDORTAUSWAHL_ANZEIGEN', false);

define('BEWERBERTOOL_REIHUNGSTEST_ANZEIGEN', true);
define('BEWERBERTOOL_ZAHLUNGEN_ANZEIGEN', true);
define('BEWERBERTOOL_DOKUMENTE_ANZEIGEN', true);
define('BEWERBERTOOL_ZGV_ANZEIGEN', true);
define('BEWERBERTOOL_BERUFSTAETIGKEIT_ANZEIGEN', true);
// Wenn hier eine Mailadresse angegeben ist, werden die Bewerbungen aus der Onlinebwerbung an diese Adresse gesendet.
// Wenn leer dann wird an BEWERBERTOOL_BEWERBUNG_EMPFAENGER geschickt, sonst an die Studiengangsadresse.
define('BEWERBERTOOL_MAILEMPFANG', '');
// Wenn true dann koennen Dokumente nachgereicht werden, wenn false dann nicht
define('BEWERBERTOOL_DOKUMENTE_NACHREICHEN', true);
//Soll beim nachtraeglichen Upload von Dokumenten im Bewerbertool ein Mail versand werden?
define('BEWERBERTOOL_SEND_UPLOAD_EMPFAENGER', true);
//Wer soll beim nachtraeglichen Upload von Dokumenten im Bewerbertool benachrichtigt werden. Kommagetrenntes Array in der Form: "Stg-Kz" => "empfaenger@domain.at". zB: "227" => "info.bbe@technikum-wien.at",
define('BEWERBERTOOL_UPLOAD_EMPFAENGER', serialize(array()));
//Array von Empfaengern fuer das Abschicken von Bewerbungen aus dem Bewerbungstool. Kommagetrenntes Array in der Form: "Stg-Kz" => "empfaenger@domain.at". zB: "227" => "info.bbe@technikum-wien.at",
define('BEWERBERTOOL_BEWERBUNG_EMPFAENGER', serialize(array()));

// Google Tag Manager HTML/JS Snippet für Bewerbertool Tracking
define('BEWERBERTOOL_GTM', '');

// Beschraenkt die Anzahl an Studiengaengen (nur Bakk oder Master), fuer die man sich pro Studiensemester online bewerben kann.
// Wenn leer, gibt es keine einschraenkung. Wenn zB 3, kann man nur 3 auswaehlen, dann sind alle Anderen optionen deaktiviert.
define('BEWERBERTOOL_MAX_STUDIENGAENGE', '');

//Zeigt das Input fuer die Sozialversicherungsnummer an.
// Moegliche Werte sind true, false oder ein String, getrennt mit Semikolons, jener nation_codes, bei denen das SVNR-Input angezeigt werden soll (zB 'A;D;I,CH').
define('BEWERBERTOOL_SOZIALVERSICHERUNGSNUMMER_ANZEIGEN', true);

define('BEWERBERTOOL_DATEN_TITEL_ANZEIGEN', false);

// Sendet eine Bestätigungsmail an den Interessenten wenn die Bewerbung abgeschickt wird: html-content bewerbung/erfolgreichBeworbenMail
define('BEWERBERTOOL_ERFOLGREICHBEWORBENMAIL', false);

// Zeigt einen weiteren Karteireiter für die Eingabe einer abweichenden Rechnungsadresse an
// Rechnungstelefonnummer: Kontakttyp: re_telefon
// RechnungsEmail: Kontakttyp: re_email
define('BEWERBERTOOL_RECHNUNGSKONTAKT_ANZEIGEN', false);

// Wenn ein Bewerber eine Bewerbung storniert, wird ein Status "Abgewiesener" angelegt
// Dieser wird mit dem hier angegebenen Statusgrund versehen. Integer.
define('BEWERBERTOOL_STORNIERUNG_STATUSGRUND_ID', '');

// Wenn true, wird bei der Registration die Checkbox mit der (verpflichtenden) Zustimmung zur Datenübermittlung angezeigt 
define('BEWERBERTOOL_SHOW_ZUSTIMMUNGSERKLAERUNG_REGISTRATION', false);

// Wenn true, wird für jede Bewerbung ein neuer PreStudent-Datensatz angelegt. 
// Ansonsten wird ein PreStudent des selben Studiengangs ermittelt (wenn vorhanden) und ein neuer Status an diesen angehängt.
define('BEWERBERTOOL_ALWAYS_CREATE_NEW_PRESTUDENT_FOR_APPLICATION', false);

// Soll "Aufmerksam durch" ein Pflichtfeld sein um die Bewerbung abschicken zu können? Mögliche Werte: true oder false
define('BEWERBERTOOL_AUFMERKSAMDURCH_PFLICHT', false);

// Soll "Aufmerksam durch" ein Pflichtfeld sein um die Bewerbung abschicken zu können? Mögliche Werte: true oder false
define('BEWERBERTOOL_GEBURTSORT_PFLICHT', false);

// Soll "Aufmerksam durch" ein Pflichtfeld sein um die Bewerbung abschicken zu können? Mögliche Werte: true oder false
define('BEWERBERTOOL_GEBURTSNATION_PFLICHT', false);
?>
