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
import EventDispatcher from "../EventDispatcher";
import ReportConnectableObjects from "./ReportConnectableObjects";
import ReportConnectObjectButton from "./ReportConnectObjectButton";
import ReportPreviewResultsTable from "./ReportPreviewResultsTable";
import ReportAllFiltersButton from "./ReportAllFiltersButton";
import ReportConnectedObjectsList from "./ReportConnectedObjectsList";

class ReportProperties {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, data) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.data = data;
        this.unbindEvents();
        this.bindEvents();
        this.render();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            reportTablePreview: '.js-report-table-preview',
            reportBackToSelectCustomObjectButton: '.js-back-to-select-custom-object-button',
            reportConnectableObjectsContainer: '.js-report-connectable-objects',
            reportAllFiltersButtonContainer: '.js-report-all-filters-button-container',
            reportConnectedObjectsListContainer: '.js-report-connected-objects-list-container',
            saveReportButton: '.js-save-report-button',
            reportName:  '.js-report-name',
            reportPropertyListContainer: '.js-report-property-list-container'
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
            ReportProperties._selectors.saveReportButton,
            this.handleSaveReportButtonClicked.bind(this)
        );
    }

    handleSaveReportButtonClicked(e) {
        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }
        let reportName = this.$wrapper.find(ReportProperties._selectors.reportName).val();
        if(reportName === '') {
            swal("Woahhh snap!!!", "Don't forget a name for your report.", "warning");
            return;
        }
        this.globalEventDispatcher.publish(Settings.Events.REPORT_SAVE_BUTTON_PRESSED, reportName);
    }

    unbindEvents() {
        this.$wrapper.off('click', ReportProperties._selectors.reportBackToSelectCustomObjectButton);
        this.$wrapper.off('click', ReportProperties._selectors.saveReportButton);
    }

    handleReportBackToSelectCustomObjectButton(e) {
        this.globalEventDispatcher.publish(Settings.Events.REPORT_BACK_TO_SELECT_CUSTOM_OBJECT_BUTTON_PRESSED, this.data);
    }

    render() {
        debugger;
        this.$wrapper.html(ReportProperties.markup(this));
        new ReportPreviewResultsTable($(ReportProperties._selectors.reportTablePreview), this.globalEventDispatcher, this.portalInternalIdentifier, this.data);
        new ReportPropertyList($(ReportProperties._selectors.reportPropertyListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.data);
        new ReportConnectObjectButton($(ReportProperties._selectors.reportConnectableObjectsContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.data.selectedCustomObject.internalName);
        new ReportAllFiltersButton($(ReportProperties._selectors.reportAllFiltersButtonContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.data.selectedCustomObject.internalName);
        new ReportConnectedObjectsList($(ReportProperties._selectors.reportConnectedObjectsListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.data.selectedCustomObject.internalName, this.data);
    }

    static markup({data: {reportName, selectedCustomObject: {internalName}}}) {
        return `

            <nav class="navbar navbar-expand-sm l-top-bar justify-content-end c-report-widget__nav">
                 <div class="container-fluid">
                    <div class="navbar-collapse collapse dual-nav w-50 order-1 order-md-0">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <button type="button" style="color: #FFF" class="btn btn-link js-back-to-select-custom-object-button"><i class="fa fa-angle-left" aria-hidden="true"></i> Back</button>
                            </li>
                        </ul>
                    </div>
                    <input style="width: 200px;" value="${reportName}" class="form-control navbar-brand mx-auto d-block text-center order-0 order-md-1 w-25 c-report-widget__report-name js-report-name" type="text" placeholder="Report name">
                    <div class="navbar-collapse collapse dual-nav w-50 order-2">
                        <ul class="nav navbar-nav ml-auto">
                            <li class="nav-item">
                            <button class="btn btn-lg btn-secondary ml-auto js-save-report-button c-report-widget__report-save">Save</button>
                            </li>
                        </ul>
                    </div>
                </div>               
            </nav> 
            <div class="row container">
                <div class="col-md-4" style="height: 600px;  overflow-y: auto">
                <h2 style="text-decoration: underline">Reporting on ${internalName}</h2>
                    <div class="col-md-12 js-report-property-list-container"></div>
                    <div class="col-md-12 js-report-connectable-objects"></div>
                    <div class="col-md-12 js-report-all-filters-button-container"></div>
                    <div class="col-md-12 js-report-connected-objects-list-container"></div>
                </div>
                <div class="col-md-8">
                    <div class="js-report-table-preview c-column-editor__selected-columns-count"></div>
                </div>  
            </div>
    `;
    }
}

export default ReportProperties;