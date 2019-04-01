'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';
import FormCollectionPrototypeUpdater from '../FormCollectionPrototypeUpdater';

class UserCreateForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;


        this.$wrapper.on(
            'submit',
            UserCreateForm._selectors.newUserForm,
            this.handleNewFormSubmit.bind(this)
        );

/*

        this.$wrapper.on(
            'change',
            PropertyCreateForm._selectors.fieldType,
            this.handleFieldTypeChange.bind(this)
        );

        this.$wrapper.on(
            'change',
            PropertyCreateForm._selectors.customObject,
            this.handleCustomObjectChange.bind(this)
        );

        this.$wrapper.on(
            'click',
            PropertyCreateForm._selectors.addItem,
            this.handleAddItemButtonClick.bind(this)
        );

        this.$wrapper.on(
            'click',
            PropertyCreateForm._selectors.removeItem,
            this.handleRemoveItemButtonClick.bind(this)
        );*/

        this.loadCreateUserForm().then(() => { this.activatePlugins(); });

        /*this.activatePlugins();*/
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            newUserForm: '.js-new-user-form',
            fieldType: '.js-field-type',
            customObject: '.js-custom-object',
            addItem: '.js-addItem',
            removeItem: '.js-removeItem'
        }
    }


    activatePlugins() {

        $('.js-selectize-multiple-select').selectize({
            plugins: ['remove_button'],
            sortField: 'text'
        });

    }

    loadCreateUserForm() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: Routing.generate('create_user', {internalIdentifier: this.portalInternalIdentifier}),
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

        this._saveUser(formData)
            .then((data) => {
                swal("Hooray!", "Well done, you created a new User!", "success");
                this.globalEventDispatcher.publish(Settings.Events.USER_CREATED);
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
    _saveUser(data) {
        return new Promise( (resolve, reject) => {
            const url = Routing.generate('create_user', {internalIdentifier: this.portalInternalIdentifier});

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

export default UserCreateForm;