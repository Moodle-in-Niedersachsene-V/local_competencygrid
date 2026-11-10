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
 * Grid editor for local_competencygrid.
 *
 * @module     local_competencygrid/editor
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import ModalEvents from 'core/modal_events';
import ModalSaveCancel from 'core/modal_save_cancel';
import Notification from 'core/notification';
import Templates from 'core/templates';
import {getString} from 'core/str';

const COMPONENT = 'local_competencygrid';

/** @type {Object} Editor state. */
const state = {
    gridid: 0,
    grid: null,
    selected: null,
    draft: null,
    dirty: false,
    root: null,
};

/**
 * Calls a web service of this plugin.
 *
 * @param {String} name Function name without component prefix.
 * @param {Object} args Arguments.
 * @returns {Promise<Object>}
 */
const call = (name, args) => Ajax.call([{methodname: `${COMPONENT}_${name}`, args}])[0];

/**
 * Builds the template context of the grid.
 *
 * @returns {Object}
 */
const gridContext = () => {
    const grid = state.grid;
    const levels = grid.levels.map((level, index) => ({...level, position: (index % 3) + 1}));
    const rows = grid.topics.map((topic, index) => {
        const comps = grid.competencies.filter((comp) => comp.topicid === topic.id);
        return {
            id: topic.id,
            name: topic.name,
            count: comps.length,
            isfirst: index === 0,
            islast: index === grid.topics.length - 1,
            cells: levels.map((level) => ({
                topicid: topic.id,
                levelid: level.id,
                position: level.position,
                comps: comps.filter((comp) => comp.levelid === level.id).map((comp) => ({
                    id: comp.id,
                    title: comp.title,
                    selected: state.selected === comp.id,
                })),
            })),
        };
    });
    return {
        canedit: grid.canedit,
        sentencestart: grid.sentencestart,
        levelcount: levels.length,
        columns: `minmax(160px, 220px) repeat(${Math.max(levels.length, 1)}, minmax(190px, 1fr))`,
        levels,
        hasrows: rows.length > 0,
        rows,
    };
};

/**
 * Builds the template context of the side panel.
 *
 * @returns {Object}
 */
const panelContext = () => {
    const grid = state.grid;
    let comp = null;
    if (state.draft) {
        comp = state.draft;
    } else if (state.selected) {
        comp = grid.competencies.find((item) => item.id === state.selected) || null;
    }
    if (!comp) {
        return {empty: true};
    }
    const topic = grid.topics.find((item) => item.id === comp.topicid);
    const level = grid.levels.find((item) => item.id === comp.levelid);
    return {
        empty: false,
        canedit: grid.canedit,
        isnew: !comp.id,
        id: comp.id || 0,
        title: comp.title,
        timemodified: comp.timemodified || 0,
        sentencestart: grid.sentencestart,
        modifiedby: comp.modifiedby || '',
        topics: grid.topics.map((item) => ({id: item.id, name: item.name, selected: item.id === comp.topicid})),
        levels: grid.levels.map((item) => ({id: item.id, name: item.name, selected: item.id === comp.levelid})),
        topicname: topic ? topic.name : '',
        levelname: level ? level.name : '',
    };
};

/**
 * Renders the side panel.
 *
 * @returns {Promise<void>}
 */
const renderPanel = async() => {
    const region = state.root.querySelector('[data-region="panel"]');
    const {html, js} = await Templates.renderForPromise(`${COMPONENT}/panel`, panelContext());
    Templates.replaceNodeContents(region, html, js);
    state.dirty = false;
};

/**
 * Renders grid and side panel.
 *
 * @returns {Promise<void>}
 */
const render = async() => {
    if (state.selected && !state.grid.competencies.some((comp) => comp.id === state.selected)) {
        state.selected = null;
    }
    const region = state.root.querySelector('[data-region="grid"]');
    const {html, js} = await Templates.renderForPromise(`${COMPONENT}/grid`, gridContext());
    Templates.replaceNodeContents(region, html, js);
    await renderPanel();
};

