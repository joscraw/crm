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
            reportName: []
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

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_NAME_CHANGED,
            this.handleReportNameChange.bind(this)
        );

        this.render();
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

    handleReportNameChange(reportName) {
        this.newData.reportName = reportName;
    }

    handleReportSaveButtonPressed(reportName) {
        this.newData.reportName = reportName;
        this._saveReport().then((data) => {
            debugger;
            swal("Woohoo!!!", "Report successfully saved. Redirecting to edit view...", "success");
            setTimeout(() => {
                this.redirectToEditView(data['reportId']);
            }, 3000);
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

    redirectToEditView(reportId) {
        window.location = Routing.generate('edit_report', {internalIdentifier: this.portalInternalIdentifier, 'reportId' : reportId});
    };

    reportBackToSelectCustomObjectButtonHandler(e) {
        // don't reshow the select custom object view for now. Just redirect back to the main reports view
        this.redirectToReportSettings();
        // todo refactor this in the future to possibly allow them to go back and reselect another custom object
        //  this is buggy though now as it's remembering state from the previously selected custom objects. Need to make sure
        //   we are unbinding and destroying all events or removing the tokens on the events and re-adding them
        //   this.globalEventDispatcher.singleSubscribe();

/*        this.$wrapper.find(ReportWidget._selectors.reportSelectCustomObjectContainer).removeClass('d-none');
        this.$wrapper.find(ReportWidget._selectors.reportPropertiesContainer).addClass('d-none');
        new ReportSelectCustomObject($(ReportWidget._selectors.reportSelectCustomObjectContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.newData.selectedCustomObject);*/
    }

    reinitializeData() {
        this.newData = {
            properties: {},
            filters: {},
            joins: {},
            selectedCustomObject: {},
            allAvailableProperties: [],
            reportName: ''
        };
    }

    handleAdvanceToReportPropertiesViewButtonClicked(customObject) {
        debugger;
        // reinitialize the data if a new object is being selected
        // (if a user has gone back to the select object view and selected a new object)
        if(this.newData.selectedCustomObject.id !== customObject.id) {
            this.reinitializeData();
        }
        // setup the default connection for pulling in properties if no connections exist yet
        // usually this is only when initially coming to the view
        if(_.isEmpty(this.newData.joins)) {
            let uID = StringHelper.makeCharId();
            _.set(this.newData.joins, uID, {connected_object: customObject});
        }
        this.newData.selectedCustomObject = customObject;
        this.$wrapper.find(ReportWidget._selectors.reportSelectCustomObjectContainer).addClass('d-none');
        this.$wrapper.find(ReportWidget._selectors.reportPropertiesContainer).removeClass('d-none');
        new ReportProperties($(ReportWidget._selectors.reportPropertiesContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.newData);
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
        this.globalEventDispatcher.publish('TEST', this.newData, this.newData.properties);
    }

    handleReportRemoveFilterButtonPressed(uid) {
        debugger;
        this._removeFilterByUid(uid);
        this.globalEventDispatcher.publish('TEST', this.newData, this.newData.properties);
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
        this.globalEventDispatcher.publish('TEST', this.newData, this.newData.properties);
        this.globalEventDispatcher.publish(Settings.Events.REPORT_CONNECTION_REMOVED, this.newData);
    }

    /**
     * This function takes a connection and removes any related properties for it
     * @param connection
     * @return {ReportWidget}
     * @private
     */
    _removePropertiesFromConnection(connection) {
        debugger;
        if(!_.isEmpty(this.newData.properties)) {
            for(let propertyId in this.newData.properties) {
                let property = this.newData.properties[propertyId];
                if(connection.relationship_property.join_direction === 'cross_join') {
                    if(connection.relationship_property.custom_object_id == property.custom_object_id) {
                        this._removeProperty(property);
                    }
                } else if(connection.relationship_property.join_direction === 'normal_join') {
                    if(connection.relationship_property.field.customObject.id == property.custom_object_id) {
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
     * @return {ReportWidget}
     * @private
     */
    _removeFiltersFromConnection(connection) {
        debugger;
        if(!_.isEmpty(this.newData.filters)) {
            for(let filterId in this.newData.filters) {
                let filter = this.newData.filters[filterId];
                if(connection.relationship_property.join_direction === 'cross_join') {
                    if(connection.relationship_property.custom_object_id == filter.custom_object_id) {
                        this._removeFilterByUid(filterId);
                    }
                } else if(connection.connected_object.join_direction === 'normal_join') {
                    if(connection.relationship_property.field.customObject.id == filter.custom_object_id) {
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
     * @return {ReportWidget}
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
     * @return {ReportWidget}
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
        swal("Yahoo!", `Filter successfully added!`, "success");
        this.globalEventDispatcher.publish('TEST', this.newData, this.newData.properties);
        this.globalEventDispatcher.publish(Settings.Events.REPORT_FILTER_ITEM_ADDED, this.newData);
    }

    handleReportRemoveSelectedColumnIconClicked(property) {
        debugger;
        this._removeProperty(property);
        this.globalEventDispatcher.publish('TEST', this.newData, this.newData.properties);
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
        this.globalEventDispatcher.publish('TEST', this.newData, this.newData.properties);
        this.globalEventDispatcher.publish(Settings.Events.REPORT_OBJECT_CONNECTED_JSON_UPDATED, this.newData, true);
        swal("Hooray!", `Object successfully connected!`, "success");
    }

    handleReportPropertyListRefreshed(properties) {
        debugger;
        if(properties.length === 0) {
            return;
        }
        this.newData.allAvailableProperties = properties;
        if(!_.isEmpty(this.newData.properties)) {
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
        this.globalEventDispatcher.publish('TEST', this.newData, this.newData.properties);
    }

    render() {
        this.$wrapper.html(ReportWidget.markup(this));
        new ReportSelectCustomObject($(ReportWidget._selectors.reportSelectCustomObjectContainer), this.globalEventDispatcher, this.portalInternalIdentifier);
    }

    /**
     * You have to pass the data up as JSON otherwise it will
     * get sent up as form data and lots of it will get truncated
     * @return {Promise<any>}
     * @private
     */
    _getReportResults() {
        return new Promise((resolve, reject) => {
            const url = Routing.generate('get_report_results', {internalIdentifier: this.portalInternalIdentifier, internalName: this.newData.selectedCustomObject.internalName});
            $.ajax({
                url,
                contentType: 'application/json',
                method: 'POST',
                data: JSON.stringify({data : this.newData})
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

    /**
     * You have to pass the data up as JSON otherwise it will
     * get sent up as form data and lots of it will get truncated
     *
     * @return {Promise<any>}
     * @private
     */
    _saveReport() {
        debugger;
        return new Promise((resolve, reject) => {
            const url = Routing.generate('save_report', {internalIdentifier: this.portalInternalIdentifier, internalName: this.newData.selectedCustomObject.internalName});
            $.ajax({
                url,
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({data : this.newData})
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

export default ReportWidget;