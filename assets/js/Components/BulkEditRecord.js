'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';
import List from 'list.js';
import ColumnSearch from "./ColumnSearch";
import SavedFilterSearch from "./SavedFilterSearch";
import ListFilterNavigation from "./ListFilterNavigation";
import ListPreviewResultsButton from "./ListPreviewResultsButton";
import ListPreviewResultsTable from "./ListPreviewResultsTable";
import BulkEditRecordPropertyList from "./BulkEditRecordPropertyList";
import StringHelper from "../StringHelper";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import NumberFieldFilterForm from "./NumberFieldFilterForm";
import DatePickerFieldFilterForm from "./DatePickerFieldFilterForm";
import SingleCheckboxFieldFilterForm from "./SingleCheckboxFieldFilterForm";
import DropdownSelectFieldFilterForm from "./DropdownSelectFieldFilterForm";
import MultilpleCheckboxFieldFilterForm from "./MultilpleCheckboxFieldFilterForm";
import ListFilterList from "./ListFilterList";
require('jquery-ui-dist/jquery-ui');

class BulkEditRecord {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName) {

        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.customFilter = null;


        this.globalEventDispatcher.subscribe(
            Settings.Events.BULK_EDIT_RECORD_PROPERTY_LIST_ITEM_CLICKED,
            this.handlePropertyListItemClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.FILTER_BACK_TO_LIST_BUTTON_CLICKED,
            this.handleFilterBackToListButtonClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.APPLY_CUSTOM_FILTER_BUTTON_PRESSED,
            this.applyCustomFilterButtonPressedHandler.bind(this)
        );


        this.render()

    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            bulkEditRecordPropertyListContainer: '.js-bulk-edit-record-property-list-container',
            propertyForm: '.js-property-form',
        }
    }


    handleFilterBackToListButtonClicked() {

        debugger;
        this.$wrapper.find(BulkEditRecord._selectors.bulkEditRecordPropertyListContainer).removeClass('d-none');

        if(!this.$wrapper.find(BulkEditRecord._selectors.propertyForm).hasClass('d-none')) {

            this.$wrapper.find(BulkEditRecord._selectors.propertyForm).addClass('d-none');

        }

        new BulkEditRecordPropertyList($(BulkEditRecord._selectors.bulkEditRecordPropertyListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
    }

    applyCustomFilterButtonPressedHandler(customFilter) {

        debugger;

        this.customFilter = customFilter;



        /*this.globalEventDispatcher.publish(Settings.Events.LIST_FILTER_ITEM_ADDED, this.data);*/

    }

    handlePropertyListItemClicked(property) {

        debugger;

        this.$wrapper.find(BulkEditRecord._selectors.bulkEditRecordPropertyListContainer).addClass('d-none');
        this.$wrapper.find(BulkEditRecord._selectors.propertyForm).removeClass('d-none');

        this.renderFilterForm(property);

    }

    renderFilterForm(property) {

        debugger;
        switch (property.fieldType) {
            case 'single_line_text_field':
            case 'multi_line_text_field':
                new SingleLineTextFieldFilterForm(this.$wrapper.find(BulkEditRecord._selectors.propertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property);
                break;
            case 'number_field':
                new NumberFieldFilterForm($(BulkEditRecord._selectors.propertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property);
                break;
            case 'date_picker_field':
                new DatePickerFieldFilterForm($(BulkEditRecord._selectors.propertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property);
                break;
            case 'single_checkbox_field':
                new SingleCheckboxFieldFilterForm($(BulkEditRecord._selectors.propertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property);
                break;
            case 'dropdown_select_field':
            case 'radio_select_field':
                new DropdownSelectFieldFilterForm($(BulkEditRecord._selectors.propertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property);
                break;
            case 'multiple_checkbox_field':
                new MultilpleCheckboxFieldFilterForm($(BulkEditRecord._selectors.propertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property);
                break;
        }

    }

    handleApplySavedFilterButtonClicked(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        this.globalEventDispatcher.publish(Settings.Events.APPLY_SAVED_FILTER_BUTTON_CLICKED, this.savedfilterToApply.customFilters);

        debugger;
    }

    render() {

        this.$wrapper.html(BulkEditRecord.markup(this));

        new BulkEditRecordPropertyList($(BulkEditRecord._selectors.bulkEditRecordPropertyListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
    }

    static markup() {

        return `        
            <div class="container">
                <div class="row">
                     <div class="col-md-12 js-bulk-edit-record-property-list-container"></div>
                     <div class="col-md-12 js-property-form d-none"></div>
                     <div class="col-md-12 js-edit-property-form d-none"></div>
                </div>
            </div>
    `;
    }

}

export default BulkEditRecord;