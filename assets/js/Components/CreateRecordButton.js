'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';

class CreateRecordButton {

    constructor($wrapper, globalEventDispatcher, portal, customObject, customObjectLabel) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.customObject = customObject;
        this.customObjectLabel = customObjectLabel;
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
        this.globalEventDispatcher.publish(Settings.Events.CREATE_RECORD_BUTTON_CLICKED);
        console.log(`Event Dispatched: ${Settings.Events.CREATE_RECORD_BUTTON_CLICKED}`);
        new RecordFormModal(this.globalEventDispatcher, this.portal, this.customObject, this.customObjectLabel);
    }

    render() {
        debugger;
        this.$wrapper.html(CreateRecordButton.markup(this));
    }

    static markup({customObjectLabel}) {

        debugger;

        return `
      <button type="button" class="js-open-create-custom-object-modal-btn btn btn-secondary">Create ${customObjectLabel}</button>
    `;
    }
}

export default CreateRecordButton;