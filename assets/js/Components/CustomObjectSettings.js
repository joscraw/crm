'use strict';

import $ from 'jquery';
import Routing from '../Routing';

class CustomObjectSettings {
    constructor($wrapper) {
        this.$wrapper = $wrapper;
        this.customObjects = [];

        debugger;
        this.loadCustomObjects();

        this.$wrapper.on(
            'submit',
            CustomObjectSettings._selectors.newCustomObjectForm,
            this.handleNewFormSubmit.bind(this)
        );
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            newCustomObjectForm: '.js-new-custom-object-form'
        }
    }

    loadCustomObjects() {
        $.ajax({
            url: Routing.generate('app_get_custom_object_form'),
        }).then(data => {
            debugger;
            this.$wrapper.html(data.formMarkup);
        })
    }

    handleNewFormSubmit(e) {
        e.preventDefault();

        const $form = $(e.currentTarget);
        const formData = {};

        for (let fieldData of $form.serializeArray()) {
            formData[fieldData.name] = fieldData.value
        }

        debugger;

        this._saveCustomObject(formData)
            .then((data) => {
                debugger;
                /*this._clearForm();
                this._addRow(data);*/
            }).catch((errorData) => {
            /*this._mapErrorsToForm(errorData.errors);*/
        });
    }

    _saveCustomObject(data) {
        debugger;
        return new Promise( (resolve, reject) => {
            debugger;
            const url = Routing.generate('custom_object_new', {portal: 1});

            debugger;

            $.ajax({
                url,
                method: 'POST',
                data: data
            }).then((data, textStatus, jqXHR) => {
                debugger;
                resolve(data);
               /* $.ajax({
                    url: jqXHR.getResponseHeader('Location')
                }).then((data) => {
                    // we're finally done!
                    resolve(data);
                });*/
            }).catch((jqXHR) => {
                /*const errorData = JSON.parse(jqXHR.responseText);*/

                debugger;
                reject(errorData);
            });
        });
    }
}

export default CustomObjectSettings;