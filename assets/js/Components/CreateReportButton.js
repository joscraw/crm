'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import ReportFormModal from "./ReportFormModal";
import Routing from '../Routing';

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

    static markup({portalInternalIdentifier}) {

        return `
        <a class="btn btn-secondary" href="${ Routing.generate('report_list', {internalIdentifier: portalInternalIdentifier}) }/create" role="button">Create Custom Report</a>
    `;
    }
}

export default CreateReportButton;