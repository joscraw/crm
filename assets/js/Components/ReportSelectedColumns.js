'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import ColumnSearch from "./ColumnSearch";
require('jquery-ui-dist/jquery-ui');

class ReportSelectedColumns {

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

        this.unbindEvents();

        this.$wrapper.on(
            'click',
            ReportSelectedColumns._selectors.removeSelectedColumnIcon,
            this.handleRemoveSelectedColumnIconClicked.bind(this)
        );

        this.setSelectedColumns(this.data);

        this.activatePlugins();

    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            removeSelectedColumnIcon: '.js-remove-selected-column-icon'
        }
    }

    activatePlugins() {

        this.$wrapper.sortable({
            placeholder: "ui-state-highlight",
            cursor: 'crosshair'
        });

    }

    unbindEvents() {

        debugger;
        this.$wrapper.off('click', ReportSelectedColumns._selectors.removeSelectedColumnIcon);

    }

    handleRemoveSelectedColumnIconClicked(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        let propertyId = $(e.target).attr('data-property-id');
        let joinString = $(e.target).attr('data-joins');
        let joins = JSON.parse(joinString);
        let propertyPath = joins.join('.');

        if(_.has(this.data, propertyPath)) {

            let properties = _.get(this.data, propertyPath);

            let property = properties.filter(property => {
                debugger;
                return parseInt(property.id) === parseInt(propertyId);
            });

            if(property.length === 1) {
                this.globalEventDispatcher.publish(Settings.Events.REPORT_REMOVE_SELECTED_COLUMN_ICON_CLICKED, property[0]);
            }
        }
    }

    handlePropertyListItemAdded(data) {

        this.data = data;

        this.setSelectedColumns(data);
    }

    handlePropertyListItemRemoved(data) {

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

        this.$wrapper.html("");

        for (let column of columns) {
            debugger;
            this._addSelectedColumn(column.label, column.id, JSON.stringify(column.joins));
        }

    }


    _addSelectedColumn(label, propertyId, joins) {

        const html = selectedColumnTemplate(label, propertyId, joins);
        const $selectedColumnTemplate = $($.parseHTML(html));
        this.$wrapper.append($selectedColumnTemplate);

        this.activatePlugins();

    }
}

const selectedColumnTemplate = (label, id, joins) => `
    <div class="card js-selected-column" id="${id}">
        <div class="card-body c-report-widget__card-body">${label}<span><i class="fa fa-times js-remove-selected-column-icon c-report-widget__remove-column-icon" data-property-id="${id}" data-joins='${joins}' aria-hidden="true"></i></span></div>
    </div>
`;


export default ReportSelectedColumns;