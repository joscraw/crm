'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import ColumnSearch from "./ColumnSearch";
import ContextHelper from "../ContextHelper";

class WorkflowTriggerType {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, uid) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.uid = uid;
        this.triggerTypes = [];
        this.list = null;

        this.unbindEvents();
        this.bindEvents();

        this.render();
        this.loadTriggerTypes();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            search: '.js-search',
            triggerListItem: '.js-trigger-list-item',
            list: '.js-list',
            triggerList: '.js-trigger-list',
            backButton: '.js-back-button'

        }
    }

    bindEvents() {

        this.$wrapper.on(
            'keyup',
            WorkflowTriggerType._selectors.search,
            this.handleKeyupEvent.bind(this)
        );

        this.$wrapper.on(
            'click',
            WorkflowTriggerType._selectors.triggerListItem,
            this.handleTriggerListItemClicked.bind(this)
        );
    }

    /**
     * Because this component can keep getting run each time a filter is added
     * you need to remove the handlers otherwise they will keep stacking up
     */
    unbindEvents() {
        this.$wrapper.off('keyup', WorkflowTriggerType._selectors.search);
        this.$wrapper.off('click', WorkflowTriggerType._selectors.triggerListItem);
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
        this.list.search(searchValue);
    }

    loadTriggerTypes() {
        this.makeRequestForTriggerTypes().then(data => {
            debugger;
            this.triggerTypes = data.data;
            debugger;
            this.renderTriggerTypes(this.triggerTypes);
        });
    }

    render() {
        this.$wrapper.html(WorkflowTriggerType.markup(this));
    }

    handleTriggerListItemClicked(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        const $listItem = $(e.currentTarget);

        if($listItem.hasClass('c-private-card__item--active')) {
            return;
        }

        let triggerName = $listItem.attr('data-trigger-name');


        let trigger = this.triggerTypes.filter(trigger => {
            return trigger.name === triggerName;
        });

        debugger;
        this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_TRIGGER_LIST_ITEM_CLICKED, trigger[0]);
    }

    renderTriggerTypes(triggerTypes) {

        let $propertyList = this.$wrapper.find(WorkflowTriggerType._selectors.triggerList);
        $propertyList.html("");

        let $triggerList = this.$wrapper.find(WorkflowTriggerType._selectors.triggerList);
        const html = listTemplate();
        const $list = $($.parseHTML(html));
        $triggerList.append($list);

        var options = {
            valueNames: [ 'description' ],
            // Since there are no elements in the list, this will be used as template.
            item: `<div class="js-trigger-list-item c-private-card__item"><span class="description"></span></div>`
        };

        this.list = new List(`list-triggers`, options, triggerTypes);

        $( `#list-triggers .js-trigger-list-item` ).each((index, element) => {
            $(element).attr('data-trigger-name', triggerTypes[index].name);
        });
    }

    makeRequestForTriggerTypes() {
        return new Promise((resolve, reject) => {
            debugger;
            const url = Routing.generate('get_trigger_types', {internalIdentifier: this.portalInternalIdentifier});

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

    static markup() {

        debugger;
        return `
            <div class="js-trigger-list c-report-widget__property-list"></div>
        `;
    }

}

const listTemplate = () => `
    <div id="list-triggers">
      <h3>Select trigger type</h3>
      <div class="js-list list c-private-card"></div>
    </div>
    
`;

export default WorkflowTriggerType;