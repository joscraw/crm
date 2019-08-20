'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import RecordForm from './RecordForm';
import Routing from "../Routing";
import Settings from "../Settings";
import List from "list.js";
import WorkflowTriggerType from "./WorkflowTriggerType";
import StringHelper from "../StringHelper";
import WorkflowTriggerCustomObject from "./WorkflowTriggerCustomObject";
import WorkflowTriggerPropertyList from "./WorkflowTriggerPropertyList";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import NumberFieldFilterForm from "./NumberFieldFilterForm";
import DatePickerFieldFilterForm from "./DatePickerFieldFilterForm";
import SingleCheckboxFieldFilterForm from "./SingleCheckboxFieldFilterForm";
import DropdownSelectFieldFilterForm from "./DropdownSelectFieldFilterForm";
import MultilpleCheckboxFieldFilterForm from "./MultilpleCheckboxFieldFilterForm";
import WorkflowTriggerFilters from "./WorkflowTriggerFilters";
import ReportFilterList from "./ReportFilterList";
import WorkflowTriggerSelectedTriggers from "./WorkflowTriggerSelectedTriggers";
import FormEditorTopBar from "./FormEditorTopBar";
import WorkflowTopBar from "./WorkflowTopBar";
import EditSingleLineTextFieldFilterForm from "./EditSingleLineTextFieldFilterForm";
import EditNumberFieldFilterForm from "./EditNumberFieldFilterForm";
import EditDatePickerFieldFilterForm from "./EditDatePickerFieldFilterForm";
import EditSingleCheckboxFieldFilterForm from "./EditSingleCheckboxFieldFilterForm";
import EditDropdownSelectFieldFilterForm from "./EditDropdownSelectFieldFilterForm";
import EditMultipleCheckboxFieldFilterForm from "./EditMultipleCheckboxFieldFilterForm";
import FormEditorSubBar from "./FormEditorSubBar";
import WorkflowSubBar from "./WorkflowSubBar";
import WorkflowActionType from "./WorkflowActionType";
import WorkflowActionPropertyList from "./WorkflowActionPropertyList";
import WorkflowActionSetPropertyValueForm from "./WorkflowActionSetPropertyValueForm";
import EditWorkflowActionSetPropertyValueForm from "./EditWorkflowActionSetPropertyValueForm";

class WorkflowTrigger {

