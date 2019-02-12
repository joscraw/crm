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

class FilterWidget {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.propertyGroups = [];
        this.lists = [];
        this.customFilters = [];
        this.customFilterJoin = null;

        this.globalEventDispatcher.subscribe(
            Settings.Events.APPLY_CUSTOM_FILTER_BUTTON_PRESSED,
            this.applyCustomFilterButtonPressedHandler.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.ADD_FILTER_BUTTON_CLICKED,
            this.handleAddFilterButtonClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.FILTER_BACK_TO_HOME_BUTTON_CLICKED,
            this.handleBackToHomeButtonClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.FILTER_PROPERTY_LIST_ITEM_CLICKED,
            this.handlePropertyListItemClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.FILTER_CUSTOM_OBJECT_PROPERTY_LIST_ITEM_CLICKED,
            this.handleCustomObjectPropertyListItemClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.FILTER_BACK_TO_LIST_BUTTON_CLICKED,
            this.filterFormBackToListButtonClickedHandler.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.EDIT_FILTER_BUTTON_CLICKED,
            this.handleEditFilterButtonClickedHandler.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.CUSTOM_FILTER_REMOVED,
            this.customFilterRemovedHandler.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.FILTER_ALL_RECORDS_BUTTON_PRESSED,
            this.customFilterAllRecordsButtonPressedHandler.bind(this)
        );

