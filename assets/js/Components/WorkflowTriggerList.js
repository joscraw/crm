'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import ColumnSearch from "./ColumnSearch";
import ContextHelper from "../ContextHelper";
import FilterHelper from "../FilterHelper";
require('jquery-ui-dist/jquery-ui');

class WorkflowTriggerList {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, uid) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.uid = uid;
        this.workflowTriggers = [];

    /*    this.form = form;

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
            Settings.Events.FORM_EDITOR_DATA_SAVED,
            this.render.bind(this)
        ));

        this.unbindEvents();
        this.bindEvents();
*/


        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
                Settings.Events.WORKFLOW_TRIGGER_SAVED,
                this.workflowTriggersSaved.bind(this)
            ));

        debugger;

        this.$wrapper.html(WorkflowTriggerList.markup(this));

        this.loadWorkflowTriggers().then(data => {
           this.workflowTriggers = data.data;
           this.render();
        });

    }



    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            formField: '.js-form-field',
            sharedButtonMarkup: '.js-shared-button-markup',
            deleteButton: '.js-delete-button',
            editButton: '.js-edit-button',
            formFieldsContainer: '.js-form-fields-container',
            arrows: '.js-arrows'
        }
    }

    workflowTriggersSaved() {
        this.loadWorkflowTriggers().then(data => {
            this.workflowTriggers = data.data;
            this.render();
        });
    }

    bindEvents() {
        this.$wrapper.on('mouseover', FormEditorFormPreview._selectors.formField, this.handleFormFieldMouseOver.bind(this));
        this.$wrapper.on('mouseout', FormEditorFormPreview._selectors.formField, this.handleFormFieldMouseOut.bind(this));
        this.$wrapper.on('click', FormEditorFormPreview._selectors.deleteButton, this.handleDeleteButtonClicked.bind(this));
        this.$wrapper.on('click', FormEditorFormPreview._selectors.editButton, this.handleEditButtonClicked.bind(this));
    }

    unbindEvents() {
        this.$wrapper.off('mouseover', FormEditorFormPreview._selectors.formField);
        this.$wrapper.off('mouseout', FormEditorFormPreview._selectors.formField);
        this.$wrapper.off('click', FormEditorFormPreview._selectors.deleteButton);
        this.$wrapper.off('click', FormEditorFormPreview._selectors.editButton);
    }

    render() {

        debugger;
        this.$wrapper.find('.js-workflow-trigger-list').html("");
        if(_.isEmpty(this.workflowTriggers)) {
            this.$wrapper.html(emptyListTemplate());
            return;
        }

        for(let trigger of this.workflowTriggers) {
            debugger;
            let markup = cardTemplate(trigger);
            this.$wrapper.find('.js-workflow-trigger-list').append(markup);
        }

    }

    handleFormFieldMouseOver(e) {
        let $field = $(e.target);
        let $parent = $field.closest('.js-form-field');

        if($parent.find(FormEditorFormPreview._selectors.sharedButtonMarkup).hasClass('d-none')) {
            $parent.find(FormEditorFormPreview._selectors.sharedButtonMarkup).removeClass('d-none');
        }

        if($parent.find(FormEditorFormPreview._selectors.arrows).hasClass('d-none')) {
            $parent.find(FormEditorFormPreview._selectors.arrows).removeClass('d-none');
        }
    }

    handleFormFieldMouseOut(e) {
        let $field = $(e.target);
        let $parent = $field.closest('.js-form-field');

        if(!$parent.find(FormEditorFormPreview._selectors.sharedButtonMarkup).hasClass('d-none')) {
            $parent.find(FormEditorFormPreview._selectors.sharedButtonMarkup).addClass('d-none');
        }

        if(!$parent.find(FormEditorFormPreview._selectors.arrows).hasClass('d-none')) {
            $parent.find(FormEditorFormPreview._selectors.arrows).addClass('d-none');
        }
    }

    handleDeleteButtonClicked(e) {
        let $button = $(e.target);
        let uid = $button.attr('data-property-uid');

        this.globalEventDispatcher.publish(Settings.Events.FORM_PREVIEW_DELETE_BUTTON_CLICKED, uid);
    }

    handleEditButtonClicked(e) {

        let $button = $(e.target);
        let uid = $button.attr('data-property-uid');

        this.globalEventDispatcher.publish(Settings.Events.FORM_PREVIEW_EDIT_BUTTON_CLICKED, uid);
    }

    handleDataSaved(data) {

        this.data = data;

        this.loadFormPreview(data).then(() => {
           this.activatePlugins();
        });
    }

    loadFormPreview(form) {

        return new Promise((resolve, reject) => {
            $.ajax({
                url: Routing.generate('form_preview', {internalIdentifier: this.portalInternalIdentifier, uid: form.uid}),
                method: 'POST',
                data: {'form': form}
            }).then(data => {
                this.$wrapper.html(data.formMarkup);
                resolve(data);
            }).catch(errorData => {
                reject(errorData);
            });
        });
    }

    loadWorkflowTriggers() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: Routing.generate('get_workflow_triggers', {internalIdentifier: this.portalInternalIdentifier, uid: this.uid}),
            }).then(data => {
                resolve(data);
            })
        });
    }

    activatePlugins() {

        this.$wrapper.find(FormEditorFormPreview._selectors.formFieldsContainer).sortable({
            placeholder: "ui-state-highlight",
            cursor: 'crosshair',
            update: (event, ui) => {
                debugger;
                let fieldOrder = $(event.target).sortable('toArray');

                this.globalEventDispatcher.publish(Settings.Events.FORM_EDITOR_FIELD_ORDER_CHANGED, fieldOrder);

            }
        });

        $('.js-selectize-multiple-select').selectize({
            plugins: ['remove_button'],
            sortField: 'text'
        });

        $('.js-selectize-single-select').selectize({
            sortField: 'text'
        });

        const url = Routing.generate('records_for_selectize', {internalIdentifier: this.portalInternalIdentifier, internalName: this.form.customObject.internalName});

        $('.js-selectize-single-select-with-search').each((index, element) => {

            let select = $(element).selectize({
                valueField: 'valueField',
                labelField: 'labelField',
                searchField: 'searchField',
                load: (query, callback) => {

                    if (!query.length) return callback();
                    $.ajax({
                        url: url,
                        type: 'GET',
                        dataType: 'json',
                        data: {
                            search: query,
                            allowed_custom_object_to_search: $(element).data('allowedCustomObjectToSearch'),
                            property_id: $(element).data('propertyId')
                        },
                        error: () => {
                            callback();
                        },
                        success: (res) => {
                            select.selectize()[0].selectize.clearOptions();
                            select.options = res;
                            callback(res);
                        }
                    })
                }
            });
        });
    }

    static markup() {
        return `       
        <div>
        <h1>Workflow Triggers</h1>
        <div class="js-workflow-trigger-list"></div>
        </div>   
          
    `;
    }
}


const cardTemplate = ({trigger: {property: {label}, condition: {description, value}}}) => `       
       <div class="card">
          <div class="card-body">
            ${label} ${description} ${value}
          </div>
        </div>     
          
    `;

/**
 * @return {string}
 */
const emptyListTemplate = () => `
    <h1 style="text-align: center; margin-top: 300px">Create a trigger on the left to get started...</h1>
`;

export default WorkflowTriggerList;