    /**
     * @author Josh Crawmer
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param uid
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, uid) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.uid = uid;
        this.trigger = null;
        this.action = null;
        this.workflow = {};
        this.originalWorkflow = {};

        this.unbindEvents();

        this.globalEventDispatcher.subscribe(
            Settings.Events.WORKFLOW_TRIGGER_LIST_ITEM_CLICKED,
            this.handleTriggerListItemClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.WORKFLOW_ACTION_LIST_ITEM_CLICKED,
            this.handleActionListItemClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.WORKFLOW_TRIGGER_CUSTOM_OBJECT_LIST_ITEM_CLICKED,
            this.handleWorkflowTriggerCustomObjectListItemClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.WORKFLOW_TRIGGER_PROPERTY_LIST_ITEM_CLICKED,
            this.handleWorkflowTriggerPropertyListItemClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.WORKFLOW_ACTION_CUSTOM_OBJECT_LIST_ITEM_CLICKED,
            this.handleWorkflowActionCustomObjectListItemClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.WORKFLOW_ACTION_PROPERTY_LIST_ITEM_CLICKED,
            this.handleWorkflowActionPropertyListItemClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.WORKFLOW_EDIT_FILTER_CLICKED,
            this.handleWorkflowEditFilterClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.WORKFLOW_BACK_BUTTON_CLICKED,
            this.handleBackButtonClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.FILTER_BACK_TO_LIST_BUTTON_CLICKED,
            this.handleFilterBackToListButtonClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.WORKFLOW_TRIGGER_CUSTOM_OBJECT_FILTER_LIST_ITEM_CLICKED,
            this.handleListCustomObjectFilterListItemClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.WORKFLOW_EDIT_TRIGGER_CLICKED,
            this.handleWorkflowEditTriggerClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.WORKFLOW_EDIT_ACTION_CLICKED,
            this.handleWorkflowEditActionClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.APPLY_CUSTOM_FILTER_BUTTON_PRESSED,
            this.applyCustomFilterButtonPressedHandler.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.APPLY_WORKFLOW_ACTION_BUTTON_PRESSED,
            this.applyWorkflowActionButtonPressedHandler.bind(this)
        );

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
                Settings.Events.WORKFLOW_TRIGGER_ADD_OR_FILTER_BUTTON_PRESSED,
                this.addOrFilterButtonPressedHandler.bind(this)
            ));

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
                Settings.Events.WORKFLOW_TRIGGER_ADD_FILTER_BUTTON_PRESSED,
                this.addFilterButtonPressedHandler.bind(this)
            ));


        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
                Settings.Events.WORKFLOW_REMOVE_FILTER_BUTTON_PRESSED,
                this.workflowRemoveFilterButtonPressedHandler.bind(this)
            ));

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
                Settings.Events.WORKFLOW_REMOVE_TRIGGER_BUTTON_PRESSED,
                this.workflowRemoveTriggerButtonPressedHandler.bind(this)
            ));

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
                Settings.Events.WORKFLOW_ADD_ACTION_BUTTON_PRESSED,
                this.workflowAddActionButtonPressedHandler.bind(this)
            ));

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
                Settings.Events.WORKFLOW_REMOVE_ACTION_BUTTON_PRESSED,
                this.workflowRemoveActionButtonPressedHandler.bind(this)
            ));

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
                Settings.Events.WORKFLOW_NEW_TRIGGER_BUTTON_PRESSED,
                this.workflowNewTriggerButtonPressedHandler.bind(this)
            ));

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
                Settings.Events.WORKFLOW_PUBLISH_BUTTON_CLICKED,
                this.workflowPublishButtonClickedHandler.bind(this)
            ));

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
                Settings.Events.WORKFLOW_NAME_CHANGED,
                this.workflowNameChangedHandler.bind(this)
            ));

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
                Settings.Events.WORKFLOW_REVERT_BUTTON_CLICKED,
                this.handleRevertButtonClicked.bind(this)
            ));

        this.loadWorkflow().then((data) => {
            debugger;
            this.workflow = data.data;
            /*this.workflow = !_.isEmpty(this.workflow.draft) ? this.workflow.draft : _.cloneDeep(data.data);*/

