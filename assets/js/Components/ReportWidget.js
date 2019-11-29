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
import ReportFilterList from "./ReportFilterList";
import ReportFilterNavigationModal from "./ReportFilterNavigationModal";
import ReportAddFilterFormModal from "./ReportAddFilterFormModal";
import ReportSelectPropertyForFilterFormModal from "./ReportSelectPropertyForFilterFormModal";

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

        // todo consider storing the entire state of this SPA in this array including all available properties
        //  this would make managing the app easier when you get to the edit report builder
        /**
         * version 2.0
         * This newData object is the new data store for all the properties, filters, and joins
         * @type {{}}
         */
        this.newData = {
            properties: {},
            filters: {},
            joins: [],
        };

        /**
         * This array holds all the available properties even after other objects are connected through the relationship
         * @type {Array}
         */
        this.availableProperties = [];

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
            Settings.Events.REPORT_OBJECT_CONNECTED,
            this.handleReportObjectConnected.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_INITIAL_PROPERTIES_LOADED,
            this.handleReportInitialPropertiesLoaded.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_ADD_AND_FILTER_BUTTON_PRESSED,
            this.reportAddAndFilterButtonPressedHandler.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_ADD_FILTER_BUTTON_PRESSED,
            this.reportAddFilterButtonPressedHandler.bind(this)
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
        this._saveReport().then((data) => {
            swal("Woohoo!!!", "Report successfully saved.", "success");
        }).catch((errorData) => {
            if(errorData.httpCode === 401) {
                swal("Woah!", `You don't have proper permissions for this!`, "error");
                return;
            }
        });
    }

    redirectToReportSettings() {
        window.location = Routing.generate('report_settings', {internalIdentifier: this.portalInternalIdentifier});
    }

    handleReportNameChange(reportName) {
        this.reportName = reportName;
    }

    reportBackToSelectCustomObjectButtonHandler(e) {
        this.$wrapper.find(ReportWidget._selectors.reportSelectCustomObjectContainer).removeClass('d-none');
        this.$wrapper.find(ReportWidget._selectors.reportPropertiesContainer).addClass('d-none');
        new ReportSelectCustomObject($(ReportWidget._selectors.reportSelectCustomObjectContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObject);
    }

    handleReportBackToPropertiesButtonPressed() {
        this.$wrapper.find(ReportWidget._selectors.reportFiltersContainer).addClass('d-none');
        this.$wrapper.find(ReportWidget._selectors.reportPropertiesContainer).removeClass('d-none');
        new ReportProperties($(ReportWidget._selectors.reportPropertiesContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObject.internalName, this.data, this.columnOrder, this.customObject);
    }

    handleReportAdvanceToFiltersViewButtonClicked(e) {
        this.$wrapper.find(ReportWidget._selectors.reportFiltersContainer).removeClass('d-none');
        this.$wrapper.find(ReportWidget._selectors.reportPropertiesContainer).addClass('d-none');
        new ReportFilters($(ReportWidget._selectors.reportFiltersContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObject.internalName, this.data, this.reportName, this.columnOrder);
    }

    handleAdvanceToReportPropertiesViewButtonClicked(customObject) {
        // If a brand new custom object is selected then clear the data
        if(this.customObject && this.customObject.id !== customObject.id) {
            this.data = {};
            this.columnOrder = [];
        }
        debugger;
        this.customObject = customObject;
        this.newData.name = customObject.internalName;
        // set up the initial object to pull down associated properties
       /* let uID = StringHelper.makeCharId();
        _.set(this.newData.joins, uID, {connected_object: customObject});*/
        this.$wrapper.find(ReportWidget._selectors.reportSelectCustomObjectContainer).addClass('d-none');
        this.$wrapper.find(ReportWidget._selectors.reportPropertiesContainer).removeClass('d-none');
        new ReportProperties($(ReportWidget._selectors.reportPropertiesContainer), this.globalEventDispatcher, this.portalInternalIdentifier, customObject.internalName, this.data, this.columnOrder, this.customObject);
    }

    handleReportColumnOrderChanged(columnOrder) {
        for(let i = 0; i < columnOrder.length; i++) {
            this.columnOrder[i] = _.get(this.data, JSON.parse(columnOrder[i]).join('.'));
        }
        this.globalEventDispatcher.publish(Settings.Events.REPORT_COLUMN_ORDER_UPDATED, this.data, this.columnOrder);
    }

    reportAddAndFilterButtonPressedHandler(parentFilterUid) {
        new ReportSelectPropertyForFilterFormModal(this.globalEventDispatcher, this.portalInternalIdentifier, this.customObject.internalName, this.newData, parentFilterUid);
    }

    reportAddFilterButtonPressedHandler() {
        new ReportSelectPropertyForFilterFormModal(this.globalEventDispatcher, this.portalInternalIdentifier, this.customObject.internalName, this.newData);
    }

    handlePropertyListItemClicked(property) {
        debugger;
        _.set(this.newData.properties, property.id, property);

        debugger;
        this._saveReport().then((data) => {
            debugger;
            this.globalEventDispatcher.publish('TEST', data, this.newData.properties);
        });

       /* let uID = StringHelper.makeCharId();
        _.set(property, 'uID', uID);
        let propertyPath = property.joins.join('.');
        this.columnOrder.push(property);
        if(_.has(this.data, propertyPath)) {
            _.set(this.data, `${propertyPath}[${uID}]`, property);
        } else {
            _.set(this.data, propertyPath, {});
            _.set(this.data, `${propertyPath}[${uID}]`, property);
        }
        this.globalEventDispatcher.publish(Settings.Events.REPORT_PROPERTY_LIST_ITEM_ADDED, this.data, this.columnOrder);*/
    }

    handleReportRemoveFilterButtonPressed(uid) {
        debugger;
        // remove the parent reference from the child filters
        if(_.has(this.newData.filters[uid], 'childFilters')) {
            let childFilters = this.newData.filters[uid].childFilters;
            for(let key in childFilters) {
                let childFilter = childFilters[key];
                _.unset(childFilter, 'hasParentFilter');
                _.unset(childFilter, 'parentFilterUid');
            }
        }
        _.unset(this.newData.filters, uid);
        this._saveReport().then((data) => {
            this.globalEventDispatcher.publish('TEST', data, this.newData.properties);
        });
        this.globalEventDispatcher.publish(Settings.Events.REPORT_FILTER_ITEM_REMOVED, this.newData);
    }

    applyCustomFilterButtonPressedHandler(customFilter) {
        debugger;
        // setup the new filter
        let uID = StringHelper.makeCharId();
        _.set(this.newData.filters, uID, customFilter);

        // if this is a child filter and has a parent then setup the relationship
        let parentFilter = null;
        if(_.has(customFilter, 'parentFilterUid')) {
            parentFilter = _.get(this.newData.filters, customFilter.parentFilterUid);
        }
        if(parentFilter) {
            if(!_.has(parentFilter, 'childFilters')) {
                _.set(parentFilter, 'childFilters', {});
            }
            _.set(parentFilter.childFilters, uID, customFilter);
        }
        this._saveReport().then((data) => {
            debugger;
            this.globalEventDispatcher.publish('TEST', data, this.newData.properties);
            swal("Yahoo!", `Filter successfully added!`, "success");
        });
        debugger;
        this.globalEventDispatcher.publish(Settings.Events.REPORT_FILTER_ITEM_ADDED, this.newData);
    }

    handleReportRemoveSelectedColumnIconClicked(property) {
        debugger;
        _.unset(this.newData.properties, property.id);
        this._saveReport().then((data) => {
            this.globalEventDispatcher.publish('TEST', data, this.newData.properties);
        });
    }

    handleCustomObjectPropertyListItemClicked(property, joins) {
        let propertyPath = property.joins.join('.');
        if(!_.has(this.data, propertyPath)) {
            _.set(this.data, propertyPath, {});
        }
        this.globalEventDispatcher.publish(Settings.Events.REPORT_CUSTOM_OBJECT_JOIN_PATH_SET, property, joins, this.data);
    }

    handleReportCustomObjectFilterListItemClicked(property, joins) {
        let propertyPath = property.joins.join('.');
        if(!_.has(this.data, propertyPath)) {
            _.set(this.data, propertyPath, {});
        }
        this.globalEventDispatcher.publish(Settings.Events.REPORT_FILTER_CUSTOM_OBJECT_JOIN_PATH_SET, property, joins, this.data);
    }

    handleReportObjectConnected(connectedData) {
        debugger;
        // make sure every join has a joins object itself so there can be nested joins
        if(!_.has(connectedData, 'joins')) {
            _.set(connectedData, 'joins', {});
        }
        this.newData.joins.push(connectedData);
        this._saveReport().then((data) => {
            this.globalEventDispatcher.publish('TEST', data, this.newData.properties);
            this.globalEventDispatcher.publish(Settings.Events.REPORT_OBJECT_CONNECTED_JSON_UPDATED, this.newData, true);
            swal("Hooray!", `Object successfully connected!`, "success");
        });
    }

    handleReportInitialPropertiesLoaded(properties) {
        if(properties.length === 0) {
            return;
        }
        for(let property of properties) {
            _.set(this.newData.properties, property.id, property);
        }
        this._saveReport().then((data) => {
            debugger;
            this.globalEventDispatcher.publish('TEST', data, this.newData.properties);
        });
    }

    render() {
        this.$wrapper.html(ReportWidget.markup(this));
        new ReportSelectCustomObject($(ReportWidget._selectors.reportSelectCustomObjectContainer), this.globalEventDispatcher, this.portalInternalIdentifier);
    }

    _saveReport() {
        return new Promise((resolve, reject) => {
            const url = Routing.generate('save_report', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObject.internalName});
            $.ajax({
                url,
                method: 'POST',
                data: {'data': this.newData, reportName: this.reportName, columnOrder: this.columnOrder}
            }).then((data, textStatus, jqXHR) => {
                resolve(data);
            }).catch((jqXHR) => {
                debugger;
                const errorData = JSON.parse(jqXHR.responseText);
                errorData.httpCode = jqXHR.status;
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