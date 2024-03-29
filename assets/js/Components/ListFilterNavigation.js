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
import FilterHelper from "../FilterHelper";

class ListFilterNavigation {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, data = {}, listType, listId) {

        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.listType = listType;
        this.listId = listId;

        /**
         * This data object is responsible for storing all the properties and filters that will get sent to the server
         * @type {{}}
         */
        this.data = data;

        this.unbindEvents();

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
            Settings.Events.LIST_FILTER_ITEM_ADDED,
            this.listFilterItemAddedHandler.bind(this)
        ));

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
            Settings.Events.LIST_FILTER_ITEM_REMOVED,
            this.listFilterItemRemovedHandler.bind(this)
        ));

        this.$wrapper.on(
            'click',
            ListFilterNavigation._selectors.addFilterButton,
            this.handleAddFilterButtonPressed.bind(this)
        );

        this.$wrapper.on(
            'click',
            ListFilterNavigation._selectors.addOrFilterButton,
            this.handleAddOrFilterButtonPressed.bind(this)
        );

        this.$wrapper.on(
            'click',
            ListFilterNavigation._selectors.removeFilterIcon,
            this.handleRemoveFilterIconPressed.bind(this)
        );

        this.$wrapper.on(
            'click',
            ListFilterNavigation._selectors.filter,
            this.handleFilterPressed.bind(this)
        );


        this.render(data);
    }

    static get _selectors() {
        return {

            addFilterButton: '.js-add-filter-button',
            addOrFilterButton: '.js-add-or-filter-button',
            listSelectedCustomFilters: '.js-list-selected-custom-filters',
            filterContainer: '.js-filter-container',
            removeFilterIcon: '.js-remove-filter-icon',
            filter: '.js-filter'
        }
    }

    unbindEvents() {

        this.$wrapper.off('click', ListFilterNavigation._selectors.addFilterButton);

        this.$wrapper.off('click', ListFilterNavigation._selectors.addOrFilterButton);

        this.$wrapper.off('click', ListFilterNavigation._selectors.removeFilterIcon);

        this.$wrapper.off('click', ListFilterNavigation._selectors.filter);

    }

    handleFilterPressed(e) {

        debugger;

        const $filter = $(e.currentTarget);

        let joinPath = JSON.parse($filter.attr('data-join-path'));

        this.globalEventDispatcher.publish(Settings.Events.LIST_EDIT_FILTER_BUTTON_CLICKED, joinPath);

    }

    handleRemoveFilterIconPressed(e) {
        debugger;

        e.stopPropagation();

        const $removeIcon = $(e.currentTarget);

        let joinPath = JSON.parse($removeIcon.attr('data-join-path'));

        this.globalEventDispatcher.publish(Settings.Events.LIST_REMOVE_FILTER_BUTTON_PRESSED, joinPath);
    }

    handleAddFilterButtonPressed() {

        debugger;

        this.globalEventDispatcher.publish(Settings.Events.LIST_ADD_FILTER_BUTTON_PRESSED);
    }

    handleAddOrFilterButtonPressed(e) {

        debugger;

        const $card = $(e.currentTarget);
        let orPath = JSON.parse($card.attr('data-or-path'));

        this.globalEventDispatcher.publish(Settings.Events.LIST_ADD_OR_FILTER_BUTTON_PRESSED, orPath);

        debugger;

    }

    listFilterItemAddedHandler(data) {

        debugger;

        this.renderCustomFilters(data);

        this.updateAddFilterButtonText(data);

    }

    listFilterItemRemovedHandler(data) {

        debugger;
        this.renderCustomFilters(data);

        this.updateAddFilterButtonText(data);

    }

    updateAddFilterButtonText(data) {

        let text = "Add Filter";

        if(this.getCustomFilterCount(data) !== 0) {

            text = 'Add "OR" Filter';
        }

        this.$wrapper.find(ListFilterNavigation._selectors.addFilterButton).html('<i class="fa fa-plus"></i> ' + text);

    }

    renderCustomFilters(data) {

        debugger;
        let customFilters = {};
        function search(data) {

            for(let key in data) {

                if(isNaN(key) && key !== 'filters') {

                    search(data[key]);

                } else if(key === 'filters'){

                    for(let uID in data[key]) {

                        // only add the custom filter to the array if it is not an "OR" condition
                        if(_.size(_.get(data, `${key}.${uID}.referencedFilterPath`, [])) === 0) {

                            _.set(customFilters, uID, data[key][uID]);

                        }

                    }
                }
            }
        }

        debugger;
        search(data);

        this.$wrapper.find(ListFilterNavigation._selectors.listSelectedCustomFilters).html("");

        let i = 1;
        for(let uID in customFilters) {
            debugger;
            let customFilter = _.get(customFilters, uID, false);

            let text = "";

            let orPath = customFilter.joins.concat(['filters', uID]);

            debugger;

            text = FilterHelper.getFilterTextFromCustomFilter(customFilter);

            const html = filterContainerTemplate(JSON.stringify(orPath));
            const $filterContainerTemplate = $($.parseHTML(html));
            const $filters = $filterContainerTemplate.find('.js-filters');

            const filterHtml = filterTemplate(text, JSON.stringify(customFilter.joins.concat(['filters', uID])));
            const $filterTemplate = $($.parseHTML(filterHtml));

            $filters.append($filterTemplate);

            debugger;

            let orFilters = customFilter.orFilters;
            for (let key in orFilters) {

                debugger;
                let orFilter = _.get(orFilters, key);

                debugger;

                let filterPath = orFilter.join('.');

                if(!_.has(data, filterPath)) {
                    continue;
                }

                let conditionalHtml = conditionalTemplate("And");
                let $conditionalTemplate = $($.parseHTML(conditionalHtml));

                $filters.append($conditionalTemplate);

                let customFilter = _.get(data, filterPath);

                text = FilterHelper.getFilterTextFromCustomFilter(customFilter);

                const filterHtml = filterTemplate(text, JSON.stringify(customFilter.joins.concat(['filters', key])));
                const $filterTemplate = $($.parseHTML(filterHtml));

                $filters.append($filterTemplate);

            }

            this.$wrapper.find(ListFilterNavigation._selectors.listSelectedCustomFilters).append($filterContainerTemplate);


            if(Object.keys(customFilters).length !== i) {

                let conditionalHtml = conditionalTemplate("Or");
                let $conditionalTemplate = $($.parseHTML(conditionalHtml));

                this.$wrapper.find(ListFilterNavigation._selectors.listSelectedCustomFilters).append($conditionalTemplate);

            }

            i++;
        }

    }

    getCustomFilterCount(data) {

        debugger;
        let customFilters = {};
        function search(data) {

            for(let key in data) {

                if(isNaN(key) && key !== 'filters') {

                    search(data[key]);

                } else if(key === 'filters'){

                    for(let uID in data[key]) {

                        // only add the custom filter to the array if it is not an "OR" condition
                        if(_.size(_.get(data, `${key}.${uID}.referencedFilterPath`, [])) === 0) {

                            _.set(customFilters, uID, data[key][uID]);

                        }

                    }
                }
            }
        }

        debugger;
        search(data);

        return Object.entries(customFilters).length;
    }

    render(data) {

        this.$wrapper.html(ListFilterNavigation.markup());

        this.renderCustomFilters(data);

        if(this.listType === 'STATIC_LIST' && this.listId) {

            $(ListFilterNavigation._selectors.addFilterButton).remove();

            $(ListFilterNavigation._selectors.addOrFilterButton).remove();

            $(ListFilterNavigation._selectors.removeFilterIcon).remove();

            this.$wrapper.off('click', ListFilterNavigation._selectors.filter);

        }

    }

    static markup() {

        return `
            <ul class="nav nav-pills flex-column">
              <li class="nav-item">
                <a class="nav-link active js-all-records-button" href="javascript:void(0)">All Filters</a>
              </li>
              <li class="nav-item">
                <div class="js-list-selected-custom-filters c-report-widget__selected-filters"></div>
              </li>
              <li class="nav-item">
                <button type="button" class="btn btn-link js-add-filter-button"><i class="fa fa-plus"></i> Add Filter</button>
              </li>
             </ul>
    `;
    }
}

const filterContainerTemplate = (orPath) => `
    <div class="card">
        <div class="card-body js-filter-container">
        
        <div class="js-filters"></div>
        
        <button type="button" class="btn btn-link js-add-or-filter-button" data-or-path=${orPath}><i class="fa fa-plus"></i> Add Filter</button>
        </div>
    </div>
`;

const filterTemplate = (text, joinPath) => `
    <div class="card js-filter" data-join-path=${joinPath}>
        <div class="card-body">
        <h5 class="card-title">${text}</h5>     
        <span><i class="fa fa-times js-remove-filter-icon c-report-widget__filter-remove-icon" data-join-path=${joinPath} aria-hidden="true"></i></span>
        </div>
    </div>
`;

const conditionalTemplate = (text) => `
    <p style="margin-top: 10px; margin-bottom: 10px">${text}</p>
`;


export default ListFilterNavigation;