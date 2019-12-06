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

class EditReportWidget {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, reportId) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.reportId = reportId;

        /**
         * version 2.0
         * This newData object is the new data store for all the properties, filters, and joins
         * @type {{}}
         */
        this.newData = {
            properties: {},
            filters: {},
            joins: {},
            selectedCustomObject: {},
            allAvailableProperties: [],
            reportName: ''
        };

        this.globalEventDispatcher.subscribe(
            Settings.Events.ADVANCE_TO_REPORT_PROPERTIES_VIEW_BUTTON_CLICKED,
            this.handleAdvanceToReportPropertiesViewButtonClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_PROPERTY_LIST_ITEM_CLICKED,
            this.handlePropertyListItemClicked.bind(this)
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
            Settings.Events.REPORT_REMOVE_FILTER_BUTTON_PRESSED,
            this.handleReportRemoveFilterButtonPressed.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_REMOVE_CONNECTION_BUTTON_PRESSED,
            this.handleReportRemoveConnectionButtonPressed.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_SAVE_BUTTON_PRESSED,
            this.handleReportSaveButtonPressed.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_OBJECT_CONNECTED,
            this.handleReportObjectConnected.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_PROPERTY_LIST_REFRESHED,
            this.handleReportPropertyListRefreshed.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_ADD_AND_FILTER_BUTTON_PRESSED,
            this.reportAddAndFilterButtonPressedHandler.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_ADD_FILTER_BUTTON_PRESSED,
            this.reportAddFilterButtonPressedHandler.bind(this)
        );

        this.loadReport().then((data) => {
            debugger;
            this.newData = data.data;
            // when pulling the data from the database empty objects are returned as empty arrays.
            // Make sure we correct this and set them back to objects
            this.newData.properties = _.isEmpty(this.newData.properties) ? {} : this.newData.properties;
            this.newData.filters = _.isEmpty(this.newData.filters) ? {} : this.newData.filters;
            this.newData.joins = _.isEmpty(this.newData.joins) ? {} : this.newData.joins;
            this.newData.selectedCustomObject = _.isEmpty(this.newData.selectedCustomObject) ? {} : this.newData.selectedCustomObject;
            this.newData.allAvailableProperties = _.isEmpty(this.newData.allAvailableProperties) ? [] : this.newData.allAvailableProperties;
            this.render();
        });
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            reportSelectCustomObjectContainer: '.js-report-select-custom-object-container',
            reportPropertiesContainer: '.js-report-properties-container'
        }
    }

    unbindEvents() {}

    loadReport() {
        return new Promise((resolve, reject) => {
            debugger;
            const url = Routing.generate('get_report', {internalIdentifier: this.portalInternalIdentifier, reportId: this.reportId});

            $.ajax({
                url: url
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

    handleReportSaveButtonPressed(reportName) {
        this.newData.reportName = reportName;
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

    reportBackToSelectCustomObjectButtonHandler(e) {
        // on the edit view we aren't going to allow them to change the custom object after
        // the report has been created. Just go ahead and redirect to the reports lists view
        this.redirectToReportSettings();
    }

    handleAdvanceToReportPropertiesViewButtonClicked(customObject) {
        debugger;
        this.newData.selectedCustomObject = customObject;
        this.$wrapper.find(EditReportWidget._selectors.reportSelectCustomObjectContainer).addClass('d-none');
        this.$wrapper.find(EditReportWidget._selectors.reportPropertiesContainer).removeClass('d-none');
        new ReportProperties($(EditReportWidget._selectors.reportPropertiesContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.newData);
    }

    reportAddAndFilterButtonPressedHandler(parentFilterUid) {
        new ReportSelectPropertyForFilterFormModal(this.globalEventDispatcher, this.portalInternalIdentifier, this.newData.selectedCustomObject.internalName, this.newData, parentFilterUid);
    }

    reportAddFilterButtonPressedHandler() {
        new ReportSelectPropertyForFilterFormModal(this.globalEventDispatcher, this.portalInternalIdentifier, this.newData.selectedCustomObject.internalName, this.newData);
    }

    handlePropertyListItemClicked(property) {
        debugger;
        _.set(this.newData.properties, property.id, property);
        this._getReportResults().then((data) => {
            debugger;
            this.globalEventDispatcher.publish('TEST', data, this.newData.properties);
        });
    }

    handleReportRemoveFilterButtonPressed(uid) {
        debugger;
        this._removeFilterByUid(uid);
        this._getReportResults().then((data) => {
            this.globalEventDispatcher.publish('TEST', data, this.newData.properties);
        });
        this.globalEventDispatcher.publish(Settings.Events.REPORT_FILTER_ITEM_REMOVED, this.newData);
    }

    handleReportRemoveConnectionButtonPressed(connectionUid) {
        debugger;
        let connection = this.newData.joins[connectionUid];
        // if a parent connection is being removed take note that child connections (joins)
        // are dependent on their parent connection (join) so go ahead and remove any children
        if(_.has(connection, 'childConnections')) {
            let childConnections = connection.childConnections;
            for(let uid in childConnections) {
                let childConnection = childConnections[uid];
                // since you are removing each child connection, don't forget to clean up
                // and remove it's properties and filters
                this._removePropertiesFromConnection(childConnection)
                    ._removeFiltersFromConnection(childConnection);
                _.unset(this.newData.joins, uid);
            }
        }
        // if a child connection is being removed check to see if it has a parent connection
        // if it does then remove the child connection from it's parent
        if(_.has(connection, 'parentConnectionUid')) {
            let parentConnectionId = _.get(connection, 'parentConnectionUid');
            _.unset(this.newData.joins[parentConnectionId].childConnections, connectionUid);
        }
        // go ahead and remove any properties and filters that rely on this connection
        this._removePropertiesFromConnection(connection)
            ._removeFiltersFromConnection(connection);
        // go ahead and remove any filters that rely on this connection
        debugger;
        // Last but not least finally remove the main connection
        _.unset(this.newData.joins, connectionUid);
        this._getReportResults().then((data) => {
            this.globalEventDispatcher.publish('TEST', data, this.newData.properties);
            this.globalEventDispatcher.publish(Settings.Events.REPORT_CONNECTION_REMOVED, this.newData);
        });
    }

    /**
     * This function takes a connection and removes any related properties for it
     * @param connection
     * @return {EditReportWidget}
     * @private
     */
    _removePropertiesFromConnection(connection) {
        if(!_.isEmpty(this.newData.properties)) {
            for(let propertyId in this.newData.properties) {
                let property = this.newData.properties[propertyId];
                if(connection.connected_object.join_direction === 'cross_join') {
                    if(connection.connected_object.id == property.custom_object_id) {
                        this._removeProperty(property);
                    }
                } else if(connection.connected_object.join_direction === 'normal_join') {
                    if(connection.connected_property.field.customObject.id == property.custom_object_id) {
                        this._removeProperty(property);
                    }
                }
            }
        }
        return this;
    }

    /**
     * This function takes a connection and removes any related filters for it
     * @param connection
     * @return {EditReportWidget}
     * @private
     */
    _removeFiltersFromConnection(connection) {
        debugger;
        if(!_.isEmpty(this.newData.filters)) {
            for(let filterId in this.newData.filters) {
                let filter = this.newData.filters[filterId];
                if(connection.connected_object.join_direction === 'cross_join') {
                    if(connection.connected_object.id == filter.custom_object_id) {
                        this._removeFilterByUid(filterId);
                    }
                } else if(connection.connected_object.join_direction === 'normal_join') {
                    if(connection.connected_property.field.customObject.id == filter.custom_object_id) {
                        this._removeFilterByUid(filterId);
                    }
                }
            }
        }
        return this;
    }

    /**
     * Remove filter by uid
     * @param uid
     * @return {EditReportWidget}
     * @private
     */
    _removeFilterByUid(uid) {
        // remove the parent reference from the child filters
        if(_.has(this.newData.filters[uid], 'childFilters')) {
            let childFilters = this.newData.filters[uid].childFilters;
            for(let key in childFilters) {
                let childFilter = childFilters[key];
                _.unset(childFilter, 'hasParentFilter');
                _.unset(childFilter, 'parentFilterUid');
            }
        }
        // if a child filter is being removed check to see if it has a parent filter
        // if it does then remove the child filters from it's parent
        if(_.has(this.newData.filters[uid], 'parentFilterUid')) {
            debugger;
            let parentFilterId = _.get(this.newData.filters[uid], 'parentFilterUid');
            _.unset(this.newData.filters[parentFilterId].childFilters, uid);
        }
        _.unset(this.newData.filters, uid);
        return this;
    }

    /**
     * Remove property by object
     * @param property
     * @return {EditReportWidget}
     * @private
     */
    _removeProperty(property) {
        _.unset(this.newData.properties, property.id);
        return this;
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
        this._getReportResults().then((data) => {
            debugger;
            this.globalEventDispatcher.publish('TEST', data, this.newData.properties);
            swal("Yahoo!", `Filter successfully added!`, "success");
        });
        debugger;
        this.globalEventDispatcher.publish(Settings.Events.REPORT_FILTER_ITEM_ADDED, this.newData);
    }

    handleReportRemoveSelectedColumnIconClicked(property) {
        debugger;
        this._removeProperty(property);
        this._getReportResults().then((data) => {
            this.globalEventDispatcher.publish('TEST', data, this.newData.properties);
        });
    }

    handleReportObjectConnected(connectedData) {
        debugger;
        // make sure every join has a joins object itself so there can be nested joins
        if(!_.has(connectedData, 'joins')) {
            _.set(connectedData, 'joins', {});
        }
        // setup the new connection
        let uID = StringHelper.makeCharId();
        _.set(this.newData.joins, uID, connectedData);
        // if this is a child connection and has a parent then setup the relationship
        let parentConnection = null;
        if(_.has(connectedData, 'parentConnectionUid')) {
            parentConnection = _.get(this.newData.joins, connectedData.parentConnectionUid);
        }
        if(parentConnection) {
            if(!_.has(parentConnection, 'childConnections')) {
                _.set(parentConnection, 'childConnections', {});
            }
            _.set(parentConnection.childConnections, uID, connectedData);
        }
        this._getReportResults().then((data) => {
            this.globalEventDispatcher.publish('TEST', data, this.newData.properties);
            this.globalEventDispatcher.publish(Settings.Events.REPORT_OBJECT_CONNECTED_JSON_UPDATED, this.newData, true);
            swal("Hooray!", `Object successfully connected!`, "success");
        });
    }

    handleReportPropertyListRefreshed(properties) {
        debugger;
        if(properties.length === 0) {
            return;
        }
        // We need to set some initial properties so the table has some to show
        // Let's go ahead and set the first 6 properties on the object if that many exist
        if(_.isEmpty(this.newData.properties)) {
            for(let i = 0; i < properties.length; i++) {
                if(i === 5) {
                    break;
                }
                let property = properties[i];
                _.set(this.newData.properties, property.id, property);
            }
        }
        this.newData.allAvailableProperties = properties;
        this._getReportResults().then((data) => {
            debugger;
            this.globalEventDispatcher.publish('TEST', data, this.newData.properties);
        });
    }

    render() {
        debugger;
        this.$wrapper.html(EditReportWidget.markup(this));
        this.$wrapper.find(EditReportWidget._selectors.reportSelectCustomObjectContainer).addClass('d-none');
        this.$wrapper.find(EditReportWidget._selectors.reportPropertiesContainer).removeClass('d-none');
        new ReportProperties($(EditReportWidget._selectors.reportPropertiesContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.newData);
    }

    _getReportResults() {
        return new Promise((resolve, reject) => {
            const url = Routing.generate('get_report_results', {internalIdentifier: this.portalInternalIdentifier, internalName: this.newData.selectedCustomObject.internalName});
            $.ajax({
                url,
                method: 'POST',
                data: {'data': this.newData, reportName: this.newData.reportName}
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

    _saveReport() {
        return new Promise((resolve, reject) => {
            debugger;
            const url = Routing.generate('api_edit_report', {reportId: this.newData.reportId, internalIdentifier: this.portalInternalIdentifier, internalName: this.newData.selectedCustomObject.internalName});
            $.ajax({
                url,
                method: 'POST',
                data: {'data': this.newData, reportName: this.newData.reportName}
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
      </div>
    `;
    }
}

export default EditReportWidget;