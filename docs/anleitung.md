# Kompetenzraster – Kurzanleitung

## Wer darf was?
- **Öffentliche Raster** sehen und exportieren alle, auch Gäste. **Private Raster** sehen nur Besitzer und Mitwirkende.
- **Anlegen und bearbeiten** dürfen nur Mitglieder der globalen Gruppe, die in der Website-Administration
  (Plugins → Lokale Plugins → Kompetenzraster → „Globale Gruppe für die Bearbeitung“) gewählt ist.
  Bearbeitet werden nur eigene Raster und Raster, bei denen man mit „Kann bearbeiten“ eingetragen ist.
- Ohne gewählte Gruppe entscheidet die Berechtigung `local/competencygrid:create`.

## Raster anlegen
Kompetenzraster → **Raster anlegen**. Name, Fach, Jahrgang, Bildungsstufe und Schulform eintragen.
Fach, Jahrgang, Bildungsstufe und Schulform wählen Sie aus Listen, die in der
Website-Administration (Plugins → Lokale Plugins → Kompetenzraster) gepflegt werden.
Ist „Eigene Einträge erlauben“ aktiv, kann man auch neue Werte eintippen; sie stehen danach allen zur Auswahl.
Unter **Niveaus** wählen Sie eine Vorlage für die Spalten, z. B. Anforderungsbereiche I–III,
Kompetenzstufen 1–4 oder GER A1–C2. Die Vorlagen pflegt die Website-Administration
(eine Zeile „[Name]“ beginnt eine Vorlage, darunter je Zeile „Niveau | Kurzbeschreibung“).
In den Einstellungen eines bestehenden Rasters lassen sich über **Niveaus aus Vorlage ergänzen**
weitere Stufen hinzufügen; gleichnamige Niveaus werden nicht doppelt angelegt.

## Komet-Raster übernehmen
Kompetenzraster → **exacomp-XML importieren** → XML-Datei aus Komet hochladen.
Jedes Fach der Datei wird ein eigenes Raster. IDs und Source-Kennung bleiben erhalten,
ein späterer Export lässt sich deshalb wieder in bestehende exacomp-Raster einspielen.

## Bearbeiten
- **+ Kompetenz** in einer Zelle öffnet rechts das Bearbeitungsfeld.
- Klick auf eine Kompetenz: Text, Niveau oder Thema ändern, speichern oder löschen.
- **Thema hinzufügen**, Stift- und Pfeilsymbole an den Themen: umbenennen, verschieben, löschen.
- **Niveau hinzufügen**, Stift am Spaltenkopf: Name und Kurzbeschreibung (z. B. Operatoren).
- Änderungen anderer erscheinen nach **Aktualisieren** oder beim Zurückkehren in den Tab.
  Hat jemand dieselbe Kompetenz inzwischen geändert, wird nicht überschrieben.

## Teilen
**Teilen** → Person per E-Mail-Adresse oder Benutzername mit „Kann bearbeiten“ oder
„Kann ansehen“ eintragen. Sichtbarkeit (privat / auf dieser Website) und Lizenz unter **Einstellungen**.

## Export
- **Export exacomp-XML** für block_exacomp (Import wie bisher aus Komet).
- **Export Moodle-CSV** für Website-Administration → Kompetenzen → Kompetenzrahmen importieren.

## Synchronisation mit den Schulinstanzen (block_exacomp)
1. **Hub (Administration):** Kompetenzraster-Einstellungen → „Feed bereitstellen“ einschalten, Quellname eintragen
   (z. B. „Moodle in Niedersachsen – Material-Hub“).
2. **Raster freigeben:** Auf der Rasterseite „Für Schulen freigeben“ (nur öffentliche Raster, Besitzer oder Manager).
   Jede spätere Änderung erreicht die Schulen mit dem nächsten nächtlichen Import.
3. **Schulinstanz:** In block_exacomp die Feed-Adresse als Server-URL (xmlserverurl) eintragen.
4. **Erster Import:** In der Bibliothek „Datei für den ersten Import herunterladen“, in block_exacomp importieren und
   die gewünschten Raster anhaken. Danach aktualisiert block_exacomp diese Raster jede Nacht.
5. **Weitere Raster:** Neu freigegebene Raster erscheinen erst nach einem erneuten manuellen Import zur Auswahl.
6. **Freigabe zurückziehen:** Das Raster verschwindet aus dem Feed; die Schulen behalten ihren Stand ohne Updates.
   Ist in block_exacomp „Daten bei Synchronisation löschen“ aktiv, werden unbenutzte Raster ohne Bewertungen entfernt.
