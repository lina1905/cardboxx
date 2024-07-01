<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This is the language file for the Card Box activity module.
 *
 * @package   mod_cardboxx
 * @copyright 2019 RWTH Aachen (see README.md)
 * @author    Anna Heynkes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

// Meta Informationen.
$string['cardboxx'] = 'Kartenbox';
$string['activityname'] = 'Kartenbox Aktivität';
$string['modulename'] = 'Kartenbox';
$string['modulename_help'] = '<p>Diese Aktivität ermöglicht es Ihnen, Karteikarten für Vokabeln, Fachbegriffe, Formeln usw. zu erstellen, die Sie sich merken möchten. Sie können mit den Karten lernen, wie Sie es mit einer Kartenbox tun würden.</p><p>Karten können von jedem Teilnehmer erstellt werden, werden aber nur zum Üben verwendet, wenn ein Lehrer sie akzeptiert hat.</p>';
$string['pluginname'] = 'Kartenbox';
$string['modulenameplural'] = 'Kartenboxen';
$string['cardboxxname'] = 'Name dieser Kartenbox';
$string['pluginadministration'] = 'Flashcards Verwaltung';
$string['setting_autocorrection'] = 'Autokorrektur erlauben';
$string['setting_autocorrection_help'] = 'Die Autokorrektur funktioniert nur für normalen Text. Wenn von den Schülern erwartet wird, dass sie Formelantworten geben, sollten Sie die Autokorrektur deaktivieren.';
$string['setting_autocorrection_label'] = '<font color="red">nur für Text geeignet</font>'; // Mit Vorsicht aktivieren.
$string['setting_enablenotifications'] = 'Benachrichtigungen erlauben';
$string['setting_enablenotifications_help'] = 'Die Schüler erhalten Benachrichtigungen, wenn Karten bearbeitet wurden oder es Zeit ist, wieder zu üben.';
$string['setting_enablenotifications_label'] = 'Senden von Benachrichtigungen an Schüler aktivieren';
$string['necessaryanswers_activity'] = 'Standardeinstellungen für "Wie viele Antworten sind notwendig?"';
$string['necessaryanswers_activity_help'] = 'Legen Sie den Standardwert für "Wie viele Antworten sind notwendig?" im Karten-Erstellungsformular fest.';
$string['necessaryanswers_activity_locked'] = 'Erlauben, die Anzahl der notwendigen Antworten nachträglich zu ändern?';
$string['necessaryanswers_activity_locked_help'] = 'Wenn "Ja" ausgewählt ist, ist es möglich, die Anzahl der erforderlichen Antworten beim Erstellen oder Bearbeiten einer Karte zu ändern.';
$string['casesensitive'] = 'Groß-/Kleinschreibung';
$string['casesensitive_help'] = 'Gibt an, ob bei der Übung mit automatischer Kontrolle auch Einträge, die sich von der richtigen Antwort nur in Bezug auf Groß-/Kleinschreibung unterscheiden, als richtig gezählt werden.';
$string['numberofcardssetting'] = 'Anzahl der Karten zum Üben';
$string['numberofcardssetting_help'] = 'Gibt an, wie viele Karten die Schüler pro Übungseinheit lernen sollen. Wenn "Schüler entscheiden" ausgewählt ist, haben sie freie Wahl.';
$string['studentschoose'] = 'Schüler wählen';
$string['messageprovider:changenotification'] = 'Benachrichtigen, wenn eine Karteikarte bearbeitet wurde';
$string['changenotification:subject'] = 'Änderungsbenachrichtigung';
$string['changenotification:message'] = 'Eine Karteikarte wurde in Ihrer Kartenbox bearbeitet. Hier ist die Karte in ihrer aktuellen Form.';

