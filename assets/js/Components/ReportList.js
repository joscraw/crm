
'use strict';

import Routing from '../Routing';
import Settings from '../Settings';
import EditCustomObjectButton from "./EditCustomObjectButton";
import $ from "jquery";
import DeleteCustomObjectButton from "./DeleteCustomObjectButton";
import DeleteReportButton from "./DeleteReportButton";

require( 'datatables.net-bs4' );
require( 'datatables.net-responsive-bs4' );
require( 'datatables.net-responsive-bs4/css/responsive.bootstrap4.css' );
require( 'datatables.net-bs4/css/dataTables.bootstrap4.css' );


class ReportList {

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
            Settings.Events.CUSTOM_OBJECT_EDITED,
            this.reloadList.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_DELETED,
            this.reloadList.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_SEARCH_KEY_UP,
            this.applySearch.bind(this)
        );

    }

    activatePlugins() {

        Pace.start({
            target: '.l-grid'
        });


        this.table = $('#table_id').DataTable({

            "pageLength": 10,
            "processing": true,
            "serverSide": true,
            "responsive": true,
            "language": {
                "emptyTable": `No reports found.`,
                "processing": `<div class="pace pace-inactive">
                                <div class="pace-progress" data-progress-text="100%" data-progress="99" style="width: 100%;">
                                <div class="pace-progress-inner"></div>
                                </div>
                                <div class="pace-activity"></div>
                                </div>`
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
            return `
                        ${row['name']} <span class="c-table__edit-button"><a href="${Routing.generate('edit_report', {'reportId' : row['id'], 'internalIdentifier' : this.portal})}" data-bypass="true" class="btn btn-primary btn-sm">Edit</a></span>
                        <span class="js-delete-report c-table__delete-button" data-report-id="${row['id']}"></span>
                    `;
                }},
                {
                    data: null,
                    className: "center",
                    title: "download",
                    mRender: (data, type, row) => {
                        debugger;
                        return `
                        <a href="${Routing.generate('download_report', {'reportId' : data['id'], 'internalIdentifier' : this.portal})}" data-bypass="true" class="btn btn-primary" download><i class="fa fa-download"></i> Export</a>
                        `;
                    }
                }
                //repeat for each of my 20 or so fields
            ],
            "ajax": {
                url: Routing.generate('reports_for_datatable', {internalIdentifier: this.portal}),
                type: "GET",
                dataType: "json",
                contentType: "application/json; charset=utf-8"
            },
            "drawCallback": (settings)  => {

                this.addDeleteReportButton();
            },
            "initComplete": function () {}
        });
    }

    addDeleteReportButton() {

        debugger;
        this.$wrapper.find('.js-delete-report').each((index, element) => {
            new DeleteReportButton($(element), this.globalEventDispatcher, this.portal, $(element).data('reportId'), "Delete");
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
        this.table.destroy();
        this.activatePlugins();
    }


    render() {
        return new Promise((resolve, reject) => {
            this.$wrapper.html(ReportList.markup(this));
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

export default ReportList;