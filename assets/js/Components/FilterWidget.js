'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilter from "./SingleLineTextFieldFilter";

class FilterWidget {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName) {
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.propertyGroups = [];
        this.lists = [];
        this.customFilters = [];

        this.$wrapper.on(
            'click',
            '.js-add-filter-button',
            this.handleAddFilterButtonClicked.bind(this)
        );

        this.$wrapper.on(
            'click',
            '.js-back-button',
            this.handleBackButtonClicked.bind(this)
        );

        this.$wrapper.on(
            'click',
            '.js-property-list-item',
            this.handlePropertyListItemClicked.bind(this)
        );

        this.$wrapper.on(
            'keyup',
            '.js-search',
            this.handleKeyupEvent.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.CUSTOM_FILTER_ADDED,
            this.customFilterAddedHandler.bind(this)
        );


        this.globalEventDispatcher.subscribe(
            Settings.Events.PROPERTY_SEARCH_KEY_UP,
            this.applySearch.bind(this)
        );

        this.render();

        this.loadPropertiesForFilter().then((data) => {
            this.propertyGroups = data.data.property_groups;
            this.renderProperties(this.propertyGroups);
        }).catch(() => {
            debugger;
        });
    }

    customFilterAddedHandler(customFilter) {
        this.customFilters.push(customFilter);
    }

    handleKeyupEvent(e) {

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
        this.$wrapper.html(FilterWidget.markup(this));
    }

    renderProperties(propertyGroups) {

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
        this.$wrapper.find('.js-add-filter-button').addClass('d-none');
        this.$wrapper.find('.js-back-button').removeClass('d-none');
        this.$wrapper.find('.js-property-list').removeClass('d-none');
        this.$wrapper.find('.js-search-container').removeClass('d-none');
    }

    handleBackButtonClicked() {
        this.$wrapper.find('.js-add-filter-button').removeClass('d-none');
        this.$wrapper.find('.js-back-button').addClass('d-none');
        this.$wrapper.find('.js-property-list').addClass('d-none');
        this.$wrapper.find('.js-property-form').addClass('d-none');
        this.$wrapper.find('.js-search-container').addClass('d-none');
    }

    handlePropertyListItemClicked(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

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

        debugger;

        this.renderFilterForm(property[0]);

        this.$wrapper.find('.js-property-form').removeClass('d-none');
        this.$wrapper.find('.js-property-list').addClass('d-none');
    }

    renderFilterForm(property) {
        debugger;

        this.$wrapper.find('.js-property-list').addClass('d-none');
        this.$wrapper.find('.js-search-container').addClass('d-none');

        switch (property.fieldType) {
            case 'single_line_text_field':
                new SingleLineTextFieldFilter(this.$wrapper.find('.js-property-form'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property);
                break;

        }

    }

    static markup() {

        return `
      <div class="js-filter-widget">
            <button type="button" class="btn btn-link js-back-button d-none"><i class="fa fa-chevron-left"></i> Back</button>
            <button type="button" class="btn btn-link js-add-filter-button"><i class="fa fa-plus"></i> Add Filter</button>
            <div class="input-group c-search-control js-search-container d-none">
              <input class="form-control c-search-control__input js-search" type="search" placeholder="Search...">
              <span class="c-search-control__foreground"><i class="fa fa-search"></i></span>
            </div>
            <div class="js-property-list d-none" style="height: 200px; overflow-y: auto"></div>
            <div class="js-property-form d-none"></div>
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