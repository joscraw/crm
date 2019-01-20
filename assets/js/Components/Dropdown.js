'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import EditColumnsButton from "./EditColumnsButton";

class Dropdown {

    constructor($wrapper, globalEventDispatcher, portal, customObject, customObjectLabel, title) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.customObject = customObject;
        this.customObjectLabel = customObjectLabel;
        this.portal = portal;
        this.title = title;

        this.$wrapper.on(
            'click',
            '.js-open-create-custom-object-modal-btn',
            this.handleButtonClick.bind(this)
        );

        this.render();
    }

    handleButtonClick() {
        console.log("Create Custom Object Button Clicked");
        this.globalEventDispatcher.publish(Settings.Events.CREATE_RECORD_BUTTON_CLICKED);
        console.log(`Event Dispatched: ${Settings.Events.CREATE_RECORD_BUTTON_CLICKED}`);
        new RecordFormModal(this.globalEventDispatcher, this.portal, this.customObject, this.customObjectLabel);
    }

    render() {
        debugger;
        this.$wrapper.html(Dropdown.markup(this));
        new EditColumnsButton(this.$wrapper.find('.js-edit-columns'), this.globalEventDispatcher, this.portal, this.customObject, this.customObjectLabel);

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
            <!--<button class="dropdown-item" type="button">Action</button>
            <button class="dropdown-item" type="button">Another action</button>
            <button class="dropdown-item" type="button">Something else here</button>-->
          </div>
      </div>
    `;
    }
}

export default Dropdown;