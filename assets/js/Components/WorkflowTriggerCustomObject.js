'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import ColumnSearch from "./ColumnSearch";
import ContextHelper from "../ContextHelper";

class WorkflowTriggerCustomObject {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, uid, trigger) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.uid = uid;
        this.triggerTypes = [];
        this.lists = null;
        this.trigger = trigger;

        this.unbindEvents();

        this.bindEvents();

        this.render();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            search: '.js-search',
            customObjectListItem: '.js-custom-object-list-item',
            list: '.js-list',
            customObjectList: '.js-custom-object-list',
            backToWorkflowTriggerTypeButton: '.js-back-to-workflow-trigger-type-button',
            backButton: '.js-backButton'

        }
    }

    bindEvents() {

        this.$wrapper.on(
            'keyup',
            WorkflowTriggerCustomObject._selectors.search,
            this.handleKeyupEvent.bind(this)
        );

        this.$wrapper.on(
            'click',
            WorkflowTriggerCustomObject._selectors.customObjectListItem,
            this.handleCustomObjectListItemClicked.bind(this)
        );

        this.$wrapper.on(
            'click',
            WorkflowTriggerCustomObject._selectors.backButton,
            this.handleBackButtonClicked.bind(this)
        );
    }

    /**
     * Because this component can keep getting run each time a filter is added
     * you need to remove the handlers otherwise they will keep stacking up
     */
    unbindEvents() {
        this.$wrapper.off('click', WorkflowTriggerCustomObject._selectors.backButton,);
        this.$wrapper.off('click', WorkflowTriggerCustomObject._selectors.customObjectListItem);
        this.$wrapper.off('keyup', WorkflowTriggerCustomObject._selectors.search);
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

    handleBackButtonClicked(e) {

        debugger;
        e.stopPropagation();

        this.globalEventDispatcher.publish(
            Settings.Events.WORKFLOW_TRIGGER_BACK_BUTTON_CLICKED,
            Settings.VIEWS.WORKFLOW_TRIGGER_SELECT_TRIGGER_TYPE
        );
    }

    render() {
        this.$wrapper.html(WorkflowTriggerCustomObject.markup(this));
        this.loadCustomObjects().then(data => {
            this.renderCustomObjectForm(data);
        })
    }

    loadCustomObjects() {
        return new Promise((resolve, reject) => {
            let url = Routing.generate('' +
                'get_custom_objects', {internalIdentifier: this.portalInternalIdentifier});

            $.ajax({
                url: url,
            }).then(data => {
                resolve(data);
            }).catch(jqXHR => {
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }

    renderCustomObjectForm(data) {

        let $customObjectList = this.$wrapper.find(WorkflowTriggerCustomObject._selectors.customObjectList);
        const html = listTemplate();
        const $list = $($.parseHTML(html));
        $customObjectList.append($list);


        let customObjects = this.customObjects = data.data.custom_objects;

        var options = {
            valueNames: [ 'label' ],
            // Since there are no elements in the list, this will be used as template.
            item: `<div class="js-custom-object-list-item c-private-card__item"><span class="label"></span></div>`
        };

        this.list = new List('list-custom-objects', options, customObjects);

        $( `#list-custom-objects .js-custom-object-list-item` ).each((index, element) => {
            $(element).attr('data-custom-object-id', customObjects[index].id);
        });
    }

    handleCustomObjectListItemClicked(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        const $listItem = $(e.currentTarget);

        if($listItem.hasClass('c-private-card__item--active')) {
            return;
        }

        let customObjectId = $listItem.attr('data-custom-object-id');


        let customObject = this.customObjects.filter(customObject => {
            return parseInt(customObject.id) === parseInt(customObjectId);
        });

        this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_TRIGGER_CUSTOM_OBJECT_LIST_ITEM_CLICKED, customObject[0]);
    }

    static markup() {

        debugger;
        return `
            <br>
            <h2>Add workflow trigger</h2>
            <div>
                <button type="button" class="btn btn-link js-backButton float-left"><i class="fa fa-chevron-left"></i> Back</button>
            </div>
            <div class="input-group c-search-control">
              <input class="form-control c-search-control__input js-search" type="search" placeholder="Search...">
              <span class="c-search-control__foreground"><i class="fa fa-search"></i></span>
            </div>
            <br>
            <div class="js-custom-object-list c-report-widget__property-list"></div>
        `;
    }

}

const listTemplate = () => `
    <div id="list-custom-objects">
      <h3>Select custom object</h3>
      <div class="js-list list c-private-card"></div>
    </div>
    
`;

export default WorkflowTriggerCustomObject;