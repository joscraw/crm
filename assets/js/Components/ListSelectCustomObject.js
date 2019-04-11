'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import RecordForm from './RecordForm';
import Routing from "../Routing";
import Settings from "../Settings";
import List from "list.js";

class ListSelectCustomObject {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param customObject
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObject = null) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjects = null;
        this.customObject = customObject;

        this.unbindEvents();

        this.bindEvents();

        this.$wrapper.on(
            'click',
            ListSelectCustomObject._selectors.advanceToListPropertiesViewButton,
            this.handleAdvanceToListPropertiesViewButtonClicked.bind(this)
        );


        this.render();
    }

    unbindEvents() {

        this.$wrapper.off('click', ListSelectCustomObject._selectors.backToSelectListTypeButton);

        this.$wrapper.off('click', ListSelectCustomObject._selectors.advanceToListPropertiesViewButton);
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            advanceToListPropertiesViewButton: '.js-advance-to-list-properties-view-button',
            backToSelectListTypeButton: '.js-back-to-select-list-type-button',
            customObjectField: '.js-custom-object:checked',
            customObjectForm: '.custom-object-form'
        }
    }

    bindEvents() {

        this.$wrapper.on(
            'click',
            ListSelectCustomObject._selectors.backToSelectListTypeButton,
            this.handleBackToSelectListTypeButtonClicked.bind(this)
        );

    }

    handleAdvanceToListPropertiesViewButtonClicked(e) {

        debugger;
        let customObjectField = this.$wrapper.find(ListSelectCustomObject._selectors.customObjectField);
        let customObjectId = customObjectField.val();

        let customObject = this.customObjects.filter(customObject => {
            return parseInt(customObject.id) === parseInt(customObjectId);
        });

        this.globalEventDispatcher.publish(Settings.Events.ADVANCE_TO_LIST_PROPERTIES_VIEW_BUTTON_CLICKED, customObject[0]);
    }


    handleBackToSelectListTypeButtonClicked(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        debugger;

        this.globalEventDispatcher.publish(Settings.Events.LIST_BACK_TO_SELECT_LIST_TYPE_BUTTON_CLICKED);

    }

    render() {
        debugger;
        this.$wrapper.html(ListSelectCustomObject.markup(this));

        this.loadCustomObjects().then(data => {
            debugger;
            this.renderCustomObjectForm(data);
        })
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

        if(this.customObject) {

            let index = _.findIndex(customObjects, (customObject) => { return customObject.id === this.customObject.id });

            $( `#listCustomObjects input[type="radio"]`).eq(index).prop('checked', true);

        } else {

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
                    <button type="button" style="color: #FFF" class="btn btn-link js-back-to-select-list-type-button"><i class="fa fa-angle-left" aria-hidden="true"></i> Back</button>
                    <button class="btn btn-lg btn-secondary ml-auto js-advance-to-list-properties-view-button">Next</button> 
                 </nav> 
                 
                 <div class="container">
                     <div class="row c-report-widget__header">
                         <div class="col-md-12" align="center">
                             <h2>Select a custom object</h2>
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

export default ListSelectCustomObject;