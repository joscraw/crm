
'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';
import FormHelper from '../FormHelper';

require('jquery-ui-dist/jquery-ui');
require('jquery-ui-dist/jquery-ui.css');
require('bootstrap-datepicker');
require('bootstrap-datepicker/dist/css/bootstrap-datepicker.css');

class RecordForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;

        this.$wrapper.on(
            'submit',
            RecordForm._selectors.newRecordForm,
            this.handleNewFormSubmit.bind(this)
        );

        this.loadForm().then(()=> {this.activatePlugins();});
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            newRecordForm: '.js-new-record-form',
        }
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

        $('.js-datepicker').datepicker({
            format: 'mm/dd/yyyy'
        });
    }

    loadForm() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: Routing.generate('create_record_form', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName}),
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

        this._saveRecord(formData)
            .then((data) => {

                swal("Hooray!", `Well done, you created a shiny brand new record!`, "success");
                this.globalEventDispatcher.publish(Settings.Events.RECORD_CREATED);

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
    _saveRecord(data) {

        return new Promise( (resolve, reject) => {

            const url = Routing.generate('create_record', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});

            $.ajax({
                url,
                method: 'POST',
                data: data,
                processData: false,
                contentType: false
            }).then((data, textStatus, jqXHR) => {

                debugger;
                resolve(data);
            }).catch((jqXHR) => {

                const errorData = JSON.parse(jqXHR.responseText);

                errorData.httpCode = jqXHR.status;

                reject(errorData);
            });
        });
    }
}

export default RecordForm;