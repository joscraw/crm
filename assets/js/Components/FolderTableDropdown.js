'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import EditColumnsButton from "./EditColumnsButton";
import EditListButton from "./EditListButton";
import DeleteListButton from "./DeleteListButton";
import MoveListToFolderButton from "./MoveListToFolderButton";
import EditFolderButton from "./EditFolderButton";
import DeleteFolderButton from "./DeleteFolderButton";

class FolderTableDropdown {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, folderId, title) {
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.folderId = folderId;
        this.title = title;

        this.render();
    }

    render() {
        this.$wrapper.html(FolderTableDropdown.markup(this));

        new EditFolderButton(this.$wrapper.find('.js-edit-folder'), this.globalEventDispatcher, this.portalInternalIdentifier, this.folderId);
        new DeleteFolderButton(this.$wrapper.find('.js-delete-folder'), this.globalEventDispatcher, this.portalInternalIdentifier, this.folderId, 'Delete');

    }

    static markup({title}) {

        return `
        <div class="dropdown">
          <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            ${title}
          </button>
          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" style="overflow:hidden">
             <div class="js-edit-folder"></div>
             <div class="js-delete-folder"></div>
          </div>
        </div>
    `;
    }
}

export default FolderTableDropdown;