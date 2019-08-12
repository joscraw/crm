'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import RecordForm from './RecordForm';
import Routing from "../Routing";
import Settings from "../Settings";
import List from "list.js";

class WorkflowSelectObject {

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
        this.workflow = {};

        this.unbindEvents();

        this.$wrapper.on(
            'click',
            WorkflowSelectObject._selectors.nextButton,
            this.handleNextButtonClicked.bind(this)
        );

        this.render();
    }

    unbindEvents() {

        this.$wrapper.off('click', WorkflowSelectObject._selectors.nextButton);
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            nextButton: '.js-next-button',
            customObjectField: '.js-custom-object:checked',
            customObjectForm: '.custom-object-form'
        }
    }

    handleNextButtonClicked(e) {

        debugger;
        let customObjectId = $(WorkflowSelectObject._selectors.customObjectField).val();

        let data = {};

        data.customObjectId = customObjectId;

        this.addCustomObjectToWorkflow(data).then((data) => {

            debugger;
            let workflow = data.data;

            window.location = Routing.generate('workflow_trigger', {internalIdentifier: this.portalInternalIdentifier, uid: workflow.uid});

        });

    }

    addCustomObjectToWorkflow(data) {

        return new Promise( (resolve, reject) => {

            const url = Routing.generate('workflow_add_custom_object', {internalIdentifier: this.portalInternalIdentifier, uid: this.uid});

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

    render() {
        debugger;
        this.$wrapper.html(WorkflowSelectObject.markup(this));

        this.loadWorkflow().then(data => {
            this.workflow = data.data;
        });

        this.loadCustomObjects().then(data => {
            debugger;
            this.renderCustomObjectForm(data);
        })
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
        let customObjects = this.customObjects = data.data.custom_objects;

        let options = {
            valueNames: [ 'label' ],
            // Since there are no elements in the list, this will be used as template.
            item: '<div class="form-check"><input class="form-check-input js-custom-object" type="radio" name="customObject" id="" value=""><label class="form-check-label label" for=""></label></div>'
        };

        new List('listCustomObjects', options, customObjects);

        $( `#listCustomObjects input[type="radio"]`).each((index, element) => {
            $(element).attr('data-label', customObjects[index].label);
            $(element).attr('value', customObjects[index].id);
            $(element).attr('data-custom-object-id', customObjects[index].id);
            $(element).attr('id', `customObject-${customObjects[index].id}`);
            $(element).next('label').attr('for', `customObject-${customObjects[index].id}`);

        });

        debugger;

        if(this.workflow.customObject) {
            debugger;
            let index = _.findIndex(customObjects, (customObject) => { return customObject.id === this.workflow.customObject.id });
            $( `#listCustomObjects input[type="radio"]`).eq(index).prop('checked', true);
        } else {
            debugger;
            $( `#listCustomObjects input[type="radio"]`).first().prop('checked', true);
        }
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

            <div class="c-report-select-custom-object">
                 <nav class="navbar navbar-expand-sm l-top-bar justify-content-end c-report-widget__nav">
                    <a class="btn btn-link" style="color:#FFF" data-bypass="true" href="${Routing.generate('form_settings', {internalIdentifier: portalInternalIdentifier})}" role="button"><i class="fa fa-angle-left" aria-hidden="true"></i> Back to forms</a>
                    <button class="btn btn-lg btn-secondary ml-auto js-next-button">Next</button> 
                 </nav> 
                 
                 <div class="container">
                     <div class="row c-report-widget__header">
                         <div class="col-md-12" align="center">
                             <h2>Select an object</h2>
                         </div>
                     </div>
                     
                     <div class="card card--center c-report-widget__custom-object-card">
                         <div class="card-body">
                             <div id="listCustomObjects">
                                <div class="input-group c-search-control">
                                    <input class="form-control c-search-control__input search" type="search" placeholder="Search...">
                                    <span class="c-search-control__foreground"><i class="fa fa-search"></i></span>
                                </div>
                                <div class="list c-report-widget__list"></div>
                             </div>                 
                         </div>
                     </div> 
                </div>            
            </div>
           
    `;
    }
}

export default WorkflowSelectObject;