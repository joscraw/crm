'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import ColumnSearch from "./ColumnSearch";
require('jquery-ui-dist/jquery-ui');

class WorkflowTriggerSelectedTriggers {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, workflow) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.workflow = workflow;

        this.unbindEvents();

        this.$wrapper.on(
            'click',
            WorkflowTriggerSelectedTriggers._selectors.removeSelectedColumnIcon,
            this.handleRemoveSelectedColumnIconClicked.bind(this)
        );

        this.$wrapper.on(
            'click',
            WorkflowTriggerSelectedTriggers._selectors.selectedColumn,
            this.handleSelectedColumnClicked.bind(this)
        );

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
                Settings.Events.WORKFLOW_TRIGGER_REMOVED,
                this.handleTriggerRemoved.bind(this)
            ));

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
                Settings.Events.WORKFLOW_TRIGGER_FILTER_ADDED,
                this.handleTriggerAdded.bind(this)
            ));

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
                Settings.Events.WORKFLOW_TRIGGER_FILTER_REMOVED,
                this.handleTriggerAdded.bind(this)
            ));

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
                Settings.Events.WORKFLOW_TRIGGER_ADDED,
                this.handleTriggerAdded.bind(this)
            ));

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
                Settings.Events.WORKFLOW_CUSTOM_OBJECT_SET,
                this.handleTriggerAdded.bind(this)
            ));

        this.renderTriggers(workflow.triggers);
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            removeSelectedColumnIcon: '.js-remove-selected-column-icon',
            selectedColumn: '.js-selected-column'
        }
    }

    unbindEvents() {
        this.$wrapper.off('click', WorkflowTriggerSelectedTriggers._selectors.removeSelectedColumnIcon);
        this.$wrapper.off('click', WorkflowTriggerSelectedTriggers._selectors.selectedColumn);
    }

    handleTriggerUpdated(workflow) {
        debugger;
        this.workflow = workflow;
        this.renderTriggers(workflow.triggers);
    }


    handleTriggerRemoved(workflow) {
        this.workflow = workflow;
        this.renderTriggers(workflow.triggers);
    }

    handleTriggerAdded(workflow) {
        debugger;
        this.workflow = workflow;
        this.renderTriggers(workflow.triggers);
    }

    handleRemoveSelectedColumnIconClicked(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        e.stopPropagation();

        let uid = $(e.target).attr('data-uid');

        this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_REMOVE_TRIGGER_BUTTON_PRESSED, uid);

    }

    handleSelectedColumnClicked(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        let uid = $(e.currentTarget).attr('data-uid');

        this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_EDIT_TRIGGER_CLICKED, uid);
    }

    handleWorkflowSave(workflow) {
        this.workflow = workflow;
        this.renderTriggers(workflow.triggers);
    }

    renderTriggers(triggers) {

        this.$wrapper.html("");

        if(_.isEmpty(triggers, true)) {
            this.$wrapper.html(emptyListTemplate());
            return;
        }

        for(let trigger of triggers) {

            this._addItem(trigger);

        }
    }

    _addItem(trigger) {

        debugger;
        let label = trigger.customObject !== null ? trigger.customObject.label : '';
        let numOfFilters = 'filters' in trigger ? trigger.filters.length : 0;
        const html = itemTemplate(trigger, label, numOfFilters);
        const $selectedColumnTemplate = $($.parseHTML(html));
        this.$wrapper.append($selectedColumnTemplate);
    }
}

const itemTemplate = ({name, description, uid}, label, numOfFilters) => `
    <div class="card js-selected-column" data-uid="${uid}">
        <div class="card-body c-report-widget__card-body">Trigger: ${label} ${description} - Total filters: ${numOfFilters}<span><i data-uid="${uid}" class="fa fa-times js-remove-selected-column-icon c-report-widget__remove-column-icon" aria-hidden="true"></i></span></div>
    </div>
`;

/**
 * @return {string}
 */
const emptyListTemplate = () => `
    <h1 style="text-align: center; margin-top: 300px">Add a trigger on the left to get started...</h1>
`;



export default WorkflowTriggerSelectedTriggers;