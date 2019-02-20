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

class ReportWidget {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectId = null;

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
            reportSelectCustomObject: '.js-report-select-custom-object',
            reportPropertyList: '.js-report-property-list'

        }
    }

    handleCustomObjectForReportSelected(customObject) {
        this.customObject = customObject;

        debugger;

        this.$wrapper.find(ReportWidget._selectors.reportSelectCustomObject).addClass('d-none');
        this.$wrapper.find(ReportWidget._selectors.reportPropertyList).removeClass('d-none');

        new ReportPropertyList($('.js-report-property-list'), this.globalEventDispatcher, this.portalInternalIdentifier, customObject.internalName);


    }

    handlePropertyListItemClicked(property) {

        debugger;

        let propertyPath = property.joins.join('.');

        /*if(property.joins === "") {
            debugger;

            if(_.get(this.data, 'root', false) === false) {
                _.set(this.data, 'root', []);
            }

            _.get(this.data, 'root').push(property);

        } else */if(_.get(this.data, propertyPath, false)) {
            _.get(this.data, propertyPath).push(property);
            debugger;

        } else {
            debugger;
            _.set(this.data, propertyPath, []);
            _.get(this.data, propertyPath).push(property);
        }

        debugger;
        /*this.$wrapper.find(FilterWidget._selectors.propertyList).addClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.propertyForm).removeClass('d-none');

        this.renderFilterForm(property);*/

        this.globalEventDispatcher.publish(Settings.Events.REPORT_PROPERTY_LIST_ITEM_ADDED, this.data);
    }

    handleCustomObjectPropertyListItemClicked(property, joins) {

        debugger;

        new ReportPropertyList($('.js-report-property-list'), this.globalEventDispatcher, this.portalInternalIdentifier, property.field.customObject.internalName, property, joins);
    }

    render() {

        this.$wrapper.html(ReportWidget.markup(this));
        new ReportSelectCustomObject($('.js-report-select-custom-object'), this.globalEventDispatcher, this.portalInternalIdentifier);

    }

    static markup() {

        return `
      <div class="js-report-widget c-report-widget">
            <div class="js-report-select-custom-object"></div>
            <div class="js-report-property-list d-none"></div>
      </div>
    `;
    }
}

export default ReportWidget;