/**
 * Loads the grid from the server and renders it.
 *
 * @returns {Promise<void>}
 */
const load = async() => {
    state.grid = await call('get_grid', {gridid: state.gridid});
    await render();
};

/**
 * Runs a changing web service, takes over the returned grid and renders it.
 *
 * @param {String} name Function name.
 * @param {Object} args Arguments.
 * @returns {Promise<Number|null>} Id returned by the service or null on failure.
 */
const mutate = async(name, args) => {
    try {
        const result = await call(name, args);
        state.grid = result.grid;
        return result.id;
    } catch (error) {
        await Notification.exception(error);
        await load();
        return null;
    }
};

/**
 * Asks for a name (and optionally a description) in a modal and passes the values to a callback.
 *
 * @param {String} titlekey String key of the modal title.
 * @param {String} name Current name.
 * @param {String|null} description Current description or null if not wanted.
 * @param {Function} onsave Async callback receiving {name, description}.
 * @returns {Promise<void>}
 */
const promptName = async(titlekey, name, description, onsave) => {
    const body = await Templates.render(`${COMPONENT}/name_form`, {
        name,
        hasdescription: description !== null,
        description: description || '',
    });
    const modal = await ModalSaveCancel.create({
        title: getString(titlekey, COMPONENT),
        body,
        show: true,
        removeOnClose: true,
    });
    const root = modal.getRoot()[0];
    const namefield = () => root.querySelector('[name="name"]');
    modal.getRoot().on(ModalEvents.shown, () => namefield().focus());
    modal.getRoot().on(ModalEvents.save, (event) => {
        const values = {
            name: namefield().value.trim(),
            description: description !== null ? root.querySelector('[name="description"]').value.trim() : '',
        };
        if (values.name === '') {
            event.preventDefault();
            namefield().focus();
            return;
        }
        onsave(values).catch(Notification.exception);
    });
    root.addEventListener('submit', (event) => {
        event.preventDefault();
        modal.getFooter().find('[data-action="save"]').trigger('click');
    });
};

/**
 * Asks for confirmation before deleting.
 *
 * @param {String} titlekey String key of the title.
 * @param {String} questionkey String key of the question.
 * @param {String} param Parameter of the question.
 * @returns {Promise<Boolean>}
 */
const confirmDelete = async(titlekey, questionkey, param) => {
    try {
        await Notification.deleteCancelPromise(
            getString(titlekey, COMPONENT),
            getString(questionkey, COMPONENT, param),
            getString('delete', 'core')
        );
        return true;
    } catch {
        return false;
    }
};

/**
 * Finds a topic or level by id.
 *
 * @param {Array} list List to search.
 * @param {Number} id Id.
 * @returns {Object|undefined}
 */
const byId = (list, id) => list.find((item) => item.id === id);

/**
 * Runs a changing web service and renders the result.
 *
 * @param {String} name Function name.
 * @param {Object} args Arguments.
 * @returns {Promise<void>}
 */
const mutateAndRender = async(name, args) => {
    await mutate(name, args);
    await render();
};

/**
 * Handlers of the data-action attributes. Each receives the clicked element and its id.
 *
 * @type {Object<string, Function>}
 */
