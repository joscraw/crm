'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';

class DeleteUserForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portal
     * @param userId
     */
    constructor($wrapper, globalEventDispatcher, portal, userId) {

        debugger;
        this.$wrapper = $wrapper;
        this.portal = portal;
        this.userId = userId;

        /**
         * @type {EventDispatcher}
         */
        this.globalEventDispatcher = globalEventDispatcher;

        this.$wrapper.on(
            'submit',
            DeleteUserForm._selectors.deleteUserForm,
            this.handleDeleteFormSubmit.bind(this)
        );
        this.loadDeleteUserForm();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            deleteUserForm: '.js-delete-user-form',
        }
    }

    loadDeleteUserForm() {
        debugger;
        $.ajax({
            url: Routing.generate('delete_user_form', {internalIdentifier: this.portal, userId: this.userId}),
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

        const $form = $(e.currentTarget);
        let formData = new FormData($form.get(0));

        this._deleteUser(formData)
            .then((data) => {
                swal("Hooray!", "Sweet! You deleted your user!", "success");
                this.globalEventDispatcher.publish(Settings.Events.USER_DELETED);
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
    _deleteUser(data) {
        return new Promise( (resolve, reject) => {

            debugger;
            const url = Routing.generate('delete_user', {internalIdentifier: this.portal, userId: this.userId});

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

export default DeleteUserForm;