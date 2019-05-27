'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import CustomObjectFormModal from "./CustomObjectFormModal";
import EditCustomObjectFormModal from "./EditCustomObjectFormModal";
import DeleteCustomObjectFormModal from "./DeleteCustomObjectFormModal";
import DeleteReportFormModal from "./DeleteReportFormModal";
import DeleteFormModal from "./DeleteFormModal";

class DeleteFormButton {

    constructor($wrapper, globalEventDispatcher, portal, uid, label) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.label = label;
        this.portal = portal;
        this.uid = uid;

        this.$wrapper.on(
            'click',
            '.js-open-delete-form-modal-btn',
            this.handleButtonClick.bind(this)
        );
        this.render();
    }

    handleButtonClick() {
        console.log("Delete Form Button Clicked");
        this.globalEventDispatcher.publish(Settings.Events.DELETE_FORM_BUTTON_CLICKED);
        console.log(`Event Dispatched: ${Settings.Events.DELETE_FORM_BUTTON_CLICKED}`);
        new DeleteFormModal(this.globalEventDispatcher, this.portal, this.uid);

    }

    render() {
        this.$wrapper.html(DeleteFormButton.markup(this));
    }

    static markup({label}) {
        return `
      <button type="button" class="js-open-delete-form-modal-btn btn btn-primary btn-sm">${label}</button>
    `;
    }
}

export default DeleteFormButton;