// Erinnerungen.
$string['send_practice_reminders'] = 'E-Mail-Erinnerungen an die Kursteilnehmer senden';
$string['messageprovider:memo'] = 'Erinnerungen zum Üben mit Kartenbox';
$string['remindersubject'] = 'Übungs-Erinnerung';
$string['remindergreeting'] = 'Hallo {$a}, ';
$string['remindermessagebody'] = 'bitte denken Sie daran, regelmäßig mit Ihrer Kartenbox zu lernen.';
$string['reminderfooting'] = 'Diese Erinnerung wurde automatisch von Ihrer Kartenbox "{$a->cardboxxname}" im Kurs "{$a->coursename}" gesendet.';

// Tab-Navigation.
$string['addflashcard'] = 'Karten anlegen';
$string['practice'] = 'Üben';
$string['statistics'] = 'Fortschritt';
$string['overview'] = 'Übersicht';
$string['review'] = 'Überprüfen';
$string['massimport'] = 'Karten importieren';
$string['edittopic'] = 'Themen verwalten';

// Unterseitentitel.
$string['titleforaddflashcard'] = 'Neue Karte';
$string['titleforpractice'] = 'Üben';
$string['titleforreview'] = 'Karte überprüfen';
$string['titleforcardedit'] = 'Karte bearbeiten';
$string['intro:overview'] = 'Diese Übersicht zeigt alle Karten an, die genehmigt wurden.';

// Formularelemente zum Erstellen einer neuen Karte.
$string['choosetopic'] = 'Thema';
$string['reviewtopic'] = 'THEMA: ';
$string['notopic'] = 'nicht zugewiesen';
$string['addnewtopic'] = 'ein Thema erstellen';
$string['entertopic'] = 'ein Thema erstellen';
$string['enterquestion'] = 'Frage';
$string['entercontextquestion'] = 'Zusätzliche Informationen zu dieser Frage';
$string['addcontext'] = 'Kontext anzeigen';
$string['removecontext'] = 'Kontext ausblenden';
$string['entercontextanswer'] = 'Zusätzliche Informationen zur Antwort';
$string['necessaryanswers_card'] = 'Wie viele Antworten sind notwendig?';
$string['necessaryanswers_all'] = 'alle';
$string['necessaryanswers_one'] = 'eine';
$string['addimage'] = 'Bildoptionen anzeigen';
$string['removeimage'] = 'Bildoptionen ausblenden';
$string['image'] = 'Fragebild';
$string['imagedescription'] = 'Beschreiben Sie dieses Bild für jemanden, der es nicht sehen kann (empfohlen)';
$string['imgdescriptionnecessary_label'] = 'Dieses Bild ist nur dekorativ';
$string['addsound'] = 'Audiooptionen anzeigen';
$string['removesound'] = 'Audiooptionen ausblenden';
$string['sound'] = 'Fragesound';
$string['answerimage'] = 'Antwortbild';
$string['answersound'] = 'Antwortsound';
$string['enteranswer'] = 'Lösungstext';
$string['answer_repeat'] = 'Eine weitere Lösung hinzufügen';
$string['autocorrectlocked'] = 'Automatische Überprüfung deaktivieren';
$string['autocorrecticon'] = 'Nur Selbstüberprüfung';
$string['autocorrecticon_help'] = 'Die Antwort kann beim Üben im Modus "Automatische Überprüfung" nicht eingegeben werden. Im Modus "Automatische Überprüfung" wird die Lernkarte dann immer noch angezeigt, aber nur als Selbstüberprüfung.';
$string['autocorrectlocked_help'] = 'Aktivieren Sie dieses Kontrollkästchen, wenn die Antwort der Lernkarte beim Üben im Modus "Automatische Überprüfung" nicht eingegeben werden soll. Im Modus "Automatische Überprüfung" wird die Lernkarte dann immer noch angezeigt, aber nur als Selbstüberprüfung. Diese Option ist besonders nützlich für Lernkarten, deren Antworten nicht für die manuelle Eingabe geeignet sind (z.B. Definitionen), aber dennoch zusammen mit anderen Lernkarten geübt werden sollen, deren Antworten manuell eingegeben werden.';
$string['answer_repeat_help'] = 'Wenn Sie mehrere Lösungen haben, verwenden Sie bitte für jede Antwort ein separates Lösungsfeld.<br>
                                Ein weiteres Lösungsfeld kann durch die Schaltfläche "Eine weitere Lösung hinzufügen" hinzugefügt werden.<br>
                                Um festzulegen, ob die Schüler alle Antworten oder nur eine (im Falle von alternativen Antworten) kennen müssen, verwenden Sie bitte das Dropdown-Menü unten.';

