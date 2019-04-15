'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';
import FormCollectionPrototypeUpdater from '../FormCollectionPrototypeUpdater';

require('jquery-ui-dist/jquery-ui');
require('jquery-ui-dist/jquery-ui.css');
require('bootstrap-datepicker');
require('bootstrap-datepicker/dist/css/bootstrap-datepicker.css');

class BulkEditForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     * @param records
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, records) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.records = records;

        this.$wrapper.on(
            'submit',
            BulkEditForm._selectors.bulkEditForm,
            this.handleFormSubmit.bind(this)
        );

        this.loadBulkEditForm().then(() => { this.activatePlugins(); });

        this.activatePlugins();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            bulkEditForm: '.js-bulk-edit-form',
            fieldType: '.js-field-type',
            property: 'js-property'
        }
    }


    activatePlugins() {

        $('.js-selectize-single-select-bulk-edit-property').selectize({
            sortField: 'text'
        }).on('change', this.handlePropertyChange.bind(this));

    }

    handlePropertyChange(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        const formData = {};

        let propertyId = $(e.target).val();


        formData[$(e.target).attr('name')] = $(e.target).val();

        debugger;

        this._changeProperty(formData).then((data) => {}).catch((errorData) => {

            $('.js-property-value-container').replaceWith(
                $(errorData.formMarkup).find('.js-property-value-container')
            );

            $('.js-selectize-single-select').selectize({
                sortField: 'text'
            });

            $('.js-selectize-multiple-select').selectize({
                plugins: ['remove_button'],
                sortField: 'text'
            });

            $('.js-datepicker').datepicker({
                format: 'mm-dd-yyyy'
            });

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
                                callback();
                            },
                            success: (res) => {
                                select.selectize()[0].selectize.clearOptions();
                                select.options = res;
                                callback(res);
                            }
                        })
                    }
                });


            });

        });
    }

    _changeProperty(data) {
        return new Promise((resolve, reject) => {

            const url = Routing.generate('bulk_edit', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});

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

    loadBulkEditForm() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: Routing.generate('bulk_edit', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName}),
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
    handleFormSubmit(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        const $form = $(e.currentTarget);
        let formData = new FormData($form.get(0));

        for (let i = 0; i < this.records.length; i++) {
            formData.append('records[]', this.records[i]);
        }

        this._updateProperty(formData)
            .then((data) => {
                swal("Hooray!", "Hooray!, record(s) successfully updated!", "success");
                this.globalEventDispatcher.publish(Settings.Events.BULK_EDIT_SUCCESSFUL);
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
    _updateProperty(data) {
        return new Promise( (resolve, reject) => {
            const url = Routing.generate('bulk_edit', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});

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

export default BulkEditForm;