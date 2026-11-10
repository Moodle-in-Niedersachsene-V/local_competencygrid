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
 * Language strings for local_competencygrid (en).
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addlevel'] = 'Add level';
$string['addlevelscheme'] = 'Add levels from template';
$string['addlevelscheme_help'] = 'Adds the levels of the chosen template to this grid. Levels with the same name are not added twice, existing levels and competencies stay unchanged.';
$string['addmember'] = 'Add collaborator';
$string['addtopic'] = 'Add topic';
$string['allowcustomvalues'] = 'Allow own entries';
$string['allowcustomvalues_desc'] = 'Users may type values that are not in the list. Values already used in grids are then offered to everybody.';
$string['classlevel'] = 'Class or year';
$string['classlevel_help'] = 'Class or year the grid is meant for. Choose a value from the list or, if allowed, type a new one; new values are offered to everybody afterwards.';
$string['compcount'] = 'Competencies: {$a}';
$string['competency'] = 'Competency';
$string['competencygrid:create'] = 'Create and import competency grids';
$string['competencygrid:manageall'] = 'Manage all competency grids';
$string['competencygrid:view'] = 'View competency grids';
$string['competencygrids'] = 'Competency grids';
$string['conflict'] = 'This competency has been changed by {$a} in the meantime. The grid has been reloaded, please check your change and save again.';
$string['creategrid'] = 'Create grid';
$string['deletecompetency'] = 'Delete competency';
$string['deletecompetencyconfirm'] = 'Do you really want to delete the competency "{$a}"?';
$string['deletegrid'] = 'Delete grid';
$string['deletegridconfirm'] = 'Do you really want to delete the grid "{$a}" with all topics, levels and competencies? This cannot be undone.';
$string['deletelevel'] = 'Delete level';
$string['deletelevelconfirm'] = 'Do you really want to delete the level "{$a}" and all competencies in it?';
$string['deletetopic'] = 'Delete topic';
$string['deletetopicconfirm'] = 'Do you really want to delete the topic "{$a}" and all competencies in it?';
$string['editcompetency'] = 'Edit competency';
$string['editlevel'] = 'Edit level';
$string['edulevel'] = 'Educational level';
$string['edulevel_help'] = 'Educational level, e.g. upper secondary level. Choose a value from the list or, if allowed, type a new one; new values are offered to everybody afterwards.';
$string['emptyname'] = 'Please enter a name.';
$string['emptytitle'] = 'Please enter the competency text.';
$string['eventgridcontentupdated'] = 'Competency grid content changed';
$string['eventgridcreated'] = 'Competency grid created';
$string['eventgriddeleted'] = 'Competency grid deleted';
$string['eventgridupdated'] = 'Competency grid settings changed';
$string['eventmemberadded'] = 'Collaborator added to competency grid';
$string['eventmemberremoved'] = 'Collaborator removed from competency grid';
$string['exportexacomp'] = 'Export exacomp XML';
$string['exportmoodlecsv'] = 'Export Moodle CSV';
$string['fieldoptionsheading'] = 'Selection lists';
$string['fieldoptionsheading_desc'] = 'Values offered for subject, class or year, educational level and school type. One value per line, in the order shown to users.';
$string['fieldvaluenotallowed'] = 'Please choose a value from the list.';
$string['formulation'] = 'Formulation';
$string['grid'] = 'Grid';
$string['griddeleted'] = 'The grid has been deleted.';
$string['gridname'] = 'Name of the grid';
$string['gridname_help'] = 'Name shown in the library and as page title, e.g. "Business studies · Year 11".';
$string['gridsettings'] = 'Grid settings';
$string['import'] = 'Import';
$string['importdone'] = '{$a} grid(s) imported.';
$string['importexacomp'] = 'Import exacomp XML';
$string['importfile'] = 'exacomp XML file';
$string['importfile_help'] = 'An XML file in exacomp format, e.g. an export from Komet or from this plugin. Ids and the source of the file are kept, so a later export can be imported into existing exacomp grids again.';
$string['importinfo'] = 'Imports an exacomp XML file, e.g. exported from Komet. Each subject in the file becomes its own grid. Examples (materials) are not imported yet.';
$string['importinvalidxml'] = 'The file is not a valid exacomp XML file.';
$string['importnosubjects'] = 'The file does not contain any subject.';
$string['lastchangedby'] = 'Last changed by {$a}';
$string['lastmodified'] = 'Last changed: {$a}';
$string['level'] = 'Level';
$string['leveldescription'] = 'Short description, e.g. typical operators';
$string['levelscheme'] = 'Levels';
$string['levelscheme_help'] = 'Template for the columns of the grid. The templates are maintained in the site administration. Levels can be added, renamed or deleted in the grid at any time.';
$string['levelschemedefaultname'] = 'Levels';
$string['levelschemekeep'] = 'Do not add levels';
$string['levelschemenone'] = 'No levels (add them in the grid later)';
$string['levelschemeoption'] = '{$a->name} ({$a->count} levels)';
$string['levelschemes'] = 'Level templates';
$string['levelschemes_default'] = '[Requirement areas I–III]
AFB I – Reproduce | name, describe, outline
AFB II – Apply | calculate, carry out, apply
AFB III – Evaluate | assess, evaluate, justify

[Competency levels 1–4]
Level 1 – Basic | with support
Level 2 – Intermediate | largely independently
Level 3 – Advanced | independently
Level 4 – Expert | transfer and develop further

