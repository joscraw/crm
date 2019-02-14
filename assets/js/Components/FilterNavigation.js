'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import FilterList from "./FilterList";
import ArrayHelper from "../ArrayHelper";
import StringHelper from "../StringHelper";

class FilterNavigation {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.propertyGroups = [];
        this.lists = [];
        this.customFilters = [];

        this.unbindEvents();

        this.$wrapper.on(
            'click',
            FilterNavigation._selectors.addFilterButton,
            this.handleAddFilterButtonClicked.bind(this)
        );

        this.$wrapper.on(
            'click',
            FilterNavigation._selectors.allRecordsButton,
            this.handleAllRecordsButtonClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.FILTERS_UPDATED,
            this.customFiltersUpdatedHandler.bind(this)
        );

        this.render();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            addFilterButton: '.js-add-filter-button',
            propertyList: '.js-property-list',
            allRecordsButton: '.js-all-records-button'
        }
    }

    customFiltersUpdatedHandler(customFilters) {

        debugger;

        this.customFilters = customFilters;
        this.activatePlugins();
    }

    /**
     * Because this component can keep getting run each time a filter is added
     * you need to remove the handlers otherwise they will keep stacking up
     */
    unbindEvents() {
        this.$wrapper.off('click', FilterNavigation._selectors.addFilterButton);
        this.$wrapper.off('click', FilterNavigation._selectors.allRecordsButton);
    }

    handleAllRecordsButtonClicked(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        this.globalEventDispatcher.publish(Settings.Events.FILTER_ALL_RECORDS_BUTTON_PRESSED);

    }

    activatePlugins() {
        debugger;

        this.$selectedProperties = $('#js-selected-properties').selectize({
            plugins: ['remove_button'],
            sortField: 'text'
        });

        this.$selectedProperties.selectize()[0].selectize.off('item_remove');

        this.$selectedProperties.selectize()[0].selectize.on('item_remove', (key) => {

            debugger;

            this.globalEventDispatcher.publish(Settings.Events.CUSTOM_FILTER_REMOVED, key);

        });

        this.$selectedProperties.selectize()[0].selectize.clear();
        this.$selectedProperties.selectize()[0].selectize.clearOptions();

        for(let i = 0; i < this.customFilters.length; i++) {
            debugger;
            let customFilter = this.customFilters[i];
            let value = "",
                values = "",
                label = "",
                joins = [];

            let customFilterJoins = customFilter.customFilterJoins.map((value) => {
                return value.label;
            });

            joins = Object.assign([], customFilterJoins);
            joins.push(customFilter.label);
            label = joins.join(" - ");

            debugger;


            switch(customFilter['fieldType']) {
                case 'custom_object_field':
                    // do nothing
                    break;
                case 'date_picker_field':
                    switch(customFilter['operator']) {
                        case 'EQ':

                            value = customFilter.value.trim() === '' ? '""' : `"${customFilter.value.trim()}"`;
                            this.$selectedProperties.selectize()[0].selectize.addOption({
                                value: i,
                                text: `${label} is equal to ${value}`
                            });
                            this.$selectedProperties.selectize()[0].selectize.addItem(i);

                            break;
                        case 'NEQ':

                            value = customFilter.value.trim() === '' ? '""' : `"${customFilter.value.trim()}"`;
                            this.$selectedProperties.selectize()[0].selectize.addOption({value:i, text: `${label} is not equal to ${value}`});
                            this.$selectedProperties.selectize()[0].selectize.addItem(i);

                            break;
                        case 'LT':

                            value = customFilter.value.trim() === '' ? '""' : `"${customFilter.value.trim()}"`;
                            this.$selectedProperties.selectize()[0].selectize.addOption({value:i, text: `${label} is before ${value}`});
                            this.$selectedProperties.selectize()[0].selectize.addItem(i);

                            break;
                        case 'GT':

                            value = customFilter.value.trim() === '' ? '""' : `"${customFilter.value.trim()}"`;
                            this.$selectedProperties.selectize()[0].selectize.addOption({value:i, text: `${label} is after ${value}`});
                            this.$selectedProperties.selectize()[0].selectize.addItem(i);

                            break;
                        case 'BETWEEN':

                            let lowValue = customFilter.low_value.trim() === '' ? '""' : `"${customFilter.low_value.trim()}"`;
                            let highValue = customFilter.high_value.trim() === '' ? '""' : `"${customFilter.high_value.trim()}"`;
                            this.$selectedProperties.selectize()[0].selectize.addOption({value:i, text: `${label} is between ${lowValue} and ${highValue}`});
                            this.$selectedProperties.selectize()[0].selectize.addItem(i);

                            break;
                        case 'HAS_PROPERTY':

                            this.$selectedProperties.selectize()[0].selectize.addOption({value:i, text: `${label} is known`});
                            this.$selectedProperties.selectize()[0].selectize.addItem(i);

                            break;
                        case 'NOT_HAS_PROPERTY':

                            this.$selectedProperties.selectize()[0].selectize.addOption({value:i, text: `${label} is unknown`});
                            this.$selectedProperties.selectize()[0].selectize.addItem(i);

                            break;
                    }
                    break;
                case 'single_checkbox_field':
                    debugger;
                    switch(customFilter['operator']) {
                        case 'IN':

                            debugger;
                            values = customFilter.value.split(",");

                            if(ArrayHelper.arraysEqual(values, ["0", "1"])) {
                                value = `"Yes" or "No"`;
                            } else if(ArrayHelper.arraysEqual(values, ["0"])) {
                                value = `"No"`;
                            } else if(ArrayHelper.arraysEqual(values, ["1"])) {
                                value = `"Yes"`;
                            }

                            this.$selectedProperties.selectize()[0].selectize.addOption({
                                value: i,
                                text: `${label} is any of ${value}`
                            });
                            this.$selectedProperties.selectize()[0].selectize.addItem(i);

                            break;
                        case 'NOT_IN':

                            debugger;
                            values = customFilter.value.split(",");

                            if(ArrayHelper.arraysEqual(values, ["0", "1"])) {
                                value = `"Yes" or "No"`;
                            } else if(ArrayHelper.arraysEqual(values, ["0"])) {
                                value = `"No"`;
                            } else if(ArrayHelper.arraysEqual(values, ["1"])) {
                                value = `"Yes"`;
                            }

                            this.$selectedProperties.selectize()[0].selectize.addOption({
                                value: i,
                                text: `${label} is none of ${value}`
                            });
                            this.$selectedProperties.selectize()[0].selectize.addItem(i);

                            break;
                        case 'HAS_PROPERTY':

                            this.$selectedProperties.selectize()[0].selectize.addOption({value:i, text: `${label} is known`});
                            this.$selectedProperties.selectize()[0].selectize.addItem(i);

                            break;
                        case 'NOT_HAS_PROPERTY':

                            this.$selectedProperties.selectize()[0].selectize.addOption({value:i, text: `${label} is unknown`});
                            this.$selectedProperties.selectize()[0].selectize.addItem(i);

                            break;
                    }
                    break;
                case 'dropdown_select_field':
                case 'multiple_checkbox_field':
                case 'radio_select_field':
                    debugger;
                    switch(customFilter['operator']) {
                        case 'IN':

                            values = customFilter.value.split(",");
                            values = values.join(" or ");

                            this.$selectedProperties.selectize()[0].selectize.addOption({
                                value: i,
                                text: `${label} is any of ${values}`
                            });
                            this.$selectedProperties.selectize()[0].selectize.addItem(i);

                            break;
                        case 'NOT_IN':

                            values = customFilter.value.split(",");
                            values = values.join(" or ");

                            this.$selectedProperties.selectize()[0].selectize.addOption({
                                value: i,
                                text: `${label} is none of ${values}`
                            });
                            this.$selectedProperties.selectize()[0].selectize.addItem(i);

                            break;
                        case 'HAS_PROPERTY':

                            this.$selectedProperties.selectize()[0].selectize.addOption({value:i, text: `${label} is known`});
                            this.$selectedProperties.selectize()[0].selectize.addItem(i);

                            break;
                        case 'NOT_HAS_PROPERTY':

                            this.$selectedProperties.selectize()[0].selectize.addOption({value:i, text: `${label} is unknown`});
                            this.$selectedProperties.selectize()[0].selectize.addItem(i);

                            break;
                    }
                    break;
                case 'single_line_text_field':
                case 'multi_line_text_field':
                case 'number_field':
                    switch(customFilter['operator']) {
                        case 'EQ':

                            value = customFilter.value.trim() === '' ? '""' : `"${customFilter.value.trim()}"`;
                            this.$selectedProperties.selectize()[0].selectize.addOption({value:i, text: `${label} contains exactly ${value}`});
                            this.$selectedProperties.selectize()[0].selectize.addItem(i);

                            break;
                        case 'NEQ':

                            value = customFilter.value.trim() === '' ? '""' : `"${customFilter.value.trim()}"`;
                            this.$selectedProperties.selectize()[0].selectize.addOption({value:i, text: `${label} doesn't contain exactly ${value}`});
                            this.$selectedProperties.selectize()[0].selectize.addItem(i);

                            break;
                        case 'LT':

                            value = customFilter.value.trim() === '' ? '""' : `"${customFilter.value.trim()}"`;
                            this.$selectedProperties.selectize()[0].selectize.addOption({value:i, text: `${label} is less than ${value}`});
                            this.$selectedProperties.selectize()[0].selectize.addItem(i);

                            break;
                        case 'GT':

                            value = customFilter.value.trim() === '' ? '""' : `"${customFilter.value.trim()}"`;
                            this.$selectedProperties.selectize()[0].selectize.addOption({value:i, text: `${label} is greater than ${value}`});
                            this.$selectedProperties.selectize()[0].selectize.addItem(i);

                            break;
                        case 'BETWEEN':

                            let lowValue = customFilter.low_value.trim() === '' ? '""' : `"${customFilter.low_value.trim()}"`;
                            let highValue = customFilter.high_value.trim() === '' ? '""' : `"${customFilter.high_value.trim()}"`;
                            this.$selectedProperties.selectize()[0].selectize.addOption({value:i, text: `${label} is between ${lowValue} and ${highValue}`});
                            this.$selectedProperties.selectize()[0].selectize.addItem(i);

                            break;
                        case 'HAS_PROPERTY':

                            this.$selectedProperties.selectize()[0].selectize.addOption({value:i, text: `${label} is known`});
                            this.$selectedProperties.selectize()[0].selectize.addItem(i);

                            break;
                        case 'NOT_HAS_PROPERTY':

                            this.$selectedProperties.selectize()[0].selectize.addOption({value:i, text: `${label} is unknown`});
                            this.$selectedProperties.selectize()[0].selectize.addItem(i);

                            break;
                    }
                    break;
            }
        }

        this.$wrapper.find('.remove').attr('data-bypass', true);


        $('.item').click((e) => {
            debugger;
            let $element = $(e.target);
            // we don't want to trigger this when the exit button is clicked
            if($element.closest('a').length){
                return;
            }

            let index = $element.attr('data-value');
            let customFilter = this.customFilters[index];

            this.globalEventDispatcher.publish(Settings.Events.EDIT_FILTER_BUTTON_CLICKED, customFilter);

        }) ;
    }

    render() {
        debugger;
        this.$wrapper.html(FilterNavigation.markup(this));
    }

    handleAddFilterButtonClicked() {
        this.globalEventDispatcher.publish(Settings.Events.ADD_FILTER_BUTTON_CLICKED);
    }

    static markup() {
        return `
    <ul class="nav nav-pills flex-column">
      <li class="nav-item">
        <a class="nav-link active js-all-records-button" href="#">All Records</a>
      </li>
      <li class="nav-item">
        <a class="nav-link disabled" href="javascript:void(0)">All Records</a>
      </li>
      <li class="nav-item js-selectized-property-container c-filter-widget__selected-filters">
        <div class="js-selectized-property-container">
            <input type="text" id="js-selected-properties">
        </div>
      </li>
      <li class="nav-item">
        <button type="button" class="btn btn-link js-add-filter-button"><i class="fa fa-plus"></i> Add Filter</button>
      </li>
    </ul>
    `;
    }
}

export default FilterNavigation;