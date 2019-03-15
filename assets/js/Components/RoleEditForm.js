'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';
import FormCollectionPrototypeUpdater from '../FormCollectionPrototypeUpdater';

class RoleEditForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, roleId) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.roleId = roleId;

        this.unbindEvents();

        this.$wrapper.on(
            'submit',
            RoleEditForm._selectors.editRoleForm,
            this.handleEditFormSubmit.bind(this)
        );

        this.loadEditRoleForm().then(() => { this.activatePlugins(); });

    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            editRoleForm: '.js-edit-role-form'
        }
    }

    unbindEvents() {

        this.$wrapper.off('submit', RoleEditForm._selectors.editRoleForm);
    }

    activatePlugins() {

        $('.js-selectize-multiple-select').selectize({
            plugins: ['remove_button'],
            sortField: 'text'
        });

    }

    loadEditRoleForm() {

        return new Promise((resolve, reject) => {

            let url = Routing.generate('edit_role', {internalIdentifier: this.portalInternalIdentifier, roleId: this.roleId});

            $.ajax({
                url: url,
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
    handleEditFormSubmit(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        const $form = $(e.currentTarget);
        let formData = new FormData($form.get(0));

        this._saveRole(formData)
            .then((data) => {
                swal("Hooray!", "Well done, you updated your role!", "success");
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
    _saveRole(data) {

        return new Promise( (resolve, reject) => {

            const url = Routing.generate('edit_role', {internalIdentifier: this.portalInternalIdentifier, roleId: this.roleId});

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

export default RoleEditForm;