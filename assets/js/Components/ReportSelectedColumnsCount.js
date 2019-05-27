'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import ColumnSearch from "./ColumnSearch";

class ReportSelectedColumnsCount {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, data, columnOrder) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.data = data;
        this.columnOrder = columnOrder;

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_PROPERTY_LIST_ITEM_ADDED,
            this.handlePropertyListItemAdded.bind(this)
        ));

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_PROPERTY_LIST_ITEM_REMOVED,
            this.handlePropertyListItemRemoved.bind(this)
        ));

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_COLUMN_ORDER_UPDATED,
            this.handleReportColumnOrderUpdated.bind(this)
        ));

        this.render()
    }

    handlePropertyListItemAdded(data, columnOrder) {

        this.data = data;
        this.columnOrder = columnOrder;

        this.render();
    }

    handlePropertyListItemRemoved(data, columnOrder) {

        this.data = data;
        this.columnOrder = columnOrder;

        this.render();

    }

    /**
     * This isn't completely necessary but mine as well give this component a fresh
     * version of the columnOrder and data object even when changing the order
     *
     * @param data
     * @param columnOrder
     */
    handleReportColumnOrderUpdated(data, columnOrder) {

        this.data = data;
        this.columnOrder = columnOrder;

        this.render();

    }

    render() {

        this.$wrapper.html("");

        let count = this.columnOrder.length;

        this.$wrapper.html(`Selected Columns: ${count}`);
    }

}

export default ReportSelectedColumnsCount;