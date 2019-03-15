'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import PropertyCreateForm from './PropertyCreateForm';
import UserCreateForm from "./UserCreateForm";
import RoleCreateForm from "./RoleCreateForm";
import RolesList from "./RolesList";
import Settings from "../Settings";
import RoleEditForm from "./RoleEditForm";

class RolesAndPermissionsFormModal {

    /**
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     */
    constructor(globalEventDispatcher, portalInternalIdentifier) {
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;

        this.globalEventDispatcher.subscribe(
            Settings.Events.EDIT_ROLE_BUTTON_CLICKED,
            this.editRoleButtonClickedHandler.bind(this)
        );

        this.render();
    }

    editRoleButtonClickedHandler(roleId) {

        $('[data-target="#edit-role"]').tab('show');

        new RoleEditForm($('#edit-role'), this.globalEventDispatcher, this.portalInternalIdentifier, roleId);
    }

    render() {
        swal({
            title: 'Roles and Permissions',
            showConfirmButton: false,
            html: RolesAndPermissionsFormModal.markup()
        });

        new RoleCreateForm($('#new-role'), this.globalEventDispatcher, this.portalInternalIdentifier);
        new RolesList($('#all-roles'), this.globalEventDispatcher, this.portalInternalIdentifier);
    }

    static markup() {
        return `
      <div id="js-roles-and-permissions-modal-container">
      <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item">
            <span class="nav-link active show" data-target="#new-role" data-toggle="tab" role="tab" aria-controls="new-role" aria-selected="true">New Role</span>
          </li>
          <li class="nav-item">
            <span class="nav-link" data-target="#all-roles" data-toggle="tab" role="tab" aria-controls="all-roles" aria-selected="false">All Roles</span>
          </li>
          <li class="nav-item" style="display: none">
            <span class="nav-link" data-target="#edit-role" data-toggle="tab" role="tab" aria-controls="edit-role" aria-selected="false"></span>
          </li>
        </ul>
        <br>
        <div class="tab-content" id="myTabContent">
          <div class="tab-pane fade show active" id="new-role" role="tabpanel" aria-labelledby="home-tab"></div>
          <div class="tab-pane fade" id="all-roles" role="tabpanel" aria-labelledby="profile-tab"></div>
          <div class="tab-pane fade" id="edit-role" role="tabpanel" aria-labelledby="profile-tab"></div>
        </div>
      
       </div>
    `;
    }
}

export default RolesAndPermissionsFormModal;