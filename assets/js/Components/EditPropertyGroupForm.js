'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';

class EditPropertyGroupForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     * @param propertyGroupInternalName
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, propertyGroupInternalName) {

        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.propertyGroupInternalName= propertyGroupInternalName;

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
        const url = Routing.generate('edit_property_group_form', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName, propertyGroupInternalName: this.propertyGroupInternalName});
        debugger;
        $.ajax({
            url: url
        }).then(data => {
            debugger;
            this.$wrapper.html(data.formMarkup);
        }).catch((errorData) => {

            debugger;
        });
    }

    /**
     * @param e
     */
    handleEditFormSubmit(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        const $form = $(e.currentTarget);
        let formData = new FormData($form.get(0));

        this._savePropertyGroupObject(formData)
            .then((data) => {
                swal("Hooray!", "Sweet! You edited your property group!", "success");
                this.globalEventDispatcher.publish(Settings.Events.PROPERTY_GROUP_EDITED);
            }).catch((errorData) => {

                debugger;
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

            const url = Routing.generate('edit_property_group', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName, propertyGroupInternalName: this.propertyGroupInternalName});

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