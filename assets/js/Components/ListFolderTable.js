'use strict';

import Routing from '../Routing';
import Settings from '../Settings';
import EditCustomObjectButton from "./EditCustomObjectButton";
import $ from "jquery";
import DeleteCustomObjectButton from "./DeleteCustomObjectButton";
import DeleteReportButton from "./DeleteReportButton";
import DeleteListButton from "./DeleteListButton";

require( 'datatables.net-bs4' );
require( 'datatables.net-responsive-bs4' );
require( 'datatables.net-responsive-bs4/css/responsive.bootstrap4.css' );
require( 'datatables.net-bs4/css/dataTables.bootstrap4.css' );


class ListFolderTable {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portal
     * @param folderId
     */
    constructor($wrapper, globalEventDispatcher, portal, folderId) {

        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portal = portal;
        this.folderId = folderId;

        this.render().then(() => {this.activatePlugins();});

        this.globalEventDispatcher.singleSubscribe(
            Settings.Events.LIST_DELETED,
            this.reloadList.bind(this)
        );

        this.globalEventDispatcher.singleSubscribe(
            Settings.Events.LIST_SEARCH_KEY_UP,
            this.applySearch.bind(this)
        );

    }

    activatePlugins() {

        Pace.start({
            target: '.l-grid'
        });

        let data = {};

        if(this.folderId) {
            data.folderId = this.folderId;
        }

        this.table = $('#table_id').DataTable({

            "pageLength": 10,
            "processing": true,
            "serverSide": true,
            "responsive": true,
            "language": {
                "emptyTable": `No folders found.`
            /*    "processing": `<div class="pace pace-inactive">
                                <div class="pace-progress" data-progress-text="100%" data-progress="99" style="width: 100%;">
                                <div class="pace-progress-inner"></div>
                                </div>
                                <div class="pace-activity"></div>
                                </div>`*/
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
                { "data": "name", "name": "name", "title": "name", mRender: (data, type, row) => {

                    let html = ``;

                    if(_.indexOf(['DYNAMIC_LIST', 'STATIC_LIST'], row['type']) !== -1) {

                        html += `${row['name']}`;
                    } else {

                        html += `<a href="${Routing.generate('list_settings', {internalIdentifier: this.portal})}/folders/${row['id']}" role="button"><i class="fa fa-folder"></i>  ${row['name']} </a>`;
                    }

                    return html;
                }},
                { "data": "type", "name": "type", "title": "Type", mRender: (data, type, row) => {

                    let listType = "";
                    if(data === 'DYNAMIC_LIST') {
                        listType = 'Dynamic List';
                    } else if(data === 'STATIC_LIST') {
                        listType = 'Static List';
                    }

                    return listType;
                    }},
                {
                    data: null,
                    className: "center",
                    title: "download",
                    mRender: (data, type, row) => {
                        debugger;
                        return `
                        <a href="${Routing.generate('download_list', {'listId' : data['id'], 'internalIdentifier' : this.portal})}" data-bypass="true" class="btn btn-primary" download><i class="fa fa-download"></i> Export</a>
                        `;
                    }
                }
            ],
            "ajax": {
                url: Routing.generate('list_folders_for_datatable', {internalIdentifier: this.portal}),
                data: {folderId: this.folderId},
                type: "GET",
                dataType: "json",
                contentType: "application/json; charset=utf-8"
            },
            "drawCallback": (settings)  => {

                this.addDeleteListButton();
            },
            "initComplete": function () {}
        });
    }

    addDeleteListButton() {

        debugger;
        this.$wrapper.find('.js-delete-list').each((index, element) => {
            new DeleteListButton($(element), this.globalEventDispatcher, this.portal, $(element).data('listId'), "Delete");
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

        switch(this.searchValue) {
            case 'static list':
                this.searchValue = 'static_list';
                break;
            case 'dynamic list':
                this.searchValue = 'static_list';
                break;
        }



        $('#table_id').DataTable().search(
            this.searchValue
        ).draw();
    }


    reloadList() {
        debugger;
        this.table.destroy();
        this.activatePlugins();
    }


    render() {
        return new Promise((resolve, reject) => {
            this.$wrapper.html(ListFolderTable.markup(this));
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

export default ListFolderTable;