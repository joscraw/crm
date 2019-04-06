'use strict';

import ArrayHelper from "./ArrayHelper";

class FilterHelper {

    /**
     * reads a customFilter object and returns what the filter text should be.
     * This is used for Reports, User Filters, Record Filters, and more
     *
     * @param customFilter
     * @return {string}
     */
    static getFilterTextFromCustomFilter(customFilter) {

        let text = '',
            value = '',
            values = '',
            label;

        debugger;
        // Here we are creating the label path. We don't want to actually
        // modify the joins so just create a copy
        /*let customFilterCopy = JSON.parse(JSON.stringify(customFilter));*/

        let customFilterCopy = _.cloneDeep(customFilter);

        customFilterCopy.joins.shift();
        customFilterCopy.joins.push(customFilter.label);
        label = customFilterCopy.joins.join(" - ");

        console.log(customFilter.joins);

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
                        text = `${label} contains exactly ${value}`;

                        break;
                    case 'NEQ':

                        value = customFilter.value.trim() === '' ? '""' : `"${customFilter.value.trim()}"`;
                        text = `${label} doesn't contain exactly ${value}`;

                        break;
                    case 'LT':

                        value = customFilter.value.trim() === '' ? '""' : `"${customFilter.value.trim()}"`;
                        text = `${label} is less than ${value}`;

                        break;
                    case 'GT':

                        value = customFilter.value.trim() === '' ? '""' : `"${customFilter.value.trim()}"`;
                        text = `${label} is greater than ${value}`;

                        break;
                    case 'BETWEEN':

                        let lowValue = customFilter.low_value.trim() === '' ? '""' : `"${customFilter.low_value.trim()}"`;
                        let highValue = customFilter.high_value.trim() === '' ? '""' : `"${customFilter.high_value.trim()}"`;

                        value = customFilter.value.trim() === '' ? '""' : `"${customFilter.value.trim()}"`;
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
        }

        return text;

    }

    /**
     * Report filters are slightly different and have "OR Reference filters" and
     * therefore require a slightly different extraction method
     *
     * @param customFilters
     */
    static getNonReportFiltersFromCustomFiltersObject(customFilters) {

        return (function search(data, filters = {}) {

            for(let key in data) {

                if(isNaN(key) && key !== 'filters') {

                    search(data[key], filters);

                } else if(key === 'filters'){

                    for(let uID in data[key]) {

                        _.set(filters, uID, data[key][uID]);

                    }
                }
            }

            return filters;
        })(customFilters);

    }

}

export default FilterHelper;