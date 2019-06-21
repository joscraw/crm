'use strict';

import Routing from '../Routing';
import Settings from '../Settings';
import EditCustomObjectButton from "./EditCustomObjectButton";
import $ from "jquery";
import DeleteCustomObjectButton from "./DeleteCustomObjectButton";

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

        this.globalEventDispatcher.subscribe(
            Settings.Events.CUSTOM_OBJECT_EDITED,
            this.reloadList.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.CUSTOM_OBJECT_DELETED,
            this.reloadList.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.CUSTOM_OBJECT_SEARCH_KEY_UP,
            this.applySearch.bind(this)
        );

    }

    activatePlugins() {

        debugger;
        Pace.start({
            target: '.l-grid'
        });

        this.table = $('#table_id').DataTable({

            "pageLength": 10,
            "processing": true,
            "serverSide": true,
            "responsive": true,
            "language": {
                "emptyTable": `No custom objects found.`,
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
                        return `
                        ${row['label']} <span class="js-edit-custom-object c-table__edit-button" data-custom-object-id="${row['id']}"></span>
                        <span class="js-delete-custom-object c-table__delete-button" data-custom-object-id="${row['id']}"></span>`;
                    }},
                //repeat for each of my 20 or so fields
            ],
            "ajax": {
                url: Routing.generate('custom_objects_for_datatable', {internalIdentifier: this.portal}),
                type: "POST"
            },
            "drawCallback": (settings)  => {
                this.addEditCustomObjectButton();
                this.addDeleteCustomObjectButton();
            }
        });
    }

    addEditCustomObjectButton() {
        this.$wrapper.find('.js-edit-custom-object').each((index, element) => {
            new EditCustomObjectButton($(element), this.globalEventDispatcher, this.portal, $(element).data('customObjectId'), "Edit");
        });
    }

    addDeleteCustomObjectButton() {
        this.$wrapper.find('.js-delete-custom-object').each((index, element) => {
            new DeleteCustomObjectButton($(element), this.globalEventDispatcher, this.portal, $(element).data('customObjectId'), "Delete");
        });
    }


    /**
     * @param args
     */
    applySearch(args = {}) {

        if(typeof args.searchValue !== 'undefined') {
            this.searchValue = args.searchValue;
        }

        $('#table_id').DataTable().search(
            this.searchValue
        ).draw();
    }


    reloadList() {
        debugger;
        console.log("reload list");
        this.table.destroy();
        this.activatePlugins();
    }


    render() {
        return new Promise((resolve, reject) => {
            this.$wrapper.html(CustomObjectList.markup(this));
            resolve();
        });
    }

    static markup() {
        return `
            <table id="table_id" class="table table-striped table-bordered c-table" style="width:100%">
                <thead>
                </thead>
                <tbody>
                </tbody>
            </table>
        `;
    }
}

export default CustomObjectList;