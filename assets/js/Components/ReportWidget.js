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
import ReportFilters from "./ReportFilters";
import ReportProperties from "./ReportProperties";
import StringHelper from "../StringHelper";
import swal from "sweetalert2";

class ReportWidget {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObject = null;
        this.reportName = '';

        /**
         * This data object is responsible for storing all the properties and filters that will get sent to the server
         * @type {{}}
         */
        this.data = {};

        this.columnOrder = [];

        this.unbindEvents();

        this.globalEventDispatcher.subscribe(
            Settings.Events.ADVANCE_TO_REPORT_PROPERTIES_VIEW_BUTTON_CLICKED,
            this.handleAdvanceToReportPropertiesViewButtonClicked.bind(this)
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
            Settings.Events.REPORT_CUSTOM_OBJECT_FILTER_LIST_ITEM_CLICKED,
            this.handleReportCustomObjectFilterListItemClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_REMOVE_SELECTED_COLUMN_ICON_CLICKED,
            this.handleReportRemoveSelectedColumnIconClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.APPLY_CUSTOM_FILTER_BUTTON_PRESSED,
            this.applyCustomFilterButtonPressedHandler.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_BACK_TO_SELECT_CUSTOM_OBJECT_BUTTON_PRESSED,
            this.reportBackToSelectCustomObjectButtonHandler.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.ADVANCE_TO_REPORT_FILTERS_VIEW_BUTTON_CLICKED,
            this.handleReportAdvanceToFiltersViewButtonClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_REMOVE_FILTER_BUTTON_PRESSED,
            this.handleReportRemoveFilterButtonPressed.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_BACK_TO_PROPERTIES_BUTTON_PRESSED,
            this.handleReportBackToPropertiesButtonPressed.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_SAVE_BUTTON_PRESSED,
            this.handleReportSaveButtonPressed.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_NAME_CHANGED,
            this.handleReportNameChange.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_COLUMN_ORDER_CHANGED,
            this.handleReportColumnOrderChanged.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_PREVIEW_RESULTS_BUTTON_CLICKED,
            this.handleReportPreviewResultsButtonClicked.bind(this)
        );

