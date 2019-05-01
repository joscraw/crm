'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import EditColumnsButton from "./EditColumnsButton";
import EditListButton from "./EditListButton";
import DeleteListButton from "./DeleteListButton";
import MoveListToFolderButton from "./MoveListToFolderButton";

class ListTableDropdown {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, listId, title) {
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.listId = listId;
        this.title = title;

        this.render();
    }

    render() {
        this.$wrapper.html(ListTableDropdown.markup(this));

        new EditListButton(this.$wrapper.find('.js-edit-list'), this.globalEventDispatcher, this.portalInternalIdentifier, this.listId);
        new DeleteListButton(this.$wrapper.find('.js-delete-list'), this.globalEventDispatcher, this.portalInternalIdentifier, this.listId, 'Delete');
        new MoveListToFolderButton(this.$wrapper.find('.js-move-list-to-folder'), this.globalEventDispatcher, this.portalInternalIdentifier, this.listId, 'Move to folder');

    }

    static markup({title}) {

        return `
        <div class="dropdown">
          <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            ${title}
          </button>
          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" style="overflow:hidden">
             <div class="js-edit-list"></div>
             <div class="js-delete-list"></div>
             <div class="js-move-list-to-folder"></div>
          </div>
        </div>
    `;
    }
}

export default ListTableDropdown;