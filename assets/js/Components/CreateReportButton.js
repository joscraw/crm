'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import Routing from '../Routing';
import ReportWidget from "./ReportWidget";

class CreateReportButton {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier) {
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;

        this.$wrapper.on(
            'click',
            '.js-open-create-custom-report-modal-btn',
            this.handleButtonClick.bind(this)
        );

        this.render();
    }

    handleButtonClick() {
        debugger;
        console.log("Create Custom Report Button Clicked");
        this.globalEventDispatcher.publish(Settings.Events.CREATE_RECORD_BUTTON_CLICKED);
        console.log(`Event Dispatched: ${Settings.Events.CREATE_RECORD_BUTTON_CLICKED}`);
    }

    render() {
        this.$wrapper.html(CreateReportButton.markup(this));
    }

    static markup({portalInternalIdentifier}) {

        return `
        <a class="btn btn-secondary" data-bypass="true" href="${Routing.generate('create_report', {internalIdentifier: portalInternalIdentifier})}" role="button">Create Custom Report</a>
    `;
    }
}

export default CreateReportButton;