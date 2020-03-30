'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';
import List from 'list.js';
import ColumnSearch from "./ColumnSearch";
require('jquery-ui-dist/jquery-ui');
import AjaxLoader from '../AjaxLoader';
import FormCollectionPrototypeUpdater from "../FormCollectionPrototypeUpdater";

class RecordImportForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.unbindEvents()
            .bindEvents();
        this.loadForm();
    }

    unbindEvents() {
        this.$wrapper.off('submit', RecordImportForm._selectors.form);
        this.$wrapper.off('change', RecordImportForm._selectors.importFileField);
        return this;
    }

    bindEvents() {
        this.$wrapper.on(
            'submit',
            RecordImportForm._selectors.form,
            this.handleFormSubmit.bind(this)
        );
        this.$wrapper.on(
            'change',
            RecordImportForm._selectors.importFileField,
            this.handleFileFieldChange.bind(this)
        );

        this.$wrapper.on(
            'click',
            RecordImportForm._selectors.addItem,
            this.handleAddItemButtonClick.bind(this)
        );

        this.$wrapper.on(
            'click',
            RecordImportForm._selectors.removeItem,
            this.handleRemoveItemButtonClick.bind(this)
        );

        return this;
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            importFileField: '.js-import-file-field',
            form: '.js-record-import-form',
            customFileLabel: '.custom-file-label',
            addItem: '.js-addItem',
            removeItem: '.js-removeItem'
        }
    }

    loadForm() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: Routing.generate('import_mapping', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName}),
            }).then(data => {
                this.$wrapper.html(data.formMarkup);
                resolve(data);
            }).catch(errorData => {
                reject(errorData);
            });
        });
    }

    handleAddItemButtonClick(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        let $parentContainer = $('.js-parent-container');
        let index = $parentContainer.children('.js-child-item').length;
        let template = $parentContainer.data('template');
        let tpl = eval('`'+template+'`');
        let $container = $('<li>').addClass('list-group-item js-child-item');
        $container.append(tpl);
        $parentContainer.append($container);
    }

    handleRemoveItemButtonClick(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        let $item = $(e.currentTarget).parents('.js-child-item');
        let $container = $item.closest('.js-parent-container');
        let fieldPrefix = FormCollectionPrototypeUpdater.getFieldPrefix($item);
        let index = $item.index();
        $item.remove();
        $container.children().slice(index).each(this.updateListElementGenerator(index, fieldPrefix));
    }

    updateListElementGenerator(offset, fieldPrefix) {
        return function(index, el) {
            debugger;
            FormCollectionPrototypeUpdater.updateAttributes($(el), fieldPrefix, offset + index + 1)
        }
    }

    /**
     * @param e
     */
    handleFormSubmit(e) {
        AjaxLoader.start(this.$wrapper);
        if(e.cancelable) {
            e.preventDefault();
        }
        const $form = $(e.currentTarget);
        let formData = new FormData($form.get(0));
        this._import(formData)
            .then((data) => {
                debugger;
                AjaxLoader.kill(this.$wrapper);
                swal("Whoop whoop!", `Spreadsheet successfully queued for import!`, "success");
            }).catch((errorData) => {
                debugger;
            AjaxLoader.kill(this.$wrapper);
            $('.js-import-file-generic-errors').replaceWith(
                $(errorData.formMarkup).find('.js-import-file-generic-errors')
            );
        });
    }

    handleFileFieldChange(e) {
        AjaxLoader.start(this.$wrapper);
        if (e.cancelable) {
            e.preventDefault();
        }
        /* let formData = new FormData();
         formData.append($(e.target).attr('name'), e.target.files[0]);*/
        const $form = $(RecordImportForm._selectors.form);
        let formData = new FormData($form.get(0));
        if (e.cancelable) {
            e.preventDefault();
        }
        this._importForm(formData)
            .then((data) => {
                // go ahead and remove the error message if there was one
                $('.js-import-file-error').replaceWith(
                    $(data.formMarkup).find('.js-import-file-error')
                );
                // go ahead and display all the columns
                $('.js-column-mapper').replaceWith(
                    $(data.formMarkup).find('.js-column-mapper')
                );
                if (this.$wrapper.find('.js-import-file-field').hasClass('is-invalid')) {
                    this.$wrapper.find('.js-import-file-field').removeClass('is-invalid');
                }
                let files = e.target.files; // FileList object
                $(e.target).parent().find(RecordImportForm._selectors.customFileLabel).html(files[0].name);
                AjaxLoader.kill(this.$wrapper);
            }).catch((errorData) => {
            $('.js-import-file').replaceWith(
                $(errorData.formMarkup).find('.js-import-file')
            );
            let files = e.target.files; // FileList object
            $(e.target).parent().find(RecordImportForm._selectors.customFileLabel).html(files[0].name);
            AjaxLoader.kill(this.$wrapper);
        });
    }


    /**
     * @param data
     * @return {Promise<any>}
     * @private
     */
    _importForm(data) {
        return new Promise( (resolve, reject) => {
            const url = Routing.generate('import_mapping', {'internalIdentifier' : this.portalInternalIdentifier, 'internalName' : this.customObjectInternalName});
            $.ajax({
                url,
                method: 'POST',
                data: data,
                processData: false,
                contentType: false
            }).then((data, textStatus, jqXHR) => {
                resolve(data);
            }).catch((jqXHR) => {
                const errorData = JSON.parse(jqXHR.responseText);
                errorData.httpCode = jqXHR.status;
                reject(errorData);
            });
        });
    }

    /**
     * @param data
     * @return {Promise<any>}
     * @private
     */
    _import(data) {
        return new Promise( (resolve, reject) => {
            const url = Routing.generate('import', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});
            $.ajax({
                url,
                method: 'POST',
                data: data,
                processData: false,
                contentType: false
            }).then((data, textStatus, jqXHR) => {
                resolve(data);
            }).catch((jqXHR) => {
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }
}

export default RecordImportForm;