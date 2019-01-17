'use strict';

import Routing from '../Routing';
import Settings from '../Settings';

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
    constructor($wrapper, globalEventDispatcher, portal, customObject) {

        this.portal = portal;
        this.$wrapper = $wrapper;
        this.customObject = customObject;
        this.globalEventDispatcher = globalEventDispatcher;

        this.render();

/*        this.globalEventDispatcher.subscribe(
            Settings.Events.CUSTOM_OBJECT_CREATED,
            this.reloadList.bind(this)
        );*/

        $('#table_id').DataTable({
            "processing": true,
            "serverSide": true,
            "responsive": true,
            "columns": [
                { "data": "id", "name": "id", "title": "id"},
                { "data": "first_name", "name": "first_name", "title": "first_name"},
                { "data": "last_name", "name": "last_name", "title": "last_name"},
                { "data": "best_friend", "name": "best_friend", "title": "best_friend"},
                { "data": "number", "name": "number", "title": "number"},
                { "data": "tes_555", "name": "tes_555", "title": "tes_555"},
                //repeat for each of my 20 or so fields
            ],
            "ajax": {
                url: Routing.generate('records_for_datatable', {internalIdentifier: this.portal}),
                type: "GET",
                data: {'custom_object_id': this.customObject},
                dataType: "json",
                contentType: "application/json; charset=utf-8"
            }
        });
    }


    reloadList() {
        $('#table_id').DataTable().ajax.reload();
    }

    loadCustomObjects() {

    }

    render() {
        this.$wrapper.html(RecordTable.markup(this));
    }

    static markup() {
        return `
            <table id="table_id" class="table table-striped table-bordered" style="width:100%">
                <thead>
                </thead>
                <tbody>
                </tbody>
            </table>
        `;
    }
}

export default RecordTable;