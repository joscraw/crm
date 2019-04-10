'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";

class UserFilterList {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, internalName = 'root', join = null, joins = [], customFilters = {}) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.properties = [];
        this.list = null;
        this.internalName = internalName;
        this.join = join;
        this.joins = joins;
        this.customFilters = customFilters;

        this.unbindEvents();

        this.$wrapper.on(
            'click',
            UserFilterList._selectors.backToHomeButton,
            this.handleBackButtonClicked.bind(this)
        );

        this.$wrapper.on(
            'click',
            UserFilterList._selectors.propertyListItem,
            this.handlePropertyListItemClicked.bind(this)
        );

        this.$wrapper.on(
            'keyup',
            UserFilterList._selectors.search,
            this.handleKeyupEvent.bind(this)
        );

        this.render();

        this.loadUserPropertiesForFilter().then((data) => {

            this.properties = data.data;
            this.renderProperties(this.properties).then(() => {
                this.highlightProperties(this.customFilters);
            });
        }).catch(() => {});
    }

    /**
     * Because this component can keep getting run each time a filter is added
     * you need to remove the handlers otherwise they will keep stacking up
     */
    unbindEvents() {
        this.$wrapper.off('click', UserFilterList._selectors.propertyListItem);
        this.$wrapper.off('click', UserFilterList._selectors.backToHomeButton);
        this.$wrapper.off('keyup', UserFilterList._selectors.search);
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            backToHomeButton: '.js-back-to-home-button',
            propertyListItem: '.js-property-list-item',
            search: '.js-search'
        }
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

    highlightProperties(data) {

        debugger;
        $(UserFilterList._selectors.propertyListItem).each((index, element) => {

            debugger;
            if($(element).hasClass('c-list__list-item--active')) {
                $(element).removeClass('c-list__list-item--active');
            }

            let propertyId = $(element).attr('data-property-id');
            let joins = JSON.parse($(element).attr('data-joins'));
            let propertyPath = joins.join('.');

            if(_.has(data, propertyPath)) {

                let properties = _.get(data, propertyPath);

                let propertyMatch = null;

                if(!_.has(properties, 'filters')) {
                    return true;
                }

                let filters =  _.get(properties, 'filters');

                for(let key in filters) {

                    let filter = filters[key];

                    if(parseInt(filter.id) === parseInt(propertyId)) {
                        propertyMatch = filter
                    }
                }

                if(propertyMatch) {

                    $(element).addClass('c-list__list-item--active');
                }
            }

        });
    }

    /**
     * @param args
     */
    applySearch(args = {}) {

        if(typeof args.searchValue !== 'undefined') {
            this.searchValue = args.searchValue;
        }

        this.list.search(this.searchValue);

    }

    render() {
        debugger;
        this.$wrapper.html(UserFilterList.markup(this));
    }

    renderProperties(properties) {

        return new Promise((resolve, reject) => {

            this._addList(properties);

            resolve();
        });
    }

    _addList(properties) {

        const html = listTemplate();
        const $list = $($.parseHTML(html));
        this.$wrapper.find('.js-filter-list').append($list);

        // List.js is used to render the list on the left and to allow searching of said list
        let options = {
            valueNames: [ 'label' ],

            item: `<li class="js-property-list-item c-filter-widget__list-item" data-joins="[]"><span class="label"></span></li>`
        };

        this.list = new List('user-property-list', options, properties);

        $( `#user-property-list li` ).each((index, element) => {

            $(element).attr('data-property-id', properties[index].id);

            if(this.join) {
                let joins = this.joins.concat(this.join.internalName);
                $(element).attr('data-joins', JSON.stringify(joins));
            } else {
                $(element).attr('data-joins', JSON.stringify(['root']));
            }

        });

    }

    loadUserPropertiesForFilter() {

        return new Promise((resolve, reject) => {
            const url = Routing.generate('user_properties_for_filter', {internalIdentifier: this.portalInternalIdentifier, internalName: this.internalName});

            $.ajax({
                url: url
            }).then(data => {
                debugger;
                resolve(data);
            }).catch(jqXHR => {
                debugger;
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }

    handleBackButtonClicked() {
        this.globalEventDispatcher.publish(Settings.Events.FILTER_BACK_TO_HOME_BUTTON_CLICKED);
    }

    handlePropertyListItemClicked(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        const $listItem = $(e.currentTarget);

        if($listItem.hasClass('c-list__list-item--active')) {
            return;
        }

        let propertyId = $listItem.attr('data-property-id');
        let joins = JSON.parse($listItem.attr('data-joins'));

        let property = this.properties.filter(property => {
            return parseInt(property.id) === parseInt(propertyId);
        });

        property[0].joins = joins;

        if(property[0].fieldType === 'custom_object_field') {

            this.globalEventDispatcher.publish(Settings.Events.CUSTOM_OBJECT_FILTER_LIST_ITEM_CLICKED, property[0]);

        } else {

            this.globalEventDispatcher.publish(Settings.Events.FILTER_PROPERTY_LIST_ITEM_CLICKED, property[0]);
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

const listTemplate = () => `
    <div id="user-property-list" class="js-list">
      <ul class="list c-filter-widget__list"></ul>
    </div>
    
`;

export default UserFilterList;