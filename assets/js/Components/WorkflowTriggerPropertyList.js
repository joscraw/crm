'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import ColumnSearch from "./ColumnSearch";
import ContextHelper from "../ContextHelper";
import ListFilterList from "./ListFilterList";

class WorkflowTriggerPropertyList {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, uid, customObject, join = null, joins = [], referencedFilterPath = []) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.lists = [];
        this.uid = uid;
        this.customObject = customObject;
        this.join = join;
        this.joins = joins;
        this.lists = [];
        this.referencedFilterPath = referencedFilterPath;

        this.unbindEvents();
        this.bindEvents();

        this.render();
        this.loadProperties()
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
            backButton: '.js-backButton',
            backToCustomObjectListButton: '.js-back-to-custom-object-list-button'

        }
    }

    bindEvents() {

        this.$wrapper.on(
            'keyup',
            WorkflowTriggerPropertyList._selectors.search,
            this.handleKeyupEvent.bind(this)
        );
        this.$wrapper.on(
            'click',
            WorkflowTriggerPropertyList._selectors.propertyListItem,
            this.handlePropertyListItemClicked.bind(this)
        );

        this.$wrapper.on(
            'click',
            WorkflowTriggerPropertyList._selectors.backButton,
            this.handleBackButtonClicked.bind(this)
        );

    }

    /**
     * Because this component can keep getting run each time a filter is added
     * you need to remove the handlers otherwise they will keep stacking up
     */
    unbindEvents() {
        this.$wrapper.off('click', WorkflowTriggerPropertyList._selectors.backButton);
        this.$wrapper.off('keyup', WorkflowTriggerPropertyList._selectors.search);
        this.$wrapper.off('click', WorkflowTriggerPropertyList._selectors.propertyListItem);
    }

    handleBackButtonClicked(e) {

        debugger;
        e.stopPropagation();

        this.globalEventDispatcher.publish(
            Settings.Events.WORKFLOW_BACK_BUTTON_CLICKED,
            Settings.VIEWS.WORKFLOW_TRIGGER_SELECT_TRIGGER_TYPE
        );
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
    }

    loadProperties() {
        this.loadPropertiesForWorkflowTrigger().then(data => {
            this.propertyGroups = data.data.property_groups;
            this.renderProperties(this.propertyGroups).then(() => {
            })
        });
    }

    render() {
        this.$wrapper.html(WorkflowTriggerPropertyList.markup(this));
    }

    handlePropertyListItemClicked(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        const $listItem = $(e.currentTarget);

        if($listItem.hasClass('c-private-card__item--active')) {
            return;
        }

        let propertyGroupId = $listItem.closest(WorkflowTriggerPropertyList._selectors.list).attr('data-property-group');
        let propertyId = $listItem.attr('data-property-id');
        let joins = JSON.parse($listItem.attr('data-joins'));
        let referencedFilterPath = $listItem.attr('data-referenced-filter-path');

        let propertyGroup = this.propertyGroups.filter(propertyGroup => {
            return parseInt(propertyGroup.id) === parseInt(propertyGroupId);
        });

        let properties = propertyGroup[0].properties;

        let property = properties.filter(property => {
            return parseInt(property.id) === parseInt(propertyId);
        });

        debugger;
        property[0].referencedFilterPath = referencedFilterPath;
        property[0].joins = joins;

        if(property[0].fieldType === 'custom_object_field') {

            this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_TRIGGER_CUSTOM_OBJECT_FILTER_LIST_ITEM_CLICKED, property[0], joins);
        } else {

            this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_TRIGGER_PROPERTY_LIST_ITEM_CLICKED, property[0]);
        }
    }

    renderProperties(propertyGroups) {

        let $propertyList = this.$wrapper.find(WorkflowTriggerPropertyList._selectors.propertyList);
        $propertyList.html("");

        return new Promise((resolve, reject) => {

            for(let i = 0; i < propertyGroups.length; i++) {
                let propertyGroup = propertyGroups[i];
                let properties = propertyGroup.properties;
                this._addList(propertyGroup, properties);

            }
            resolve();
        });
    }

    loadPropertiesForWorkflowTrigger() {
        return new Promise((resolve, reject) => {
            debugger;
            const url = Routing.generate('get_properties', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObject.internalName});

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
        let $propertyList = this.$wrapper.find(WorkflowTriggerPropertyList._selectors.propertyList);
        const html = listTemplate(propertyGroup);
        const $list = $($.parseHTML(html));
        $propertyList.append($list);

        var options = {
            valueNames: [ 'label' ],
            // Since there are no elements in the list, this will be used as template.
            item: `<div class="js-property-list-item c-private-card__item"><span class="label"></span></li>`
        };

        this.lists.push(new List(`list-property-${propertyGroup.id}`, options, properties));

        $( `#list-property-${propertyGroup.id} .js-property-list-item` ).each((index, element) => {
            $(element).attr('data-property-id', properties[index].id);

            debugger;
            if(this.referencedFilterPath) {
                debugger;
                $(element).attr('data-referenced-filter-path', this.referencedFilterPath);
            }

            if(this.join) {
                let joins = this.joins.concat(this.join.internalName);
                $(element).attr('data-joins', JSON.stringify(joins));
            } else {
                $(element).attr('data-joins', JSON.stringify(['root']));
            }
        });

    }

    static markup({customObject: {label}}) {

        debugger;
        return `
        <div>
            <button type="button" class="btn btn-link js-backButton float-left" style="padding:0"><i class="fa fa-chevron-left"></i> Back</button>
        </div>
            <h4>${label.toUpperCase()} PROPERTIES</h4>
            <div class="input-group c-search-control">
              <input class="form-control c-search-control__input js-search" type="search" placeholder="Search...">
              <span class="c-search-control__foreground"><i class="fa fa-search"></i></span>
            </div>
            <div class="js-property-list c-report-widget__property-list"></div>
        `;
    }

}

const listTemplate = ({id, name}) => `
    <div id="list-property-${id}">
      <p>${name}</p>
      <div class="js-list list c-private-card" data-property-group="${id}"></div>
    </div>
    
`;

export default WorkflowTriggerPropertyList;