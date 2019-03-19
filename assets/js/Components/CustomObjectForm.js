'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';

class CustomObjectForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portal
     */
    constructor($wrapper, globalEventDispatcher, portal) {

        this.$wrapper = $wrapper;
        this.portal = portal;

        /**
         * @type {EventDispatcher}
         */
        this.globalEventDispatcher = globalEventDispatcher;

        this.$wrapper.on(
            'submit',
            CustomObjectForm._selectors.newCustomObjectForm,
            this.handleNewFormSubmit.bind(this)
        );

        this.loadCustomObjectForm();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            newCustomObjectForm: '.js-new-custom-object-form',
        }
    }

    loadCustomObjectForm() {
        $.ajax({
            url: Routing.generate('custom_object_form', {internalIdentifier: this.portal}),
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

            if(errorData.httpCode === 401) {
                swal("Woah!", `You don't have proper permissions for this!`, "error");
                return;
            }

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
                errorData.httpCode = jqXHR.status;
                reject(errorData);
            });
        });
    }
}

export default CustomObjectForm;