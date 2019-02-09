'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';
import List from 'list.js';
import PropertySearch from "./PropertySearch";
require('jquery-ui-dist/jquery-ui');

class DefaultPropertiesForm {

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
        this.searchValue = '';
        this.lists = [];

        this.$wrapper.on(
            'submit',
            DefaultPropertiesForm._selectors.selectedPropertiesForm,
            this.handleNewFormSubmit.bind(this)
        );

        this.$wrapper.on(
            'change',
            DefaultPropertiesForm._selectors.propertyCheckbox,
            this.handlePropertyCheckboxChanged.bind(this)
        );

        this.$wrapper.on(
            'click',
            DefaultPropertiesForm._selectors.removeSelectedPropertyIcon,
            this.handleRemoveSelectedPropertyIconClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.PROPERTY_SEARCH_KEY_UP,
            this.applySearch.bind(this)
        );

        this.loadProperties().then(data => {
            this.render(data).then(() => {
                this._setSelectedPropertyCount();
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
            selectedPropertiesContainer: '.js-selected-properties-container',
            removeSelectedPropertyIcon: '.js-remove-selected-property-icon',
            selectedPropertyCount: '.js-selected-properties-count'
        }
    }

    activatePlugins() {
        const $selectedPropertiesContainer = $(DefaultPropertiesForm._selectors.selectedPropertiesContainer);
        $selectedPropertiesContainer.sortable({
            placeholder: "ui-state-highlight",
            cursor: 'crosshair'
        });
        $selectedPropertiesContainer.disableSelection();
    }

    handleRemoveSelectedPropertyIconClicked(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        let propertyId = $(e.target).data('propertyId');
        this._removeSelectedProperty(propertyId);

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
            const url = Routing.generate('get_default_properties', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});

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
            debugger;
            const html = mainTemplate();
            const $mainTemplate = $($.parseHTML(html));
            this.$wrapper.append($mainTemplate);

            for(let key in data.data.property_groups) {
                if(data.data.property_groups.hasOwnProperty(key)) {
                    let propertyGroup = data.data.property_groups[key];
                    let properties = data.data.properties[key];
                    this._addList(propertyGroup, properties);
                }
            }

            new PropertySearch(this.$wrapper.find('.js-search-container'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, "Search for a property...");

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

        const $selectedPropertiesContainer = $(DefaultPropertiesForm._selectors.selectedPropertiesContainer);

        let newOrderArray = $selectedPropertiesContainer.sortable('toArray');

        const $form = $(e.currentTarget);
        let formData = new FormData($form.get(0));

        for (let i = 0; i < newOrderArray.length; i++) {
            formData.append('selected_properties[]', newOrderArray[i]);
        }

        this._saveProperties(formData)
            .then((data) => {
                debugger;
                swal("Whoop whoop!", `Default Properties successfully updated!`, "success");
                this.globalEventDispatcher.publish(Settings.Events.DEFAULT_PROPERTIES_UPDATED);
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
            this._addSelectedProperty(label, propertyId);
        } else {
            this._removeSelectedProperty(propertyId);
        }
    }

    /**
     * @param data
     * @return {Promise<any>}
     * @private
     */
    _saveProperties(data) {

        return new Promise( (resolve, reject) => {

            const url = Routing.generate('set_default_properties', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});

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

        // List.js is used to render the list on the left and to allow searching of said list
        var options = {
            valueNames: [ 'label' ],
            // Since there are no elements in the list, this will be used as template.
            item: `<li><div class="form-check"><input class="form-check-input js-property-checkbox c-column-editor__checkbox" type="checkbox" value="" id=""><label class="form-check-label c-column-editor__checkbox-label" for=""><p class="label"></p></label></div></li>`
        };

        this.lists.push(new List(`list-${propertyGroup.id}`, options, properties));

        $( `#list-${propertyGroup.id} li input[type="checkbox"]` ).each((index, element) => {
            $(element).attr('data-label', properties[index].label);
            $(element).attr('data-property-id', properties[index].id);

            // Used to make sure when you click the label the checkbox gets checked
            $(element).attr('id', `property-${properties[index].id}`);
            $(element).next().attr('for', `property-${properties[index].id}`);
        });

        // Make sure the checkboxes are either checked or not checked
        debugger;
        let selectedProperties = {};
        for(let i = 0; i < properties.length; i++) {
            debugger;
            let property = properties[i];
            if(property.isDefaultProperty) {
                $( `#list-${propertyGroup.id} li [data-property-id='${property.id}']` ).prop('checked', true);
                selectedProperties[property.propertyOrder] = {'label': property.label, 'id': property.id};
            } else {
                $( `#list-${propertyGroup.id} li [data-property-id='${property.id}']` ).prop('checked', false);
            }
        }

        // make sure the selected properties appear in the correct order on the right side of the modal
        for(let order in selectedProperties) {
            debugger;
            this._addSelectedProperty(selectedProperties[order].label, selectedProperties[order].id);
        }

    }

    _addSelectedProperty(label, propertyId) {
        debugger;
        const $container = $(DefaultPropertiesForm._selectors.selectedPropertiesContainer);
        const html = selectedColumnTemplate(label, propertyId);
        const $template = $($.parseHTML(html));
        $container.append($template);

        this.activatePlugins();
        this._setSelectedPropertyCount();
    }

    _removeSelectedProperty(propertyId) {
        const $container = $(DefaultPropertiesForm._selectors.selectedPropertiesContainer);
        $container.find(`[data-property-id="${propertyId}"]`).closest('.js-selected-property').remove();
        this._setSelectedPropertyCount();
    }

    _setSelectedPropertyCount() {
        const $container = $(DefaultPropertiesForm._selectors.selectedPropertiesContainer);
        let count = $container.find('.js-selected-property').length;
        $(DefaultPropertiesForm._selectors.selectedPropertyCount).html(`Selected Properties: ${count}`);
    }
}

const listTemplate = ({id, label}) => `
    <div id="list-${id}" class="js-list">
      <p>${label}</p>
      <ul class="list"></ul>
    </div>
    
`;

const mainTemplate = () => `
    <div class="row c-column-editor">
        <div class="col-md-6">
            <div class="js-search-container c-column-editor__search-container"></div>
            <div class="js-property-list c-column-editor__property-list"></div>
        </div>
        <div class="col-md-6">
            <div class="js-selected-properties-count c-column-editor__selected-columns-count"></div>
            <div class="js-selected-properties-container c-column-editor__selected-columns"></div>
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
    <div class="card js-selected-property" id="${id}">
        <div class="card-body">${label}<span><i class="fa fa-times js-remove-selected-property-icon c-column-editor__remove-icon" data-property-id="${id}" aria-hidden="true"></i></span></div>
    </div>
`;

export default DefaultPropertiesForm;