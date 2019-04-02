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

class UserFilterNavigation {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;

        this.customFilters = [];

        this.unbindEvents();

        this.$wrapper.on(
            'click',
            UserFilterNavigation._selectors.addFilterButton,
            this.handleAddFilterButtonClicked.bind(this)
        );


        this.$wrapper.on(
            'click',
            UserFilterNavigation._selectors.allRecordsButton,
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

        this.customFilters = customFilters;
        this.activatePlugins(customFilters);
    }

    /**
     * Because this component can keep getting run each time a filter is added
     * you need to remove the handlers otherwise they will keep stacking up
     */
    unbindEvents() {
        this.$wrapper.off('click', UserFilterNavigation._selectors.addFilterButton);

        this.$wrapper.off('click', UserFilterNavigation._selectors.allRecordsButton);
    }

    handleAllRecordsButtonClicked(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        this.globalEventDispatcher.publish(Settings.Events.FILTER_ALL_RECORDS_BUTTON_PRESSED);

    }

    activatePlugins(customFilters) {


        let filters = {};
        function search(data) {

            for(let key in data) {

                if(isNaN(key) && key !== 'filters') {

                    search(data[key]);

                } else if(key === 'filters'){

                    for(let uID in data[key]) {

                        _.set(filters, uID, data[key][uID]);

                    }
                }
            }
        }

        search(customFilters);

        this.$selectedProperties = $('#js-selected-properties').selectize({
            plugins: ['remove_button'],
            sortField: 'text',

            render: {
                item: function(data, escape) {

                    debugger;
                    return "<div data-value='"+data.value+"' data-join-path='"+data.joinPath+"' class='item'>"+data.text+" </div>";
                }
            },
        });

        this.$selectedProperties.selectize()[0].selectize.off('item_remove');

        this.$selectedProperties.selectize()[0].selectize.on('item_remove', (value, $item) => {

            debugger;
            let joinPath = JSON.parse($item.closest('div').attr('data-join-path'));

            this.globalEventDispatcher.publish(Settings.Events.CUSTOM_FILTER_REMOVED, joinPath);

        });

        this.$selectedProperties.selectize()[0].selectize.clear();
        this.$selectedProperties.selectize()[0].selectize.clearOptions();


        let i = 0;
        for(let uID in filters) {

            debugger;
            let customFilter = _.get(filters, uID, false);

            let text = this.getFilterTextFromCustomFilter(customFilter);

            this.$selectedProperties.selectize()[0].selectize.addOption({value:i, text: text, joinPath: JSON.stringify(customFilter.joins.concat(['filters', uID]))});
            this.$selectedProperties.selectize()[0].selectize.addItem(i);

            i++;
        }

        this.$wrapper.find('.remove').attr('data-bypass', true);


        $('.item').click((e) => {
            debugger;
            let $element = $(e.target);
            // we don't want to trigger this when the exit button is clicked
            if($element.closest('a').length){
                return;
            }

            let joinPath = JSON.parse($element.attr('data-join-path'));

            this.globalEventDispatcher.publish(Settings.Events.EDIT_FILTER_BUTTON_CLICKED, joinPath);

        }) ;
    }