        this.render();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            propertyList: '.js-property-list',
            propertyForm: '.js-property-form',
            editPropertyForm: '.js-edit-property-form',
            filterNavigation: '.js-filter-navigation'
        }
    }

    customFilterRemovedHandler(key) {
        debugger;

        let customFilter = this.customFilters[key];
        let otherFiltersWithTheSameJoin = [];


        function clean(customFilters, customFilterToDelete) {

            debugger;

            let children = customFilters.filter(cf => {

                if('customFilterJoin' in customFilterToDelete) {
                    if(parseInt(cf.id) === parseInt(customFilterToDelete.customFilterJoin)) {
                        return true;
                    }
                    return false;
                }
            });


            this.customFilters = $.grep(customFilters, function(cf){

                if('customFilterJoin' in customFilterToDelete) {
                    if(parseInt(cf.id) === parseInt(customFilterToDelete.customFilterJoin)) {
                        return true;
                    }
                    return false;
                }

            });

            debugger;

        }

        debugger;
        clean(this.customFilters, customFilter);

        if('customFilterJoin' in customFilter) {
                otherFiltersWithTheSameJoin = this.customFilters.filter(cf => {

                return ('customFilterJoin' in cf && parseInt(cf.customFilterJoin) === parseInt(customFilter.customFilterJoin) && parseInt(cf.id) !== parseInt(customFilter.id));

            });
        }

        // remove the child filter and if no other child filters are attached to the parent then remove the parent as well
        this.customFilters = $.grep(this.customFilters, function(cf){

            if(parseInt(cf.id) === parseInt(customFilter.id)) {
                return false;
            }

            return !(otherFiltersWithTheSameJoin.length === 0 &&
                'customFilterJoin' in customFilter &&
                parseInt(cf.id) === parseInt(customFilter.customFilterJoin)
            );

        });


        debugger;

        this.globalEventDispatcher.publish(Settings.Events.FILTERS_UPDATED, this.customFilters);
    }

    customFilterAllRecordsButtonPressedHandler() {

        debugger;

        this.customFilters = [];

        this.globalEventDispatcher.publish(Settings.Events.FILTERS_UPDATED, this.customFilters);

    }

    filterFormBackToListButtonClickedHandler() {
        this.$wrapper.find(FilterWidget._selectors.propertyList).removeClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.propertyForm).addClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.editPropertyForm).addClass('d-none');
    }

    applyCustomFilterButtonPressedHandler(customFilter) {

        // Make sure that properties with the same id that belong to the same join override each other
        this.customFilters = $.grep(this.customFilters, function(cf){

            if(cf.id === customFilter.id && 'customFilterJoin' in cf && 'customFilterJoin' in customFilter) {

                return cf.customFilterJoin !== customFilter.customFilterJoin;
            }

            return cf.id !== customFilter.id;
        });

        this.customFilters.push(customFilter);

        this.$wrapper.find(FilterWidget._selectors.propertyList).addClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.filterNavigation).removeClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.propertyForm).addClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.editPropertyForm).addClass('d-none');

        /*this.globalEventDispatcher.publish(Settings.Events.CUSTOM_FILTER_ADDED, this.customFilters);*/
        this.globalEventDispatcher.publish(Settings.Events.FILTERS_UPDATED, this.customFilters);
    }

    render() {
        debugger;
        this.$wrapper.html(FilterWidget.markup(this));
        new FilterNavigation(this.$wrapper.find('.js-filter-navigation'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, this.customFilters);
    }

    handleAddFilterButtonClicked() {

        debugger;
        this.$wrapper.find(FilterWidget._selectors.filterNavigation).addClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.propertyList).removeClass('d-none');
        new FilterList(this.$wrapper.find('.js-property-list'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
    }

    handleBackToHomeButtonClicked() {
        this.$wrapper.find(FilterWidget._selectors.filterNavigation).removeClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.propertyList).addClass('d-none');
    }

    handlePropertyListItemClicked(property) {

        debugger;

        if(this.customFilterJoin) {
            property.customFilterJoin = this.customFilterJoin.id;
        }

        this.$wrapper.find(FilterWidget._selectors.propertyList).addClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.propertyForm).removeClass('d-none');

        this.renderFilterForm(property);
    }

    handleCustomObjectPropertyListItemClicked(property) {

        debugger;
        if(this.customFilterJoin) {
            property.customFilterJoin = this.customFilterJoin.id;
        }

        debugger;
        this.customFilterJoin = property;


        this.customFilters = $.grep(this.customFilters, function(cf){
            return cf.id !== property.id;
        });

        this.customFilters.push(property);

        new FilterList(this.$wrapper.find('.js-property-list'), this.globalEventDispatcher, this.portalInternalIdentifier, property.field.customObject.internalName, this.customFilterJoin);
    }

    renderFilterForm(property) {
        debugger;

        switch (property.fieldType) {
            case 'single_line_text_field':
            case 'multi_line_text_field':
                new SingleLineTextFieldFilterForm($(FilterWidget._selectors.propertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property);
                break;
            case 'number_field':
                new NumberFieldFilterForm($(FilterWidget._selectors.propertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property);
                break;
            case 'date_picker_field':
                new DatePickerFieldFilterForm($(FilterWidget._selectors.propertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property);
                break;
            case 'single_checkbox_field':
                new SingleCheckboxFieldFilterForm($(FilterWidget._selectors.propertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property);
                break;
            case 'dropdown_select_field':
            case 'radio_select_field':
                new DropdownSelectFieldFilterForm($(FilterWidget._selectors.propertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property);
                break;
            case 'multiple_checkbox_field':
                new MultilpleCheckboxFieldFilterForm($(FilterWidget._selectors.propertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property);
                break;
        }

    }

    handleEditFilterButtonClickedHandler(customFilter) {
        debugger;

        this.$wrapper.find(FilterWidget._selectors.filterNavigation).addClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.propertyList).addClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.editPropertyForm).removeClass('d-none');

        switch (customFilter.fieldType) {
            case 'single_line_text_field':
            case 'multi_line_text_field':
                new EditSingleLineTextFieldFilterForm($(FilterWidget._selectors.editPropertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, customFilter);
                break;
            case 'number_field':
                new EditNumberFieldFilterForm($(FilterWidget._selectors.editPropertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, customFilter);
                break;
            case 'date_picker_field':
                new EditDatePickerFieldFilterForm($(FilterWidget._selectors.editPropertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, customFilter);
                break;
            case 'single_checkbox_field':
                new EditSingleCheckboxFieldFilterForm($(FilterWidget._selectors.editPropertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, customFilter);
                break;
            case 'dropdown_select_field':
            case 'radio_select_field':
                new EditDropdownSelectFieldFilterForm($(FilterWidget._selectors.editPropertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, customFilter);
                break;
            case 'multiple_checkbox_field':
                new EditMultipleCheckboxFieldFilterForm($(FilterWidget._selectors.editPropertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, customFilter);
                break;
        }
    }

    static markup() {

        debugger;
        return `
      <div class="js-filter-widget c-filter-widget">
            <div class="js-filter-navigation"></div>
            <div class="js-property-list d-none"></div>
            <div class="js-property-form d-none"></div>
            <div class="js-edit-property-form d-none"></div>
      </div>
    `;
    }
}

export default FilterWidget;