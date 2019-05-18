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
import FormEditorEditFormTopBar from "./FormEditorEditFormTopBar";
import FormEditorShareYourFormModal from "./FormEditorShareYourFormModal";


class FormEditorEditForm {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, uid) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.uid = uid;
        this.form = null;

        this.unbindEvents();

        this.bindEvents();

        this.globalEventDispatcher.singleSubscribe(
            Settings.Events.FORM_EDITOR_BACK_TO_LIST_BUTTON_CLICKED,
            this.handleBackButtonClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.FORM_EDITOR_PROPERTY_LIST_ITEM_CLICKED,
            this.handlePropertyListItemClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.FORM_PREVIEW_DELETE_BUTTON_CLICKED,
            this.handleDeleteButtonClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.FORM_PREVIEW_EDIT_BUTTON_CLICKED,
            this.handleEditButtonClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.FORM_EDITOR_FIELD_ORDER_CHANGED,
            this.handleFieldOrderChanged.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.FORM_EDITOR_EDIT_FIELD_FORM_CHANGED,
            this.handleEditFieldFormChanged.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.FORM_EDITOR_FORM_NAME_CHANGED,
            this.handleFormNameChange.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.FORM_EDITOR_PUBLISH_FORM_BUTTON_CLICKED,
            this.handlePublishFormButtonClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.FORM_EDITOR_REVERT_BUTTON_CLICKED,
            this.handleRevertButtonClicked.bind(this)
        );

        this.loadForm().then((data) => {
            this.form = data.data;
            this.render();
        });
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {

            propertyList: '.js-property-list',
            formContainer: '.js-form',
            editFieldForm: '.js-edit-field-form',
            topBar: '.js-top-bar'

        }
    }

    bindEvents() {}

    unbindEvents() {}

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

    handleDeleteButtonClicked(uid) {
        this.form.draft = $.grep(this.form.draft, function(form){
            return !(form.uid === uid);
        });

        this.saveFormData();
    }

    handleEditButtonClicked(uid) {
        let fields = this.form.draft;

        let field = fields.filter(field => {
            return field.uid === uid;
        });

        this.$wrapper.find(FormEditorEditForm._selectors.propertyList).addClass('d-none');
        this.$wrapper.find(FormEditorEditForm._selectors.editFieldForm).removeClass('d-none');

        new FormEditorEditFieldForm($(FormEditorEditForm._selectors.editFieldForm), this.globalEventDispatcher, this.portalInternalIdentifier, field[0]);
    }

    handleFormNameChange(formName) {
        this.form.name = formName;
        this.saveFormData();
    }

    handleFieldOrderChanged(fieldOrder) {

        let fields = this.form.draft;
        this.form.draft = [];
        for(let i = 0; i < fieldOrder.length; i++) {

            let field = fields.filter(field => {
                return field.uid === fieldOrder[i];
            });

            this.form.draft[i] = field[0];
        }

        this.saveFormData();
    }

    handleEditFieldFormChanged(field) {

        let index = this.form.draft.findIndex(f => f.uid === field.uid);
        this.form.draft[index] = field;

        this.saveFormData();
    }

    handlePropertyListItemClicked(property) {

        let uID = StringHelper.makeCharId();
        _.set(property, 'uid', uID);

        this.form.draft.push(property);

        this.saveFormData();
    }

    handleRevertButtonClicked() {

        debugger;
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

    handleBackButtonClicked() {
        this.$wrapper.find(FormEditorEditForm._selectors.propertyList).removeClass('d-none');
        this.$wrapper.find(FormEditorEditForm._selectors.editFieldForm).addClass('d-none');
    }

    render() {
        this.$wrapper.html(FormEditorEditForm.markup(this));
        new FormEditorEditFormTopBar(this.$wrapper.find(FormEditorEditForm._selectors.topBar), this.globalEventDispatcher, this.portalInternalIdentifier, this.form);
        new FormEditorPropertyList($(FormEditorEditForm._selectors.propertyList), this.globalEventDispatcher, this.portalInternalIdentifier, this.form);
        new FormEditorFormPreview(this.$wrapper.find(FormEditorEditForm._selectors.formContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.form);
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
             
            <div class="t-private-template">                 
                <div class="t-private-template__inner">
                    <div class="t-private-template__sidebar js-property-list"></div>
                    <div class="t-private-template__sidebar js-edit-field-form d-none"></div>
                    <div class="t-private-template__main js-form" style="background-color: rgb(245, 248, 250);"></div>
                </div>
            </div>  
    `;
    }
}

export default FormEditorEditForm;