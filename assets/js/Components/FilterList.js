'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import EditSingleLineTextFieldFilter from "./EditSingleLineTextFieldFilter";

class FilterList {

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
            FilterList._selectors.backToHomeButton,
            this.handleBackButtonClicked.bind(this)
        );

        this.$wrapper.on(
            'click',
            FilterList._selectors.propertyListItem,
            this.handlePropertyListItemClicked.bind(this)
        );

        this.$wrapper.on(
            'keyup',
            FilterList._selectors.search,
            this.handleKeyupEvent.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.FILTER_FORM_BACK_BUTTON_CLICKED,
            this.filterFormBackButtonClickedHandler.bind(this)
        );

        this.render();

        this.loadPropertiesForFilter().then((data) => {
            debugger;
            this.propertyGroups = data.data.property_groups;
            this.renderProperties(this.propertyGroups);
        }).catch(() => {
            debugger;
        });
    }

    filterFormBackButtonClickedHandler() {
        debugger;
        this.$wrapper.find('.js-filter-list').removeClass('d-none');
        this.$wrapper.find('.js-search-container').removeClass('d-none');
        this.$wrapper.find('.js-back-button').removeClass('d-none');
        this.$wrapper.find(FilterList._selectors.propertyForm).addClass('d-none');
    }

    /**
     * Because this component can keep getting run each time a filter is added
     * you need to remove the handlers otherwise they will keep stacking up
     */
    unbindEvents() {
        this.$wrapper.off('click', FilterList._selectors.propertyListItem);
        this.$wrapper.off('click', FilterList._selectors.backToHomeButton);
        this.$wrapper.off('keyup', FilterList._selectors.search);
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            backToHomeButton: '.js-back-to-home-button',
            backToListButton: '.js-back-to-list-button',
            addFilterButton: '.js-add-filter-button',
            propertyList: '.js-property-list',
            propertyForm: '.js-property-form',
            editPropertyForm: '.js-edit-property-form',
            searchContainer: '.js-search-container',
            propertyListItem: '.js-property-list-item',
            search: '.js-search'
        }
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

    render() {
        debugger;
        this.$wrapper.html(FilterList.markup(this));
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
        const html = listTemplate(propertyGroup);
        const $list = $($.parseHTML(html));
        this.$wrapper.find('.js-filter-list').append($list);

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

    handleBackButtonClicked() {
        this.globalEventDispatcher.publish(Settings.Events.FILTER_BACK_TO_HOME_BUTTON_CLICKED);
    }

    handlePropertyListItemClicked(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        const $listItem = $(e.currentTarget);
        let propertyGroupId = $listItem.closest('.js-list').attr('data-property-group');
        let propertyId = $listItem.attr('data-property-id');


        let propertyGroup = this.propertyGroups.filter(propertyGroup => {
            return parseInt(propertyGroup.id) === parseInt(propertyGroupId);
        });

        let properties = propertyGroup[0].properties;

        let property = properties.filter(property => {
            return parseInt(property.id) === parseInt(propertyId);
        });

        this.globalEventDispatcher.publish(Settings.Events.FILTER_PROPERTY_LIST_ITEM_CLICKED, property[0]);
    }

    renderFilterForm(property) {

        this.$wrapper.find('.js-property-list').addClass('d-none');
        this.$wrapper.find('.js-search-container').addClass('d-none');
        this.$wrapper.find(FilterList._selectors.propertyForm).removeClass('d-none');

        switch (property.fieldType) {
            case 'single_line_text_field':
                new SingleLineTextFieldFilterForm(this.$wrapper.find(FilterList._selectors.propertyForm), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property);
                break;

        }
    }

    static markup() {

        debugger;
        return `
        <button type="button" class="btn btn-link js-back-to-home-button"><i class="fa fa-chevron-left"></i> Back</button>
        <div class="input-group c-search-control js-search-container">
          <input class="form-control c-search-control__input js-search" type="search" placeholder="Search...">
          <span class="c-search-control__foreground"><i class="fa fa-search"></i></span>
        </div>
        <div class="js-filter-list" style="height: 200px; overflow-y: auto"></div>
        `;
    }
}

const listTemplate = ({id, name}) => `
    <div id="list-${id}" class="js-list" data-property-group="${id}">
      <p>${name}</p>
      <ul class="list"></ul>
    </div>
    
`;

export default FilterList;