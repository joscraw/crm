'use strict';

import Settings from '../Settings';
import CustomObjectFormModal from './CustomObjectFormModal';
import CreateFolderFormModal from "./CreateFolderFormModal";

class CreateFolderButton {

    constructor($wrapper, globalEventDispatcher, portal, folderId) {

        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portal = portal;
        this.folderId = folderId;

        this.$wrapper.on(
            'click',
            '.js-create-folder-btn',
            this.handleButtonClick.bind(this)
        );

        this.render();
    }

    handleButtonClick() {
        console.log("Create Folder Button Clicked");
        this.globalEventDispatcher.publish(Settings.Events.CREATE_FOLDER_BUTTON_CLICKED);
        console.log(`Event Dispatched: ${Settings.Events.CREATE_FOLDER_BUTTON_CLICKED}`);
        new CreateFolderFormModal(this.globalEventDispatcher, this.portal, this.folderId);
    }

    render() {
        this.$wrapper.html(CreateFolderButton.markup(this));
    }

    static markup() {
        return `
      <button type="button" class="js-create-folder-btn btn btn-light">Create Folder</button>
    `;
    }
}

export default CreateFolderButton;