'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';

class CreateRecordButton {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;

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
        new RecordFormModal(this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
    }

    render() {
        debugger;
        this.$wrapper.html(CreateRecordButton.markup(this));
    }

    static markup() {

        debugger;

        return `
      <button type="button" class="js-open-create-custom-object-modal-btn btn btn-secondary">Create Record</button>
    `;
    }
}

export default CreateRecordButton;