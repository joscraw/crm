'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import Routing from '../Routing';
import ReportWidget from "./ReportWidget";

class CreateWorkflowButton {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier) {
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;

        this.render();
    }


    render() {
        this.$wrapper.html(CreateWorkflowButton.markup(this));
    }

    static markup({portalInternalIdentifier}) {

        return `
        <a class="btn btn-secondary" data-bypass="true" href="${Routing.generate('workflow_type', {internalIdentifier: portalInternalIdentifier})}" role="button">Create Workflow</a>
    `;
    }
}

export default CreateWorkflowButton;