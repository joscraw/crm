'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';
import FormCollectionPrototypeUpdater from '../FormCollectionPrototypeUpdater';

class PropertyCreateForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portal
     * @param customObject
     */
    constructor($wrapper, globalEventDispatcher, portal, customObject) {

        this.$wrapper = $wrapper;
        this.portal = portal;
        this.customObject = customObject;

        /**
         * @type {EventDispatcher}
         */
        this.globalEventDispatcher = globalEventDispatcher;

        this.$wrapper.on(
            'submit',
            PropertyCreateForm._selectors.newPropertyForm,
            this.handleNewFormSubmit.bind(this)
        );

        this.$wrapper.on(
            'change',
            PropertyCreateForm._selectors.fieldType,
            this.handleFieldTypeChange.bind(this)
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
        );

        this.loadCreatePropertyForm();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            newPropertyForm: '.js-new-property-form',
            fieldType: '.js-field-type',
            addItem: '.js-addItem',
            removeItem: '.js-removeItem'
        }
    }

    loadCreatePropertyForm() {
        $.ajax({
            url: Routing.generate('create_property', {portal: this.portal, customObject: this.customObject}),
        }).then(data => {
            this.$wrapper.html(data.formMarkup);
        })
    }

    /**
     * @param e
     */
    handleNewFormSubmit(e) {

        console.log("form submitted");

        if(e.cancelable) {
            e.preventDefault();
        }

        const $form = $(e.currentTarget);
        const formData = {};

        for (let fieldData of $form.serializeArray()) {
            formData[fieldData.name] = fieldData.value
        }

        this._saveProperty(formData)
            .then((data) => {
                swal("Hooray!", "Well done, you created a new Property!", "success");
                this.globalEventDispatcher.publish(Settings.Events.PROPERTY_CREATED);
            }).catch((errorData) => {

            this.$wrapper.html(errorData.formMarkup);

            // Use for when the form is being generated on the JS side
            /*this._mapErrorsToForm(errorData.errors);*/
        });
    }

    handleFieldTypeChange(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        const formData = {};

        formData[$(e.target).attr('name')] = $(e.target).val();
        formData['validate'] = false;

        this._changeFieldType(formData)
            .then((data) => {
                debugger;
                console.log("hi");
            }).catch((errorData) => {

            /*$(errorData.formMarkup).find('.invalid-feedback').remove();*/

            $('.js-field-container').replaceWith(
                // ... with the returned one from the AJAX response.
                $(errorData.formMarkup).find('.js-field-container')
            );

            /*this.$wrapper.html(errorData.formMarkup);*/

        });

    }

    _changeFieldType(data) {
        debugger;
        return new Promise((resolve, reject) => {
            debugger;
            const url = Routing.generate('create_property', {portal: this.portal, customObject: this.customObject});

            $.ajax({
                url,
                method: 'POST',
                data: data
            }).then((data, textStatus, jqXHR) => {
                debugger;
                resolve(data);
            }).catch((jqXHR) => {
                debugger;
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
        debugger;
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
    _saveProperty(data) {
        return new Promise( (resolve, reject) => {
            const url = Routing.generate('create_property', {portal: this.portal, customObject: this.customObject});

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
}

export default PropertyCreateForm;