        this.render();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            reportSelectCustomObjectContainer: '.js-report-select-custom-object-container',
            reportPropertiesContainer: '.js-report-properties-container',
            reportFiltersContainer: '.js-report-filters-container'

        }
    }

    unbindEvents() {

        this.$wrapper.off('click', ReportPropertyList._selectors.reportAdvanceToFiltersView);
    }

    handleReportSaveButtonPressed() {

        debugger;
        this._saveReport().then((data) => {

            swal("Woohoo!!!", "Report successfully saved.", "success");

        }).catch((errorData) => {

            if(errorData.httpCode === 401) {
                swal("Woah!", `You don't have proper permissions for this!`, "error");
                return;
            }

        });

    }

    handleReportPreviewResultsButtonClicked() {

        this.loadReportPreview().then((data) => {

            debugger;
            this.globalEventDispatcher.publish(Settings.Events.REPORT_PREVIEW_RESULTS_LOADED, data.data, this.columnOrder);

        });

    }

    redirectToReportSettings() {

        window.location = Routing.generate('report_settings', {internalIdentifier: this.portalInternalIdentifier});
    }

    handleReportNameChange(reportName) {
        debugger;
        this.reportName = reportName;
    }

    reportBackToSelectCustomObjectButtonHandler(e) {

        this.$wrapper.find(ReportWidget._selectors.reportSelectCustomObjectContainer).removeClass('d-none');
        this.$wrapper.find(ReportWidget._selectors.reportPropertiesContainer).addClass('d-none');

        new ReportSelectCustomObject($(ReportWidget._selectors.reportSelectCustomObjectContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObject);

    }

    handleReportBackToPropertiesButtonPressed() {
        debugger;

        this.$wrapper.find(ReportWidget._selectors.reportFiltersContainer).addClass('d-none');
        this.$wrapper.find(ReportWidget._selectors.reportPropertiesContainer).removeClass('d-none');

        /*new ReportProperties($(ReportWidget._selectors.reportPropertiesContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObject.internalName, this.data);*/

    }

    handleReportAdvanceToFiltersViewButtonClicked(e) {

        debugger;
        this.$wrapper.find(ReportWidget._selectors.reportFiltersContainer).removeClass('d-none');
        this.$wrapper.find(ReportWidget._selectors.reportPropertiesContainer).addClass('d-none');

        new ReportFilters($(ReportWidget._selectors.reportFiltersContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObject.internalName, this.data, this.reportName);

    }

    handleAdvanceToReportPropertiesViewButtonClicked(customObject) {

        debugger;

        // If a brand new custom object is selected then clear the data
        if(this.customObject && this.customObject.id !== customObject.id) {
            this.data = {};
            this.columnOrder = [];
        }

        this.customObject = customObject;

        this.$wrapper.find(ReportWidget._selectors.reportSelectCustomObjectContainer).addClass('d-none');
        this.$wrapper.find(ReportWidget._selectors.reportPropertiesContainer).removeClass('d-none');

        new ReportProperties($(ReportWidget._selectors.reportPropertiesContainer), this.globalEventDispatcher, this.portalInternalIdentifier, customObject.internalName, this.data, this.columnOrder);

    }

    handleReportColumnOrderChanged(columnOrder) {

        for(let i = 0; i < columnOrder.length; i++) {

            this.columnOrder[i] = _.get(this.data, JSON.parse(columnOrder[i]).join('.'));
        }

        this.globalEventDispatcher.publish(Settings.Events.REPORT_COLUMN_ORDER_UPDATED, this.data, this.columnOrder);

    }

    handlePropertyListItemClicked(property) {

        debugger;
        let uID = StringHelper.makeCharId();
        _.set(property, 'uID', uID);

        let propertyPath = property.joins.join('.');

        this.columnOrder.push(property);

        debugger;

        if(_.has(this.data, propertyPath)) {

            _.set(this.data, `${propertyPath}[${uID}]`, property);

        } else {
            _.set(this.data, propertyPath, {});
            _.set(this.data, `${propertyPath}[${uID}]`, property);
        }

        debugger;
        this.globalEventDispatcher.publish(Settings.Events.REPORT_PROPERTY_LIST_ITEM_ADDED, this.data, this.columnOrder);

    }

    handleReportRemoveFilterButtonPressed(joinPath) {

        let filterPath = joinPath.join('.');

        /**
         * If a referenced filter is being deleted we need to setup a new referenced filter and make
         * sure to update all the child (orFilters) to point to the new referenced filter
         */
        if(_.keys(_.get(this.data, `${filterPath}.orFilters`, [])).length !== 0) {

            let orFilterPaths = _.get(this.data, `${filterPath}.orFilters`);

            let orFilterPath = orFilterPaths[Object.keys(orFilterPaths)[0]];

            let uID = orFilterPath[orFilterPath.length-1];
            _.unset(orFilterPaths, uID);

            _.set(this.data, `${orFilterPath.join('.')}.referencedFilterPath`, []);
            _.set(this.data, `${orFilterPath.join('.')}.orFilters`, orFilterPaths);


            _.forOwn(orFilterPaths, (value, key) => {

                _.set(this.data, `${value.join('.')}.referencedFilterPath`, orFilterPath);

            });
        }

        let referencedFilterPath = _.get(this.data, `${filterPath}.referencedFilterPath`).join('.');

        _.unset(this.data, `${referencedFilterPath}.orFilters.${joinPath[joinPath.length - 1]}`);

        _.unset(this.data, filterPath);

        this.globalEventDispatcher.publish(Settings.Events.REPORT_FILTER_ITEM_REMOVED, this.data);

        debugger;

    }

    applyCustomFilterButtonPressedHandler(customFilter) {

        debugger;
        let filterPath = customFilter.joins.join('.') + `.filters`,
            referencedFilterPath = customFilter.referencedFilterPath.join('.'),
            uID = StringHelper.makeCharId();

        // if it has a joinPath we are editing the filter and th4e uID already exists
        if(_.has(customFilter, 'joinPath')) {

            filterPath = customFilter.joinPath.join('.');

            _.set(this.data, filterPath, customFilter);

        } else if(_.has(this.data, filterPath)) {

            _.set(this.data, `${filterPath}[${uID}]`, customFilter);

            _.set(this.data, `${filterPath}[${uID}].orFilters`, {});

            if(referencedFilterPath !== "") {

                let orFilterPath = customFilter.joins.concat(['filters', uID]);

                _.set(this.data, `${referencedFilterPath}.orFilters.${uID}`, orFilterPath);

            }

        } else {

            _.set(this.data, filterPath, {});

            _.set(this.data, `${filterPath}[${uID}]`, customFilter);

            _.set(this.data, `${filterPath}[${uID}].orFilters`, {});

            if(referencedFilterPath !== "") {

                let orFilterPath = customFilter.joins.concat(['filters', uID]);

                _.set(this.data, `${referencedFilterPath}.orFilters.${uID}`, orFilterPath);

            }
        }

        this.globalEventDispatcher.publish(Settings.Events.REPORT_FILTER_ITEM_ADDED, this.data);

    }

    handleReportRemoveSelectedColumnIconClicked(property) {

        debugger;
        let propertyPath = property.joins.concat([property.uID]).join('.');

        debugger;
        _.unset(this.data, propertyPath);
        debugger;


        // go ahead and remove the main filter
        this.columnOrder = $.grep(this.columnOrder, function(co){

            return !(property.uID === co.uID);

        });

        console.log(this.columnOrder);

        this.globalEventDispatcher.publish(Settings.Events.REPORT_PROPERTY_LIST_ITEM_REMOVED, this.data, this.columnOrder);
    }

    handleCustomObjectPropertyListItemClicked(property, joins) {

        debugger;

        let propertyPath = property.joins.join('.');

        if(!_.has(this.data, propertyPath)) {
            _.set(this.data, propertyPath, {});
        }

        this.globalEventDispatcher.publish(Settings.Events.REPORT_CUSTOM_OBJECT_JOIN_PATH_SET, property, joins, this.data);

    }

    handleReportCustomObjectFilterListItemClicked(property, joins) {

        debugger;
        let propertyPath = property.joins.join('.');

        if(!_.has(this.data, propertyPath)) {
            _.set(this.data, propertyPath, {});
        }

        this.globalEventDispatcher.publish(Settings.Events.REPORT_FILTER_CUSTOM_OBJECT_JOIN_PATH_SET, property, joins, this.data);

    }

    render() {

        this.$wrapper.html(ReportWidget.markup(this));
        new ReportSelectCustomObject($(ReportWidget._selectors.reportSelectCustomObjectContainer), this.globalEventDispatcher, this.portalInternalIdentifier);

    }

    _saveReport() {

        return new Promise((resolve, reject) => {
            debugger;
            const url = Routing.generate('save_report', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObject.internalName});

            $.ajax({
                url,
                method: 'POST',
                data: {'data': this.data, reportName: this.reportName, columnOrder: this.columnOrder}
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

    loadReportPreview() {
        return new Promise((resolve, reject) => {
            debugger;

            const url = Routing.generate('get_report_preview', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObject.internalName});

            $.ajax({
                url: url,
                data: {data: this.data, columnOrder: this.columnOrder}
            }).then(data => {
                debugger;
                resolve(data);
            }).catch(jqXHR => {
                debugger;
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }

    static markup() {

        return `
      <div class="js-report-widget c-report-widget">
            <div class="js-report-select-custom-object-container"></div>
            
            <div class="js-report-properties-container d-none"></div>
            
            <div class="js-report-filters-container d-none"></div>
            
      </div>
    `;
    }
}

export default ReportWidget;