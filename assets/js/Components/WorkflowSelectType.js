'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import RecordForm from './RecordForm';
import Routing from "../Routing";
import Settings from "../Settings";
import List from "list.js";

class WorkflowSelectType {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;

        this.unbindEvents();

        this.$wrapper.on(
            'click',
            WorkflowSelectType._selectors.workflowStartButton,
            this.handleStartButtonClicked.bind(this)
        );
        this.render();
    }

    unbindEvents() {

        this.$wrapper.off('click', WorkflowSelectType._selectors.formsStartButton);
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            workflowStartButton: '.js-workflow-start-button',
            workflowTypeChecked: '.js-workflow-type-list-item:checked',
        }
    }

    handleStartButtonClicked(e) {

        debugger;
        let workflowType = $(WorkflowSelectType._selectors.workflowTypeChecked).val();

        this.initializeWorkflow(workflowType).then((data) => {

            debugger;
            let workflow = data.data;

            window.location = Routing.generate('workflow_object', {internalIdentifier: this.portalInternalIdentifier, uid: workflow.uid});

        });

    }

    initializeWorkflow(workflowType) {

        return new Promise( (resolve, reject) => {

            const url = Routing.generate('initialize_workflow', {internalIdentifier: this.portalInternalIdentifier});

            $.ajax({
                url,
                method: 'POST',
                data: {workflowType: workflowType},
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
        this.$wrapper.html(WorkflowSelectType.markup(this));

        this.loadWorkflowTypes().then(data => {
            debugger;
            this.renderWorkflowTypes(data);
        });
    }

    renderWorkflowTypes(data) {

        debugger;
        let types = data.data;

        let options = {
            valueNames: [ 'label' ],
            // Since there are no elements in the list, this will be used as template.
            item: '<div class="form-check"><input class="form-check-input js-workflow-type-list-item" type="radio" name="workflowType" id="" value=""><label class="form-check-label label" for=""></label></div>'
        };

        new List('listWorkflowTypes', options, types);

        $( `#listWorkflowTypes .js-workflow-type-list-item` ).each((index, element) => {
            $(element).attr('data-workflow-type', types[index].name);
            $(element).attr('value', types[index].name);
            $(element).attr('id', types[index].name);
            $(element).next('label').attr('for', types[index].name);
        });

        $( `#listWorkflowTypes input[type="radio"]`).first().prop('checked', true);
    }

    loadWorkflowTypes() {
        return new Promise((resolve, reject) => {
            let url = Routing.generate('' +
                'workflow_types', {internalIdentifier: this.portalInternalIdentifier});

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
                    <a class="btn btn-link" style="color:#FFF" data-bypass="true" href="${Routing.generate('workflow_settings', {internalIdentifier: portalInternalIdentifier})}" role="button"><i class="fa fa-angle-left" aria-hidden="true"></i> Back to workflows</a>
                    <button class="btn btn-lg btn-secondary ml-auto js-workflow-start-button">Start</button> 
                 </nav> 
                 
                 <div class="container">
                     <div class="row c-report-widget__header">
                         <div class="col-md-12" align="center">
                             <h2>Select a workflow type</h2>
                         </div>
                     </div>
                     
                     <div class="card card--center c-report-widget__custom-object-card">
                         <div class="card-body">
                             <div id="listWorkflowTypes">
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

export default WorkflowSelectType;