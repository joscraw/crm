'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import ColumnSearch from "./ColumnSearch";
import StringHelper from "../StringHelper";
import ReportConnectObjectFormModal from "./ReportConnectObjectFormModal";
import ReportAddFilterFormModal from "./ReportAddFilterFormModal";

class ReportPropertyList {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, data) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = data.selectedCustomObject.internalName;
        this.lists = [];
        this.data = data;
        this.unbindEvents();
        this.bindEvents();
        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_OBJECT_CONNECTED_JSON_UPDATED,
            this.refreshPropertyList.bind(this)
        );
        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_CONNECTION_REMOVED,
            this.refreshPropertyList.bind(this)
        );
        this.render();
        this.refreshPropertyList(this.data);
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
            backButton: '.js-back-button',
            filterListItem: '.js-filter-list-item',
            propertyRemoveItem: '.js-property-remove-item'

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

        this.$wrapper.on(
            'click',
            ReportPropertyList._selectors.propertyRemoveItem,
            this.handlePropertyRemoveItemClicked.bind(this)
        );

        this.$wrapper.on(
            'click',
            ReportPropertyList._selectors.filterListItem,
            this.handleFilterListItemClicked.bind(this)
        );

        this.$wrapper.on(
            'click',
            ReportPropertyList._selectors.backButton,
            this.handleBackButtonClicked.bind(this)
        );
    }

    /**
     * Because this component can keep getting run each time a filter is added
     * you need to remove the handlers otherwise they will keep stacking up
     */
    unbindEvents() {
        this.$wrapper.off('keyup', ReportPropertyList._selectors.search);
        this.$wrapper.off('click', ReportPropertyList._selectors.propertyListItem);
        this.$wrapper.off('click', ReportPropertyList._selectors.filterListItem);
        this.$wrapper.off('click', ReportPropertyList._selectors.backButton);
        this.$wrapper.off('click', ReportPropertyList._selectors.propertyRemoveItem);
    }

