<?php
$this->phrasen['bewerbung/ungueltigerZugriff']='invalid request';
$this->phrasen['bewerbung/welcome']='Welcome to the Online Application Tool';
$this->phrasen['bewerbung/welcomeHeaderLogin']='<div class="col-xs-3">
									<div style="text-align: center;">
										<img style="margin: 30px 10px; width: 100%%" src="' . APP_ROOT . 'skin/styles/' . DEFAULT_STYLE . '/logo_academy.png"/>
									</div>
								</div>
								<div style="text-align: center;" class="col-xs-6">
									<h1 style="margin: 30px 10px;">Welcome to the Online Application Tool</h1>
								</div>
								<div class="col-xs-3">
									<div style="text-align: center;">
										<img style="margin: 30px 10px; width: 100%%" src="' . APP_ROOT . 'skin/styles/' . DEFAULT_STYLE . '/logo.png"/>
									</div>
								</div>';
$this->phrasen['bewerbung/welcomeHeaderRegistration']='
					<div class="row">
						<div class="col-xs-2">
							<img style="width:150px;" class="center-block img-responsive" src="' . APP_ROOT . 'skin/styles/' . DEFAULT_STYLE . '/logo_academy.png">
						</div>
						<div class="col-xs-8">
							<h2 class="text-center">Welcome to the Online Application Tool</h2>
						</div>
						<div class="col-xs-2">
							<img style="width:150px;" class="center-block img-responsive" src="' . APP_ROOT . 'skin/styles/' . DEFAULT_STYLE . '/logo.png">
						</div>
					</div>
					';
$this->phrasen['bewerbung/registration']='Registration';
$this->phrasen['bewerbung/registrieren']='Register';
$this->phrasen['bewerbung/abschicken']='Submit';
$this->phrasen['bewerbung/registrierenOderZugangscode']='<a href="'.$_SERVER['PHP_SELF'].'?method=registration">Register here</a> or enter access code';
$this->phrasen['bewerbung/einleitungstext']='Please complete the form, select your preferred degree or certificate program(s) and click "Submit".<br>
We will then send you an access code via e-mail to the address specified. You may use this access code at any time to log in, add personal information or degree programs and submit non-binding applications.<br><br>
If you are interested in more than one degree programs, you may select up to 3 study programs.<br><br>
Should you require any additional information, please do not hesitate to contact our <a href=\'https://www.technikum-wien.at/en/student-guide/admission-counselors/\' target=\'_blank\'>student counselling team</a> in person, by phone, or via e-mail or WhatsApp.<br><br>
		<a href="#datenschutzText" data-toggle="collapse">Privacy information: <span class="glyphicon glyphicon-collapse-down"></span></a>
		<div id="datenschutzText" class="collapse">
		The data communicated to us by you for the purpose of the application will be used by us exclusively for the processing of the application on the basis of pre-contractual or contractual purposes and will not be passed on to third parties with the exception described below in case of uncertainties regarding the entry requirements. If there is no further contact or enrolment, your data will be deleted after three years.<br><br>
		Information on your data subject rights can be found here: <a href=\'https://www.technikum-wien.at/information-ueber-ihre-rechte-gemaess-datenschutz-grundverordnung/\' target=\'_blank\'>https://www.technikum-wien.at/information-ueber-ihre-rechte-gemaess-datenschutz-grundverordnung/</a><br><br>
		If you have any questions, please contact us at <a href=\'mailto:datenschutz@technikum-wien.at\'>datenschutz@technikum-wien.at</a><br><br>
		Data Processing Office:<br>
		University of Applied Sciences Technikum Wien<br>
		Höchstädtplatz 6<br>
		1200 Wien
		</div> ';
$this->phrasen['bewerbung/login']='Login';
$this->phrasen['bewerbung/zugangscode']='Access Code';
$this->phrasen['bewerbung/fallsVorhanden']='(if available)';
$this->phrasen['bewerbung/mailtextHtml']='Please look at the message in the HTML view, in order to display the link fully.';
$this->phrasen['bewerbung/anredeMaennlich']='Mr';
$this->phrasen['bewerbung/anredeWeiblich']='Ms';
$this->phrasen['bewerbung/mailtext']='Dear %4$s %1$s %2$s.<br><br>
                                        Thank you for your interest in a degree program at '.CAMPUS_NAME.'. <br>
                                        To apply for a course, please use the following link and access code:<br><br>
                                        <a href="'.APP_ROOT.'addons/bewerbung/cis/registration.php?code=%3$s&emailAdresse=%5$s">Link to registration</a><br>
                                        Access code: %3$s <br><br>
                                        Best regards, <br>
                                        '.CAMPUS_NAME;
