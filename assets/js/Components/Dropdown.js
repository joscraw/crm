'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import EditColumnsButton from "./EditColumnsButton";
import RecordImportButton from "./RecordImportButton";

class Dropdown {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, title) {
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.title = title;

        this.render();
    }

    render() {
        this.$wrapper.html(Dropdown.markup(this));
        new EditColumnsButton(this.$wrapper.find('.js-edit-columns'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
        new RecordImportButton(this.$wrapper.find('.js-import'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);

    }

    static markup({title}) {

        return `
      <div class="dropdown">
          <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            ${title}
          </button>
          <div class="dropdown-menu" aria-labelledby="dropdownMenu2">
               <div class="js-edit-columns"></div>
               <div class="js-import"></div>
          </div>
      </div>
    `;
    }
}

export default Dropdown;