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
import ReportSelectedCustomFilters from "./ReportSelectedCustomFilters";
import ReportFilterNavigation from "./ReportFilterNavigation";
import ReportPreviewResultsButton from "./ReportPreviewResultsButton";
import ReportPreviewResultsTable from "./ReportPreviewResultsTable";
import ListFilterNavigation from "./ListFilterNavigation";
import ListFilterList from "./ListFilterList";
import ListPreviewResultsButton from "./ListPreviewResultsButton";
import ListPreviewResultsTable from "./ListPreviewResultsTable";

class ListFilters {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, data = {}, listName = '', columnOrder, listType, listId) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.listName = listName;
        this.columnOrder = columnOrder;
        this.listType = listType;
        this.listId = listId;

        /**
         * This data object is responsible for storing all the properties and filters that will get sent to the server
         * @type {{}}
         */
        this.data = data;

        this.globalEventDispatcher.singleSubscribe(
            Settings.Events.LIST_ADD_FILTER_BUTTON_PRESSED,
            this.listAddFilterButtonPressedHandler.bind(this)
        );

        this.globalEventDispatcher.singleSubscribe(
            Settings.Events.LIST_FILTER_ITEM_CLICKED,
            this.handleListFilterItemClicked.bind(this)
        );

        this.globalEventDispatcher.singleSubscribe(
            Settings.Events.LIST_FILTER_ITEM_ADDED,
            this.listFilterItemAddedHandler.bind(this)
        );

        this.globalEventDispatcher.singleSubscribe(
            Settings.Events.LIST_ADD_OR_FILTER_BUTTON_PRESSED,
            this.listAddOrFilterButtonPressedHandler.bind(this)
        );

        this.globalEventDispatcher.singleSubscribe(
            Settings.Events.LIST_EDIT_FILTER_BUTTON_CLICKED,
            this.handleListEditFilterButtonClicked.bind(this)
        );


        this.globalEventDispatcher.singleSubscribe(
            Settings.Events.FILTER_BACK_TO_NAVIGATION_BUTTON_CLICKED,
            this.listFilterBackToNavigationButtonClickedHandler.bind(this)
        );

        this.globalEventDispatcher.singleSubscribe(
            Settings.Events.FILTER_BACK_TO_LIST_BUTTON_CLICKED,
            this.handleFilterBackToListButtonClicked.bind(this)
        );

        this.globalEventDispatcher.singleSubscribe(
            Settings.Events.LIST_FILTER_CUSTOM_OBJECT_JOIN_PATH_SET,
            this.handleListFilterCustomObjectJoinPathSet.bind(this)
        );

        this.unbindEvents();

        this.$wrapper.on(
            'click',
            ListFilters._selectors.backToListPropertiesButton,
            this.handleBackToListPropertiesButtonClicked.bind(this)
        );

        this.$wrapper.on(
            'change',
            ListFilters._selectors.listName,
            this.handleListNameChange.bind(this)
        );

        this.$wrapper.on(
             'click',
             ListFilters._selectors.saveListButton,
             this.handleSaveListButtonClicked.bind(this)
        );


        this.render();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {

            listFilterListContainer: '.js-list-filter-list-container',
            propertyForm: '.js-property-form',
            editPropertyForm: '.js-edit-property-form',
            listFilterNavigation: '.js-list-filter-navigation',
            backToListPropertiesButton: '.js-back-to-list-properties-button',
            saveListButton: '.js-save-list-button',
            listName: '.js-list-name',
            listPreviewResultsButtonContainer: '.js-list-preview-results-button-container',
            listPreviewResultsTableContainer: '.js-list-preview-results-table-container'

        }
    }

    unbindEvents() {

        this.$wrapper.off('click', ListFilters._selectors.backToListPropertiesButton);

        this.$wrapper.off('change', ListFilters._selectors.listName);

        this.$wrapper.off('click', ListFilters._selectors.saveListButton);
    }

    handleSaveListButtonClicked(e) {

        debugger;

        if(e.cancelable) {
            e.preventDefault();
        }

        if(!this.$wrapper.find(ListFilters._selectors.listName).val()) {

            swal("Woahhh snap!!!", "Don't forget a name for your list.", "warning");

            return;

        }

        this.globalEventDispatcher.publish(Settings.Events.LIST_SAVE_BUTTON_PRESSED);

    }

    handleListNameChange(e) {
        debugger;

        this.globalEventDispatcher.publish(Settings.Events.LIST_NAME_CHANGED, $(e.target).val());

    }

    handleBackToListPropertiesButtonClicked(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        this.globalEventDispatcher.publish(Settings.Events.LIST_BACK_TO_PROPERTIES_BUTTON_PRESSED);

        debugger;

    }

    handleListEditFilterButtonClicked(joinPath) {

        let filterPath = joinPath.join('.');

        let customFilter = _.get(this.data, filterPath);

        customFilter.joinPath = joinPath;

        this.$wrapper.find(ListFilters._selectors.listFilterNavigation).addClass('d-none');
        this.$wrapper.find(ListFilters._selectors.editPropertyForm).removeClass('d-none');

        debugger;

        this.renderEditPropertyForm(customFilter);

    }

    renderEditPropertyForm(customFilter) {
        debugger;

        switch (customFilter.fieldType) {
            case 'single_line_text_field':
            case 'multi_line_text_field':
                new EditSingleLineTextFieldFilterForm($(ListFilters._selectors.editPropertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, customFilter);
                break;
            case 'number_field':
                new EditNumberFieldFilterForm($(ListFilters._selectors.editPropertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, customFilter);
                break;
            case 'date_picker_field':
                new EditDatePickerFieldFilterForm($(ListFilters._selectors.editPropertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, customFilter);
                break;
            case 'single_checkbox_field':
                new EditSingleCheckboxFieldFilterForm($(ListFilters._selectors.editPropertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, customFilter);
                break;
            case 'dropdown_select_field':
            case 'radio_select_field':
                new EditDropdownSelectFieldFilterForm($(ListFilters._selectors.editPropertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, customFilter);
                break;
            case 'multiple_checkbox_field':
                new EditMultipleCheckboxFieldFilterForm($(ListFilters._selectors.editPropertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, customFilter);
                break;
        }
    }

    listAddFilterButtonPressedHandler() {

        debugger;

        this.$wrapper.find(ListFilters._selectors.listFilterNavigation).addClass('d-none');
        this.$wrapper.find(ListFilters._selectors.listFilterListContainer).removeClass('d-none');

        new ListFilterList($(ListFilters._selectors.listFilterListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
    }

    listAddOrFilterButtonPressedHandler(referencedFilterPath) {
        debugger;

        this.$wrapper.find(ListFilters._selectors.listFilterNavigation).addClass('d-none');
        this.$wrapper.find(ListFilters._selectors.listFilterListContainer).removeClass('d-none');

        new ListFilterList($(ListFilters._selectors.listFilterListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, null, [], {}, referencedFilterPath);

    }

    renderFilterForm(property) {

        debugger;
        switch (property.fieldType) {
            case 'single_line_text_field':
            case 'multi_line_text_field':
                new SingleLineTextFieldFilterForm($(ListFilters._selectors.propertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, property);
                break;
            case 'number_field':
                new NumberFieldFilterForm($(ListFilters._selectors.propertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property);
                break;
            case 'date_picker_field':
                new DatePickerFieldFilterForm($(ListFilters._selectors.propertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property);
                break;
            case 'single_checkbox_field':
                new SingleCheckboxFieldFilterForm($(ListFilters._selectors.propertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property);
                break;
            case 'dropdown_select_field':
            case 'radio_select_field':
                new DropdownSelectFieldFilterForm($(ListFilters._selectors.propertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property);
                break;
            case 'multiple_checkbox_field':
                new MultilpleCheckboxFieldFilterForm($(ListFilters._selectors.propertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property);
                break;
        }

    }

    handleFilterBackToListButtonClicked() {

        debugger;
        this.$wrapper.find(ListFilters._selectors.listFilterListContainer).removeClass('d-none');

        if(!this.$wrapper.find(ListFilters._selectors.propertyForm).hasClass('d-none')) {

            this.$wrapper.find(ListFilters._selectors.propertyForm).addClass('d-none');

        }

        if(!this.$wrapper.find(ListFilters._selectors.editPropertyForm).hasClass('d-none')) {

            this.$wrapper.find(ListFilters._selectors.editPropertyForm).addClass('d-none');

        }


        new ListFilterList($(ListFilters._selectors.listFilterListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
    }

    listFilterBackToNavigationButtonClickedHandler() {

        this.$wrapper.find(ListFilters._selectors.listFilterNavigation).removeClass('d-none');
        this.$wrapper.find(ListFilters._selectors.propertyForm).addClass('d-none');
        this.$wrapper.find(ListFilters._selectors.listFilterListContainer).addClass('d-none');

    }

    listFilterItemAddedHandler() {

        this.$wrapper.find(ListFilters._selectors.listFilterNavigation).removeClass('d-none');

        if(!this.$wrapper.find(ListFilters._selectors.propertyForm).hasClass('d-none')) {

            this.$wrapper.find(ListFilters._selectors.propertyForm).addClass('d-none');

        }

        if(!this.$wrapper.find(ListFilters._selectors.editPropertyForm).hasClass('d-none')) {

            this.$wrapper.find(ListFilters._selectors.editPropertyForm).addClass('d-none');

        }
    }

    handleListFilterItemClicked(property) {

        this.$wrapper.find(ListFilters._selectors.listFilterListContainer).addClass('d-none');
        this.$wrapper.find(ListFilters._selectors.propertyForm).removeClass('d-none');

        this.renderFilterForm(property);

    }

    handleListFilterCustomObjectJoinPathSet(property, joins, data) {

        debugger;
        new ListFilterList($(ListFilters._selectors.listFilterListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, property.field.customObject.internalName, property, joins, data, property.referencedFilterPath);

    }

    render() {

        debugger;

        this.$wrapper.html(ListFilters.markup(this));

        new ListFilterNavigation($(ListFilters._selectors.listFilterNavigation), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, this.data, this.listType, this.listId);

        new ListPreviewResultsButton($(ListFilters._selectors.listPreviewResultsButtonContainer), this.globalEventDispatcher);

        new ListPreviewResultsTable($(ListFilters._selectors.listPreviewResultsTableContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, this.data, this.columnOrder, this.listType, this.listId);

        this.$wrapper.find(ListFilters._selectors.listName).val(this.listName);

    }

    static markup() {

        return `
             <nav class="navbar navbar-expand-sm l-top-bar justify-content-end c-report-widget__nav">
             
                 <div class="container-fluid">
                    <div class="navbar-collapse collapse dual-nav w-50 order-1 order-md-0">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <button type="button" style="color: #FFF" class="btn btn-link js-back-to-list-properties-button"><i class="fa fa-angle-left" aria-hidden="true"></i> Back</button>
                            </li>
                        </ul>
                    </div>
                    
                    <input style="width: 200px;" class="form-control navbar-brand mx-auto d-block text-center order-0 order-md-1 w-25 c-report-widget__report-name js-list-name" type="search" placeholder="List name" aria-label="Search">
                    
                    <div class="navbar-collapse collapse dual-nav w-50 order-2">
                        <ul class="nav navbar-nav ml-auto">
                            <li class="nav-item">
                            <button class="btn btn-lg btn-secondary ml-auto js-save-list-button c-report-widget__report-save">Save</button>
                            </li>
                        </ul>
                    </div>
                </div>
                                   
             </nav> 
        
            <div class="row container">
            <div class="col-md-4 js-list-filter-navigation c-report-widget__filter-navigation"></div>
             
             <div class="col-md-4 js-list-filter-list-container d-none"></div>
             <div class="col-md-4 js-property-form d-none"></div>
             <div class="col-md-4 js-edit-property-form d-none"></div>
            
            
            <div class="col-md-8 js-list-preview-results-container">
                <div class="col-md-12 js-list-preview-results-button-container"></div>
                <br>
                <div class="col-md-12 js-list-preview-results-table-container"></div>  
            </div>
             
            </div>
    `;
    }
}

export default ListFilters;