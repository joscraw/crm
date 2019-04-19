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

        this.render();
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