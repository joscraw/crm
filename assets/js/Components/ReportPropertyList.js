'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import ColumnSearch from "./ColumnSearch";

class ReportPropertyList {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, join = null, joins = []) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.join = join;
        this.joins = joins;
        this.lists = [];
        this.data = {};

        debugger;

        this.unbindEvents();

        this.bindEvents();

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_PROPERTY_LIST_ITEM_ADDED,
            this.handlePropertyListItemAdded.bind(this)
        );


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
            selectedColumnsContainer: '.js-selected-columns-container'

        }
    }

    bindEvents() {

        this.$wrapper.on(
            'keyup',
            ReportPropertyList._selectors.search,
            this.handleKeyupEvent.bind(this)
        );

        this.$wrapper.on(
            'click',
            ReportPropertyList._selectors.propertyListItem,
            this.handlePropertyListItemClicked.bind(this)
        );

    }

    /**
     * Because this component can keep getting run each time a filter is added
     * you need to remove the handlers otherwise they will keep stacking up
     */
    unbindEvents() {

        this.$wrapper.off('keyup', ReportPropertyList._selectors.search);
        this.$wrapper.off('click', ReportPropertyList._selectors.propertyListItem);
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

        this.$wrapper.find(ReportPropertyList._selectors.list).each((index, element) => {
            if($(element).find('.list').is(':empty') && searchValue !== '') {
                $(element).addClass('d-none');

            } else {
                if($(element).hasClass('d-none')) {
                    $(element).removeClass('d-none');
                }
            }
        });
    }

    loadProperties() {

        this.loadPropertiesForReport().then(data => {
            this.propertyGroups = data.data.property_groups;
            this.renderProperties(this.propertyGroups).then(() => {
                debugger;
                /*this._setSelectedColumnsCount();*/
            })
        });

    }

    handlePropertyListItemAdded(data) {

        this.data = data;

        this.highlightProperties(data);

        this.setSelectedColumns(data);
    }

    highlightProperties(data) {

        $(ReportPropertyList._selectors.propertyListItem).each((index, element) => {

            let propertyId = $(element).attr('data-property-id');
            let joins = JSON.parse($(element).attr('data-joins'));
            let propertyPath = joins.join('.');

            if(_.has(data, propertyPath)) {

                let properties = _.get(data, propertyPath);

                let propertyMatch = properties.filter(property => {
                    return parseInt(property.id) === parseInt(propertyId);
                });

                if(propertyMatch.length === 1) {
                    $(element).addClass('c-report-widget__list-item--active');
                }
            }

        });
    }


    setSelectedColumns(data) {

        debugger;

        let columns = [];
        function search(data) {
            debugger;
            for(let key in data) {
                debugger;
                if(data[key] instanceof Array) {
                    search(data[key]);
                } else {
                    columns.push(data[key]);
                }
            }
        }

        search(this.data);

        const $selectedColumnsContainer = $(ReportPropertyList._selectors.selectedColumnsContainer);
        $selectedColumnsContainer.html("");

        for (let column of columns) {
            debugger;
            this._addSelectedColumn(column.label, column.id);
        }

    }

    render() {
        this.$wrapper.html(ReportPropertyList.markup(this));
    }

    handlePropertyListItemClicked(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        const $listItem = $(e.currentTarget);
        let propertyGroupId = $listItem.closest(ReportPropertyList._selectors.list).attr('data-property-group');
        let propertyId = $listItem.attr('data-property-id');
        let joins = JSON.parse($listItem.attr('data-joins'));


        let propertyGroup = this.propertyGroups.filter(propertyGroup => {
            return parseInt(propertyGroup.id) === parseInt(propertyGroupId);
        });

        let properties = propertyGroup[0].properties;

        let property = properties.filter(property => {
            return parseInt(property.id) === parseInt(propertyId);
        });

        if(property[0].fieldType === 'custom_object_field') {

            this.globalEventDispatcher.publish(Settings.Events.REPORT_CUSTOM_OBJECT_PROPERTY_LIST_ITEM_CLICKED, property[0], joins);
        } else {

            property[0].joins = joins;

            this.globalEventDispatcher.publish(Settings.Events.REPORT_PROPERTY_LIST_ITEM_CLICKED, property[0]);
        }
    }

    renderProperties(propertyGroups) {

        let $propertyList = this.$wrapper.find(ReportPropertyList._selectors.propertyList);
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
            const url = Routing.generate('properties_for_report', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});

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
        let $propertyList = this.$wrapper.find(ReportPropertyList._selectors.propertyList);
        const html = listTemplate(propertyGroup);
        const $list = $($.parseHTML(html));
        $propertyList.append($list);

        var options = {
            valueNames: [ 'label' ],
            // Since there are no elements in the list, this will be used as template.
            item: `<li class="js-property-list-item c-report-widget__list-item"><span class="label"></span></li>`
        };

        this.lists.push(new List(`list-property-${propertyGroup.id}`, options, properties));

        $( `#list-property-${propertyGroup.id} li` ).each((index, element) => {
            $(element).attr('data-property-id', properties[index].id);

            if(this.join) {
                let joins = this.joins.concat(this.join.internalName);
                $(element).attr('data-joins', JSON.stringify(joins));
            } else {
                $(element).attr('data-joins', JSON.stringify(['root']));
            }

        });




