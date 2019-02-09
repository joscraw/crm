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
            Settings.Events.FILTER_BACK_TO_LIST_BUTTON_CLICKED,
            this.filterFormBackToListButtonClickedHandler.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.EDIT_FILTER_BUTTON_CLICKED,
            this.handleEditFilterButtonClickedHandler.bind(this)
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

    filterFormBackToListButtonClickedHandler() {
        this.$wrapper.find(FilterWidget._selectors.propertyList).removeClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.propertyForm).addClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.editPropertyForm).addClass('d-none');
    }

    applyCustomFilterButtonPressedHandler(customFilter) {

        this.$wrapper.find(FilterWidget._selectors.propertyList).addClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.filterNavigation).removeClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.propertyForm).addClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.editPropertyForm).addClass('d-none');
    }

    render() {
        debugger;
        this.$wrapper.html(FilterWidget.markup(this));
        new FilterNavigation(this.$wrapper.find('.js-filter-navigation'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
    }

    handleAddFilterButtonClicked() {

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

        this.$wrapper.find(FilterWidget._selectors.propertyList).addClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.propertyForm).removeClass('d-none');

        this.renderFilterForm(property);
    }

    renderFilterForm(property) {
        debugger;

        switch (property.fieldType) {
            case 'single_line_text_field':
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