$this->phrasen['bewerbung/zugangsdatenFalsch']='The access code you have entered is incorrect or you have not yet registered.';
$this->phrasen['bewerbung/mailFalsch']='The e-mail address you have entered is incorrect or you have not yet registered.';
$this->phrasen['bewerbung/fehlerBeimSenden']='An error occured while sending the E-Mail.';
$this->phrasen['bewerbung/emailgesendetan']='The e-mail with your access code has successfully been sent to %s.';
$this->phrasen['bewerbung/zurueckZurAnmeldung']='Back to registration.';
$this->phrasen['bewerbung/sicherheitscodeFalsch']='The access code is incorrect.';
$this->phrasen['bewerbung/geplanterStudienbeginn']='Planned start of study period';
$this->phrasen['bewerbung/studienrichtung']='Desired field of study(-ies)';
$this->phrasen['bewerbung/bitteStudienrichtungWaehlen']='Please select at least one field of study.';
$this->phrasen['bewerbung/bitteVornameAngeben']='Please enter your first name.';
$this->phrasen['bewerbung/bitteNachnameAngeben']='Please enter your last name.';
$this->phrasen['bewerbung/bitteGeburtsdatumEintragen']='Please enter your date of birth.';
$this->phrasen['bewerbung/bitteGeschlechtWaehlen']='Please enter your gender.';
$this->phrasen['bewerbung/bitteEmailAngeben']='Please enter a valid E-Mail Address.';
$this->phrasen['bewerbung/bitteStudienbeginnWaehlen']='Please select the desired start of study period.';
$this->phrasen['bewerbung/captcha']='Please enter the characters shown in the picture (spam protection).';
$this->phrasen['bewerbung/andereGrafik']='Reload picture';
$this->phrasen['bewerbung/datumFormat']='dd.mm.yyyy';
$this->phrasen['bewerbung/datumUngueltig']='Date is invalid or out of range. Please enter a valid date in the format dd.mm.yyyy.';
$this->phrasen['bewerbung/datumsformatUngueltig']='Date is invalid. Please enter a valid date in the format dd.mm.yyyy.';
$this->phrasen['bewerbung/egal']='no preference';
$this->phrasen['bewerbung/orgform']='Organizational structure';
$this->phrasen['bewerbung/orgform/BB']='Part time';
$this->phrasen['bewerbung/orgform/VZ']='Full time';
$this->phrasen['bewerbung/orgform/teilzeit']='Part time';
$this->phrasen['bewerbung/orgform/DL']='Distance Study';
$this->phrasen['bewerbung/orgform/DDP']='Double Degree Program';
$this->phrasen['bewerbung/orgform/PT']='Part time';
$this->phrasen['bewerbung/orgform/ZGS']='Zielgruppenspezifisch';
$this->phrasen['bewerbung/orgform/DUA']='Dual';
$this->phrasen['bewerbung/German']='German';
$this->phrasen['bewerbung/English']='English';
$this->phrasen['bewerbung/Italian']='Italian';
$this->phrasen['bewerbung/topprio']='Top priority';
$this->phrasen['bewerbung/alternative']='Alternative';
$this->phrasen['bewerbung/priowaehlen']='Select primary and alternative variant';
$this->phrasen['bewerbung/prioBeschreibungstext'] = 'Please choose the organizational structure and language. In the event that there are no more places you can also select an alternative.';
$this->phrasen['bewerbung/prioUeberschrifttopprio'] = 'Top priority';
$this->phrasen['bewerbung/prioUeberschriftalternative'] = 'Alternative (optional)';
$this->phrasen['bewerbung/neuerStudiengang'] = 'Please choose a degree program to which you wish to apply';
$this->phrasen['bewerbung/geplanteStudienrichtung']='Planned field of study';
$this->phrasen['bewerbung/menuAllgemein']='General';
$this->phrasen['bewerbung/loginmitAccount']='Login with account';
$this->phrasen['bewerbung/dokumentOhneUploadGeprueft'] = 'Approved without upload.';
$this->phrasen['bewerbung/allgemeineErklaerung']='We are pleased that you are interested in the study programs we offer.<br><br>
You may select - online - up to 3 study programs and as many further education courses as you like.<br>
Click the green button to add a study program or course to your application.<br>
Should you require any additional information, please do not hesitate to contact our <a href=\'https://www.technikum-wien.at/en/student-guide/admission-counselors/\' target=\'_blank\'>student counselling team</a> in person, by phone, or via e-mail or WhatsApp.<br><br>
Complete the form in full. Once you have entered all details, send in your application by clicking "Send application".<br><br>
Application deadlines for third countries (outside the EU) see:<br>
<a href=\'https://www.technikum-wien.at/bewerbungsfristen\' target=\'_blank\'>https://www.technikum-wien.at/bewerbungsfristen</a><br><br>
In accordance with the new provisions of the GDPR we want to <a href=\'https://cis.technikum-wien.at/cms/dms.php?id=77605\' target=\'_blank\'>inform you where personal data</a> are collected from you';
$this->phrasen['bewerbung/erklaerungStudierende']='We are pleased that you are interested in the study programs we offer.<br><br>Click the green button to add a study program or course to your application.';
$this->phrasen['bewerbung/aktuelleBewerbungen']='Current applications:';
$this->phrasen['bewerbung/status']='Status';
$this->phrasen['bewerbung/legende']='Legend';
$this->phrasen['bewerbung/bewerbungsstatus']='Application Status';
$this->phrasen['bewerbung/keinStatus']='No current application. Click "<i>Add application for degree program</i>" in section "<i>General</i>" to add a new application';
$this->phrasen['bewerbung/bestaetigt']='confirmed, in progress';
$this->phrasen['bewerbung/nichtBestaetigt']='application sent, not yet confirmed';
$this->phrasen['bewerbung/nichtAbgeschickt']='application not sent';
$this->phrasen['bewerbung/studiengangHinzufuegen']='Add application for degree program';
$this->phrasen['bewerbung/weiter']='continue';
$this->phrasen['bewerbung/geburtsnation']='Country of Birth';
$this->phrasen['bewerbung/svnr']='Austrian Social Security Number';
$this->phrasen['bewerbung/maennlich']='male';
$this->phrasen['bewerbung/weiblich']='female';
$this->phrasen['bewerbung/berufstaetigkeit']='Job';
$this->phrasen['bewerbung/berufstaetig']='employed';
$this->phrasen['bewerbung/dienstgeber']='Employer';
$this->phrasen['bewerbung/artDerTaetigkeit']='Type of Occupation';
$this->phrasen['bewerbung/weiter']='Next';
$this->phrasen['bewerbung/eintragVom']='Date of record:';
$this->phrasen['bewerbung/menuPersDaten']='Personal Data';
$this->phrasen['bewerbung/accountVorhanden']='Since you have already been confirmed as an interested party or you already have an account at the UASTW, you can no longer change your master data. If there are some incorrect details here, please contact the administrative assistant responsible.';
$this->phrasen['bewerbung/bitteAuswaehlen']='-- please select --';
$this->phrasen['bewerbung/menuKontaktinformationen']='Contact Details';
$this->phrasen['bewerbung/kontakt']='Contact';
$this->phrasen['bewerbung/nation']='Nation';
$this->phrasen['bewerbung/menuDokumente']='Documents';
$this->phrasen['bewerbung/dokument']='Document';
$this->phrasen['bewerbung/bitteDokumenteHochladen']='In order to be able to send in your application, you need to upload all documents marked as "required". 
													If the document is not yet available at the respective time, you may submit the same at a later date. 
													To do so, click the "submit document later" icon (hourglass) and specify when you are going to submit the document as well as the institution issuing the same.<br><br>
													You may be prompted to upload more documents here in the course of your application.';
