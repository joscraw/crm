'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';
import FormCollectionPrototypeUpdater from '../FormCollectionPrototypeUpdater';

class RecordEditForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     * @param recordId
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, recordId) {

        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.recordId = recordId;
        this.collapseStatus = {};


/*        this.$wrapper.on(
            'submit',
            PropertyEditForm._selectors.newPropertyForm,
            this.handleEditFormSubmit.bind(this)
        );

        this.$wrapper.on(
            'change',
            PropertyEditForm._selectors.fieldType,
            this.handleFieldTypeChange.bind(this)
        );

        this.$wrapper.on(
            'change',
            PropertyEditForm._selectors.customObject,
            this.handleCustomObjectChange.bind(this)
        );*/

        this.$wrapper.on('click',
            RecordEditForm._selectors.collapseTitle,
            this.handleTitleClick.bind(this)
        );

        this.loadEditRecordForm().then(() => { this.activatePlugins(); });

       /* this.activatePlugins();*/

    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            newPropertyForm: '.js-new-property-form',
            fieldType: '.js-field-type',
            customObject: '.js-custom-object',
            addItem: '.js-addItem',
            removeItem: '.js-removeItem',
            collapse: '.js-collapse',
            collapseTitle: '.js-collapse__title',
            collapseBody: '.js-collapse__body'
        }
    }

    handleTitleClick(e) {

        debugger;
        let $collapseBody = $(e.target).closest(RecordEditForm._selectors.collapse)
            .find(RecordEditForm._selectors.collapseBody);

        let $collapseTitle = $(e.target).closest(RecordEditForm._selectors.collapse)
            .find(RecordEditForm._selectors.collapseTitle);

        let propertyGroupId = $(e.target).closest(RecordEditForm._selectors.collapse).data('property-group-id');

        $collapseBody.on('hidden.bs.collapse', (e) => {
            this.collapseStatus[propertyGroupId] = 'hide';
        });

        $collapseBody.on('shown.bs.collapse', (e) => {
            this.collapseStatus[propertyGroupId] = 'show';
        });

        $collapseBody.on('show.bs.collapse', (e) => {
            $collapseTitle.find('i').addClass('is-active');
        });

        $collapseBody.on('hide.bs.collapse', (e) => {
            $collapseTitle.find('i').removeClass('is-active');
        });

        $collapseBody.collapse('toggle');
    }


    activatePlugins() {
        $('.js-selectize-multiple-select').selectize({
            plugins: ['remove_button'],
            sortField: 'text'
        });

        $('.js-selectize-single-select').selectize({
            sortField: 'text'
        });

        debugger;

        const url = Routing.generate('records_for_selectize', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});

        debugger;

        $('.js-selectize-single-select-with-search').each((index, element) => {

            let select = $(element).selectize({
                valueField: 'valueField',
                labelField: 'labelField',
                searchField: 'searchField',
                load: (query, callback) => {

                    if (!query.length) return callback();
                    $.ajax({
                        url: url,
                        type: 'GET',
                        dataType: 'json',
                        data: {
                            search: query,
                            allowed_custom_object_to_search: $(element).data('allowedCustomObjectToSearch'),
                            property_id: $(element).data('propertyId')
                        },
                        error: () => {
                            debugger;
                            callback();
                        },
                        success: (res) => {
                            debugger;
                            select.options = res;
                            callback(res);
                        }
                    })
                },
                render: {
                    option: function(record, escape) {

                        debugger;
                        let rows = ``,
                            items = record.items;
                        debugger;
                        for(let i = 0; i < items.length; i++) {
                            debugger;
                            let item = items[i];
                            rows += `<li class="c-selectize__list-item">${item.label}: ${item.value}</li>`;
                        }
                        return `<div class="c-selectize"><ul class="c-selectize__list">${rows}</ul></div>`;
                    }
                }
            });


        });

        $('.js-datepicker').datepicker({
            format: 'yyyy-MM-dd'
        });
    }

    loadEditRecordForm() {
        return new Promise((resolve, reject) => {
            debugger;
            $.ajax({
                url: Routing.generate('edit_record_form', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName, recordId: this.recordId}),
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

        this._saveProperty(formData)
            .then((data) => {
                swal("Sweeeeet!", "You've edited your Property!", "success");
                this.globalEventDispatcher.publish(Settings.Events.PROPERTY_EDITED);
            }).catch((errorData) => {

            this.$wrapper.html(errorData.formMarkup);

            this.activatePlugins();

            // Use for when the form is being generated on the JS side
            /*this._mapErrorsToForm(errorData.errors);*/
        });
    }

    handleCustomObjectChange(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        debugger;

        const formData = {};

        formData[$(e.target).attr('name')] = $(e.target).val();
        formData[$(PropertyEditForm._selectors.customObject).attr('name')] = $(PropertyEditForm._selectors.customObject).val();
        formData[$(PropertyEditForm._selectors.fieldType).attr('name')] = $(PropertyEditForm._selectors.fieldType).val();

        formData['validate'] = false;

        debugger;
        this._changeCustomObject(formData)
            .then((data) => {
                debugger;
                console.log("hi");
            }).catch((errorData) => {

                debugger;
            $('.js-selectize-search-result-properties-container').replaceWith(
                // ... with the returned one from the AJAX response.
                $(errorData.formMarkup).find('.js-selectize-search-result-properties-container')
            );

            this.activatePlugins();

        });

    }

    handleFieldTypeChange(e) {

        debugger;
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

                debugger;
            $('.js-field-container').replaceWith(
                // ... with the returned one from the AJAX response.
                $(errorData.formMarkup).find('.js-field-container')
            );

            this.activatePlugins();

        });

    }

    _changeCustomObject(data) {
        return new Promise((resolve, reject) => {
            debugger;
            const url = Routing.generate('edit_property', {internalIdentifier: this.portal, internalName: this.propertyInternalName});

            data.custom_object_id = this.customObject;

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

    _changeFieldType(data) {
        return new Promise((resolve, reject) => {
            debugger;
            const url = Routing.generate('edit_property', {internalIdentifier: this.portal, internalName: this.propertyInternalName});

            data.custom_object_id = this.customObject;

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
            const url = Routing.generate('edit_property', {internalIdentifier: this.internalIdentifier, internalName: this.internalName, propertyInternalName: this.propertyInternalName});

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

export default RecordEditForm;