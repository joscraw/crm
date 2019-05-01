'use strict';

import Routing from '../Routing';
import Settings from '../Settings';
import EditCustomObjectButton from "./EditCustomObjectButton";
import $ from "jquery";
import DeleteCustomObjectButton from "./DeleteCustomObjectButton";
import DeleteReportButton from "./DeleteReportButton";
import DeleteListButton from "./DeleteListButton";
import DeleteListFormModal from "./DeleteListFormModal";
import ListTableDropdown from "./ListTableDropdown";
import FolderTableDropdown from "./FolderTableDropdown";

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
            Settings.Events.FOLDER_CREATED,
            this.reloadList.bind(this)
        );

        this.globalEventDispatcher.singleSubscribe(
            Settings.Events.FOLDER_DELETED,
            this.reloadList.bind(this)
        );

        this.globalEventDispatcher.singleSubscribe(
            Settings.Events.FOLDER_MODIFIED,
            this.reloadList.bind(this)
        );


        this.globalEventDispatcher.singleSubscribe(
            Settings.Events.LIST_MOVED_TO_FOLDER,
            this.reloadList.bind(this)
        );

        this.globalEventDispatcher.singleSubscribe(
            Settings.Events.LIST_SEARCH_KEY_UP,
            this.applySearch.bind(this)
        );
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            listTableDropdown: '.js-list-table-dropdown',
            folderTableDropdown: '.js-folder-table-dropdown',
        }
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
            "dom": "lprt",
            "columns": [
                { "data": "name", "name": "name", "title": "Name", mRender: (data, type, row) => {

                    let html = ``;

                    if(_.indexOf(['DYNAMIC_LIST', 'STATIC_LIST'], row['type']) !== -1) {

                        html += `${row['name']}`;

                        html += `<span class="c-table__edit-button js-list-table-dropdown" data-list-id="${row['id']}"></span>`;

                    } else {

                        html += `<a href="${Routing.generate('list_settings', {internalIdentifier: this.portal})}/folders/${row['id']}" role="button"><i class="fa fa-folder"></i>  ${row['name']} </a>`;

                        html += `<span class="c-table__edit-button js-folder-table-dropdown" data-folder-id="${row['id']}"></span>`;
                    }

                    return html;
                }},
                { "data": "type", "name": "type", "title": "Type", mRender: (data, type, row) => {

                    let html = '-';

                    if(_.indexOf(['DYNAMIC_LIST', 'STATIC_LIST'], row['type']) !== -1) {

                        if(data === 'DYNAMIC_LIST') {
                            html = 'Dynamic List';
                        } else if(data === 'STATIC_LIST') {
                            html = 'Static List';
                        }
                    }

                    return html;
                    }},
                { "data": "size", "name": "size", "title": "Size", mRender: (data, type, row) => {

                        let html = `-`;

                        if(_.indexOf(['DYNAMIC_LIST', 'STATIC_LIST'], row['type']) === -1) {

                            html = `${row['size']}`;

                        }
                        return html;
                    }},
                {
                    data: null,
                    className: "center",
                    title: "Download",
                    mRender: (data, type, row) => {

                        let html = '-';

                        if(_.indexOf(['DYNAMIC_LIST', 'STATIC_LIST'], row['type']) !== -1) {

                        html = `<a href="${Routing.generate('download_list', {'listId' : data['id'], 'internalIdentifier' : this.portal})}" data-bypass="true" class="btn btn-primary" download><i class="fa fa-download"></i> Export</a>`;
                        }

                        return html;
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

                this.addListDropdown();

                this.addFolderDropdown();

            },
            "initComplete": function () {}
        });
    }

    addListDropdown() {

        this.$wrapper.find(ListFolderTable._selectors.listTableDropdown).each((index, element) => {

            let listId = $(element).attr('data-list-id');

            new ListTableDropdown($(element), this.globalEventDispatcher, this.portal, listId, "Actions");
        });

    }

    addFolderDropdown() {

        this.$wrapper.find(ListFolderTable._selectors.folderTableDropdown).each((index, element) => {

            let folderId = $(element).attr('data-folder-id');

            new FolderTableDropdown($(element), this.globalEventDispatcher, this.portal, folderId, "Actions");
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