$this->phrasen['bewerbung/linkDokumenteHochladen']='Upload Documents';
$this->phrasen['bewerbung/dokumenteZumHochladen']='Required documents:';
$this->phrasen['bewerbung/dokumentName']='Name';
$this->phrasen['bewerbung/benoetigtFuer']='Required for';
$this->phrasen['bewerbung/dokumentErforderlich']='Required document';
$this->phrasen['bewerbung/dokumentOffen']='Document not yet submitted (open)';
$this->phrasen['bewerbung/dokumentNichtUeberprueft']='Document has been submitted but not yet examined';
$this->phrasen['bewerbung/dokumentWirdNachgereicht']='Document will be submitted later';
$this->phrasen['bewerbung/dokumentWurdeUeberprueft']='Document has been examined';
$this->phrasen['bewerbung/dokumentHerunterladen']='Download document';
$this->phrasen['bewerbung/abgegeben']='Submitted';
$this->phrasen['bewerbung/upload']='Upload';
$this->phrasen['bewerbung/menuZgv']='Admission requirements';
$this->phrasen['bewerbung/menuZugangsvoraussetzungen']='Admission requirements';
$this->phrasen['bewerbung/fuer']='for';
$this->phrasen['bewerbung/artDerVoraussetzung']='Type of condition';
$this->phrasen['bewerbung/menuZahlungen']='Payments';
$this->phrasen['bewerbung/zahlungsinformation']='Payment information';
$this->phrasen['bewerbung/bezahlt']='payed';
$this->phrasen['bewerbung/zahlungsdetails']='Payment details';
$this->phrasen['bewerbung/menuReihungstest']='Placement test';
$this->phrasen['bewerbung/fuerReihungstestAnmelden']='Here are the next available placement tests. Please register for <b>one</b> and note that you need to take the test only once even if you have selected more than one bachelor degree programme.';
$this->phrasen['bewerbung/fehler']='An error occurred';
$this->phrasen['bewerbung/angemeldetPlaetze']='registered / places';
$this->phrasen['bewerbung/uhrzeit']='Time';
$this->phrasen['bewerbung/stornieren']='cancel';
$this->phrasen['bewerbung/menuBewerbungAbschicken']='Send Application';
$this->phrasen['bewerbung/erklaerungBewerbungAbschicken']='If you have filled in all the information correctly you can submit your application.<br>
Generally, we will get back to you within 5 working days.';
$this->phrasen['bewerbung/bewerbungAbschickenFuer']='Send application for';
$this->phrasen['bewerbung/buttonBewerbungAbschicken']='Send application';
$this->phrasen['bewerbung/logout']='Logout';
$this->phrasen['bewerbung/einzahlungFuer']='Payment from';
$this->phrasen['bewerbung/zahlungsinformationen']='Payment information';
$this->phrasen['bewerbung/buchungstyp']='Booking type';
$this->phrasen['bewerbung/buchungstext']='Booking text';
$this->phrasen['bewerbung/betrag']='Amount';
$this->phrasen['bewerbung/zahlungAn']='Payment to';
$this->phrasen['bewerbung/empfaenger']='Recipient';
$this->phrasen['bewerbung/iban']='IBAN';
$this->phrasen['bewerbung/bic']='BIC';
$this->phrasen['bewerbung/zahlungsreferenz']='Payment Reference';
$this->phrasen['bewerbung/buchungsnummerNichtVorhanden']='Booking number %s not present';
$this->phrasen['bewerbung/teilweiseVollstaendig']='partially complete';
$this->phrasen['bewerbung/vollstaendig']='complete';
$this->phrasen['bewerbung/unvollstaendig']='incomplete';
$this->phrasen['bewerbung/teilweiseVollstaendig']='partially complete';
$this->phrasen['bewerbung/maxAnzahlTeilnehmer']='Maximum number of participants reached';
$this->phrasen['bewerbung/erfolgreichBeworben']='You have applied successfully. Generally, we will get back to you within 5 working days.';
$this->phrasen['bewerbung/fehlerBeimVersendenDerBewerbung']='An error occured while sending the application. Please try again.';
$this->phrasen['bewerbung/svnrBereitsVorhanden']='Social Security Number already exists.';
$this->phrasen['bewerbung/menuBewerbungFuerStudiengang']='Application for a degree program';
$this->phrasen['bewerbung/emailBodyStart']='<html>
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
						Für mehr Details öffnen Sie bitte den <a href="%1$s" target="_blank">Personendatensatz</a> im FAS.';//Mail an Assistenz. Nur übersetzen, wenn das Mail in der Sprache der Bewerbung versendet werden soll.
