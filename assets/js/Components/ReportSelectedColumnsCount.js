'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import ColumnSearch from "./ColumnSearch";

class ReportSelectedColumnsCount {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, data) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.data = data;

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_PROPERTY_LIST_ITEM_ADDED,
            this.handlePropertyListItemAdded.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_PROPERTY_LIST_ITEM_REMOVED,
            this.handlePropertyListItemRemoved.bind(this)
        );

        this.render()
    }

    handlePropertyListItemAdded(data) {

        this.data = data;

        this.render();
    }

    handlePropertyListItemRemoved(data) {

        this.data = data;

        this.render();

    }

    render() {

        let columns = [];
        function search(data) {

            for(let key in data) {

                if(data[key] instanceof Array) {
                    search(data[key]);
                } else {
                    columns.push(data[key]);
                }
            }
        }

        search(this.data);

        this.$wrapper.html("");

        let count = columns.length;

        this.$wrapper.html(`Selected Columns: ${count}`);
    }

}

export default ReportSelectedColumnsCount;