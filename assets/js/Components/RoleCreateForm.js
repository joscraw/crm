'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';
import FormCollectionPrototypeUpdater from '../FormCollectionPrototypeUpdater';

class RoleCreateForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;

        this.unbindEvents();

        this.$wrapper.on(
            'submit',
            RoleCreateForm._selectors.newRoleForm,
            this.handleNewFormSubmit.bind(this)
        );

        this.loadCreateRoleForm().then(() => { this.activatePlugins(); });

    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            newRoleForm: '.js-new-role-form'
        }
    }

    unbindEvents() {

        this.$wrapper.off('submit', RoleCreateForm._selectors.newRoleForm);
    }


    activatePlugins() {

        $('.js-selectize-multiple-select').selectize({
            plugins: ['remove_button'],
            sortField: 'text'
        });

    }

    loadCreateRoleForm() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: Routing.generate('create_role', {internalIdentifier: this.portalInternalIdentifier}),
            }).then(data => {
                this.$wrapper.html(data.formMarkup);
                resolve(data);
            }).catch(errorData => {
                reject(errorData);
            });
        });
    }

    /**
     * @param e
     */
    handleNewFormSubmit(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        const $form = $(e.currentTarget);
        let formData = new FormData($form.get(0));

        this._saveRole(formData)
            .then((data) => {
                swal("Hooray!", "Well done, you created a new role!", "success");
            }).catch((errorData) => {

            if(errorData.httpCode === 401) {
                swal("Woah!", `You don't have proper permissions for this!`, "error");
                return;
            }

            this.$wrapper.html(errorData.formMarkup);
            this.activatePlugins();
        });
    }

    /**
     * @param data
     * @return {Promise<any>}
     * @private
     */
    _saveRole(data) {
        return new Promise( (resolve, reject) => {
            const url = Routing.generate('create_role', {internalIdentifier: this.portalInternalIdentifier});

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

export default RoleCreateForm;