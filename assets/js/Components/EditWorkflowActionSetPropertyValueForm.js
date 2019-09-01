'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import $ from "jquery";

require('jquery-ui-dist/jquery-ui');
require('jquery-ui-dist/jquery-ui.css');
require('bootstrap-datepicker');
require('bootstrap-datepicker/dist/css/bootstrap-datepicker.css');

class EditWorkflowActionSetPropertyValueForm {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, action) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.property = action.property;
        this.action = action;

        this.unbindEvents();

        this.$wrapper.on(
            'click',
            EditWorkflowActionSetPropertyValueForm._selectors.radioButton,
            this.handleOperatorRadioButtonClicked.bind(this)
        );

        this.$wrapper.on(
            'submit',
            EditWorkflowActionSetPropertyValueForm._selectors.applyFilterForm,
            this.handleNewFilterFormSubmit.bind(this)
        );

        this.$wrapper.on(
            'click',
            EditWorkflowActionSetPropertyValueForm._selectors.backToListButton,
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
            radioButton: '.js-radio-button',
        }
    }

    /**
     * Because this component can keep getting run each time a filter is added
     * you need to remove the handlers otherwise they will keep stacking up
     */
    unbindEvents() {
        this.$wrapper.off('submit', EditWorkflowActionSetPropertyValueForm._selectors.applyFilterForm);
        this.$wrapper.off('click', EditWorkflowActionSetPropertyValueForm._selectors.backToListButton);
    }

    handleBackButtonClicked() {
        this.globalEventDispatcher.publish(
            Settings.Events.WORKFLOW_BACK_BUTTON_CLICKED,
            Settings.VIEWS.WORKFLOW_ACTION_SELECT_PROPERTY
        );
    }

    render() {
        this.$wrapper.html(EditWorkflowActionSetPropertyValueForm.markup(this));
        let values = [];

        switch (this.property.fieldType) {
            case 'single_line_text_field':
                this.$wrapper.find('.js-form-fields').html(singleLineTextFieldTemplate());
                this.$wrapper.find('input[type="text"]').val(this.action.value);
                break;
            case 'multi_line_text_field':
                this.$wrapper.find('.js-form-fields').html(multiLineTextFieldTemplate());
                break;
            case 'dropdown_select_field':
            case 'multiple_checkbox_field':
                this.$wrapper.find('.js-form-fields').html(selectTemplate());
                this.activatePlugins();
                values = this.action.value.split(",");
                this.s.selectize()[0].selectize.setValue(values);
                break;
            case 'number_field':
                this.$wrapper.find('.js-form-fields').html(numberTemplate());
                this.$wrapper.find('.js-radio-button').first().click();
                break;
            case 'date_picker_field':
                this.$wrapper.find('.js-form-fields').html(dateTemplate());
                this.$wrapper.find('.js-datepicker').datepicker('setDate', new Date(this.action.value));
                this.$wrapper.find('.js-datepicker').datepicker('update');
                this.activatePlugins();
                break;
            case 'single_checkbox_field':
            case 'radio_select_field':
                this.$wrapper.find('.js-form-fields').html(singleSelectTemplate());
                this.activatePlugins();
                values = this.action.value.split(",");
                this.s.selectize()[0].selectize.setValue(values);
                break;
        }
    }

    setRadioOption() {

        debugger;
        this.$wrapper.find('.js-radio-button').each((index, element) => {

            debugger;
            let value = $(element).val();
            if(this.customFilter.operator === value) {

                $(element).click();

                let values = this.customFilter.value.split(",");
                this.s.selectize()[0].selectize.setValue(values);
            }
        });
    }

    activatePlugins() {

        let options = [];
        switch (this.property.fieldType) {
            case 'dropdown_select_field':
            case 'multiple_checkbox_field':
                for(let i = 0; i < this.property.field.options.length; i++) {
                    let option = this.property.field.options[i];
                    options.push({value: option.label, name: option.label});
                }
                this.s = $('.js-selectize-multiple-select').selectize({
                    plugins: ['remove_button'],
                    valueField: 'value',
                    labelField: 'name',
                    searchField: ['name'],
                    options: options
                });
                break;
            case 'radio_select_field':
                for(let i = 0; i < this.property.field.options.length; i++) {
                    let option = this.property.field.options[i];
                    options.push({value: option.label, name: option.label});
                }
                this.s = $('.js-selectize-single-select').selectize({
                    maxItems: 1,
                    valueField: 'value',
                    labelField: 'name',
                    searchField: ['name'],
                    options: options
                });
                break;
            case 'date_picker_field':
                $('.js-datepicker').datepicker({
                    format: 'mm-dd-yyyy'
                });
                break;
            case 'single_checkbox_field':
                this.s = $('.js-selectize-single-select').selectize({
                    maxItems: 1,
                    valueField: 'value',
                    labelField: 'name',
                    searchField: ['name'],
                    options: [
                        {value: 0, name: 'No'},
                        {value: 1, name: 'Yes'}
                    ],
                });
                break;
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

    handleOperatorRadioButtonClicked(e) {
        debugger;
        this.$wrapper.find('.js-operator-value').remove();
        let $radioButton = $(e.currentTarget);
        if($radioButton.attr('data-has-text-input')) {
            const html = textFieldTemplate();
            const $textField = $($.parseHTML(html));
            $radioButton.closest('div').after($textField);
        }
    }

    static markup({property: {label}}) {
        return `
        <button type="button" class="btn btn-link js-back-to-list-button text-left" style="padding:0"><i class="fa fa-chevron-left"></i> Back</button>
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
       <input type="hidden" name="operator" value="SET_VALUE">
       <input type="text" name="value" class="form-control" autocomplete="off">
    </div>
`;

const textFieldTemplate = () => `
  <div class="form-group js-operator-value">
    <input type="text" name="value" class="form-control" autocomplete="off">
  </div>
`;

const multiLineTextFieldTemplate = () => `
    <div class="form-group">
        <input type="hidden" name="operator" value="SET_VALUE">
        <textarea class="form-control" name="value" rows="3"></textarea>
    </div>
`;

const selectTemplate = () => `
  <div class="form-group js-operator-value">
    <input type="hidden" name="operator" value="SET_VALUE">
    <input type="text" name="value" class="form-control js-selectize-multiple-select">
  </div>
`;

const singleSelectTemplate = () => `
  <div class="form-group js-operator-value">
    <input type="hidden" name="operator" value="SET_VALUE">
    <input type="text" name="value" class="form-control js-selectize-single-select">
  </div>
`;

const dateTemplate = () => `
  <div class="form-group js-operator-value">
    <input type="hidden" name="operator" value="SET_VALUE">
    <input type="text" name="value" class="form-control js-datepicker" autocomplete="off">
  </div>
    
`;

const numberTemplate = () => `
    <div class="form-check">
        <input class="form-check-input js-radio-button" type="radio" name="operator" id="editOperator1" value="SET_VALUE" data-has-text-input="true">
        <label class="form-check-label" for="editOperator1">
         <p>Set value</p>
        </label>
    </div>
    <div class="form-check">
        <input class="form-check-input js-radio-button" type="radio" name="operator" id="editOperator2" value="INCREMENT_BY" data-has-text-input="true">
        <label class="form-check-label" for="editOperator2">
        <p>Increment by</p>
        </label>
    </div>
    <div class="form-check">
        <input class="form-check-input js-radio-button" type="radio" name="operator" id="editOperator3" value="DECREMENT_BY" data-has-text-input="true">
        <label class="form-check-label" for="editOperator3">
         <p>Decrement by</p>
        </label>
    </div>
`;

export default EditWorkflowActionSetPropertyValueForm;