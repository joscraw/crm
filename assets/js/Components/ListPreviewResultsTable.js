'use strict';

import Routing from '../Routing';
import Settings from '../Settings';
import $ from "jquery";
import DeletePropertyButton from "./DeletePropertyButton";

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

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;

        this.globalEventDispatcher.subscribe(
            Settings.Events.LIST_PREVIEW_RESULTS_LOADED,
            this.listPreviewResultsLoaded.bind(this)
        );

        this.render();

    }

    listPreviewResultsLoaded(data, columnOrder) {

        this.activatePlugins(data, columnOrder);

    }

    activatePlugins(data, columnOrder) {

        let columns = [];

        for(let c of columnOrder) {

            columns.push({data: c.internalName, name: c.internalName, title: c.label});

        }

        $('#listPreviewResultsTable').DataTable({
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
        this.$wrapper.html(ListPreviewResultsTable.markup(this));
    }

    static markup() {
        return `
            <table id="listPreviewResultsTable" class="table table-striped table-bordered c-table" style="width:100%">
                <thead>
                </thead>
                <tbody>
                </tbody>
            </table>
        `;
    }
}

export default ListPreviewResultsTable;