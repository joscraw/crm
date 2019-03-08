'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import CustomObjectFormModal from "./CustomObjectFormModal";
import EditCustomObjectFormModal from "./EditCustomObjectFormModal";
import DeleteCustomObjectFormModal from "./DeleteCustomObjectFormModal";
import DeleteReportFormModal from "./DeleteReportFormModal";

class DeleteReportButton {

    constructor($wrapper, globalEventDispatcher, portal, reportId, label) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.label = label;
        this.portal = portal;
        this.reportId = reportId;
        debugger;

        this.$wrapper.on(
            'click',
            '.js-open-delete-report-modal-btn',
            this.handleButtonClick.bind(this)
        );
        this.render();
    }

    handleButtonClick() {
        console.log("Delete Report Button Clicked");
        this.globalEventDispatcher.publish(Settings.Events.DELETE_REPORT_BUTTON_CLICKED);
        console.log(`Event Dispatched: ${Settings.Events.DELETE_REPORT_BUTTON_CLICKED}`);
        new DeleteReportFormModal(this.globalEventDispatcher, this.portal, this.reportId);

    }

    render() {
        this.$wrapper.html(DeleteReportButton.markup(this));
    }

    static markup({label}) {
        return `
      <button type="button" class="js-open-delete-report-modal-btn btn btn-primary btn-sm">${label}</button>
    `;
    }
}

export default DeleteReportButton;