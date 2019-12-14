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
     * @param data
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, data) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.data = data;

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

        $('.js-selectize-single-select').selectize({
            sortField: 'text'
        });

        $('.js-selectize-multiple-select').selectize({
            plugins: ['remove_button'],
            sortField: 'text'
        });

        $('.js-datepicker').datepicker({
            format: 'mm/dd/yyyy'
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

    }

    handlePropertyChange(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        const formData = {};

        let propertyId = $(e.target).val();

        formData[$(e.target).attr('name')] = $(e.target).val();

        this._changeProperty(formData).then((data) => {
            this.$wrapper.html(data.formMarkup);
            this.activatePlugins();
        }).catch((errorData) => {});
    }

    _changeProperty(data) {
        return new Promise((resolve, reject) => {

            const url = Routing.generate('bulk_edit_form', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});

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
                url: Routing.generate('bulk_edit_form', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName}),
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
        const formData = {};
        let propertyValue = document.getElementById("propertyValue");
        if(propertyValue.tagName === 'SELECT') {
            let options = propertyValue.getElementsByTagName('option'),
                values  = [];
            for (var i=options.length; i--;) {
                if (options[i].selected) values.push(options[i].value)
            }
            formData.propertyValue = values.join(";");
        } else if(propertyValue.tagName === 'INPUT' || propertyValue.tagName === 'TEXTAREA') {
            formData.propertyValue = propertyValue.value;
        }
        let propertyToUpdate = document.getElementById("propertyToUpdate");
        formData.propertyToUpdate = propertyToUpdate.value;
        formData.data = this.data;
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
        debugger;
        return new Promise( (resolve, reject) => {
            const url = Routing.generate('bulk_edit', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});
            // todo this needs to post raw json as we are actually going to post the entire payload for the data object
            //  cause those are going to be the records we are going to update. Let's make this a background job? Probably.
            //   try to google how to use JSON to update a value. We can maybe knock this out with one query and not have to do any
            //    background job. Hooray!
            $.ajax({
                url,
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data)
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