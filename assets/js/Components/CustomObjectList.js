'use strict';

import Routing from '../Routing';
import Settings from '../Settings';

require( 'datatables.net-bs4' );
require( 'datatables.net-responsive-bs4' );
require( 'datatables.net-responsive-bs4/css/responsive.bootstrap4.css' );
require( 'datatables.net-bs4/css/dataTables.bootstrap4.css' );


class CustomObjectList {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     */
    constructor($wrapper, globalEventDispatcher) {

        this.init($wrapper, globalEventDispatcher);
    }

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     */
    init($wrapper, globalEventDispatcher) {

        this.$wrapper = $wrapper;

        /**
         * @type {EventDispatcher}
         */
        this.globalEventDispatcher = globalEventDispatcher;

        this.render();

        this.globalEventDispatcher.subscribe(
            Settings.Events.CUSTOM_OBJECT_CREATED,
            this.reloadList.bind(this)
            );


/*        this.loadCustomObjects();

        this.render();

        $('#table_id').DataTable({
            serverSide: true,
            ajax: Routing.generate('custom_objects_for_datatable')
        } );
        */

        $('#table_id').DataTable({
            "processing": true,
            "serverSide": true,
            "responsive": true,
            "columns": [
                { "data": "label", "name": "label", "title": "label" },
                //repeat for each of my 20 or so fields
            ],
            "ajax": {
                url: Routing.generate('custom_objects_for_datatable', {portal: 1}),
                type: "GET",
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
        this.$wrapper.html(CustomObjectList.markup(this));
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

export default CustomObjectList;