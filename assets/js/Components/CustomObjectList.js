'use strict';

import Routing from '../Routing';
import Settings from '../Settings';
import EditCustomObjectButton from "./EditCustomObjectButton";
import $ from "jquery";

require( 'datatables.net-bs4' );
require( 'datatables.net-responsive-bs4' );
require( 'datatables.net-responsive-bs4/css/responsive.bootstrap4.css' );
require( 'datatables.net-bs4/css/dataTables.bootstrap4.css' );


class CustomObjectList {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portal
     */
    constructor($wrapper, globalEventDispatcher, portal) {

        this.portal = portal;

        this.$wrapper = $wrapper;

        /**
         * @type {EventDispatcher}
         */
        this.globalEventDispatcher = globalEventDispatcher;

        this.render().then(() => {this.activatePlugins();});

        this.globalEventDispatcher.subscribe(
            Settings.Events.CUSTOM_OBJECT_CREATED,
            this.reloadList.bind(this)
        );

      /*  this.globalEventDispatcher.subscribe(
            Settings.Events.CUSTOM_OBJECT_EDITED,
            this.reloadList.bind(this)
        );
*/
        this.globalEventDispatcher.subscribe(
            Settings.Events.CUSTOM_OBJECT_SEARCH_KEY_UP,
            this.applySearch.bind(this)
        );

    }

    activatePlugins() {

        this.table = $('#table_id').DataTable({

            "pageLength": 10,
            "processing": true,
            "serverSide": true,
            "responsive": true,
            "language": {
                "emptyTable": `No "${this.customObjectLabel}" custom objects found.`,
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
            "columns": [
                { "data": "label", "name": "label", "title": "label", mRender: (data, type, row) => {
                        return `${row['label']} <span class="js-edit-custom-object" data-custom-object-id="${row['id']}"></span>`;
                    }},
                //repeat for each of my 20 or so fields
            ],
            "ajax": {
                url: Routing.generate('custom_objects_for_datatable', {internalIdentifier: this.portal}),
                type: "GET",
                dataType: "json",
                contentType: "application/json; charset=utf-8"
            },
            "initComplete": (settings, json) => {this.addEditCustomObjectButton()},
            "drawCallback": (settings)  => {this.addEditCustomObjectButton();}
        });
    }

    addEditCustomObjectButton() {
        this.$wrapper.find('.js-edit-custom-object').each((index, element) => {
            new EditCustomObjectButton($(element), this.globalEventDispatcher, this.portal, $(element).data('customObjectId'), "Edit");
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

        debugger;
       /* $('.js-edit-custom-object').each((index, element) => {
            debugger;
            new EditCustomObjectButton($(element), this.globalEventDispatcher, this.portal, $(element).data('customObjectId'), "Edit");
        });*/
    }


    reloadList() {
        this.table.destroy();
        /*this.table.ajax.reload();*/
        this.activatePlugins();
    }

    loadCustomObjects() {

    }

    render() {
        return new Promise((resolve, reject) => {
            this.$wrapper.html(CustomObjectList.markup(this));
            resolve();
        });
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