'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';

class EditUserForm {

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
            EditUserForm._selectors.editUserForm,
            this.handleEditFormSubmit.bind(this)
        );
        this.loadEditUserForm().then(() => { this.activatePlugins(); });
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            editUserForm: '.js-edit-user-form',
        }
    }

    loadEditUserForm() {
        debugger;
        return new Promise((resolve, reject) => {

            let url = Routing.generate('edit_user', {internalIdentifier: this.portal, userId: this.userId});

            $.ajax({
                url: url
            }).then(data => {
                this.$wrapper.html(data.formMarkup);
                resolve(data);
            }).catch(errorData => {
                reject(errorData);
            });

        });
    }

    activatePlugins() {

        $('.js-selectize-multiple-select').selectize({
            plugins: ['remove_button'],
            sortField: 'text'
        });

    }


    /**
     * @param e
     */
    handleEditFormSubmit(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        const $form = $(e.currentTarget);
        let formData = new FormData($form.get(0));

        this._editUser(formData)
            .then((data) => {
                swal("Hooray!", "Well done, you've edited your User!", "success");
                this.globalEventDispatcher.publish(Settings.Events.USER_EDITED);
            }).catch((errorData) => {

            this.$wrapper.html(errorData.formMarkup);
            this.activatePlugins();
        });
    }

    /**
     * @param data
     * @return {Promise<any>}
     * @private
     */
    _editUser(data) {
        return new Promise( (resolve, reject) => {
            const url = Routing.generate('edit_user', {internalIdentifier: this.portal, userId: this.userId});

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

export default EditUserForm;