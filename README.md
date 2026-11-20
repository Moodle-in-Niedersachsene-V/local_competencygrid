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
- Visibility: private or public. Public grids can be seen and exported by everybody, guests included.
- Optional editing group: if a global group (cohort) is configured, only its members may create,
  import and edit grids.
- Import of exacomp XML files (for example Komet exports). Each subject becomes a grid.
- Export as exacomp XML with stable ids: imported ids are kept, new items get
  ids starting at 900000000, so a new import into block_exacomp updates instead of duplicating.
- Synchronisation with block_exacomp in other Moodle sites: public grids can be shared with the
  school instances. They are delivered as one exacomp XML feed (`/local/competencygrid/feed.php`)
  that block_exacomp fetches via its setting `xmlserverurl`. Every change of a shared grid raises
  its version and reaches the schools with the next nightly import of block_exacomp.
- Export as CSV for the core import of competency frameworks (tool_lpimportcsv).
  Hierarchy: topic, level, competency.
- Simple conflict detection: a competency changed by someone else in the
  meantime is not overwritten.

## Requirements

Moodle 5.1 to 5.3, PHP 8.2 to 8.4, MariaDB, MySQL or PostgreSQL.

## Synchronisation with block_exacomp

1. Hub: enable `feedenabled`, optionally set `feedsourcename`. Share public grids with
   "Share with schools" on the grid page (owner or managers).
2. School instance: enter the feed address as server URL (`exacomp | xmlserverurl`).
3. First import once manually: download the feed file from the library page and import it in
   block_exacomp, selecting the grids the school needs.
4. From then on the scheduled task of block_exacomp updates the selected grids every night.

The feed uses one fixed source identifier (created at installation, shown in the settings). All ids
in the feed are derived from database ids (offset 900000000), so they are unique and never change.
Withdrawing a grid removes it from the feed; schools keep their copy, which is no longer updated.

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

Teachers usually hold their roles in course context only. The simplest way to let a defined
set of people create and edit grids is the setting `editorcohort` (global group): its members
need no further role. Without a group, allow `local/competencygrid:create` for the authenticated
user role or a system role assigned to teachers.

Public grids need no capability at all; private grids are visible to owner, collaborators and
holders of `local/competencygrid:manageall`.

## Settings

- `feedenabled`: provide the feed for the school instances (default off).
- `feedsourcename`: source name shown in block_exacomp (default: site name).
- `showinprimarynav`: link to the library in the primary navigation (default on).
- `editorcohort`: global group whose members may create and edit grids (default: none).
- `options_subject`, `options_classlevel`, `options_edulevel`, `options_schooltype`: selection lists
  for the grid metadata, one value per line.
- `levelschemes`: level templates. A line `[Name]` starts a template, each following line
  `Level | short description` adds a level. The first template is preselected for new grids.
- `allowcustomvalues`: users may type values that are not in the lists (default on). Values already
  used in grids are then offered to everybody.

## Limitations of version 0.1

- No real-time collaboration: changes of others appear after "Refresh" or when the tab becomes visible again.
- Examples (materials) and child descriptors of exacomp files are not imported.
- No version history and no comments yet. Shared grids always deliver their current state.

## Licence

GNU GPL v3 or later.