/*        $( `#list-columns-${propertyGroup.id} li input[type="checkbox"]` ).each((index, element) => {
            $(element).attr('data-label', properties[index].label);
            $(element).attr('data-property-id', properties[index].id);

            // Used to make sure when you click the label the checkbox gets checked
            $(element).attr('id', `property-${properties[index].id}`);
            $(element).next().attr('for', `property-${properties[index].id}`);
        });

        let selectedColumns = {};
        debugger;
        for(let i = 0; i < properties.length; i++) {
            debugger;
            let property = properties[i];

            if(property.isColumn) {
                debugger;
                $( `#list-columns-${propertyGroup.id} li [data-property-id='${property.id}']` ).prop('checked', true);
                selectedColumns[property.columnOrder] = {'label': property.label, 'id': property.id};
            } else {
                $( `#list-columns-${propertyGroup.id} li [data-property-id='${property.id}']` ).prop('checked', false);
            }
        }*/

        // make sure the selected columns appear in the correct order
        debugger;
        /*for(let order in selectedColumns) {
            this._addSelectedColumn(selectedColumns[order].label, selectedColumns[order].id);
        }*/

    }

    _addSelectedColumn(label, propertyId) {
        debugger;
        const $selectedColumnsContainer = $(ReportPropertyList._selectors.selectedColumnsContainer);
        const html = selectedColumnTemplate(label, propertyId);
        const $selectedColumnTemplate = $($.parseHTML(html));
        $selectedColumnsContainer.append($selectedColumnTemplate);

        /*this.activatePlugins();
        this._setSelectedColumnsCount();*/
    }

    static markup() {

        debugger;
        return `
            <div class="input-group c-search-control">
              <input class="form-control c-search-control__input js-search" type="search" placeholder="Search...">
              <span class="c-search-control__foreground"><i class="fa fa-search"></i></span>
            </div>
            <div class="js-property-list c-report-widget__property-list"></div>
        `;
    }

}

const selectedColumnTemplate = (label, id) => `
    <div class="card js-selected-column" id="${id}">
        <div class="card-body">${label}<span><i class="fa fa-times js-remove-selected-column-icon c-column-editor__remove-icon" data-property-id="${id}" aria-hidden="true"></i></span></div>
    </div>
`;

const listTemplate = ({id, name}) => `
    <div id="list-property-${id}">
      <p>${name}</p>
      <ul class="js-list list c-report-widget__list" data-property-group="${id}"></ul>
    </div>
    
`;

export default ReportPropertyList;