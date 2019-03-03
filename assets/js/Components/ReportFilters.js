'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
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

class ReportFilters {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, data = {}) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;

        /**
         * This data object is responsible for storing all the properties and filters that will get sent to the server
         * @type {{}}
         */
        this.data = data;

        this.unbindEvents();

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_FILTER_ITEM_CLICKED,
            this.handleReportFilterItemClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.FILTER_BACK_TO_LIST_BUTTON_CLICKED,
            this.handleFilterBackToListButtonClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_FILTER_CUSTOM_OBJECT_JOIN_PATH_SET,
            this.handleReportFilterCustomObjectJoinPathSet.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_FILTER_ITEM_ADDED,
            this.reportFilterItemAddedHandler.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_ADD_FILTER_BUTTON_PRESSED,
            this.reportAddFilterButtonPressedHandler.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_ADD_OR_FILTER_BUTTON_PRESSED,
            this.reportAddOrFilterButtonPressedHandler.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.FILTER_BACK_TO_NAVIGATION_BUTTON_CLICKED,
            this.reportFilterBackToNavigationButtonClickedHandler.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_EDIT_FILTER_BUTTON_CLICKED,
            this.handleReportEditFilterButtonClicked.bind(this)
        );


        this.$wrapper.on(
            'click',
            ReportFilters._selectors.backToReportPropertiesButton,
            this.handleBackToReportPropertiesButtonClicked.bind(this)
        );

        this.$wrapper.on(
            'click',
            ReportFilters._selectors.saveReportButton,
            this.handleSaveReportButtonClicked.bind(this)
        );

        this.render();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {

            reportFilterListContainer: '.js-report-filter-list-container',
            propertyForm: '.js-property-form',
            editPropertyForm: '.js-edit-property-form',
            reportSelectedCustomFilters: '.js-report-selected-custom-filters',
            reportFilterNavigation: '.js-report-filter-navigation',
            backToReportPropertiesButton: '.js-back-to-report-properties-button',
            saveReportButton: '.js-save-report-button'

        }
    }

    unbindEvents() {

        this.$wrapper.off('click', ReportFilters._selectors.backToReportPropertiesButton);
        this.$wrapper.off('click', ReportFilters._selectors.saveReportButton);
    }

    handleSaveReportButtonClicked(e) {

        if(e.cancelable) {
            e.preventDefault();
        }


        this.globalEventDispatcher.publish(Settings.Events.REPORT_SAVE_BUTTON_PRESSED);

    }

    handleBackToReportPropertiesButtonClicked(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        this.globalEventDispatcher.publish(Settings.Events.REPORT_BACK_TO_PROPERTIES_BUTTON_PRESSED);

        debugger;

    }

    handleReportEditFilterButtonClicked(joinPath) {

        let filterPath = joinPath.join('.');

        let customFilter = _.get(this.data, filterPath);

        customFilter.joinPath = joinPath;

        this.$wrapper.find(ReportFilters._selectors.reportFilterNavigation).addClass('d-none');
        this.$wrapper.find(ReportFilters._selectors.editPropertyForm).removeClass('d-none');

        debugger;

        this.renderEditPropertyForm(customFilter);

    }

    renderEditPropertyForm(customFilter) {
        debugger;

        switch (customFilter.fieldType) {
            case 'single_line_text_field':
            case 'multi_line_text_field':
                new EditSingleLineTextFieldFilterForm($(ReportFilters._selectors.editPropertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, customFilter);
                break;
            case 'number_field':
                new EditNumberFieldFilterForm($(ReportFilters._selectors.editPropertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, customFilter);
                break;
            case 'date_picker_field':
                new EditDatePickerFieldFilterForm($(ReportFilters._selectors.editPropertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, customFilter);
                break;
            case 'single_checkbox_field':
                new EditSingleCheckboxFieldFilterForm($(ReportFilters._selectors.editPropertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, customFilter);
                break;
            case 'dropdown_select_field':
            case 'radio_select_field':
                new EditDropdownSelectFieldFilterForm($(ReportFilters._selectors.editPropertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, customFilter);
                break;
            case 'multiple_checkbox_field':
                new EditMultipleCheckboxFieldFilterForm($(ReportFilters._selectors.editPropertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, customFilter);
                break;
        }
    }

    reportAddFilterButtonPressedHandler() {

        debugger;

        this.$wrapper.find(ReportFilters._selectors.reportFilterNavigation).addClass('d-none');
        this.$wrapper.find(ReportFilters._selectors.reportFilterListContainer).removeClass('d-none');

        new ReportFilterList($(ReportFilters._selectors.reportFilterListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
    }

    reportAddOrFilterButtonPressedHandler(referencedFilterPath) {
        debugger;

        this.$wrapper.find(ReportFilters._selectors.reportFilterNavigation).addClass('d-none');
        this.$wrapper.find(ReportFilters._selectors.reportFilterListContainer).removeClass('d-none');

        new ReportFilterList($(ReportFilters._selectors.reportFilterListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, null, [], {}, referencedFilterPath);

    }

    renderFilterForm(property) {

        debugger;
        switch (property.fieldType) {
            case 'single_line_text_field':
            case 'multi_line_text_field':
                new SingleLineTextFieldFilterForm($(ReportFilters._selectors.propertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property);
                break;
            case 'number_field':
                new NumberFieldFilterForm($(ReportFilters._selectors.propertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property);
                break;
            case 'date_picker_field':
                new DatePickerFieldFilterForm($(ReportFilters._selectors.propertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property);
                break;
            case 'single_checkbox_field':
                new SingleCheckboxFieldFilterForm($(ReportFilters._selectors.propertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property);
                break;
            case 'dropdown_select_field':
            case 'radio_select_field':
                new DropdownSelectFieldFilterForm($(ReportFilters._selectors.propertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property);
                break;
            case 'multiple_checkbox_field':
                new MultilpleCheckboxFieldFilterForm($(ReportFilters._selectors.propertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property);
                break;
        }

    }

    handleReportBackToSelectCustomObjectButton(e) {

        debugger;
        this.$wrapper.find(ReportFilters._selectors.reportSelectCustomObjectContainer).removeClass('d-none');
        /*this.$wrapper.find(ReportFilters._selectors.reportSelectPropertyContainer).addClass('d-none');*/

        new ReportSelectCustomObject($(ReportFilters._selectors.reportSelectCustomObjectContainer), this.globalEventDispatcher, this.portalInternalIdentifier);

    }

    handleFilterBackToListButtonClicked() {

        this.$wrapper.find(ReportFilters._selectors.reportFilterListContainer).removeClass('d-none');
        this.$wrapper.find(ReportFilters._selectors.propertyForm).addClass('d-none');

        new ReportFilterList($(ReportFilters._selectors.reportFilterListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
    }

    reportFilterBackToNavigationButtonClickedHandler() {

        this.$wrapper.find(ReportFilters._selectors.reportFilterNavigation).removeClass('d-none');
        this.$wrapper.find(ReportFilters._selectors.propertyForm).addClass('d-none');
        this.$wrapper.find(ReportFilters._selectors.reportFilterListContainer).addClass('d-none');

    }

    handleReportAdvanceToFiltersViewButtonClicked(e) {

        debugger;

        new ReportFilterList($(ReportFilters._selectors.reportFilterListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);

    }

    handleCustomObjectForReportSelected(customObject) {

        this.customObject = customObject;

        this.$wrapper.find(ReportFilters._selectors.reportSelectCustomObjectContainer).addClass('d-none');
        /*this.$wrapper.find(ReportFilters._selectors.reportSelectPropertyContainer).removeClass('d-none');*/

        new ReportPropertyList($(ReportFilters._selectors.reportPropertyListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, customObject.internalName);

        new ReportSelectedColumns(this.$wrapper.find(ReportFilters._selectors.reportSelectedColumnsContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.data);

        new ReportSelectedColumnsCount(this.$wrapper.find(ReportFilters._selectors.reportSelectedColumnsCountContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.data);

    }

    reportFilterItemAddedHandler() {

        this.$wrapper.find(ReportFilters._selectors.reportFilterNavigation).removeClass('d-none');

        if(!this.$wrapper.find(ReportFilters._selectors.propertyForm).hasClass('d-none')) {

            this.$wrapper.find(ReportFilters._selectors.propertyForm).addClass('d-none');

        }

        if(!this.$wrapper.find(ReportFilters._selectors.editPropertyForm).hasClass('d-none')) {

            this.$wrapper.find(ReportFilters._selectors.editPropertyForm).addClass('d-none');

        }

        /*new ReportFilterList($(ReportFilters._selectors.reportFilterListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);*/

    }

    handleReportFilterItemClicked(property) {

        debugger;

        this.$wrapper.find(ReportFilters._selectors.reportFilterListContainer).addClass('d-none');
        this.$wrapper.find(ReportFilters._selectors.propertyForm).removeClass('d-none');

        this.renderFilterForm(property);

    }

    handleReportRemoveSelectedColumnIconClicked(property) {

        let propertyPath = property.joins.join('.');

        if(_.has(this.data, propertyPath)) {

            let properties = _.get(this.data, propertyPath);

            let key = null;

            properties.forEach((p, k) => {

                if(parseInt(p.id) === parseInt(property.id)) {
                    key = k;
                }
            });

            _.unset(this.data, `${propertyPath}[${key}]`);

        }

        this.globalEventDispatcher.publish(Settings.Events.REPORT_PROPERTY_LIST_ITEM_REMOVED, this.data);
    }

    handleBackButtonClicked() {

        new ReportPropertyList($(ReportFilters._selectors.reportPropertyListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObject.internalName, null, [], this.data);

    }

    handleReportFilterCustomObjectJoinPathSet(property, joins, data) {

        debugger;
        new ReportFilterList($(ReportFilters._selectors.reportFilterListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, property.field.customObject.internalName, property, joins, data, property.referencedFilterPath);

    }

    render() {

        debugger;

        this.$wrapper.html(ReportFilters.markup(this));

        new ReportFilterNavigation($(ReportFilters._selectors.reportFilterNavigation), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, this.data);

     /*   new ReportFilterList($(ReportFilters._selectors.reportFilterListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);

        new ReportSelectedCustomFilters($(ReportFilters._selectors.reportSelectedCustomFilters), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
*/
    }

    static markup() {

        return `
             <nav class="navbar navbar-expand-sm l-top-bar justify-content-end c-report-widget__nav">
                  <button type="button" style="color: #FFF" class="btn btn-link js-back-to-report-properties-button"><i class="fa fa-angle-left" aria-hidden="true"></i> Back</button>
                  <button class="btn btn-lg btn-secondary ml-auto js-save-report-button">Save</button> 
             </nav> 
        
            <div class="row container">
            <div class="col-md-6 js-report-filter-navigation"></div>
             
             <div class="col-md-6 js-report-filter-list-container d-none"></div>
             <div class="col-md-6 js-property-form d-none"></div>
             <div class="col-md-6 js-edit-property-form d-none"></div>
            
            <div class="col-md-6">
                
            </div> 
            </div>
    `;
    }
}

export default ReportFilters;