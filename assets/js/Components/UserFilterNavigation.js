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
import FilterHelper from "../FilterHelper";

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

        let filters = FilterHelper.getNonReportFiltersFromCustomFiltersObject(customFilters);

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

            let joinPath = JSON.parse($item.closest('div').attr('data-join-path'));

            this.globalEventDispatcher.publish(Settings.Events.CUSTOM_FILTER_REMOVED, joinPath);

        });

        this.$selectedProperties.selectize()[0].selectize.clear();
        this.$selectedProperties.selectize()[0].selectize.clearOptions();


        let i = 0;
        for(let uID in filters) {

            debugger;
            let customFilter = _.get(filters, uID, false);

            let text = FilterHelper.getFilterTextFromCustomFilter(customFilter);

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