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

class ConnectObjectForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, parentConnectionUid) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.parentConnectionUid = parentConnectionUid;
        this.connectedObjects = [];
        this.connectedProperties = [];
        this.propertySelect = null;

        this.$wrapper.on(
            'click',
            ConnectObjectForm._selectors.connectedObjectButton,
            this.handleApplyConnectedObjectButtonClick.bind(this)
        );

        this.$wrapper.html(ConnectObjectForm.markup());

        this.loadConnectedObjects().then((data) => {
            this.connectedObjects = data.data.custom_objects;
            let options = [];
            for(let i = 0; i < this.connectedObjects.length; i++) {
                let option = this.connectedObjects[i];
                options.push({value: option.id, name: option.label});
            }
            $('#js-select-connected-object').selectize({
                valueField: 'value',
                labelField: 'name',
                searchField: ['name'],
                options: options,
                placeholder: 'Select an object to connect'
            }).on('change', this.handleConnectableObjectChange.bind(this));
        });

        $('#js-select-join-type').selectize({});
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            connectedObjectButton: '.js-apply-connected-object-button',
            connectObjectForm: '#js-connect-object-form'
        }
    }

    loadConnectedObjects() {
        return new Promise((resolve, reject) => {
            const url = Routing.generate('get_connectable_objects', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});
            $.ajax({
                url: url,
                method: 'GET'
            }).then(data => {
                resolve(data);
            }).catch(jqXHR => {
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }

    handleConnectableObjectChange(e) {
        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }
        let customObjectId =  $(e.target).val();
        let data = {};
        data.customObjectId = customObjectId;

        this.getConnectableProperties(data).then((data) => {
            debugger;
            this.connectedProperties = data.data.properties;
            let options = [];
            for(let i = 0; i < this.connectedProperties.length; i++) {
                let option = this.connectedProperties[i];
                options.push({value: option.id, name: option.label});
            }

            debugger;
          if(!this.propertySelect) {
              this.propertySelect = $('#js-select-property').selectize({
                  valueField: 'value',
                  labelField: 'name',
                  searchField: ['name'],
                  options: options,
                  placeholder: 'Select a property to join on.'
              });
          } else {
              debugger;

              this.propertySelect.selectize()[0].selectize.clear();
              this.propertySelect.selectize()[0].selectize.clearOptions();

              for(var i = 0; i < options.length; i++) {
                  this.propertySelect.selectize()[0].selectize.addOption(options[i]);
                  this.propertySelect.selectize()[0].selectize.addItem(i);
              }
          }
        });
    }


    getConnectableProperties(data) {
        return new Promise((resolve, reject) => {

            const url = Routing.generate('get_connectable_properties', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});

            $.ajax({
                url,
                method: 'GET',
                data: data
            }).then((data, textStatus, jqXHR) => {
                debugger;
                resolve(data);
            }).catch((jqXHR) => {
                debugger;
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }

    handleApplyConnectedObjectButtonClick(e) {
        if(e.cancelable) {
            e.preventDefault();
        }
        const $form = $(ConnectObjectForm._selectors.connectObjectForm);
        let formData = new FormData($form.get(0));
        var object = {};
        formData.forEach((value, key) => {
            if(!object.hasOwnProperty(key)){
                object[key] = value;
                return;
            }
            if(!Array.isArray(object[key])){
                object[key] = [object[key]];
            }
            object[key].push(value);
        });
        object.connected_property = this.connectedProperties.filter(property => {
            return parseInt(property.id) === parseInt(object.connected_property);
        })[0];
        object.connected_object = this.connectedObjects.filter(connectedObject => {
            return parseInt(connectedObject.id) === parseInt(object.connected_object);
        })[0];
        if(this.parentConnectionUid) {
            object.parentConnectionUid = this.parentConnectionUid;
            object.hasParentConnection = true;
        }
        this.globalEventDispatcher.publish(Settings.Events.REPORT_OBJECT_CONNECTED, object);
    }

    static markup() {
        return `
        <form id="js-connect-object-form">
        <select name="connected_object" id="js-select-connected-object"></select>
        <br>
        <select name="join_type" id="js-select-join-type">
            <option>With</option>
            <option>Without</option>
            <option>With/Without</option>
        </select>
        <br>
        <select style="display:none" name="connected_property" id="js-select-property"></select>
        <br>
        <button type="button" class="btn-primary btn w-100 js-apply-connected-object-button">Connect Object</button>
        </form>
        
    `;
    }
}

export default ConnectObjectForm;