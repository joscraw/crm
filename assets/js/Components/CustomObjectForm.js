'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';

class CustomObjectForm {
    constructor($wrapper) {
        this.$wrapper = $wrapper;
        debugger;

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
            url: Routing.generate('app_get_custom_object_form'),
        }).then(data => {
            debugger;
            this.$wrapper.html(data.formMarkup);
        })
    }

    handleNewFormSubmit(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        const $form = $(e.currentTarget);
        const formData = {};

        for (let fieldData of $form.serializeArray()) {
            formData[fieldData.name] = fieldData.value
        }


        /*
        Swal({
  type: 'error',
  title: 'Oops...',
  text: 'Something went wrong!',
  footer: '<a href>Why do I have this issue?</a>'
})

         */

        this._saveCustomObject(formData)
            .then((data) => {
                swal("Success Message Title", "Well done, you created a custom object!", "success");
            }).catch((errorData) => {
                debugger;
            this.$wrapper.html(errorData.formMarkup);
            /*this._mapErrorsToForm(errorData.errors);*/
        });
    }

    _saveCustomObject(data) {
        return new Promise( (resolve, reject) => {
            const url = Routing.generate('custom_object_new', {portal: 1});

            $.ajax({
                url,
                method: 'POST',
                data: data
            }).then((data, textStatus, jqXHR) => {
                resolve(data);
            }).catch((jqXHR) => {
                debugger;
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }
}

export default CustomObjectForm;