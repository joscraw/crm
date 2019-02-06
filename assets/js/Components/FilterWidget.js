'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import EditSingleLineTextFieldFilter from "./EditSingleLineTextFieldFilter";
import FilterList from "./FilterList";
import FilterNavigation from "./FilterNavigation";

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

/*        this.globalEventDispatcher.subscribe(
            Settings.Events.PROPERTY_SEARCH_KEY_UP,
            this.applySearch.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.FILTER_LIST_BACK_BUTTON_CLICKED,
            this.handleFilterListBackButtonClicked.bind(this)
        );*/

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
            selectizedPropertyContainer: '.js-selectized-property-container',
            filterNavigation: '.js-filter-navigation'
        }
    }

    filterFormBackToListButtonClickedHandler() {
        this.$wrapper.find(FilterWidget._selectors.propertyList).removeClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.propertyForm).addClass('d-none');
    }

    handleFilterListBackButtonClicked() {
        this.$wrapper.find(FilterWidget._selectors.propertyList).addClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.filterNavigation).removeClass('d-none');
    }

    applyCustomFilterButtonPressedHandler(customFilter) {

        debugger;
        this.customFilters = $.grep(this.customFilters, function(cf){
            return cf.id !== customFilter.id;
        });

        this.customFilters.push(customFilter);

        this.globalEventDispatcher.publish(Settings.Events.CUSTOM_FILTER_ADDED, this.customFilters);

        this.activatePlugins();

        this.$wrapper.find(FilterWidget._selectors.propertyList).addClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.filterNavigation).removeClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.propertyForm).addClass('d-none');


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
        this.$wrapper.html(FilterWidget.markup(this));
        new FilterNavigation(this.$wrapper.find('.js-filter-navigation'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
    }

    renderProperties(propertyGroups) {

        debugger;
        return new Promise((resolve, reject) => {

            for(let i = 0; i < propertyGroups.length; i++) {
                let propertyGroup = propertyGroups[i];
                let properties = propertyGroup.properties;
                this._addList(propertyGroup, properties);

            }
            resolve();
        });
    }

    _addList(propertyGroup, properties) {
        debugger;
        let $propertyList = this.$wrapper.find('.js-property-list');
        const html = listTemplate(propertyGroup);
        const $list = $($.parseHTML(html));
        $propertyList.append($list);

        // List.js is used to render the list on the left and to allow searching of said list
        let options = {
            valueNames: [ 'label' ],
            // Since there are no elements in the list, this will be used as template.
            item: `<li class="js-property-list-item"><p class="label"></p></li>`
        };

        this.lists.push(new List(`list-${propertyGroup.id}`, options, properties));

        $( `#list-${propertyGroup.id} li` ).each((index, element) => {
            $(element).attr('data-property-id', properties[index].id);
        });

    }

    loadPropertiesForFilter() {
        debugger;
        return new Promise((resolve, reject) => {
            const url = Routing.generate('properties_for_filter', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});

            $.ajax({
                url: url
            }).then(data => {
                resolve(data);
            }).catch(jqXHR => {
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }

    handleAddFilterButtonClicked() {
        debugger;
       /* this.$wrapper.find(FilterWidget._selectors.addFilterButton).addClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.propertyList).removeClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.selectizedPropertyContainer).addClass('d-none');*/



/*        this.$wrapper.find(FilterWidget._selectors.backToHomeButton).removeClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.propertyList).removeClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.searchContainer).removeClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.backToListButton).addClass('d-none');*/



        this.$wrapper.find(FilterWidget._selectors.filterNavigation).addClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.propertyList).removeClass('d-none');
        new FilterList(this.$wrapper.find('.js-property-list'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
    }

    handleBackToHomeButtonClicked() {
        this.$wrapper.find(FilterWidget._selectors.filterNavigation).removeClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.propertyList).addClass('d-none');
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

    handlePropertyListItemClicked(property) {

        debugger;

        this.$wrapper.find(FilterWidget._selectors.propertyList).addClass('d-none');
        this.$wrapper.find(FilterWidget._selectors.propertyForm).removeClass('d-none');

        this.renderFilterForm(property);
    }

    renderFilterForm(property) {
        debugger;

       /* this.$wrapper.find('.js-property-list').addClass('d-none');
        this.$wrapper.find('.js-search-container').addClass('d-none');*/

        switch (property.fieldType) {
            case 'single_line_text_field':
                new SingleLineTextFieldFilterForm($(FilterWidget._selectors.propertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property);
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

const listTemplate = ({id, name}) => `
    <div id="list-${id}" class="js-list" data-property-group="${id}">
      <p>${name}</p>
      <ul class="list"></ul>
    </div>
    
`;

export default FilterWidget;