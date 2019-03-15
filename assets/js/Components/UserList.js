'use strict';

import Routing from '../Routing';
import Settings from '../Settings';
import $ from "jquery";
import EditCustomObjectButton from "./EditCustomObjectButton";
import EditPropertyGroupButton from "./EditPropertyGroupButton";
import DeletePropertyGroupButton from "./DeletePropertyGroupButton";
import DeleteCustomObjectButton from "./DeleteCustomObjectButton";
import DeletePropertyButton from "./DeletePropertyButton";

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



/*        this.globalEventDispatcher.subscribe(
            Settings.Events.PROPERTY_SETTINGS_TOP_BAR_SEARCH_KEY_UP,
            this.applySearch.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.PROPERTY_GROUP_CREATED,
            this.redrawDataTable.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.PROPERTY_CREATED,
            this.redrawDataTable.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.PROPERTY_EDITED,
            this.redrawDataTable.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.PROPERTY_GROUP_EDITED,
            this.redrawDataTable.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.PROPERTY_GROUP_DELETED,
            this.redrawDataTable.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.PROPERTY_DELETED,
            this.redrawDataTable.bind(this)
        );

        this.$wrapper.on('click',
            PropertyList._selectors.collapseTitle,
            this.handleTitleClick.bind(this)
            );*/

        /*this.loadUsers().then(data => {
            debugger;
            this.render(data);
        })*/


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
                        ${row['firstName']} <span class="c-table__edit-button js-edit-user-button"><button type="button" data-role-id="${row['id']}" class="btn btn-primary btn-sm">Edit</button></span>
                        <span class="js-delete-user-button c-table__delete-button"><button type="button" data-role-id="${row['id']}" class="btn btn-primary btn-sm">Delete</button></span>
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
            }
        });
    }

    /**
     * @param args
     */
    applySearch(args = {}) {

        if(typeof args.searchValue !== 'undefined') {
            this.searchValue = args.searchValue;
        }

        $('table').DataTable().search(
            this.searchValue
        ).draw();

        this.$wrapper.find('.js-collapse').each((index, element) => {
            if($(element).find('.dataTables_empty').length && this.searchValue !== '') {
                $(element).addClass('is-disabled');

            } else {
                if($(element).hasClass('is-disabled')) {
                    $(element).removeClass('is-disabled');
                }
            }
        });
    }

    redrawDataTable() {
        this.loadProperties().then(data => {
            this.render(data);
            this.applyCollapseStatus();
            this.applySearch();
        });
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