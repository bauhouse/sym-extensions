<?php

/*
	This is translation file for Symphony.
	To make it available for Symphony installation, rename it lang.de.php and upload to /symphony/lib/lang/ directory on server.
*/
$about = array (
  'name' => 'Deutsch (formell)',
  'extension' => 'symphony',
  'author' => 
  array (
    'Nils Hörrmann' => 
    array (
      'name' => 'Nils Hörrmann',
      'website' => 'http://nilshoerrmann.de',
      'email' => 'post@nilshoerrmann.de',
      'release-date' => '2009-02-07',
    ),
  ),
  'release-date' => '2009-02-07',
);

/*
	Dictionary array contains translations of texts (labels, guidelines, titles, etc...) used by Symphony.

	There are 3 states of translations:
	- Translated: already used by Symphony,
	- Missing: there was no translation available at the time of generating this template file,
	- Obsolete: translated text which is no longer used by Symphony and can be removed from translation file.

	To add missing translations, simply scroll down to part of array marked as "// MISSING"
	and change each "false" value into something like "'New translation of original text'"
	(notice single quotes around translated text! Text has to be wrapped either by them or by double quotes).
	So instead of something like:

		'Original text' =>
		false,

	You'll have something like:

		'Original text' =>
		'Tekst oryginału',

	You should leave all parts of text which look like "%s" or "%1$s" (usually there is "s" or "d" character there).
	They are placeholders for other text or HTML which will be put in their place when needed by Symphony.
	You can move them around inside translated text, but not remove them. For example:

		'Original %s is here' =>
		'Tu jest oryginalny %s'

	Placeholders with numbers inside them are used when there are more than one of them inside original text.
	You can switch their positions if needed, but do not change numbers into something else.
	For example text used in page titles looks like "Symphony - Language - English" and is generated with:

		'%1$s &ndash; %2$s &ndash; %3$s'

	To make titles look like "Language: English | Symphony" simply move placeholders around:

		'%2$s: %3$s | %1$s'
*/
$dictionary = array (
// TRANSLATED
  ' (<b>Notice that it is possible to get mixtures of success and failure messages when using the "Allow Multiple" option</b>)' =>
  ' (<b>Beachten Sie, dass Sie möglicherweise sowohl Erfolgs- als auch Fehlermeldungen erhalten, wenn Sie die Option "Mehrere zulassen" verwenden</b>)',

  '\'%s\' is a required field.' =>
  '\'%s\' ist ein Pflichtfeld.',

  '"%1$s" contains invalid XML. The following error was returned: <code>%2$s</code>' =>
  '"%1$s" enthält ungültiges XML. Nachfolgender Fehler wurde zurückgegeben: <code>%2$s</code>',

  '%1$s &ndash; %2$s' =>
  '%1$s &ndash; %2$s',

  '%1$s &ndash; %2$s &ndash; %3$s' =>
  '%1$s &ndash; %2$s &ndash; %3$s',

  '%1$s Allow remote login via <a href="%2$s">%2$s</a>' =>
  '%1$s Remotezugriff über <a href="%2$s">%2$s</a> zulassen.',

  '%s Redirect to 404 page when no results are found' =>
  '%s Auf 404-Fehlerseite umleiten, wenn keine Ergebnisse gefunden werden können.',

  '%s HTML-encode text' =>
  '%s Ausgabe HTML-konform kodieren.',

  '%s Hide this section from the Publish menu' =>
  '%s Diesen Bereich im Menü ausblenden.',

  '%s Pre-populate this field with today\'s date' =>
  '%s Dieses Feld mit dem heutigen Datum vorausfüllen.',

  '%s Make this a required field' =>
  '%s Dieses Feld verpflichtend machen.',

  '%s Allow selection of multiple authors' =>
  '%s Erlaube Auswahl mehrerer Autoren.',

  '%s Allow selection of multiple options' =>
  '%s Erlaube Mehrfachauswahl.',

  'Filter %s by' =>
  '%s filtern mit',

  '%s Show column' =>
  '%s In der Übersicht anzeigen',

  '%s is not a valid object. Failed to append to XML.' =>
  '%s ist kein gültiges Objekt. Es konnte dem XML nicht hinzugefügt werden.',

  '%s Checked by default' =>
  '%s Standardmäßig ausgewählt.',

  '&larr; Previous' =>
  '&larr; Vorherige',

  '</code> file after Symphony has installed successfully.' =>
  '</code> löschen nachdem Symphony erfolgreich installiert wurde.',

  '<a href="%1$s" title="Show debug view for %2$s">Line %3$d</a>' =>
  '<a href="%1$s" title="Zeige Prüfansicht für %2$s">Zeile %3$d</a>',

  '<a href="%s" title="Show debug view">Compile</a>' =>
  '<a href="%s" title="Zeige Prüfansicht">Kompilieren</a>',

  '<abbr title="eXtensible Stylesheet Language Transformation">XSLT</abbr> Processor' =>
  '<abbr title="eXtensible Stylesheet Language Transformation">XSLT</abbr>-Prozessor',

  '<abbr title="PHP: Hypertext Pre-processor">PHP</abbr> 5.1 or above' =>
  '<abbr title="PHP: Hypertext Pre-processor">PHP</abbr> 5.1 oder höher',

  '<acronym title="Universal Resource Locator">URL</acronym>' =>
  '<acronym title="Universal Resource Locator">URL</acronym>',

  '<acronym title="Universal Resource Locator">URL</acronym> Parameters' =>
  '<acronym title="Universal Resource Locator">URL</acronym>-Parameter',

  'Failed to delete <code>%s</code>. Please check permissions.' =>
  '<code>%s</code> konnte nicht gelöscht werden. Bitte überprüfen Sie die Zugriffsrechte.',

  'Failed to write Event to <code>%s</code>. Please check permissions.' =>
  '<code>%s</code> konnte nicht gespeichert werden. Bitte überprüfen Sie die Zugriffsrechte.',

  '[Symphony] A new entry was created on %s' =>
  '[Symphony] Ein neuer Eintrag auf %s wurde erstellt',

  '\'%s\' contains invalid data. Please check the contents.' =>
  '\'%s\' enthält ungültige Daten. Bitte überprüfen Sie den Inhalt.',

  'Submit' =>
  'Abschicken',

  'descending' =>
  'absteigend',

  'Monkeys' =>
  'Affen',

  'Enable' =>
  'Aktivieren',

  'Enabled' =>
  'Aktiviert',

  'All of these fields can be set dynamically using the exact field name of another field in the form as shown below in the example form:' =>
  'Alle diese Felder können dynamisch befüllt werden, indem Sie den genauen Feldnamen eines anderen Feldes des Formulares verwenden, wie das beigefügte Beispiel zeigt:',

  'General' =>
  'Allgemein',

  'Old Password' =>
  'Altes Passwort',

  'No <code>/symphony</code> directory was found at this location. Please upload the contents of Symphony\'s install package here.' =>
  'An diesem Ort konnte kein Verzeichnis <code>/symphony</code> gefunden weren. Bitte laden Sie den Inhalt des Symphony-Installationspakets hierher hoch.',

  'An existing <code>/workspace</code> directory was found at this location. Symphony will use this workspace.' =>
  'An diesem Ort wurde ein bereits existierendes <code>/workspace</code>-Verzeichnis gefunden. Symphony wird diesen Workspace verwenden.',

  'Login Details' =>
  'Anmeldedaten',

  'Login' =>
  'Anmeldung',

  'Apply' =>
  'Anwenden',

  'The parameter <code id="output-param-name">$ds-%s</code> will be created with this field\'s value for XSLT or other data sources to use.' =>
  'Auf Grundlage dieses Feldes wird das Parameter <code id="output-param-name">$ds-%s</code> zur Benutzung in der XSL-Transformation oder in anderen Datenquellen bereitgestellt.',

  'ascending' =>
  'aufsteigend',

  'Output Options' =>
  'Ausgabeoptionen',

  'With Selected...' =>
  'Auswahl &#8230;',

  'Author' =>
  'Autor',

  'Create Author' =>
  'Autor erstellen',

  'Author ID' =>
  'Autor-ID',

  'Authors' =>
  'Autoren',

  'Filter Authors by' =>
  'Autoren filtern mit',

  'Save Changes' =>
  'Änderungen speichern',

  'Create Utility' =>
  'Baustein erstellen',

  'Utility could not be written to disk. Please check permissions on <code>/workspace/utilities</code>.' =>
  'Baustein konnte nicht auf der Festplatte gespeichert werden. Bitte überprüfen Sie die Zugriffsrechte für <code>/workspace/utilities</code>.',

  'Utilities' =>
  'Bausteine',

  'Edit' =>
  'Bearbeiten',

  'There was an error while trying to upload the file <code>%1$s</code> to the target directory <code>%2$s</code>.' =>
  'Beim Hochladen der Datei <code>%1$s</code> in den Zielordner <code>%2$s</code> ist ein Fehler aufgetreten.',

  'There was a problem loading the Symphony navigation XML document.' =>
  'Beim Laden des Symphony-Navigations-XML gab es ein Problem.',

  'Error reading image <code>%s</code>. Check it exists and is readable.' =>
  'Beim Lesen des Bildes <code>%s</code> ist ein Fehler aufgetreten. Bitte überprüfen, ob es existiert und lesbar ist.',

  'Error reading external image <code>%s</code>. Please check the URI.' =>
  'Beim Lesen des externen Bildes <code>%s</code> ist ein Fehler aufgetreten. Bitte überprüfen Sie die URI.',

  'A database error occurred while attempting to reorder.' =>
  'Beim Neuordnen ist ein Datenbankfehler aufgetreten.',

  'Error writing to temporary file <code>%s</code>.' =>
  'Beim Schreiben der temporären Datei <code>%s</code> ist ein Fehler aufgetreten.',

  'Entry encountered errors when saving.' =>
  'Beim Speichern des Eintrags sind Fehler aufgetreten.',

  'There were some problems while attempting to save. Please check below for problem fields.' =>
  'Beim Speichern sind einige Fehler aufgetreten. Bitte überprüfen Sie die betroffenen Felder.',

  'Unknown errors where encountered when saving.' =>
  'Beim Speichern sind unbekannte Fehler aufgetreten.',

  'An error occurred while processing this form. <a href="#error">See below for details.</a>' =>
  'Beim Verarbeiten dieses Formulars ist ein Fehler aufgetreten. <a href="#error">Nähere Details: siehe unten.</a>',

  'Some errors were encountered while attempting to save.' =>
  'Beim Versucht zu speichern sind Fehler aufgetreten.',

  'Example Front-end Form Markup' =>
  'Beispiel-Frontend-Formular',

  'Example XML' =>
  'Beispiel-XML',

  'Leave these fields unless you are sure they need to be changed.' =>
  'Belassen Sie diese Felder wie sie sind, es sei denn, Sie sind sich sicher, dass sie geändert werden müssen.',

  'Encumbered' =>
  'belastet',

  'Total Database Queries' =>
  'Benötigte Gesamtzahl an Datenbankabfragen',

  'Total Time Spent on Queries' =>
  'Benötigte Gesamtzeit für Datenbankabfragen',

  'Time Triggering All Events' =>
  'Benötigte Zeit um alle Ereignisse auszulösen',

  'Time Running All Data Sources' =>
  'Benötigte Zeit zum Ausführen aller Datenquellen',

  'Use an XPath expression to select which elements from the source XML to include.' =>
  'Benutzen Sie einen X-Path-Ausdruck, um die einzubindenen Elemente der XML-Quelle auszuwählen.',

  'Custom XML' =>
  'Benutzerdefiniertes XML',

  'User Type' =>
  'Benutzergruppe',

  'User type' =>
  'Benutzergruppe',

  'User Information' =>
  'Benutzerinformationen',

  'Username' =>
  'Benutzername',

  'Create a section' =>
  'Bereich erstellen',

  'Create Section' =>
  'Bereich erstellen',

  'Sections' =>
  'Bereiche',

  'Description' =>
  'Beschreibung',

  'Please <a href="%s">login</a> to view this page.' =>
  'Bitte <a href="%s">melden Sie sich an</a>, um diese Seite zu sehen.',

  'Please add the following personal details for this user.' =>
  'Bitte ergänzen Sie die nachfolgenden persönlichen Informationen dieses Nutzers.',

  'Please provide Symphony with access to a database.' =>
  'Bitte räumen Sie Symphony einen Datenbankzugang ein.',

  'Blueprints' =>
  'Blaupausen',

  'Cannot load CMYK JPG Images' =>
  'CMYK-JPG-Bilder können nicht geladen werden',

  'The date specified in \'%s\' is invalid.' =>
  'Das angegebene Datum \'%s\' ist ungültig.',

  'The supplied password was rejected. <a href="%s">Retrieve password?</a>' =>
  'Das eingegebene Passwort wurde abgewiesen. <a href="%s">Neues Passwort anfordern?</a>',

  'Could not find Event <code>%s</code>. If the Event was provided by an Extensions, ensure that it is installed, and enabled.' =>
  'Das Ereignis <code>%s</code> konnte nicht gefunden werden. Wenn dieses Ereignis von einer Erweiterung bereitgestellt wurde, überprüfen Sie, dass diese installiert und aktiviert ist.',

  'Uploading \'%s\' failed. File upload stopped by extension.' =>
  'Das Hochladen von \'%s\' ist fehlgeschlagen. Der Vorgang wurde von einer Erweiterung unterbrochen.',

  'Uploading \'%s\' failed. Could not write temporary file to disk.' =>
  'Das Hochladen von \'%s\' ist fehlgeschlagen. Temporäre Datei konnte nicht gespeichert werden.',

  'Password is required' =>
  'Das Passwort ist eine Pflichtangabe',

  'The password and confirmation did not match. Please retype your password.' =>
  'Das Passwort und seine Wiederholung stimmten nicht überein. Bitte geben Sie Ihr Passwort erneut ein.',

  'Destination folder, <code>%s</code>, is not writable. Please check permissions.' =>
  'Das Zielverzeichnis <code>%s</code> ist nicht schreibbar. Bitte überprüfen Sie die Zugriffsrechte.',

  'Can\'t open file %s' =>
  'Datei %s konnte nicht geöffnet werden',

  'Unable to remove file - %s' =>
  'Datei konnte nicht entfernt werden – %s',

  'Files' =>
  'Dateien',

  'Database' =>
  'Datenbank',

  'Database Error' =>
  'Datenbankfehler',

  'Database Connection' =>
  'Datenbankverbindung',

  'Data Source' =>
  'Datenquelle',

  'Create Data Source' =>
  'Datenquelle erstellen',

  'Failed to write Data source to <code>%s</code>. Please check permissions.' =>
  'Datenquelle konnte nicht unter <code>%s</code> gespeichert werden. Bitte überprüfen Sie die Zugriffsrechte.',

  'Data Sources' =>
  'Datenquellen',

  'Date and Time' =>
  'Datum und Zeit',

  'Date Format' =>
  'Datumsformat',

  'Disable' =>
  'Deaktivieren',

  'Debug' =>
  'Debug',

  'Uninstall' =>
  'Deinstallieren',

  'Username is required' =>
  'Der Benutzername ist eine Pflichtangabe',

  'Section is invalid' =>
  'Der Bereich ist ungültig',

  'The send email filter, upon the event successfully saving the entry, takes input from the form and send an email to the desired recipient. <b>This filter currently does not work with the "Allow Multiple" option.</b> The following are the recognised fields:' =>
  'Der E-Mail-Versandfilter sendet, nach erfolgreichem Speichern des Eintrags durch das Ereignis, eine E-Mail mit allen Eingaben des Formulars an die gewünschten Empfänger. <b>Dieser Filter funktioniert derzeit nicht mit der Option „Mehrere zulassen“.</b> Nachfolgende Felder werden erkannt:',

  'The section associated with the data source <code>%s</code> could not be found.' =>
  'Der mit der Datenquelle <code>%s</code> verbundene Bereich konnte nicht gefunden werden.',

  'Last name is required' =>
  'Der Nachname ist eine Pflichtangabe',

  'Folder is not writable. Please check permissions.' =>
  'Der Ordner ist nicht schreibbar. Bitte überprüfen Sie die Zugriffsrechte.',

  'Body is a required field' =>
  'Der Datenbereich ist ein Pflichtfeld',

  'Body is a required field.' =>
  'Der Datenbereich ist ein Pflichtfeld.',

  'The table prefix <code><!-- TABLE-PREFIX --></code> is already in use. Please choose a different prefix to use with Symphony.' =>
  'Der Tabellenprefix <code><!-- TABLE-PREFIX --></code>ist bereits in Benutzung. Bitte wählen Sie einen anderen Prefix, der in Verbindung mit Symphony verwendet werden soll.',

  'The Section you are looking, <code>%s</code> for could not be found.' =>
  'Der von Ihnen gesuchte Bereich <code>%s</code> konnte nicht gefunden werden.',

  'The Section you are looking for could not be found.' =>
  'Der von Ihnen gesuchte Bereich konnte nicht gefunden werden.',

  'The entry you are looking for could not be found.' =>
  'Der von Ihnen gesuchte Eintrag konnte nicht gefunden werden.',

  'First name is required' =>
  'Der Vorname ist eine Pflichtangabe',

  'Big' =>
  'Dick',

  'The page you requested does not exist.' =>
  'Die aufgerufene Seite existiert nicht.',

  'Could not find Data Source <code>%s</code>. If the Data Source was provided by an Extensions, ensure that it is installed, and enabled.' =>
  'Die Datenquelle <code>%s</code> konnte nicht gefunden werden. Wenn diese Datenquelle von einer Erweiterung bereitgestellt wurde, überprüfen Sie, dass diese installiert und aktiviert ist.',

  'E-mail address is required' =>
  'Die E-Mail-Adresse ist eine Pflichtangabe',

  'E-mail address entered is invalid' =>
  'Die eigegebene E-Mail-adresse ist ungültig',

  'Could not find extension at location %s' =>
  'Die Erweiterung konnte nicht unter %s gefunden werden.',

  'Entry limit specified was not a valid type. String or Integer expected.' =>
  'Die festgelegte Obergrenze entspricht keinem gültigen Typ. String oder Integer erwartet. ',

  'Page could not be written to disk. Please check permissions on <code>/workspace/pages</code>.' =>
  'Die Seite konnte nicht auf der Festplatte gespeichert werden. Bitte überprüfen Sie die Zugriffsrechte für <code>/workspace/pages</code>.',

  'The page you requested to edit does not exist.' =>
  'Die Seite, die Sie bearbeiten möchten, existiert nicht.',

  'The Symphony configuration file, <code>/manifest/config.php</code>, is not writable. You will not be able to save changes to preferences.' =>
  'Die Symphony-Konfigurationsdatei <code>/manifest/config.php</code> ist nicht lesbar. Sie werden keine Änderungen der Voreinstellungen speichern können.',

  'Cannot open XML data file: %s' =>
  'Die XML-Datei %s konnten nicht geöffnet werden',

  'This is an example of the form markup you can use on your frontend:' =>
  'Dies ist ein Beispiel, dass Sie für Ihr Frontend-Formular nutzen können:',

  'This is a required field.' =>
  'Dies ist ein Pflichtfeld.',

  'This is not a valid email address. You must provide an email address since you will need it if you forget your password.' =>
  'Dies keine gültige E-Mail-Adresse. Sie müssen eine E-Mail-Adresse angeben, da Sie diese benötigen, falls Sie Ihr Passwort vergessen sollten.',

  'Delete this data source' =>
  'Diese Datenquelle löschen',

  'This is a courtesy email to notify you that an entry was created on the %1$s section. You can edit the entry by going to: %2$s' =>
  'Diese E-Mail möchte Sie darüber informieren, dass ein Eintrag im Bereich %1$s erstellt wurde. Sie können diesen bearbeiten, indem folgende Seite aufrufen: %2$s',

  'This page could not be rendered due to the following XSLT processing errors.' =>
  'Diese Seite konnte aufgrund nachfolgender XSLT-Verarbeitungfehler nicht dargestellt werden.',

  'Delete this page' =>
  'Diese Seite löschen',

  'Delete this author' =>
  'Diesen Autor löschen',

  'Delete this utility' =>
  'Diesen Baustein löschen',

  'Delete this section' =>
  'Diesen Bereich löschen',

  'Delete this entry' =>
  'Diesen Eintrag löschen',

  'Username is already taken' =>
  'Dieser Benutzername ist bereits vergeben',

  'This document is not well formed. The following error was returned: <code>%s</code>' =>
  'Dieses Dokument ist nicht wohlgeformt. Folgender Fehler wurde zurückgegeben: <code>%s</code>',

  'Delete this event' =>
  'Dieses Ereignis löschen',

  'This event will not be processed if any of these rules return true.' =>
  'Dieses Ereignis wird nicht ausgeführt werden, wenn eine dieser Regel wahr zurückgibt.',

  'This is a required field' =>
  'Dieses Feld ist verpflichtend.',

  'Dynamic Options' =>
  'Dynamische Optionen',

  'Dynamic XML' =>
  'Dynamisches XML',

  'Email' =>
  'E-Mail',

  'Send Email' =>
  'E-Mail verschicken',

  'Email Address' =>
  'E-Mail-Adresse',

  'Send Email Filter' =>
  'E-Mail-Versandfilter',

  'An Event with the name <code>%s</code> name already exists' =>
  'Ein Ereignis mit dem Namen <code>%s</code> existiert bereits',

  'A field with that element name already exists. Please choose another.' =>
  'Ein Feld mit diesem Elementnamen existiert bereits. Bitte wählen Sie einen anderen.',

  'A new password has been requested for your account. Login using the following link, and change your password via the Authors area:' =>
  'Ein neues Passwort wurden für Ihren Zugang angefordert. Sie können sich anmelden, indem Sie nachfolgendem Link folgen, und dann Ihr Passwort im Autorenbereich ändern:',

  'Unknown errors occurred while attempting to save. Please check your <a href="%s">activity log</a>.' =>
  'Ein unbekannter Fehler ist während des Speichern aufgetreten. Bitte überprüfen Sie Ihr <a href="%s">Logbuch</a>.',

  'A file with the name %1$s already exists in %2$s. Please rename the file first, or choose another.' =>
  'Eine Datei mit de mNamen %1$s existiert bereits in %2$s. Bitte benennen Sie die Datei zuerst um oder wähle Sie eine andere.',

  'A Data source with the name <code>%s</code> name already exists' =>
  'Eine Datenquelle mit dem Namen <code>%s</code> existiert bereits',

  'An email containing a customised login link has been sent. It will expire in 2 hours.' =>
  'Eine E-Mail mit personalisierten Anmeldedaten wurden verschickt. Sie verliert in zwei Stunden ihre Gültigkeit.',

  'A result limit must be set' =>
  'Eine Ergebnisobergrenze muss festgelegt werden',

  'A page number must be set' =>
  'Eine Seitenzahl muss festgelegt werden',

  'Included Elements' =>
  'Eingebundene Elemente',

  'Preferences' =>
  'Einstellungen',

  'Entry [created | edited] successfully.' =>
  'Eintrag erfolgreich [erstellt | bearbeitet].',

  'Entry edited successfully.' =>
  'Eintrag erfolgreich bearbeitet.',

  'Entry created successfully.' =>
  'Eintrag erfolgreich erstellt.',

  'Create Entry' =>
  'Eintrag erstellen',

  'Entries' =>
  'Einträge',

  'Recipient username was invalid' =>
  'Empfängername war ungültig',

  'Developer' =>
  'Entwickler',

  'Aardvarks' =>
  'Erdferkel',

  'Create Event' =>
  'Ereignis erstellen',

  'Events' =>
  'Ereignisse',

  'Success and Failure XML Examples' =>
  'Erfolgs- und Fehlerbeispiele',

  'Filter Results' =>
  'Ergebnisfilter',

  'Result' =>
  'Ergebnisse',

  'First' =>
  'Erste',

  'Advanced Configuration' =>
  'Erweiterte Einstellungen',

  'Extensions' =>
  'Erweiterungen',

  'A Utility with that name already exists. Please choose another.' =>
  'Es existiert bereits ein Baustein mit dem namen <code>%s</code>.',

  'A Section with the name <code>%s</code> name already exists' =>
  'Es existiert bereits ein Bereich mit dem Namen <code>%s</code>',

  'There is already a field of type <code>%s</code>. There can only be one per section.' =>
  'Es existiert bereits ein Feld des Typs <code>%s</code>. Es ist für jeden Bereich nur eines zulässig.',

  'A 403 type page already exists.' =>
  'Es existiert bereits eine 403-Fehlerseite.',

  'A 404 type page already exists.' =>
  'Es existiert bereits eine 404-Fehlerseite.',

  'An index type page already exists.' =>
  'Es existiert bereits eine Index-Seite.',

  'A page with that handle %s already exists' =>
  'Es existiert bereits eine Seite mit diesem Bezeichner %s.',

  'A page with that title %s already exists' =>
  'Es existiert bereits eine Seite mit dem Titel %s.',

  'A page with that handle already exists' =>
  'Es existiert bereits eine Seite mit diesem Bezeichner.',

  'A page with that title already exists' =>
  'Es existiert bereits eine Seite mit diesem Titel.',

  'There was a problem locating your account. Please check that you are using the correct email address.' =>
  'Es gab Schwierigkeiten Ihren Benutzerzugang zuzuordnen. Überprüfen Sie bitte, ob Sie die richtige E-Mail-Adresse angegeben haben.',

  'An unknown database occurred while attempting to create the section.' =>
  'Es ist ein unbekannter Datenbankfehler beim Erstellen des Bereiches aufgetreten.',

  'No suitable XSLT processor was found.' =>
  'Es konnte kein ausreichender XSLT-Prozessor gefunden werden.',

  'No suitable engine object found' =>
  'Es konnte kein ausreichendes Engine-Objekt gefunden werden.',

  'No valid recipients found. Check send-email[recipient] field.' =>
  'Es konnten keine Empfänger gefunden werden. Überprüfen Sie das Feld send-email[recipient].',

  'There appears to be an existing <code>.htaccess</code> file in the <code>/symphony</code> directory.' =>
  'Es scheint bereits eine <code>.htaccess</code>-Datei innerhalb des Verzeichnisses <code>/symphony</code> zu existieren.',

  'There appears to be an existing <code>.htaccess</code> file in the Symphony install location. To avoid name clashes, you will need to delete or rename this file.' =>
  'Es scheint bereits eine <code>.htaccess</code>-Datei innerhalb Ihrer Symphony-Installation zu existieren. Um Missverständnisse zu vermeiden, müssen Sie diese löschen oder umbenennen.',

  'It will expire in 2 hours. If you did not ask for a new password, please disregard this email.' =>
  'Es wird in zwei Stunden ablaufen. Falls Sie kein neues Passwort angefordert haben, ignorieren Sie bitte diese Nachricht.',

  'Existing Values' =>
  'Existierende Werte',

  'Wrong password. Enter old one to change email address.' =>
  'Falsches Passwort. Geben Sie Ihr altes Passwort zum Ändern der E-Mail-Adresse ein.',

  'Wrong password. Enter old password to change it.' =>
  'Falsches Passwort. Geben Sie Ihr altes Passwort zum Ändern ein.',

  'Outstanding Requirements' =>
  'Fehlende Anforderungen',

  'Missing Requirements' =>
  'Fehlende Voraussetzungen',

  'Fields' =>
  'Felder',

  'Filter Rules' =>
  'Filterregeln',

  'Women' =>
  'Frauen',

  'Output Creation Time' =>
  'Gesamtzeit der Ausgabe',

  'An empty result will be returned when this parameter does not have a value.' =>
  'Gibt ein leeres Ergebnis zurück, wenn dieses Parameter keinen Wert hat.',

  'Large' =>
  'Groß',

  'Essentials' =>
  'Grundangaben',

  'Group By' =>
  'Gruppieren nach',

  'Hairy' =>
  'Haarig',

  'Handle' =>
  'Bezeichner',

  'Hot' =>
  'Heiß',

  'Hi %s,' =>
  'Hi %s,',

  'Host' =>
  'Host',

  'Dogs' =>
  'Hunde',

  'ID' =>
  'ID',

  'The Symphony Team' =>
  'Ihr Symphony-Team',

  'Installation Failure' =>
  'Installation fehlgeschlagen',

  'Yes' =>
  'Ja',

  'Cold' =>
  'Kalt',

  'Cats' =>
  'Katze',

  'Bugs' =>
  'Käfer',

  'None' =>
  'Keine Angaben',

  'No records found.' =>
  'Keine Einträge gefunden.',

  'None found.' =>
  'Keine Einträge.',

  'Small' =>
  'Klein',

  'Coconut' =>
  'Kokosnuss',

  'list of comma author usernames.' =>
  'Kommagetrennte Liste der Autoren-Benutzernamen.',

  'Components' =>
  'Komponenten',

  'Could not add directory "%s".' =>
  'Konnte das Verzeichnis "%s" nicht hinzufügen.',

  'Could not add file "%s".' =>
  'Konnte die Datei "%s" nicht hinzufügen.',

  'Long Description <i>Optional</i>' =>
  'Lange Beschreibung <i>optional</i>',

  'Slow Queries (> 0.09s)' =>
  'Langsame Abfragen (> 0,09 s)',

  'Leave new password field blank to keep the current password' =>
  'Lassen Sie das Neue-Passwort-Feld leer, um das bisherige Passwort zu behalten.',

  'Last' =>
  'Letzte',

  'Dear <!-- RECIPIENT NAME -->,' =>
  'Liebe(r) <!-- RECIPIENT NAME -->,',

  'Delete' =>
  'Löschen',

  'Men' =>
  'Männer',

  'Allow Multiple' =>
  'Mehrere zulassen',

  'At least one source must be specified, dynamic or static.' =>
  'Mindestens eine Quelle, dynamisch oder statisch, muss festgelegt werden.',

  'Best Regards,' =>
  'Mit freundlichen Grüßen,',

  'Must be a valid number or parameter' =>
  'Muss eine gültige Zahl oder ein gültiger Parameter sein',

  'Must be a valid number' =>
  'Muss eine gültige Zahl sein',

  'Must be greater than zero' =>
  'Muss größer als Null sein',

  'My<abbr title="Structured Query Language">SQL</abbr> 4.1 or above' =>
  'My<abbr title="Structured Query Language">SQL</abbr> 4.1 oder höher',

  'MySQL Error (%1$s): %2$s in query "%3$s"' =>
  'MySQL-Fehler (%1$s): %2$s in Anfrage "%3$s"',

  'When saved successfully, the following XML will be returned:' =>
  'Nach erfolgreicher Speicherung, wird nachfolgendes XML ausgegeben:',

  'The following is an example of what is returned if any filters fail:' =>
  'Nachfolgendes Beispiel zeigt das Ergebnis, wenn ein Filter einen Fehler ausgibt:',

  'Last Name' =>
  'Nachname',

  'Message' =>
  'Nachricht',

  'Name' =>
  'Name',

  'Name is a required field.' =>
  'Name ist ein Pflichfeld.',

  'Namespace' =>
  'Namensraum',

  'Namespace Declarations <i>Optional</i>' =>
  'Namensraumdeklarationen <i>optional</i>',

  'Navigation' =>
  'Navigation',

  'Filter Navigation by' =>
  'Navigation filtern mit',

  'Failed to load Navigation' =>
  'Navigation konnte nicht geladen werden',

  'Next &rarr;' =>
  'Nächste &rarr;',

  'No' =>
  'Nein',

  'new' =>
  'neu',

  'Create new' =>
  'Neu erstellen',

  'Create New' =>
  'Neu erstellen',

  'Create a new data source' =>
  'Neue Datenquelle erstellen',

  'Create a new page' =>
  'Neue Seite erstellen',

  'Create a new utility' =>
  'Neuen Baustein erstellen',

  'Create a new entry' =>
  'Neuen Eintrag erstellen',

  'Create a new event' =>
  'Neues Ereignis erstellen',

  'New Password' =>
  'Neues Passwort',

  'New Symphony Account Password' =>
  'Neues Passwort für Ihren Symphony-Zugang',

  'Confirm New Password' =>
  'Neues Passwort wiederholen',

  'Unsupported image type. Supported types: GIF, JPEG and PNG' =>
  'Nicht unterstützter Bildtyp. Unterstützte Typen: GIF, JPEG und PNG',

  'Admin Only' =>
  'Nur Administratoren',

  'Untitled' =>
  'Ohne Titel',

  'Optional' =>
  'optional',

  'Params' =>
  'Parameter',

  'Parameter Output' =>
  'Parameterausgabe',

  'Password' =>
  'Passwort',

  'Confirm Password' =>
  'Passwort wiederholen',

  'Passwords did not match' =>
  'Passworteingabe stimmte nicht überein',

  'Personal Information' =>
  'Persönliche Informationen',

  'Pirates' =>
  'Piraten',

  'Port' =>
  'Port',

  'Profile' =>
  'Profil',

  'Lumpy' =>
  'Pummelig',

  'Source' =>
  'Quelle',

  'Body' =>
  'Daten',

  'Region' =>
  'Region',

  'Round' =>
  'Rund',

  'Pigs' =>
  'Schweine',

  'Page %1$s of %2$s' =>
  'Seite %1$s von %2$s',

  'Create Page' =>
  'Seite erstellen',

  'Page Not Found' =>
  'Seite konnte nicht gefunden werden',

  'Page not found' =>
  'Seite nicht gefunden',

  'Pages' =>
  'Seiten',

  'Page ID' =>
  'Seiten-ID',

  'Page Metadata' =>
  'Seiteneinstellungen',

  'Page Profiler' =>
  'Seitenprofil',

  'Page Type' =>
  'Seitentyp',

  'Set %s' =>
  'Setze %s',

  'You must enter a Password. This will be your Symphony login information.' =>
  'Sie müssen ein Passwort anlegen, welches für Sie bei der Symphony-Anmeldung verwendet werden soll.',

  'You must enter a Username. This will be your Symphony login information.' =>
  'Sie müssen einen Benutzernamen anlegen, welcher für Sie bei der Symphony-Anmeldung verwendet werden soll.',

  'You must enter your name.' =>
  'Sie müssen Ihren Namen angeben.',

  'You are not authorised to access this section.' =>
  'Sie sind nicht autorisiert auf diesen Bereich zuzugreifen',

  'You are not authorised to access this page.' =>
  'Sie sind nicht berechtigt diese Seite zu besuchen.',

  'You are already using the most recent version of Symphony. There is no need to run the installer, and can be safely deleted.' =>
  'Sie verwenden bereits die aktuellste Version von Symphony. Es ist nicht nötig das Installationsprogramm laufen zu lassen, es kann sicher entfernt werden.',

  'You are not using the most recent version of Symphony. This update is only compatible with Symphony 2.' =>
  'Sie verwenden nicht die aktuellste Version von Symphony. Diese Aktualisierung ist nur mit Symphony 2 kompatibel.',

  'Once installed, you will be able to login to the Symphony admin with these user details.' =>
  'Sobald die Installation abgeschlossen ist, können Sie mit diesen Zugangsdaten auf Symphony zugreifen.',

  'Sort by %1$s %2$s' =>
  'Sortiere nach %1$s %2$s',

  'Sort By' =>
  'Sortieren nach',

  'Sort Order' =>
  'Sortierreihenfolge',

  'Sorting and Limiting' =>
  'Sortierung und Begrenzung',

  'Default Section' =>
  'Standardbereich',

  'Static XML' =>
  'Statisches XML',

  'Status' =>
  'Status',

  'Make textarea %s rows tall' =>
  'Stelle Textfeld %s Zeilen hoch dar.',

  'Make sure that you delete <code>' =>
  'Stellen Sie sich, dass Sie <code>',

  'Symphony' =>
  'Symphony',

  'Update Symphony' =>
  'Symphony Aktualisierung',

  'Symphony needs a recent version of <abbr title="PHP: Hypertext Pre-processor">PHP</abbr>.' =>
  'Symphony benötigt eine aktuelle Version von <abbr title="PHP: Hypertext Pre-processor">PHP</abbr>.',

  'Symphony needs a recent version of My<abbr title="Structured Query Language">SQL</abbr>.' =>
  'Symphony benötigt eine aktuelle Version von My<abbr title="Structured Query Language">SQL</abbr>.',

  'Symphony needs the following requirements satisfied before installation can proceed.' =>
  'Symphony benötigt folgende Voraussetzungen bevor die Installation fortgesetzt werden kann.',

  'Symphony needs permission to read and write both files and directories.' =>
  'Symphony benötigt Lese- und Schreibrechte für Dateien und Verzeichnisse.',

  'Symphony needs an XSLT processor such as Lib<abbr title="eXtensible Stylesheet Language Transformation">XSLT</abbr> or Sablotron to build pages.' =>
  'Symphony benötigt zum Seitenaufbau einen XSLT-Prozessor wie beispielsweise Lib<abbr title="eXtensible Stylesheet Language Transformation">XSLT</abbr> oder Sablotron.',

  'Symphony does not have write permission to the <code>/manifest</code> directory. Please modify permission settings on this directory and its contents to allow this, such as with a recursive <code>chmod -R</code> command.' =>
  'Symphony hat keine Schreibrechte das Verzeichnis <code>/manifest</code>. Bitte passen Sie die Zugriffsrechte dieses Verzeichnisses und seiner Inhalte an, zum Beispiel mit einen rekursiven <code>chmod -R</code> Kommando.',

  'Symphony does not have write permission to the <code>/symphony</code> directory. Please modify permission settings on this directory. This is necessary only during installation, and can be reverted once installation is complete.' =>
  'Symphony hat keine Schreibrechte das Verzeichnis <code>/symphony</code>. Bitte passen Sie die Zugriffsrechte dieses Verzeichnisses an. Diese Änderung ist nur während der Installation nötig und kann danach rückgängig gemacht werden.',

  'Symphony does not have write permission to the existing <code>/workspace</code> directory. Please modify permission settings on this directory and its contents to allow this, such as with a recursive <code>chmod -R</code> command.' =>
  'Symphony hat keine Schreibrechte das Verzeichnis <code>/workspace</code>. Bitte passen Sie die Zugriffsrechte dieses Verzeichnisses und seiner Inhalte an, zum Beispiel mit einen rekursiven <code>chmod -R</code> Kommando.',

  'Symphony does not have write permission to the temporary <code>htaccess</code> file. Please modify permission settings on this file so it can be written to, and renamed.' =>
  'Symphony hat keine Schreibrechte für die temporäre <code>.htaccess</code>-Datei. Bitte passen Sie die Zugriffsrechte dieser Datei so an, dass sie umbenannt und beschrieben werden kann.',

  'Symphony does not have write permission to the root directory. Please modify permission settings on this directory. This is necessary only if you are not including a workspace, and can be reverted once installation is complete.' =>
  'Symphony hat keine Schreibreiche für das Wurzelverzeichnis. Bitte passen Sie die Zugriffsrechte dieses Verzeichnisses an. Diese Änderung ist nur nötig, wenn Sie keinen Workspace einbinden und kann nach der Installation rückgängig gemacht werden.',

  'Install Symphony' =>
  'Symphony installieren',

  'Symphony is ready to be installed at the following location.' =>
  'Symphony ist bereit für die Installation an nachfolgendem Ort.',

  'Symphony normally specifies UTF-8 character encoding for database entries. With compatibility mode enabled, Symphony will instead use the default character encoding of your database.' =>
  'Symphony verwendet normalerweise UTF-8-Zeichenkodierung für Datenbankeinträge. Im Kompatibilitätsmodus verwendet Symphony anstelle dessen die Standardzeichenkodierung ihrer Datenbank.',

  'Symphony was unable to connect to the specified database. You may need to modify host or port settings.' =>
  'Symphony war nicht in der Lage eine Verbindung zur angegebenen Datenbank aufzubauen. Möglicherweise müssen Sie Ihre Host- oder Port-Einstellungen anpassen.',

  'Symphony Concierge' =>
  'Symphony-Concierge',

  'Symphony Database Error' =>
  'Symphony-Datenbankfehler',

  'System' =>
  'System',

  'System ID' =>
  'System-ID',

  'System Author' =>
  'Systemautor',

  'System Date' =>
  'Systemdatum',

  'Table Prefix' =>
  'Tabellenprefix',

  'Title' =>
  'Titel',

  'Title is a required field' =>
  'Titel ist ein Pflichtfeld',

  'Type' =>
  'Typ',

  'To edit an existing entry, include the entry ID value of the entry in the form. This is best as a hidden field like so:' =>
  'Um einen existierenden Eintrag zu bearbeiten, müssen Sie die Eintrags-ID im Formular einbinden. Dies geht am besten mit einem versteckten Feld:',

  'To redirect to a different location upon a successful save, include the redirect location in the form. This is best as a hidden field like so, where the value is the URL to redirect to:' =>
  'Um nach erfolgreichem Speichern zu einer anderen Adresse weiterzuleiten, müssen Sie das Umleitungsziel im Formular einbinden. Dies geht am besten mit einem versteckten Feld, wobei der Wert der Ziel-URL entspricht:',

  'Environment Settings' =>
  'Umgebungseinstellungen',

  'Unknown Section' =>
  'Unbekannter Bereich',

  'Unknown Entry' =>
  'Unbekannter Eintrag',

  'and parent' =>
  'und das Elternelement',

  'URI' =>
  'URI',

  'URL' =>
  'URL',

  'URL Settings' =>
  'URL-Einstellungen',

  'URL Handle' =>
  'URL-Bezeichner',

  'URL Parameters' =>
  'URL-Parameter',

  'Parent Page' =>
  'Übergeordnete Seite',

  'Validation Rule <i>Optional</i>' =>
  'Validierungsregel <i>optional</i>',

  'Forbidden' =>
  'Verboten',

  'Publish' =>
  'Verfassen',

  'Release Date' =>
  'Veröffentlichungsdatum',

  'Required URL Parameter <i>Optional</i>' =>
  'Verpflichtende URL-Parameter <i>optional</i>',

  'Version' =>
  'Version',

  'Version %s' =>
  'Version %s',

  'Use compatibility mode' =>
  'Verwende Kompatibilitätsmodus',

  'Use <code>{$param}</code> syntax to specify dynamic portions of the URL.' =>
  'Verwenden Sie <code>{$param}</code>, um dynamische Teile der URL festzulegen.',

  'Use <code>{$param}</code> syntax to limit by page parameters.' =>
  'Verwenden Sie <code>{$param}</code>, um mit Seitenparametern zu begrenzen.',

  'Use <code>{$param}</code> syntax to filter by page parameters.' =>
  'Verwenden Sie <code>{$param}</code>, um mit Seitenparametern zu filtern.',

  'Directories' =>
  'Verzeichnisse',

  'First Name' =>
  'Vorname',

  'Suggestion List' =>
  'Vorschlagsliste',

  'Birds' =>
  'Vögel',

  'An error occurred during installation. You can view you log <a href="install-log.txt">here</a> for more details.' =>
  'Während der Installation ist ein Fehler aufgetreten. Sie können Nähere Informationen in den <a href="install-log.txt">Aufzeichnungen</a> nachlesen.',

  'Website Preferences' =>
  'Webseiteneinstellungen',

  'Website Name' =>
  'Webseitenname',

  'When an error occurs during saving, due to either missing or invalid fields, the following XML will be returned' =>
  'Wenn beim Speichern ein Fehler auftritt, weil Felder fehlen oder ungültig sind, wird nachfolgendes XML ausgeben',

  'Value' =>
  'Wert',

  'It looks like your trying to create an entry. Perhaps you want custom fields first? <a href="%s">Click here to create some.</a>' =>
  'Wie es scheint, versuchen Sie einen Eintrag erstellen. Vielleicht möchten Sie zunächst einige Felder anlegen? <a href="%s">Klicken Sie hier, um Felder zu erstellen.</a>',

  'It looks like your trying to create an entry. Perhaps you want fields first? <a href="%s">Click here to create some.</a>' =>
  'Wie es scheint, versuchen Sie einen Eintrag erstellen. Vielleicht möchten Sie zunächst einige Felder anlegen? <a href="%s">Klicken Sie hier, um Felder zu erstellen.</a>',

  'Weasels' =>
  'Wiesel',

  'Root Path' =>
  'Wurzelpfad',

  'Worms' =>
  'Würmer',

  'XML' =>
  'XML',

  'XML is invalid' =>
  'XML ist ungültig',

  'XML Output' =>
  'XML-Ausgabe',

  'XML Generation Function' =>
  'XML-Generierungsfunktion',

  'XSLT Generation' =>
  'XSLT-Generierung',

  'XSLT Processing Error' =>
  'XSLT-Verarbeitungsfehler',

  'Viewing %1$s - %2$s of %3$s entries' =>
  'Zeige %1$s - %2$s von %3$s Einträgen',

  'Show a maximum of %s results' =>
  'Zeige maximal %s Ergebnisse',

  'Show page %s of results' =>
  'Zeige Seite %s der Ergebnisse',

  'Line %s' =>
  'Zeile %s',

  'Time Format' =>
  'Zeitformat',

  'Destination Directory' =>
  'Zielordner',

  'ZLib Compression Library' =>
  'ZLib-Compression-Library',

  'Use Field' =>
  'Zu verwendendes Feld',

  'random' =>
  'zufällig',

  'Access Denied' =>
  'Zugriff verweigert',

  'Permission Settings' =>
  'Zugriffseinstellungen',

  'XML returned is invalid.' =>
  'Zurückgegebenes XML ist ungültig',

  'Two custom fields have the same element name. All element names must be unique.' =>
  'Zwei Felder haben den selben Elementnamen. Alle Elementnamen müssen eindeutig sein.',

  'Could not find Field <code>%1$s</code> at <code>%2$s</code>. If the Field was provided by an Extension, ensure that it is installed, and enabled.' =>
  'Das Feld <code>%1$s</code> konnte nicht unter <code>%2$s</code> gefunden werden. Wenn dieses Feld von einer Erweiterung bereitgestellt wurde, überprüfen Sie, dass diese installiert und aktiviert ist.',

  'Could not find Text Formatter <code>%s</code>. If the Text Formatter was provided by an Extensions, ensure that it is installed, and enabled.' =>
  'Der Textformatierer <code>%s</code> konnte nicht gefunden werden. Wenn dieser Textformatierer von einer Erweiterung bereitgestellt wurde, überprüfen Sie, dass diese installiert und aktiviert ist.',

  'Customise how Date and Time values are displayed throughout the Administration interface.' =>
  'Passen Sie an, wie Datums- und Zeitangaben innerhalb des Administrationsbereichs dargestellt werden.',

  'Data retrieved from the Symphony support server is decompressed with the ZLib compression library.' =>
  'Daten, die vom Symphony-Supportserver empfangen werden, wir mit der ZLib-Kompression-Bibliothek dekomprimiert.',

  'Data source output grouping is not supported by the <code>%s</code> field' =>
  'Ergebnisgruppierung für Datenquellen wird vom Feld <code>%s</code> nicht unterstützt',

  'Enter your email address to be sent a remote login link with further instructions for logging in.' =>
  'Geben Sie Ihre E-Mail-Adresse an, um einen Link mit weiteren Erläuterungen zur Anmeldung zugesandt zubekommen.',

  'Error creating field object with id %1$d, for filtering in data source "%2$s". Check this field exists.' =>
  'Beim Erstellen des Feldobjektes mit der ID %1$s zum Filtern der Datenquelle "%2$s" ist ein Fehler aufgetreten.',

  'File chosen in "%1$s" exceeds the maximum allowed upload size of %2$s specified by your host.' =>
  'Die in "%1$s" ausgewählte Datei überschreitet die von Ihrem Host festgelegte, maximal erlaubte Uploadgröße von %2$s.',

  'File chosen in "%1$s" exceeds the maximum allowed upload size of %2$s, specified by Symphony.' =>
  'Die in "%1$s" ausgewählte Datei überschreitet die von Symphony festgelegte, maximal erlaubte Uploadgröße von %2$s.',

  'File chosen in \'%s\' does not match allowable file types for that field.' =>
  'Die in \'%s\' ausgewählte Datei entspricht keinem erlaubten Dateityp für dieses Feld.',

  'File chosen in \'%s\' was only partially uploaded due to an error.' =>
  'Die in \'%s\' ausgewählte Datei wurde aufgrund eines Fehlers nur teilweise hochgeladen.',

  'Invalid element name. Must be valid QName.' =>
  'Ungültiger Elementname. Muss ein gültiger QName sein.',

  'Invalid Entry ID specified. Could not create Entry object.' =>
  'Ungültige Eintrags-ID angegeben. Eintragsobjekt konnte nicht erstellt werden.',

  'Invalid image resource supplied' =>
  'Ungültige Bildquelle übermittelt',

  'Text Input' =>
  'Eingabefeld',

  'Textarea' =>
  'Textfeld',

  'Select Box' =>
  'Auswahlfeld',

  'Checkbox' =>
  'Kontrollkästchen',

  'Date' =>
  'Datum',

  'File Upload' =>
  'Dateiupload',

  'Tag List' =>
  'Tag-Liste',

  'Last Seen' =>
  'Letzter Besuch',

  'Data source updated at %1$s. <a href="%2$s">Create another?</a> <a href="%2$s">View all Data sources</a>' =>
  'Die Datenquelle wurde um %1$s Uhr aktualisiert. <a href="%2$s">Möchten Sie eine weitere erstellen?</a> <a href="%2$s">Zeige alle Datenquellen</a>',

  'Data source created at %1$s. <a href="%2$s">Create another?</a> <a href="%3$s">View all Data source</a>' =>
  'Die Datenquelle wurde um %1$s Uhr erstellt. <a href="%2$s">Möchten Sie eine weitere erstellen?</a> <a href="%2$s">Zeige alle Datenquellen</a>',

  'sections' =>
  'bereiche',

  'authors' =>
  'autoren',

  'navigation' =>
  'navigation',

  'static_xml' =>
  'statisches_xml',

  'dynamic_xml' =>
  'dynamisches_xml',

  'Event updated at %1$s. <a href="%2$s">Create another?</a> <a href="%3$s">View all Events</a>' =>
  'Das Ereignis wurde um %1$s Uhr aktualisiert. <a href="%2$s">Möchten Sie ein weiteres erstellen?</a> <a href="%2$s">Zeige alle Ereignisse</a>',

  'Event created at %1$s. <a href="%2$s">Create another?</a> <a href="%3$s">View all Events</a>' =>
  'Das Ereignis wurde um %1$s Uhr erstellt. <a href="%2$s">Möchten Sie ein weiteres erstellen?</a> <a href="%2$s">Zeige alle Ereignisse</a>',

  'Page updated at %1$s. <a href="%2$s">Create another?</a> <a href="%3$s">View all Pages</a>' =>
  'Die Seite wurde um %1$s Uhr aktualisiert. <a href="%2$s">Möchten Sie eine weitere erstellen?</a> <a href="%2$s">Zeige alle Seiten</a>',

  'Page created at %1$s. <a href="%2$s">Create another?</a> <a href="%3$s">View all Pages</a>' =>
  'Die Seite wurde um %1$s Uhr erstellt. <a href="%2$s">Möchten Sie eine weitere erstellen?</a> <a href="%2$s">Zeige alle Seiten</a>',

  'Section updated at %1$s. <a href="%2$s">Create another?</a> <a href="%3$s">View all Sections</a>' =>
  'Der Bereich wurde um %1$s Uhr aktualisiert. <a href="%2$s">Möchten Sie einen weiteren erstellen?</a> <a href="%2$s">Zeige alle Bereiche</a>',

  'Section created at %1$s. <a href="%2$s">Create another?</a> <a href="%3$s">View all Sections</a>' =>
  'Der Bereich wurde um %1$s Uhr erstellt. <a href="%2$s">Möchten Sie einen weiteren erstellen?</a> <a href="%2$s">Zeige alle Bereiche</a>',

  'Utility updated at %1$s. <a href="%2$s">Create another?</a> <a href="%3$s">View all Utilities</a>' =>
  'Der Baustein wurde um %1$s Uhr aktualisiert. <a href="%2$s">Möchten Sie einen weiteren erstellen?</a> <a href="%2$s">Zeige alle Bausteine</a>',

  'Utility created at %1$s. <a href="%2$s">Create another?</a> <a href="%3$s">View all Utilities</a>' =>
  'Der Baustein wurde um %1$s Uhr erstellt. <a href="%2$s">Möchten Sie einen weiteren erstellen?</a> <a href="%2$s">Zeige alle Bausteine</a>',

  'The supplied password was rejected. Make sure it is not empty and that password matches password confirmation.' =>
  'Das verwendete Passwort wurde nicht akzeptiert. Stellen Sie sicher, dass das Passwort nicht leer ist und dass es der Passwort-Benachrichtung entspricht.',

  'The Section you are looking for, <code>%s</code>, could not be found.' =>
  'Der von Ihnen gesuchte Bereich <code>%s</code> konnte nicht gefunden werden.',

  'Entry updated at %1$s. <a href="%2$s">Create another?</a> <a href="%3$s">View all Entries</a>' =>
  'Der Eintrag wurde um %1$s Uhr aktualisiert. <a href="%2$s">Möchten Sie einen weiteren erstellen?</a> <a href="%2$s">Zeige alle Einträge</a>',

  'Entry created at %1$s. <a href="%2$s">Create another?</a> <a href="%3$s">View all Entries</a>' =>
  'Der Eintrag wurde um %1$s Uhr erstellt. <a href="%2$s">Möchten Sie einen weiteren erstellen?</a> <a href="%2$s">Zeige alle Einträge</a>',

  'Add an author' =>
  'Autor hinzufügen',

  'Add a new author' =>
  'Neuen Autor hinzufügen',

  'Author updated at %1$s. <a href="%2$s">Create another?</a> <a href="%3$s">View all Authors</a>' =>
  'Der Autor wurde um %1$s Uhr aktualisiert. <a href="%2$s">Möchten Sie einen weiteren erstellen?</a> <a href="%2$s">Zeige alle Autoren</a>',

  'Author created at %1$s. <a href="%2$s">Create another?</a> <a href="%3$s">View all Authors</a>' =>
  'Der Autor wurde um %1$s Uhr erstellt. <a href="%2$s">Möchten Sie einen weiteren erstellen?</a> <a href="%2$s">Zeige alle Autoren</a>',

  'Could not %1$s %2$s, there was a problem loading the object. Check the driver class exists.' =>
  '%s konnte aufgrund eines Problems beim Laden des Objektes nicht %1$s werden. Überprüfen Sie, ob die Treiberklasse existiert.',

  'Static Options' =>
  'Statische Optionen',

  'Label' =>
  'Bezeichnung',

  'Placement' =>
  'Platzierung',

  'Main content' =>
  'Hauptbereich',

  'Sidebar' =>
  'Seitenleiste',

  'Formatting' =>
  'Formatierung',

);

