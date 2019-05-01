'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';

class CreateFolderForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portal
     * @param folderId
     */
    constructor($wrapper, globalEventDispatcher, portal, folderId) {

        debugger;
        this.$wrapper = $wrapper;
        this.portal = portal;
        this.folderId = folderId;

        /**
         * @type {EventDispatcher}
         */
        this.globalEventDispatcher = globalEventDispatcher;

        this.$wrapper.on(
            'submit',
            CreateFolderForm._selectors.newFolderForm,
            this.handleNewFormSubmit.bind(this)
        );

        this.loadCreateFolderForm();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            newFolderForm: '.js-new-folder-form',
        }
    }

    loadCreateFolderForm() {
        $.ajax({
            url: Routing.generate('create_list_folder', {internalIdentifier: this.portal}),
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

        if(this.folderId) {
            formData.folderId = this.folderId;
        }

        this._saveFolder(formData)
            .then((data) => {
                swal("Hooray!", "Well done, you created a folder!", "success");
                this.globalEventDispatcher.publish(Settings.Events.FOLDER_CREATED);
            }).catch((errorData) => {

            if(errorData.httpCode === 401) {
                swal("Woah!", `You don't have proper permissions for this!`, "error");
                return;
            }

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
    _saveFolder(data) {
        return new Promise( (resolve, reject) => {
            const url = Routing.generate('create_list_folder', {internalIdentifier: this.portal});

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

export default CreateFolderForm;