'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';
import FormHelper from '../FormHelper';
import List from 'list.js';
import ColumnSearch from "./ColumnSearch";
require('jquery-ui-dist/jquery-ui');

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
            ColumnsForm._selectors.selectedPropertiesForm,
            this.handleNewFormSubmit.bind(this)
        );

        this.$wrapper.on(
            'change',
            ColumnsForm._selectors.propertyCheckbox,
            this.handlePropertyCheckboxChanged.bind(this)
        );

        this.$wrapper.on(
            'click',
            ColumnsForm._selectors.removeSelectedColumnIcon,
            this.handleRemoveSelectedColumnIconClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.COLUMN_SEARCH_KEY_UP,
            this.applySearch.bind(this)
        );

        this.loadProperties().then(data => {
            this.render(data).then(() => {
                this.renderSelectedColumns();
            })
        });

        this.activatePlugins();

    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            selectedPropertiesForm: '.js-selected-properties-form',
            propertyCheckbox: '.js-property-checkbox',
            selectedColumnsContainer: '.js-selected-columns-container',
            removeSelectedColumnIcon: '.js-remove-selected-column-icon'
        }
    }

    activatePlugins() {
        const $selectedColumnsContainer = $(ColumnsForm._selectors.selectedColumnsContainer);
        $selectedColumnsContainer.sortable({
            placeholder: "ui-state-highlight",
            cursor: 'crosshair',
            update: function(event, ui) {
                var order = $("#sortable").sortable("toArray");
                $('#image_order').val(order.join(","));
                alert($('#image_order').val());
            }});
        $selectedColumnsContainer.disableSelection();
    }

    handleRemoveSelectedColumnIconClicked(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        let propertyId = $(e.target).data('propertyId');
        this._removeSelectedColumn(propertyId);

        this.$wrapper.find('.js-property-list').find(`[data-property-id="${propertyId}"]`).prop('checked', false);

        /*this.renderSelectedColumns();*/
    }

    renderSelectedColumns() {

        debugger;
        const $selectedColumnsContainer = $(ColumnsForm._selectors.selectedColumnsContainer);
        $selectedColumnsContainer.html("");
        const $form = $(ColumnsForm._selectors.columnsForm);
        for (let fieldData of $form.serializeArray()) {
            debugger;
            let value = fieldData.value;
            let label = $form.find(`input[value=${value}]`).attr('data-label');
            const html = selectedColumnTemplate(label, value);
            const $selectedColumnTemplate = $($.parseHTML(html));
            $selectedColumnsContainer.append($selectedColumnTemplate);
        }

        this.activatePlugins();
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
        return new Promise((resolve, reject) => {
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

            resolve();
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

    handlePropertyCheckboxChanged(e) {
        debugger;
        let label = $(e.target).attr('data-label');
        let propertyId = $(e.target).attr('data-property-id');
        if($(e.target).is(":checked")) {
            this._addSelectedColumn(label, propertyId);
        } else {
            debugger;
            this._removeSelectedColumn(propertyId);
        }

        debugger;
        /*this.renderSelectedColumns();*/
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
        let $propertyList = this.$wrapper.find('.js-property-list');
        const html = listTemplate(propertyGroup);
        const $list = $($.parseHTML(html));
        $propertyList.append($list);

        var options = {
            valueNames: [ 'label' ],
            // Since there are no elements in the list, this will be used as template.
            item: `<li><div class="form-check"><input class="form-check-input js-property-checkbox" type="checkbox" value="" id=""><label class="form-check-label" for=""><p class="label"></p></label></div></li>`
        };

        this.lists.push(new List(`list-${propertyGroup.id}`, options, properties));

        $( `#list-${propertyGroup.id} li input[type="checkbox"]` ).each((index, element) => {
            $(element).attr('data-label', properties[index].label);
            $(element).attr('data-property-id', properties[index].id);

            // Used to make sure when you click the label the checkbox gets checked
            $(element).attr('id', `property-${properties[index].id}`);
            $(element).next().attr('for', `property-${properties[index].id}`);
        });

    }

    _addSelectedColumn(label, propertyId) {
        const $selectedColumnsContainer = $(ColumnsForm._selectors.selectedColumnsContainer);
        const html = selectedColumnTemplate(label, propertyId);
        const $selectedColumnTemplate = $($.parseHTML(html));
        $selectedColumnsContainer.append($selectedColumnTemplate);

        this.activatePlugins();
    }

    _removeSelectedColumn(propertyId) {
        debugger;
        const $selectedColumnsContainer = $(ColumnsForm._selectors.selectedColumnsContainer);
        $selectedColumnsContainer.find(`[data-property-id="${propertyId}"]`).closest('.js-selected-column').remove();

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
        <div class="col-md-6">
        <div class="js-search-container"></div>
        <div class="js-property-list"></div>
        <form class="js-selected-properties-form">
        <input type="hidden" value="" name="sortedProperties">
        <button type="submit" class="btn-primary btn">Submit</button>
        </form>
        </div>
        <div class="js-selected-columns-container col-md-6"></div>
        <div class="col-md-12">
        </div>
    </div>
`;


const selectedColumnTemplate = (label, id) => `
    <div class="card js-selected-column" id="item-${id}">
        <div class="card-body">${label}<span><i class="fa fa-times js-remove-selected-column-icon" data-property-id="${id}" aria-hidden="true"></i></span></div>
    </div>
`;

export default ColumnsForm;