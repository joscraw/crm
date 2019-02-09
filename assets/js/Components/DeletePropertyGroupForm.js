'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';

class DeletePropertyGroupForm {

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
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.propertyGroupInternalName= propertyGroupInternalName;

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
            url: Routing.generate('delete_property_group_form', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName, propertyGroupInternalName: this.propertyGroupInternalName}),
        }).then(data => {
            this.$wrapper.html(data.formMarkup);
        })
    }

    /**
     * @param e
     */
    handleDeleteFormSubmit(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        const $form = $(e.currentTarget);
        let formData = new FormData($form.get(0));

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

            debugger;
            const url = Routing.generate('delete_property_group', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName, propertyGroupInternalName: this.propertyGroupInternalName});

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