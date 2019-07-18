'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import ColumnSearch from "./ColumnSearch";
require('jquery-ui-dist/jquery-ui');

class WorkflowTriggerSelectedTriggers {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, data/*, columnOrder*/) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.data = data;
/*

        this.columnOrder = columnOrder;

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
            Settings.Events.LIST_PROPERTY_LIST_ITEM_ADDED,
            this.handlePropertyListItemAdded.bind(this)
        ));

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
            Settings.Events.LIST_PROPERTY_LIST_ITEM_REMOVED,
            this.handlePropertyListItemRemoved.bind(this)
        ));

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
            Settings.Events.LIST_COLUMN_ORDER_UPDATED,
            this.handleListColumnOrderUpdated.bind(this)
        ));

        this.unbindEvents();

        this.$wrapper.on(
            'click',
            ListSelectedColumns._selectors.removeSelectedColumnIcon,
            this.handleRemoveSelectedColumnIconClicked.bind(this)
        );
*/

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
                Settings.Events.WORKFLOW_TRIGGER_ADDED,
                this.handleWorkflowTriggerAdded.bind(this)
            ));

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
            cursor: 'crosshair',
            update: (event, ui) => {
                let columnOrder = $(event.target).sortable('toArray');

                this.globalEventDispatcher.publish(Settings.Events.LIST_COLUMN_ORDER_CHANGED, columnOrder);

            }
        });

    }

    unbindEvents() {

        this.$wrapper.off('click', ListSelectedColumns._selectors.removeSelectedColumnIcon);

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

            this.globalEventDispatcher.publish(Settings.Events.LIST_REMOVE_SELECTED_COLUMN_ICON_CLICKED, property);
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

    handleWorkflowTriggerAdded(triggers) {

        debugger;
       /* this.data = data;

        this.columnOrder = columnOrder;*/

        this.setSelectedColumns(triggers);

    }

    setSelectedColumns(data) {

        debugger;
        this.$wrapper.html("");

        if(_.isEmpty(data, true)) {
            this.$wrapper.html(emptyListTemplate());
            return;
        }

        for(let key in data) {

            let trigger = data[key];

            /*let joins = column.joins.concat([column.uID]);*/

            /*this._addSelectedColumn(column.label, column.id, JSON.stringify(joins));*/

            this._addSelectedColumn(trigger);

        }

    }


    _addSelectedColumn(trigger) {

        debugger;
        const html = selectedColumnTemplate(trigger);
        const $selectedColumnTemplate = $($.parseHTML(html));
        this.$wrapper.append($selectedColumnTemplate);

        this.activatePlugins();

    }
}

const selectedColumnTemplate = ({label}) => `
    <div class="card js-selected-column"}>
        <div class="card-body c-report-widget__card-body">${label}<span><i class="fa fa-times js-remove-selected-column-icon c-report-widget__remove-column-icon" aria-hidden="true"></i></span></div>
    </div>
`;

/**
 * @return {string}
 */
const emptyListTemplate = () => `
    <h1 style="text-align: center; margin-top: 300px">Add a trigger on the left to get started...</h1>
`;



export default WorkflowTriggerSelectedTriggers;