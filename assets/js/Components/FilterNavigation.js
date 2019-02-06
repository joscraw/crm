'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import EditSingleLineTextFieldFilter from "./EditSingleLineTextFieldFilter";
import FilterList from "./FilterList";

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

 /*       this.globalEventDispatcher.subscribe(
            Settings.Events.APPLY_CUSTOM_FILTER_BUTTON_PRESSED,
            this.applyCustomFilterButtonPressedHandler.bind(this)
        );
*/

        this.globalEventDispatcher.subscribe(
            Settings.Events.PROPERTY_SEARCH_KEY_UP,
            this.applySearch.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.FILTER_LIST_BACK_BUTTON_CLICKED,
            this.handleFilterListBackButtonClicked.bind(this)
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
            propertyForm: '.js-property-form',
            editPropertyForm: '.js-edit-property-form',
            searchContainer: '.js-search-container',
            selectizedPropertyContainer: '.js-selectized-property-container'
        }
    }

    /**
     * Because this component can keep getting run each time a filter is added
     * you need to remove the handlers otherwise they will keep stacking up
     */
    unbindEvents() {
        this.$wrapper.off('click', FilterNavigation._selectors.addFilterButton);
    }

    handleFilterListBackButtonClicked() {
        this.$wrapper.find(FilterWidget._selectors.propertyList).addClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.addFilterButton).removeClass('d-none');
    }

    handleKeyupEvent(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        const searchValue = $(e.target).val();
        const searchObject = {
            searchValue: searchValue
        };

        this.applySearch(searchObject);
    }

    /**
     * @param args
     */
    applySearch(args = {}) {

        debugger;
        if(typeof args.searchValue !== 'undefined') {
            this.searchValue = args.searchValue;
        }

        for(let i = 0; i < this.lists.length; i++) {
            this.lists[i].search(this.searchValue);
        }

        this.$wrapper.find('.js-list').each((index, element) => {
            if($(element).find('.list').is(':empty') && this.searchValue !== '') {
                $(element).addClass('d-none');

            } else {
                if($(element).hasClass('d-none')) {
                    $(element).removeClass('d-none');
                }
            }
        });
    }

    activatePlugins() {
        debugger;

        this.$selectedProperties = $('#js-selected-properties').selectize({
            plugins: ['remove_button'],
            sortField: 'text'
        });

        this.$selectedProperties.selectize()[0].selectize.on('item_remove', (key) => {
            debugger;
            this.customFilters.splice(key, 1);
            this.globalEventDispatcher.publish(Settings.Events.CUSTOM_FILTER_REMOVED, this.customFilters);

        });

        this.$selectedProperties.selectize()[0].selectize.clear();
        this.$selectedProperties.selectize()[0].selectize.clearOptions();

        for(let i = 0; i < this.customFilters.length; i++) {
            debugger;
            let customFilter = this.customFilters[i];
            switch(customFilter['operator']) {
                case 'EQ':
                    let value = customFilter.value.trim() === '' ? '""' : `"${customFilter.value.trim()}"`;
                    this.$selectedProperties.selectize()[0].selectize.addOption({value:i, text: `${customFilter.label} contains exactly ${value}`});
                    this.$selectedProperties.selectize()[0].selectize.addItem(i);
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

            let index = $element.index();
            let customFilter = this.customFilters[index];

            this.$wrapper.find(FilterWidget._selectors.addFilterButton).addClass('d-none');
            this.$wrapper.find(FilterWidget._selectors.searchContainer).addClass('d-none');
            this.$wrapper.find(FilterWidget._selectors.backToListButton).removeClass('d-none');

            switch (customFilter.fieldType) {
                case 'single_line_text_field':
                    new EditSingleLineTextFieldFilter(this.$wrapper.find('.js-edit-property-form'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, customFilter);
                    break;
            }

            debugger;
        }) ;

        /*$('.remove').click(function(e) {
            debugger;
            e.stopPropagation();
        });*/
    }

    render() {
        debugger;
        this.$wrapper.html(FilterNavigation.markup(this));
        /*new FilterList(this.$wrapper.find('.js-property-list'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);*/
    }

    handleAddFilterButtonClicked() {

        this.globalEventDispatcher.publish(Settings.Events.ADD_FILTER_BUTTON_CLICKED);
        debugger;
/*        this.$wrapper.find(FilterWidget._selectors.addFilterButton).addClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.propertyList).removeClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.selectizedPropertyContainer).addClass('d-none');

        new FilterList(this.$wrapper.find('.js-property-list'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);*/
    }

    handleBackButtonClicked() {
        debugger;
        this.$wrapper.find(FilterWidget._selectors.addFilterButton).removeClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.backToHomeButton).addClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.backToListButton).addClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.propertyList).addClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.propertyForm).addClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.searchContainer).addClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.editPropertyForm).addClass('d-none');
    }

    handleBackToListButtonClicked() {
        this.$wrapper.find(FilterWidget._selectors.backToListButton).addClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.backToHomeButton).removeClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.propertyList).removeClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.propertyForm).addClass('d-none');
    }

    handlePropertyListItemClicked(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        this.$wrapper.find(FilterWidget._selectors.backToListButton).removeClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.backToHomeButton).addClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.propertyList).removeClass('d-none');

        debugger;
        const $listItem = $(e.currentTarget);
        let propertyGroupId = $listItem.closest('.js-list').attr('data-property-group');
        let propertyId = $listItem.attr('data-property-id');


        let propertyGroup = this.propertyGroups.filter(propertyGroup => {
           return parseInt(propertyGroup.id) === parseInt(propertyGroupId);
        });

        debugger;
        let properties = propertyGroup[0].properties;

        debugger;
        let property = properties.filter(property => {
            return parseInt(property.id) === parseInt(propertyId);
        });

        this.renderEditFilterForm(property[0]);

        this.$wrapper.find('.js-property-form').removeClass('d-none');
        this.$wrapper.find('.js-property-list').addClass('d-none');
    }

    renderEditFilterForm(property) {
        debugger;

        this.$wrapper.find('.js-property-list').addClass('d-none');
        this.$wrapper.find('.js-search-container').addClass('d-none');

        switch (property.fieldType) {
            case 'single_line_text_field':
                new SingleLineTextFieldFilterForm(this.$wrapper.find('.js-property-form'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property);
                break;

        }
    }

    static markup() {
        return `
    
        <div class="js-selectized-property-container">
            <input type="text" id="js-selected-properties">
        </div>
        <button type="button" class="btn btn-link js-add-filter-button"><i class="fa fa-plus"></i> Add Filter</button>

    `;
    }
}

export default FilterNavigation;