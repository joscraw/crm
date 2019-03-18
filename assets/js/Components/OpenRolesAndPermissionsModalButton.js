'use strict';

import Settings from '../Settings';
import PropertyCreateFormModal from './PropertyCreateFormModal';
import UserCreateFormModal from "./UserCreateFormModal";
import RolesAndPermissionsFormModal from "./RolesAndPermissionsFormModal";

class OpenRolesAndPermissionsModalButton {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier) {
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;

        this.$wrapper.on(
            'click',
            '.js-open-roles-and-permissions-modal-btn',
            this.handleButtonClick.bind(this)
        );

        this.render();
    }

    handleButtonClick() {
        console.log("Roles & Permissions Button Clicked");
        this.globalEventDispatcher.publish(Settings.Events.ROLES_AND_PERMISSIONS_BUTTON_CLICKED);
        console.log(`Event Dispatched: ${Settings.Events.ROLES_AND_PERMISSIONS_BUTTON_CLICKED}`);
        new RolesAndPermissionsFormModal(this.globalEventDispatcher, this.portalInternalIdentifier);
    }

    render() {
        this.$wrapper.append(OpenRolesAndPermissionsModalButton.markup(this));
    }

    static markup() {
        return `
      <button type="button" class="js-open-roles-and-permissions-modal-btn btn btn-secondary">Roles & Permissions</button>
    `;
    }
}

export default OpenRolesAndPermissionsModalButton;