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
                this._setSelectedColumnsCount();
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
            removeSelectedColumnIcon: '.js-remove-selected-column-icon',
            selectedColumnsCount: '.js-selected-columns-count'
        }
    }

    activatePlugins() {
        const $selectedColumnsContainer = $(ColumnsForm._selectors.selectedColumnsContainer);
        $selectedColumnsContainer.sortable({
            placeholder: "ui-state-highlight",
            cursor: 'crosshair'
        });
        $selectedColumnsContainer.disableSelection();
    }

    handleRemoveSelectedColumnIconClicked(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        let propertyId = $(e.target).data('propertyId');
        this._removeSelectedColumn(propertyId);

        this.$wrapper.find('.js-property-list').find(`[data-property-id="${propertyId}"]`).prop('checked', false);

    }

    /**
     * @param args
     */
    applySearch(args = {}) {

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

        const $selectedColumnsContainer = $(ColumnsForm._selectors.selectedColumnsContainer);

        let newOrderArray = $selectedColumnsContainer.sortable('toArray');

        const $form = $(e.currentTarget);
        let formData = new FormData($form.get(0));
        formData.append('custom_object_id', this.customObject);

        for (let i = 0; i < newOrderArray.length; i++) {
            formData.append('selected_properties[]', newOrderArray[i]);
        }

        this._saveColumns(formData)
            .then((data) => {
                debugger;
                swal("Whoop whoop!", `Columns successfully updated!`, "success");
                this.globalEventDispatcher.publish(Settings.Events.COLUMNS_UPDATED);
            }).catch((errorData) => {

            /*this.$wrapper.html(errorData.formMarkup);
            this.activatePlugins();*/

            // Use for when the form is being generated on the JS side
            /*this._mapErrorsToForm(errorData.errors);*/
        });
    }

    handlePropertyCheckboxChanged(e) {
        let label = $(e.target).attr('data-label');
        let propertyId = $(e.target).attr('data-property-id');
        if($(e.target).is(":checked")) {
            this._addSelectedColumn(label, propertyId);
        } else {
            this._removeSelectedColumn(propertyId);
        }
    }

    /**
     * @param data
     * @return {Promise<any>}
     * @private
     */
    _saveColumns(data) {

        return new Promise( (resolve, reject) => {

            const url = Routing.generate('set_property_columns', {internalIdentifier: this.portal});

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

        let selectedColumns = {};
        for(let i = 0; i < properties.length; i++) {
            let property = properties[i];

            if(property.isColumn) {
                $( `#list-${propertyGroup.id} li [data-property-id='${property.id}']` ).prop('checked', true);
                selectedColumns[property.columnOrder] = {'label': property.label, 'id': property.id};
            } else {
                $( `#list-${propertyGroup.id} li [data-property-id='${property.id}']` ).prop('checked', false);
            }
        }

        // make sure the selected columns appear in the correct order
        for(let order in selectedColumns) {
            this._addSelectedColumn(selectedColumns[order].label, selectedColumns[order].id);
        }

    }

    _addSelectedColumn(label, propertyId) {
        const $selectedColumnsContainer = $(ColumnsForm._selectors.selectedColumnsContainer);
        const html = selectedColumnTemplate(label, propertyId);
        const $selectedColumnTemplate = $($.parseHTML(html));
        $selectedColumnsContainer.append($selectedColumnTemplate);

        this.activatePlugins();
        this._setSelectedColumnsCount();
    }

    _removeSelectedColumn(propertyId) {
        const $selectedColumnsContainer = $(ColumnsForm._selectors.selectedColumnsContainer);
        $selectedColumnsContainer.find(`[data-property-id="${propertyId}"]`).closest('.js-selected-column').remove();
        this._setSelectedColumnsCount();
    }

    _setSelectedColumnsCount() {
        const $selectedColumnsContainer = $(ColumnsForm._selectors.selectedColumnsContainer);
        let count = $selectedColumnsContainer.find('.js-selected-column').length;
        $(ColumnsForm._selectors.selectedColumnsCount).html(`Selected Columns: ${count}`);
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
        </div>
        <div class="col-md-6">
            <div class="js-selected-columns-count"></div>
            <div class="js-selected-columns-container"></div>
        </div>
        
        <div class="col-md-6">
            <form class="js-selected-properties-form">
                <input type="hidden" value="" class="js-sorted-properties" name="sortedProperties">
                <button type="submit" class="btn-primary btn">Submit</button>
            </form>
        </div>
      
    </div>
`;


const selectedColumnTemplate = (label, id) => `
    <div class="card js-selected-column" id="${id}">
        <div class="card-body">${label}<span><i class="fa fa-times js-remove-selected-column-icon" data-property-id="${id}" aria-hidden="true"></i></span></div>
    </div>
`;

export default ColumnsForm;