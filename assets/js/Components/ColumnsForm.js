'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';
import FormHelper from '../FormHelper';
import List from 'list.js';
import ColumnSearch from "./ColumnSearch";

class ColumnsForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param customObject
     * @param customObjectLabel
     * @param portal
     */
    constructor($wrapper, globalEventDispatcher, customObject, customObjectLabel, portal) {

        this.$wrapper = $wrapper;
        this.customObjectLabel = customObjectLabel;
        this.customObject = customObject;
        this.portal = portal;
        this.searchValue = '';
        this.lists = [];

        /**
         * @type {EventDispatcher}
         */
        this.globalEventDispatcher = globalEventDispatcher;

        this.$wrapper.on(
            'submit',
            ColumnsForm._selectors.columnsForm,
            this.handleNewFormSubmit.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.COLUMN_SEARCH_KEY_UP,
            this.applySearch.bind(this)
        );

        this.loadProperties().then(data => {
            debugger;
            this.render(data);
        });

        /*this.activatePlugins();*/
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            columnsForm: '.js-columns-form',
        }
    }

    /**
     * @param args
     */
    applySearch(args = {}) {

        debugger;
        if(typeof args.searchValue !== 'undefined') {
            this.searchValue = args.searchValue;
        }

        for(let i = 0; i < this.lists.length; i++) {
            this.lists[i].search(this.searchValue);
        }

        this.$wrapper.find('.js-list').each((index, element) => {
            if($(element).find('.list').is(':empty') && this.searchValue !== '') {
                $(element).addClass('d-none');

            } else {
                if($(element).hasClass('d-none')) {
                    $(element).removeClass('d-none');
                }
            }
        });
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

        const url = Routing.generate('records_for_selectize', {internalIdentifier: this.portal});

        var $j = $('.js-allowed-selectize-search-result-properties').val();

        debugger;
        this.$select = $('.js-selectize-single-select-with-search').selectize({
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
                        allowed_custom_object_to_search: $('.js-selectize-single-select-with-search').data('allowedCustomObjectToSearch'),
                        allowed_selectize_search_result_properties: $('.js-allowed-selectize-search-result-properties').val()
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
            format: 'yyyy-mm-dd'
        });
    }

    loadProperties() {
        return new Promise((resolve, reject) => {
            debugger;
            const url = Routing.generate('properties_for_columns', {internalIdentifier: this.portal});

            $.ajax({
                url: url,
                data: {custom_object_id: this.customObject}
            }).then(data => {
                resolve(data);
            }).catch(jqXHR => {
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }

    render(data) {
        debugger;
        const html = mainTemplate();
        const $mainTemplate = $($.parseHTML(html));
        this.$wrapper.append($mainTemplate);
        debugger;

        for(let key in data.data.property_groups) {
            debugger;
            if(data.data.property_groups.hasOwnProperty(key)) {
                let propertyGroup = data.data.property_groups[key];
                let properties = data.data.properties[key];
                this._addList(propertyGroup, properties);
            }
        }

        new ColumnSearch(this.$wrapper.find('.js-search-container'), this.globalEventDispatcher, this.portal, this.customObject, this.customObjectLabel, "Search for a column...");
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
        formData.append('custom_object_id', this.customObject);

        this._saveRecord(formData)
            .then((data) => {
                debugger;
                swal("Hooray!", `Well done, you created a shiny brand new ${this.customObjectLabel}!`, "success");
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
            const url = Routing.generate('create_record', {internalIdentifier: this.portal});

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

    /**
     * @param propertyGroup
     * @param properties
     * @private
     */
    _addList(propertyGroup, properties) {
        debugger;
        let $propertyList = this.$wrapper.find('.js-property-list');
        const html = listTemplate(propertyGroup);
        const $list = $($.parseHTML(html));
        $propertyList.append($list);


        var options = {
            valueNames: [ 'label' ],
            // Since there are no elements in the list, this will be used as template.
            item: '<li><p class="label"></p></li>'
        };

        this.lists.push(new List(`list-${propertyGroup.id}`, options, properties));
    }
}

const listTemplate = ({id, label}) => `
    <div id="list-${id}" class="js-list">
      <p>${label}</p>
      <ul class="list"></ul>
    </div>
`;

const mainTemplate = () => `
    <div class="row">
        <div class="js-property-list col-md-6">
        <div class="js-search-container"></div>
        </div>
        <div class="js-selected-properties col-md-6"></div>
    </div>
`;

const propertyListTemplate = () => {

};

const selectedPropertiesTemplate = () => {

};

export default ColumnsForm;