/*    addDefaultPropertiesToTable() {
        debugger;
        let propertyGroups = this.propertyGroups;
        for(let propertyGroupId in propertyGroups) {
            let propertyGroup = propertyGroups[propertyGroupId];
            let properties = propertyGroup.properties;
            //Lets go ahead and add a couple properties to the report right off the get go and simulate a property click
            if(typeof properties[0] !== 'undefined') {
                this.globalEventDispatcher.publish(Settings.Events.REPORT_PROPERTY_LIST_ITEM_CLICKED, properties[0]);
            }
            if(typeof properties[1] !== 'undefined') {
                this.globalEventDispatcher.publish(Settings.Events.REPORT_PROPERTY_LIST_ITEM_CLICKED, properties[1]);
            }
            break;
        }
    }*/

    handleKeyupEvent(e) {
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
        this.$wrapper.find(ReportPropertyList._selectors.list).each((index, element) => {
            let propertyGroupId = $(element).attr('data-property-group');
            let $parent = $(element).closest(`#list-property-${propertyGroupId}`);
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
        this.globalEventDispatcher.publish(Settings.Events.REPORT_BACK_BUTTON_CLICKED);
    }

    refreshPropertyList(data) {
        debugger;
        this.loadPropertiesForAllObjects(data).then((data) => {
            this.propertyGroups = data.data.property_groups;
            this.renderProperties(this.propertyGroups).then(() => {
                let properties = [];
                for(let propertyGroupId in this.propertyGroups) {
                    let propertyGroup = this.propertyGroups[propertyGroupId];
                    for(let property of propertyGroup.properties) {
                        properties.push(property);
                    }
                }
                this.globalEventDispatcher.publish(Settings.Events.REPORT_PROPERTY_LIST_REFRESHED, properties);
            })
        });
    }

    loadPropertiesForAllObjects(data) {
        return new Promise((resolve, reject) => {
            const url = Routing.generate('get_properties_from_multiple_objects', {
                internalIdentifier: this.portalInternalIdentifier,
                internalName: this.customObjectInternalName});
            $.ajax({
                url: url,
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({data : data})
            }).then(data => {
                resolve(data);
            }).catch(jqXHR => {
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }

    render() {
        this.$wrapper.html(ReportPropertyList.markup(this));
    }

    handlePropertyListItemClicked(e) {
        if(e.cancelable) {
            e.preventDefault();
        }
        const $listItem = $(e.currentTarget).parent('li');
        if($listItem.hasClass('c-report-widget__list-item--active')) {
            return;
        }
        let propertyGroupId = $listItem.closest(ReportPropertyList._selectors.list).attr('data-property-group');
        let propertyId = $listItem.attr('data-property-id');
        let propertyGroup = this.propertyGroups[propertyGroupId];
        let properties = propertyGroup.properties;
        let property = properties.filter(property => {
            return parseInt(property.id) === parseInt(propertyId);
        });
        this.globalEventDispatcher.publish(Settings.Events.REPORT_PROPERTY_LIST_ITEM_CLICKED, property[0]);
    }

    handlePropertyRemoveItemClicked(e) {
        if(e.cancelable) {
            e.preventDefault();
        }
        const $listItem = $(e.currentTarget).parent('li');
        if($listItem.hasClass('c-report-widget__list-item--active')) {
            return;
        }
        let propertyGroupId = $listItem.closest(ReportPropertyList._selectors.list).attr('data-property-group');
        let propertyId = $listItem.attr('data-property-id');

        let propertyGroup = this.propertyGroups[propertyGroupId];
        let properties = propertyGroup.properties;
        let property = properties.filter(property => {
            return parseInt(property.id) === parseInt(propertyId);
        });
        this.globalEventDispatcher.publish(Settings.Events.REPORT_REMOVE_SELECTED_COLUMN_ICON_CLICKED, property[0]);
    }

    handleFilterListItemClicked(e) {
        if(e.cancelable) {
            e.preventDefault();
        }
        const $listItem = $(e.currentTarget).parent('li');
        let propertyGroupId = $listItem.closest(ReportPropertyList._selectors.list).attr('data-property-group');
        let propertyId = $listItem.attr('data-property-id');
        let propertyGroup = this.propertyGroups[propertyGroupId];
        let properties = propertyGroup.properties;
        let property = properties.filter(property => {
            return parseInt(property.id) === parseInt(propertyId);
        });
        new ReportAddFilterFormModal(this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property[0]);
    }

    renderProperties(propertyGroups) {
        let $propertyList = this.$wrapper.find(ReportPropertyList._selectors.propertyList);
        $propertyList.empty();
        return new Promise((resolve, reject) => {
            for(let propertyGroupId in propertyGroups) {
                let propertyGroup = propertyGroups[propertyGroupId];
                let properties = propertyGroup.properties;
                this._addList(propertyGroup, properties);
            }
            resolve();
        });
    }

    /**
     * @param propertyGroup
     * @param properties
     * @private
     */
    _addList(propertyGroup, properties) {
        let $propertyList = this.$wrapper.find(ReportPropertyList._selectors.propertyList);
        const html = listTemplate(propertyGroup);
        const $list = $($.parseHTML(html));
        $propertyList.append($list);
        var options = {
            valueNames: [ 'label' ],
            // Since there are no elements in the list, this will be used as template.
            item: `<li class="c-report-widget__list-item"><span class="label"></span><i class="fa fa-trash-o js-property-remove-item" style="float: right; padding-left: 5px"></i> <i class="fa fa-plus js-property-list-item" style="float: right; padding-left: 5px"></i> <i class="fa fa-filter js-filter-list-item" style="float: right"></li>`
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
    }

    static markup() {
        return `
            <h4>Available Properties</h4>
            <div class="input-group c-search-control">
              <input class="form-control c-search-control__input js-search" type="search" placeholder="Search...">
              <span class="c-search-control__foreground"><i class="fa fa-search"></i></span>
            </div>
            <div class="js-property-list c-report-widget__property-list"></div>
        `;
    }
}

const listTemplate = ({id, grouping_label}) => `
    <div id="list-property-${id}">
      <p>${grouping_label}</p>
      <ul class="js-list list c-report-widget__list" data-property-group="${id}"></ul>
    </div>
    
`;

export default ReportPropertyList;