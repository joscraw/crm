'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';

class DeletePropertyForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     * @param propertyInternalName
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, propertyInternalName) {

        debugger;
        this.$wrapper = $wrapper;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.propertyInternalName = propertyInternalName;

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
            url: Routing.generate('delete_property_form', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName, propertyInternalName: this.propertyInternalName}),
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

        this._deleteProperty(formData)
            .then((data) => {
                swal("Hooray!", "Sweet! You deleted your property!", "success");
                this.globalEventDispatcher.publish(Settings.Events.PROPERTY_DELETED);
            }).catch((errorData) => {

            if(errorData.httpCode === 401) {
                swal("Woah!", `You don't have proper permissions for this!`, "error");
                return;
            }

            this.$wrapper.html(errorData.formMarkup);
        });
    }

    /**
     * @param data
     * @return {Promise<any>}
     * @private
     */
    _deleteProperty(data) {
        return new Promise( (resolve, reject) => {

            const url = Routing.generate('delete_property', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName, propertyInternalName: this.propertyInternalName});

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

export default DeletePropertyForm;