$string['addanswer'] = 'Eine weitere Lösung hinzufügen';
$string['autocorrectlocked'] = 'Automatische Überprüfung deaktivieren';
$string['savecard'] = 'Speichern';
$string['saveandaccept'] = 'Speichern';

// Erfolgsbenachrichtigungen.
$string['success:addnewcard'] = 'Die Karte wurde erstellt und wartet auf Genehmigung.';
$string['success:addandapprovenewcard'] = 'Die Karte wurde erstellt.';
$string['success:approve'] = 'Die Karte wurde genehmigt und ist jetzt zur Verwendung freigegeben.';
$string['success:edit'] = 'Die Karte wurde bearbeitet.';
$string['success:reject'] = 'Die Karte wurde gelöscht.';

// Error notifications.
$string['error:updateafterreview'] = 'Aktualisierung fehlgeschlagen.';
$string['error:createcard'] = 'Die Karte wurde nicht erstellt, da entweder eine Frage und/oder Antwort fehlt oder wenn Sie ein Bild hochgeladen haben, fehlt die Bildbeschreibung.';


// Import cards.
$string['examplesinglecsv'] = 'Beispieltextdatei für den Import von Karten';
$string['examplesinglecsv_help'] = 'Beispieltextdatei für Karten mit einzelnen Antworten';
$string['examplemulticsv'] = 'Beispieltextdatei für Karten mit mehreren Antworten';
$string['examplemulticsv_help'] = 'Beispieltextdatei für Karten mit mehreren Antworten';
$string['cancelimport'] = 'Import wurde abgebrochen';
$string['importpreview'] = 'Vorschau der Kartenimportierung';
$string['importsuccess'] = '{$a} Karten erfolgreich importiert';
$string['allowedcolumns'] = '<br><p>Erlaubte Spaltennamen sind:</p>';
$string['ques'] = 'Spaltenname für Frage';
$string['ans'] = 'Spaltenname für Antwort';
$string['qcontext'] = 'Spaltenname für Fragekontext';
$string['acontext'] = 'Spaltenname für Antwortkontext';
$string['topic'] = 'Spaltenname für Thema';
$string['acdisable'] = 'Spaltenname zum Deaktivieren der automatischen Überprüfung für eine Karte. Ja = 1; Nein = 0.';

