'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';

class PropertyCreateForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     */
    constructor($wrapper, globalEventDispatcher) {

        this.$wrapper = $wrapper;

        /**
         * @type {EventDispatcher}
         */
        this.globalEventDispatcher = globalEventDispatcher;

  /*      this.$wrapper.on(
            'submit',
            PropertyGroupForm._selectors.newPropertyGroupForm,
            this.handleNewFormSubmit.bind(this)
        );*/

        this.loadCreatePropertyForm();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            newPropertyGroupForm: '.js-new-property-group-form',
        }
    }

    loadCreatePropertyForm() {
        $.ajax({
            url: Routing.generate('create_property_form', {portal: 1}),
        }).then(data => {
            this.$wrapper.html(data.formMarkup);
        })
    }

    /**
     * @param e
     */
    handleNewFormSubmit(e) {

/*        console.log("form submitted");

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

            // Use for when the form is being generated on the JS side
            /!*this._mapErrorsToForm(errorData.errors);*!/
        });*/
    }

    /**
     * @param data
     * @return {Promise<any>}
     * @private
     */
    _savePropertyGroup(data) {
  /*      return new Promise( (resolve, reject) => {
            const url = Routing.generate('property_group_new', {portal: 1});

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
        });*/
    }
}

export default PropertyCreateForm;