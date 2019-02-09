'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';

class PropertyGroupForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;

        this.$wrapper.on(
            'submit',
            PropertyGroupForm._selectors.newPropertyGroupForm,
            this.handleNewFormSubmit.bind(this)
        );

        this.loadPropertyGroupForm();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            newPropertyGroupForm: '.js-new-property-group-form',
        }
    }

    loadPropertyGroupForm() {
        const url = Routing.generate('property_group_form', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});
        $.ajax({
            url: url,
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

        this._savePropertyGroup(formData)
            .then((data) => {
                swal("Hooray!", "Well done, you created a new Property Group!", "success");
                this.globalEventDispatcher.publish(Settings.Events.PROPERTY_GROUP_CREATED);
            }).catch((errorData) => {

            this.$wrapper.html(errorData.formMarkup);
        });
    }

    /**
     * @param data
     * @return {Promise<any>}
     * @private
     */
    _savePropertyGroup(data) {
        return new Promise( (resolve, reject) => {
            console.log(this.portalId);
            const url = Routing.generate('property_group_new', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});

            $.ajax({
                url,
                method: 'POST',
                data: data
            }).then((data, textStatus, jqXHR) => {
                debugger;
                resolve(data);
            }).catch((jqXHR) => {
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }
}

export default PropertyGroupForm;