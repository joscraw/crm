'use strict';

import Settings from '../Settings';
import CustomObjectFormModal from './CustomObjectFormModal';
import CreateFolderFormModal from "./CreateFolderFormModal";
import BulkEditFormModal from "./BulkEditFormModal";

class BulkEditButton {

    constructor($wrapper, globalEventDispatcher, portal, customObjectInternalName, data) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portal = portal;
        this.customObjectInternalName = customObjectInternalName;
        this.data = data;

        this.$wrapper.on(
            'click',
            '.js-bulk-edit-btn',
            this.handleButtonClick.bind(this)
        );

        this.render();
    }

    handleButtonClick() {
        new BulkEditFormModal(this.globalEventDispatcher, this.portal, this.customObjectInternalName, this.data);
    }

    render() {
        this.$wrapper.html(BulkEditButton.markup(this));
    }

    static markup() {
        return `
      <button type="button" class="js-bulk-edit-btn btn btn-link">Bulk Edit</button>
    `;
    }
}

export default BulkEditButton;