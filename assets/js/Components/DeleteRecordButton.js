'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import CustomObjectFormModal from "./CustomObjectFormModal";
import EditCustomObjectFormModal from "./EditCustomObjectFormModal";
import DeleteCustomObjectFormModal from "./DeleteCustomObjectFormModal";
import DeleteReportFormModal from "./DeleteReportFormModal";
import DeleteRecordFormModal from "./DeleteRecordFormModal";

class DeleteRecordButton {

    constructor($wrapper, globalEventDispatcher, portal, recordId, label) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.label = label;
        this.portal = portal;
        this.recordId = recordId;

        this.$wrapper.on(
            'click',
            '.js-open-delete-record-modal-btn',
            this.handleButtonClick.bind(this)
        );
        this.render();
    }

    handleButtonClick() {
        debugger;
        new DeleteRecordFormModal(this.globalEventDispatcher, this.portal, this.recordId);
    }

    render() {
        this.$wrapper.html(DeleteRecordButton.markup(this));
    }

    static markup({label}) {
        return `
      <button type="button" class="js-open-delete-record-modal-btn btn btn-primary btn-sm">${label}</button>
    `;
    }
}

export default DeleteRecordButton;