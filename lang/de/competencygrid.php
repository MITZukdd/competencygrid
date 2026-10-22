<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     mod_competencygrid
 * @category    string
 * @copyright   2026 MITZ <mitz.software@ukdd.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['add:levelinstance:defaulttext'] = 'Niveaubeschreibung : Bitte anpassen!';
$string['add:skill'] = 'Kompetenz hinzufügen';
$string['add:skill:defaulttext'] = 'Neue Kompetenz : Bitte anpassen!';
$string['add:subskill'] = 'Teilkompetenz hinzufügen';
$string['add:subskill:defaulttext'] = 'Neue Teilkompetenz : Bitte anpassen!';

$string['alternatefittab'] = 'Alternativer Titel für den Fit-Tab';

$string['chart:bar'] = 'Balkendiagramm';
$string['chart:historic_bar'] = 'Gestapeltes Balkendiagramm (historisch)';
$string['chart:historic_line'] = 'Liniendiagramm (historisch)';
$string['chart:polar'] = 'Polardiagramm';
$string['chart:radar'] = 'Netzdiagramm';
$string['chart:sum'] = 'Summe';

$string['cleanup_deleted_userinput'] = 'Benutzereingaben von Nutzern bereinigen, die in der Nutzertabelle (user) als gelöscht markiert sind (deleted=1)';
$string['cleanup_old_userinput'] = 'Alte Benutzerdaten bereinigen, die älter als {$a} Monate sind';

$string['cleanup_retention'] = 'Aufbewahrungszeitraum für Bereinigung (Monate)';
$string['cleanup_retention_desc'] = 'Benutzereingaben, die älter als diese Anzahl von Monaten sind, werden durch die geplante Aufgabe gelöscht.';

$string['competencygrid:addinstance'] = 'Ein neues Kompetenzraster zu einem Kurs hinzufügen';
$string['competencygrid:edit'] = 'Ein Kompetenzraster bearbeiten';
$string['competencygrid:view'] = 'Kompetenzraster anzeigen';

$string['competencygrid_help'] = 'Die Kompetenzraster Aktivität ermöglicht es Lehrkräften, ein Raster aus Kompetenzen und Niveaustufen zu erstellen, in dem Studierende eine Selbsteinschätzung der passenden Stufe für jede Kompetenz vornehmen können. Dies kann von Studierenden genutzt werden, um ihren Fortschritt während des Studiums zu verfolgen und Bereiche zu identifizieren, in denen sie möglicherweise zusätzliche Unterstützung benötigen.';
$string['competencygridfieldset'] = 'Benutzerdefinierte Eigenschaften';
$string['competencygridname'] = 'Name des Kompetenzrasters';
$string['competencygridname_help'] = 'Dies ist der Name der Kompetenzraster-Instanz. Er wird auf der Kursseite und in der Navigation angezeigt.';
$string['competencygridsettings'] = 'Einstellungen für das Kompetenzraster';
$string['dataerror'] = 'Es ist ein Fehler aufgetreten, Ihre Daten konnten nicht gespeichert werden.';
$string['datapersisted'] = 'Ihre Daten wurden erfolgreich gespeichert.';

$string['delete:skill'] = 'Diese Kompetenz löschen';
$string['delete:skilltext'] = 'Sind Sie sicher? Diese Aktion entfernt diese Kompetenz und alle zugehörigen Teilkompetenzen dauerhaft aus der Datenbank. Lernende sehen diese Kompetenz nicht mehr auf der Analyse-Seite und auf der Seite Fit für die Prüfung. Wenn Sie sich unsicher sind, verwenden Sie bitte stattdessen die Schaltfläche Ausblenden, um die Kompetenz für Lernende während des Ausfüllens des Kompetenzrasters unsichtbar zu machen.';
$string['delete:subskill'] = 'Diese Teilkompetenz löschen';
$string['delete:subskilltext'] = 'Sind Sie sicher? Diese Aktion entfernt diese Teilkompetenz dauerhaft aus der Datenbank. Lernende sehen diese Teilkompetenz nicht mehr auf der Analyse-Seite und auf der Seite „Fit für die Prüfung". Wenn Sie sich unsicher sind, verwenden Sie bitte stattdessen die Schaltfläche „Ausblenden", um die Teilkompetenz für Lernende während des Ausfüllens des Kompetenzrasters unsichtbar zu machen.';

