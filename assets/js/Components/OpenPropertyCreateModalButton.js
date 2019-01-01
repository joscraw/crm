'use strict';

import Settings from '../Settings';
import PropertyCreateFormModal from './PropertyCreateFormModal';

class OpenPropertyCreateModalButton {
    constructor($wrapper, globalEventDispatcher) {
        this.init($wrapper, globalEventDispatcher);
    }

    init($wrapper, globalEventDispatcher) {
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;

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
        new PropertyCreateFormModal(this.globalEventDispatcher);
    }

    render() {
        this.$wrapper.append(OpenPropertyCreateModalButton.markup(this));
    }

    static markup() {
        return `
      <button type="button" class="js-open-create-property-modal-btn btn btn-secondary">Create Property</button>
    `;
    }
}

export default OpenPropertyCreateModalButton;