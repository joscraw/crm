'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import CreateRecordButton from "./CreateRecordButton";
import EditColumnsModal from "./EditColumnsModal";
import RecordImportModal from "./RecordImportModal";

class RecordImportButton {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;

        this.$wrapper.on(
            'click',
            '.js-record-import-button',
            this.handleButtonClick.bind(this)
        );

        this.render();
    }

    handleButtonClick() {
        new RecordImportModal(this.globalEventDispatcher,  this.portalInternalIdentifier, this.customObjectInternalName);
    }

    render() {
        this.$wrapper.html(RecordImportButton.markup(this));
    }

    static markup() {

        return `
      <button type="button" class="dropdown-item js-record-import-button">Import Records</button>
    `;
    }
}

export default RecordImportButton;