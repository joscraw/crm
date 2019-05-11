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

class FormEditorEditForm {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, uid) {

        debugger;

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.uid = uid;
        this.formName = '';
        this.data = [];


       /* this.unbindEvents();

        this.bindEvents();

        this.globalEventDispatcher.singleSubscribe(
            Settings.Events.LIST_BACK_BUTTON_CLICKED,
            this.handleBackButtonClicked.bind(this)
        );
*/

        this.globalEventDispatcher.subscribe(
            Settings.Events.FORM_EDITOR_PROPERTY_LIST_ITEM_CLICKED,
            this.handlePropertyListItemClicked.bind(this)
        );

        this.render();

    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {

            listSelectedColumnsContainer: '.js-list-selected-columns-container',
            listPropertyListContainer: '.js-property-list',
            listSelectedColumnsCountContainer: '.js-list-selected-columns-count-container',
            listBackToSelectCustomObjectButton: '.js-back-to-select-custom-object-button',
            listAdvanceToFiltersView: '.js-advance-to-filters-view',
            formContainer: '.js-form'

        }
    }

    bindEvents() {

        this.$wrapper.on(
            'click',
            ListProperties._selectors.listBackToSelectCustomObjectButton,
            this.handleListBackToSelectCustomObjectButton.bind(this)
        );

        this.$wrapper.on(
            'click',
            ListProperties._selectors.listAdvanceToFiltersView,
            this.handleListAdvanceToFiltersViewButtonClicked.bind(this)
        );

    }

    unbindEvents() {

        this.$wrapper.off('click', ListPropertyList._selectors.listBackToSelectCustomObjectButton);
        this.$wrapper.off('click', ListProperties._selectors.listAdvanceToFiltersView);
    }

    handleListAdvanceToFiltersViewButtonClicked(e) {

        let properties = this.getPropertiesFromData();

        if(Object.keys(properties).length === 0) {

            swal("Yikes!!!", "You need at least one property.", "warning");

            return;
        }

        debugger;
        this.globalEventDispatcher.publish(Settings.Events.LIST_ADVANCE_TO_FILTERS_VIEW_BUTTON_CLICKED);

    }

    handlePropertyListItemClicked(property) {

        let uID = StringHelper.makeCharId();
        _.set(property, 'uID', uID);

        this.data.push(property);

        this._saveFormData();

        this.globalEventDispatcher.publish(Settings.Events.FORM_EDITOR_PROPERTY_LIST_ITEM_ADDED, this.data);
    }


    _saveFormData() {

        debugger;
        this._saveForm().then((data) => {

            this.globalEventDispatcher.publish(Settings.Events.FORM_EDITOR_DATA_SAVED, this.data);

        }).catch((errorData) => {});
    }

    _saveForm() {

        debugger;
        return new Promise((resolve, reject) => {
            debugger;
            const url = Routing.generate('save_form', {internalIdentifier: this.portalInternalIdentifier, uid: this.uid});

            $.ajax({
                url,
                method: 'POST',
                data: {'data': this.data, formName: this.formName}
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


    getPropertiesFromData() {

        let properties = {};
        function search(data) {

            for(let key in data) {

                if(key !== 'filters' && !_.has(data[key], 'uID')) {

                    search(data[key]);

                } else if(key === 'filters'){

                    continue;

                } else {

                    _.set(properties, key, data[key]);

                }
            }
        }

        debugger;
        search(this.data);

        return properties;
    }

    handleBackButtonClicked() {

        new ListPropertyList($(ListProperties._selectors.listPropertyListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, null, [], this.data);

    }

    handleListBackToSelectCustomObjectButton(e) {

        this.globalEventDispatcher.publish(Settings.Events.LIST_BACK_TO_SELECT_CUSTOM_OBJECT_BUTTON_PRESSED, this.data);

    }

    render() {

        this.$wrapper.html(FormEditorEditForm.markup(this));

        new FormEditorPropertyList($(FormEditorEditForm._selectors.listPropertyListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.uid, this.data);

        new FormEditorFormPreview(this.$wrapper.find(FormEditorEditForm._selectors.formContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.data, this.columnOrder, this.uid);

        /*
        new ListSelectedColumnsCount(this.$wrapper.find(ListProperties._selectors.listSelectedColumnsCountContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.data, this.columnOrder);

*/
    }

    static markup({portalInternalIdentifier}) {
        return `

            <nav class="navbar fixed-top navbar-expand-sm l-top-bar justify-content-end">
                <a class="btn btn-link" style="color:#FFF" data-bypass="true" href="${Routing.generate('form_settings', {internalIdentifier: portalInternalIdentifier})}" role="button"><i class="fa fa-angle-left" aria-hidden="true"></i> Back to forms</a>
                <button class="btn btn-lg btn-secondary ml-auto js-advance-to-report-properties-view-button">Next</button> 
            </nav> 
            <div class="t-private-template">                 
                <div class="t-private-template__inner">
                    <div class="t-private-template__sidebar js-property-list"></div>
                    <div class="t-private-template__main js-form" style="background-color: rgb(245, 248, 250);"></div>
                </div>
            </div>
           
    `;
    }
}

export default FormEditorEditForm;