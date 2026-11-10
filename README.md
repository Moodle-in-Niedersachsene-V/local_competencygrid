# Competency grids (local_competencygrid)

Create competency grids together in Moodle, share them and export them for
Exabis Competence Grids (block_exacomp) or Moodle competency frameworks.
The plugin replaces external editors such as Komet for creating grids.

## Features

- Library of grids with search and the tabs "All visible grids" and "My grids".
- Grid editor: topics as rows, levels as columns (for example AFB I to III),
  competencies as cards. Editing in a side panel without page reloads.
- Level templates (for example AFB I to III, competency levels 1 to 4, CEFR A1 to C2)
  maintained by the administrator; chosen when a grid is created and addable to existing grids.
- Collaborators with the roles "Can edit" and "Can view". Owner and site
  managers change settings, collaborators and may delete the grid.
- Visibility: private or visible on this site.
- Import of exacomp XML files (for example Komet exports). Each subject becomes a grid.
- Export as exacomp XML with stable ids: imported ids are kept, new items get
  ids starting at 900000000, so a new import into block_exacomp updates instead of duplicating.
- Export as CSV for the core import of competency frameworks (tool_lpimportcsv).
  Hierarchy: topic, level, competency.
- Simple conflict detection: a competency changed by someone else in the
  meantime is not overwritten.

## Requirements

Moodle 5.1 to 5.3, PHP 8.2 to 8.4, MariaDB, MySQL or PostgreSQL.

## Events

Creating, changing and deleting grids, changes of topics, levels and competencies, and
adding or removing collaborators are logged as events (`\local_competencygrid\event\*`).

## Installation

1. Copy the folder to `local/competencygrid` (Moodle 5.1+: `public/local/competencygrid`).
2. Build the JavaScript: `npx grunt amd --root=public/local/competencygrid`.
3. Visit the notifications page to install.

## Capabilities

| Capability | Default | Purpose |
|---|---|---|
| local/competencygrid:view | Authenticated user, manager | View visible grids, be a collaborator |
| local/competencygrid:create | Course creator, manager | Create and import grids |
| local/competencygrid:manageall | Manager | Edit and manage all grids |

Teachers usually hold their roles in course context only. To let all teachers create
grids, allow `local/competencygrid:create` for the authenticated user role or a
system role assigned to teachers.

## Settings

- `showinprimarynav`: link to the library in the primary navigation (default on).
- `options_subject`, `options_classlevel`, `options_edulevel`, `options_schooltype`: selection lists
  for the grid metadata, one value per line.
- `levelschemes`: level templates. A line `[Name]` starts a template, each following line
  `Level | short description` adds a level. The first template is preselected for new grids.
- `allowcustomvalues`: users may type values that are not in the lists (default on). Values already
  used in grids are then offered to everybody.

## Limitations of version 0.1

- No real-time collaboration: changes of others appear after "Refresh" or when the tab becomes visible again.
- Examples (materials) and child descriptors of exacomp files are not imported.
- No direct distribution to other Moodle sites, no version history and no comments yet.

## Licence

GNU GPL v3 or later.
