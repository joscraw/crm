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
import ListSelectListType from "./ListSelectListType";
import ListSelectCustomObject from "./ListSelectCustomObject";
import ListProperties from "./ListProperties";
import ListFilters from "./ListFilters";

class ListWidget {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObject = null;
        this.listName = '';
        this.listType = null;

        /**
         * This data object is responsible for storing all the properties and filters that will get sent to the server
         * @type {{}}
         */
        this.data = {};

        this.columnOrder = [];

        /*this.unbindEvents();*/

        this.globalEventDispatcher.subscribe(
            Settings.Events.LIST_BACK_TO_SELECT_LIST_TYPE_BUTTON_CLICKED,
            this.handleBackToSelectListTypeButtonClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.ADVANCE_TO_LIST_SELECT_CUSTOM_OBJECT_VIEW_BUTTON_CLICKED,
            this.handleAdvanceToListSelectCustomObjectViewButtonClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.ADVANCE_TO_LIST_PROPERTIES_VIEW_BUTTON_CLICKED,
            this.handleAdvanceToListPropertiesViewButtonClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.LIST_BACK_TO_SELECT_CUSTOM_OBJECT_BUTTON_PRESSED,
            this.listBackToSelectCustomObjectButtonHandler.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.LIST_PROPERTY_LIST_ITEM_CLICKED,
            this.handlePropertyListItemClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.LIST_ADVANCE_TO_FILTERS_VIEW_BUTTON_CLICKED,
            this.handleListAdvanceToFiltersViewButtonClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.APPLY_CUSTOM_FILTER_BUTTON_PRESSED,
            this.applyCustomFilterButtonPressedHandler.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.LIST_REMOVE_FILTER_BUTTON_PRESSED,
            this.handleListRemoveFilterButtonPressed.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.LIST_BACK_TO_PROPERTIES_BUTTON_PRESSED,
            this.handleListBackToPropertiesButtonPressed.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.LIST_CUSTOM_OBJECT_FILTER_LIST_ITEM_CLICKED,
            this.handleListCustomObjectFilterListItemClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.LIST_PREVIEW_RESULTS_BUTTON_CLICKED,
            this.handleListPreviewResultsButtonClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.LIST_NAME_CHANGED,
            this.handleListNameChange.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.LIST_REMOVE_SELECTED_COLUMN_ICON_CLICKED,
            this.handleListRemoveSelectedColumnIconClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.LIST_COLUMN_ORDER_CHANGED,
            this.handleListColumnOrderChanged.bind(this)
        );

        /*



                this.globalEventDispatcher.subscribe(
                    Settings.Events.REPORT_SAVE_BUTTON_PRESSED,
                    this.handleReportSaveButtonPressed.bind(this)
                );





               */

