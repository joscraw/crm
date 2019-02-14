'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import ReportFormModal from "./ReportFormModal";

class CreateReportButton {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName) {
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;

        this.$wrapper.on(
            'click',
            '.js-open-create-custom-report-modal-btn',
            this.handleButtonClick.bind(this)
        );

        this.render();
    }

    handleButtonClick() {
        console.log("Create Custom Report Button Clicked");
        this.globalEventDispatcher.publish(Settings.Events.CREATE_RECORD_BUTTON_CLICKED);
        console.log(`Event Dispatched: ${Settings.Events.CREATE_RECORD_BUTTON_CLICKED}`);
        new ReportFormModal(this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
    }

    render() {
        this.$wrapper.html(CreateReportButton.markup(this));
    }

    static markup() {

        return `
      <button type="button" class="js-open-create-custom-report-modal-btn btn btn-secondary">Create Report</button>
    `;
    }
}

export default CreateReportButton;