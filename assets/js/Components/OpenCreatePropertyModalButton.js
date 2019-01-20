'use strict';

import Settings from '../Settings';
import PropertyCreateFormModal from './PropertyCreateFormModal';

class OpenCreatePropertyModalButton {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portal
     * @param customObject
     */
    constructor($wrapper, globalEventDispatcher, portal, customObject) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portal = portal;
        this.customObject = customObject;

        this.$wrapper.on(
            'click',
            '.js-open-create-property-modal-btn',
            this.handleButtonClick.bind(this)
        );

        this.render();
    }

    handleButtonClick() {
        debugger;
        console.log("Create Custom Object Button Clicked");
        this.globalEventDispatcher.publish(Settings.Events.CREATE_PROPERTY_BUTTON_CLICKED);
        console.log(`Event Dispatched: ${Settings.Events.CREATE_PROPERTY_BUTTON_CLICKED}`);
        new PropertyCreateFormModal(this.globalEventDispatcher, this.portal, this.customObject);
    }

    render() {
        this.$wrapper.append(OpenCreatePropertyModalButton.markup(this));
    }

    static markup() {
        return `
      <button type="button" class="js-open-create-property-modal-btn btn btn-secondary">Create Property</button>
    `;
    }
}

export default OpenCreatePropertyModalButton;