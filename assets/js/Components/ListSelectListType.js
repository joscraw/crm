'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import RecordForm from './RecordForm';
import Routing from "../Routing";
import Settings from "../Settings";
import List from "list.js";

class ListSelectListType {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param listType
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, listType = null) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.listTypes = null;
        this.listType = listType;

        this.unbindEvents();

        this.$wrapper.on(
            'click',
            ListSelectListType._selectors.advanceToListSelectCustomObjectButton,
            this.handleAdvanceToListSelectCustomObjectViewButtonClicked.bind(this)
        );


        this.render();
    }

    unbindEvents() {

        this.$wrapper.off('click', ListSelectListType._selectors.advanceToListSelectCustomObjectButton);

    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            advanceToListSelectCustomObjectButton: '.js-advance-to-list-select-custom-object-button',
            listTypeField: '.js-list-type:checked',
            customObjectForm: '.custom-object-form'
        }
    }

    handleAdvanceToListSelectCustomObjectViewButtonClicked(e) {

        debugger;
        let listTypeField = this.$wrapper.find(ListSelectListType._selectors.listTypeField);
        let listTypeName = listTypeField.val();


        let listType = this.listTypes.filter(listType => {
            return listType.name === listTypeName;
        });

        debugger;

        this.globalEventDispatcher.publish(Settings.Events.ADVANCE_TO_LIST_SELECT_CUSTOM_OBJECT_VIEW_BUTTON_CLICKED, listType[0]);
    }

    render() {
        debugger;
        this.$wrapper.html(ListSelectListType.markup(this));

        this.loadListTypes().then(data => {
            debugger;
            this.renderListTypeForm(data);

            $('[data-toggle="tooltip"]').tooltip();
        })
    }

    renderListTypeForm(data) {

        debugger;
        let listTypes = this.listTypes = data.data.list_types;

        let options = {
            valueNames: [ 'label' ],
            // Since there are no elements in the list, this will be used as template.
            item: '<div class="form-check"><input class="form-check-input js-list-type" type="radio" name="customObject" id="" value=""><label class="form-check-label label" for=""></label></div>'
        };

        new List('listTypes', options, listTypes);

        $( `#listTypes input[type="radio"]`).each((index, element) => {
            $(element).attr('value', listTypes[index].name);
            $(element).attr('id', listTypes[index].name);
            $(element).next('label').attr('for', listTypes[index].name);

        });

        if(this.listType) {
            let index = _.findIndex(listTypes, (listType) => { return listType.name === this.listType.name });
            $( `#listTypes input[type="radio"]`).eq(index).prop('checked', true);
        } else {
            $( `#listTypes input[type="radio"]`).first().prop('checked', true);
        }


    }

    loadListTypes() {
        return new Promise((resolve, reject) => {
            let url = Routing.generate('' +
                'get_list_types', {internalIdentifier: this.portalInternalIdentifier});

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
                    <a class="btn btn-link" style="color:#FFF" data-bypass="true" href="${Routing.generate('list_settings', {internalIdentifier: portalInternalIdentifier})}" role="button"><i class="fa fa-angle-left" aria-hidden="true"></i> Back to lists</a>
                    <button class="btn btn-lg btn-secondary ml-auto js-advance-to-list-select-custom-object-button">Next</button> 
                 </nav> 
                 
                 <div class="container">
                     <div class="row c-report-widget__header">
                         <div class="col-md-12" align="center">
                             <h2>Select list type <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="A dynamic list will update continuously based on your filters. A static list will only be modified if you manually add or remove records."></i></h2>
                         </div>
                     </div>
                     
                     <div class="card card--center c-report-widget__custom-object-card">
                         <div class="card-body">
                             <div id="listTypes">
                                <div class="list c-report-widget__list"></div>
                             </div>                 
                         </div>
                     </div> 
                </div>            
            </div>
    `;
    }
}

export default ListSelectListType;