        this.render();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            listSelectListTypeContainer: '.js-list-select-list-type-container',
            listSelectCustomObjectContainer: '.js-list-select-custom-object-container',
            listPropertiesContainer: '.js-list-properties-container',
            listFiltersContainer: '.js-list-filters-container'

        }
    }

    unbindEvents() {}

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

    handleListPreviewResultsButtonClicked() {

        this.loadReportPreview().then((data) => {

            debugger;
            this.globalEventDispatcher.publish(Settings.Events.LIST_PREVIEW_RESULTS_LOADED, data.data, this.columnOrder);

        });

    }

    handleAdvanceToListPropertiesViewButtonClicked(customObject) {

        debugger;

        this.customObject = customObject;

        this.$wrapper.find(ListWidget._selectors.listSelectCustomObjectContainer).addClass('d-none');
        this.$wrapper.find(ListWidget._selectors.listPropertiesContainer).removeClass('d-none');

        new ListProperties($(ListWidget._selectors.listPropertiesContainer), this.globalEventDispatcher, this.portalInternalIdentifier, customObject.internalName, this.data, this.columnOrder);

    }

    redirectToReportSettings() {

        window.location = Routing.generate('report_settings', {internalIdentifier: this.portalInternalIdentifier});
    }

    handleListNameChange(listName) {
        debugger;
        this.listName = listName;
    }

    listBackToSelectCustomObjectButtonHandler(e) {

        debugger;
        this.$wrapper.find(ListWidget._selectors.listSelectCustomObjectContainer).removeClass('d-none');
        this.$wrapper.find(ListWidget._selectors.listPropertiesContainer).addClass('d-none');

        new ListSelectCustomObject($(ListWidget._selectors.listSelectCustomObjectContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObject);

    }

    handleListBackToPropertiesButtonPressed() {
        debugger;

        this.$wrapper.find(ListWidget._selectors.listFiltersContainer).addClass('d-none');
        this.$wrapper.find(ListWidget._selectors.listPropertiesContainer).removeClass('d-none');

        /*new ReportProperties($(ReportWidget._selectors.reportPropertiesContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObject.internalName, this.data);*/

    }

    handleListAdvanceToFiltersViewButtonClicked(e) {

        debugger;
        this.$wrapper.find(ListWidget._selectors.listFiltersContainer).removeClass('d-none');
        this.$wrapper.find(ListWidget._selectors.listPropertiesContainer).addClass('d-none');

        new ListFilters($(ListWidget._selectors.listFiltersContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObject.internalName, this.data, this.listName);

    }

    handleAdvanceToListSelectCustomObjectViewButtonClicked(listType) {

        debugger;

        // If a brand new custom object is selected then clear the data
        if(this.listType && this.listType.name !== listType.name) {
            this.customObject = null;
        }

        this.listType = listType;

        this.$wrapper.find(ListWidget._selectors.listSelectListTypeContainer).addClass('d-none');
        this.$wrapper.find(ListWidget._selectors.listSelectCustomObjectContainer).removeClass('d-none');

        new ListSelectCustomObject($(ListWidget._selectors.listSelectCustomObjectContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObject);

    }

    handleBackToSelectListTypeButtonClicked() {

        debugger;
        this.$wrapper.find(ListWidget._selectors.listSelectListTypeContainer).removeClass('d-none');
        this.$wrapper.find(ListWidget._selectors.listSelectCustomObjectContainer).addClass('d-none');

        new ListSelectListType($(ListWidget._selectors.listSelectListTypeContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.listType);


    }

    handleListColumnOrderChanged(columnOrder) {

        debugger;
        for(let i = 0; i < columnOrder.length; i++) {

            this.columnOrder[i] = _.get(this.data, JSON.parse(columnOrder[i]).join('.'));
        }

        this.globalEventDispatcher.publish(Settings.Events.LIST_COLUMN_ORDER_UPDATED, this.data, this.columnOrder);

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
        this.globalEventDispatcher.publish(Settings.Events.LIST_PROPERTY_LIST_ITEM_ADDED, this.data, this.columnOrder);

    }

    handleListRemoveFilterButtonPressed(joinPath) {

        debugger;

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

        this.globalEventDispatcher.publish(Settings.Events.LIST_FILTER_ITEM_REMOVED, this.data);

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

        this.globalEventDispatcher.publish(Settings.Events.LIST_FILTER_ITEM_ADDED, this.data);

    }

    handleListRemoveSelectedColumnIconClicked(property) {

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

        this.globalEventDispatcher.publish(Settings.Events.LIST_PROPERTY_LIST_ITEM_REMOVED, this.data, this.columnOrder);
    }

    handleListCustomObjectFilterListItemClicked(property, joins) {

        debugger;
        let propertyPath = property.joins.join('.');

        if(!_.has(this.data, propertyPath)) {
            _.set(this.data, propertyPath, {});
        }

        this.globalEventDispatcher.publish(Settings.Events.LIST_FILTER_CUSTOM_OBJECT_JOIN_PATH_SET, property, joins, this.data);

    }

    render() {

        this.$wrapper.html(ListWidget.markup(this));

        new ListSelectListType($(ListWidget._selectors.listSelectListTypeContainer), this.globalEventDispatcher, this.portalInternalIdentifier);
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

            const url = Routing.generate('get_list_preview', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObject.internalName});

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
            <div class="js-list-select-list-type-container"></div>
            
            <div class="js-list-select-custom-object-container d-none"></div>
            
            <div class="js-list-properties-container d-none"></div>
            
            <div class="js-list-filters-container d-none"></div>
            
      </div>
    `;
    }
}

export default ListWidget;