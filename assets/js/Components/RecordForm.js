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

        /*this.activatePlugins();*/
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

        var $j = $('.js-allowed-selectize-search-result-properties').val();

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


  /*      this.$select = $('.js-selectize-single-select-with-search').selectize({
            valueField: 'valueField',
            labelField: 'labelField',
            searchField: 'searchField',
            load: (query, callback) => {
                console.log(this.customObject);
                debugger;
                if (!query.length) return callback();
                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        search: query,
                        custom_object_id: this.customObject,
                        allowed_custom_object_to_search: $(this).data('allowedCustomObjectToSearch'),
                        property_id: $(this).data('propertyId')
                    },
                    error: () => {
                        debugger;
                        callback();
                    },
                    success: (res) => {
                        debugger;
                        this.$select.options = res;
                        callback(res);
                    }
                })
            },
            render: {
                option: function(record, escape) {

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
*/

        debugger;

/*        var $name = $('.js-selectize-single-select-with-search').selectize({
            valueField: 'Id',
            labelField: 'Name',
            searchField: 'Name',
            options: [],
            persist: false,
            loadThrottle: 600,
            create: false,
            allowEmptyOption: true,
            load: function(query, callback) {
                if (!query.length) return callback();
                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        name: query,
                        additionalDataIfRequired: 'Additional Data'
                    },
                    error: function() {
                        debugger;
                        callback();
                    },
                    success: function(res) {
                        debugger;
                        // you can apply any modification to data before passing it to selectize
                        callback(res);
                        // res is json response from server
                        // it contains array of objects. Each object has two properties. In this case 'id' and 'Name'
                        // if array is inside some other property of res like 'response' or something. than use this
                        //callback(res.response);
                    }
                });
            }
        })[0].selectize;*/

        $('.js-datepicker').datepicker({
            format: 'yyyy-MM-dd'
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
                debugger;
                swal("Hooray!", `Well done, you created a shiny brand new record!`, "success");
                this.globalEventDispatcher.publish(Settings.Events.RECORD_CREATED);
            }).catch((errorData) => {

                debugger;
            this.$wrapper.html(errorData.formMarkup);
            this.activatePlugins();

            // Use for when the form is being generated on the JS side
            /*this._mapErrorsToForm(errorData.errors);*/
        });
    }

    /**
     * @param data
     * @return {Promise<any>}
     * @private
     */
    _saveRecord(data) {
        debugger;
        return new Promise( (resolve, reject) => {
            debugger;
            const url = Routing.generate('create_record', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});

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

export default RecordForm;