$this->phrasen['bewerbung/emailDokumentuploadStart']='Das folgende Dokument wurde hochgeladen: <br>';//Mail an Assistenz. Nur übersetzen, wenn das Mail in der Sprache der Bewerbung versendet werden soll.
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
</html>';//Mail an Assistenz. Nur übersetzen, wenn das Mail in der Sprache der Bewerbung versendet werden soll.
$this->phrasen['bewerbung/fileUpload']='File-Upload';
$this->phrasen['bewerbung/fehlerKeinePersonId']='There was no Person_id supplied';
$this->phrasen['bewerbung/woWurdeUrkundeAusgestellt']='Where was the certificate issued?';
$this->phrasen['bewerbung/ausbildungstyp']='Type of Education';
$this->phrasen['bewerbung/lehrgang']='Course';
$this->phrasen['bewerbung/keineStgAngeboten']='There are no degree programs available at the moment';
$this->phrasen['bewerbung/keineLehrgAngeboten']='There are no courses available at the moment';
$this->phrasen['bewerbung/aufmerksamdurch']='How did you hear about us?';
$this->phrasen['bewerbung/BewerbungBereitsVerschickt']='Application is being processed';
$this->phrasen['bewerbung/bitteZuerstStudiengangWaehlen']='Please select at least one field of study first (see section "Overview").';
$this->phrasen['bewerbung/ExtensionInformation']='Maximum file size per document: 15 MB.<br>Supported file formats:';
$this->phrasen['bewerbung/falscherDateityp']='This File format is not supported';
$this->phrasen['bewerbung/mailadresseBereitsGenutzt']='The e-mail address %s has already been used for an application. Do you want to send a new access code to this address?';
$this->phrasen['bewerbung/mailadresseBereitsVorhanden']='The e-mail address %s already exists in our system an cannot be saved. Please enter a different e-mail address.';
$this->phrasen['bewerbung/buttonBewerbungUnvollstaendig']='Required fields incomplete';
$this->phrasen['bewerbung/prestudentID']='Prestudent ID';
$this->phrasen['bewerbung/bewerbung']='Application';
$this->phrasen['bewerbung/dokumentuploadZuBewerbung']='Documentupload for application';
$this->phrasen['bewerbung/maennlich']='Male';
$this->phrasen['bewerbung/weiblich']='Female';
$this->phrasen['bewerbung/maturazeugnis']='School leaving certificate';
$this->phrasen['bewerbung/details']='Details';
$this->phrasen['bewerbung/mehrDetails']='More details...';
$this->phrasen['bewerbung/codeZuschicken']='Send new code';
$this->phrasen['bewerbung/codeZuschickenAnleitung']='Please enter your e-mail address and click "Send code"';
$this->phrasen['bewerbung/keinCodeVorhanden']='There is no access code available yet for this e-mail address. Please register in advance.';
$this->phrasen['bewerbung/zgvDatumNichtZukunft']='The date of access requirement may not be in the future. If you intend to provide the access requirement later, please leave the date blank.';
$this->phrasen['bewerbung/ab']='from';
$this->phrasen['bewerbung/adresse']='Address (principal residence)';
$this->phrasen['bewerbung/notizVom']='Note from';
$this->phrasen['bewerbung/anmerkung']='Comment';
$this->phrasen['bewerbung/anmerkungPlaceholder']='Here you have the opportunity to enter an additional comment (1024 characters) that you want to tell the assistant';
$this->phrasen['bewerbung/orgformMussGewaehltWerden']='An organization form must be selected';
$this->phrasen['bewerbung/hierUnverbindlichAnmelden']='Register here without obligation';
$this->phrasen['bewerbung/keineOrgformVorhanden']='No organization form has been stored for the semester of study selected';
$this->phrasen['bewerbung/bitteOrgformWaehlen']='If you select a degree program with more than one organization form you must enter a priority';
$this->phrasen['bewerbung/orgformWaehlen']='Select organization form';
$this->phrasen['bewerbung/orgformBeschreibungstext']='Please enter the organization form(s) that interests you. If all the places in your chosen organization form have already been allocated, you are free to enter an alternative.';
$this->phrasen['bewerbung/menuAbschließen']='Finish';
$this->phrasen['bewerbung/sieHabenNochKeinenZugangscode']='You don\'t have an access code or account at the UAS Technikum Wien?';
$this->phrasen['bewerbung/habenSieBereitsEinenZugangscode']='You already have an access code?';
$this->phrasen['bewerbung/studierenOderArbeitenSieBereits']='You already study or work at the UAS Technikum Wien?';
$this->phrasen['bewerbung/zugangscodeVergessen']='Forgot access code?';
$this->phrasen['bewerbung/dannHiermitAccountEinloggen']='Then login here with your CIS account';
$this->phrasen['bewerbung/dannHierEinloggen']='Then login here';
$this->phrasen['bewerbung/dokumentHerunterladen']='Download document';
$this->phrasen['bewerbung/hinweisZGVdatenaenderung']='<b>Notice:</b> Saved data cannot be changed afterwards due to organisational reasons. If there is incorrect data please use the comment-area under "Finish" or contact your administrative assistant responsible.';
$this->phrasen['bewerbung/statusBestaetigen']='Confirm status directly';
$this->phrasen['bewerbung/footerText']='';
$this->phrasen['bewerbung/vorbehaltlichAkkreditierung']='Subject to official accreditation by AQ Austria';
$this->phrasen['bewerbung/auswahlmöglichkeitenImNaechstenSchritt']='Options in next step';
$this->phrasen['bewerbung/sieKoennenMaximalXStudiengaengeWaehlen']='You can apply online for a maximum of %s degree programs in the same study semester. If you need more information, our <a href=\'https://www.technikum-wien.at/en/student-guide/admission-counselors/\' target=\'_blank\'>student advisory service</a> is available to you.<br><br><a href=\'#\' class=\'alert-link\' data-dismiss=\'alert\' aria-label=\'close\'>Close</a>'; // Link muss mit einfachen Hochkomma maskiert werden, das es sonst im Bewerbungstool zu Anzeigefehlern kommt
$this->phrasen['bewerbung/bitteEineStudienrichtungWaehlen']='Please select one field of study.';
$this->phrasen['bewerbung/beschreibungTitelPre']='Academic title e.g. Dr., Prof.';
$this->phrasen['bewerbung/beschreibungTitelPost']='Academic post-nominal letters (titles) e.g. BA, BSc, PhD';
$this->phrasen['bewerbung/BilduploadInfotext']='Currently it is possible to upload JPG, PNG or GIF images with a maximum size of 15MB.<br><br><b>Please follow the <a href=\''.APP_ROOT.'cms/content.php?content_id=%s\' target=\'_blank\'>guidelines for uploading images</a></b>';
$this->phrasen['bewerbung/fotoAuswaehlen']='Click on the image below to upload and crop a photo<br>If the upload fails or your browser does not support image cropping <br>you can upload a photo <a href="dms_akteupload.php?person_id=%s&dokumenttyp=Lichtbil"><b>here</b></a>';
$this->phrasen['bewerbung/akademischeTitel']='Academic Title(s)';
$this->phrasen['bewerbung/pflichtfelder']='Required';
$this->phrasen['bewerbung/bitteGueltigeOesterreichischePlzEingeben']='Please enter a valid Austrian postcode';
$this->phrasen['bewerbung/plzMussGueltigSein']='Postcode must be a valid number';
$this->phrasen['bewerbung/plzUnbekannt']='Postcode unknown';
$this->phrasen['bewerbung/dateien']='File(s)';
$this->phrasen['bewerbung/dokumentWirdGeprueft']='Document in examination';
$this->phrasen['bewerbung/dokumentUeberprueft']='Document examined';
$this->phrasen['bewerbung/keineDateiAusgewaehlt']='No file selected or found';
$this->phrasen['bewerbung/placeholderAnmerkungNachgereicht']='Please indicate which institution will issue the document and when you can probably submit it';
$this->phrasen['bewerbung/bitteAusstellungsnationAuswaehlen']='-- Please select the country in which the document was issued --';
$this->phrasen['bewerbung/sitzungAbgelaufen']='Seesion expired. Please log in again.';
$this->phrasen['bewerbung/placeholderOrtNachgereicht']='Issuing institution (eg: TGM Wien)';
$this->phrasen['bewerbung/wirdNachgreichtAm']='Will be submitted by';
$this->phrasen['bewerbung/ausstellendeInstitution']='Issuing institution';
$this->phrasen['bewerbung/dokumenteVollstaendig']='All documents required have been uploaded';
$this->phrasen['bewerbung/keinDokumententypUebergeben']='Document type is not set';
$this->phrasen['bewerbung/aktion']='Action';
$this->phrasen['bewerbung/bewerbungStornieren']='Cancel application';
$this->phrasen['bewerbung/bewerbungStorniert']='Application cancelled';
$this->phrasen['bewerbung/bewerbungStornierenInfotext']='If you cancel the application you will not be able to assign again for <span style="text-align: center; font-weight: bold; display: block; padding-top: 10px">%2$s</span> in %1$s again.<br><br>Do you want to proceed?';
$this->phrasen['bewerbung/bewerbungStornierenBestaetigen']='Yes, cancel application';
$this->phrasen['bewerbung/vergangeneBewerbungen']='Past applications';
$this->phrasen['bewerbung/buttonStornierenDisabled']='Once the application has been sent or rejected you can not cancel it any more';
$this->phrasen['bewerbung/infotextDisabled']='Since an application already exists or existed, you can no longer apply in %s for this degree program';
$this->phrasen['bewerbung/bitteAnmerkungEintragen']='Please enter the name of the institution that will issue the document';
$this->phrasen['bewerbung/nachreichDatumNichtVergangenheit']='The date of submission may not be in the past ';
$this->phrasen['bewerbung/infotextVorlaeufigesZgvDokument']='Please upload the last semester certificate of your education';
$this->phrasen['bewerbung/bitteDateiAuswaehlen']='Please choose a file';
$this->phrasen['bewerbung/zustimmungDatenuebermittlung']='If in exceptional cases the admission requirements can not be finally clarified by the UAS Technikum Wien, I give my consent that the UAS Technikum Wien can forward the documents to the competent authorities for verification.<br>
I have been informed that I am under no obligation to consent to the transmission of my data. However, this consent is necessary in order for the application to be considered.';
$this->phrasen['bewerbung/bitteDatenuebermittlungZustimmen']='You have to consent the transmission of your data to send the application.';
$this->phrasen['bewerbung/vorlaeufigesDokument']='Temporary document';
$this->phrasen['bewerbung/lehrgangsArt/1']='Post graduate program';
$this->phrasen['bewerbung/lehrgangsArt/2']='Academic course';
$this->phrasen['bewerbung/lehrgangsArt/3']='International programs';
$this->phrasen['bewerbung/lehrgangsArt/4']='Certificate program';
$this->phrasen['bewerbung/lehrgangsArt/5']='Postgradualer Lehrgang';
$this->phrasen['bewerbung/hackTypBezeichnungLehrgeange']='Certificate Program for Further Education'; // Überschreibt die Typ-Bezeichnung "Lehrgang". Leer lassen, wenn nicht benötigt
$this->phrasen['bewerbung/hackTypBezeichnungBachelor']='Bachelor\'s Degree Programs'; // Überschreibt die Typ-Bezeichnung "Bachelor". Leer lassen, wenn nicht benötigt
$this->phrasen['bewerbung/hackTypBezeichnungMaster']='Master\'s Degree Programs'; // Überschreibt die Typ-Bezeichnung "Master". Leer lassen, wenn nicht benötigt
$this->phrasen['bewerbung/hackStufeBezeichnungBachelor']=''; // Überschreibt die ZGV Stufen-Bezeichnung "Bachelor". Leer lassen, wenn nicht benötigt
$this->phrasen['bewerbung/hackStufeBezeichnungMaster']=''; // Überschreibt die ZGV Stufen-Bezeichnung "Master". Leer lassen, wenn nicht benötigt
$this->phrasen['bewerbung/prioritaet']='Priority';
$this->phrasen['bewerbung/studierendenDaten']='Student’s current data';
$this->phrasen['bewerbung/keineRtTermineZurAuswahl']='At the moment there are no placement test dates available';
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
									Dear %3$s %1$s %2$s,<br><br>
									Your have successfully submitted your application for %4$s. Generally, we will get back to you within 5 working days. If necessary, you will then be requested to upload additional documents.<br><br>
									You can see the status of your application in the <a href="'.APP_ROOT.'addons/bewerbung/cis/registration.php">application tool</a>.<br> 
									If you have any questions regarding your application, do not hesitate to contact our Info Center at <a href="mailto:studienberatung@technikum-wien.at">studienberatung@technikum-wien.at</a>.<br><br>
									Best regards,<br>
									UAS Technikum Wien
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
									Dear %3$s %1$s %2$s,<br><br>
									Your have successfully submitted your application for %4$s. Generally, we will get back to you within 5 working days. If necessary, you will then be requested to upload additional documents.<br><br>
									You can see the status of your application in the <a href="'.APP_ROOT.'addons/bewerbung/cis/registration.php">application tool</a>.<br>
									If you have any questions regarding your application, do not hesitate to contact us at <a href="mailto:%5$s">%5$s</a>.<br><br>
									Best regards,<br>
									UAS Technikum Wien
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
$this->phrasen['bewerbung/erfolgreichBeworbenMailBetreff']='Application confirmation UAS Technikum Wien';
$this->phrasen['bewerbung/bewerbungsfrist']='Application deadline';
$this->phrasen['bewerbung/bewerbungszeitraum']='Application period (within the EU)';
$this->phrasen['bewerbung/bewerbungsfristAbgelaufen']='Application deadline expired';
$this->phrasen['bewerbung/bewerbungAusserhalbZeitraum']='Your application for this degree program is out of period. Your application has not been sent.';
$this->phrasen['bewerbung/unbegrenzt']='open';
$this->phrasen['bewerbung/bewerbungszeitraumStartetAm']='Application period starts %s';
$this->phrasen['bewerbung/bewerbungsfristEndetInXTagen']='Application period ends in %s days';
$this->phrasen['bewerbung/bewerbungsfristEndetHeute']='Application period ends today';
$this->phrasen['bewerbung/bewerbungsfristFuerStudiensemesterXAbgelaufen']='Application deadline for %s has expired';
$this->phrasen['bewerbung/bewerbungDerzeitNichtMoeglich']='Application currently not possible. Please contact <a href="mailto:%1$s">%1$s</a>';
$this->phrasen['bewerbung/bewerbungenFuerAb']='Application for %s starting %s';
$this->phrasen['bewerbung/bewerbungenFuerStudiensemesterXX']='Applications for study semester %s';
$this->phrasen['bewerbung/bewerbungAbschicken']='Send application';
$this->phrasen['bewerbung/erklaerungBewerbungAbschickenFuerStudiengang']='If you have filled in all the information correctly you can submit your application for <span style="text-align: center; font-weight: bold; display: block; padding-top: 10px">%s</span>
Generally, we will get back to you within 5 working days.';
$this->phrasen['bewerbung/anmeldungStornieren']='Cancel registration';
$this->phrasen['bewerbung/anmeldungStornierenBis']='Cancel (until %s)';
$this->phrasen['bewerbung/menuUebersicht']='Overview';
$this->phrasen['bewerbung/menuSicherheit']='Security';
$this->phrasen['bewerbung/erklaerungSicherheit']='For reasons of privacy, we recommend that you generate a new access code.<br>
													Simply click on the button and make a note of the newly generated code.<br><br>
													If you forget the code, click on the login page on "Forgot access code?"';
