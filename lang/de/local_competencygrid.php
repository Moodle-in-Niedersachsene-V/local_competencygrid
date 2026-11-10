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
 * Language strings for local_competencygrid (de).
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addlevel'] = 'Niveau hinzufügen';
$string['addlevelscheme'] = 'Niveaus aus Vorlage ergänzen';
$string['addlevelscheme_help'] = 'Ergänzt das Raster um die Niveaus der gewählten Vorlage. Niveaus mit gleichem Namen werden nicht doppelt angelegt, vorhandene Niveaus und Kompetenzen bleiben unverändert.';
$string['addmember'] = 'Mitwirkende hinzufügen';
$string['addtopic'] = 'Thema hinzufügen';
$string['allowcustomvalues'] = 'Eigene Einträge erlauben';
$string['allowcustomvalues_desc'] = 'Nutzerinnen und Nutzer dürfen Werte eintippen, die nicht in der Liste stehen. Bereits in Rastern verwendete Werte werden dann allen zur Auswahl angeboten.';
$string['classlevel'] = 'Klasse oder Jahrgang';
$string['classlevel_help'] = 'Klasse oder Jahrgang, für die das Raster gedacht ist. Wählen Sie einen Wert aus der Liste oder tippen Sie, falls erlaubt, einen neuen ein; neue Werte stehen danach allen zur Auswahl.';
$string['compcount'] = 'Kompetenzen: {$a}';
$string['competency'] = 'Kompetenz';
$string['competencygrid:create'] = 'Kompetenzraster anlegen und importieren';
$string['competencygrid:manageall'] = 'Alle Kompetenzraster verwalten';
$string['competencygrid:view'] = 'Kompetenzraster ansehen';
$string['competencygrids'] = 'Kompetenzraster';
$string['conflict'] = 'Diese Kompetenz wurde inzwischen von {$a} geändert. Das Raster wurde neu geladen, bitte prüfen Sie Ihre Änderung und speichern Sie erneut.';
$string['creategrid'] = 'Raster anlegen';
$string['deletecompetency'] = 'Kompetenz löschen';
$string['deletecompetencyconfirm'] = 'Möchten Sie die Kompetenz „{$a}“ wirklich löschen?';
$string['deletegrid'] = 'Raster löschen';
$string['deletegridconfirm'] = 'Möchten Sie das Raster „{$a}“ mit allen Themen, Niveaus und Kompetenzen wirklich löschen? Das kann nicht rückgängig gemacht werden.';
$string['deletelevel'] = 'Niveau löschen';
$string['deletelevelconfirm'] = 'Möchten Sie das Niveau „{$a}“ und alle Kompetenzen darin wirklich löschen?';
$string['deletetopic'] = 'Thema löschen';
$string['deletetopicconfirm'] = 'Möchten Sie das Thema „{$a}“ und alle Kompetenzen darin wirklich löschen?';
$string['editcompetency'] = 'Kompetenz bearbeiten';
$string['editlevel'] = 'Niveau bearbeiten';
$string['edulevel'] = 'Bildungsstufe';
$string['edulevel_help'] = 'Bildungsstufe, z. B. Sekundarstufe II. Wählen Sie einen Wert aus der Liste oder tippen Sie, falls erlaubt, einen neuen ein; neue Werte stehen danach allen zur Auswahl.';
$string['emptyname'] = 'Bitte geben Sie einen Namen ein.';
$string['emptytitle'] = 'Bitte geben Sie den Text der Kompetenz ein.';
$string['eventgridcontentupdated'] = 'Inhalt eines Kompetenzrasters geändert';
$string['eventgridcreated'] = 'Kompetenzraster angelegt';
$string['eventgriddeleted'] = 'Kompetenzraster gelöscht';
$string['eventgridupdated'] = 'Einstellungen eines Kompetenzrasters geändert';
$string['eventmemberadded'] = 'Mitwirkende Person zu Kompetenzraster hinzugefügt';
$string['eventmemberremoved'] = 'Mitwirkende Person aus Kompetenzraster entfernt';
$string['exportexacomp'] = 'Export exacomp-XML';
$string['exportmoodlecsv'] = 'Export Moodle-CSV';
$string['fieldoptionsheading'] = 'Auswahllisten';
$string['fieldoptionsheading_desc'] = 'Werte für Fach, Klasse oder Jahrgang, Bildungsstufe und Schulform. Ein Wert pro Zeile, in der Reihenfolge der Anzeige.';
$string['fieldvaluenotallowed'] = 'Bitte wählen Sie einen Wert aus der Liste.';
$string['formulation'] = 'Formulierung';
$string['grid'] = 'Raster';
$string['griddeleted'] = 'Das Raster wurde gelöscht.';
$string['gridname'] = 'Name des Rasters';
$string['gridname_help'] = 'Name, der in der Bibliothek und als Seitentitel erscheint, z. B. „Betriebswirtschaftslehre · Jahrgang 11“.';
$string['gridsettings'] = 'Einstellungen des Rasters';
$string['import'] = 'Importieren';
$string['importdone'] = '{$a} Raster importiert.';
$string['importexacomp'] = 'exacomp-XML importieren';
$string['importfile'] = 'exacomp-XML-Datei';
$string['importfile_help'] = 'Eine XML-Datei im exacomp-Format, z. B. ein Export aus Komet oder aus diesem Plugin. IDs und Source-Kennung der Datei bleiben erhalten, ein späterer Export lässt sich deshalb wieder in bestehende exacomp-Raster einspielen.';
$string['importinfo'] = 'Importiert eine exacomp-XML-Datei, z. B. einen Export aus Komet. Jedes Fach in der Datei wird ein eigenes Raster. Beispiele (Materialien) werden noch nicht übernommen.';
$string['importinvalidxml'] = 'Die Datei ist keine gültige exacomp-XML-Datei.';
$string['importnosubjects'] = 'Die Datei enthält kein Fach.';
$string['lastchangedby'] = 'Zuletzt geändert von {$a}';
$string['lastmodified'] = 'Zuletzt geändert: {$a}';
$string['level'] = 'Niveau';
$string['leveldescription'] = 'Kurzbeschreibung, z. B. typische Operatoren';
$string['levelscheme'] = 'Niveaus';
$string['levelscheme_help'] = 'Vorlage für die Spalten des Rasters. Die Vorlagen werden in der Website-Administration gepflegt. Im Raster lassen sich Niveaus jederzeit ergänzen, umbenennen oder löschen.';
$string['levelschemedefaultname'] = 'Niveaus';
$string['levelschemekeep'] = 'Keine Niveaus ergänzen';
$string['levelschemenone'] = 'Keine Niveaus (später im Raster anlegen)';
$string['levelschemeoption'] = '{$a->name} ({$a->count} Niveaus)';
$string['levelschemes'] = 'Niveau-Vorlagen';
$string['levelschemes_default'] = '[Anforderungsbereiche I–III]
AFB I – Wiedergeben | nennen, beschreiben, darstellen
AFB II – Anwenden | berechnen, durchführen, anwenden
AFB III – Beurteilen | beurteilen, bewerten, begründen

