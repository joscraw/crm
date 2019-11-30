'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import ColumnSearch from "./ColumnSearch";
import StringHelper from "../StringHelper";
import ReportConnectObjectFormModal from "./ReportConnectObjectFormModal";
import ReportAddFilterFormModal from "./ReportAddFilterFormModal";
import FilterHelper from "../FilterHelper";

class ReportConnectedObjectsList {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, data = {}) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.data = data;
        this.unbindEvents();
        this.bindEvents();
        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_OBJECT_CONNECTED_JSON_UPDATED,
            this.refreshConnectedObjects.bind(this)
        );
        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_CONNECTION_REMOVED,
            this.refreshConnectedObjects.bind(this)
        );
        this.render(data);
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            connectedObjects: '.js-connected-objects',
            noConnectionsExistMessage: '.js-no-connections-exist-message',
            connectionRemoveItem: '.js-connection-remove-item',
            connectionAddItem: '.js-connection-add-item'
        }
    }

    bindEvents() {
        this.$wrapper.on(
            'click',
            ReportConnectedObjectsList._selectors.connectionAddItem,
            this.handleConnectionAddItemButtonClick.bind(this)
        );

        this.$wrapper.on(
            'click',
            ReportConnectedObjectsList._selectors.connectionRemoveItem,
            this.handleConnectionRemoveItemButtonClick.bind(this)
        );
    }

    /**
     * Because this component can keep getting run each time a filter is added
     * you need to remove the handlers otherwise they will keep stacking up
     */
    unbindEvents() {}

    render(data) {
        this.$wrapper.html(ReportConnectedObjectsList.markup(this));
        this.refreshConnectedObjects(data);
        $('[data-toggle="tooltip"]').tooltip();
    }

    refreshConnectedObjects(data = {}) {
        debugger;
        this.$wrapper.find(ReportConnectedObjectsList._selectors.connectedObjects).html("");
        if(!_.has(data, 'joins') || _.isEmpty(data.joins) || Object.keys(data.joins).length === 1) {
            this.$wrapper.find(ReportConnectedObjectsList._selectors.noConnectionsExistMessage).show();
            return;
        } else {
            this.$wrapper.find(ReportConnectedObjectsList._selectors.noConnectionsExistMessage).hide();
        }
        for(let uid in data.joins) {
            let join = data.joins[uid];
            if(join.hasParentConnection || !_.has(join, 'connected_property') || !_.has(join, 'join_type')) {
                continue;
            }
            debugger;
            let connectedObject = join.connected_object,
                connectedProperty = join.connected_property,
                joinType = join.join_type,
                customObjectInternalName = connectedProperty.field.customObject.internalName;
            let text = `${connectedObject.label} ${joinType} ${connectedProperty.label}`;
            const html = connectionTemplate(text, customObjectInternalName, uid);
            const $listTemplate = $($.parseHTML(html));
            this.$wrapper.find(ReportConnectedObjectsList._selectors.connectedObjects).append($listTemplate);
            // render any child connections here
            debugger;
            if(_.has(join, 'childConnections') && !_.isEmpty(join.childConnections)) {
                debugger;
                let childConnections = _.get(join, 'childConnections');
                for(let uid in childConnections) {
                    let childConnection = childConnections[uid];
                    let connectedObject = childConnection.connected_object,
                        connectedProperty = childConnection.connected_property,
                        joinType = childConnection.join_type,
                        customObjectInternalName = connectedProperty.field.customObject.internalName;
                    let text = `${connectedObject.label} ${joinType} ${connectedProperty.label}`;
                    const html = childConnectionTemplate(text, customObjectInternalName, uid);
                    const $listTemplate = $($.parseHTML(html));
                    this.$wrapper.find(ReportConnectedObjectsList._selectors.connectedObjects).find('.js-child-connections').append($listTemplate);
                }
            }
        }
    }

    handleConnectionAddItemButtonClick(e) {
        if(e.cancelable) {
            e.preventDefault();
        }
        const $listItem = $(e.currentTarget);
        let parentConnectionObject = $listItem.attr('data-parent-connection-object');
        let parentConnectionUid = $listItem.attr('data-parent-connection-uid');
        new ReportConnectObjectFormModal(this.globalEventDispatcher, this.portalInternalIdentifier, parentConnectionObject, parentConnectionUid);
    }

    handleConnectionRemoveItemButtonClick(e) {
        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }
        const $listItem = $(e.currentTarget);
        let connectionUid = $listItem.attr('data-connection-uid');
        this.globalEventDispatcher.publish(Settings.Events.REPORT_REMOVE_CONNECTION_BUTTON_PRESSED, connectionUid);
    }

    static markup() {
        return `
            <h4>Connected Objects <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="Manage your connected objects and add CHILD connections. i.e Contacts with chapters with drops."></i></h4>
            <ul class="js-connected-objects"></ul>
           <div class="alert alert-warning js-no-connections-exist-message" role="alert" style="font-size: 14px">No connections exist yet!</div>
        `;
    }
}

const connectionTemplate = (text, customObjectInternalName, uid) => `
    <li style="margin-top: 10px; margin-bottom: 10px">${text} 
    <i class="fa fa-trash-o js-connection-remove-item" style="float: right; padding-left: 5px" data-connection-uid="${uid}"></i> 
    <i class="fa fa-plus js-connection-add-item" style="float: right; padding-left: 5px" data-parent-connection-uid="${uid}" data-parent-connection-object="${customObjectInternalName}"></i>
    <ul class="js-child-connections"></ul>
    </li>
`;

const childConnectionTemplate = (text, customObjectInternalName, uid) => `
    <li style="margin-top: 10px; margin-bottom: 10px">${text} 
    <i class="fa fa-trash-o js-connection-remove-item" style="float: right; padding-left: 5px" data-connection-uid="${uid}"></i> 
    </li>
`;

export default ReportConnectedObjectsList;