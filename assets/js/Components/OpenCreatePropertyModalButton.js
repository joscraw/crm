'use strict';

import Settings from '../Settings';
import PropertyCreateFormModal from './PropertyCreateFormModal';

class OpenCreatePropertyModalButton {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName) {
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;

        this.$wrapper.on(
            'click',
            '.js-open-create-property-modal-btn',
            this.handleButtonClick.bind(this)
        );

        this.render();
    }

    handleButtonClick() {
        console.log("Create Custom Object Button Clicked");
        this.globalEventDispatcher.publish(Settings.Events.CREATE_PROPERTY_BUTTON_CLICKED);
        console.log(`Event Dispatched: ${Settings.Events.CREATE_PROPERTY_BUTTON_CLICKED}`);
        new PropertyCreateFormModal(this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
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