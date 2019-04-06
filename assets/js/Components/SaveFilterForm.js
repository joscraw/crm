'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';

class SaveFilterForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portal
     * @param customObjectInternalName
     * @param customFilters
     */
    constructor($wrapper, globalEventDispatcher, portal, customObjectInternalName, customFilters) {

        this.$wrapper = $wrapper;
        this.portal = portal;
        this.customObjectInternalName = customObjectInternalName;
        this.customFilters = customFilters;

        /**
         * @type {EventDispatcher}
         */
        this.globalEventDispatcher = globalEventDispatcher;

        this.$wrapper.on(
            'submit',
            SaveFilterForm._selectors.saveFilterForm,
            this.handleNewFormSubmit.bind(this)
        );

        this.loadSaveFilterForm();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            saveFilterForm: '.js-save-filter-form',
        }
    }

    loadSaveFilterForm() {
        $.ajax({
            url: Routing.generate('save_filter_form', {internalIdentifier: this.portal, internalName: this.customObjectInternalName}),
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

        formData['customFilters'] = this.customFilters;

        this._saveFilter(formData)
            .then((data) => {
                swal("Hooray!", "Filter successfully saved!", "success");
                this.globalEventDispatcher.publish(Settings.Events.CUSTOM_OBJECT_CREATED);
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
    _saveFilter(data) {
        return new Promise( (resolve, reject) => {
            const url = Routing.generate('save_filter', {internalIdentifier: this.portal, internalName: this.customObjectInternalName});

            $.ajax({
                url,
                method: 'POST',
                data: data
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

export default SaveFilterForm;