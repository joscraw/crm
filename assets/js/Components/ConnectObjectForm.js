'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';
import FormCollectionPrototypeUpdater from '../FormCollectionPrototypeUpdater';

require('jquery-ui-dist/jquery-ui');
require('jquery-ui-dist/jquery-ui.css');
require('bootstrap-datepicker');
require('bootstrap-datepicker/dist/css/bootstrap-datepicker.css');

class ConnectObjectForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;

        this.$wrapper.on(
            'submit',
            ConnectObjectForm._selectors.bulkEditForm,
            this.handleFormSubmit.bind(this)
        );

        this.loadBulkEditForm().then(() => {

            $('.js-selectize-single-select-connectable-object').selectize({
                sortField: 'text'
            }).on('change', this.handleConnectableObjectChange.bind(this));

            $('.js-selectize-single-select').selectize({
                sortField: 'text'
            });

            $('.js-selectize-single-select-property').selectize({
                sortField: 'text'
            });
        });
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            bulkEditForm: '.js-bulk-edit-form',
            fieldType: '.js-field-type',
            property: 'js-property'
        }
    }

    handleConnectableObjectChange(e) {
        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }
        const formData = {};
        formData[$(e.target).attr('name')] = $(e.target).val();
        this._changeProperty(formData).then((data) => {}).catch((errorData) => {

            $('.js-property-value-container').replaceWith(
                // ... with the returned one from the AJAX response.
                $(errorData.formMarkup).find('.js-property-value-container')
            );

            $('.js-selectize-single-select-property').selectize({
                sortField: 'text'
            });
        });
    }

    _changeProperty(data) {
        return new Promise((resolve, reject) => {

            const url = Routing.generate('connect_object_form', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});

            $.ajax({
                url,
                method: 'POST',
                data: data
            }).then((data, textStatus, jqXHR) => {
                resolve(data);
            }).catch((jqXHR) => {
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }

    loadBulkEditForm() {

        debugger;
        let url = Routing.generate('connect_object_form', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});

        return new Promise((resolve, reject) => {
            $.ajax({
                url: Routing.generate('connect_object_form', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName}),
            }).then(data => {
                this.$wrapper.html(data.formMarkup);
                resolve(data);
            }).catch(errorData => {
                reject(errorData);
            });
        });
    }

    /**
     * @param e
     */
    handleFormSubmit(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        const $form = $(e.currentTarget);
        let formData = new FormData($form.get(0));

        for (let i = 0; i < this.records.length; i++) {
            formData.append('records[]', this.records[i]);
        }

        this._updateProperty(formData)
            .then((data) => {
                swal("Hooray!", "Hooray!, record(s) successfully updated!", "success");
                this.globalEventDispatcher.publish(Settings.Events.BULK_EDIT_SUCCESSFUL);
            }).catch((errorData) => {

            if(errorData.httpCode === 401) {
                swal("Woah!", `You don't have proper permissions for this!`, "error");
                return;
            }

            this.$wrapper.html(errorData.formMarkup);
            this.activatePlugins();
        });
    }

    /**
     * @param data
     * @return {Promise<any>}
     * @private
     */
    _updateProperty(data) {
        return new Promise( (resolve, reject) => {
            const url = Routing.generate('bulk_edit', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});

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
}

export default ConnectObjectForm;