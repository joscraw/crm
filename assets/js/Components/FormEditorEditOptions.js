'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import swal from 'sweetalert2';
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import FilterList from "./FilterList";
import FilterNavigation from "./FilterNavigation";
import EditSingleLineTextFieldFilterForm from "./EditSingleLineTextFieldFilterForm";
import NumberFieldFilterForm from "./NumberFieldFilterForm";
import EditNumberFieldFilterForm from "./EditNumberFieldFilterForm";
import DatePickerFieldFilterForm from "./DatePickerFieldFilterForm";
import SingleCheckboxFieldFilterForm from "./SingleCheckboxFieldFilterForm";
import EditDatePickerFieldFilterForm from "./EditDatePickerFieldFilterForm";
import EditSingleCheckboxFieldFilterForm from "./EditSingleCheckboxFieldFilterForm";
import DropdownSelectFieldFilterForm from "./DropdownSelectFieldFilterForm";
import EditDropdownSelectFieldFilterForm from "./EditDropdownSelectFieldFilterForm";
import MultilpleCheckboxFieldFilterForm from "./MultilpleCheckboxFieldFilterForm";
import EditMultipleCheckboxFieldFilterForm from "./EditMultipleCheckboxFieldFilterForm";
import ArrayHelper from "../ArrayHelper";
import ReportSelectCustomObject from "./ReportSelectCustomObject";
import ReportPropertyList from "./ReportPropertyList";
import ReportSelectedColumns from "./ReportSelectedColumns";
import ReportSelectedColumnsCount from "./ReportSelectedColumnsCount";
import ReportFilterList from "./ReportFilterList";
import ReportFilters from "./ReportFilters";
import ReportProperties from "./ReportProperties";
import ListPropertyList from "./ListPropertyList";
import ListSelectedColumns from "./ListSelectedColumns";
import ListSelectedColumnsCount from "./ListSelectedColumnsCount";
import FormEditorPropertyList from "./FormEditorPropertyList";
import StringHelper from "../StringHelper";
import FormEditorFormPreview from "./FormEditorFormPreview";
import FormEditorEditFieldForm from "./FormEditorEditFieldForm";
import FormEditorTopBar from "./FormEditorTopBar";
import FormEditorShareYourFormModal from "./FormEditorShareYourFormModal";
import FormEditorSubBar from "./FormEditorSubBar";
import ContextHelper from "../ContextHelper";


