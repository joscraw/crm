'use strict';

import Routing from '../Routing';
import Settings from '../Settings';
import EditCustomObjectButton from "./EditCustomObjectButton";
import $ from "jquery";
import DeleteCustomObjectButton from "./DeleteCustomObjectButton";
import DeleteReportButton from "./DeleteReportButton";
import DeleteFormButton from "./DeleteFormButton";
import DeleteWorkflowButton from "./DeleteWorkflowButton";

require( 'datatables.net-bs4' );
require( 'datatables.net-responsive-bs4' );
require( 'datatables.net-responsive-bs4/css/responsive.bootstrap4.css' );
require( 'datatables.net-bs4/css/dataTables.bootstrap4.css' );


class WorkflowList {

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
            Settings.Events.WORKFLOW_DELETED,
            this.reloadList.bind(this)
        );
        this.globalEventDispatcher.subscribe(
            Settings.Events. WORKFLOW_SEARCH_KEY_UP,
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
                "emptyTable": `No workflows found.`,
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
                    debugger;
            return `
                        ${row['name']} <span class="c-table__edit-button"><a href="${Routing.generate('workflow_trigger', {'uid' : row['uid'], 'internalIdentifier' : this.portal})}" data-bypass="true" class="btn btn-primary btn-sm">Edit</a></span>
                        <span class="js-delete-workflow c-table__delete-button" data-uid="${row['uid']}"></span>
                    `;
                }}
            ],
            "ajax": {
                url: Routing.generate('workflows_for_datatable', {internalIdentifier: this.portal}),
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
        this.$wrapper.find('.js-delete-workflow').each((index, element) => {
            new DeleteWorkflowButton($(element), this.globalEventDispatcher, this.portal, $(element).data('uid'), "Delete");
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
            this.$wrapper.html(WorkflowList.markup(this));
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

export default WorkflowList;