$this->phrasen['bewerbung/buttonNeuerZugangscode']='Generate new access code';
$this->phrasen['bewerbung/erfolgsMessageNeuerZugangscode']='<p>Your new access code is:</p><p>%s</p><p>Please make a note of the new access code</p>';
$this->phrasen['bewerbung/keineStudienrichtungenFuerStudiensemesterZurAuswahl']='There are currently no courses of study available for the selected semester';
$this->phrasen['bewerbung/studienberechtigungErlangtIn']='Studienberechtigung erlangt in'; // Noch zu übersetzen
$this->phrasen['bewerbung/studienberechtigungErlangtInErklaerung']='Hier ist jene Nation zu wählen, in der Sie Ihren Abschluss erlangt haben/erlangen werden, der Sie zu einem Studium berechtigt (optional für Lehrgänge)';  // Noch zu übersetzen
$this->phrasen['bewerbung/StatusSeitDatum']='%1$s since %2$s';
$this->phrasen['bewerbung/bewerbungszeitraumFuer']='Application period for %1$s';
$this->phrasen['bewerbung/bitteAuswaehlenBaMa']='-- Please select for bachelor and master --';
$this->phrasen['bewerbung/bitteZGVausweahlen']='Wenn Sie sich für einen Bachelor- oder Master-Studiengang bewerben, wählen Sie bitte das Land aus, in dem Sie die Studienberechtigung erlangt haben'; // Noch zu übersetzen

