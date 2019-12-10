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

    refreshTable(data, columns) {
        if(this.table) {
            $('#reportPreviewResultsTable').DataTable().clear();
            $('#reportPreviewResultsTable').DataTable().destroy();
            $('#reportPreviewResultsTable thead').empty();
            $('#reportPreviewResultsTable tbody').empty();
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
                    data: column.custom_object_label + ' ' + column.label,
                    name: column.custom_object_label + ' ' + column.label,
                    title: column.custom_object_label + ' ' + column.label
                });
            }
        }
        $('#reportPreviewResultsTable thead').empty();
        $('#reportPreviewResultsTable tbody').empty();
        this.table = $('#reportPreviewResultsTable').DataTable({
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

    render() {
        debugger;
        let propertiesPageUrl = Routing.generate('property_settings', {internalIdentifier: this.portalInternalIdentifier, internalName: this.data.selectedCustomObject.internalName});
        this.$wrapper.html(ReportPreviewResultsTable.markup(propertiesPageUrl));
    }

    static markup(propertiesPageUrl) {
        return `
            <table id="reportPreviewResultsTable" class="table table-striped table-bordered c-table" style="width:100%">
                <thead>
                <tr><th>No properties exist for this custom object. Head to the <a href="${propertiesPageUrl}">properties page to create some.</a></th></tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        `;
    }
}

export default ReportPreviewResultsTable;