[CEFR A1–C2]
A1 | basic user
A2 | basic user
B1 | independent user
B2 | independent user
C1 | proficient user
C2 | proficient user';
$string['levelschemes_desc'] = 'Templates offered when a grid is created or extended. A line "[Name]" starts a template, every following line "Level | short description" adds a level. The first template is preselected.';
$string['libraryintro'] = 'Create competency grids together, share them and export them for exacomp or Moodle competencies.';
$string['license'] = 'Licence';
$string['license_help'] = 'Licence under which the grid may be shared and reused by other schools.';
$string['memberadded'] = 'The collaborator has been saved.';
$string['memberidentifier'] = 'Email address or username';
$string['memberidentifier_help'] = 'Enter the exact email address or username of the person. If the person is already a collaborator, the role is changed.';
$string['memberremoved'] = 'The collaborator has been removed.';
$string['newcompetency'] = 'New competency';
$string['nogrids'] = 'No grids found.';
$string['nolevel'] = 'Without level';
$string['nopermissionedit'] = 'You are not allowed to edit this grid.';
$string['nopermissionmanage'] = 'You are not allowed to manage this grid.';
$string['nopermissionview'] = 'You are not allowed to view this grid.';
$string['norows'] = 'This grid does not have any topics yet.';
$string['notspecified'] = 'Not specified';
$string['open'] = 'Open';
$string['options_classlevel'] = 'Classes or years';
$string['options_classlevel_default'] = 'Year 5
Year 6
Year 7
Year 8
Year 9
Year 10
Year 11
Year 12
Year 13';
$string['options_desc'] = 'One value per line.';
$string['options_edulevel'] = 'Educational levels';
$string['options_edulevel_default'] = 'Primary level
Lower secondary level
Upper secondary level
Vocational schools';
$string['options_schooltype'] = 'School types or qualifications';
$string['options_schooltype_default'] = 'Primary school
Lower secondary school
Intermediate school
Comprehensive school
Grammar school
Vocational school';
$string['options_subject'] = 'Subjects';
$string['options_subject_default'] = 'Business studies
Economics
German
English
Mathematics
Computer science';
$string['panelempty'] = 'Select a competency in the grid to view or edit it.';
$string['pluginname'] = 'Competency grids';
$string['privacy:metadata:comp'] = 'Competencies of the grids.';
$string['privacy:metadata:comp:timemodified'] = 'Time of the last change.';
$string['privacy:metadata:comp:title'] = 'Text of the competency.';
$string['privacy:metadata:comp:usermodified'] = 'User who changed the competency last.';
$string['privacy:metadata:grid'] = 'Competency grids.';
$string['privacy:metadata:grid:name'] = 'Name of the grid.';
$string['privacy:metadata:grid:ownerid'] = 'Owner of the grid.';
$string['privacy:metadata:grid:timecreated'] = 'Time of creation.';
$string['privacy:metadata:grid:timemodified'] = 'Time of the last change.';
$string['privacy:metadata:grid:usermodified'] = 'User who changed the grid last.';
$string['privacy:metadata:member'] = 'Collaborators of the grids.';
$string['privacy:metadata:member:role'] = 'Role of the collaborator.';
$string['privacy:metadata:member:timecreated'] = 'Time the collaborator was added.';
$string['privacy:metadata:member:userid'] = 'The collaborator.';
$string['renametopic'] = 'Rename topic';
$string['role'] = 'Role';
$string['roleeditor'] = 'Can edit';
$string['roleowner'] = 'Owner';
$string['roleviewer'] = 'Can view';
$string['scalenotreached'] = 'Not yet achieved';
$string['scalereached'] = 'Achieved';
$string['schooltype'] = 'School type or qualification';
$string['schooltype_help'] = 'School type or qualification, e.g. grammar school or Abitur. Choose a value from the list or, if allowed, type a new one; new values are offered to everybody afterwards.';
$string['searchplaceholder'] = 'Name, subject, level';
$string['sentencestart'] = 'Sentence start';
$string['sentencestart_help'] = 'Common start of all competency formulations, e.g. "Students can …". It is shown as a hint and added in front of the text in the Moodle CSV export.';
$string['share'] = 'Share';
$string['shareintro'] = 'Collaborators with the role "Can edit" may change topics, levels and competencies. Settings, collaborators and deletion are reserved for the owner.';
$string['showinprimarynav'] = 'Show in primary navigation';
$string['showinprimarynav_desc'] = 'Adds a link to the grid library to the primary navigation for users who may view grids.';
$string['subject'] = 'Subject';
$string['subject_help'] = 'Subject of the grid. Choose a value from the list or, if allowed, type a new one; new values are offered to everybody afterwards.';
$string['tabmine'] = 'My grids';
$string['tabvisible'] = 'All visible grids';
$string['topic'] = 'Topic';
$string['topiccount'] = 'Topics: {$a}';
$string['usernotfound'] = 'No unique, active user with this email address or username was found.';
$string['visibility'] = 'Visibility';
$string['visibility_help'] = 'Private grids can only be seen by the owner and the collaborators. Grids visible on this site can be seen by everyone who may view grids; editing remains reserved for the collaborators.';
$string['visibilityhint'] = 'Change visibility and licence in the grid settings';
$string['visibilityprivate'] = 'Private';
$string['visibilitysite'] = 'Visible on this site';
