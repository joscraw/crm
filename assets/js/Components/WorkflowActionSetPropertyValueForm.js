'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import $ from "jquery";

class WorkflowActionSetPropertyValueForm {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, property) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.property = property;

        this.unbindEvents();

        this.$wrapper.on(
            'submit',
            WorkflowActionSetPropertyValueForm._selectors.applyFilterForm,
            this.handleNewFilterFormSubmit.bind(this)
        );

        this.$wrapper.on(
            'click',
            WorkflowActionSetPropertyValueForm._selectors.backToListButton,
            this.handleBackButtonClicked.bind(this)
        );

        this.render();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            backToListButton: '.js-back-to-list-button',
            applyFilterForm: '#js-apply-action-form',
        }
    }

    /**
     * Because this component can keep getting run each time a filter is added
     * you need to remove the handlers otherwise they will keep stacking up
     */
    unbindEvents() {
        this.$wrapper.off('submit', WorkflowActionSetPropertyValueForm._selectors.applyFilterForm);
        this.$wrapper.off('click', WorkflowActionSetPropertyValueForm._selectors.backToListButton);
    }

    handleBackButtonClicked() {
        this.globalEventDispatcher.publish(
            Settings.Events.WORKFLOW_BACK_BUTTON_CLICKED,
            Settings.VIEWS.WORKFLOW_ACTION_SELECT_PROPERTY
        );
    }

    render() {
        this.$wrapper.html(WorkflowActionSetPropertyValueForm.markup(this));

        debugger;
        switch (this.property.fieldType) {
            case 'single_line_text_field':
                this.$wrapper.find('.js-form-fields').html(singleLineTextFieldTemplate());
                break;
            case 'multi_line_text_field':

                break;
            /*   case 'number_field':
                   new EditNumberFieldFilterForm($(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.workflow.customObject.internalName, customFilter);
                   break;
               case 'date_picker_field':
                   new EditDatePickerFieldFilterForm($(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.workflow.customObject.internalName, customFilter);
                   break;
               case 'single_checkbox_field':
                   new EditSingleCheckboxFieldFilterForm($(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.workflow.customObject.internalName, customFilter);
                   break;
               case 'dropdown_select_field':
               case 'radio_select_field':
                   new EditDropdownSelectFieldFilterForm($(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.workflow.customObject.internalName, customFilter);
                   break;
               case 'multiple_checkbox_field':
                   new EditMultipleCheckboxFieldFilterForm($(WorkflowTrigger._selectors.workflowTriggerContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.workflow.customObject.internalName, customFilter);
                   break;*/
        }

    }

    handleNewFilterFormSubmit(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        const $form = $(e.currentTarget);
        const formData = {};

        for (let fieldData of $form.serializeArray()) {
            formData[fieldData.name] = fieldData.value
        }

        this.globalEventDispatcher.publish(Settings.Events.APPLY_WORKFLOW_ACTION_BUTTON_PRESSED, this.property, formData);
    }

    static markup({property: {label}}) {
        return `
        <button type="button" class="btn btn-link js-back-to-list-button text-left"><i class="fa fa-chevron-left"></i> Back</button>
        <p><small>${label}*</small></p>
        <form name="filter" id="js-apply-action-form" novalidate="novalidate">
          <div class="js-form-fields"></div>
          <button type="submit" class="js-apply-action-button btn btn-light btn--full-width">Apply action</button>
        </form>
    `;
    }
}

const singleLineTextFieldTemplate = () => `
    <div class="form-group">
       <input type="text" name="value" class="form-control" autocomplete="off">
    </div>
`;

export default WorkflowActionSetPropertyValueForm;