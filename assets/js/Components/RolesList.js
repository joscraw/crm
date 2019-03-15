'use strict';

import Routing from '../Routing';
import Settings from '../Settings';
import EditCustomObjectButton from "./EditCustomObjectButton";
import $ from "jquery";
import DeleteCustomObjectButton from "./DeleteCustomObjectButton";
import DeletePropertyButton from "./DeletePropertyButton";

require( 'datatables.net-bs4' );
require( 'datatables.net-responsive-bs4' );
require( 'datatables.net-responsive-bs4/css/responsive.bootstrap4.css' );
require( 'datatables.net-bs4/css/dataTables.bootstrap4.css' );


class RolesList {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier) {

        this.portalInternalIdentifier = portalInternalIdentifier;
        this.$wrapper = $wrapper;

        /**
         * @type {EventDispatcher}
         */
        this.globalEventDispatcher = globalEventDispatcher;

        this.unbindEvents();

        this.$wrapper.on(
            'click',
            RolesList._selectors.editRoleButton,
            this.handleEditRoleButtonClick.bind(this)
        );

        this.$wrapper.on(
            'click',
            RolesList._selectors.deleteRoleButton,
            this.handleDeleteRoleButtonClick.bind(this)
        );

        this.$wrapper.on(
            'keyup',
            '.js-search',
            this.handleKeyupEvent.bind(this)
        );

        this.render().then(() => {

            this.loadRoles().then((data) => {

                this.activatePlugins(data.data);
            });
        })

    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            editRoleButton: '.js-edit-role-button',
            deleteRoleButton: '.js-delete-role-button'

        }
    }

    unbindEvents() {

        this.$wrapper.off('click', RolesList._selectors.editRoleButton);
        this.$wrapper.off('click', RolesList._selectors.deleteRoleButton);
        this.$wrapper.off('keyup', '.js-search');
    }

    handleKeyupEvent(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        const searchValue = $(e.target).val();
        this.applySearch(searchValue);

    }

    /**
     * @param searchValue
     */
    applySearch(searchValue) {

        debugger;

        this.table.search(
            searchValue
        ).draw();
    }

    handleEditRoleButtonClick(e) {

        let roleId = $(e.target).data('roleId');

        this.globalEventDispatcher.publish(Settings.Events.EDIT_ROLE_BUTTON_CLICKED, roleId);

    }

    handleDeleteRoleButtonClick(e) {

        debugger;
        let roleId = $(e.target).data('roleId');

        this._deleteRole(roleId).then((data) => {

            this.loadRoles().then((data) => {

                this.activatePlugins(data.data);
            });

        });

    }

    loadRoles() {
        return new Promise((resolve, reject) => {

            const url = Routing.generate('roles_for_datatable', {internalIdentifier: this.portalInternalIdentifier});

            $.ajax({
                url: url
            }).then(data => {
                debugger;
                resolve(data);
            }).catch(jqXHR => {
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }

    activatePlugins(data) {

        this.table = $('#table_roles').DataTable({

            "paging": false,
            "destroy": true,
            "responsive": true,
            "searching":true,
            "language": {
                "emptyTable": "No roles.",
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
            "dom": "rtpl",
            "columns": [
                { "data": "name", "name": "name", "title": "Name", mRender: (data, type, row) => {

                        return `
                        ${row['name']} <span class="c-table__edit-button js-edit-role-button"><button type="button" data-role-id="${row['id']}" class="btn btn-primary btn-sm">Edit</button></span>
                        <span class="js-delete-role-button c-table__delete-button"><button type="button" data-role-id="${row['id']}" class="btn btn-primary btn-sm">Delete</button></span>
                         `;

                    } },
            ],
            "data": data
        });
    }

    render() {
        return new Promise((resolve, reject) => {
            this.$wrapper.html(RolesList.markup(this));
            resolve();
        });
    }

    /**
     * @return {Promise<any>}
     * @private
     * @param roleId
     */
    _deleteRole(roleId) {

        return new Promise( (resolve, reject) => {

            const url = Routing.generate('delete_role', {internalIdentifier: this.portalInternalIdentifier, roleId: roleId});

            $.ajax({
                url,
                method: 'POST'
            }).then((data, textStatus, jqXHR) => {
                resolve(data);
            }).catch((jqXHR) => {
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }

    static markup() {
        return `
            <div class="input-group c-search-control">
              <input class="form-control c-search-control__input js-search" type="search" placeholder="Search for a role">
              <span class="c-search-control__foreground"><i class="fa fa-search"></i></span>
            </div>
            <br>
            <table id="table_roles" class="table table-striped table-bordered c-table" style="width:100%">
                <thead>
                </thead>
                <tbody>
                </tbody>
            </table>
        `;
    }
}

export default RolesList;