'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import ColumnSearch from "./ColumnSearch";
import StringHelper from "../StringHelper";

class ReportPropertyList {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, join = null, joins = [], data = {}, customObject = {}) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.join = join;
        this.joins = joins;
        this.lists = [];
        this.data = data;
        let uID = StringHelper.makeCharId();
        // set up the initial object to pull down associated properties
        let initialData = {joins: {}};
        _.set(initialData.joins, uID, {connected_object: customObject});

        this.unbindEvents();

        this.bindEvents();

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_PROPERTY_LIST_ITEM_ADDED,
            this.handlePropertyListItemAdded.bind(this)
        ));

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_PROPERTY_LIST_ITEM_REMOVED,
            this.handlePropertyListItemRemoved.bind(this)
        ));

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
                Settings.Events.REPORT_OBJECT_CONNECTED_JSON_UPDATED,
                this.refreshPropertyList.bind(this)
            ));

        this.render();
        this.refreshPropertyList(initialData)
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
        this.$wrapper.off('click', ReportPropertyList._selectors.backButton);
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

    loadProperties() {
        this.loadPropertiesForReport().then(data => {
            this.propertyGroups = data.data.property_groups;
            this.renderProperties(this.propertyGroups).then(() => {
                debugger;
                this.highlightProperties(this.data);
            })
        });
    }

    refreshPropertyList(data) {
        this.loadPropertiesForAllObjects(data).then((data) => {
            debugger;
            this.propertyGroups = data.data.property_groups;
            this.renderProperties(this.propertyGroups).then(() => {
                debugger;
                this.highlightProperties(this.data);
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
                data: {'data': data}
            }).then(data => {
                resolve(data);
            }).catch(jqXHR => {
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }

    handlePropertyListItemAdded(data) {
        this.data = data;
        this.highlightProperties(data);
    }

    handlePropertyListItemRemoved(data) {
        this.data = data;
        this.highlightProperties(data);
    }

    highlightProperties(data) {
        $(ReportPropertyList._selectors.propertyListItem).each((index, element) => {
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
        this.$wrapper.html(ReportPropertyList.markup(this));
    }

    handlePropertyListItemClicked(e) {
        if(e.cancelable) {
            e.preventDefault();
        }
        const $listItem = $(e.currentTarget);
        if($listItem.hasClass('c-report-widget__list-item--active')) {
            return;
        }
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
        property[0].joins = joins;
        if(property[0].fieldType === 'custom_object_field') {
            this.globalEventDispatcher.publish(Settings.Events.REPORT_CUSTOM_OBJECT_PROPERTY_LIST_ITEM_CLICKED, property[0], joins);
        } else {
            this.globalEventDispatcher.publish(Settings.Events.REPORT_PROPERTY_LIST_ITEM_CLICKED, property[0]);
        }
    }

    renderProperties(propertyGroups) {
        let $propertyList = this.$wrapper.find(ReportPropertyList._selectors.propertyList);
        return new Promise((resolve, reject) => {
            for(let propertyGroupId in propertyGroups) {
                let propertyGroup = propertyGroups[propertyGroupId];
                let properties = propertyGroup.properties;
                this._addList(propertyGroup, properties);
            }
            resolve();
        });
    }

    loadPropertiesForReport() {
        return new Promise((resolve, reject) => {
            const url = Routing.generate('get_properties', {
                    internalIdentifier: this.portalInternalIdentifier,
                    internalName: this.customObjectInternalName
                }) + "?excludeCustomObjects=true&includeGroupingLabel=true";
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
            item: `<li class="js-property-list-item c-report-widget__list-item"><span class="label"></span><i class="fa fa-plus js-add-property" style="float: right"></i> <i class="fa fa-filter" style="float: right"></li>`
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
            <button type="button" class="btn btn-link js-back-button"><i class="fa fa-chevron-left"></i> Back</button>
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