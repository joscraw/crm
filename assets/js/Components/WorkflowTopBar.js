'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import swal from 'sweetalert2';
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import FilterList from "./FilterList";
import FilterNavigation from "./FilterNavigation";
import EditSingleLineTextFieldFilterForm from "./EditSingleLineTextFieldFilterForm";
import NumberFieldFilterForm from "./NumberFieldFilterForm";
import EditNumberFieldFilterForm from "./EditNumberFieldFilterForm";
import DatePickerFieldFilterForm from "./DatePickerFieldFilterForm";
import SingleCheckboxFieldFilterForm from "./SingleCheckboxFieldFilterForm";
import EditDatePickerFieldFilterForm from "./EditDatePickerFieldFilterForm";
import EditSingleCheckboxFieldFilterForm from "./EditSingleCheckboxFieldFilterForm";
import DropdownSelectFieldFilterForm from "./DropdownSelectFieldFilterForm";
import EditDropdownSelectFieldFilterForm from "./EditDropdownSelectFieldFilterForm";
import MultilpleCheckboxFieldFilterForm from "./MultilpleCheckboxFieldFilterForm";
import EditMultipleCheckboxFieldFilterForm from "./EditMultipleCheckboxFieldFilterForm";
import ArrayHelper from "../ArrayHelper";
import ReportSelectCustomObject from "./ReportSelectCustomObject";
import ReportPropertyList from "./ReportPropertyList";
import ReportSelectedColumns from "./ReportSelectedColumns";
import ReportSelectedColumnsCount from "./ReportSelectedColumnsCount";
import ReportFilterList from "./ReportFilterList";
import ReportFilters from "./ReportFilters";
import ReportProperties from "./ReportProperties";
import ListPropertyList from "./ListPropertyList";
import ListSelectedColumns from "./ListSelectedColumns";
import ListSelectedColumnsCount from "./ListSelectedColumnsCount";
import FormEditorPropertyList from "./FormEditorPropertyList";
import StringHelper from "../StringHelper";
import FormEditorFormPreview from "./FormEditorFormPreview";
import FormEditorEditFieldForm from "./FormEditorEditFieldForm";
import ContextHelper from "../ContextHelper";

class WorkflowTopBar {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, workflow, publishedWorkflow) {

        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.workflow = workflow;
        this.publishedWorkflow = publishedWorkflow;

        this.unbindEvents();
        this.bindEvents();

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
                Settings.Events.WORKFLOW_TRIGGER_FILTER_ADDED,
                this.handleDataSaved.bind(this)
            ));

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
                Settings.Events.WORKFLOW_DATA_UPDATED,
                this.handleDataUpdated.bind(this)
            ));

        this.render();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            workflowName: '.js-workflow-name',
            publishButton: '.js-publish-button',
            autosaveMessage: '.js-autosave-message',
            revertButton: '.js-revert-button',
            startPauseButton: '.js-start-pause-button'

        }
    }

    bindEvents() {
        this.$wrapper.on('click', WorkflowTopBar._selectors.publishButton, this.handlePublishButtonClicked.bind(this));
        this.$wrapper.on('keyup', WorkflowTopBar._selectors.workflowName, this.handleFormNameChange.bind(this));
        this.$wrapper.on('click', WorkflowTopBar._selectors.revertButton, this.handleRevertButtonClicked.bind(this));
        this.$wrapper.on('click', WorkflowTopBar._selectors.startPauseButton, this.handleStartPauseButtonClicked.bind(this));
    }

    unbindEvents() {
        this.$wrapper.off('click', WorkflowTopBar._selectors.publishButton);
        this.$wrapper.off('keyup', WorkflowTopBar._selectors.workflowName);
        this.$wrapper.off('click', WorkflowTopBar._selectors.revertButton);
        this.$wrapper.off('click', WorkflowTopBar._selectors.startPauseButton);
    }

    render() {
        this.$wrapper.html(WorkflowTopBar.markup(this));
        this.setAutoSaveMessage();
    }

    handleRevertButtonClicked(e) {
        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }
        this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_REVERT_BUTTON_CLICKED);
    }

    handleStartPauseButtonClicked(e) {
        if(e.cancelable) {
            e.preventDefault();
        }
        this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_START_PAUSE_BUTTON_CLICKED);
    }

    handleDataSaved(workflow) {
        debugger;
        this.workflow = workflow;

        this.setAutoSaveMessage();
        this.setPauseSaveButton();
    }

    handleDataUpdated(workflow, publishedWorkflow) {
        debugger;
        this.workflow = workflow;
        this.publishedWorkflow = publishedWorkflow;

        this.setAutoSaveMessage();
        this.setPauseSaveButton();
    }

    setAutoSaveMessage() {
        debugger;
        let autosaveMessage = '';
        if(this.workflow.hash !== this.publishedWorkflow.hash) {
            autosaveMessage = 'Autosaved with unpublished changes <button type="button" class="btn btn-link js-revert-button">revert</button>';

        }
        this.$wrapper.find(WorkflowTopBar._selectors.autosaveMessage).html(autosaveMessage);
    }

    setPauseSaveButton() {
        debugger;

        let buttonText = 'Start Workflow';
        if(!this.workflow.paused) {
            buttonText = 'Pause Workflow';
        }

        this.$wrapper.find(WorkflowTopBar._selectors.startPauseButton).html(buttonText);
    }

    handleFormNameChange(e) {
        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }
        let workflowName = $(e.target).val();
        this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_NAME_CHANGED, workflowName);
    }

    handlePublishButtonClicked(e) {
        if(e.cancelable) {
            e.preventDefault();
        }
        this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_PUBLISH_BUTTON_CLICKED);
    }

    static markup({portalInternalIdentifier, workflow}) {

        return `            
          
             <nav class="navbar navbar-expand-sm l-top-bar justify-content-end">
               <div class="container-fluid">
                        <div class="navbar-collapse collapse dual-nav w-50 order-0">
                            <ul class="navbar-nav">
                                <li class="nav-item">
                                    <a class="btn btn-link" style="color:#FFF" data-bypass="true" href="${Routing.generate('workflow_settings', {internalIdentifier: portalInternalIdentifier})}" role="button"><i class="fa fa-angle-left" aria-hidden="true"></i> Back to workflows</a>
                                </li>
                            </ul>
                        </div>
        
                        <input style="width: 200px; text-align: left !important;" class="form-control navbar-brand mx-auto d-block text-center order-1 w-25 js-workflow-name" type="search" placeholder="Workflow name" aria-label="Search" value="${workflow.name}">
        
                        <div class="navbar-collapse collapse dual-nav w-50 order-3">
                            <ul class="nav navbar-nav ml-auto">
                                <li class="nav-item">
                                    <span style="color: #FFF; margin-right: 20px;" class="js-autosave-message"></span><button class="btn btn-lg btn-light ml-auto js-start-pause-button" style="margin-right:10px">Start Workflow</button>
                                </li>
                                <li class="nav-item">
                                    <button class="btn btn-lg btn-secondary ml-auto js-publish-button">Publish</button>
                                </li>
                            </ul>
                        </div>
                    </div> 
                 </nav> 
    `;
    }
}

export default WorkflowTopBar;