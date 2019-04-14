'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import ColumnSearch from "./ColumnSearch";

class BulkEditRecordPropertyList {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName) {
        debugger;

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.lists = [];


/*

        this.data = data;

        debugger;



        this.globalEventDispatcher.subscribe(
            Settings.Events.LIST_PROPERTY_LIST_ITEM_ADDED,
            this.handlePropertyListItemAdded.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.LIST_PROPERTY_LIST_ITEM_REMOVED,
            this.handlePropertyListItemRemoved.bind(this)
        );*/

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
            BulkEditRecordPropertyList._selectors.search,
            this.handleKeyupEvent.bind(this)
        );

        this.$wrapper.on(
            'click',
            BulkEditRecordPropertyList._selectors.propertyListItem,
            this.handlePropertyListItemClicked.bind(this)
        );

/*

        this.$wrapper.on(
            'click',
            ListPropertyList._selectors.backButton,
            this.handleBackButtonClicked.bind(this)
        );*/

    }

    /**
     * Because this component can keep getting run each time a filter is added
     * you need to remove the handlers otherwise they will keep stacking up
     */
    unbindEvents() {

        this.$wrapper.off('keyup', BulkEditRecordPropertyList._selectors.search);
        this.$wrapper.off('click', BulkEditRecordPropertyList._selectors.propertyListItem);

       /*
        this.$wrapper.off('click', ListPropertyList._selectors.backButton);*/
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

        debugger;

        for(let i = 0; i < this.lists.length; i++) {
            this.lists[i].search(searchValue);
        }

        this.$wrapper.find(BulkEditRecordPropertyList._selectors.list).each((index, element) => {

            let propertyGroupId = $(element).attr('data-property-group');
            let $parent = $(element).closest(`#list-bulk-edit-record-${propertyGroupId}`);

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

        debugger;
        e.stopPropagation();

        this.globalEventDispatcher.publish(Settings.Events.LIST_BACK_BUTTON_CLICKED);

    }

    loadProperties() {

        this.loadPropertiesForReport().then(data => {
            this.propertyGroups = data.data.property_groups;
            this.renderProperties(this.propertyGroups).then(() => {
                debugger;
                /*this.highlightProperties(this.data);*/
            })
        });

    }

    handlePropertyListItemAdded(data) {

        debugger;

        this.data = data;

        this.highlightProperties(data);

    }

    handlePropertyListItemRemoved(data) {

        debugger;

        this.data = data;

        this.highlightProperties(data);

    }

    highlightProperties(data) {

        $(ListPropertyList._selectors.propertyListItem).each((index, element) => {

            if($(element).hasClass('c-report-widget__list-item--active')) {
                $(element).removeClass('c-report-widget__list-item--active');
            }

            let propertyId = $(element).attr('data-property-id');
            let joins = JSON.parse($(element).attr('data-joins'));
            let propertyPath = joins.join('.');

            if(_.has(data, propertyPath)) {

                let properties = _.get(data, propertyPath);

                let propertyMatch = null;

                for(let key in properties) {

                    let property = properties[key];

                    if(parseInt(property.id) === parseInt(propertyId)) {
                        propertyMatch = property;
                    }
                }

                if(propertyMatch) {

                    $(element).addClass('c-report-widget__list-item--active');
                }
            }

        });
    }


    render() {
        debugger;
        this.$wrapper.html(BulkEditRecordPropertyList.markup(this));
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

        let propertyGroupId = $listItem.closest(BulkEditRecordPropertyList._selectors.list).attr('data-property-group');
        let propertyId = $listItem.attr('data-property-id');

        let propertyGroup = this.propertyGroups.filter(propertyGroup => {
            return parseInt(propertyGroup.id) === parseInt(propertyGroupId);
        });

        let properties = propertyGroup[0].properties;

        let property = properties.filter(property => {
            return parseInt(property.id) === parseInt(propertyId);
        });

        this.globalEventDispatcher.publish(Settings.Events.BULK_EDIT_RECORD_PROPERTY_LIST_ITEM_CLICKED, property[0]);
    }

    renderProperties(propertyGroups) {

        let $propertyList = this.$wrapper.find(BulkEditRecordPropertyList._selectors.propertyList);
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

    loadPropertiesForReport() {
        return new Promise((resolve, reject) => {
            debugger;
            const url = Routing.generate('properties_for_list', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});

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
        let $propertyList = this.$wrapper.find(BulkEditRecordPropertyList._selectors.propertyList);
        const html = listTemplate(propertyGroup);
        const $list = $($.parseHTML(html));
        $propertyList.append($list);

        var options = {
            valueNames: [ 'label' ],
            // Since there are no elements in the list, this will be used as template.
            item: `<li class="js-property-list-item c-report-widget__list-item"><span class="label"></span></li>`
        };

        this.lists.push(new List(`list-bulk-edit-record-${propertyGroup.id}`, options, properties));

        $( `#list-bulk-edit-record-${propertyGroup.id} li` ).each((index, element) => {

            $(element).attr('data-property-id', properties[index].id);

        });

    }

    static markup() {

        debugger;
        return `
            <div class="input-group c-search-control">
              <input class="form-control c-search-control__input js-search" type="search" placeholder="Search...">
              <span class="c-search-control__foreground"><i class="fa fa-search"></i></span>
            </div>
            <br>
            <div class="js-property-list c-column-editor__property-list"></div>
        `;
    }

}

const listTemplate = ({id, name}) => `
    <div id="list-bulk-edit-record-${id}">
      <p>${name}</p>
      <ul class="js-list list c-report-widget__list" data-property-group="${id}"></ul>
    </div>
    
`;

export default BulkEditRecordPropertyList;