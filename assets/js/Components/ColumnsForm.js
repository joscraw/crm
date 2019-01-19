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

    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            columnsForm: '.js-property-list-form',
            propertyCheckbox: '.js-property-checkbox',
            selectedColumnsContainer: '.js-selected-columns-container',
            removeSelectedColumnIcon: '.js-remove-selected-column-icon'
        }
    }

    handleRemoveSelectedColumnIconClicked(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        let propertyId = $(e.target).data('propertyId');
        $( `${ColumnsForm._selectors.propertyCheckbox}#property-${propertyId}` ).prop('checked', false);
        this.renderSelectedColumns();
    }

    renderSelectedColumns() {

        const $selectedColumnsContainer = $(ColumnsForm._selectors.selectedColumnsContainer);
        $selectedColumnsContainer.html("");
        const $form = $(ColumnsForm._selectors.columnsForm);
        for (let fieldData of $form.serializeArray()) {
            let value = fieldData.value;
            let label = $form.find(`input[value=${value}]`).attr('data-label');
            const html = selectedColumnTemplate(label, value);
            const $selectedColumnTemplate = $($.parseHTML(html));
            $selectedColumnsContainer.append($selectedColumnTemplate);
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
        this.renderSelectedColumns();
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
        let $propertyListForm = this.$wrapper.find('.js-property-list-form');
        const html = listTemplate(propertyGroup);
        const $list = $($.parseHTML(html));
        $propertyListForm.append($list);

        var options = {
            valueNames: [ 'label' ],
            // Since there are no elements in the list, this will be used as template.
            item: `<li><div class="form-check"><input class="form-check-input js-property-checkbox" name="properties[]" type="checkbox" value="" id=""><label class="form-check-label" for=""><p class="label"></p></label></div></li>`
        };

        this.lists.push(new List(`list-${propertyGroup.id}`, options, properties));

        $( `#list-${propertyGroup.id} li input[type="checkbox"]` ).each((index, element) => {
            $(element).attr('data-label', properties[index].label);
            $(element).attr('id', `property-${properties[index].id}`);
            $(element).next().attr('for', `property-${properties[index].id}`);
            $(element).val(properties[index].id);
        });

    }

    _addSelectedColumn() {

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
        <form class="js-property-list-form"></form>
        </div>
        <div class="js-selected-columns-container col-md-6"></div>
    </div>
`;


const selectedColumnTemplate = (label, id) => `
    <div class="card js-selected-column">
        <div class="card-body">${label}<span><i class="fa fa-times js-remove-selected-column-icon" data-property-id="${id}" aria-hidden="true"></i></span></div>
    </div>
`;

export default ColumnsForm;