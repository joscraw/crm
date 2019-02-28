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

    reportBackToSelectCustomObjectButtonHandler(e) {

        this.$wrapper.find(ReportWidget._selectors.reportSelectCustomObjectContainer).removeClass('d-none');
        this.$wrapper.find(ReportWidget._selectors.reportPropertiesContainer).addClass('d-none');

        new ReportSelectCustomObject($(ReportWidget._selectors.reportSelectCustomObjectContainer), this.globalEventDispatcher, this.portalInternalIdentifier);

    }

    handleReportAdvanceToFiltersViewButtonClicked(e) {

        debugger;
        this.$wrapper.find(ReportWidget._selectors.reportFiltersContainer).removeClass('d-none');
        this.$wrapper.find(ReportWidget._selectors.reportPropertiesContainer).addClass('d-none');

        new ReportFilters($(ReportWidget._selectors.reportFiltersContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObject.internalName);

    }

    handleAdvanceToReportPropertiesViewButtonClicked(customObject) {

        this.customObject = customObject;

        this.$wrapper.find(ReportWidget._selectors.reportSelectCustomObjectContainer).addClass('d-none');
        this.$wrapper.find(ReportWidget._selectors.reportPropertiesContainer).removeClass('d-none');

        new ReportProperties($(ReportWidget._selectors.reportPropertiesContainer), this.globalEventDispatcher, this.portalInternalIdentifier, customObject.internalName, this.data);

    }

    handlePropertyListItemClicked(property) {

        let propertyPath = property.joins.join('.');

        if(_.get(this.data, propertyPath, false)) {

            _.get(this.data, propertyPath).push(property);

        } else {
            _.set(this.data, propertyPath, []);
            _.get(this.data, propertyPath).push(property);
        }

        this.globalEventDispatcher.publish(Settings.Events.REPORT_PROPERTY_LIST_ITEM_ADDED, this.data);

    }

    handleReportRemoveFilterButtonPressed(joinPath) {

        debugger;

        let filterPath = joinPath.join('.');

        let referencedFilterPath = _.get(this.data, `${filterPath}.referencedFilterPath`).join('.');

        _.unset(this.data, `${referencedFilterPath}.orFilters.${joinPath[joinPath.length - 1]}`);

        debugger;

        _.unset(this.data, filterPath);

        this.globalEventDispatcher.publish(Settings.Events.REPORT_FILTER_ITEM_REMOVED, this.data);

        debugger;

    }

    applyCustomFilterButtonPressedHandler(customFilter) {

        debugger;

        let filterPath = customFilter.joins.join('.') + `.filters`,
            referencedFilterPath = customFilter.referencedFilterPath.join('.'),
            uID = StringHelper.makeCharId();

        if(_.get(this.data, filterPath, false)) {

            debugger;

            _.set(this.data, `${filterPath}[${uID}]`, customFilter);

            _.set(this.data, `${filterPath}[${uID}].orFilters`, {});

            debugger;

            if(referencedFilterPath !== "") {

                let orFilterPath = customFilter.joins.concat(['filters', uID]);


                _.set(this.data, `${referencedFilterPath}.orFilters.${uID}`, orFilterPath);

                /*_.get(this.data, `${referencedFilterPath}.orFilters`).push(orFilterPath);*/
            }

        } else {
            debugger;
            _.set(this.data, filterPath, {});

            _.set(this.data, `${filterPath}[${uID}]`, customFilter);

            _.set(this.data, `${filterPath}[${uID}].orFilters`, {});

            if(referencedFilterPath !== "") {

                let orFilterPath = customFilter.joins.concat(['filters', uID]);

                _.set(this.data, `${referencedFilterPath}.orFilters.${uID}`, orFilterPath);

                /*_.get(this.data, `${referencedFilterPath}.orFilters`).push(orFilterPath);*/

            }
        }

        this.globalEventDispatcher.publish(Settings.Events.REPORT_FILTER_ITEM_ADDED, this.data);

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

    handleCustomObjectPropertyListItemClicked(property, joins) {

        debugger;

        let propertyPath = property.joins.join('.');

        if(!_.has(this.data, propertyPath)) {
            _.set(this.data, propertyPath, []);
        }

        this.globalEventDispatcher.publish(Settings.Events.REPORT_CUSTOM_OBJECT_JOIN_PATH_SET, property, joins, this.data);

    }

    handleReportCustomObjectFilterListItemClicked(property, joins) {

        debugger;
        let propertyPath = property.joins.join('.');

        if(!_.has(this.data, propertyPath)) {
            _.set(this.data, propertyPath, []);
        }

        this.globalEventDispatcher.publish(Settings.Events.REPORT_FILTER_CUSTOM_OBJECT_JOIN_PATH_SET, property, joins, this.data);

    }

    render() {

        this.$wrapper.html(ReportWidget.markup(this));
        new ReportSelectCustomObject($(ReportWidget._selectors.reportSelectCustomObjectContainer), this.globalEventDispatcher, this.portalInternalIdentifier);

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