            this.render();
        });
    }

    unbindEvents() {}

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            workflowTriggerContainer: '.js-workflow-trigger-container',
            workflowTriggerListContainer: '.js-workflow-trigger-list-container',
            topBar: '.js-top-bar',
            subBar: '.js-sub-bar'
        }
    }

    handleTriggerListItemClicked(trigger) {
        debugger;
        this.trigger = trigger;
        this.trigger.uid = !this.trigger.uid ? StringHelper.makeCharId() : this.trigger.uid;
        this.workflow.triggers.push(this.trigger);

        switch (trigger.name) {
            case 'property_based_trigger':
                new WorkflowTriggerPropertyList(this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.uid, this.workflow.customObject);
                break;
        }
    }

    handleActionListItemClicked(action) {
        debugger;
        this.action = action;
        this.action.uid = !this.action.uid ? StringHelper.makeCharId() : this.action.uid;
        this.workflow.actions.push(this.action);

        switch (action.name) {
            case 'PROPERTY_VALUE_ACTION':
                new WorkflowActionPropertyList(this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.uid, this.workflow.customObject);
                break;
        }
    }

    workflowAddActionButtonPressedHandler() {
        new WorkflowActionType(this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.uid);
    }

    addOrFilterButtonPressedHandler(referencedFilterPath) {
        new WorkflowTriggerPropertyList(this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.uid, this.workflow.customObject, null, [], {}, referencedFilterPath);
    }

    handleWorkflowEditTriggerClicked(uid) {
        let index = this.workflow.triggers.findIndex(trigger => trigger.uid === uid);
        this.trigger = this.workflow.triggers[index];
        new WorkflowTriggerFilters(this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.trigger);
    }

    handleWorkflowEditActionClicked(uid) {
        debugger;
        let actionIndex = this.workflow.actions.findIndex(action => action.uid === uid);
        let action = this.action = this.workflow.actions[actionIndex];
        new EditWorkflowActionSetPropertyValueForm($(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.workflow.customObject.internalName, action);
    }

    addFilterButtonPressedHandler() {
        debugger;
        new WorkflowTriggerPropertyList(this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.uid, this.workflow.customObject);
    }

    handleBackButtonClicked(view) {
        debugger;
        switch (view) {
            case Settings.VIEWS.WORKFLOW_TRIGGER_SELECT_TRIGGER_TYPE:
                new WorkflowTriggerType(this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.uid);
                break;
            case Settings.VIEWS.WORKFLOW_TRIGGER_SELECT_CUSTOM_OBJECT:
                new WorkflowTriggerCustomObject(this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.uid, this.trigger);
                break;
            case Settings.VIEWS.WORKFLOW_ACTION_SELECT_TYPE:
                new WorkflowActionType(this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.uid);
                break;
            case Settings.VIEWS.WORKFLOW_ACTION_SELECT_PROPERTY:
                new WorkflowActionPropertyList(this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.uid, this.workflow.customObject);
                break;
        }
    }

    workflowNewTriggerButtonPressedHandler() {
        new WorkflowTriggerType(this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.uid);
    }

    handleFilterBackToListButtonClicked() {
        new WorkflowTriggerPropertyList(this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.uid, this.workflow.customObject);
    }

    handleWorkflowTriggerCustomObjectListItemClicked(customObject) {
        debugger;
        this.workflow.customObject = customObject;
        new WorkflowTriggerPropertyList(this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.uid, this.workflow.customObject);
        this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_CUSTOM_OBJECT_SET, this.workflow);
    }

    handleListCustomObjectFilterListItemClicked(property, joins) {
        debugger;
        new WorkflowTriggerPropertyList(this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.uid, property.field.customObject, property, joins, property.referencedFilterPath);
    }

    handleWorkflowActionCustomObjectListItemClicked(property, joins) {
        debugger;
        this.action.joins = joins;
        new WorkflowActionPropertyList(this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.uid, property.field.customObject, property, joins, property.referencedFilterPath);
    }

    workflowRemoveFilterButtonPressedHandler(uid) {

        let triggerIndex = this.workflow.triggers.findIndex(trigger => trigger.uid === this.trigger.uid);

        this.workflow.triggers[triggerIndex].filters = jQuery.grep(this.workflow.triggers[triggerIndex].filters, function( n, i ) {
            return ( n.uid !== uid );
        });

        for (let filter of this.workflow.triggers[triggerIndex].filters) {
            // remove any filters that have the uid in their andFilters array
            _.remove(filter.andFilters, function (el) {
                return el === uid;
            });

            let index = this.trigger.filters.findIndex(filter => filter.referencedFilterPath === uid);
            if(index !== -1) {
                filter.referencedFilterPath = "";
            }
        }

        this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_TRIGGER_FILTER_REMOVED, this.workflow, this.trigger);

    }

    workflowRemoveTriggerButtonPressedHandler(uid) {

        this.workflow.triggers = jQuery.grep(this.workflow.triggers, function( n, i ) {
            return ( n.uid !== uid );
        });

        this._saveWorkflow().then((data) => {
            this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_DATA_UPDATED, this.workflow);
        });

        if(this.trigger && this.trigger.uid === uid) {
            new WorkflowTriggerType(this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.uid);
        }
    }

    workflowRemoveActionButtonPressedHandler(uid) {
        this.workflow.actions = jQuery.grep(this.workflow.actions, function( n, i ) {
            return ( n.uid !== uid );
        });

        this._saveWorkflow().then((data) => {
            this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_DATA_UPDATED, this.workflow);
        });
    }

    handleWorkflowTriggerPropertyListItemClicked(property) {

        switch (property.fieldType) {
            case 'single_line_text_field':
            case 'multi_line_text_field':
                new SingleLineTextFieldFilterForm($(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, property);
                break;
            case 'number_field':
                new NumberFieldFilterForm($(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.workflow.customObject.internalName, property);
                break;
            case 'date_picker_field':
                new DatePickerFieldFilterForm($(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.workflow.customObject.internalName, property);
                break;
            case 'single_checkbox_field':
                new SingleCheckboxFieldFilterForm($(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.workflow.customObject.internalName, property);
                break;
            case 'dropdown_select_field':
            case 'radio_select_field':
                new DropdownSelectFieldFilterForm($(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.workflow.customObject.internalName, property);
                break;
            case 'multiple_checkbox_field':
                new MultilpleCheckboxFieldFilterForm($(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.workflow.customObject.internalName, property);
                break;
        }
    }

    handleWorkflowActionPropertyListItemClicked(property, joins) {
        debugger;
        this.action.joins = joins;
        new WorkflowActionSetPropertyValueForm($(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.workflow.customObject.internalName, property);
    }

    handleWorkflowEditFilterClicked(uid) {

        let filterIndex = this.trigger.filters.findIndex(filter => filter.uid === uid);
        let customFilter = this.trigger.filters[filterIndex];

        switch (customFilter.fieldType) {
            case 'single_line_text_field':
            case 'multi_line_text_field':
                new EditSingleLineTextFieldFilterForm($(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.workflow.customObject.internalName, customFilter);
                break;
            case 'number_field':
                new EditNumberFieldFilterForm($(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.workflow.customObject.internalName, customFilter);
                break;
            case 'date_picker_field':
                new EditDatePickerFieldFilterForm($(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.workflow.customObject.internalName, customFilter);
                break;
            case 'single_checkbox_field':
                new EditSingleCheckboxFieldFilterForm($(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.workflow.customObject.internalName, customFilter);
                break;
            case 'dropdown_select_field':
            case 'radio_select_field':
                new EditDropdownSelectFieldFilterForm($(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.workflow.customObject.internalName, customFilter);
                break;
            case 'multiple_checkbox_field':
                new EditMultipleCheckboxFieldFilterForm($(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.workflow.customObject.internalName, customFilter);
                break;
        }
    }

    applyCustomFilterButtonPressedHandler(customFilter) {

        debugger;

        if(customFilter.uid) {
            let filterIndex = this.trigger.filters.findIndex(filter => filter.uid === customFilter.uid);
            this.trigger.filters[filterIndex] = customFilter;
        } else {
            customFilter.uid = StringHelper.makeCharId();
            this.trigger.filters.push(customFilter);
        }

        if(customFilter.referencedFilterPath) {
            let index = this.trigger.filters.findIndex(filter => filter.uid === customFilter.referencedFilterPath);
            if(!_.has(this.trigger.filters[index], 'andFilters')) {
                this.trigger.filters[index].andFilters = [];
            }

            if(!_.includes(this.trigger.filters[index].andFilters,customFilter.uid)) {
                this.trigger.filters[index].andFilters.push(customFilter.uid);
            }
        }

        debugger;
        this._saveWorkflow().then((data) => {
            this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_DATA_UPDATED, this.workflow);
            new WorkflowTriggerFilters(this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.trigger);
        });
    }

    applyWorkflowActionButtonPressedHandler(property, formData) {
        debugger;
        this.action.operator = formData.operator;
        this.action.property = property;
        this.action.value = formData.value;

        this._saveWorkflow().then((data) => {
            this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_DATA_UPDATED, this.workflow);
            new WorkflowActionType(this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.uid);
        });
    }

    workflowPublishButtonClickedHandler() {

        if(_.isEmpty(this.workflow.triggers)) {
            swal("Woahhhh!", `You need to setup some triggers before you can publish your workflow`, "error");
            return;
        }

        if(this.workflow.name === '') {
            swal("Woahhhh!", `Don't forget a name for your workflow!`, "error");
            return;
        }

        this._publishWorkflow().then((data) => {
            this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_DATA_UPDATED, this.workflow);
            swal("Hooray!", `Well done, you have successfully published your workflow!`, "success");
        });
    }

    handleRevertButtonClicked() {

        debugger;
        /*this.workflow = _.cloneDeep(this.workflow);*/

        debugger;
        this._saveWorkflow().then((data) => {
            debugger;
            this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_DATA_UPDATED, this.workflow);
        });
    }

    _saveWorkflow() {
        debugger;
        return new Promise((resolve, reject) => {
            const url = Routing.generate('save_workflow', {internalIdentifier: this.portalInternalIdentifier, uid: this.uid});
            $.ajax({
                url,
                method: 'POST',
                data: {'workflow': this.workflow}
            }).then((data, textStatus, jqXHR) => {
                debugger;
                resolve(data);
            }).catch((jqXHR) => {
                debugger;
                const errorData = JSON.parse(jqXHR.responseText);
                errorData.httpCode = jqXHR.status;
                reject(errorData);
            });
        });
    }

    _publishWorkflow() {
        debugger;
        return new Promise((resolve, reject) => {
            const url = Routing.generate('publish_workflow', {internalIdentifier: this.portalInternalIdentifier, uid: this.uid});
            $.ajax({
                url,
                method: 'POST',
                data: {'workflow': this.workflow}
            }).then((data, textStatus, jqXHR) => {
                debugger;
                resolve(data);
            }).catch((jqXHR) => {
                debugger;
                const errorData = JSON.parse(jqXHR.responseText);
                errorData.httpCode = jqXHR.status;
                reject(errorData);
            });
        });
    }

    render() {
        this.$wrapper.html(WorkflowTrigger.markup(this));

        new WorkflowTopBar(this.$wrapper.find(WorkflowTrigger._selectors.topBar), this.globalEventDispatcher, this.portalInternalIdentifier, this.workflow);
        new WorkflowSubBar(this.$wrapper.find(WorkflowTrigger._selectors.subBar), this.globalEventDispatcher, this.portalInternalIdentifier, this.workflow, Settings.PAGES.WORKFLOW_TRIGGERS);
        new WorkflowTriggerType(this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.uid);
        new WorkflowTriggerSelectedTriggers(this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.workflow);
    }

    activatePlugins() {

        $('.js-selectize-single-select').selectize({
            sortField: 'text'
        });
    }

    loadWorkflow() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: Routing.generate('get_workflow', {internalIdentifier: this.portalInternalIdentifier, uid: this.uid}),
            }).then(data => {
                resolve(data);
            })
        });
    }

    workflowNameChangedHandler(workflowName) {
        this.workflow.name = workflowName;
    }

    static markup({portalInternalIdentifier}) {
        return `            
           <div class="js-top-bar"></div>
           <div class="js-sub-bar"></div>
            <div class="t-private-template">                 
                <div class="t-private-template__inner">
                    <div class="t-private-template__sidebar js-workflow-trigger-container"></div>
                    <div class="t-private-template__main js-workflow-trigger-list-container workflow-trigger-list-container" style="background-color: rgb(245, 248, 250);">
                    </div>
                </div>
            </div>  
    `;
    }
}

export default WorkflowTrigger;