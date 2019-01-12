'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';
import FormHelper from '../FormHelper';

class RecordForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param customObject
     * @param customObjectLabel
     * @param portal
     */
    constructor($wrapper, globalEventDispatcher, customObject, customObjectLabel, portal) {

        this.$wrapper = $wrapper;
        this.customObjectLabel = customObjectLabel;
        this.customObject = customObject;
        this.portal = portal;

        /**
         * @type {EventDispatcher}
         */
        this.globalEventDispatcher = globalEventDispatcher;

        this.$wrapper.on(
            'submit',
            RecordForm._selectors.newCustomObjectForm,
            this.handleNewFormSubmit.bind(this)
        );

        this.loadForm().then(()=> {this.activatePlugins();});

        this.activatePlugins();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            newCustomObjectForm: '.js-new-record-form',
        }
    }

    activatePlugins() {
        $('.js-selectize-multiple-select').selectize({
            plugins: ['remove_button'],
            sortField: 'text'
        });

        $('.js-selectize-single-select').selectize({
            sortField: 'text'
        });

        $('.js-datepicker').datepicker({
            format: 'yyyy-mm-dd'
        });
    }

    loadForm() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: Routing.generate('create_record_form', {internalIdentifier: this.portal}),
                data: {custom_object_id: this.customObject}
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
    handleNewFormSubmit(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        const $form = $(e.currentTarget);
        let formData = new FormData($form.get(0));
        formData.append('custom_object_id', this.customObject);

        this._saveRecord(formData)
            .then((data) => {
                debugger;
                swal("Hooray!", `Well done, you created a shiny brand new ${this.customObjectLabel}!`, "success");
                this.globalEventDispatcher.publish(Settings.Events.RECORD_CREATED);
            }).catch((errorData) => {

                debugger;
            this.$wrapper.html(errorData.formMarkup);
            this.activatePlugins();

            // Use for when the form is being generated on the JS side
            /*this._mapErrorsToForm(errorData.errors);*/
        });
    }

    /**
     * @param data
     * @return {Promise<any>}
     * @private
     */
    _saveRecord(data) {
        debugger;
        return new Promise( (resolve, reject) => {
            debugger;
            const url = Routing.generate('create_record', {internalIdentifier: this.portal});

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

export default RecordForm;