'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import RecordForm from './RecordForm';
import Routing from "../Routing";
import Settings from "../Settings";
import List from "list.js";
import WorkflowTriggerList from "./WorkflowTriggerList";
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
        this.data = {};
        this.trigger = null;
        this.workflow = {};

        this.unbindEvents();

        this.$wrapper.on(
            'click',
            WorkflowTrigger._selectors.formsStartButton,
            this.handleStartButtonClicked.bind(this)
        );

        this.$wrapper.on(
            'click',
            WorkflowTrigger._selectors.addTriggerButton,
            this.handleAddTriggerButtonClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.WORKFLOW_TRIGGER_LIST_ITEM_CLICKED,
            this.handleTriggerListItemClicked.bind(this)
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
            Settings.Events.WORKFLOW_TRIGGER_BACK_BUTTON_CLICKED,
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
            Settings.Events.APPLY_CUSTOM_FILTER_BUTTON_PRESSED,
            this.applyCustomFilterButtonPressedHandler.bind(this)
        );

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
                Settings.Events.WORKFLOW_TRIGGER_ADD_OR_FILTER_BUTTON_PRESSED,
                this.reportAddOrFilterButtonPressedHandler.bind(this)
            ));

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
                Settings.Events.WORKFLOW_TRIGGER_ADD_FILTER_BUTTON_PRESSED,
                this.reportAddFilterButtonPressedHandler.bind(this)
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


        this.loadWorkflow().then((data) => {
            debugger;
            this.workflow = data.data;

            this.render();
        });

    }

    unbindEvents() {

        this.$wrapper.off('click', WorkflowTrigger._selectors.formsStartButton);
        this.$wrapper.off('click', WorkflowTrigger._selectors.addItem);
        this.$wrapper.off('change', WorkflowTrigger._selectors.workflowTriggerFormField);
        this.$wrapper.off('change', WorkflowTrigger._selectors.customObject);
        this.$wrapper.off('change', WorkflowTrigger._selectors.property);
        this.$wrapper.off('change', WorkflowTrigger._selectors.condition);
        this.$wrapper.off('submit', WorkflowTrigger._selectors.workflowTriggerForm);
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {

            workflowTriggerContainer: '.js-workflow-trigger-container',

            workflowTriggerTypeContainer: '.js-workflow-trigger-type-container',
            workflowTriggerCustomObjectContainer: '.js-workflow-trigger-custom-object-container',
            workflowTriggerPropertyListContainer: '.js-workflow-trigger-property-list-container',
            workflowTriggerFiltersContainer: '.js-workflow-trigger-filters-container',
            addTriggerButton: '.js-add-trigger-button',
            workflowTriggerListContainer: '.js-workflow-trigger-list-container',
            topBar: '.js-top-bar',

            workflowTriggerFormContainer: '.js-workflow-trigger-form-container',
            addItem: '.js-addItem',
            cancelItem: '.js-cancel',
            removeItem: '.js-removeItem',
            workflowTriggerFormField: '.js-workflow-trigger',
            customObject: '.js-custom-object',
            property: '.js-property',
            condition: '.js-condition',
            workflowTriggerForm: '.js-workflow-trigger-form',

            formsStartButton: '.js-forms-start-button',
            customObjectField: '.js-custom-object:checked',
            customObjectForm: '.custom-object-form'
        }
    }

    handleTriggerListItemClicked(trigger) {
        debugger;
        this.trigger = trigger;
        this.trigger.uid = this.trigger.uid === null ? StringHelper.makeCharId() : this.trigger.uid;
        this.workflow.triggers.push(this.trigger);

        switch (trigger.name) {
            case 'property_trigger':
                new WorkflowTriggerCustomObject(this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.uid, this.trigger);
                break;
        }

        this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_TRIGGER_ADDED, this.workflow);
    }

    reportAddOrFilterButtonPressedHandler(referencedFilterPath) {
        new WorkflowTriggerPropertyList(this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.uid, this.trigger.customObject, null, [], {}, referencedFilterPath);
    }

    handleWorkflowEditTriggerClicked(uid) {
        let index = this.workflow.triggers.findIndex(trigger => trigger.uid === uid);
        this.trigger = this.workflow.triggers[index];
        new WorkflowTriggerFilters(this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.trigger);
    }

    reportAddFilterButtonPressedHandler() {
        debugger;
        new WorkflowTriggerPropertyList(this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.uid, this.trigger.customObject);
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
        }
    }

    handleFilterBackToListButtonClicked() {
        new WorkflowTriggerPropertyList(this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.uid, this.trigger.customObject);
    }

    handleWorkflowTriggerCustomObjectListItemClicked(customObject) {
        this.trigger.customObject = customObject;
        new WorkflowTriggerPropertyList(this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.uid, this.trigger.customObject);
    }

    handleListCustomObjectFilterListItemClicked(property, joins) {
        let propertyPath = property.joins.join('.');

        if(!_.has(this.data, propertyPath)) {
            _.set(this.data, propertyPath, {});
        }

        new WorkflowTriggerPropertyList(this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.uid, property.field.customObject, property, joins, this.data, property.referencedFilterPath);
    }

    workflowRemoveFilterButtonPressedHandler(uid) {
        debugger;

        let triggerIndex = this.workflow.triggers.findIndex(trigger => trigger.uid === this.trigger.uid);

        this.workflow.triggers[triggerIndex].filters = jQuery.grep(this.workflow.triggers[triggerIndex].filters, function( n, i ) {
            return ( n.uid !== uid );
        });

        debugger;

        for (let filter of this.workflow.triggers[triggerIndex].filters) {
            debugger;
            // remove any filters that have the uid in their andFilters array
            _.remove(filter.andFilters, function (el) {
                return el === uid;
            });

            debugger;
            let index = this.trigger.filters.findIndex(filter => filter.referencedFilterPath === uid);
            if(index !== -1) {
                filter.referencedFilterPath = "";
            }
        }

        this._saveWorkflow();

        this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_TRIGGER_FILTER_REMOVED, this.workflow, this.trigger);

        debugger;
    }

    workflowRemoveTriggerButtonPressedHandler(uid) {

        debugger;

        this.workflow.triggers = jQuery.grep(this.workflow.triggers, function( n, i ) {
            return ( n.uid !== uid );
        });

        debugger;

        this._saveWorkflow();

        this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_TRIGGER_REMOVED, this.workflow);

        if(this.trigger && this.trigger.uid === uid) {
            new WorkflowTriggerType(this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.uid);
        }
    }

    handleWorkflowTriggerPropertyListItemClicked(property) {

        switch (property.fieldType) {
            case 'single_line_text_field':
            case 'multi_line_text_field':
                new SingleLineTextFieldFilterForm($(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, property);
                break;
            case 'number_field':
                new NumberFieldFilterForm($(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.trigger.customObject.internalName, property);
                break;
            case 'date_picker_field':
                new DatePickerFieldFilterForm($(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.trigger.customObject.internalName, property);
                break;
            case 'single_checkbox_field':
                new SingleCheckboxFieldFilterForm($(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.trigger.customObject.internalName, property);
                break;
            case 'dropdown_select_field':
            case 'radio_select_field':
                new DropdownSelectFieldFilterForm($(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.trigger.customObject.internalName, property);
                break;
            case 'multiple_checkbox_field':
                new MultilpleCheckboxFieldFilterForm($(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.trigger.customObject.internalName, property);
                break;
        }
    }

    handleStartButtonClicked(e) {

        debugger;
        let customObjectId = $(FormSelectObject._selectors.customObjectField).val();

        let data = {};

        data.customObjectId = customObjectId;

        this.initializeForm(data).then((data) => {

            let form = data.data;

            window.location = Routing.generate('editor_edit_form', {internalIdentifier: this.portalInternalIdentifier, uid: form.uid});

        });

    }

    applyCustomFilterButtonPressedHandler(customFilter) {

        debugger;




        /*let filterPath = `filters.` + customFilter.joins.join('.') + `.filters`;*/
            /*referencedFilterPath = customFilter.referencedFilterPath.join('.');*/

        customFilter.uid = StringHelper.makeCharId();

        if(customFilter.referencedFilterPath) {
            let index = this.trigger.filters.findIndex(filter => filter.uid === customFilter.referencedFilterPath);
            if(!_.has(this.trigger.filters[index], 'andFilters')) {
                this.trigger.filters[index].andFilters = [];
            }
            this.trigger.filters[index].andFilters.push(customFilter.uid);
        }

        this.trigger.filters.push(customFilter);

        debugger;

        this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_TRIGGER_FILTER_ADDED, this.workflow);

        new WorkflowTriggerFilters(this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.trigger);

    }

    initializeForm(data) {

        return new Promise( (resolve, reject) => {

            const url = Routing.generate('initialize_form', {internalIdentifier: this.portalInternalIdentifier});

            $.ajax({
                url,
                method: 'POST',
                data: data,
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

    handleAddTriggerButtonClicked(e) {

        debugger;

        if(!_.has(this.workflow, 'triggers')) {
            _.set(this.workflow, 'triggers', []);
        }

        this.workflow.triggers.push(this.trigger);

        this._saveWorkflow().then((data) => {
            debugger;
            this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_TRIGGER_ADDED, this.workflow.triggers);
        });
    }

    _saveWorkflow() {
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

    render() {
        this.$wrapper.html(WorkflowTrigger.markup(this));

        new WorkflowTopBar(this.$wrapper.find(WorkflowTrigger._selectors.topBar), this.globalEventDispatcher, this.portalInternalIdentifier, this.workflow);
        /*new WorkflowTriggerList(this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.uid);*/
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

    renderCustomObjectForm(data) {

        debugger;
        let options = {
            valueNames: [ 'label' ],
        };

        new List('listWorkflowTriggers', options);


        $( `#listWorkflowTriggers input[type="radio"]`).first().prop('checked', true);

  /*      $( `#listCustomObjects input[type="radio"]`).each((index, element) => {
            $(element).attr('data-label', customObjects[index].label);
            $(element).attr('value', customObjects[index].id);
            $(element).attr('data-custom-object-id', customObjects[index].id);
            $(element).attr('id', `customObject-${customObjects[index].id}`);
            $(element).next('label').attr('for', `customObject-${customObjects[index].id}`);

        });
*/

      /*  if(this.customObject) {
            debugger;
            let index = _.findIndex(customObjects, (customObject) => { return customObject.id === this.customObject.id });
            $( `#listCustomObjects input[type="radio"]`).eq(index).prop('checked', true);
        } else {
            debugger;
            $( `#listCustomObjects input[type="radio"]`).first().prop('checked', true);
        }
*/

    }

    loadCustomObjects() {
        return new Promise((resolve, reject) => {
            let url = Routing.generate('' +
                'get_custom_objects', {internalIdentifier: this.portalInternalIdentifier});

            $.ajax({
                url: url,
            }).then(data => {
                resolve(data);
            }).catch(jqXHR => {
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }



    static markup({portalInternalIdentifier}) {
        return `            
           <div class="js-top-bar"></div>
            <div class="t-private-template">                 
                <div class="t-private-template__inner">
                    <div class="t-private-template__sidebar js-workflow-trigger-container"></div>
                    <!--<div class="t-private-template__sidebar js-workflow-trigger-custom-object-container d-none"></div>
                    <div class="t-private-template__sidebar js-workflow-trigger-property-list-container d-none"></div>
                    <div class="t-private-template__sidebar js-workflow-trigger-form-container d-none"></div>
                    <div class="t-private-template__sidebar js-workflow-trigger-filters-container d-none"></div>
                    <div class="t-private-template__sidebar js-edit-field-form d-none"></div>-->
                    <div class="t-private-template__main js-workflow-trigger-list-container" style="background-color: rgb(245, 248, 250);">
                    </div>
                </div>
            </div>  
    `;
    }

/*    static markup({portalInternalIdentifier}) {
        return `

            <div class="c-report-select-custom-object">
                 <nav class="navbar navbar-expand-sm l-top-bar justify-content-end c-report-widget__nav">
                    <a class="btn btn-link" style="color:#FFF" data-bypass="true" href="${Routing.generate('workflows', {internalIdentifier: portalInternalIdentifier})}" role="button"><i class="fa fa-angle-left" aria-hidden="true"></i> Back to workflows</a>
                    <button class="btn btn-lg btn-secondary ml-auto js-forms-start-button">Start</button> 
                 </nav> 
                 
                 <div class="container">
                     <div class="row c-report-widget__header">
                         <div class="col-md-12" align="center">
                             <h2>Select a workflow trigger</h2>
                         </div>
                     </div>
                     
                     <div class="card card--center c-report-widget__custom-object-card">
                         <div class="card-body">
                            <div class="js-workflow-trigger-form-container"></div>
                         
                             <!--<div id="listWorkflowTriggers">
                                <div class="input-group c-search-control">
                                    <input class="form-control c-search-control__input search" type="search" placeholder="Search...">
                                    <span class="c-search-control__foreground"><i class="fa fa-search"></i></span>
                                </div>
                                <div class="list c-report-widget__list">
                                <div class="form-check"><input class="form-check-input js-custom-object" type="radio" name="customObject" id="property_based_trigger" value=""><label class="form-check-label label" for="property_based_trigger">Property based trigger</label></div>
                                </div>
                             </div>  -->               
                         </div>
                     </div> 
                </div>            
            </div>
           
    `;
    }*/
}

export default WorkflowTrigger;