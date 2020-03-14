'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import CustomObjectFormModal from "./CustomObjectFormModal";
import EditCustomObjectFormModal from "./EditCustomObjectFormModal";
import DeleteCustomObjectFormModal from "./DeleteCustomObjectFormModal";
import DeleteReportFormModal from "./DeleteReportFormModal";
import DeleteListFormModal from "./DeleteListFormModal";
import MoveListToFolderFormModal from "./MoveListToFolderFormModal";
import ReportConnectObjectFormModal from "./ReportConnectObjectFormModal";

class ReportConnectObjectButton {

    constructor($wrapper, globalEventDispatcher, portal, customObjectInternalName) {
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portal = portal;
        this.customObjectInternalName = customObjectInternalName;

        this.$wrapper.on(
            'click',
            '.js-connect-object-btn',
            this.handleButtonClick.bind(this)
        );
        this.render();
    }

    handleButtonClick() {

        new ReportConnectObjectFormModal(this.globalEventDispatcher, this.portal, this.customObjectInternalName);
    }

    render() {
        this.$wrapper.html(ReportConnectObjectButton.markup(this));
    }

    static markup() {
        return `
      <button type="button" class="js-connect-object-btn btn btn-link">Create Join</button>
    `;
    }
}

export default ReportConnectObjectButton;