[Kompetenzstufen 1–4]
Stufe 1 – Grundlegend | mit Unterstützung
Stufe 2 – Mittel | weitgehend selbstständig
Stufe 3 – Erweitert | selbstständig
Stufe 4 – Vertieft | übertragen und weiterentwickeln

[GER A1–C2]
A1 | elementare Sprachverwendung
A2 | elementare Sprachverwendung
B1 | selbstständige Sprachverwendung
B2 | selbstständige Sprachverwendung
C1 | kompetente Sprachverwendung
C2 | kompetente Sprachverwendung';
$string['levelschemes_desc'] = 'Vorlagen, die beim Anlegen oder Erweitern eines Rasters angeboten werden. Eine Zeile „[Name]“ beginnt eine Vorlage, jede folgende Zeile „Niveau | Kurzbeschreibung“ fügt ein Niveau hinzu. Die erste Vorlage ist vorausgewählt.';
$string['libraryintro'] = 'Kompetenzraster gemeinsam erstellen, teilen und für exacomp oder Moodle-Kompetenzen exportieren.';
$string['license'] = 'Lizenz';
$string['license_help'] = 'Lizenz, unter der das Raster geteilt und von anderen Schulen weiterverwendet werden darf.';
$string['memberadded'] = 'Die Person wurde gespeichert.';
$string['memberidentifier'] = 'E-Mail-Adresse oder Benutzername';
$string['memberidentifier_help'] = 'Geben Sie die genaue E-Mail-Adresse oder den Benutzernamen der Person ein. Ist die Person bereits eingetragen, wird ihre Rolle geändert.';
$string['memberremoved'] = 'Die Person wurde entfernt.';
$string['newcompetency'] = 'Neue Kompetenz';
$string['nogrids'] = 'Keine Raster gefunden.';
$string['nolevel'] = 'Ohne Niveau';
$string['nopermissionedit'] = 'Sie dürfen dieses Raster nicht bearbeiten.';
$string['nopermissionmanage'] = 'Sie dürfen dieses Raster nicht verwalten.';
$string['nopermissionview'] = 'Sie dürfen dieses Raster nicht ansehen.';
$string['norows'] = 'Dieses Raster hat noch keine Themen.';
$string['notspecified'] = 'Nicht angegeben';
$string['open'] = 'Öffnen';
$string['options_classlevel'] = 'Klassen oder Jahrgänge';
$string['options_classlevel_default'] = 'Jahrgang 5
Jahrgang 6
Jahrgang 7
Jahrgang 8
Jahrgang 9
Jahrgang 10
Jahrgang 11
Jahrgang 12
Jahrgang 13';
$string['options_desc'] = 'Ein Wert pro Zeile.';
$string['options_edulevel'] = 'Bildungsstufen';
$string['options_edulevel_default'] = 'Primarstufe
Sekundarstufe I
Sekundarstufe II
Berufsbildende Schulen';
$string['options_schooltype'] = 'Schulformen oder Abschlüsse';
$string['options_schooltype_default'] = 'Grundschule
Hauptschule
Realschule
Oberschule
Gesamtschule
Gymnasium
Berufsbildende Schule
Höherer Schulabschluss Abitur';
$string['options_subject'] = 'Fächer';
$string['options_subject_default'] = 'Betriebswirtschaftslehre
Volkswirtschaftslehre
Rechnungswesen
Deutsch
Englisch
Mathematik
Informatik';
$string['panelempty'] = 'Wählen Sie eine Kompetenz im Raster aus, um sie anzusehen oder zu bearbeiten.';
$string['pluginname'] = 'Kompetenzraster';
$string['privacy:metadata:comp'] = 'Kompetenzen der Raster.';
$string['privacy:metadata:comp:timemodified'] = 'Zeitpunkt der letzten Änderung.';
$string['privacy:metadata:comp:title'] = 'Text der Kompetenz.';
$string['privacy:metadata:comp:usermodified'] = 'Person, die die Kompetenz zuletzt geändert hat.';
$string['privacy:metadata:grid'] = 'Kompetenzraster.';
$string['privacy:metadata:grid:name'] = 'Name des Rasters.';
$string['privacy:metadata:grid:ownerid'] = 'Besitzer des Rasters.';
$string['privacy:metadata:grid:timecreated'] = 'Zeitpunkt der Erstellung.';
$string['privacy:metadata:grid:timemodified'] = 'Zeitpunkt der letzten Änderung.';
$string['privacy:metadata:grid:usermodified'] = 'Person, die das Raster zuletzt geändert hat.';
$string['privacy:metadata:member'] = 'Mitwirkende der Raster.';
$string['privacy:metadata:member:role'] = 'Rolle der mitwirkenden Person.';
$string['privacy:metadata:member:timecreated'] = 'Zeitpunkt, zu dem die Person hinzugefügt wurde.';
$string['privacy:metadata:member:userid'] = 'Die mitwirkende Person.';
$string['renametopic'] = 'Thema umbenennen';
$string['role'] = 'Rolle';
$string['roleeditor'] = 'Kann bearbeiten';
$string['roleowner'] = 'Besitzer';
$string['roleviewer'] = 'Kann ansehen';
$string['scalenotreached'] = 'Noch nicht erreicht';
$string['scalereached'] = 'Erreicht';
$string['schooltype'] = 'Schulform oder Abschluss';
$string['schooltype_help'] = 'Schulform oder Abschluss, z. B. Gymnasium oder Abitur. Wählen Sie einen Wert aus der Liste oder tippen Sie, falls erlaubt, einen neuen ein; neue Werte stehen danach allen zur Auswahl.';
$string['searchplaceholder'] = 'Name, Fach, Stufe';
$string['sentencestart'] = 'Satzanfang';
$string['sentencestart_help'] = 'Gemeinsamer Anfang aller Formulierungen, z. B. „Die Schüler können …“. Er wird als Hinweis angezeigt und beim Moodle-CSV-Export vor den Text gesetzt.';
$string['share'] = 'Teilen';
$string['shareintro'] = 'Personen mit der Rolle „Kann bearbeiten“ dürfen Themen, Niveaus und Kompetenzen ändern. Einstellungen, Mitwirkende und Löschen bleiben dem Besitzer vorbehalten.';
$string['showinprimarynav'] = 'In der Hauptnavigation anzeigen';
$string['showinprimarynav_desc'] = 'Fügt für Personen, die Raster ansehen dürfen, einen Link zur Raster-Bibliothek in die Hauptnavigation ein.';
$string['subject'] = 'Fach';
$string['subject_help'] = 'Fach des Rasters. Wählen Sie einen Wert aus der Liste oder tippen Sie, falls erlaubt, einen neuen ein; neue Werte stehen danach allen zur Auswahl.';
$string['tabmine'] = 'Meine Raster';
$string['tabvisible'] = 'Alle sichtbaren Raster';
$string['topic'] = 'Thema';
$string['topiccount'] = 'Themen: {$a}';
$string['usernotfound'] = 'Es wurde keine eindeutige, aktive Person mit dieser E-Mail-Adresse oder diesem Benutzernamen gefunden.';
$string['visibility'] = 'Sichtbarkeit';
$string['visibility_help'] = 'Private Raster sehen nur Besitzer und Mitwirkende. Auf dieser Website sichtbare Raster sehen alle, die Raster ansehen dürfen; bearbeiten dürfen weiterhin nur die Mitwirkenden.';
$string['visibilityhint'] = 'Sichtbarkeit und Lizenz in den Einstellungen des Rasters ändern';
$string['visibilityprivate'] = 'Privat';
$string['visibilitysite'] = 'Auf dieser Website sichtbar';
