'use strict';

import Settings from '../Settings';
import CustomObjectFormModal from './CustomObjectFormModal';
import CreateFolderFormModal from "./CreateFolderFormModal";
import EditFolderFormModal from "./EditFolderFormModal";

class EditFolderButton {

    constructor($wrapper, globalEventDispatcher, portal, folderId) {

        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portal = portal;
        this.folderId = folderId;

        this.$wrapper.on(
            'click',
            '.js-edit-folder-btn',
            this.handleButtonClick.bind(this)
        );

        this.render();
    }

    handleButtonClick() {
        new EditFolderFormModal(this.globalEventDispatcher, this.portal, this.folderId);
    }

    render() {
        this.$wrapper.html(EditFolderButton.markup(this));
    }

    static markup() {
        return `
      <button type="button" class="js-edit-folder-btn dropdown-item">Edit</button>
    `;
    }
}

export default EditFolderButton;