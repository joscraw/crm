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
        this.lists = [];

        this.unbindEvents();

        this.bindEvents();

       /* this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
            Settings.Events.FORM_EDITOR_DATA_SAVED,
            this.handleDataSaved.bind(this)
        ));*/

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

        this.$wrapper.off('keyup', WorkflowTriggerType._selectors.search);
        this.$wrapper.off('click', WorkflowTriggerType._selectors.propertyListItem);

        this.$wrapper.off('click', WorkflowTriggerType._selectors.triggerListItem);
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

        for(let i = 0; i < this.lists.length; i++) {
            this.lists[i].search(searchValue);
        }

        this.$wrapper.find(WorkflowTriggerType._selectors.list).each((index, element) => {

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

        debugger;
        e.stopPropagation();

        this.globalEventDispatcher.publish(Settings.Events.LIST_BACK_BUTTON_CLICKED);

    }

    loadTriggerTypes() {

        this.makeRequestForTriggerTypes().then(data => {
            debugger;
            this.triggerTypes = data.data;
            this.renderTriggerTypes(this.triggerTypes).then(() => {
                /*this.highlightProperties(this.form.draft);*/
            })
        });

    }

    handleDataSaved(form) {

        this.form = form;

        this.highlightProperties(form.draft);
    }

    highlightProperties(data) {

        $(FormEditorPropertyList._selectors.propertyListItem).each((index, element) => {

            if($(element).hasClass('c-private-card__item--active')) {
                $(element).removeClass('c-private-card__item--active');
            }

            let propertyId = $(element).attr('data-property-id');

            let property = data.filter(property => {
                return parseInt(property.id) === parseInt(propertyId);
            });

            if(property[0]) {
                $(element).addClass('c-private-card__item--active');
            }
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

        debugger;
        let $propertyList = this.$wrapper.find(WorkflowTriggerType._selectors.triggerList);
        $propertyList.html("");


        return new Promise((resolve, reject) => {

            this._addList(triggerTypes);

            /*for(let i = 0; i < triggerTypes.length; i++) {
                let triggerType = triggerTypes[i];
                this._addList(triggerType);

            }*/
            resolve();
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


    /**
     * @private
     * @param triggerTypes
     */
    _addList(triggerTypes) {

        debugger;
        let $triggerList = this.$wrapper.find(WorkflowTriggerType._selectors.triggerList);
        const html = listTemplate();
        const $list = $($.parseHTML(html));
        $triggerList.append($list);

        var options = {
            valueNames: [ 'description' ],
            // Since there are no elements in the list, this will be used as template.
            item: `<div class="js-trigger-list-item c-private-card__item"><span class="description"></span></div>`
        };

        this.lists.push(new List(`list-triggers`, options, triggerTypes));

        $( `#list-triggers .js-trigger-list-item` ).each((index, element) => {
            $(element).attr('data-trigger-name', triggerTypes[index].name);
        });
    }

    static markup() {

        debugger;
        return `
            <br>
            <h2>Add workflow trigger</h2>
            <br>
            <div class="input-group c-search-control">
              <input class="form-control c-search-control__input js-search" type="search" placeholder="Search...">
              <span class="c-search-control__foreground"><i class="fa fa-search"></i></span>
            </div>
            <br>
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