'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import RecordForm from './RecordForm';
import Routing from "../Routing";
import Settings from "../Settings";
import List from "list.js";

class ReportSelectCustomObject {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName) {
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;


        this.$wrapper.on(
            'click',
            ReportSelectCustomObject._selectors.addCustomObjectButton,
            this.handleAddCustomObjectButtonClicked.bind(this)
        );


        this.render();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            addCustomObjectButton: '.js-select-custom-object-button',
            customObjectField: 'input[name="customObject"]:checked',

            customObjectForm: '.custom-object-form'
        }
    }

    handleAddCustomObjectButtonClicked(e) {

        let customObjectField = this.$wrapper.find(ReportSelectCustomObject._selectors.customObjectField);
        let customObject = customObjectField.val();

        $(ReportSelectCustomObject._selectors.modal).modal('hide');

        this.globalEventDispatcher.publish(Settings.Events.CUSTOM_OBJECT_FOR_REPORT_SELECTED, customObject);
    }

    render() {
        this.$wrapper.html(ReportSelectCustomObject.markup(this));

        this.loadCustomObjects().then(data => {
            this.renderCustomObjectForm(data);
        })
    }

    renderCustomObjectForm(data) {

        let customObjects = data.data.custom_objects;

        let options = {
            valueNames: [ 'label' ],
            // Since there are no elements in the list, this will be used as template.
            item: '<div class="form-check"><input class="form-check-input" type="radio" name="customObject" id="" value=""><label class="form-check-label label" for=""></label></div>'
        };

        new List('listCustomObjects', options, customObjects);

        $( `#listCustomObjects input[type="radio"]`).each((index, element) => {
            $(element).attr('data-label', customObjects[index].label);
            $(element).attr('data-custom-object-id', customObjects[index].id);
            $(element).attr('id', `customObject-${customObjects[index].id}`);
            $(element).next('label').attr('for', `customObject-${customObjects[index].id}`);
        });

        // set the first option checked at least for now
        $( `#listCustomObjects input[type="radio"]`).first().prop('checked', true);

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
                 <nav class="navbar navbar-expand-sm l-top-bar justify-content-end c-report-select-custom-object__nav">
                    <a class="btn btn-link" style="color:#FFF" data-bypass="true" href="${Routing.generate('report_list', {internalIdentifier: portalInternalIdentifier})}" role="button"><i class="fa fa-angle-left" aria-hidden="true"></i> Back to reports</a>
                    <button class="btn btn-lg btn-secondary ml-auto js-select-custom-object-button">Next</button> 
                 </nav> 
                 
                 <div class="container">
                     <div class="row c-report-select-custom-object__header">
                         <div class="col-md-12" align="center">
                             <h2>Select a custom object</h2>
                         </div>
                     </div>
                     
                     <div class="card card--center">
                         <div class="card-body">
                             <div id="listCustomObjects">
                                <div class="input-group c-search-control">
                                    <input class="form-control c-search-control__input search" type="search" placeholder="Search...">
                                    <span class="c-search-control__foreground"><i class="fa fa-search"></i></span>
                                </div>
                                <div class="list c-report-select-custom-object__list"></div>
                             </div>                 
                         </div>
                     </div> 
                </div>            
            </div>
    `;
    }
}

export default ReportSelectCustomObject;