'use strict';

import Routing from '../Routing';
import Settings from '../Settings';
import $ from "jquery";
import EditCustomObjectButton from "./EditCustomObjectButton";
import EditPropertyGroupButton from "./EditPropertyGroupButton";
import DeletePropertyGroupButton from "./DeletePropertyGroupButton";
import DeleteCustomObjectButton from "./DeleteCustomObjectButton";
import DeletePropertyButton from "./DeletePropertyButton";
import EditUserButton from "./EditUserButton";
import DeleteUserButton from "./DeleteUserButton";

require( 'datatables.net-bs4' );
require( 'datatables.net-responsive-bs4' );
require( 'datatables.net-responsive-bs4/css/responsive.bootstrap4.css' );
require( 'datatables.net-bs4/css/dataTables.bootstrap4.css' );


class UserList {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier) {

        debugger;

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.searchValue = '';
        this.collapseStatus = {};

        this.globalEventDispatcher.subscribe(
            Settings.Events.USER_SETTINGS_TOP_BAR_SEARCH_KEY_UP,
            this.applySearch.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.USER_DELETED,
            this.redrawDataTable.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.USER_CREATED,
            this.redrawDataTable.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.USER_EDITED,
            this.redrawDataTable.bind(this)
        );

        this.render().then(() => {
            this.activatePlugins();
        })

    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            collapse: '.js-collapse',
            collapseTitle: '.js-collapse__title',
            collapseBody: '.js-collapse__body'
        }
    }

    activatePlugins() {

        this.table = $('#user_table').DataTable({

            "pageLength": 10,
            "processing": true,
            "serverSide": true,
            "responsive": true,
            "language": {
                "emptyTable": `No users found.`,
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

                { "data": "firstName", "name": "firstName", "title": "First Name", mRender: (data, type, row) => {

                        return `
                        ${row['firstName']} <span class="c-table__edit-button js-edit-user-button" data-user-id="${row['id']}"></span>
                        <span class="js-delete-user-button c-table__delete-button" data-user-id="${row['id']}"></span>
                         `;

                    } },
                { "data": "lastName", "name": "lastName", "title": "Last Name"},
                { "data": "email", "name": "email", "title": "Email"}
            ],
            "ajax": {
                url: Routing.generate('users_for_datatable', {internalIdentifier: this.portalInternalIdentifier}),
                type: "GET",
                dataType: "json",
                contentType: "application/json; charset=utf-8"
            },
            "drawCallback": (settings)  => {
                this.addEditUserButton();
                this.addDeleteUserButton();
            }
        });
    }

    addEditUserButton() {
        this.$wrapper.find('.js-edit-user-button').each((index, element) => {

            new EditUserButton($(element), this.globalEventDispatcher, this.portalInternalIdentifier, $(element).data('userId'), "Edit");
        });
    }

    addDeleteUserButton() {
        this.$wrapper.find('.js-delete-user-button').each((index, element) => {
            new DeleteUserButton($(element), this.globalEventDispatcher, this.portalInternalIdentifier, $(element).data('userId'), "Delete");
        });
    }

    /**
     * @param args
     */
    applySearch(args = {}) {

        if(typeof args.searchValue !== 'undefined') {
            this.searchValue = args.searchValue;
        }

        $('#user_table').DataTable().search(
            this.searchValue
        ).draw();

    }

    redrawDataTable() {
        this.table.destroy();
        this.activatePlugins();
    }

    render() {
        return new Promise((resolve, reject) => {
            this.$wrapper.html(UserList.markup(this));
            resolve();
        });
    }

    static markup() {
        return `
            <table id="user_table" class="table table-striped table-bordered c-table" style="width:100%">
                <thead>
                </thead>
                <tbody>
                </tbody>
            </table>
        `;
    }

}

export default UserList;