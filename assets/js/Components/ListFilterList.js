'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import ColumnSearch from "./ColumnSearch";

class ListFilterList {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, join = null, joins = [], data = {}, referencedFilterPath = []) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.join = join;
        this.joins = joins;
        this.lists = [];
        this.data = data;

        // The path to the filter that this filter should be tied to
        // Only used in "WHERE" clauses with mysql "OR" condition
        this.referencedFilterPath = referencedFilterPath;

        this.unbindEvents();

        this.bindEvents();

        this.render();

        this.loadProperties();

    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            search: '.js-search',
            propertyListItem: '.js-property-list-item',
            list: '.js-list',
            propertyList: '.js-property-list',
            backButton: '.js-back-button'

        }
    }

    bindEvents() {

        this.$wrapper.on(
            'keyup',
            ListFilterList._selectors.search,
            this.handleKeyupEvent.bind(this)
        );

        this.$wrapper.on(
            'click',
            ListFilterList._selectors.propertyListItem,
            this.handlePropertyListItemClicked.bind(this)
        );

        this.$wrapper.on(
            'click',
            ListFilterList._selectors.backButton,
            this.handleBackButtonClicked.bind(this)
        );

    }

    /**
     * Because this component can keep getting run each time a filter is added
     * you need to remove the handlers otherwise they will keep stacking up
     */
    unbindEvents() {

        this.$wrapper.off('keyup', ListFilterList._selectors.search);
        this.$wrapper.off('click', ListFilterList._selectors.propertyListItem);
        this.$wrapper.off('click', ListFilterList._selectors.backButton);
    }

    handleKeyupEvent(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        const searchValue = $(e.target).val();

        this.applySearch(searchValue);

    }

    /**
     *
     * @param searchValue
     */
    applySearch(searchValue) {

        for(let i = 0; i < this.lists.length; i++) {
            this.lists[i].search(searchValue);
        }

        this.$wrapper.find(ListFilterList._selectors.list).each((index, element) => {

            let propertyGroupId = $(element).attr('data-property-group');
            let $parent = $(element).closest(`#list-filters-${propertyGroupId}`);

            if($(element).is(':empty') && searchValue !== '') {
                $parent.addClass('d-none');

            } else {
                if($parent.hasClass('d-none')) {
                    $parent.removeClass('d-none');
                }
            }
        });
    }

    handleBackButtonClicked(e) {

        e.stopPropagation();

        debugger;

        if(this.join) {

            this.globalEventDispatcher.publish(Settings.Events.FILTER_BACK_TO_LIST_BUTTON_CLICKED);
        } else {

            this.globalEventDispatcher.publish(Settings.Events.FILTER_BACK_TO_NAVIGATION_BUTTON_CLICKED);
        }

    }

    loadProperties() {

        this.loadPropertiesForFilterList().then(data => {
            debugger;
            this.propertyGroups = data.data.property_groups;
            this.renderProperties(this.propertyGroups).then(() => {
                debugger;
                /*this.highlightProperties(this.data);*/
            })
        });

    }

    handlePropertyListItemAdded(data) {

        this.data = data;

        this.highlightProperties(data);

    }

    handlePropertyListItemRemoved(data) {

        debugger;

        this.data = data;

        this.highlightProperties(data);

    }

    highlightProperties(data) {

        debugger;
        $(ListFilterList._selectors.propertyListItem).each((index, element) => {

            if($(element).hasClass('c-report-widget__list-item--active')) {
                $(element).removeClass('c-report-widget__list-item--active');
            }

            debugger;

            let propertyId = $(element).attr('data-property-id');
            let joins = JSON.parse($(element).attr('data-joins'));
            let propertyPath = joins.join('.');

            debugger;

            if(_.has(data, propertyPath)) {

                debugger;

                let properties = _.get(data, propertyPath);

                debugger;

                let propertyMatch = properties.filter(property => {
                    debugger;
                    return parseInt(property.id) === parseInt(propertyId);
                });

                if(propertyMatch.length === 1) {
                    $(element).addClass('c-report-widget__list-item--active');
                }
            }

        });
    }


    render() {
        this.$wrapper.html(ListFilterList.markup(this));
    }

    handlePropertyListItemClicked(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        const $listItem = $(e.currentTarget);

        if($listItem.hasClass('c-report-widget__list-item--active')) {
            return;
        }

        let propertyGroupId = $listItem.closest(ListFilterList._selectors.list).attr('data-property-group');
        let propertyId = $listItem.attr('data-property-id');
        let joins = JSON.parse($listItem.attr('data-joins'));
        let referencedFilterPath = JSON.parse($listItem.attr('data-referenced-filter-path'));


        let propertyGroup = this.propertyGroups.filter(propertyGroup => {
            return parseInt(propertyGroup.id) === parseInt(propertyGroupId);
        });

        let properties = propertyGroup[0].properties;

        let property = properties.filter(property => {
            return parseInt(property.id) === parseInt(propertyId);
        });

        property[0].joins = joins;
        property[0].referencedFilterPath = referencedFilterPath;

        if(property[0].fieldType === 'custom_object_field') {

            this.globalEventDispatcher.publish(Settings.Events.LIST_CUSTOM_OBJECT_FILTER_LIST_ITEM_CLICKED, property[0], joins);
        } else {

            this.globalEventDispatcher.publish(Settings.Events.LIST_FILTER_ITEM_CLICKED, property[0]);
        }
    }

    renderProperties(propertyGroups) {

        let $propertyList = this.$wrapper.find(ListFilterList._selectors.propertyList);
        $propertyList.html("");

        return new Promise((resolve, reject) => {

            for(let i = 0; i < propertyGroups.length; i++) {
                let propertyGroup = propertyGroups[i];
                let properties = propertyGroup.properties;

                debugger;
                this._addList(propertyGroup, properties);

            }
            resolve();
        });
    }

    loadPropertiesForFilterList() {
        return new Promise((resolve, reject) => {
            debugger;
            const url = Routing.generate('get_properties', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});

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

    /**
     * @param propertyGroup
     * @param properties
     * @private
     */
    _addList(propertyGroup, properties) {

        debugger;
        let $propertyList = this.$wrapper.find(ListFilterList._selectors.propertyList);
        const html = listTemplate(propertyGroup);
        const $list = $($.parseHTML(html));
        $propertyList.append($list);

        var options = {
            valueNames: [ 'label' ],
            // Since there are no elements in the list, this will be used as template.
            item: `<li class="js-property-list-item c-report-widget__list-item"><span class="label"></span></li>`
        };

        this.lists.push(new List(`list-filters-${propertyGroup.id}`, options, properties));

        $( `#list-filters-${propertyGroup.id} li` ).each((index, element) => {
            $(element).attr('data-property-id', properties[index].id);

            if(this.referencedFilterPath) {
                debugger;
                $(element).attr('data-referenced-filter-path', JSON.stringify(this.referencedFilterPath));
            }

            if(this.join) {
                let joins = this.joins.concat(this.join.internalName);
                $(element).attr('data-joins', JSON.stringify(joins));
            } else {
                $(element).attr('data-joins', JSON.stringify(['root']));
            }

        });

    }

    static markup() {

        debugger;
        return `
            <button type="button" class="btn btn-link js-back-button"><i class="fa fa-chevron-left"></i> Back</button>
            <div class="input-group c-search-control">
              <input class="form-control c-search-control__input js-search" type="search" placeholder="Search...">
              <span class="c-search-control__foreground"><i class="fa fa-search"></i></span>
            </div>
            <div class="js-property-list c-report-widget__property-list"></div>
        `;
    }

}

const listTemplate = ({id, name}) => `
    <div id="list-filters-${id}">
      <p>${name}</p>
      <ul class="js-list list c-report-widget__list" data-property-group="${id}"></ul>
    </div>
    
`;

export default ListFilterList;