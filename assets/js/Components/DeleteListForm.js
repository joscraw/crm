'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';

class DeleteListForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portal
     * @param listId
     */
    constructor($wrapper, globalEventDispatcher, portal, listId) {

        debugger;
        this.$wrapper = $wrapper;
        this.portal = portal;
        this.listId = listId;

        /**
         * @type {EventDispatcher}
         */
        this.globalEventDispatcher = globalEventDispatcher;

        this.$wrapper.on(
            'submit',
            DeleteListForm._selectors.deleteListForm,
            this.handleDeleteFormSubmit.bind(this)
        );

        this.loadForm();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            deleteListForm: '.js-delete-list-form',
        }
    }

    loadForm() {
        debugger;
        $.ajax({
            url: Routing.generate('delete_list_form', {internalIdentifier: this.portal, listId: this.listId}),
        }).then(data => {
            this.$wrapper.html(data.formMarkup);
        })
    }

    /**
     * @param e
     */
    handleDeleteFormSubmit(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        debugger;
        const $form = $(e.currentTarget);
        let formData = new FormData($form.get(0));

        this._delete(formData)
            .then((data) => {

                swal("Hooray!", "Sweet! List successfully removed!", "success");
                this.globalEventDispatcher.publish(Settings.Events.LIST_DELETED);
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
    _delete(data) {
        return new Promise( (resolve, reject) => {

            const url = Routing.generate('delete_list', {internalIdentifier: this.portal, listId: this.listId});
            debugger;

            $.ajax({
                url,
                method: 'POST',
                data: data,
                processData: false,
                contentType: false
            }).then((data, textStatus, jqXHR) => {
                debugger;
                resolve(data);
            }).catch((jqXHR) => {
                debugger;
                const errorData = JSON.parse(jqXHR.responseText);

                errorData.httpCode = jqXHR.status;

                reject(errorData);
            });
        });
    }
}

export default DeleteListForm;