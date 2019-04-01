'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';

class DeleteCustomObjectForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portal
     * @param customObjectId
     */
    constructor($wrapper, globalEventDispatcher, portal, customObjectId) {

        debugger;
        this.$wrapper = $wrapper;
        this.portal = portal;
        this.customObjectId = customObjectId;

        /**
         * @type {EventDispatcher}
         */
        this.globalEventDispatcher = globalEventDispatcher;

        this.$wrapper.on(
            'submit',
            DeleteCustomObjectForm._selectors.deleteCustomObjectForm,
            this.handleDeleteFormSubmit.bind(this)
        );
        this.loadDeleteCustomObjectForm();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            deleteCustomObjectForm: '.js-delete-custom-object-form',
        }
    }

    loadDeleteCustomObjectForm() {
        debugger;
        $.ajax({
            url: Routing.generate('delete_custom_object_form', {internalIdentifier: this.portal, customObject: this.customObjectId}),
        }).then(data => {
            this.$wrapper.html(data.formMarkup);
        })
    }

    /**
     * @param e
     */
    handleDeleteFormSubmit(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        debugger;
        const $form = $(e.currentTarget);
        let formData = new FormData($form.get(0));
        formData.append('custom_object_id', this.customObject);

        this._deleteCustomObject(formData)
            .then((data) => {
                swal("Hooray!", "Sweet! You deleted your custom object!", "success");
                this.globalEventDispatcher.publish(Settings.Events.CUSTOM_OBJECT_DELETED);
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
    _deleteCustomObject(data) {
        return new Promise( (resolve, reject) => {

            const url = Routing.generate('delete_custom_object', {internalIdentifier: this.portal, customObject: this.customObjectId});

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

export default DeleteCustomObjectForm;