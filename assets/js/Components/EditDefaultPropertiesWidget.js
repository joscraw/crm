'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import CreateRecordButton from "./CreateRecordButton";
import EditColumnsModal from "./EditColumnsModal";
import EditDefaultPropertiesButton from "./EditDefaultPropertiesButton";

class EditDefaultPropertiesWidget {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;

        this.render();
    }

    render() {
        this.$wrapper.html(EditDefaultPropertiesWidget.markup(this));
        new EditDefaultPropertiesButton(this.$wrapper.find('.js-edit-default-properties-button-container'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
    }

    static markup() {

        debugger;

        return `
          <div class="card">
              <div class="card-body">
                <h2>Default Properties</h2>
                <p>These contact properties will appear on every contact record you initially create. Here you can change both visibility and order.</p>
                <div class="js-edit-default-properties-button-container"></div>
              </div>
          </div>
    `;
    }
}

export default EditDefaultPropertiesWidget;