// Reihungstest
$this->phrasen['bewerbung/anmeldungReihungstestMailBetreff']='Confirmation of registration to placement test UAS Technikum Wien';
$this->phrasen['bewerbung/anmeldungReihungstestMail']='Sehr %3$s %1$s %2$s,<br><br>
Sie haben sich erfolgreich für den Reihungstest am %4$s beworben. Der Test beginnt um %5$s und dauert etwa 3,5 Stunden.<br><br>
Bitte bringen Sie einen amtlichen Lichtbildausweis zur Überprüfung Ihrer Identität mit.<br>
Den Raum können Sie etwa eine Woche vor Testbeginn im Bewerbungstool sehen<br>
Sollten Sie Fragen haben, kontaktieren Sie bitte unser Infocenter <a href="mailto:studienberatung@technikum-wien.at">studienberatung@technikum-wien.at</a>.<br><br>
Mit freundlichen Grüßen<br>
Fachhochschule Technikum Wien'; // Noch zu übersetzen
$this->phrasen['bewerbung/reihungstestInfoTextAngemeldet']='<div class="alert alert-info">
<p>Please make sure that you arrive 15 minutes prior to the placement test at the UAS, 1200 Wien, Höchstädtplatz 6.</p>
<p>Please bring an official photo ID (passport, identity card, etc.) on the day of your placement test.</p>
</div><br>';
$this->phrasen['bewerbung/anmeldefrist']='Term of application';
$this->phrasen['bewerbung/infoVorgemerktFuerQualifikationskurs']='Sie sind als TeilnehmerIn für die Qualifikationskurse vorgemerkt. Sobald sie dort bestätigt wurden, können Sie hier einen Termin für den Reihungstest wählen.';// Noch zu übersetzen
$this->phrasen['bewerbung/raumzuteilungFolgt']='One week prior to the test you will see here the room-number';
$this->phrasen['bewerbung/sieHabenFolgendenTerminGewaehlt']='You have registered for the following placement test date';

// Ausbildung
$this->phrasen['bewerbung/menuAusbildung']='Ausbildung';
$this->phrasen['bewerbung/ausbildung']='Ausbildung zu Ihrer Zugangsvoraussetzung';
$this->phrasen['bewerbung/ausbildungSchule']='Name der Schule';
$this->phrasen['bewerbung/ausbildungSchuleAdresse']='Adresse der Schule';
$this->phrasen['bewerbung/paymentInfoText']='';

// Rechnungskontakt
$this->phrasen['bewerbung/menuRechnungsKontaktinformationen']='Billing Data';
$this->phrasen['bewerbung/rechnungsadresseInfoText']='Please enter your Billing Data if different from Home Address';
$this->phrasen['bewerbung/rechnungsKontakt']='Billing Contact';
$this->phrasen['bewerbung/rechnungsAdresse']='Billing Address';
$this->phrasen['bewerbung/re_anrede']='Salutation';
$this->phrasen['bewerbung/re_titel']='Titel';
$this->phrasen['bewerbung/re_vorname']='Surname';
$this->phrasen['bewerbung/re_nachname']='Name';
?>
