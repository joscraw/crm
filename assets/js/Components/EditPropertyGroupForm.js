'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';

class EditPropertyGroupForm {

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
            EditPropertyGroupForm._selectors.editPropertyGroupForm,
            this.handleEditFormSubmit.bind(this)
        );
        this.loadEditPropertyGroupForm();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            editPropertyGroupForm: '.js-edit-property-group-form',
        }
    }

    loadEditPropertyGroupForm() {
        debugger;
        $.ajax({
            url: Routing.generate('edit_property_group_form', {internalIdentifier: this.portal, propertyGroup: this.propertyGroupId}),
        }).then(data => {
            this.$wrapper.html(data.formMarkup);
        })
    }

    /**
     * @param e
     */
    handleEditFormSubmit(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        debugger;
        const $form = $(e.currentTarget);
        let formData = new FormData($form.get(0));
        formData.append('custom_object_id', this.customObject);

        this._savePropertyGroupObject(formData)
            .then((data) => {
                swal("Hooray!", "Sweet! You edited your property group!", "success");
                this.globalEventDispatcher.publish(Settings.Events.PROPERTY_GROUP_EDITED);
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
    _savePropertyGroupObject(data) {
        return new Promise( (resolve, reject) => {

            const url = Routing.generate('edit_property_group', {internalIdentifier: this.portal, propertyGroup: this.propertyGroupId});

            data.custom_object_id = this.customObject;

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

export default EditPropertyGroupForm;