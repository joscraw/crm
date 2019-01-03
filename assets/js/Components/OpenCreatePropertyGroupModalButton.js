'use strict';

import Settings from '../Settings';
import PropertyGroupFormModal from './PropertyGroupFormModal';

class OpenCreatePropertyGroupModalButton {

    constructor($wrapper, globalEventDispatcher, children) {
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        children.openCreatePropertyGroupModalButton = this;
        this.children = children;

        this.$wrapper.on(
            'click',
            '.js-open-create-property-group-modal-btn',
            this.handleButtonClick.bind(this)
        );

        this.render();
    }

    handleButtonClick() {
        console.log("Create Custom Object Button Clicked");
        this.globalEventDispatcher.publish(Settings.Events.CREATE_PROPERTY_GROUP_BUTTON_CLICKED);
        console.log(`Event Dispatched: ${Settings.Events.CREATE_PROPERTY_GROUP_BUTTON_CLICKED}`);
        new PropertyGroupFormModal(this.globalEventDispatcher, this.children);
    }

    render() {
        this.$wrapper.append(OpenCreatePropertyGroupModalButton.markup(this));
    }

    static markup() {
        return `
      <button type="button" class="js-open-create-property-group-modal-btn btn btn-secondary">Create Property Group</button>
    `;
    }
}

export default OpenCreatePropertyGroupModalButton;