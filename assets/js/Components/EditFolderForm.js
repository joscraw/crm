'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';

class EditFolderForm {

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
            EditFolderForm._selectors.editFolderForm,
            this.handleNewFormSubmit.bind(this)
        );

        this.loadForm();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            editFolderForm: '.js-edit-folder-form',
        }
    }

    loadForm() {
        $.ajax({
            url: Routing.generate('edit_folder', {internalIdentifier: this.portal, folderId: this.folderId}),
        }).then(data => {
            this.$wrapper.html(data.formMarkup);
        })
    }

    /**
     * @param e
     */
    handleNewFormSubmit(e) {

        debugger;

        if(e.cancelable) {
            e.preventDefault();
        }

        const $form = $(e.currentTarget);
        let formData = new FormData($form.get(0));

        this._editFolder(formData)
            .then((data) => {
                swal("Hooray!", "Well done, you've edited your folder!", "success");
                this.globalEventDispatcher.publish(Settings.Events.FOLDER_MODIFIED);
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
    _editFolder(data) {
        return new Promise( (resolve, reject) => {
            const url = Routing.generate('edit_folder', {internalIdentifier: this.portal, folderId: this.folderId});

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
                errorData.httpCode = jqXHR.status;
                reject(errorData);
            });
        });
    }
}

export default EditFolderForm;