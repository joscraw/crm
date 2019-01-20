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
     * @param portal
     * @param customObject
     */
    constructor($wrapper, globalEventDispatcher, portal, customObject, customObjectLabel) {

        this.portal = portal;
        this.$wrapper = $wrapper;
        this.customObject = customObject;
        this.customObjectLabel = customObjectLabel;
        this.globalEventDispatcher = globalEventDispatcher;

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
        debugger;
        $('#table_id thead').empty();
        $('#table_id tbody').empty();
        debugger;

        this.table = $('#table_id').DataTable({
            "processing": true,
            "serverSide": true,
            /*"order": [],*/
            "language": {
                "emptyTable": `No "${this.customObjectLabel}" records found.`,
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
            // num of results per page
            "pageLength": 10,
            /*"iDisplayLength": 1,*/
            /*"order": [[1, 'asc']],*/
            "ajax": {
                url: Routing.generate('records_for_datatable', {internalIdentifier: this.portal}),
                type: "GET",
                data: {'custom_object_id': this.customObject},
                dataType: "json",
                contentType: "application/json; charset=utf-8"
            }
        });
    }

    loadColumnsForTable() {
        return new Promise((resolve, reject) => {
            debugger;
            const url = Routing.generate('get_columns_for_table', {internalIdentifier: this.portal});

            $.ajax({
                url: url,
                data: {custom_object_id: this.customObject}
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
            <table id="table_id" class="table table-striped table-bordered" style="width:100%">
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