/*
	Transliterations are used to generate handles of entry fields and filenames.
	They specify which characters (or bunch of characters) have to be changed, and what should be put into their place when entry or file is saved.
	For example:

		'/_and_/' => '+',

	will change every instance of "_and_" into "+", so:

		me_and_family.jpg

	will turn into:

		me+family.jpg

	Please notice slashes at the beginning and end of original text. They are required there.
	You can change them into different character, but that character cannot be used inside original text or has to be escaped by backslash, like this:

		'/original\/path/' => 'new/path',

	You can use full syntax of regular expressions there too. Read more about it on: http://php.net/manual/en/regexp.reference.php

	Transliterations are required only inside translations of Symphony. They are not needed for extensions.
*/
$transliterations = array (
  '/À/' => 'A',
  '/Á/' => 'A',
  '/Â/' => 'A',
  '/Ã/' => 'A',
  '/Ä/' => 'Ae',
  '/Å/' => 'A',
  '/Ā/' => 'A',
  '/Ą/' => 'A',
  '/Ă/' => 'A',
  '/Æ/' => 'Ae',
  '/Ç/' => 'C',
  '/Ć/' => 'C',
  '/Č/' => 'C',
  '/Ĉ/' => 'C',
  '/Ċ/' => 'C',
  '/Ď/' => 'D',
  '/Đ/' => 'D',
  '/Ð/' => 'D',
  '/È/' => 'E',
  '/É/' => 'E',
  '/Ê/' => 'E',
  '/Ë/' => 'E',
  '/Ē/' => 'E',
  '/Ę/' => 'E',
  '/Ě/' => 'E',
  '/Ĕ/' => 'E',
  '/Ė/' => 'E',
  '/Ĝ/' => 'G',
  '/Ğ/' => 'G',
  '/Ġ/' => 'G',
  '/Ģ/' => 'G',
  '/Ĥ/' => 'H',
  '/Ħ/' => 'H',
  '/Ì/' => 'I',
  '/Í/' => 'I',
  '/Î/' => 'I',
  '/Ï/' => 'I',
  '/Ī/' => 'I',
  '/Ĩ/' => 'I',
  '/Ĭ/' => 'I',
  '/Į/' => 'I',
  '/İ/' => 'I',
  '/Ĳ/' => 'Ij',
  '/Ĵ/' => 'J',
  '/Ķ/' => 'K',
  '/Ł/' => 'L',
  '/Ľ/' => 'L',
  '/Ĺ/' => 'L',
  '/Ļ/' => 'L',
  '/Ŀ/' => 'L',
  '/Ñ/' => 'N',
  '/Ń/' => 'N',
  '/Ň/' => 'N',
  '/Ņ/' => 'N',
  '/Ŋ/' => 'N',
  '/Ò/' => 'O',
  '/Ó/' => 'O',
  '/Ô/' => 'O',
  '/Õ/' => 'O',
  '/Ö/' => 'Oe',
  '/Ø/' => 'O',
  '/Ō/' => 'O',
  '/Ő/' => 'O',
  '/Ŏ/' => 'O',
  '/Œ/' => 'Oe',
  '/Ŕ/' => 'R',
  '/Ř/' => 'R',
  '/Ŗ/' => 'R',
  '/Ś/' => 'S',
  '/Š/' => 'S',
  '/Ş/' => 'S',
  '/Ŝ/' => 'S',
  '/Ș/' => 'S',
  '/Ť/' => 'T',
  '/Ţ/' => 'T',
  '/Ŧ/' => 'T',
  '/Ț/' => 'T',
  '/Ù/' => 'U',
  '/Ú/' => 'U',
  '/Û/' => 'U',
  '/Ü/' => 'Ue',
  '/Ū/' => 'U',
  '/Ů/' => 'U',
  '/Ű/' => 'U',
  '/Ŭ/' => 'U',
  '/Ũ/' => 'U',
  '/Ų/' => 'U',
  '/Ŵ/' => 'W',
  '/Ý/' => 'Y',
  '/Ŷ/' => 'Y',
  '/Ÿ/' => 'Y',
  '/Y/' => 'Y',
  '/Ź/' => 'Z',
  '/Ž/' => 'Z',
  '/Ż/' => 'Z',
  '/Þ/' => 'T',
  '/à/' => 'a',
  '/á/' => 'a',
  '/â/' => 'a',
  '/ã/' => 'a',
  '/ä/' => 'ae',
  '/å/' => 'a',
  '/ā/' => 'a',
  '/ą/' => 'a',
  '/ă/' => 'a',
  '/æ/' => 'ae',
  '/ç/' => 'c',
  '/ć/' => 'c',
  '/č/' => 'c',
  '/ĉ/' => 'c',
  '/ċ/' => 'c',
  '/ď/' => 'd',
  '/đ/' => 'd',
  '/ð/' => 'd',
  '/è/' => 'e',
  '/é/' => 'e',
  '/ê/' => 'e',
  '/ë/' => 'e',
  '/ē/' => 'e',
  '/ę/' => 'e',
  '/ě/' => 'e',
  '/ĕ/' => 'e',
  '/ė/' => 'e',
  '/ƒ/' => 'f',
  '/ĝ/' => 'g',
  '/ğ/' => 'g',
  '/ġ/' => 'g',
  '/ģ/' => 'g',
  '/ĥ/' => 'h',
  '/ħ/' => 'h',
  '/ì/' => 'i',
  '/í/' => 'i',
  '/î/' => 'i',
  '/ï/' => 'i',
  '/ī/' => 'i',
  '/ĩ/' => 'i',
  '/ĭ/' => 'i',
  '/į/' => 'i',
  '/ı/' => 'i',
  '/ĳ/' => 'ij',
  '/ĵ/' => 'j',
  '/ķ/' => 'k',
  '/ĸ/' => 'k',
  '/ł/' => 'l',
  '/ľ/' => 'l',
  '/ĺ/' => 'l',
  '/ļ/' => 'l',
  '/ŀ/' => 'l',
  '/ñ/' => 'n',
  '/ń/' => 'n',
  '/ň/' => 'n',
  '/ņ/' => 'n',
  '/ŉ/' => 'n',
  '/ŋ/' => 'n',
  '/ò/' => 'o',
  '/ó/' => 'o',
  '/ô/' => 'o',
  '/õ/' => 'o',
  '/ö/' => 'oe',
  '/ø/' => 'o',
  '/ō/' => 'o',
  '/ő/' => 'o',
  '/ŏ/' => 'o',
  '/œ/' => 'oe',
  '/ŕ/' => 'r',
  '/ř/' => 'r',
  '/ŗ/' => 'r',
  '/ú/' => 'u',
  '/û/' => 'u',
  '/ü/' => 'ue',
  '/ū/' => 'u',
  '/ů/' => 'u',
  '/ű/' => 'u',
  '/ŭ/' => 'u',
  '/ũ/' => 'u',
  '/ų/' => 'u',
  '/ŵ/' => 'w',
  '/ý/' => 'y',
  '/ÿ/' => 'y',
  '/ŷ/' => 'y',
  '/y/' => 'y',
  '/ž/' => 'z',
  '/ż/' => 'z',
  '/ź/' => 'z',
  '/þ/' => 't',
  '/ß/' => 'ss',
  '/ſ/' => 'ss',
  '/\\(/' => NULL,
  '/\\)/' => NULL,
  '/,/' => NULL,
  '/–/' => '-',
  '/－/' => '-',
  '/„/' => '"',
  '/“/' => '"',
  '/”/' => '"',
  '/—/' => '-',
  '/^&(?!&)$/' => 'und',
  '/^&(?!&)/' => 'und-',
  '/&(?!&)&/' => '-und',
  '/&(?!&)/' => '-und-',
);

?>