// Info notifications.
$string['info:statisticspage'] = 'Hier siehst du deine Fortschritte. Du kannst sehen in welchen Level deine Karten sind und wie gut deine Lerneinheiten waren.';
$string['info:nocardsavailableforreview'] = 'Es gibt derzeit keine neuen Karten zur Überprüfung.';
$string['info:waslastcardforreview'] = 'Dies war die letzte Karte zur Überprüfung.';
$string['info:nocardsavailableforoverview'] = 'Es gibt keine Karten in dieser Kartenbox.';
$string['info:nocardsavailable'] = 'Es gibt derzeit keine Karten in dieser Karteikartenbox.';
$string['help:nocardsavailable'] = 'Leere Karteikartenbox';
$string['help:nocardsavailable_help'] = 'Möglicher Grund: Es wurden keine Karten erstellt.';
$string['info:nocardsavailableforpractice'] = 'Es gibt keine Karten, die bereit zum Üben sind.';
$string['help:nocardsavailableforpractice'] = 'Keine Karten';
$string['help:nocardsavailableforpractice_help'] = 'Sie haben jede derzeit verfügbare Karte 5 Mal über einen Zeitraum von mindestens 1 Monat korrekt beantwortet. Diese Karten gelten als gemeistert und werden nicht mehr wiederholt.';
$string['info:nocardsdueforpractice'] = 'Keine Ihrer Karten ist derzeit zur Wiederholung fällig.';
$string['info:enrolledstudentsthreshold_manager'] = 'Es müssen mindestens {$a} Studenten in diesem Kurs eingeschrieben sein, damit die wöchentlichen Übungsstatistiken angezeigt werden.';
$string['info:enrolledstudentsthreshold_student'] = 'Der durchschnittliche Fortschritt der Studenten wird nur angezeigt, wenn mindestens {$a} Studenten im Kurs eingeschrieben sind.';
$string['help:nocardsdueforpractice'] = 'Keine fälligen Karten';
$string['help:nocardsdueforpractice_help'] = 'Neue Karten sind sofort fällig. Für jede andere Karte entscheidet das Deck:<ol><li>Deck: täglich</li><li>Deck: nach 3 Tagen</li><li>Deck: nach 7 Tagen</li><li>Deck: nach 16 Tagen</li><li>Deck: nach 34 Tagen</li></ol>';
$string['help:whenarecardsdue'] = 'Erklärung Level';
$string['help:whenarecardsdue_help'] = 'Neue Karten müssen sofort wiederholt werden. Sonst entscheidet das Level:<ol><li>Level: Wiederholung täglich</li><li>Level: Wiederholung nach 2 Tagen</li><li>Level: Wiederholung nach 4 Tagen</li><li>Level: Wiederholung nach 8 Tagen</li><li>Level: Wiederholung nach 16 Tagen</li></ol>';
$string['help:practiceanyway_help'] = 'Es sind derzeit keine Karten fällig. Wenn du trotzdem übst, bleiben deine Karten im aktuellen Level und rücken nicht vor. Karten sind fällig gemäß ihrem Level: <ol><li>Level: Wiederholung täglich</li><li>Level: Wiederholung nach 2 Tagen</li><li>Level: Wiederholung nach 4 Tagen</li><li>Level: Wiederholung nach 8 Tagen</li><li>Level: Wiederholung nach 16 Tagen</li></ol>';
$string['help:practiceanyway'] = 'Trotzdem üben';

// Title and form elements for choosing the settings for a new practice session.
$string['titleforchoosesettings'] = 'Jetzt Üben?';
$string['choosecorrectionmode'] = 'Übungsmodus';
$string['selfcorrection'] = 'Selbstkontrolle';
$string['autocorrection'] = 'Automatische Überprüfung';
$string['weightopic'] = 'Fokus';
$string['notopicpreferred'] = 'keine Präferenz';
$string['practiceall'] = 'Alle Karten üben';
$string['practiceall_help'] = 'Diese Karten rücken nicht in das nächste Fach vor, wenn sie korrekt beantwortet werden. So können Sie so oft üben, wie Sie möchten, ohne das Risiko einzugehen, dass Karten nach nur wenigen Tagen für immer aus der Kartenbox verschwinden.';
$string['onlyonetopic'] = 'Thema';
$string['maxnumbercardspractice'] = 'Max. Anzahl von Karten';
$string['undefined'] = 'Keine Begrenzung';

$string['beginpractice'] = 'Starten';
$string['applysettings'] = 'Anwenden';
$string['cancel'] = 'Abbrechen';

// Practice mode: Buttons.
$string['options'] = 'Trotzdem Üben';
$string['optionsone'] = 'Trotzdem Üben';
$string['optionstwo'] = 'Jetzt Üben';
$string['endpractice'] = 'Übung beenden';
$string['chartpractice'] = 'Heute zu<br> wiederholen:';
$string['chartpracticedone'] = 'Du hast alle<br> Karten für<br> heute gelernt';
$string['totalcardsinbox'] = 'Gesamtzahl an Karten: ';

