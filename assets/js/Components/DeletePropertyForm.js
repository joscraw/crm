'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';

class DeletePropertyForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portal
     * @param customObjectId
     * @param propertyId
     */
    constructor($wrapper, globalEventDispatcher, portal, customObjectId, propertyId) {

        debugger;
        this.$wrapper = $wrapper;
        this.portal = portal;
        this.customObjectId = customObjectId;
        this.propertyId = propertyId;

        /**
         * @type {EventDispatcher}
         */
        this.globalEventDispatcher = globalEventDispatcher;

        this.$wrapper.on(
            'submit',
            DeletePropertyForm._selectors.deletePropertyForm,
            this.handleDeleteFormSubmit.bind(this)
        );
        this.loadDeletePropertyForm();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            deletePropertyForm: '.js-delete-property-form',
        }
    }

    loadDeletePropertyForm() {
        debugger;
        $.ajax({
            url: Routing.generate('delete_property_form', {internalIdentifier: this.portal, property: this.propertyId}),
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

        const $form = $(e.currentTarget);
        let formData = new FormData($form.get(0));
        formData.append('custom_object_id', this.customObjectId);

        this._deleteProperty(formData)
            .then((data) => {
                swal("Hooray!", "Sweet! You deleted your property!", "success");
                this.globalEventDispatcher.publish(Settings.Events.PROPERTY_DELETED);
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
    _deleteProperty(data) {
        return new Promise( (resolve, reject) => {

            const url = Routing.generate('delete_property', {internalIdentifier: this.portal, property: this.propertyId});

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

export default DeletePropertyForm;