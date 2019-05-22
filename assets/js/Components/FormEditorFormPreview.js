'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import ColumnSearch from "./ColumnSearch";
import ContextHelper from "../ContextHelper";
require('jquery-ui-dist/jquery-ui');

class FormEditorFormPreview {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, form) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.form = form;

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
            Settings.Events.FORM_EDITOR_DATA_SAVED,
            this.render.bind(this)
        ));

        this.unbindEvents();
        this.bindEvents();

        this.render(this.form);
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

    render(form) {

        if(_.isEmpty(form.draft)) {
            this.$wrapper.html(emptyListTemplate());
            return;
        }

        this.form = form;

        this.loadFormPreview(form).then(() => {
            this.activatePlugins();
        });
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
}

/**
 * @return {string}
 */
const emptyListTemplate = () => `
    <h1 style="text-align: center; margin-top: 300px">Select a field on the left to get started...</h1>
`;

export default FormEditorFormPreview;