'use strict';

import Routing from '../Routing';
import Settings from '../Settings';
import $ from "jquery";
import DeletePropertyButton from "./DeletePropertyButton";

require( 'datatables.net-bs4' );
require( 'datatables.net-responsive-bs4' );
require( 'datatables.net-responsive-bs4/css/responsive.bootstrap4.css' );
require( 'datatables.net-bs4/css/dataTables.bootstrap4.css' );


class ReportPreviewResultsTable {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     * @param data
     * @param columnOrder
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, data, columnOrder) {

        debugger;
        console.log("table");
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.data = data;
        this.columnOrder = columnOrder;

        this.globalEventDispatcher.subscribe(Settings.Events.REPORT_PREVIEW_RESULTS_BUTTON_CLICKED, this.handleReportPreviewResultsButtonClicked.bind(this));

        this.globalEventDispatcher.subscribe('TEST', this.handleReportPreviewResultsButtonClicked.bind(this));

        this.render();
        this.activatePlugins();

    }

    reportPreviewResultsLoaded(data, columnOrder) {

        this.activatePlugins(data, columnOrder);

    }

    handleReportPreviewResultsButtonClicked(data, columns) {
        console.log("preview table update");
        debugger;
        this.table.destroy();
        this.activatePlugins(data.data, columns);

       /* this.loadReportPreview().then((data) => {
            this.activatePlugins(data.data, this.columnOrder);
        });
*/
    }

    loadReportPreview() {
        return new Promise((resolve, reject) => {
            debugger;

            const url = Routing.generate('get_report_preview', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});

            $.ajax({
                url: url,
                data: {data: this.data, columnOrder: this.columnOrder},
                method: 'POST'
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

    activatePlugins(data = {}, columns = {}) {
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
            debugger;
            for(let key in columns) {
                debugger;
                let column = columns[key];
                datatableColumns.push({data: column.internalName, name: column.internalName, title: column.internalName});
            }
            debugger;
        }
        $('#reportPreviewResultsTable thead').empty();
        $('#reportPreviewResultsTable tbody').empty();
        this.table = $('#reportPreviewResultsTable').DataTable({
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
            "columns": datatableColumns,
            "data": data,
            "initComplete": () => {
                debugger;
                /*this.globalEventDispatcher.publish(Settings.Events.REPORT_PREVIEW_TABLE_INITIALIZED);*/
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
        this.$wrapper.html(ReportPreviewResultsTable.markup(this));
    }

    static markup() {
        return `
            <table id="reportPreviewResultsTable" class="table table-striped table-bordered c-table" style="width:100%">
                <thead>
                <tr><th></th></tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        `;
    }
}

export default ReportPreviewResultsTable;