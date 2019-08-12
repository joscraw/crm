'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import ColumnSearch from "./ColumnSearch";
import ContextHelper from "../ContextHelper";

class WorkflowActionType {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, uid) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.uid = uid;
        this.actionTypes = [];
        this.list = null;

        this.unbindEvents();
        this.bindEvents();

        this.render();
        this.loadActionTypes();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            search: '.js-search',
            actionListItem: '.js-action-list-item',
            list: '.js-list',
            actionList: '.js-action-list',
            backButton: '.js-back-button'

        }
    }

    bindEvents() {

        this.$wrapper.on(
            'keyup',
            WorkflowActionType._selectors.search,
            this.handleKeyupEvent.bind(this)
        );

        this.$wrapper.on(
            'click',
            WorkflowActionType._selectors.actionListItem,
            this.handleActionListItemClicked.bind(this)
        );
    }

    /**
     * Because this component can keep getting run each time a filter is added
     * you need to remove the handlers otherwise they will keep stacking up
     */
    unbindEvents() {
        this.$wrapper.off('keyup', WorkflowActionType._selectors.search);
        this.$wrapper.off('click', WorkflowActionType._selectors.actionListItem);
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

    loadActionTypes() {
        this.makeRequestForActionTypes().then(data => {
            debugger;
            this.actionTypes = data.data;
            debugger;
            this.renderActionTypes(this.actionTypes);
        });
    }

    render() {
        this.$wrapper.html(WorkflowActionType.markup(this));
    }

    handleActionListItemClicked(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        const $listItem = $(e.currentTarget);

        if($listItem.hasClass('c-private-card__item--active')) {
            return;
        }

        let actionName = $listItem.attr('data-action-name');


        let action = this.actionTypes.filter(action => {
            return action.name === actionName;
        });

        debugger;
        this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_ACTION_LIST_ITEM_CLICKED, action[0]);
    }

    renderActionTypes(actionTypes) {

        let $propertyList = this.$wrapper.find(WorkflowActionType._selectors.actionList);
        $propertyList.html("");

        let $actionList = this.$wrapper.find(WorkflowActionType._selectors.actionList);
        const html = listTemplate();
        const $list = $($.parseHTML(html));
        $actionList.append($list);

        var options = {
            valueNames: [ 'description' ],
            // Since there are no elements in the list, this will be used as template.
            item: `<div class="js-action-list-item c-private-card__item"><span class="description"></span></div>`
        };

        this.list = new List(`list-actions`, options, actionTypes);

        $( `#list-actions .js-action-list-item` ).each((index, element) => {
            $(element).attr('data-action-name', actionTypes[index].name);
        });
    }

    makeRequestForActionTypes() {
        return new Promise((resolve, reject) => {
            debugger;
            const url = Routing.generate('get_action_types', {internalIdentifier: this.portalInternalIdentifier});

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
            <br>
            <h2>Add workflow action</h2>
            <br>
            <div class="input-group c-search-control">
              <input class="form-control c-search-control__input js-search" type="search" placeholder="Search...">
              <span class="c-search-control__foreground"><i class="fa fa-search"></i></span>
            </div>
            <br>
            <div class="js-action-list c-report-widget__property-list"></div>
        `;
    }

}

const listTemplate = () => `
    <div id="list-actions">
      <h3>Select action type</h3>
      <div class="js-list list c-private-card"></div>
    </div>
`;

export default WorkflowActionType;