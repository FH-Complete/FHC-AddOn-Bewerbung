# Change Log

## [2.1]

### Added
- Bewerbungsfristen werden bei Registration und hinzufügen von Studiengängen angezeigt und berücksichtigt
- Auswahl der Studiengänge bei Registration berücksichtigt Attribut "onlinebewerbung" beim Studienplan

### Fixed
- 

### Changed
- Es gibt nun kein eigenes Auswahl-Modal mehr für verschiedene Orgformen. Diese werden wie in der Registration als aufklappende Optionen angezeigt
- Die Auswahl der Studiengänge ist abhängig von ausgewähltem Studiensemester (je nach eingestellter Gültigkeit des Studienplans)
- GUI-Änderung bei der Auflistung der Studiengänge um die Zugehörigkeit der Fristen besser hervor zu heben

### Updateinfo
- Die Gültigkeit der Studienpläne muss richtig gesetzt sein
- Wenn Bewerbungsfristen eingetragen sind, werden diese Berücksichtigt
- Das Attribut "Onlinebewerbung" beim **STUDIENPLAN** muss gesetzt sein, wenn er im Tool aufscheinen soll
- Das Attribut "Onlinebewerbung" beim **Studiengang** hat weiterhin Gültigkeit und bestimmt, ob der Studiengang überhaupt aufscheint
  Der Name des angezeigten Studiengangs kommt aus dem Studienplan mitt Fallback auf den Namen des Studiengangs

## [2.0.2]

### Added
- Beim Registrationsformular wird nun auch die OE-Kurzbz übergeben bei einem refresh übergeben und vorausgefüllt

### Fixed
- Der richtige Studienplan wird ermittelt und beim Prestudentstatus gespeichert
- Insert- und Updatedatum bei den Kontakten wird gesetzt
- BugFix beim nachträglichen hinzufügen von Bewerbungen bei eingestelltem BEWERBERTOOL_MAX_STUDIENGAENGE
- Wenn mehrere Kontakte des gleichen Typs vorhanden sind, wird nun der aktuellste geladen

### Changed
- 

### Updateinfo
- Konstante "BEWERBERTOOL_SEND_UPLOAD_EMPFAENGER" hinzugefügt

## [2.0.1]

### Added
- Neue Konstante "BEWERBERTOOL_SEND_UPLOAD_EMPFAENGER" definiert, ob beim nachträglichen Upload von Dokumenten ein Mail versand werden soll

### Fixed
- 

### Changed
- 

### Updateinfo
- Konstante "BEWERBERTOOL_SEND_UPLOAD_EMPFAENGER" hinzugefügt

## [2.0]

### Added
- Die Anzahl an Studiengängen, für die man sich pro Studiensemester bewerben kann, kann nun mit der Konstante "BEWERBERTOOL_MAX_STUDIENGAENGE" beschränkt werden.
Die Lehrgänge sind davon nicht betroffen, sondern es werden nur Bachelor und Master gezählt.
Wenn zB "3" eingestellt ist, werden alle verbleibenden Checkboxen deaktiviert sobald drei Checkboxen markiert sind.
Das gilt für die Registration und das nachträgliche hinzufügen von Studiengängen.
Wenn die Konstante leer ist, gibt es keine Einschränkungen.
- Das Input-Feld der Sozialversicherungsnummer kann nun mit der Konstante "BEWERBERTOOL_SOZIALVERSICHERUNGSNUMMER_ANZEIGEN" manipuliert werden. Mögliche Werte sind true (zeigt es immer), false (blendet es immer aus) oder ein Semikolon-getrennter String der Nation-Codes, bei denen das Feld angezeigt werden soll (zB "A;D;ES" zeigt die SVNR bei Österreich, Detschland und Spanien).
- Gemeinde wird im Hintergrund gespeichert. Wenn bei der "Adresse (Hauptwohnsitz)" die Nation "Österreich" gewählt wird, ändert sich das Feld "Ort" von einem Textfeld zu einem DropDown.
Wenn dann eine gültige PLZ eingegeben wird, befüllt sich das DropDown mit passenden Orten zur PLZ. Danach wird beim speichern die richtige Gemeinde für die BIS-Meldung gesetzt.
- Bildzuschnitt beim Lichtbild. Beim Dokumentenupload vom Typ "Lichtbild" wird ein Imagecropper geöffnet mit dem man sein Bild auf das für den FH-Ausweis notwendige Seitenverhältnis von 3:4 zuschneiden kann.
- Beim hinzufügen von Studiengängen (Registrierung und danach) wird der Studienplan ermittelt und beim Prestudentstatus gespeichert.
- Diverse neue Phrasen

### Fixed
- Diverse kleine BugFixes

### Changed
- Beim registrieren wird die Mailadresse mit zustellung = true gespeichert
- Die "Weiter"- und "Zurück"-Buttons speichern nun auch gleichzeitig das Formular.
- Dokumente werden alphabetisch sortiert.
- Die Einträge in "Aufmerksamdurch" werden nun alphabetisch sortiert.
- Die Mailadresse kann nach dem registrieren nicht mehr verändert werden.

### Updateinfo
- Konstanten im bewerbung.config.inc.php setzen
- include/gemeinde.class.php aktualisieren, bzw. einspielen
- cis/public/bild.php aktualisieren


## [1.0]

### Added
- Bewerber können nun eigene Dokumente herunterladen, wenn sie der Besitzer des Dokuments sind und das Dokument in der Onlinebewerbung hochgeladen werden kann
- Bestehende ZGV von anderen PreStudenten dieser Person werden übernommen, wenn ein weiterer Studiengang hinzugefügt wird.
- Einmal gespeicherte ZGV können im Tool nicht mehr verändert werden. Wenn eine Person mehrere PreStudenten hat, können sonst bestehende ZGVs überschrieben werden.
- Wenn Person schon PreStudent in diesem Studiengang war, wird die hochste bestehende PreStudent_id ermittelt und der neue Status zu diesem hinzugefügt.

### Fixed
- Diverse Bugfixes
- Überprüfung auf richtiges Datumsformat bei den ZGV
- Im Dokumentupload-Popup-Fenster können nur mehr Dokumente hochgeladen werden, wenn noch keines vorhanden ist

### Changed
- Diverse Anpassungen an FHTW-Prozesse hardcodiert
- 

### Updateinfo
- 
 

## [0.0.1] - 2015-02-24
### Added
- 

### Fixed
- 

### Changed
- 

### Updateinfo
- 


