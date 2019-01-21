'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';

class DeletePropertyGroupForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portal
     * @param propertyGroupId
     */
    constructor($wrapper, globalEventDispatcher, portal, propertyGroupId, customObject) {

        debugger;
        this.$wrapper = $wrapper;
        this.portal = portal;
        this.propertyGroupId = propertyGroupId;
        this.customObject = customObject;

        /**
         * @type {EventDispatcher}
         */
        this.globalEventDispatcher = globalEventDispatcher;

        this.$wrapper.on(
            'submit',
            DeletePropertyGroupForm._selectors.deletePropertyGroupForm,
            this.handleDeleteFormSubmit.bind(this)
        );

        this.loadDeletePropertyGroupForm();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            deletePropertyGroupForm: '.js-delete-property-group-form',
        }
    }

    loadDeletePropertyGroupForm() {
        debugger;
        $.ajax({
            url: Routing.generate('delete_property_group_form', {internalIdentifier: this.portal, propertyGroup: this.propertyGroupId}),
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
        formData.append('custom_object_id', this.customObject);

        this._deletePropertyGroup(formData)
            .then((data) => {
                swal("Hooray!", "Sweet! You deleted your property group!", "success");
                this.globalEventDispatcher.publish(Settings.Events.PROPERTY_GROUP_DELETED);
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
    _deletePropertyGroup(data) {
        return new Promise( (resolve, reject) => {

            const url = Routing.generate('delete_property_group', {internalIdentifier: this.portal, propertyGroup: this.propertyGroupId});

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

export default DeletePropertyGroupForm;