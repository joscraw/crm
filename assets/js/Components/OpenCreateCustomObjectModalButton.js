'use strict';

import Settings from '../Settings';
import CustomObjectFormModal from './CustomObjectFormModal';

class OpenCreateCustomObjectModalButton {

    constructor($wrapper, globalEventDispatcher, portal) {
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portal = portal;

        this.$wrapper.on(
            'click',
            '.js-open-create-custom-object-modal-btn',
            this.handleButtonClick.bind(this)
        );

        this.render();
    }

    handleButtonClick() {
        console.log("Create Custom Object Button Clicked");
        this.globalEventDispatcher.publish(Settings.Events.CREATE_CUSTOM_OBJECT_BUTTON_CLICKED);
        console.log(`Event Dispatched: ${Settings.Events.CREATE_CUSTOM_OBJECT_BUTTON_CLICKED}`);
        new CustomObjectFormModal(this.globalEventDispatcher, this.portal);
    }

    render() {
        this.$wrapper.html(OpenCreateCustomObjectModalButton.markup(this));
    }

    static markup() {
        return `
      <button type="button" class="js-open-create-custom-object-modal-btn btn btn-secondary">Create Object</button>
    `;
    }
}

export default OpenCreateCustomObjectModalButton;