    getFilterTextFromCustomFilter(customFilter) {

        let text = '',
            value = '',
            values = '',
            label = customFilter.label;

        switch(customFilter['fieldType']) {
            case 'custom_object_field':
                // do nothing
                break;
            case 'date_picker_field':
                switch(customFilter['operator']) {
                    case 'EQ':

                        value = customFilter.value.trim() === '' ? '""' : `"${customFilter.value.trim()}"`;
                        text = `${label} is equal to ${value}`;

                        break;
                    case 'NEQ':

                        value = customFilter.value.trim() === '' ? '""' : `"${customFilter.value.trim()}"`;
                        text = `${label} is not equal to ${value}`;

                        break;
                    case 'LT':

                        value = customFilter.value.trim() === '' ? '""' : `"${customFilter.value.trim()}"`;
                        text = `${label} is before ${value}`;

                        break;
                    case 'GT':

                        value = customFilter.value.trim() === '' ? '""' : `"${customFilter.value.trim()}"`;
                        text = `${label} is after ${value}`;

                        break;
                    case 'BETWEEN':

                        let lowValue = customFilter.low_value.trim() === '' ? '""' : `"${customFilter.low_value.trim()}"`;
                        let highValue = customFilter.high_value.trim() === '' ? '""' : `"${customFilter.high_value.trim()}"`;
                        text = `${label} is between ${lowValue} and ${highValue}`;

                        break;
                    case 'HAS_PROPERTY':

                        text = `${label} is known`;

                        break;
                    case 'NOT_HAS_PROPERTY':

                        text = `${label} is unknown`;

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

                        text = `${label} is any of ${value}`;

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

                        text = `${label} is none of ${value}`;

                        break;
                    case 'HAS_PROPERTY':

                        text = `${label} is known`;

                        break;
                    case 'NOT_HAS_PROPERTY':

                        text = `${label} is unknown`;

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

                        text = `${label} is any of ${values}`;

                        break;
                    case 'NOT_IN':

                        values = customFilter.value.split(",");
                        values = values.join(" or ");

                        text = `${label} is none of ${values}`;

                        break;
                    case 'HAS_PROPERTY':

                        text = `${label} is known`;

                        break;
                    case 'NOT_HAS_PROPERTY':

                        text = `${label} is unknown`;

                        break;
                }
                break;
            case 'single_line_text_field':
            case 'multi_line_text_field':
            case 'number_field':
                switch(customFilter['operator']) {
                    case 'EQ':

                        debugger;
                        value = customFilter.value.trim() === '' ? '""' : `"${customFilter.value.trim()}"`;
                        text = `${customFilter.label} contains exactly ${value}`;

                        break;
                    case 'NEQ':

                        value = customFilter.value.trim() === '' ? '""' : `"${customFilter.value.trim()}"`;
                        text = `${customFilter.label} doesn't contain exactly ${value}`;

                        break;
                    case 'LT':

                        value = customFilter.value.trim() === '' ? '""' : `"${customFilter.value.trim()}"`;
                        text = `${customFilter.label} is less than ${value}`;

                        break;
                    case 'GT':

                        value = customFilter.value.trim() === '' ? '""' : `"${customFilter.value.trim()}"`;
                        text = `${customFilter.label} is greater than ${value}`;

                        break;
                    case 'BETWEEN':

                        let lowValue = customFilter.low_value.trim() === '' ? '""' : `"${customFilter.low_value.trim()}"`;
                        let highValue = customFilter.high_value.trim() === '' ? '""' : `"${customFilter.high_value.trim()}"`;

                        value = customFilter.value.trim() === '' ? '""' : `"${customFilter.value.trim()}"`;
                        text = `${customFilter.label} is between ${lowValue} and ${highValue}`;

                        break;
                    case 'HAS_PROPERTY':

                        text = `${customFilter.label} is known`;

                        break;
                    case 'NOT_HAS_PROPERTY':

                        text = `${customFilter.label} is unknown`;

                        break;
                }
                break;
        }

        return text;

    }

    render() {

        this.$wrapper.html(UserFilterNavigation.markup(this));
    }

    handleAddFilterButtonClicked() {
        this.globalEventDispatcher.publish(Settings.Events.ADD_FILTER_BUTTON_CLICKED);
    }

    static markup() {
        return `
    <ul class="nav nav-pills flex-column">
      <li class="nav-item">
        <a class="nav-link active js-all-records-button" href="#">All Users</a>
      </li>
      <li class="nav-item">
        <a class="nav-link disabled" href="javascript:void(0)">All Users</a>
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

export default UserFilterNavigation;