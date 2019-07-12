'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import RecordForm from './RecordForm';
import Routing from "../Routing";
import Settings from "../Settings";
import List from "list.js";
import WorkflowTriggerList from "./WorkflowTriggerList";

class WorkflowTrigger {

    /**
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

        this.unbindEvents();

        this.$wrapper.on(
            'click',
            WorkflowTrigger._selectors.formsStartButton,
            this.handleStartButtonClicked.bind(this)
        );

        this.$wrapper.on(
            'click',
            WorkflowTrigger._selectors.addItem,
            this.handleAddItemButtonClick.bind(this)
        );

        this.$wrapper.on(
            'change',
            WorkflowTrigger._selectors.workflowTriggerFormField,
            this.handleFieldTypeChange.bind(this)
        );

        this.$wrapper.on(
            'change',
            WorkflowTrigger._selectors.customObject,
            this.handleCustomObjectChange.bind(this)
        );

        this.$wrapper.on(
            'change',
            WorkflowTrigger._selectors.property,
            this.handlePropertyChange.bind(this)
        );

        this.$wrapper.on('change',
            WorkflowTrigger._selectors.condition,
            this.handleConditionChange.bind(this)
        );

        this.$wrapper.on(
            'submit',
            WorkflowTrigger._selectors.workflowTriggerForm,
            this.handleFormSubmit.bind(this)
        );

        this.render();
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
            workflowTriggerFormContainer: '.js-workflow-trigger-form-container',
            addItem: '.js-addItem',
            cancelItem: '.js-cancel',
            removeItem: '.js-removeItem',
            workflowTriggerFormField: '.js-workflow-trigger',
            customObject: '.js-custom-object',
            property: '.js-property',
            condition: '.js-condition',
            workflowTriggerForm: '.js-workflow-trigger-form',
            workflowTriggerListContainer: '.js-workflow-trigger-list-container',

            formsStartButton: '.js-forms-start-button',
            customObjectField: '.js-custom-object:checked',
            customObjectForm: '.custom-object-form'
        }
    }

    handleAddItemButtonClick(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        /*this.$wrapper.find(WorkflowTrigger._selectors.addItem).addClass('d-none');*/

        let $parentContainer = $('.js-parent-container');
        let index = $parentContainer.children('.js-child-item').length;
        let template = $parentContainer.data('template');
        let tpl = eval('`'+template+'`');
        let $container = $('<li>').addClass('list-group-item js-child-item border-0');
        $container.append(tpl);
        $parentContainer.append($container);