$string['error:missingn0option'] = 'Sie müssen eine Option auswählen, wie mit der Niveaustufe 0 umgegangen werden soll';
$string['error:missingtemplate'] = 'Eine Vorlagendatei für den Kompetenzraster ist erforderlich.';
$string['error:stringtoolong'] = '{$a->element} {$a->index} ist zu lang. Die zulässige Länge beträgt < {$a->allowed} Zeichen.';
$string['error:sufficiencylevel'] = 'Die Ausreichend-Schwelle darf nicht größer als die maximale Anzahl an Niveaus sein';
$string['error:templateinvalid'] = 'Die bereitgestellte CSV-Datei ist ungültig. Sie benötigt mindestens drei Spalten (Kompetenz, Teilkompetenz und ein Niveau).';
$string['error:toomanylevels'] = 'Ist das ein Scherz? Warum sollten Sie 10000 verschiedene Niveaus benötigen?';

$string['intro:analysis'] = 'Einleitungstext der Analyse-Seite';
$string['intro:chart:default'] = 'Standard-Diagrammtyp';
$string['intro:chart:defaulthistoric'] = 'Standard-Diagrammtyp (historisch)';
$string['intro:chart:options'] = 'Diagrammoptionen';

$string['intro:coursereferences'] = '<p>Hier können Sie Kompetenzen mit Moodle-Kursen verknüpfen. Wählen Sie beliebig viele davon aus der Dropdown-Liste aus oder nutzen Sie die Suche. ' .
        'Wenn Sie zufrieden sind, klicken Sie auf die Schaltfläche „Speichern".</p>' .
        '<p>Die Kurse erscheinen dann beim Ausfüllen des Rasters und bilden die Grundlage für die Kursvorschläge der Fit-Seite.</p>';
$string['intro:defaultanalysisintro'] = 'Hier siehst du eine Auswertung deiner aktuellen und früheren Selbsteinschätzungen.';
$string['intro:defaultfitinsufficient'] = 'Ihnen fehlen noch einige Kompetenzen, die für die Prüfung erforderlich sind. <br>
       Sehen Sie sich die Übersicht an, um zu erkennen, welche Kompetenzen Sie verbessern müssen, und besuchen Sie erneut die Kurse zu diesen Kompetenzen. <br>
       Basierend auf Ihrem Profil sind dies die Kurse, die Sie möglicherweise erneut besuchen sollten:';
$string['intro:defaultfitintro'] = '<h2>Fit für die Prüfung?</h2><p>Bist du fit für die Prüfung? Sieh dir die Übersicht unten an und prüfe, welche Kurse du erneut besuchen solltest.</p>';
$string['intro:defaultfitsufficient'] = 'Gute Arbeit! Du bist bestens auf die Prüfung vorbereitet.';
$string['intro:defaultintro'] = 'Hier kannst du deine Selbsteinschätzung vornehmen. ' .
        'Wähle einfach die Kompetenz und Teilkompetenz aus, die du bewerten möchten, und wähle die am besten passende Stufe. ' .
        'Wenn du mit deiner Einschätzung fertig bist, klicke einfach auf die Schaltfläche „Absenden". ' .
        'Es ist nicht erforderlich, jede Teilkompetenz jedes Mal zu aktualisieren. Aktualisiere einfach diejenigen, die sich geändert haben. ';
$string['intro:fit'] = 'Einleitungstext der Seite „Fit für die Prüfung"';
$string['intro:textoptions'] = 'Textoptionen';

$string['modulename'] = 'Kompetenzraster';
$string['modulename_help'] = '#### Hauptfunktionen
* Lernende können sich schnell selbst einschätzen, da sie nur Teilkompetenzen aktualisieren müssen, die sich für sie geändert haben
* Kompetenz-/Teilkompetenzbeschreibungen einfach bearbeiten und aktualisieren
* Aktuelle und historische Daten in reaktiven Diagrammen anzeigen
* Parameter anpassen, z. B. der den Nutzern angezeigte Text, Standarddiagramme und wann eine Kompetenz als ausreichend gilt

#### Einsatzmöglichkeiten
* Langfristige Lernprozesse begleiten
* Vorbereitung auf Prüfungen mit sofortigem Feedback, welche Kurse Lernende erneut besuchen sollten';

