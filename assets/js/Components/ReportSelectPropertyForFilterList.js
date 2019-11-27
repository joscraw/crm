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

class ReportSelectPropertyForFilterList {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, data, parentFilterUid) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.data = data;
        this.parentFilterUid = parentFilterUid;
        this.lists = [];
        this.unbindEvents();
        this.bindEvents();
        this.render();
        this.loadPropertiesForAllObjects(data).then((data) => {
            this.propertyGroups = data.data.property_groups;
            this.renderProperties(this.propertyGroups).then(() => {
            })
        });
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
            filterListItem: '.js-filter-list-item'
        }
    }

    bindEvents() {
        this.$wrapper.on(
            'keyup',
            ReportSelectPropertyForFilterList._selectors.search,
            this.handleKeyupEvent.bind(this)
        );
        this.$wrapper.on(
            'click',
            ReportSelectPropertyForFilterList._selectors.filterListItem,
            this.handleFilterListItemClicked.bind(this)
        );
    }

    /**
     * Because this component can keep getting run each time a filter is added
     * you need to remove the handlers otherwise they will keep stacking up
     */
    unbindEvents() {
        this.$wrapper.off('keyup', ReportSelectPropertyForFilterList._selectors.search);
        this.$wrapper.off('click', ReportSelectPropertyForFilterList._selectors.filterListItem);
    }

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
        this.$wrapper.find(ReportSelectPropertyForFilterList._selectors.list).each((index, element) => {
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

    render() {
        this.$wrapper.html(ReportSelectPropertyForFilterList.markup(this));
    }

    handleFilterListItemClicked(e) {
        if(e.cancelable) {
            e.preventDefault();
        }
        const $listItem = $(e.currentTarget);
        let propertyGroupId = $listItem.closest(ReportSelectPropertyForFilterList._selectors.list).attr('data-property-group');
        let propertyId = $listItem.attr('data-property-id');
        let propertyGroup = this.propertyGroups[propertyGroupId];
        let properties = propertyGroup.properties;
        let property = properties.filter(property => {
            return parseInt(property.id) === parseInt(propertyId);
        });
        if(this.parentFilterUid) {
            property[0].parentFilterUid = this.parentFilterUid;
            property[0].hasParentFilter = true;
        }
        new ReportAddFilterFormModal(this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property[0]);
    }

    renderProperties(propertyGroups) {
        let $propertyList = this.$wrapper.find(ReportSelectPropertyForFilterList._selectors.propertyList);
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
        let $propertyList = this.$wrapper.find(ReportSelectPropertyForFilterList._selectors.propertyList);
        const html = listTemplate(propertyGroup);
        const $list = $($.parseHTML(html));
        $propertyList.append($list);
        var options = {
            valueNames: [ 'label' ],
            // Since there are no elements in the list, this will be used as template.
            item: `<li class="c-report-widget__list-item js-filter-list-item"><span class="label"></span></li>`
        };
        this.lists.push(new List(`list-property-for-filter-${propertyGroup.id}`, options, properties));
        $( `#list-property-for-filter-${propertyGroup.id} li` ).each((index, element) => {
            $(element).attr('data-property-id', properties[index].id);
            if(this.uid) {
                $(element).attr('data-and-uid', this.uid);
            }
        });
    }

    static markup() {
        return `
            <div class="input-group c-search-control">
              <input class="form-control c-search-control__input js-search" type="search" placeholder="Search...">
              <span class="c-search-control__foreground"><i class="fa fa-search"></i></span>
            </div>
            <div class="js-property-list c-report-widget__property-list"></div>
        `;
    }
}

const listTemplate = ({id, grouping_label}) => `
    <div id="list-property-for-filter-${id}">
      <p>${grouping_label}</p>
      <ul class="js-list list c-report-widget__list" data-property-group="${id}"></ul>
    </div>
    
`;

export default ReportSelectPropertyForFilterList;