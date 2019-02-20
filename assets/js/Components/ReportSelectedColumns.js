'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import ColumnSearch from "./ColumnSearch";

class ReportSelectedColumns {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, data) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.data = data;

        debugger;

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_PROPERTY_LIST_ITEM_ADDED,
            this.handlePropertyListItemAdded.bind(this)
        );


        this.setSelectedColumns(this.data);

    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            search: '.js-search',
            propertyListItem: '.js-property-list-item',
            list: '.js-list',
            propertyListContainer: '.js-list-property-container',
            selectedColumnsContainer: '.js-selected-columns-container'

        }
    }

    handlePropertyListItemAdded(data) {

        this.data = data;

        this.setSelectedColumns(data);
    }

    setSelectedColumns(data) {

        debugger;

        let columns = [];
        function search(data) {
            debugger;
            for(let key in data) {
                debugger;
                if(data[key] instanceof Array) {
                    search(data[key]);
                } else {
                    columns.push(data[key]);
                }
            }
        }

        search(this.data);

        const $selectedColumnsContainer = $(ReportSelectedColumns._selectors.selectedColumnsContainer);
        $selectedColumnsContainer.html("");

        for (let column of columns) {
            debugger;
            this._addSelectedColumn(column.label, column.id);
        }

    }

    render() {
    }


    _addSelectedColumn(label, propertyId) {
        debugger;
        const $selectedColumnsContainer = $(ReportSelectedColumns._selectors.selectedColumnsContainer);
        const html = selectedColumnTemplate(label, propertyId);
        const $selectedColumnTemplate = $($.parseHTML(html));
        $selectedColumnsContainer.append($selectedColumnTemplate);

        /*this.activatePlugins();
        this._setSelectedColumnsCount();*/
    }
}

const selectedColumnTemplate = (label, id) => `
    <div class="card js-selected-column" id="${id}">
        <div class="card-body">${label}<span><i class="fa fa-times js-remove-selected-column-icon c-column-editor__remove-icon" data-property-id="${id}" aria-hidden="true"></i></span></div>
    </div>
`;


export default ReportSelectedColumns;