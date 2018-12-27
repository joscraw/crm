'use strict';

import Settings from '../Settings';
import CustomObjectFormModal from './CustomObjectFormModal';

class CreateCustomObjectButton {
    constructor($wrapper, globalEventDispatcher) {
        this.init($wrapper, globalEventDispatcher);
    }

    init($wrapper, globalEventDispatcher) {
        this.$wrapper = $wrapper;
        this.title = this.$wrapper.data('title');
        this.buttonClass = this.$wrapper.data('js-button-class');
        this.globalEventDispatcher = globalEventDispatcher;

        this.$wrapper.on(
            'click',
            '.js-create-custom-object-btn',
            this.handleButtonClick.bind(this)
        );

        this.render();
    }

    handleButtonClick() {
        console.log("Create Custom Object Button Clicked");
        this.globalEventDispatcher.publish(Settings.Events.CREATE_CUSTOM_OBJECT_BUTTON_CLICKED);
        console.log(`Event Dispatched: ${Settings.Events.CREATE_CUSTOM_OBJECT_BUTTON_CLICKED}`);
        new CustomObjectFormModal(this.globalEventDispatcher);
    }

    render() {
        this.$wrapper.html(CreateCustomObjectButton.markup(this));
    }

    static markup() {
        return `
      <button type="button" class="js-create-custom-object-btn btn btn-secondary">Create Object</button>
    `;
    }
}

export default CreateCustomObjectButton;