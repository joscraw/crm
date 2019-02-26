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
import ReportFilterList from "./ReportFilterList";
import ReportSelectedCustomFilters from "./ReportSelectedCustomFilters";

class ReportFilterNavigation {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;

        /**
         * This data object is responsible for storing all the properties and filters that will get sent to the server
         * @type {{}}
         */
        this.data = {};

        this.unbindEvents();

/*        this.unbindEvents();

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_FILTER_ITEM_CLICKED,
            this.handleReportFilterItemClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.FILTER_BACK_TO_LIST_BUTTON_CLICKED,
            this.handleFilterBackToListButtonClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_CUSTOM_OBJECT_FILTER_LIST_ITEM_CLICKED,
            this.handleReportCustomObjectFilterListItemClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_FILTER_ITEM_ADDED,
            this.reportFilterItemAddedHandler.bind(this)
        );

        this.$wrapper.on(
            'click',
            ReportFilters._selectors.addFilterButton,
            this.handleAddFilterButtonPressed.bind(this)
        );*/

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_FILTER_ITEM_ADDED,
            this.reportFilterItemAddedHandler.bind(this)
        );

        this.$wrapper.on(
            'click',
            ReportFilterNavigation._selectors.addFilterButton,
            this.handleAddFilterButtonPressed.bind(this)
        );

        this.$wrapper.on(
            'click',
            ReportFilterNavigation._selectors.addOrFilterButton,
            this.handleAddOrFilterButtonPressed.bind(this)
        );

        this.render();
    }

    static get _selectors() {
        return {

            addFilterButton: '.js-add-filter-button',
            addOrFilterButton: '.js-add-or-filter-button',
            reportSelectedCustomFilters: '.js-report-selected-custom-filters',
        }
    }

    unbindEvents() {

        this.$wrapper.off('click', ReportFilterNavigation._selectors.addFilterButton);

        this.$wrapper.off('click', ReportFilterNavigation._selectors.addOrFilterButton);

    }

    handleAddFilterButtonPressed() {

        debugger;

        this.globalEventDispatcher.publish(Settings.Events.REPORT_ADD_FILTER_BUTTON_PRESSED);
    }

    handleAddOrFilterButtonPressed(e) {

        debugger;

        const $card = $(e.currentTarget);
        let orPath = JSON.parse($card.attr('data-or-path'));

        this.globalEventDispatcher.publish(Settings.Events.REPORT_ADD_OR_FILTER_BUTTON_PRESSED, orPath);

        debugger;

    }

    reportFilterItemAddedHandler(data) {

        this.renderCustomFilters(data);

    }

    renderCustomFilters(data) {

        let customFilters = {};
        function search(data) {

            for(let key in data) {

                if(isNaN(key) && key !== 'filters') {

                    search(data[key]);

                } else if(key === 'filters'){

                    for(let uID in data[key]) {

                        // only add the custom filter to the array if it is not an "OR" condition
                        if(_.size(_.get(data, `${key}.${uID}.orPath`, [])) === 0) {

                            _.set(customFilters, uID, data[key][uID]);

                        }

                    }
                }
            }
        }

        debugger;
        search(data);

        this.$wrapper.find(ReportFilterNavigation._selectors.reportSelectedCustomFilters).html("");

        for(let uID in customFilters) {
            debugger;
            let customFilter = _.get(customFilters, uID, false);

            let value = "",
                values = "",
                label = "",
                joins = [],
                text = "";

            let orPath = customFilter.joins.concat(['filters', uID]);



            // get or conditions for filter


            debugger;

            /* let customFilterJoins = customFilter.customFilterJoins.map((value) => {
                 return value.label;
             });

             joins = Object.assign([], customFilterJoins);
             joins.push(customFilter.label);
             label = joins.join(" - ");*/


            debugger;

            text = this.getFilterTextFromCustomFilter(customFilter);


            debugger;
            const html = selectedCustomFilterTemplate(text, customFilter, JSON.stringify(orPath));
            const $selectedCustomFilterTemplate = $($.parseHTML(html));

            for(let orFilter of customFilter.orFilters) {

                debugger;

                let filterPath = orFilter.join('.');

                let customFilter = _.get(data, filterPath);

                text = this.getFilterTextFromCustomFilter(customFilter);

                debugger;

                const orFilterHtml = orFilterTemplate(text);
                const $orFilterTemplate = $($.parseHTML(orFilterHtml));

                $selectedCustomFilterTemplate.find('.js-or-filters').append($orFilterTemplate);


                debugger;

            }

            debugger;


            debugger;


            this.$wrapper.find(ReportFilterNavigation._selectors.reportSelectedCustomFilters).append($selectedCustomFilterTemplate);
        }

    }

    render() {

        this.$wrapper.html(ReportFilterNavigation.markup(this));

    }

    getFilterTextFromCustomFilter(customFilter) {

        let text = '',
            value = '',
            values = '';

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

    static markup() {

        return `
            <ul class="nav nav-pills flex-column">
              <li class="nav-item">
                <a class="nav-link active js-all-records-button" href="#">All Filters</a>
              </li>
              <li class="nav-item">
                <div class="js-report-selected-custom-filters"></div>
              </li>
              <li class="nav-item">
                <button type="button" class="btn btn-link js-add-filter-button"><i class="fa fa-plus"></i> Add Filter</button>
              </li>
             </ul>
    `;
    }
}

const selectedCustomFilterTemplate = (text, customFilter, orPath) => `
    <div class="card">
        <div class="card-body">
        <h5 class="card-title">${text}</h5>
        
        <div class="js-or-filters"></div>
        
        <button type="button" class="btn btn-link js-add-or-filter-button" data-or-path=${orPath}><i class="fa fa-plus"></i> Add "OR" Filter</button>
        </div>
    </div>
`;


const orFilterTemplate = (text) => `
    <div class="card">
        <div class="card-body">
        <h5 class="card-title">${text}</h5>
        </div>
    </div>
`;



export default ReportFilterNavigation;