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

class ReportFilters {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;

        /**
         * This data object is responsible for storing all the properties and filters that will get sent to the server
         * @type {{}}
         */
        this.data = {};

/*        this.unbindEvents();

        this.globalEventDispatcher.subscribe(
            Settings.Events.CUSTOM_OBJECT_FOR_REPORT_SELECTED,
            this.handleCustomObjectForReportSelected.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_CUSTOM_OBJECT_PROPERTY_LIST_ITEM_CLICKED,
            this.handleCustomObjectPropertyListItemClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_BACK_BUTTON_CLICKED,
            this.handleBackButtonClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_REMOVE_SELECTED_COLUMN_ICON_CLICKED,
            this.handleReportRemoveSelectedColumnIconClicked.bind(this)
        );

        this.$wrapper.on(
            'click',
            ReportFilters._selectors.reportBackToSelectCustomObjectButton,
            this.handleReportBackToSelectCustomObjectButton.bind(this)
        );

        this.$wrapper.on(
            'click',
            ReportSelectCustomObject._selectors.reportAdvanceToFiltersView,
            this.handleReportAdvanceToFiltersViewButtonClicked.bind(this)
        );*/


        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_FILTER_ITEM_CLICKED,
            this.handleReportFilterItemClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.FILTER_BACK_TO_LIST_BUTTON_CLICKED,
            this.handleFilterBackToListButtonClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_CUSTOM_OBJECT_FILTER_LIST_ITEM_CLICKED,
            this.handleReportCustomObjectFilterListItemClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_FILTER_ITEM_ADDED,
            this.reportFilterItemAddedHandler.bind(this)
        );




        /*REPORT_FILTER_LIST_BACK_BUTTON_CLICKED*/


        this.render();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            /*reportSelectCustomObjectContainer: '.js-report-select-custom-object-container',
            reportSelectPropertyContainer: '.js-report-select-property-container',
            reportSelectedColumnsContainer: '.js-report-selected-columns-container',
            reportPropertyListContainer: '.js-report-property-list-container',
            reportSelectedColumnsCountContainer: '.js-report-selected-columns-count-container',
            reportBackToSelectCustomObjectButton: '.js-back-to-select-custom-object-button',
            reportAdvanceToFiltersView: '.js-advance-to-filters-view'*/


            reportFilterListContainer: '.js-report-filter-list-container',
            propertyForm: '.js-property-form'



        }
    }

    unbindEvents() {
        this.$wrapper.off('click', ReportPropertyList._selectors.reportBackToSelectCustomObjectButton);
        this.$wrapper.off('click', ReportPropertyList._selectors.reportAdvanceToFiltersView);
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
        this.$wrapper.find(ReportFilters._selectors.reportSelectPropertyContainer).addClass('d-none');

        new ReportSelectCustomObject($(ReportFilters._selectors.reportSelectCustomObjectContainer), this.globalEventDispatcher, this.portalInternalIdentifier);

    }

    handleFilterBackToListButtonClicked() {

        this.$wrapper.find(ReportFilters._selectors.reportFilterListContainer).removeClass('d-none');
        this.$wrapper.find(ReportFilters._selectors.propertyForm).addClass('d-none');

        new ReportFilterList($(ReportFilters._selectors.reportFilterListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
    }

    handleReportAdvanceToFiltersViewButtonClicked(e) {

        debugger;

    }

    handleCustomObjectForReportSelected(customObject) {

        this.customObject = customObject;

        this.$wrapper.find(ReportFilters._selectors.reportSelectCustomObjectContainer).addClass('d-none');
        this.$wrapper.find(ReportFilters._selectors.reportSelectPropertyContainer).removeClass('d-none');

        new ReportPropertyList($(ReportFilters._selectors.reportPropertyListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, customObject.internalName);

        new ReportSelectedColumns(this.$wrapper.find(ReportFilters._selectors.reportSelectedColumnsContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.data);

        new ReportSelectedColumnsCount(this.$wrapper.find(ReportFilters._selectors.reportSelectedColumnsCountContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.data);

    }

    reportFilterItemAddedHandler() {

        debugger;
        this.$wrapper.find(ReportFilters._selectors.reportFilterListContainer).removeClass('d-none');
        this.$wrapper.find(ReportFilters._selectors.propertyForm).addClass('d-none');

        new ReportFilterList($(ReportFilters._selectors.reportFilterListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);

    }

    handleReportFilterItemClicked(property) {

        debugger;
        this.$wrapper.find(ReportFilters._selectors.reportFilterListContainer).addClass('d-none');
        this.$wrapper.find(ReportFilters._selectors.propertyForm).removeClass('d-none');

        this.renderFilterForm(property);

        debugger;

        debugger;

       /* let propertyPath = property.joins.join('.');

        if(_.get(this.data, propertyPath, false)) {

            _.get(this.data, propertyPath).push(property);

        } else {
            _.set(this.data, propertyPath, []);
            _.get(this.data, propertyPath).push(property);
        }

        this.globalEventDispatcher.publish(Settings.Events.REPORT_PROPERTY_LIST_ITEM_ADDED, this.data);*/

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

    handleReportCustomObjectFilterListItemClicked(property, joins) {

        debugger;


        new ReportFilterList($(ReportFilters._selectors.reportFilterListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, property.field.customObject.internalName, property, joins, this.data);

       /* let propertyPath = property.joins.join('.');

        if(!_.has(this.data, propertyPath)) {
            _.set(this.data, propertyPath, []);
        }*/

/*

        let propertyPath = property.joins.join('.') + '.filters';

        if(_.get(this.data, propertyPath, false)) {

            debugger;
            _.get(this.data, propertyPath).push(property);

        } else {
            debugger;
            _.set(this.data, propertyPath, []);
            _.get(this.data, propertyPath).push(property);
        }

        this.globalEventDispatcher.publish(Settings.Events.REPORT_FILTER_ITEM_ADDED, this.data);
*/


    }

    render() {

        this.$wrapper.html(ReportFilters.markup(this));
        /*new ReportSelectCustomObject($(ReportFilters._selectors.reportSelectCustomObjectContainer), this.globalEventDispatcher, this.portalInternalIdentifier);*/

        new ReportFilterList($(ReportFilters._selectors.reportFilterListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);

    }

    static markup() {

        return `
             <nav class="navbar navbar-expand-sm l-top-bar justify-content-end c-report-widget__nav">
                  <button type="button" style="color: #FFF" class="btn btn-link js-back-to-select-custom-object-button"><i class="fa fa-angle-left" aria-hidden="true"></i> Back</button>
                  <button class="btn btn-lg btn-secondary ml-auto js-advance-to-filters-view">Save</button> 
             </nav> 
        
            <div class="row container">
                <div class="col-md-6 js-report-filter-list-container"></div>
                <div class="col-md-6 js-property-form d-none"></div>
                <div class="col-md-6">
                
                    <!--<div class="js-report-selected-columns-count-container c-column-editor__selected-columns-count"></div>
                    <div class="js-report-selected-columns-container c-report-widget__selected-columns"></div>-->
               
                </div>  
            </div>
    `;
    }
}

export default ReportFilters;