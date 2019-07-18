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
        this.lists = [];
        this.trigger = trigger;

        this.unbindEvents();

        this.bindEvents();
       /* this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
            Settings.Events.FORM_EDITOR_DATA_SAVED,
            this.handleDataSaved.bind(this)
        ));*/

        this.render();
        /*this.loadTriggerTypes();*/
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            search: '.js-search',
            customObjectListItem: '.js-custom-object-list-item',
            list: '.js-list',
            triggerList: '.js-trigger-list',
            backToWorkflowTriggerTypeButton: '.js-back-to-workflow-trigger-type-button'

        }
    }

    bindEvents() {

    /*    this.$wrapper.on(
            'keyup',
            WorkflowTriggerType._selectors.search,
            this.handleKeyupEvent.bind(this)
        );*/

        this.$wrapper.on(
            'click',
            WorkflowTriggerCustomObject._selectors.customObjectListItem,
            this.handleCustomObjectListItemClicked.bind(this)
        );

        this.$wrapper.on(
            'click',
            WorkflowTriggerCustomObject._selectors.backToWorkflowTriggerTypeButton,
            this.handleBackButtonClicked.bind(this)
        );

    }

    /**
     * Because this component can keep getting run each time a filter is added
     * you need to remove the handlers otherwise they will keep stacking up
     */
    unbindEvents() {

        this.$wrapper.off('click', WorkflowTriggerCustomObject._selectors.customObjectListItem);

        /*
        this.$wrapper.off('keyup', WorkflowTriggerType._selectors.search);
        this.$wrapper.off('click', WorkflowTriggerType._selectors.propertyListItem);
        */

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

        e.stopPropagation();

        this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_TRIGGER_BACK_TO_WORKFLOW_TRIGGER_TYPE_BUTTON_CLICKED);
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
        this.$wrapper.html(WorkflowTriggerCustomObject.markup(this));

        /*this.$wrapper.html(ListSelectCustomObject.markup(this));*/

        this.loadCustomObjects().then(data => {
            debugger;
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

        let $triggerList = this.$wrapper.find(WorkflowTriggerCustomObject._selectors.triggerList);
        const html = listTemplate();
        const $list = $($.parseHTML(html));
        $triggerList.append($list);


        let customObjects = this.customObjects = data.data.custom_objects;

        var options = {
            valueNames: [ 'label' ],
            // Since there are no elements in the list, this will be used as template.
            item: `<div class="js-custom-object-list-item c-private-card__item"><span class="label"></span></div>`
        };

        new List('list-custom-objects', options, customObjects);

        $( `#list-custom-objects .js-custom-object-list-item` ).each((index, element) => {
            $(element).attr('data-custom-object-id', customObjects[index].id);
        });

      /*  this.lists.push(new List(`list-triggers`, options, triggerTypes));

        $( `#list-triggers .js-trigger-list-item` ).each((index, element) => {
            $(element).attr('data-trigger-id', triggerTypes[index].id);
        });
*/
  /*      debugger;


        let options = {
            valueNames: [ 'label' ],
            // Since there are no elements in the list, this will be used as template.
            item: '<div class="form-check"><input class="form-check-input js-custom-object" type="radio" name="customObject" id="" value=""><label class="form-check-label label" for=""></label></div>'
        };

        new List('listCustomObjects', options, customObjects);

        $( `#listCustomObjects input[type="radio"]`).each((index, element) => {
            $(element).attr('data-label', customObjects[index].label);
            $(element).attr('value', customObjects[index].id);
            $(element).attr('data-custom-object-id', customObjects[index].id);
            $(element).attr('id', `customObject-${customObjects[index].id}`);
            $(element).next('label').attr('for', `customObject-${customObjects[index].id}`);
        });

        if(this.customObject) {
            debugger;
            let index = _.findIndex(customObjects, (customObject) => { return customObject.id === this.customObject.id });
            $( `#listCustomObjects input[type="radio"]`).eq(index).prop('checked', true);
        } else {
            debugger;
            $( `#listCustomObjects input[type="radio"]`).first().prop('checked', true);
        }*/
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

    renderTriggerTypes(triggerTypes) {

        debugger;
        let $propertyList = this.$wrapper.find(WorkflowTriggerType._selectors.propertyList);
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
            valueNames: [ 'label' ],
            // Since there are no elements in the list, this will be used as template.
            item: `<div class="js-trigger-list-item c-private-card__item"><span class="label"></span></div>`
        };

        this.lists.push(new List(`list-triggers`, options, triggerTypes));

        $( `#list-triggers .js-trigger-list-item` ).each((index, element) => {
            $(element).attr('data-trigger-id', triggerTypes[index].id);
        });
    }

    static markup() {

        debugger;
        return `
            <br>
            <h2>Add workflow trigger</h2>
            <div>
                <button type="button" class="btn btn-link js-back-to-workflow-trigger-type-button float-left"><i class="fa fa-chevron-left"></i> Back</button>
            </div>
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
    <div id="list-custom-objects">
      <h3>Select custom object</h3>
      <div class="js-list list c-private-card"></div>
    </div>
    
`;

export default WorkflowTriggerCustomObject;