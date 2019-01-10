'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';

class RecordForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param customObject
     * @param portal
     */
    constructor($wrapper, globalEventDispatcher, customObject, portal) {

        this.$wrapper = $wrapper;
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

        this.loadForm();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            newCustomObjectForm: '.js-new-record-form',
        }
    }

    loadForm() {
        $.ajax({
            url: Routing.generate('create_record_form', {internalIdentifier: this.portal}),
            data: {custom_object_id: this.customObject}
        }).then(data => {
            this.$wrapper.html(data.formMarkup);
        })
    }

    /**
     * @param e
     */
    handleNewFormSubmit(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        const $form = $(e.currentTarget);
        const formData = {};

        for (let fieldData of $form.serializeArray()) {
            formData[fieldData.name] = fieldData.value
        }

        this._saveCustomObject(formData)
            .then((data) => {
                swal("Hooray!", "Well done, you created a custom object!", "success");
                this.globalEventDispatcher.publish(Settings.Events.CUSTOM_OBJECT_CREATED);
            }).catch((errorData) => {

            this.$wrapper.html(errorData.formMarkup);

            // Use for when the form is being generated on the JS side
            /*this._mapErrorsToForm(errorData.errors);*/
        });
    }

    /**
     * @param data
     * @return {Promise<any>}
     * @private
     */
    _saveCustomObject(data) {
        return new Promise( (resolve, reject) => {
            const url = Routing.generate('create_custom_object', {internalIdentifier: this.portal});

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
}

export default RecordForm;