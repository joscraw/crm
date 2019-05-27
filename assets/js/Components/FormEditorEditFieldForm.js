'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import $ from "jquery";
import StringHelper from "../StringHelper";

class FormEditorEditFieldForm {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, field) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.field = field;

        this.unbindEvents();

        this.$wrapper.on(
            'click',
            FormEditorEditFieldForm._selectors.backToListButton,
            this.handleBackButtonClicked.bind(this)
        );

        this.$wrapper.on(
            'keyup',
            FormEditorEditFieldForm._selectors.formField,
            this.handleFieldChange.bind(this)
        );

        this.$wrapper.on(
            'change',
            FormEditorEditFieldForm._selectors.formFieldCheckbox,
            this.handleFieldChange.bind(this)
        );

        this.render();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            backToListButton: '.js-back-to-list-button',
            applyFilterForm: '#js-apply-filter-form',
            radioButton: '.js-radio-button',
            editFieldForm: '#js-edit-field-form',
            formField: '.js-form-field',
            formFieldCheckbox: '.js-form-field-checkbox'

        }
    }

    /**
     * Because this component can keep getting run each time a filter is added
     * you need to remove the handlers otherwise they will keep stacking up
     */
    unbindEvents() {
        this.$wrapper.off('click', FormEditorEditFieldForm._selectors.backToListButton);
        this.$wrapper.off('keyup', FormEditorEditFieldForm._selectors.formField);
        this.$wrapper.off('change', FormEditorEditFieldForm._selectors.formFieldCheckbox);
    }

    handleBackButtonClicked() {
        this.globalEventDispatcher.publish(Settings.Events.FORM_EDITOR_BACK_TO_LIST_BUTTON_CLICKED);
    }

    render() {
        this.$wrapper.html(FormEditorEditFieldForm.markup(this));
    }

    handleFieldChange(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        if(document.getElementById("required").checked) {
            document.getElementById('required2').disabled = true;
        }

        const $form = $(FormEditorEditFieldForm._selectors.editFieldForm);
        const formData = {};

        for (let fieldData of $form.serializeArray()) {
            formData[fieldData.name] = fieldData.value
        }

        debugger;
        const field = {...this.field, ...formData};

        this.globalEventDispatcher.publish(Settings.Events.FORM_EDITOR_EDIT_FIELD_FORM_CHANGED, field);
        console.log(`Event Dispatched: ${Settings.Events.FORM_EDITOR_EDIT_FIELD_FORM_CHANGED}`);

    }

    static markup({field}) {

        let checked = 'required' in field ? 'checked' : '';
        let helpText = 'helpText' in field ? field.helpText : '';
        let placeholderText = 'placeholderText' in field ? field.placeholderText : '';

        return `
        <button type="button" class="btn btn-link js-back-to-list-button text-left"><i class="fa fa-chevron-left"></i> Back</button>
        <p class="float-left"><small>Configuration for '${field.label}' field</small></p>
        <form name="editFieldForm" id="js-edit-field-form" novalidate="novalidate" autocomplete="off">
          <div class="form-group">
            <label for="label">Label</label>
            <input name="label" type="text" class="form-control js-form-field" id="label" value="${field.label}">
          </div>
          <div class="form-group">
            <label for="helpText">Help text</label>
            <input name="helpText" type="text" class="form-control js-form-field" id="helpText" value="${helpText}">
          </div>
          <div class="form-group">
            <label for="placeholderText">Placeholder text</label>
            <input name="placeholderText" type="text" class="form-control js-form-field" id="placeholderText" value="${placeholderText}">
          </div>
          <div class="form-check">
            <input type="checkbox" name="required" class="form-check-input js-form-field-checkbox" value="true" id="required" ${checked}>
            <label class="form-check-label" for="required">Required</label>
          </div>
          <input id='required2' type='hidden' value='false' name='required'>
        </form>
    `;
    }
}


const textFieldTemplate = () => `
  <div class="form-group js-operator-value">
    <input type="text" name="value" class="form-control" autocomplete="off">
  </div>
    
`;

export default FormEditorEditFieldForm;