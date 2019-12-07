'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';
import List from 'list.js';
import ColumnSearch from "./ColumnSearch";
require('jquery-ui-dist/jquery-ui');

class RecordImportForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName) {

        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.searchValue = '';
        this.lists = [];

     /*   this.$wrapper.on(
            'submit',
            ImportForm._selectors.selectedPropertiesForm,
            this.handleNewFormSubmit.bind(this)
        );

        this.$wrapper.on(
            'click',
            ImportForm._selectors.removeSelectedColumnIcon,
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
*/
       /* this.$wrapper.on(
            'submit',
            RecordImportForm._selectors.form,
            this.handleNewFormSubmit.bind(this)
        );*/

        this.$wrapper.on(
            'change',
            RecordImportForm._selectors.importFileField,
            this.handleFileFieldChange.bind(this)
        );

     this.loadForm();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            importFileField: '.js-import-file-field',
            form: '.js-record-import-form'
            /*propertyCheckbox: '.js-property-checkbox',
            selectedColumnsContainer: '.js-selected-columns-container',
            removeSelectedColumnIcon: '.js-remove-selected-column-icon',
            selectedColumnsCount: '.js-selected-columns-count'*/
        }
    }

    loadForm() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: Routing.generate('record_import_form', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName}),
            }).then(data => {
                this.$wrapper.html(data.formMarkup);
                resolve(data);
            }).catch(errorData => {
                reject(errorData);
            });
        });
    }

    activatePlugins() {
        const $selectedColumnsContainer = $(RecordImportForm._selectors.selectedColumnsContainer);
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

        this.$wrapper.find('.js-column-list').find(`[data-property-id="${propertyId}"]`).prop('checked', false);

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
            const url = Routing.generate('properties_for_columns', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});

            $.ajax({
                url: url
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

            debugger;
            new ColumnSearch(this.$wrapper.find('.js-search-container'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, "Search for a column...");

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

        const $selectedColumnsContainer = $(RecordImportForm._selectors.selectedColumnsContainer);

        let newOrderArray = $selectedColumnsContainer.sortable('toArray');

        const $form = $(e.currentTarget);
        let formData = new FormData($form.get(0));

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

    handleFileFieldChange(e) {
        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }
        const $selectedColumnsContainer = $(RecordImportForm._selectors.selectedColumnsContainer);
        let formData = new FormData();
        formData.append($(e.target).attr('name'), e.target.files[0]);
        /* const formData = {};
         let files = e.target.files;
         formData[$(e.target).attr('name')] = files;
         formData['skip_validation'] = true;*/
        if(e.cancelable) {
            e.preventDefault();
        }
      /*  const $form = $(RecordImportForm._selectors.form);
        let formData = new FormData($form.get(0));*/
        Pace.start();
        this._import(formData)
            .then((data) => {
                debugger;
                this.$wrapper.html(data.formMarkup);
                /*this.globalEventDispatcher.publish(Settings.Events.BINGO_CARDS_GENERATED, data.urlToDownload);*/
            }).catch((errorData) => {
            debugger;
            $('.js-column-mapper').replaceWith(
                // ... with the returned one from the AJAX response.
                $(errorData.formMarkup).find('.js-column-mapper')
            );
        });
    }

    /**
     * @param data
     * @return {Promise<any>}
     * @private
     */
    _import(data) {
        return new Promise( (resolve, reject) => {
            const url = Routing.generate('record_import_form', {'internalIdentifier' : this.portalInternalIdentifier, 'internalName' : this.customObjectInternalName});
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

    /**
     * @param data
     * @return {Promise<any>}
     * @private
     */
    _saveColumns(data) {

        return new Promise( (resolve, reject) => {

            const url = Routing.generate('set_property_columns', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});

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
        let $propertyList = this.$wrapper.find('.js-column-list');
        const html = listTemplate(propertyGroup);
        const $list = $($.parseHTML(html));
        $propertyList.append($list);

        var options = {
            valueNames: [ 'label' ],
            // Since there are no elements in the list, this will be used as template.
            item: `<li><div class="form-check"><input class="form-check-input js-property-checkbox c-column-editor__checkbox" type="checkbox" value="" id=""><label class="form-check-label c-column-editor__checkbox-label" for=""><p class="label"></p></label></div></li>`
        };

        this.lists.push(new List(`list-columns-${propertyGroup.id}`, options, properties));

        $( `#list-columns-${propertyGroup.id} li input[type="checkbox"]` ).each((index, element) => {
            $(element).attr('data-label', properties[index].label);
            $(element).attr('data-property-id', properties[index].id);

            // Used to make sure when you click the label the checkbox gets checked
            $(element).attr('id', `property-${properties[index].id}`);
            $(element).next().attr('for', `property-${properties[index].id}`);
        });

        let selectedColumns = {};
        debugger;
        for(let i = 0; i < properties.length; i++) {
            debugger;
            let property = properties[i];

            if(property.isColumn) {
                debugger;
                $( `#list-columns-${propertyGroup.id} li [data-property-id='${property.id}']` ).prop('checked', true);
                selectedColumns[property.columnOrder] = {'label': property.label, 'id': property.id};
            } else {
                $( `#list-columns-${propertyGroup.id} li [data-property-id='${property.id}']` ).prop('checked', false);
            }
        }

        // make sure the selected columns appear in the correct order
        debugger;
        for(let order in selectedColumns) {
            this._addSelectedColumn(selectedColumns[order].label, selectedColumns[order].id);
        }

    }

    _addSelectedColumn(label, propertyId) {
        debugger;
        const $selectedColumnsContainer = $(RecordImportForm._selectors.selectedColumnsContainer);
        const html = selectedColumnTemplate(label, propertyId);
        const $selectedColumnTemplate = $($.parseHTML(html));
        $selectedColumnsContainer.append($selectedColumnTemplate);

        this.activatePlugins();
        this._setSelectedColumnsCount();
    }

    _removeSelectedColumn(propertyId) {
        const $selectedColumnsContainer = $(RecordImportForm._selectors.selectedColumnsContainer);
        $selectedColumnsContainer.find(`[data-property-id="${propertyId}"]`).closest('.js-selected-column').remove();
        this._setSelectedColumnsCount();
    }

    _setSelectedColumnsCount() {
        const $selectedColumnsContainer = $(RecordImportForm._selectors.selectedColumnsContainer);
        let count = $selectedColumnsContainer.find('.js-selected-column').length;
        $(RecordImportForm._selectors.selectedColumnsCount).html(`Selected Columns: ${count}`);
    }
}

const listTemplate = ({id, label}) => `
    <div id="list-columns-${id}" class="js-list">
      <p>${label}</p>
      <ul class="list"></ul>
    </div>
    
`;

const mainTemplate = () => `
    <div class="row c-column-editor">
        <div class="col-md-6">
            <div class="js-search-container c-column-editor__search-container"></div>
            <div class="js-column-list c-column-editor__property-list"></div>
        </div>
        <div class="col-md-6">
            <div class="js-selected-columns-count c-column-editor__selected-columns-count"></div>
            <div class="js-selected-columns-container c-column-editor__selected-columns"></div>
        </div>
        
        <div class="col-md-6 c-column-editor__footer">
            <form class="js-selected-properties-form">
                <input type="hidden" value="" class="js-sorted-properties" name="sortedProperties">
                <button type="submit" class="btn-primary btn">Submit</button>
            </form>
        </div>
      
    </div>
`;


const selectedColumnTemplate = (label, id) => `
    <div class="card js-selected-column" id="${id}">
        <div class="card-body">${label}<span><i class="fa fa-times js-remove-selected-column-icon c-column-editor__remove-icon" data-property-id="${id}" aria-hidden="true"></i></span></div>
    </div>
`;

export default RecordImportForm;