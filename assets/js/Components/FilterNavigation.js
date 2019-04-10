'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import FilterList from "./FilterList";
import ArrayHelper from "../ArrayHelper";
import FilterHelper from "../FilterHelper";
import StringHelper from "../StringHelper";
import SaveFilterFormModal from "./SaveFilterFormModal";
import SavedFiltersModal from "./SavedFiltersModal";

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

        this.$wrapper.on(
            'click',
            FilterNavigation._selectors.resetFiltersButton,
            this.handleResetFiltersButtonClicked.bind(this)
        );

        this.$wrapper.on(
            'click',
            FilterNavigation._selectors.saveFiltersButton,
            this.handleSaveFiltersButtonClicked.bind(this)
        );

        this.$wrapper.on(
            'click',
            FilterNavigation._selectors.allSavedFiltersButton,
            this.handleAllSavedFiltersButtonClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.FILTERS_UPDATED,
            this.activatePlugins.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.FILTERS_UPDATED,
            this.showOrHideSaveFilterNavItem.bind(this)
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
            allRecordsButton: '.js-all-records-button',
            saveFilterNavItem: '.js-save-filter-nav-item',
            saveFiltersButton: '.js-save-filters-button',
            resetFiltersButton: '.js-reset-filters-button',
            allSavedFiltersButton: '.js-all-saved-filters-button'
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
        this.$wrapper.off('click', FilterNavigation._selectors.addFilterButton);
        this.$wrapper.off('click', FilterNavigation._selectors.allRecordsButton);
        this.$wrapper.off('click', FilterNavigation._selectors.resetFiltersButton);
        this.$wrapper.off('click', FilterNavigation._selectors.saveFiltersButton);
    }

    handleAllRecordsButtonClicked(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        this.globalEventDispatcher.publish(Settings.Events.FILTER_ALL_RECORDS_BUTTON_PRESSED);

    }

    handleResetFiltersButtonClicked(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        this.globalEventDispatcher.publish(Settings.Events.RESET_FILTERS_BUTTON_PRESSED);
    }

    handleAllSavedFiltersButtonClicked(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        new SavedFiltersModal(this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);

    }

    handleSaveFiltersButtonClicked(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        new SaveFilterFormModal(this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, this.customFilters);
    }

    showOrHideSaveFilterNavItem(customFilters) {

        let filters = FilterHelper.getNonReportFiltersFromCustomFiltersObject(customFilters);

        if(_.isEmpty(filters)) {

            if(!$(FilterNavigation._selectors.saveFilterNavItem).hasClass('d-none')) {

                $(FilterNavigation._selectors.saveFilterNavItem).addClass('d-none');

            }

        } else {

            if($(FilterNavigation._selectors.saveFilterNavItem).hasClass('d-none')) {

                $(FilterNavigation._selectors.saveFilterNavItem).removeClass('d-none');

            }
        }
    }

    activatePlugins(customFilters) {

        this.customFilters = customFilters;

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

            let text = FilterHelper.getFilterTextFromCustomFilter(customFilter);

            debugger;

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
        <button type="button" class="btn btn-link js-all-saved-filters-button">All saved filters <i class="fa fa-angle-right"></i></button>
      </li>
      <li class="nav-item js-selectized-property-container c-filter-widget__selected-filters">
        <div class="js-selectized-property-container">
            <input type="text" id="js-selected-properties">
        </div>
      </li>
      <li class="nav-item">
        <button type="button" class="btn btn-link js-add-filter-button"><i class="fa fa-plus"></i> Add Filter</button>
      </li>
      
      <li class="nav-item js-save-filter-nav-item d-none">
        <hr>
        <button type="button" class="btn btn-sm btn-primary js-save-filters-button">Save</button> <button type="button" class="btn btn-sm btn-light js-reset-filters-button">Reset</button>
      </li>
    </ul>
    `;
    }
}

export default FilterNavigation;