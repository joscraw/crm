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
import UserFilterNavigation from "./UserFilterNavigation";
import UserFilterList from "./UserFilterList";
import StringHelper from "../StringHelper";

class UserFilterWidget {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;

        this.customFilters = {};

        this.globalEventDispatcher.subscribe(
            Settings.Events.APPLY_CUSTOM_FILTER_BUTTON_PRESSED,
            this.applyCustomFilterButtonPressedHandler.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.ADD_FILTER_BUTTON_CLICKED,
            this.handleAddFilterButtonClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.FILTER_PROPERTY_LIST_ITEM_CLICKED,
            this.handlePropertyListItemClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.FILTER_BACK_TO_HOME_BUTTON_CLICKED,
            this.handleBackToHomeButtonClicked.bind(this)
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

    customFilterRemovedHandler(path) {

        _.unset(this.customFilters, path);

        this.globalEventDispatcher.publish(Settings.Events.FILTERS_UPDATED, this.customFilters);
    }

    customFilterAllRecordsButtonPressedHandler() {

        this.customFilters = {};

        this.globalEventDispatcher.publish(Settings.Events.FILTERS_UPDATED, this.customFilters);

    }

    filterFormBackToListButtonClickedHandler() {
        this.$wrapper.find(UserFilterWidget._selectors.propertyList).removeClass('d-none');
        this.$wrapper.find(UserFilterWidget._selectors.propertyForm).addClass('d-none');
        this.$wrapper.find(UserFilterWidget._selectors.editPropertyForm).addClass('d-none');
    }

    applyCustomFilterButtonPressedHandler(customFilter) {

        let uID = StringHelper.makeCharId();

        if(_.has(customFilter, 'path')) {

            _.set(this.customFilters, customFilter.path, customFilter);

        } else {

            customFilter.path = uID;

            _.set(this.customFilters, uID, customFilter);
        }

        this.$wrapper.find(UserFilterWidget._selectors.propertyList).addClass('d-none');
        this.$wrapper.find(UserFilterWidget._selectors.filterNavigation).removeClass('d-none');
        this.$wrapper.find(UserFilterWidget._selectors.propertyForm).addClass('d-none');
        this.$wrapper.find(UserFilterWidget._selectors.editPropertyForm).addClass('d-none');

        this.globalEventDispatcher.publish(Settings.Events.FILTERS_UPDATED, this.customFilters);

    }

    render() {

        this.$wrapper.html(UserFilterWidget.markup(this));
        new UserFilterNavigation(this.$wrapper.find('.js-filter-navigation'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customFilters);
    }

    handleAddFilterButtonClicked() {

        this.$wrapper.find(UserFilterWidget._selectors.filterNavigation).addClass('d-none');
        this.$wrapper.find(UserFilterWidget._selectors.propertyList).removeClass('d-none');

        new UserFilterList(this.$wrapper.find('.js-property-list'), this.globalEventDispatcher, this.portalInternalIdentifier);
    }

    handleBackToHomeButtonClicked() {
        this.$wrapper.find(UserFilterWidget._selectors.filterNavigation).removeClass('d-none');
        this.$wrapper.find(UserFilterWidget._selectors.propertyList).addClass('d-none');
    }

    handlePropertyListItemClicked(property) {

        this.$wrapper.find(UserFilterWidget._selectors.propertyList).addClass('d-none');
        this.$wrapper.find(UserFilterWidget._selectors.propertyForm).removeClass('d-none');

        this.renderFilterForm(property);
    }

    renderFilterForm(property) {

        switch (property.fieldType) {
            case 'single_line_text_field':
            case 'multi_line_text_field':
                new SingleLineTextFieldFilterForm($(UserFilterWidget._selectors.propertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, null, property);
                break;
            case 'number_field':
                new NumberFieldFilterForm($(UserFilterWidget._selectors.propertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property);
                break;
            case 'date_picker_field':
                new DatePickerFieldFilterForm($(UserFilterWidget._selectors.propertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property);
                break;
            case 'single_checkbox_field':
                new SingleCheckboxFieldFilterForm($(UserFilterWidget._selectors.propertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, null, property);
                break;
            case 'dropdown_select_field':
            case 'radio_select_field':
                new DropdownSelectFieldFilterForm($(UserFilterWidget._selectors.propertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property);
                break;
            case 'multiple_checkbox_field':
                new MultilpleCheckboxFieldFilterForm($(UserFilterWidget._selectors.propertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, null, property);
                break;
        }

    }

    handleEditFilterButtonClickedHandler(path) {

        let customFilter;

        if(_.has(this.customFilters, path)) {

            customFilter = _.get(this.customFilters, path);

        }

        this.$wrapper.find(UserFilterWidget._selectors.filterNavigation).addClass('d-none');
        this.$wrapper.find(UserFilterWidget._selectors.propertyList).addClass('d-none');
        this.$wrapper.find(UserFilterWidget._selectors.editPropertyForm).removeClass('d-none');

        switch (customFilter.fieldType) {
            case 'single_line_text_field':
            case 'multi_line_text_field':
                new EditSingleLineTextFieldFilterForm($(UserFilterWidget._selectors.editPropertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, customFilter);
                break;
            case 'number_field':
                new EditNumberFieldFilterForm($(UserFilterWidget._selectors.editPropertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, customFilter);
                break;
            case 'date_picker_field':
                new EditDatePickerFieldFilterForm($(UserFilterWidget._selectors.editPropertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, customFilter);
                break;
            case 'single_checkbox_field':
                new EditSingleCheckboxFieldFilterForm($(UserFilterWidget._selectors.editPropertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, customFilter);
                break;
            case 'dropdown_select_field':
            case 'radio_select_field':
                new EditDropdownSelectFieldFilterForm($(UserFilterWidget._selectors.editPropertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, customFilter);
                break;
            case 'multiple_checkbox_field':
                new EditMultipleCheckboxFieldFilterForm($(UserFilterWidget._selectors.editPropertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, customFilter);
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

export default UserFilterWidget;