$string['modulename_link'] = 'https://github.com/MITZukdd/competencygrid';
$string['modulename_summary'] = 'Erstellen Sie ein Kompetenzraster, welches Lernenden eine geführte Selbsteinschätzung ermöglicht.';
$string['modulename_tip'] = 'Eine exemplarische Vorlagendatei finden Sie auf unserem GitHub : https://github.com/MITZukdd/competencygrid/blob/main/docs/Template.csv';
$string['modulenameplural'] = 'Kompetenzraster';

$string['niveau_0_custom_text'] = 'Benutzerdefinierter Text für Niveau 0';
$string['niveau_0_custom_text_content'] = 'Diese Kompetenz habe ich in meiner aktuellen Lernphase noch nicht erlernt';
$string['niveau_0_label'] = 'Niveau 0 Optionen';
$string['niveau_0_option1'] = 'Niveau 0 (oder Ähnliches) ist bereits in der CSV-Datei enthalten';
$string['niveau_0_option2'] = 'Niveau 0 ist nicht in der CSV-Datei enthalten.<br> Fügt ein Niveau 0 mit benutzerdefiniertem Text hinzu';
$string['niveau_0_option3'] = 'Niveau 0 ist nicht in der CSV-Datei enthalten.<br> Fügt ein Niveau 0 ohne Text hinzu';
$string['pluginadministration'] = 'Kompetenzraster-Verwaltung';
$string['pluginname'] = 'Kompetenzraster';

$string['privacy:metadata:competencygrid_userinput'] = 'Informationen über Benutzereingaben zu Kompetenzrastern. Dies umfasst die Kompetenz, Teilkompetenz und Stufe, die ein Nutzer erfasst hat, sowie den Zeitpunkt der Erstellung des Eintrags.';
$string['privacy:metadata:competencygrid_userinput:gridid'] = 'Die ID des Kompetenzrasters, an dem der Nutzer gearbeitet hat.';
$string['privacy:metadata:competencygrid_userinput:levelniveau'] = 'Die vom Nutzer eingegebene Stufe.';
$string['privacy:metadata:competencygrid_userinput:skillid'] = 'Die Kompetenz, die der Eingabe zugeordnet war.';
$string['privacy:metadata:competencygrid_userinput:subskillid'] = 'Die Teilkompetenz, die der Eingabe zugeordnet war.';
$string['privacy:metadata:competencygrid_userinput:timecreated'] = 'Der Zeitpunkt, an dem der Nutzer die Eingabe vorgenommen hat';
$string['privacy:metadata:competencygrid_userinput:userid'] = 'Die ID des Nutzers, der die Eingabe erstellt hat';

$string['progress'] = 'Fortschritt';

$string['showfit'] = 'Fit-Tab anzeigen';
$string['showfit_help'] = 'Den Tab „Fit für die Prüfung" anzeigen oder ausblenden';
$string['skill'] = 'Kompetenz';
$string['submitall_hint'] = 'Um alle Eingaben auf einmal abzusenden, scrollen Sie ganz nach unten und wählen Sie „Alle absenden"';
$string['sufficiencylevel'] = 'Ausreichend-Schwelle';
$string['sufficiencylevel_help'] = 'Wird verwendet, um zu bestimmen, ob ein Lernender ausreichend vorbereitet ist.';
$string['tab:analysis'] = 'Analyse';
$string['tab:courseedit'] = 'Kursverweise bearbeiten';
$string['tab:fit'] = 'Fit für die Prüfung?';
$string['templatefile'] = 'Vorlagendatei';
$string['templatefile_help'] = 'Laden Sie eine Vorlagendatei im .csv-Format hoch, die die Struktur des Kompetenzrasters definiert. Die Datei sollte die folgenden Spalten enthalten: Kompetenz, Teilkompetenz, Stufenbeschreibungen. Jede Zeile repräsentiert eine Teilkompetenz und die zugehörigen Stufenbeschreibungen für eine bestimmte Kompetenz.\n Weitere Informationen dazu, wie die Vorlage aussehen sollte, finden Sie in der Dokumentation oder in der mit dem Plugin bereitgestellten Beispieldatei.';
$string['total_progress'] = 'Gesamtfortschritt';

$string['unknownaction'] = 'Unbekannte Aktion. Änderungen konnten nicht durchgeführt werden.';
