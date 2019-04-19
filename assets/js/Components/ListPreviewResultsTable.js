'use strict';

import Routing from '../Routing';
import Settings from '../Settings';
import $ from "jquery";
import DeletePropertyButton from "./DeletePropertyButton";
import swal from "sweetalert2";
import BulkEditFormModal from "./BulkEditFormModal";

require( 'datatables.net-bs4' );
require( 'datatables.net-responsive-bs4' );
require( 'datatables.net-responsive-bs4/css/responsive.bootstrap4.css' );
require( 'datatables.net-bs4/css/dataTables.bootstrap4.css' );


class ListPreviewResultsTable {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     * @param data
     * @param columnOrder
     * @param listType
     * @param listId
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, data, columnOrder, listType, listId) {


        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.data = data;
        this.columnOrder = columnOrder;
        this.listType = listType;
        this.listId = listId;

        this.$tableHeader = null;
        this.records = [];

        this.globalEventDispatcher.singleSubscribe(
            Settings.Events.LIST_PREVIEW_RESULTS_BUTTON_CLICKED,
            this.handleListPreviewResultsButtonClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.LIST_FILTER_ITEM_ADDED,
            this.listFilterItemAddedHandler.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.LIST_FILTER_ITEM_REMOVED,
            this.listFilterItemRemovedHandler.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.LIST_PROPERTY_LIST_ITEM_REMOVED,
            this.listPropertyListItemRemovedHandler.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.LIST_PROPERTY_LIST_ITEM_ADDED,
            this.listPropertyListItemAddedHandler.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.LIST_COLUMN_ORDER_UPDATED,
            this.listColumnOrderUpdatedHandler.bind(this)
        );

        this.globalEventDispatcher.singleSubscribe(
            Settings.Events.BULK_EDIT_SUCCESSFUL,
            this.handleBulkEditSuccessful.bind(this)
        );

        this.unbindEvents();

        this.bindEvents();

        this.render();

    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            table: '.js-table',
            recordCheckbox: '.js-record-checkbox',
            recordCheckboxChecked: '.js-record-checkbox:checked',
            selectAllRecordsCheckbox: '.js-select-all-records-checkbox',
            bulkEditButton: '.js-bulk-edit-button'
        }
    }


    unbindEvents() {

        this.$wrapper.off('change', ListPreviewResultsTable._selectors.recordCheckbox);

        this.$wrapper.off('change', ListPreviewResultsTable._selectors.selectAllRecordsCheckbox);

        this.$wrapper.off('click', ListPreviewResultsTable._selectors.bulkEditButton);

    }

    bindEvents() {

        this.$wrapper.on('change', ListPreviewResultsTable._selectors.recordCheckbox, this.handleRecordCheckboxChange.bind(this));

        this.$wrapper.on('change', ListPreviewResultsTable._selectors.selectAllRecordsCheckbox, this.handleSelectAllRecordsCheckboxChange.bind(this));

        this.$wrapper.on('click', ListPreviewResultsTable._selectors.bulkEditButton, this.handleBulkEditButtonClicked.bind(this));

    }

    listFilterItemAddedHandler(data) {

        this.data = data;
    }

    listFilterItemRemovedHandler(data) {

        this.data = data;
    }

    listPropertyListItemRemovedHandler(data, columnOrder) {

        this.data = data;
        this.columnOrder = columnOrder;
    }

    listColumnOrderUpdatedHandler(data, columnOrder) {

        this.data = data;
        this.columnOrder = columnOrder;
    }

    listPropertyListItemAddedHandler(data, columnOrder) {

        this.data = data;
        this.columnOrder = columnOrder;
    }

    handleListPreviewResultsButtonClicked() {

        this.loadReportPreview().then((data) => {

            this.activatePlugins(data.data, this.columnOrder);

        });

    }

    handleBulkEditSuccessful() {

        this.loadReportPreview().then((data) => {

            this.activatePlugins(data.data, this.columnOrder);

        });

    }

    loadReportPreview() {
        return new Promise((resolve, reject) => {
            debugger;

            const url = Routing.generate('get_list_preview', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});

            $.ajax({
                url: url,
                data: {data: this.data, columnOrder: this.columnOrder, listType: this.listType, listId: this.listId},
                method: 'POST',
            }).then(data => {
                debugger;
                resolve(data);
            }).catch(jqXHR => {
                debugger;
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }

    handleRecordCheckboxChange(e) {

        debugger;

        if(e.cancelable) {
            e.preventDefault();
        }

        const html = tableActionsTemplate();
        const $tableActionsTemplate = $($.parseHTML(html));

        if ($(ListPreviewResultsTable._selectors.recordCheckboxChecked).length > 0) {

            this.$wrapper.find(ListPreviewResultsTable._selectors.table).first().find('thead').replaceWith($tableActionsTemplate);

        }
        else {
            this.$wrapper.find(ListPreviewResultsTable._selectors.table).first().find('thead').replaceWith(this.$tableHeader);

            this.$wrapper.find(ListPreviewResultsTable._selectors.selectAllRecordsCheckbox).first().prop('checked', false);
        }

        this.updateRecords();
    }

    handleSelectAllRecordsCheckboxChange(e) {

        debugger;

        if(e.cancelable) {
            e.preventDefault();
        }

        const html = tableActionsTemplate();
        const $tableActionsTemplate = $($.parseHTML(html));

        if(!$(ListPreviewResultsTable._selectors.selectAllRecordsCheckbox).first().is(':checked')) {

            this.$wrapper.find(ListPreviewResultsTable._selectors.table).first().find('thead').replaceWith(this.$tableHeader);

            this.$wrapper.find(ListPreviewResultsTable._selectors.recordCheckbox).prop('checked', false);

            this.$wrapper.find(ListPreviewResultsTable._selectors.selectAllRecordsCheckbox).first().prop('checked', false);

        } else {

            this.$wrapper.find(ListPreviewResultsTable._selectors.table).first().find('thead').replaceWith($tableActionsTemplate);

            this.$wrapper.find(ListPreviewResultsTable._selectors.recordCheckbox).prop('checked', true);

            this.$wrapper.find(ListPreviewResultsTable._selectors.selectAllRecordsCheckbox).first().prop('checked', true);

        }

        this.updateRecords();
    }

    updateRecords() {

        this.records = [];

        this.$wrapper.find(ListPreviewResultsTable._selectors.recordCheckbox).each((index, element) => {

            let $element  = $(element);

            if($element.is(":checked")) {

                this.records.push($element.attr('data-record-id'));
            }

        });

    }

    handleBulkEditButtonClicked(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        new BulkEditFormModal(this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, this.records);

    }

    activatePlugins(data, columnOrder) {

        let columns = [
            {
                data: null,
                title: `<div class="form-check"><input class="form-check-input js-select-all-records-checkbox c-table__checkbox" type="checkbox"><label class="form-check-label" for=""><p class="label"></p></label></div>`,
                mRender: (data, type, row) => {
                    debugger;
                    return `
                    <div class="form-check"><input class="form-check-input js-record-checkbox c-table__checkbox" data-record-id="${data['id']}" type="checkbox"><label class="form-check-label" for=""><p class="label"></p></label></div>
                    `;
                },
                "targets"  : 'no-sort',
                "orderable": false,
            }
        ];

        for(let c of columnOrder) {

            columns.push({data: c.internalName, name: c.internalName, title: c.label});

        }

        $('#listPreviewResultsTable').DataTable({
            "order": [],
            "paging": true,
            "destroy": true,
            /*"responsive": true,*/
            "scrollX": true,
            "searching":true,
            "language": {
                "emptyTable": "No results found.",
            },
            /*
            the "dom" property determines what components DataTables shows by default

            Possible Flags:

            l - length changing input control
            f - filtering input
            t - The table!
            i - Table information summary
            p - pagination control
            r - processing display element

            For more information on the "dom" property and how to use it
            https://datatables.net/reference/option/dom
            */
            /*"dom": "rt",*/
            "columns": columns,
            "data": data,
            "initComplete": () => {

                if(!this.$tableHeader) {
                    this.$tableHeader = this.$wrapper.find(ListPreviewResultsTable._selectors.table).first().find('thead');
                }

                this.$wrapper.find(ListPreviewResultsTable._selectors.selectAllRecordsCheckbox).prop('checked', false);
            }
        });

    }

    render() {
        this.$wrapper.html(ListPreviewResultsTable.markup(this));
    }

    static markup() {
        return `
            <table id="listPreviewResultsTable" class="js-table table table-striped c-table c-table--with-actions" style="width:100%">
                <thead>
                </thead>
                <tbody>
                </tbody>
            </table>
        `;
    }
}

const tableActionsTemplate = () => `
    <thead>
        <tr role="row">
            <th rowspan="1" colspan="1" style="width: 20px; vertical-align:middle">
                <div class="form-check">
                    <input class="form-check-input js-select-all-records-checkbox c-table__checkbox" type="checkbox">
                    <label class="form-check-label" for="">
                        <p class="label"></p>
                    </label>
                </div>
            </th>
            <th rowspan="1" colspan="10000" style="padding-left: 0">
                <button type="button" class="btn btn-link js-bulk-edit-button"><i class="fa fa-pencil"></i> Edit</button>
            </th>
        </tr>
    </thead>
`;

export default ListPreviewResultsTable;