$string['dontknow'] = "Ich weiß es nicht";
$string['checkanswer'] = 'Überprüfen';
$string['submitanswer'] = 'Antworten';
$string['markascorrect'] = 'Gewusst';
$string['markasincorrect'] = 'Nicht gewusst';
$string['override'] = 'Überschreiben';
$string['override_iscorrect'] = 'Nein, ich hatte recht!';
$string['override_isincorrect'] = 'Nein, ich lag falsch.';
$string['proceed'] = 'Weiter';
$string['suggestanswer_label'] = 'Bitte schlagen Sie eine neue Lösung vor';
$string['suggestanswer'] = 'Antwort vorschlagen';
$string['suggestanswer_send'] = 'Antwort senden';
$string['cardsleft'] = 'Verbleibende Karten:';

$string['solution'] = 'Lösung';
$string['yoursolution'] = 'Ihre Antwort';

// Practice mode: Feedback.
$string['feedback:correctandcomplete'] = 'Gut gemacht!';
$string['feedback:incomplete'] = 'Antworten fehlen!';
$string['feedback:correctbutincomplete'] = 'Es fehlen {$a} Antworten.';
$string['feedback:incorrectandpossiblyincomplete'] = 'Falsch!';
$string['feedback:notknown'] = 'Keine Antwort gegeben!';

$string['sessioncompleted'] = 'Fertig! :-)';
$string['titleprogresschart'] = 'Ergebnisse';
$string['right'] = 'Gewusst';
$string['wrong'] = 'Nicht gewusst';
$string['titleoverviewchart'] = 'Übersicht Level';
$string['new'] = 'neu';
$string['known'] = 'gelernt';
$string['flashcards'] = 'Karten';
$string['flashcardsdue'] = 'fällig';
$string['flashcardsnotdue'] = 'noch nicht fällig';
$string['box'] = 'Box';
$string['titelpracticechartone'] = 'Lerneinheit geschafft! Du hast';
$string['titelpracticecharttwo'] = '% der Karten gewusst.';

$string['titleperformancechart'] = 'Vergangene Übungssitzungen';
$string['performance'] = '% korrekt';

$string['titlenumberofcards'] = 'Anzahl der Karten pro Sitzung';
$string['numberofcards'] = 'Anzahl';
$string['numberofcardsavg'] = 'Durchschnitt';
$string['numberofcardsmin'] = 'Minimum';
$string['numberofcardsmax'] = 'Maximum';

$string['titledurationofasession'] = 'Dauer einer Sitzung';
$string['duration'] = 'Dauer (min)';
$string['durationavg'] = 'Durchschnitt';
$string['durationmin'] = 'Minimum';
$string['durationmax'] = 'Maximum';


// Review.
$string['approve'] = 'Genehmigen';
$string['reject'] = 'Ablehnen';
$string['edit'] = 'Bearbeiten';
$string['skip'] = 'Überspringen';
$string['countcardapprove'] = '{&a} Karten wurden genehmigt und sind bereit zum Üben';
$string['countcardreject'] = '{&a} Karten wurden abgelehnt';
$string['rejectcard'] = 'Karte ablehnen';
$string['rejectcardinfo'] = 'Möchten Sie die ausgewählten {$a} Karten ablehnen? Diese Karten werden gelöscht und können nicht wiederhergestellt werden.';

$string['allanswersnecessary'] = "Alle";
$string['oneanswersnecessary'] = "Eine";
$string['allanswersnecessary_help'] = "alle Antworten notwendig";
$string['oneanswersnecessary_help'] = "eine Antwort notwendig";

// Statistics.
$string['strftimedate'] = '%d. %B %Y';
$string['strftimedatetime'] = '%d. %b %Y, %H:%M';
$string['strftimedateshortmonthabbr'] = '%d %b';


$string['barchartxaxislabel'] = 'Deck';
$string['barchartyaxislabel'] = 'Kartenanzahl';
$string['barchartstatistic1'] = 'Anzahl der Karten pro Deck für alle Schüler';
$string['linegraphxaxislabel'] = 'Datum';
$string['linegraphyaxislabel_performance'] = '% bekannt';
$string['linegraphyaxislabel_numbercards'] = 'Anzahl der Karten';
$string['linegraphyaxislabel_duration'] = 'Dauer (min)';
$string['linegraphtooltiplabel_below_threshold'] = 'keine Statistiken, weil <{$a} Benutzer in dieser Woche geübt haben';
$string['lastpractise'] = 'zuletzt geübt';
$string['nopractise'] = 'noch nicht geübt';
$string['newcard'] = 'neue Karten';
$string['knowncard'] = 'gelernte Karten';
$string['averagestudentscompare'] = 'Durchschnitt aller Schüler';
$string['absolutenumberofcards'] = 'Absolute Anzahl der Karten';

