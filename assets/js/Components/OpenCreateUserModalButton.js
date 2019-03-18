'use strict';

import Settings from '../Settings';
import PropertyCreateFormModal from './PropertyCreateFormModal';
import UserCreateFormModal from "./UserCreateFormModal";

class OpenCreateUserModalButton {

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
            '.js-open-create-user-modal-btn',
            this.handleButtonClick.bind(this)
        );

        this.render();
    }

    handleButtonClick() {
        console.log("Create User Button Clicked");
        this.globalEventDispatcher.publish(Settings.Events.CREATE_USER_BUTTON_CLICKED);
        console.log(`Event Dispatched: ${Settings.Events.CREATE_USER_BUTTON_CLICKED}`);
        new UserCreateFormModal(this.globalEventDispatcher, this.portalInternalIdentifier);
    }

    render() {
        this.$wrapper.append(OpenCreateUserModalButton.markup(this));
    }

    static markup() {
        return `
      <button type="button" class="js-open-create-user-modal-btn btn btn-secondary">Create User</button>
    `;
    }
}

export default OpenCreateUserModalButton;