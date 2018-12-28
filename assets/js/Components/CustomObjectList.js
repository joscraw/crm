'use strict';

/*window.$ = require('jquery');
var dt = require('datatables.net-bs4');
window.$.DataTable = dt;*/

import Routing from '../Routing';


require( 'datatables.net-bs4' );

/*var $ = require('jquery');
var dt = require('datatables.net-bs');
$.DataTable = dt;*/

class CustomObjectList {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     */
    constructor($wrapper, globalEventDispatcher) {

        debugger;

        this.init($wrapper, globalEventDispatcher);
    }

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     */
    init($wrapper, globalEventDispatcher) {
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;



        this.render();


/*        this.loadCustomObjects();

        this.render();

        $('#table_id').DataTable({
            serverSide: true,
            ajax: Routing.generate('custom_objects_for_datatable')
        } );
        */


        debugger;

        $('#table_id').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                url: Routing.generate('custom_objects_for_datatable', {portal: 1}),
                type: "GET",
                dataType: "json",
                contentType: "application/json; charset=utf-8"
            }/*,
            "columns": [{ "sName": "NAME" },
                { "sName": "SERIAL_NUMBER" },
                { "sName": "AUTHOR" }]*/
        });



    }

    loadCustomObjects() {

    }

    render() {
        this.$wrapper.html(CustomObjectList.markup(this));
    }

    static markup() {
        return `
            <table id="table_id" class="table display">
                <thead>
                    <tr>
                        <th>Column 1</th>
                        <th>Column 2</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        `;
    }
}

export default CustomObjectList;