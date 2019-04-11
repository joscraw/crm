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
import ReportProperties from "./ReportProperties";
import ListPropertyList from "./ListPropertyList";
import ListSelectedColumns from "./ListSelectedColumns";
import ListSelectedColumnsCount from "./ListSelectedColumnsCount";

class ListProperties {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, data, columnOrder) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.data = data;
        this.columnOrder = columnOrder;

        this.unbindEvents();

        this.bindEvents();

        this.globalEventDispatcher.subscribe(
            Settings.Events.LIST_BACK_BUTTON_CLICKED,
            this.handleBackButtonClicked.bind(this)
        );

        this.render();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {

            listSelectedColumnsContainer: '.js-list-selected-columns-container',
            listPropertyListContainer: '.js-list-property-list-container',
            listSelectedColumnsCountContainer: '.js-list-selected-columns-count-container',
            listBackToSelectCustomObjectButton: '.js-back-to-select-custom-object-button',
            listAdvanceToFiltersView: '.js-advance-to-filters-view'

        }
    }

    bindEvents() {

        this.$wrapper.on(
            'click',
            ListProperties._selectors.listBackToSelectCustomObjectButton,
            this.handleListBackToSelectCustomObjectButton.bind(this)
        );

        this.$wrapper.on(
            'click',
            ReportProperties._selectors.listAdvanceToFiltersView,
            this.handleListAdvanceToFiltersViewButtonClicked.bind(this)
        );

    }

    unbindEvents() {

        this.$wrapper.off('click', ListPropertyList._selectors.listBackToSelectCustomObjectButton);
        this.$wrapper.off('click', ListProperties._selectors.listAdvanceToFiltersView);
    }

    handleListAdvanceToFiltersViewButtonClicked(e) {

        let properties = this.getPropertiesFromData();

        if(Object.keys(properties).length === 0) {

            swal("Yikes!!!", "You need at least one property.", "warning");

            return;
        }

        debugger;
        this.globalEventDispatcher.publish(Settings.Events.ADVANCE_TO_REPORT_FILTERS_VIEW_BUTTON_CLICKED);

    }

    getPropertiesFromData() {

        let properties = {};
        function search(data) {

            for(let key in data) {

                if(key !== 'filters' && !_.has(data[key], 'uID')) {

                    search(data[key]);

                } else if(key === 'filters'){

                    continue;

                } else {

                    _.set(properties, key, data[key]);

                }
            }
        }

        debugger;
        search(this.data);

        return properties;
    }

    handleBackButtonClicked() {

        new ListPropertyList($(ReportProperties._selectors.reportPropertyListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, null, [], this.data);

    }

    handleCustomObjectJoinPathSet(property, joins, data) {

        debugger;
        new ReportPropertyList($(ReportProperties._selectors.reportPropertyListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, property.field.customObject.internalName, property, joins, data);

    }

    handleListBackToSelectCustomObjectButton(e) {

        this.globalEventDispatcher.publish(Settings.Events.LIST_BACK_TO_SELECT_CUSTOM_OBJECT_BUTTON_PRESSED, this.data);

    }

    render() {

        this.$wrapper.html(ListProperties.markup(this));

        new ListPropertyList($(ListProperties._selectors.listPropertyListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, null, [], this.data);

        new ListSelectedColumns(this.$wrapper.find(ListProperties._selectors.listSelectedColumnsContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.data, this.columnOrder);

        new ListSelectedColumnsCount(this.$wrapper.find(ListProperties._selectors.listSelectedColumnsCountContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.data, this.columnOrder);


    }

    static markup() {

        return `
             <nav class="navbar navbar-expand-sm l-top-bar justify-content-end c-report-widget__nav">
                  <button type="button" style="color: #FFF" class="btn btn-link js-back-to-select-custom-object-button"><i class="fa fa-angle-left" aria-hidden="true"></i> Back</button>
                 <button class="btn btn-lg btn-secondary ml-auto js-advance-to-filters-view">Next</button> 
             </nav> 
        
            <div class="row container">
                <div class="col-md-6 js-list-property-list-container"></div>
                <div class="col-md-6">
                
                    <div class="js-list-selected-columns-count-container c-column-editor__selected-columns-count"></div>
                    <div class="js-list-selected-columns-container c-report-widget__selected-columns"></div>
               
                </div>  
            </div>
    `;
    }
}

export default ListProperties;