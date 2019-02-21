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

class ReportWidget {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObject = null;

        /**
         * This data object is responsible for storing all the properties and filters that will get sent to the server
         * @type {{}}
         */
        this.data = {};

        this.globalEventDispatcher.subscribe(
            Settings.Events.CUSTOM_OBJECT_FOR_REPORT_SELECTED,
            this.handleCustomObjectForReportSelected.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_PROPERTY_LIST_ITEM_CLICKED,
            this.handlePropertyListItemClicked.bind(this)
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



        /*

        this.globalEventDispatcher.subscribe(
            Settings.BACK_TO_SELECT_CUSTOM_OBJECT_FOR_REPORT_BUTTON_PRESSED,
            this.handleBackToSelectCustomObjectForReportButtonPressed.bind(this)
        );
*/

        this.render();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            reportSelectCustomObjectContainer: '.js-report-select-custom-object-container',
            reportSelectPropertyContainer: '.js-report-select-property-container',
            reportSelectedColumnsContainer: '.js-report-selected-columns-container',
            reportPropertyListContainer: '.js-report-property-list-container'

        }
    }

    handleCustomObjectForReportSelected(customObject) {

        this.customObject = customObject;

        this.$wrapper.find(ReportWidget._selectors.reportSelectCustomObjectContainer).addClass('d-none');
        this.$wrapper.find(ReportWidget._selectors.reportSelectPropertyContainer).removeClass('d-none');

        new ReportPropertyList($(ReportWidget._selectors.reportPropertyListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, customObject.internalName);


        debugger;

        new ReportSelectedColumns(this.$wrapper.find(ReportWidget._selectors.reportSelectedColumnsContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.data);

    }

    handlePropertyListItemClicked(property) {

        let propertyPath = property.joins.join('.');

        if(_.get(this.data, propertyPath, false)) {
            _.get(this.data, propertyPath).push(property);
            debugger;

        } else {
            debugger;
            _.set(this.data, propertyPath, []);
            _.get(this.data, propertyPath).push(property);
        }

        this.globalEventDispatcher.publish(Settings.Events.REPORT_PROPERTY_LIST_ITEM_ADDED, this.data);

    }

    handleReportRemoveSelectedColumnIconClicked(property) {

        let propertyPath = property.joins.join('.');

        if(_.has(this.data, propertyPath)) {

            let properties = _.get(this.data, propertyPath);

            let filteredProperties = $.grep(properties, function(p){

                return parseInt(p.id) !== property.id;
            });

            _.set(this.data, propertyPath, filteredProperties);

        }

        debugger;
        this.globalEventDispatcher.publish(Settings.Events.REPORT_PROPERTY_LIST_ITEM_REMOVED, this.data);
    }

    handleBackButtonClicked() {

        new ReportPropertyList($(ReportWidget._selectors.reportPropertyListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObject.internalName, null, [], this.data);

    }

    handleCustomObjectPropertyListItemClicked(property, joins) {

        new ReportPropertyList($(ReportWidget._selectors.reportPropertyListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, property.field.customObject.internalName, property, joins, this.data);

    }

    render() {

        this.$wrapper.html(ReportWidget.markup(this));
        new ReportSelectCustomObject($(ReportWidget._selectors.reportSelectCustomObjectContainer), this.globalEventDispatcher, this.portalInternalIdentifier);

    }

    static markup() {

        return `
      <div class="js-report-widget c-report-widget">
            <div class="js-report-select-custom-object-container"></div>
            
            <div class="js-report-select-property-container d-none">
                 <nav class="navbar navbar-expand-sm l-top-bar justify-content-end c-report-widget__nav">
                     <a class="btn btn-link" style="color:#FFF" data-bypass="true" href="#" role="button"><i class="fa fa-angle-left" aria-hidden="true"></i> Back</a>
                     <button class="btn btn-lg btn-secondary ml-auto js-select-custom-object-button">Next</button> 
                 </nav> 
            
                <div class="row container">
                    <div class="col-md-6 js-report-property-list-container">
                        
                    </div>
                    <div class="col-md-6 js-report-selected-columns-container c-report-widget__selected-columns">
                    
                    <!--
                        <div class="js-selected-columns-count c-column-editor__selected-columns-count"></div>
                        <div class="js-selected-columns-container c-column-editor__selected-columns"></div>
                        -->
                    </div>  
                </div>
            </div>
            
      </div>
    `;
    }
}

export default ReportWidget;