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

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;

        this.unbindEvents();
        this.bindEvents();

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_OBJECT_CONNECTED_JSON_UPDATED,
            this.refreshConnectedObjects.bind(this)
        );

        this.render();
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

    }

    /**
     * Because this component can keep getting run each time a filter is added
     * you need to remove the handlers otherwise they will keep stacking up
     */
    unbindEvents() {}

    render() {
        this.$wrapper.html(ReportConnectedObjectsList.markup(this));
        this.refreshConnectedObjects();
        $('[data-toggle="tooltip"]').tooltip();
    }

    refreshConnectedObjects(data = {}) {
        debugger;
        if(!_.isEmpty(data)) {
            this.$wrapper.find(ReportConnectedObjectsList._selectors.noConnectionsExistMessage).hide();
        } else {
            this.$wrapper.find(ReportConnectedObjectsList._selectors.noConnectionsExistMessage).show();
        }
        if(!_.has(data, 'joins') || _.isEmpty(data.joins)) {
            return;
        }
        this.$wrapper.find(ReportConnectedObjectsList._selectors.connectedObjects).html("");
        for(let join of data.joins) {
            debugger;
            let connectedObject = join.connected_object,
                connectedProperty = join.connected_property,
                joinType = join.join_type,
                customObjectInternalName = connectedProperty.field.customObject.internalName;
            debugger;
            let text = `${connectedObject.label} ${joinType} ${connectedProperty.label}`;
            const html = listItemTemplate(text, customObjectInternalName);
            const $listTemplate = $($.parseHTML(html));
            this.$wrapper.find(ReportConnectedObjectsList._selectors.connectedObjects).append($listTemplate);
        }
    }

    handleConnectionAddItemButtonClick(e) {
        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }
        debugger;
        const $listItem = $(e.currentTarget);
        let parentConnectionObject = $listItem.attr('data-parent-connection-object');
        debugger;
        new ReportConnectObjectFormModal(this.globalEventDispatcher, this.portalInternalIdentifier, parentConnectionObject);
        /*this.globalEventDispatcher.publish(Settings.Events.REPORT_PROPERTY_LIST_ITEM_CLICKED, property[0]);*/
    }

    static markup() {
        return `
            <h4>Connected Objects <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="Manage your connected objects and add CHILD connections. i.e Contacts with chapters with drops."></i></h4>
            <ul class="js-connected-objects"></ul>
           <div class="alert alert-warning js-no-connections-exist-message" role="alert" style="font-size: 14px">No connections exist yet!</div>
        `;
    }
}

const listItemTemplate = (text, customObjectInternalName) => `
    <li style="margin-top: 10px; margin-bottom: 10px">${text} 
    <i class="fa fa-trash-o js-connection-remove-item" style="float: right; padding-left: 5px"></i> 
    <i class="fa fa-plus js-connection-add-item" style="float: right; padding-left: 5px" data-parent-connection-object="${customObjectInternalName}"></i>
    </li>
`;

export default ReportConnectedObjectsList;