        $container.find('.js-selectize-single-select').selectize({
            sortField: 'text'
        });
    }

    handleFieldTypeChange(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        const formData = {};
        formData[$(e.target).attr('name')] = $(e.target).val();
        formData['skip_validation'] = true;

        this._onChange(formData).then((data) => {}).catch((errorData) => {

            debugger;


            this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerFormContainer).html(errorData.formMarkup);
            this.activatePlugins();

        });
    }

    handleFormSubmit(e) {

        debugger;

        if(e.cancelable) {
            e.preventDefault();
        }

        const $form = $(e.currentTarget);
        let formData = new FormData($form.get(0));

        this._saveTrigger(formData).then((data) => {

            this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_TRIGGER_SAVED);

            this.loadForm();

   /*         toastr.options.showMethod = "slideDown";
            toastr.options.hideMethod = "slideUp";
            toastr.options.preventDuplicates = true;*/


            /*toastr.options = {
                "closeButton": false,
                "debug": false,
                "newestOnTop": false,
                "progressBar": false,
                "positionClass": "js-top-bar",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut",
            };

            toastr.success('Please select a question', 'Error!');*/

            toastr.options.positionClass = 't-private-template';

            toastr.success('success', {positionClass : "t-private-template"});

        }).catch((errorData) => {});
    }

    handlePropertyChange(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        const formData = {};

        formData[$(e.target).attr('name')] = $(e.target).val();

        formData[$(WorkflowTrigger._selectors.customObject).attr('name')] = $(WorkflowTrigger._selectors.customObject).val();
        formData[$(WorkflowTrigger._selectors.workflowTriggerFormField).attr('name')] = $(WorkflowTrigger._selectors.workflowTriggerFormField).val();
        formData[$(WorkflowTrigger._selectors.property).attr('name')] = $(WorkflowTrigger._selectors.property).val();
        debugger;
        formData['skip_validation'] = true;
        this._onChange(formData).then((data) => {}).catch((errorData) => {

            debugger;

            this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerFormContainer).html(errorData.formMarkup);

            /* $('.js-selectize-search-result-properties-container').replaceWith(
                 // ... with the returned one from the AJAX response.
                 $(errorData.formMarkup).find('.js-selectize-search-result-properties-container')
             );*/

            this.activatePlugins();
        });

    }

    handleConditionChange(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        const formData = {};

        formData[$(e.target).attr('name')] = $(e.target).val();
        formData[$(WorkflowTrigger._selectors.condition).attr('name')] = $(WorkflowTrigger._selectors.condition).val();
        formData[$(WorkflowTrigger._selectors.customObject).attr('name')] = $(WorkflowTrigger._selectors.customObject).val();
        formData[$(WorkflowTrigger._selectors.workflowTriggerFormField).attr('name')] = $(WorkflowTrigger._selectors.workflowTriggerFormField).val();
        formData[$(WorkflowTrigger._selectors.property).attr('name')] = $(WorkflowTrigger._selectors.property).val();

        debugger;
        formData['skip_validation'] = true;
        this._onChange(formData).then((data) => {}).catch((errorData) => {

            debugger;

            this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerFormContainer).html(errorData.formMarkup);

            /* $('.js-selectize-search-result-properties-container').replaceWith(
                 // ... with the returned one from the AJAX response.
                 $(errorData.formMarkup).find('.js-selectize-search-result-properties-container')
             );*/

            this.activatePlugins();
        });

    }

    handleCustomObjectChange(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        const formData = {};

        formData[$(e.target).attr('name')] = $(e.target).val();


        formData[$(WorkflowTrigger._selectors.customObject).attr('name')] = $(WorkflowTrigger._selectors.customObject).val();
        formData[$(WorkflowTrigger._selectors.workflowTriggerFormField).attr('name')] = $(WorkflowTrigger._selectors.workflowTriggerFormField).val();
        debugger;
        formData['skip_validation'] = true;
        this._onChange(formData).then((data) => {}).catch((errorData) => {

            debugger;

            this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerFormContainer).html(errorData.formMarkup);

           /* $('.js-selectize-search-result-properties-container').replaceWith(
                // ... with the returned one from the AJAX response.
                $(errorData.formMarkup).find('.js-selectize-search-result-properties-container')
            );*/

            this.activatePlugins();
        });

    }

    _onChange(data) {
        return new Promise((resolve, reject) => {

            const url = Routing.generate('submit_workflow_trigger_form', {internalIdentifier: this.portalInternalIdentifier, uid: this.uid});

            $.ajax({
                url,
                method: 'POST',
                data: data
            }).then((data, textStatus, jqXHR) => {
                resolve(data);
            }).catch((jqXHR) => {
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }

    /**
     * @param data
     * @return {Promise<any>}
     * @private
     */
    _saveTrigger(data) {

        return new Promise( (resolve, reject) => {

            const url = Routing.generate('submit_workflow_trigger_form', {internalIdentifier: this.portalInternalIdentifier, uid: this.uid});

            $.ajax({
                url,
                method: 'POST',
                data: data,
                processData: false,
                contentType: false
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

    handleAdvanceToReportPropertiesViewButtonClicked(e) {

        debugger;
        let customObjectField = this.$wrapper.find(ReportSelectCustomObject._selectors.customObjectField);
        let customObjectId = customObjectField.val();


        let customObject = this.customObjects.filter(customObject => {
            return parseInt(customObject.id) === parseInt(customObjectId);
        });

        debugger;

        this.globalEventDispatcher.publish(Settings.Events.ADVANCE_TO_REPORT_PROPERTIES_VIEW_BUTTON_CLICKED, customObject[0]);
    }

    render() {
        this.$wrapper.html(WorkflowTrigger.markup(this));

        this.loadForm();

        new WorkflowTriggerList(this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.uid);

    }

    activatePlugins() {

        $('.js-selectize-single-select').selectize({
            sortField: 'text'
        });
    }

    loadForm() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: Routing.generate('get_workflow_trigger_form', {internalIdentifier: this.portalInternalIdentifier, uid: this.uid}),
            }).then(data => {
                this.$wrapper.find(WorkflowTrigger._selectors.workflowTriggerFormContainer).html(data.formMarkup);
                this.activatePlugins();
                resolve();
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
           <div class="js-top-bar">
               <nav class="navbar navbar-expand-sm l-top-bar justify-content-end">
                    <a class="btn btn-link" style="color:#FFF" data-bypass="true" href="${Routing.generate('workflows', {internalIdentifier: portalInternalIdentifier})}" role="button"><i class="fa fa-angle-left" aria-hidden="true"></i> Back to workflows</a>
                    <button class="btn btn-lg btn-secondary ml-auto js-forms-start-button">Start</button> 
                 </nav> 
            </div>
            <div class="t-private-template">                 
                <div class="t-private-template__inner">
                    <div class="t-private-template__sidebar js-workflow-trigger-form-container"></div>
                    <div class="t-private-template__sidebar js-edit-field-form d-none"></div>
                    <div class="t-private-template__main js-workflow-trigger-list-container" style="background-color: rgb(245, 248, 250);"></div>
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