'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import EditColumnsButton from "./EditColumnsButton";

class Dropdown {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, title) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.title = title;

        this.render();
    }

    render() {
        debugger;
        this.$wrapper.html(Dropdown.markup(this));
        new EditColumnsButton(this.$wrapper.find('.js-edit-columns'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);

    }

    static markup({title}) {

        debugger;

        return `
      <div class="dropdown">
          <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            ${title}
          </button>
          <div class="dropdown-menu" aria-labelledby="dropdownMenu2">
               <div class="js-edit-columns"></div>
          </div>
      </div>
    `;
    }
}

export default Dropdown;