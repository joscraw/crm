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
     * @param data
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, data) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.data = data;
        this.table = null;
        this.globalEventDispatcher.subscribe('TEST', this.refreshTable.bind(this));
        this.render();
        // only render the table on instantiation if the properties aren't empty
        if(!_.isEmpty(this.data.properties)) {
            this.refreshTable({}, this.data.properties);
        }
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

    refreshTable(data, columns) {
        if(this.table) {
            $('#listPreviewResultsTable').DataTable().clear();
            $('#listPreviewResultsTable').DataTable().destroy();
            $('#listPreviewResultsTable thead').empty();
            $('#listPreviewResultsTable tbody').empty();
        }
        this.activatePlugins(data, columns);
    }

    activatePlugins(data = {}, columns = {}) {
        debugger;
        let datatableColumns = [];
        if(_.isEmpty(data)) {
            data = [];
        }
        // Setup some default display data if columns is empty
        if(_.isEmpty(columns)) {
            datatableColumns = [{
                data: 'Select property on the left to get started...',
                name: 'Select property on the left to get started...',
                title: 'Select property on the left to get started...'
            }];
        } else {
            for(let key in columns) {
                let column = columns[key];
                datatableColumns.push({
                    data: column.column_label,
                    name: column.column_label,
                    title: column.column_label,
                });
            }
        }
        $('#listPreviewResultsTable thead').empty();
        $('#listPreviewResultsTable tbody').empty();
        this.table = $('#listPreviewResultsTable').DataTable({
            "paging": true,
            "destroy": true,
            "responsive": true,
            "processing": true,
            "serverSide": true,
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
            "columns": datatableColumns,
            "initComplete": () => {},
            "pageLength": 10,
            "ajax": {
                url: Routing.generate('report_records_for_datatable', {internalIdentifier: this.portalInternalIdentifier, internalName: this.data.selectedCustomObject.internalName}),
                type: "POST",
                data: (d) => {
                    // this is important as we send up a ton of data and
                    // we must pass it up as raw json otherwise it gets truncated
                    // as form data https://datatables.net/forums/discussion/26282/posting-json-with-built-in-ajax-functionality
                    d.data = this.data;
                    return JSON.stringify(d);
                },
                contentType: "application/json; charset=utf-8",
                dataType: "json",
            },
        });

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

    render() {
        let propertiesPageUrl = Routing.generate('property_settings', {internalIdentifier: this.portalInternalIdentifier, internalName: this.data.selectedCustomObject.internalName});
        this.$wrapper.html(ListPreviewResultsTable.markup(propertiesPageUrl));
    }

    static markup(propertiesPageUrl) {
        return `
            <table id="listPreviewResultsTable" class="table table-striped table-bordered c-table" style="width:100%">
                <thead>
                <tr><th>No properties exist for this custom object. Head to the <a href="${propertiesPageUrl}">properties page to create some.</a></th></tr>
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