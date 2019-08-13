'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import ColumnSearch from "./ColumnSearch";
require('jquery-ui-dist/jquery-ui');
import FilterHelper from "../FilterHelper";

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

        this.$wrapper.on(
            'click',
            WorkflowTriggerSelectedTriggers._selectors.editAction,
            this.handleActionClicked.bind(this)
        );

        this.$wrapper.on(
            'click',
            WorkflowTriggerSelectedTriggers._selectors.newActionButton,
            this.handleNewActionButtonClicked.bind(this)
        );

        this.$wrapper.on(
            'click',
            WorkflowTriggerSelectedTriggers._selectors.removeActionButton,
            this.handleRemoveActionButtonClicked.bind(this)
        );

        this.$wrapper.on(
            'click',
            WorkflowTriggerSelectedTriggers._selectors.removeActionButton,
            this.handleRemoveActionButtonClicked.bind(this)
        );

        this.$wrapper.on(
            'click',
            WorkflowTriggerSelectedTriggers._selectors.newTriggerButton,
            this.handleNewTriggerButtonClicked.bind(this)
        );

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
                Settings.Events.WORKFLOW_TRIGGER_REMOVED,
                this.updateView.bind(this)
            ));

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
                Settings.Events.WORKFLOW_ACTION_REMOVED,
                this.updateView.bind(this)
            ));

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
                Settings.Events.WORKFLOW_TRIGGER_FILTER_ADDED,
                this.updateView.bind(this)
            ));

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
                Settings.Events.WORKFLOW_TRIGGER_FILTER_REMOVED,
                this.updateView.bind(this)
            ));

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
                Settings.Events.WORKFLOW_CUSTOM_OBJECT_SET,
                this.updateView.bind(this)
            ));

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
                Settings.Events.WORKFLOW_ACTION_ADDED,
                this.updateView.bind(this)
            ));

        this.render();

    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            removeSelectedColumnIcon: '.js-remove-selected-column-icon',
            selectedColumn: '.js-selected-column',
            newActionButton: '.js-new-action-button',
            removeActionButton: '.js-remove-action-button',
            editAction: '.js-edit-action',
            newTriggerButton: '.js-new-trigger-button'
        }
    }

    unbindEvents() {
        this.$wrapper.off('click', WorkflowTriggerSelectedTriggers._selectors.removeSelectedColumnIcon);
        this.$wrapper.off('click', WorkflowTriggerSelectedTriggers._selectors.selectedColumn);
        this.$wrapper.off('click', WorkflowTriggerSelectedTriggers._selectors.newActionButton);
        this.$wrapper.off('click', WorkflowTriggerSelectedTriggers._selectors.removeActionButton);
        this.$wrapper.off('click', WorkflowTriggerSelectedTriggers._selectors.newTriggerButton);
    }

    render() {
        debugger;
        this.$wrapper.html(WorkflowTriggerSelectedTriggers.markup(this));

        this.renderTriggers(this.workflow.triggers);
        this.renderActions(this.workflow.actions);
        this.renderAddActionButton();
    }

    handleTriggerUpdated(workflow) {
        debugger;
        this.workflow = workflow;
        this.renderTriggers(workflow.triggers);
    }

    updateView(workflow) {
        this.workflow = workflow;
        this.render();
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

    handleActionAdded(workflow) {
        debugger;
        this.workflow = workflow;
        this.renderActions(workflow.actions);
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

    handleNewActionButtonClicked(e) {
        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_ADD_ACTION_BUTTON_PRESSED);
    }

    handleRemoveActionButtonClicked(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        e.stopPropagation();

        let uid = $(e.currentTarget).attr('data-uid');

        this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_REMOVE_ACTION_BUTTON_PRESSED, uid);
    }

    handleNewTriggerButtonClicked(e) {

        if(e.cancelable) {
            e.preventDefault();
        }
        this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_NEW_TRIGGER_BUTTON_PRESSED);
    }

    handleSelectedColumnClicked(e) {
        if(e.cancelable) {
            e.preventDefault();
        }

        let uid = $(e.currentTarget).attr('data-uid');

        this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_EDIT_TRIGGER_CLICKED, uid);
    }

    handleActionClicked(e) {
        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        let uid = $(e.currentTarget).attr('data-uid');

        this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_EDIT_ACTION_CLICKED, uid);
    }

    handleWorkflowSave(workflow) {
        debugger;
        this.workflow = workflow;
        this.renderTriggers(workflow.triggers);
    }

    renderTriggers(triggers) {

        debugger;
        this.$wrapper.find('.js-triggers').html("");

        if(_.isEmpty(triggers, true)) {
            this.$wrapper.find('.js-triggers').html(emptyListTemplate());
            return;
        }

        for(let trigger of triggers) {

            this._addItem(trigger);

        }
    }

    renderActions(actions) {
        for(let action of actions) {
            this._addAction(action);
        }
    }

    renderAddActionButton() {
        this.$wrapper.append(addActionButtonTemplate());
    }

    _addItem(trigger) {

        debugger;
        let numOfFilters = 'filters' in trigger ? trigger.filters.length : 0;
        const html = itemTemplate(trigger, numOfFilters);
        const $selectedColumnTemplate = $($.parseHTML(html));
        this.$wrapper.find('.js-triggers').append($selectedColumnTemplate);
    }

    _addAction(action) {

        debugger;
        let label = FilterHelper.getActionTextFromAction(action);
        const html = actionTemplate(action, label);
        const $selectedColumnTemplate = $($.parseHTML(html));
        this.$wrapper.append($selectedColumnTemplate);
    }

    static markup({workflow: {customObject: {label:customObjectLabel}}}) {
        return `            
        <div class="card mx-auto" style="width:400px; margin-bottom: 0">
            <div class="card-header text-center">
                ${customObjectLabel} enrollment trigger
            </div>
            <div class="card-body">
            <div class="js-triggers"></div>
            <button class="btn btn-lg btn-primary ml-auto js-new-trigger-button w-100">New Trigger</button>
            </div>
        </div>
        <div style="width:1px; border-left:1px solid black; margin-left:auto; margin-right: auto; height: 100px;"></div>
    `;
    }
}

const itemTemplate = ({description, uid}, numOfFilters) => `
    <div class="card js-selected-column" data-uid="${uid}">
        <div class="card-body">${description} - Total criteria: ${numOfFilters}<span class="float-right"><i data-uid="${uid}" class="fa fa-times js-remove-selected-column-icon" aria-hidden="true"></i></span></div>
    </div>
`;

const actionTemplate = ({description, uid}, label) => `
        <div class="card mx-auto js-edit-action" style="width:400px; margin-bottom: 0" data-uid="${uid}">
            <div class="card-header text-center">
                ${description}
            </div>
            <div class="card-body">
            ${label}
            <span class="float-right"><i data-uid="${uid}" class="fa fa-times js-remove-action-button" aria-hidden="true"></i></span>
            </div>
        </div>
        <div style="width:1px; border-left:1px solid black; margin-left:auto; margin-right: auto; height: 100px;"></div>
`;

/**
 * @return {string}
 */
const emptyListTemplate = () => `
    <p style="text-align: center; font-size: 14px;">Add a trigger on the left to get started...</p>
`;

const addActionButtonTemplate = () => `
    <div class="text-center">
    <button type="button" class="btn btn-link js-new-action-button"><i class="fa fa-plus"></i> Add Action</button>
    </div>
`;



export default WorkflowTriggerSelectedTriggers;