const ACTIONS = {
    'select': async(target, id) => {
        state.draft = null;
        state.selected = id;
        await render();
        const field = state.root.querySelector('[data-region="panel"] textarea');
        if (field) {
            field.focus();
        }
    },
    'add-comp': async(target) => {
        state.selected = null;
        state.draft = {
            id: 0,
            title: '',
            topicid: parseInt(target.dataset.topicid, 10),
            levelid: parseInt(target.dataset.levelid, 10),
        };
        await render();
        state.root.querySelector('[data-region="panel"] textarea').focus();
    },
    'close-panel': async() => {
        state.draft = null;
        state.selected = null;
        await render();
    },
    'refresh': () => load(),
    'delete-comp': async(target, id) => {
        const comp = byId(state.grid.competencies, id);
        if (comp && await confirmDelete('deletecompetency', 'deletecompetencyconfirm', comp.title)) {
            state.selected = null;
            await mutateAndRender('delete_competency', {id});
        }
    },
    'add-topic': () => promptName('addtopic', '', null,
        (values) => mutateAndRender('save_topic', {gridid: state.gridid, id: 0, name: values.name})),
    'rename-topic': async(target, id) => {
        const topic = byId(state.grid.topics, id);
        if (topic) {
            await promptName('renametopic', topic.name, null,
                (values) => mutateAndRender('save_topic', {gridid: state.gridid, id, name: values.name}));
        }
    },
    'delete-topic': async(target, id) => {
        const topic = byId(state.grid.topics, id);
        if (topic && await confirmDelete('deletetopic', 'deletetopicconfirm', topic.name)) {
            await mutateAndRender('delete_topic', {id});
        }
    },
    'move-topic': (target, id) => mutateAndRender('move_topic', {
        id,
        direction: target.dataset.direction === 'up' ? 'up' : 'down',
    }),
    'add-level': () => promptName('addlevel', '', '',
        (values) => mutateAndRender('save_level', {gridid: state.gridid, id: 0, ...values})),
    'edit-level': async(target, id) => {
        const level = byId(state.grid.levels, id);
        if (level) {
            await promptName('editlevel', level.name, level.description,
                (values) => mutateAndRender('save_level', {gridid: state.gridid, id, ...values}));
        }
    },
    'delete-level': async(target, id) => {
        const level = byId(state.grid.levels, id);
        if (level && await confirmDelete('deletelevel', 'deletelevelconfirm', level.name)) {
            await mutateAndRender('delete_level', {id});
        }
    },
};

/**
 * Handles clicks on elements with a data-action attribute.
 *
 * @param {Event} event Click event.
 * @returns {Promise<void>}
 */
const handleClick = async(event) => {
    const target = event.target.closest('[data-action]');
    if (!target || !state.root.contains(target) || !ACTIONS[target.dataset.action]) {
        return;
    }
    await ACTIONS[target.dataset.action](target, parseInt(target.dataset.id || '0', 10));
};

/**
 * Saves the competency form of the side panel.
 *
 * @param {Event} event Submit event.
 * @returns {Promise<void>}
 */
const handleSubmit = async(event) => {
    const form = event.target.closest('[data-region="comp-form"]');
    if (!form) {
        return;
    }
    event.preventDefault();
    const title = form.querySelector('[name="title"]').value.trim();
    if (title === '') {
        form.querySelector('[name="title"]').focus();
        return;
    }
    const savedid = await mutate('save_competency', {
        gridid: state.gridid,
        id: parseInt(form.querySelector('[name="id"]').value, 10),
        topicid: parseInt(form.querySelector('[name="topicid"]').value, 10),
        levelid: parseInt(form.querySelector('[name="levelid"]').value, 10),
        title,
        timemodified: parseInt(form.querySelector('[name="timemodified"]').value, 10),
    });
    if (savedid) {
        state.draft = null;
        state.selected = savedid;
    }
    await render();
};

/**
 * Initialises the editor.
 *
 * @param {Number} gridid Grid id.
 */
export const init = (gridid) => {
    state.gridid = gridid;
    state.root = document.querySelector('[data-region="local_competencygrid-editor"]');
    if (!state.root) {
        return;
    }
    state.root.addEventListener('click', (event) => {
        handleClick(event).catch(Notification.exception);
    });
    state.root.addEventListener('submit', (event) => {
        handleSubmit(event).catch(Notification.exception);
    });
    state.root.addEventListener('input', (event) => {
        if (event.target.closest('[data-region="comp-form"]')) {
            state.dirty = true;
        }
    });
    // Changes of other collaborators are picked up when the page becomes visible again.
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible' && !state.dirty && !state.draft) {
            load().catch(Notification.exception);
        }
    });
    load().catch(Notification.exception);
};
