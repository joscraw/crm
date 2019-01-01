'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';

class PropertyCreateForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     */
    constructor($wrapper, globalEventDispatcher) {

        this.$wrapper = $wrapper;

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

        this.loadCreatePropertyForm();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            newPropertyForm: '.js-new-property-form',
            fieldType: '.js-field-type',
            addItem: '.js-addItem'
        }
    }

    loadCreatePropertyForm() {
        $.ajax({
            url: Routing.generate('create_property', {portal: 1}),
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
                this.globalEventDispatcher.publish(Settings.Events.PROPERTY_GROUP_CREATED);
            }).catch((errorData) => {

            this.$wrapper.html(errorData.formMarkup);

            // Use for when the form is being generated on the JS side
            /*this._mapErrorsToForm(errorData.errors);*/
        });
    }

    handleFieldTypeChange() {
        console.log("field changed");
    }

    handleAddItemButtonClick(e) {

        debugger;

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

        debugger;

        console.log("button clicked");
    }

    /**
     * @param data
     * @return {Promise<any>}
     * @private
     */
    _saveProperty(data) {
        return new Promise( (resolve, reject) => {
            const url = Routing.generate('create_property', {portal: 1});

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