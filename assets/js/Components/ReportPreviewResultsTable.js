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

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.data = data;
        this.columnOrder = columnOrder;

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_PREVIEW_RESULTS_BUTTON_CLICKED,
            this.handleReportPreviewResultsButtonClicked.bind(this)
        ));

        this.render();

    }

    reportPreviewResultsLoaded(data, columnOrder) {

        this.activatePlugins(data, columnOrder);

    }

    handleReportPreviewResultsButtonClicked() {

        this.loadReportPreview().then((data) => {

            this.activatePlugins(data.data, this.columnOrder);

        });

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

    activatePlugins(data, columnOrder) {

        let columns = [];

        for(let c of columnOrder) {

            columns.push({data: c.internalName, name: c.internalName, title: c.label});

        }

        $('#reportPreviewResultsTable').DataTable({
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
            "data": data
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
                </thead>
                <tbody>
                </tbody>
            </table>
        `;
    }
}

export default ReportPreviewResultsTable;