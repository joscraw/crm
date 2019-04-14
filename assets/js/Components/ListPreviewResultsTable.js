'use strict';

import Routing from '../Routing';
import Settings from '../Settings';
import $ from "jquery";
import DeletePropertyButton from "./DeletePropertyButton";
import BulkEditRecordFormModal from "./BulkEditRecordFormModal";

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
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName) {

        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.$tableHeader = null;
        this.records = [];

        this.globalEventDispatcher.subscribe(
            Settings.Events.LIST_PREVIEW_RESULTS_LOADED,
            this.listPreviewResultsLoaded.bind(this)
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

    handleRecordCheckboxChange(e) {

        debugger;

        if(e.cancelable) {
            e.preventDefault();
        }

        const html = tableActionsTemplate();
        const $tableActionsTemplate = $($.parseHTML(html));

        if ($(ListPreviewResultsTable._selectors.recordCheckboxChecked).length > 0) {

            this.$wrapper.find(ListPreviewResultsTable._selectors.table).find('thead').replaceWith($tableActionsTemplate);

        }
        else {
            this.$wrapper.find(ListPreviewResultsTable._selectors.table).find('thead').replaceWith(this.$tableHeader);

            this.$wrapper.find(ListPreviewResultsTable._selectors.selectAllRecordsCheckbox).prop('checked', false);
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

        if(!$(ListPreviewResultsTable._selectors.selectAllRecordsCheckbox).is(':checked')) {

            this.$wrapper.find(ListPreviewResultsTable._selectors.table).find('thead').replaceWith(this.$tableHeader);

            this.$wrapper.find(ListPreviewResultsTable._selectors.recordCheckbox).prop('checked', false);

            this.$wrapper.find(ListPreviewResultsTable._selectors.selectAllRecordsCheckbox).prop('checked', false);

        } else {

            this.$wrapper.find(ListPreviewResultsTable._selectors.table).find('thead').replaceWith($tableActionsTemplate);

            this.$wrapper.find(ListPreviewResultsTable._selectors.recordCheckbox).prop('checked', true);

            this.$wrapper.find(ListPreviewResultsTable._selectors.selectAllRecordsCheckbox).prop('checked', true);

        }

        this.updateRecords();
    }

    updateRecords() {

        debugger;
        this.$wrapper.find(ListPreviewResultsTable._selectors.recordCheckbox).each((index, $element) => {

            debugger;
            if($element.is(":checked")) {
                debugger;
                this.records.push($element.attr('data-record-id'));
            }

        });

    }

    handleBulkEditButtonClicked(e) {

        debugger;

        if(e.cancelable) {
            e.preventDefault();
        }

        new BulkEditRecordFormModal(this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);

    }

    listPreviewResultsLoaded(data, columnOrder) {

        this.activatePlugins(data, columnOrder);

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
            "responsive": true,
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
                    this.$tableHeader = this.$wrapper.find(ListPreviewResultsTable._selectors.table).find('thead');
                }

                this.$wrapper.find(ListPreviewResultsTable._selectors.selectAllRecordsCheckbox).prop('checked', false);
            }
        });

    }

/*    reloadTable() {
        this.loadColumnsForTable().then((data) => {
            this.table.destroy();
            this.activatePlugins(data.data);
        }).catch(errorData => {
        });
    }*/

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