class FormEditorEditOptions {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, uid) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.uid = uid;
        this.form = null;

        this.unbindEvents();
        this.bindEvents();
        this.globalEventDispatcher.removeRemovableTokens();

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
            Settings.Events.FORM_EDITOR_FORM_NAME_CHANGED,
            this.handleFormNameChange.bind(this)
        ));

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
            Settings.Events.FORM_EDITOR_REVERT_BUTTON_CLICKED,
            this.handleRevertButtonClicked.bind(this)
        ));

        debugger;
        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
            Settings.Events.FORM_EDITOR_PUBLISH_FORM_BUTTON_CLICKED,
            this.handlePublishFormButtonClicked.bind(this)
        ));

        this.loadForm().then((data) => {
            this.form = data.data;
            this.render();
            this.loadEditOptionsForm();
        });
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            editOptionsFormContainer: '.js-edit-options-form-container',
            editOptionsForm: '.js-form-editor-edit-options-form',
            topBar: '.js-top-bar',
            subBar: '.js-sub-bar',
            submitAction: '.js-submit-action',
            formField: '.js-form-field',
            radioField: '.js-radio-field'
        }
    }

    bindEvents() {
        this.$wrapper.on('change', FormEditorEditOptions._selectors.submitAction, this.handleSubmitActionChange.bind(this));
        this.$wrapper.on('change', FormEditorEditOptions._selectors.radioField, this.handleRadioFieldChange.bind(this));
        this.$wrapper.on('keyup', FormEditorEditOptions._selectors.formField, this.handleSubmitActionKeyup.bind(this));
    }

    unbindEvents() {
        this.$wrapper.off('change', FormEditorEditOptions._selectors.submitAction);
        this.$wrapper.off('change', FormEditorEditOptions._selectors.radioField);
        this.$wrapper.off('keyup', FormEditorEditFieldForm._selectors.formField);

    }

    bindCKEditorEvents() {
        for (var i in CKEDITOR.instances) {
            CKEDITOR.instances[i].on('change', this.handleCKEditorChange.bind(this));
        }
    }

    loadEditOptionsForm() {
        $.ajax({
            url: Routing.generate('get_edit_options_form', {internalIdentifier: this.portalInternalIdentifier, uid: this.uid}),
        }).then(data => {
            this.$wrapper.find(FormEditorEditOptions._selectors.editOptionsFormContainer).html(data.formMarkup);
            this.bindCKEditorEvents();

        })
    }

    handleCKEditorChange(e) {
        if(e.cancelable) {
            e.preventDefault();
        }

        console.log($(e.editor.document.$.body).html());
        const $form = $(FormEditorEditOptions._selectors.editOptionsForm);
        let formData = new FormData($form.get(0));

        this._submitEditOptionsForm(formData)
            .then((data) => {
                this.globalEventDispatcher.publish(Settings.Events.PROPERTY_EDITED);
            }).catch((errorData) => {
            this.$wrapper.html(errorData.formMarkup);
        });
    }

    handleSubmitActionKeyup(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        const $form = $(FormEditorEditOptions._selectors.editOptionsForm);
        let formData = new FormData($form.get(0));

        this._submitEditOptionsForm(formData)
            .then((data) => {
                this.globalEventDispatcher.publish(Settings.Events.PROPERTY_EDITED);
            }).catch((errorData) => {
            this.$wrapper.html(errorData.formMarkup);
        });
    }

    /**
     * @param data
     * @return {Promise<any>}
     * @private
     */
    _submitEditOptionsForm(data) {
        return new Promise( (resolve, reject) => {
            const url = Routing.generate('submit_edit_options_form', {internalIdentifier: this.portalInternalIdentifier, uid: this.form.uid});

            $.ajax({
                url,
                method: 'POST',
                data: data,
                processData: false,
                contentType: false
            }).then((data, textStatus, jqXHR) => {
                resolve(data);
            }).catch((jqXHR) => {
                const errorData = JSON.parse(jqXHR.responseText);
                errorData.httpCode = jqXHR.status;
                reject(errorData);
            });
        });
    }

    handleSubmitActionChange(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        const formData = {};
        formData[$(e.target).attr('name')] = $(e.target).val();
        formData['skip_validation'] = true;

        this._changeSubmitAction(formData).then((data) => {}).catch((errorData) => {

            this.$wrapper.find(FormEditorEditOptions._selectors.editOptionsFormContainer).html(errorData.formMarkup);
            this.bindCKEditorEvents();
        });
    }

    handleRadioFieldChange(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        const $form = $(FormEditorEditOptions._selectors.editOptionsForm);
        let formData = new FormData($form.get(0));

        this._changeRadio(formData).then((data) => {}).catch((errorData) => {});
    }

    _changeSubmitAction(data) {

        debugger;
        return new Promise((resolve, reject) => {
            const url = Routing.generate('submit_edit_options_form', {internalIdentifier: this.portalInternalIdentifier, uid: this.form.uid});

            $.ajax({
                url,
                method: 'POST',
                data: data
            }).then((data, textStatus, jqXHR) => {
                resolve(data);
            }).catch((jqXHR) => {
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }

    _changeRadio(data) {

        debugger;
        return new Promise((resolve, reject) => {
            const url = Routing.generate('submit_edit_options_form', {internalIdentifier: this.portalInternalIdentifier, uid: this.form.uid});

            $.ajax({
                url,
                method: 'POST',
                data: data,
                processData: false,
                contentType: false
            }).then((data, textStatus, jqXHR) => {
                resolve(data);
            }).catch((jqXHR) => {
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }

    handlePublishFormButtonClicked() {

        if(this.form.name === '') {
            swal("Woahhh snap!!!", "Don't forget a name for your form.", "warning");
            return;
        }

        if(_.isEmpty(this.form.draft)) {
            swal("Woahhh snap!!!", "Don't forget to select at least one field for your form.", "warning");
            return;
        }

        this.form.published = true;
        this.form.data = _.cloneDeep(this.form.draft);
        this.publishForm().then(() => {
            this.globalEventDispatcher.publish(Settings.Events.FORM_PUBLISHED, this.form);

            swal("Sweeeet!", "Form successully published.", "success").then(() => {
                new FormEditorShareYourFormModal(this.globalEventDispatcher, this.portalInternalIdentifier, this.form);
            });
        });
    }

    handleFormNameChange(formName) {
        this.form.name = formName;
        this.saveFormData();
    }

    handleRevertButtonClicked() {

        this.form.draft = _.cloneDeep(this.form.data);

        this.saveFormData();
    }

    saveFormData() {
        this._saveForm().then(() => {
            this.globalEventDispatcher.publish(Settings.Events.FORM_EDITOR_DATA_SAVED, this.form);
        }).catch((errorData) => {});
    }

    _saveForm() {

        return new Promise((resolve, reject) => {
            const url = Routing.generate('save_form', {internalIdentifier: this.portalInternalIdentifier, uid: this.uid});

            $.ajax({
                url,
                method: 'POST',
                data: {'form': this.form}
            }).then((data, textStatus, jqXHR) => {

                debugger;
                resolve(data);

            }).catch((jqXHR) => {
                debugger;
                const errorData = JSON.parse(jqXHR.responseText);
                errorData.httpCode = jqXHR.status;

                reject(errorData);
            });
        });

    }

    publishForm() {
        return new Promise((resolve, reject) => {
            const url = Routing.generate('publish_form', {internalIdentifier: this.portalInternalIdentifier, uid: this.uid});
            $.ajax({
                url,
                method: 'POST'
            }).then((data, textStatus, jqXHR) => {
                resolve(data);
            }).catch((jqXHR) => {
                const errorData = JSON.parse(jqXHR.responseText);
                errorData.httpCode = jqXHR.status;
                reject(errorData);
            });
        });
    }

    render() {
        this.$wrapper.html(FormEditorEditOptions.markup(this));
        new FormEditorTopBar(this.$wrapper.find(FormEditorEditOptions._selectors.topBar), this.globalEventDispatcher, this.portalInternalIdentifier, this.form);
        new FormEditorSubBar(this.$wrapper.find(FormEditorEditOptions._selectors.subBar), this.globalEventDispatcher, this.portalInternalIdentifier, this.form, Settings.PAGES.FORM_EDITOR_EDIT_OPTIONS);
    }

    loadForm() {
        return new Promise((resolve, reject) => {
            const url = Routing.generate('get_form_data', {uid: this.uid});
            $.ajax({
                url: url
            }).then(data => {
                resolve(data);
            }).catch(jqXHR => {
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }

    static markup() {
        return `            
           <div class="js-top-bar"></div>
           <div class="js-sub-bar"></div>
             
            <div class="t-private-template">                 
                <div class="t-private-template__inner t-private-template__inner--center ">
                    <div class="js-edit-options-form-container"></div>
                </div>
            </div>  
    `;
    }
}

export default FormEditorEditOptions;