$string['yes'] = 'Ja';
$string['no'] = 'Nein';
$string['cancel'] = 'Abbrechen';
$string['deletecard'] = 'Karte löschen?';
$string['deletecardinfo'] = 'Die Karte und der Fortschritt dieser Karte werden für alle Benutzer gelöscht.';
$string['delete'] = 'Löschen';


$string['topicfilter'] = 'Thema ';
$string['deckfilter'] = 'Level:';
$string['noselection'] = 'alle';
$string['createddate'] = 'Erstellungsdatum';
$string['alphabetical'] = 'Alphabetisch';
$string['sorting'] = 'Sortieren';
$string['descending'] = 'absteigend';
$string['ascending'] = 'aufsteigend';

$string['card'] = 'Frage/Antwort:';
$string['cardposition'] = 'Deck:';
$string['cardposition_help'] = 'Zeigt, in welchem Deck sich diese Karte befindet. Je höher die Zahl, desto besser wurde die Karte bereits gelernt. Neue Karten sind noch nicht in einer Box. Nach Box 5 werden Karten als "gelernt" angesehen und nicht mehr geübt.';

// Overview Tab.
$string['student:deckdescription'] = 'Diese Karte liegt im Deck {$a}';
$string['manager:deckdescription'] = 'Im Durchschnitt liegt diese Karte im Deck {$a} bei allen Schülern';
$string['manager:repeatdesc'] = 'Diese Karte wurde im Durchschnitt nach {$a} Wiederholungen von den Schülern gelernt';
$string['student:repeatdesc'] = 'Diese Karte wurde nach {$a} Wiederholungen gelernt';

// Edit topics Tab.
$string['deletetopic'] = 'Thema löschen';
$string['deletetopicinfo'] = 'Möchten Sie das ausgewählte Thema {$a} löschen? Für Karten, die diesem Thema zugeordnet sind, wird das Thema auf "nicht zugeordnet" gesetzt.';
$string['createtopic'] = 'Hinzufügen';
$string['existingtopics'] = 'bereits vorhandene Themen';
$string['notopics'] = 'es gibt noch keine Themen';

// Settings.
$string['statistics_heading'] = 'Statistiken';
$string['weekly_users_practice_threshold'] = 'Schwelle Übende pro Woche';
$string['weekly_users_practice_threshold_desc'] = 'Wie viele Benutzer müssen pro Woche üben, damit Manager Statistiken für diese Woche sehen können.';
$string['weekly_enrolled_students_threshold'] = 'Schwelle eingeschriebene Schüler';
$string['weekly_enrolled_students_threshold_desc'] = 'Wie viele Schüler müssen in den Kurs eingeschrieben sein, damit wöchentliche Statistiken für Manager angezeigt werden.';
$string['qmissing'] = 'Frage fehlt.';
$string['qfieldmissing'] = 'Fragefeld fehlt.';
$string['amissing'] = 'Antwort fehlt.';
$string['afieldmissing'] = 'Antwortfeld fehlt.';
$string['successmsg'] = ' Karte(n) wurden erfolgreich importiert.';
$string['errormsg'] = 'Die folgenden Zeilen konnten nicht in Karten importiert werden';
$string['status'] = 'Status';
$string['continue'] = 'Fortsetzen';
$string['unmatchedanswers'] = 'CSV-Datei erfordert {$a->csvschema} Antworten; nur {$a->actual} gegeben.';
$string['emptyimportfile'] = 'Nichts zu importieren - CSV-Datei hat keine Zeilen.';

$string['continuepracticebutton'] = 'Weiter Üben';
