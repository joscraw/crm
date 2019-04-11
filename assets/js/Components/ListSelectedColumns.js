'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import ColumnSearch from "./ColumnSearch";
require('jquery-ui-dist/jquery-ui');

class ListSelectedColumns {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, data, columnOrder) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.data = data;
        this.columnOrder = columnOrder;

        this.globalEventDispatcher.subscribe(
            Settings.Events.LIST_PROPERTY_LIST_ITEM_ADDED,
            this.handlePropertyListItemAdded.bind(this)
        );

     /*   this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_PROPERTY_LIST_ITEM_REMOVED,
            this.handlePropertyListItemRemoved.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_COLUMN_ORDER_UPDATED,
            this.handleReportColumnOrderUpdated.bind(this)
        );


        this.unbindEvents();

        this.$wrapper.on(
            'click',
            ReportSelectedColumns._selectors.removeSelectedColumnIcon,
            this.handleRemoveSelectedColumnIconClicked.bind(this)
        );

        this.setSelectedColumns(this.data, this.columnOrder);

        this.activatePlugins();*/

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
            cursor: 'crosshair',
            update: (event, ui) => {
                let columnOrder = $(event.target).sortable('toArray');

                this.globalEventDispatcher.publish(Settings.Events.REPORT_COLUMN_ORDER_CHANGED, columnOrder);

            }
        });

    }

    unbindEvents() {

        this.$wrapper.off('click', ReportSelectedColumns._selectors.removeSelectedColumnIcon);

    }

    handleRemoveSelectedColumnIconClicked(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        e.stopPropagation();

        let propertyId = $(e.target).attr('data-property-id');
        let joinString = $(e.target).attr('data-joins');
        let joins = JSON.parse(joinString);
        let propertyPath = joins.join('.');

        debugger;
        if(_.has(this.data, propertyPath)) {

            debugger;
            let property = _.get(this.data, propertyPath);

            this.globalEventDispatcher.publish(Settings.Events.REPORT_REMOVE_SELECTED_COLUMN_ICON_CLICKED, property);
        }
    }

    handlePropertyListItemAdded(data, columnOrder) {

        this.data = data;

        this.columnOrder = columnOrder;

        this.setSelectedColumns(data, columnOrder);
    }

    handlePropertyListItemRemoved(data, columnOrder) {

        this.data = data;

        this.columnOrder = columnOrder;

        this.setSelectedColumns(data, columnOrder);

    }

    handleReportColumnOrderUpdated(data, columnOrder) {

        this.data = data;

        this.columnOrder = columnOrder;

        this.setSelectedColumns(data, columnOrder);

    }

    setSelectedColumns(data, columnOrder) {

        this.$wrapper.html("");

        for(let i = 0; i < columnOrder.length; i ++) {

            let column = columnOrder[i];

            let joins = column.joins.concat([column.uID]);

            this._addSelectedColumn(column.label, column.id, JSON.stringify(joins));

        }

    }


    _addSelectedColumn(label, propertyId, joins) {

        debugger;
        const html = selectedColumnTemplate(label, propertyId, joins);
        const $selectedColumnTemplate = $($.parseHTML(html));
        this.$wrapper.append($selectedColumnTemplate);

        this.activatePlugins();

    }
}

const selectedColumnTemplate = (label, id, joins) => `
    <div class="card js-selected-column" id=${joins}>
        <div class="card-body c-report-widget__card-body">${label}<span><i class="fa fa-times js-remove-selected-column-icon c-report-widget__remove-column-icon" data-property-id="${id}" data-joins='${joins}' aria-hidden="true"></i></span></div>
    </div>
`;


export default ListSelectedColumns;