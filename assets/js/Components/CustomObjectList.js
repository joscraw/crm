'use strict';

import Routing from '../Routing';
import Settings from '../Settings';
import EditCustomObjectButton from "./EditCustomObjectButton";

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

    }

    activatePlugins() {
        $('#table_id').DataTable({

            "pageLength": 10,
            "processing": true,
            "serverSide": true,
            "responsive": true,
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
            "initComplete": (settings, json) => {

                $('.js-edit-custom-object').each((index, element) => {
                    new EditCustomObjectButton($(element), this.globalEventDispatcher, this.portal, $(element).data('customObjectId'), "Edit");
                });
            }
        });
    }


    reloadList() {
        $('#table_id').DataTable().ajax.reload();
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