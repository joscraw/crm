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

            this.$wrapper.html(errorData.formMarkup);
            this.activatePlugins();
        });
    }

    handleCustomObjectChange(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        const formData = {};

        formData[$(e.target).attr('name')] = $(e.target).val();
        formData[$(PropertyCreateForm._selectors.customObject).attr('name')] = $(PropertyCreateForm._selectors.customObject).val();
        formData[$(PropertyCreateForm._selectors.fieldType).attr('name')] = $(PropertyCreateForm._selectors.fieldType).val();
        formData['validate'] = false;

        this._changeCustomObject(formData).then((data) => {}).catch((errorData) => {

            $('.js-selectize-search-result-properties-container').replaceWith(
                // ... with the returned one from the AJAX response.
                $(errorData.formMarkup).find('.js-selectize-search-result-properties-container')
            );

            this.activatePlugins();
        });

    }

    handleFieldTypeChange(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        const formData = {};
        formData[$(e.target).attr('name')] = $(e.target).val();

        this._changeFieldType(formData).then((data) => {}).catch((errorData) => {

            $('.js-field-container').replaceWith(
                $(errorData.formMarkup).find('.js-field-container')
            );

            this.activatePlugins();

        });

    }

    _changeCustomObject(data) {
        return new Promise((resolve, reject) => {

            const url = Routing.generate('create_property', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});

            $.ajax({
                url,
                method: 'POST',
                data: data
            }).then((data, textStatus, jqXHR) => {
                resolve(data);
            }).catch((jqXHR) => {
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }

    _changeFieldType(data) {
        return new Promise((resolve, reject) => {
            const url = Routing.generate('create_property', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});

            $.ajax({
                url,
                method: 'POST',
                data: data
            }).then((data, textStatus, jqXHR) => {
                resolve(data);
            }).catch((jqXHR) => {
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }

    handleAddItemButtonClick(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        let $parentContainer = $('.js-parent-container');
        let index = $parentContainer.children('.js-child-item').length;
        let template = $parentContainer.data('template');
        let tpl = eval('`'+template+'`');
        let $container = $('<li>').addClass('list-group-item js-child-item');
        $container.append(tpl);
        $parentContainer.append($container);
    }

    handleRemoveItemButtonClick(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        let $item = $(e.currentTarget).parents('.js-child-item');
        let $container = $item.closest('.js-parent-container');
        let fieldPrefix = FormCollectionPrototypeUpdater.getFieldPrefix($item);
        let index = $item.index();
        $item.remove();
        $container.children().slice(index).each(this.updateListElementGenerator(index, fieldPrefix));
    }

    updateListElementGenerator(offset, fieldPrefix) {
        return function(index, el) {
            debugger;
            FormCollectionPrototypeUpdater.updateAttributes($(el), fieldPrefix, offset + index + 1)
        }
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
                reject(errorData);
            });
        });
    }
}

export default UserCreateForm;