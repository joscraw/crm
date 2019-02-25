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
import ReportFilters from "./ReportFilters";

class ReportProperties {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, data) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.data = data;

        this.unbindEvents();

        this.bindEvents();

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_BACK_BUTTON_CLICKED,
            this.handleBackButtonClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_CUSTOM_OBJECT_JOIN_PATH_SET,
            this.handleCustomObjectJoinPathSet.bind(this)
        );

        this.render();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {

            reportSelectedColumnsContainer: '.js-report-selected-columns-container',
            reportPropertyListContainer: '.js-report-property-list-container',
            reportSelectedColumnsCountContainer: '.js-report-selected-columns-count-container',
            reportBackToSelectCustomObjectButton: '.js-back-to-select-custom-object-button',
            reportAdvanceToFiltersView: '.js-advance-to-filters-view'

        }
    }

    bindEvents() {

        this.$wrapper.on(
            'click',
            ReportProperties._selectors.reportBackToSelectCustomObjectButton,
            this.handleReportBackToSelectCustomObjectButton.bind(this)
        );

        this.$wrapper.on(
            'click',
            ReportProperties._selectors.reportAdvanceToFiltersView,
            this.handleReportAdvanceToFiltersViewButtonClicked.bind(this)
        );

    }

    unbindEvents() {

        this.$wrapper.off('click', ReportPropertyList._selectors.reportBackToSelectCustomObjectButton);
        this.$wrapper.off('click', ReportProperties._selectors.reportAdvanceToFiltersView);
    }

    handleReportAdvanceToFiltersViewButtonClicked(e) {

        debugger;
        this.globalEventDispatcher.publish(Settings.Events.ADVANCE_TO_REPORT_FILTERS_VIEW_BUTTON_CLICKED);

    }

    handleBackButtonClicked() {

        new ReportPropertyList($(ReportProperties._selectors.reportPropertyListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, null, [], this.data);

    }

    handleCustomObjectJoinPathSet(property, joins, data) {

        new ReportPropertyList($(ReportProperties._selectors.reportPropertyListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, property.field.customObject.internalName, property, joins, data);

    }

    handleReportBackToSelectCustomObjectButton(e) {

        this.globalEventDispatcher.publish(Settings.Events.REPORT_BACK_TO_SELECT_CUSTOM_OBJECT_BUTTON_PRESSED, this.data);

    }

    render() {

        this.$wrapper.html(ReportProperties.markup(this));

        new ReportPropertyList($(ReportProperties._selectors.reportPropertyListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);

        new ReportSelectedColumns(this.$wrapper.find(ReportProperties._selectors.reportSelectedColumnsContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.data);

        new ReportSelectedColumnsCount(this.$wrapper.find(ReportProperties._selectors.reportSelectedColumnsCountContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.data);

    }

    static markup() {

        return `
             <nav class="navbar navbar-expand-sm l-top-bar justify-content-end c-report-widget__nav">
                  <button type="button" style="color: #FFF" class="btn btn-link js-back-to-select-custom-object-button"><i class="fa fa-angle-left" aria-hidden="true"></i> Back</button>
                 <button class="btn btn-lg btn-secondary ml-auto js-advance-to-filters-view">Next</button> 
             </nav> 
        
            <div class="row container">
                <div class="col-md-6 js-report-property-list-container"></div>
                <div class="col-md-6">
                
                    <div class="js-report-selected-columns-count-container c-column-editor__selected-columns-count"></div>
                    <div class="js-report-selected-columns-container c-report-widget__selected-columns"></div>
               
                </div>  
            </div>
    `;
    }
}

export default ReportProperties;