'use strict';

import Routing from '../Routing';
import Settings from '../Settings';
import $ from "jquery";

require( 'datatables.net-bs4' );
require( 'datatables.net-responsive-bs4' );
require( 'datatables.net-responsive-bs4/css/responsive.bootstrap4.css' );
require( 'datatables.net-bs4/css/dataTables.bootstrap4.css' );


class RecordTable {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;

        this.globalEventDispatcher.subscribe(
            Settings.Events.DATATABLE_SEARCH_KEY_UP,
            this.applySearch.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.RECORD_CREATED,
            this.reloadTable.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.COLUMNS_UPDATED,
            this.reloadTable.bind(this)
        );

        this.render();

        this.loadColumnsForTable().then((data) => {
            debugger;
            this.activatePlugins(data.data);
        }).catch(() => {
            debugger;
        });
    }

    activatePlugins(columns) {

        if(columns.length !== 0) {
            columns[0].mRender = (data, type, row) => {
                
                let url = Routing.generate('record_list', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});
                url = `${url}/${row['id']}`;

                return `
                        ${data} <span class="c-table__edit-button"><a href="${url}" role="button" class="btn btn-primary btn-sm">Edit</a></span>
                        <span class="js-delete-property c-table__delete-button" data-record-id="${row['id']}"></span>
                         `;

            }
        }

        debugger;
        $('#table_id thead').empty();
        $('#table_id tbody').empty();
        debugger;

        this.table = $('#table_id').DataTable({
            "processing": true,
            "serverSide": true,
            "scrollX": true,
            "language": {
                "emptyTable": `No records found.`,
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
            "dom": "lpirt",
            "columns": columns,
            "pageLength": 10,
            "ajax": {
                url: Routing.generate('records_for_datatable', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName}),
                type: "GET",
                dataType: "json",
                contentType: "application/json; charset=utf-8"
            }
        });
    }

    loadColumnsForTable() {
        return new Promise((resolve, reject) => {
            debugger;
            const url = Routing.generate('get_columns_for_table', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});

            $.ajax({
                url: url
            }).then(data => {
                resolve(data);
            }).catch(jqXHR => {
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }

    /**
     * @param args
     */
    applySearch(args = {}) {

        debugger;
        if(typeof args.searchValue !== 'undefined') {
            this.searchValue = args.searchValue;
        }

        $('#table_id').DataTable().search(
            this.searchValue
        ).draw();
    }

    reloadTable() {
        this.loadColumnsForTable().then((data) => {
            debugger;
            this.table.destroy();
            /*$('#table_id').DataTable().destroy();*/
            debugger;
            this.activatePlugins(data.data);
        }).catch(errorData => {
            debugger;
        });
    }

    render() {
        this.$wrapper.html(RecordTable.markup(this));
    }

    static markup() {
        return `
            <table id="table_id" class="table table-striped table-bordered c-table" style="width:100%">
                <thead>
                <tr><th></th></tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        `;
    }
}

export default RecordTable;