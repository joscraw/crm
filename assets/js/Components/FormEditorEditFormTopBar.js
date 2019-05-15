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

class FormEditorEditFormTopBar {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, form) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.form = form;

        this.unbindEvents();
        this.bindEvents();

        this.globalEventDispatcher.subscribe(
            Settings.Events.FORM_EDITOR_DATA_SAVED,
            this.handleDataSaved.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.FORM_PUBLISHED,
            this.handleDataSaved.bind(this)
        );

        this.render();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            formName: '.js-form-name',
            publishFormButton: '.js-publish-form-button',
            autosaveMessage: '.js-autosave-message'

        }
    }

    bindEvents() {
        this.$wrapper.on('keyup', FormEditorEditFormTopBar._selectors.formName, this.handleFormNameChange.bind(this));
        this.$wrapper.on('click', FormEditorEditFormTopBar._selectors.publishFormButton, this.handlePublishFormButtonClicked.bind(this));
    }

    unbindEvents() {
        this.$wrapper.off('keyup', FormEditorEditFormTopBar._selectors.formName);
        this.$wrapper.off('click', FormEditorEditFormTopBar._selectors.publishFormButton);
    }

    render() {
        this.$wrapper.html(FormEditorEditFormTopBar.markup(this));
        this.setAutoSaveMessage();
    }

    handleDataSaved(form) {
        this.form = form;

        this.setAutoSaveMessage();
    }

    handleFormNameChange(e) {
        if(e.cancelable) {
            e.preventDefault();
        }
        let formName = $(e.target).val();
        this.globalEventDispatcher.publish(Settings.Events.FORM_EDITOR_FORM_NAME_CHANGED, formName);
    }

    setAutoSaveMessage() {
        let autosaveMessage = '';
        if(!_.isEqual(this.form.data, this.form.draft)) {
            autosaveMessage = 'Autosaved with unpublished changes <a href="#">revert</a>';
        }

        this.$wrapper.find(FormEditorEditFormTopBar._selectors.autosaveMessage).html(autosaveMessage);
    }

    handlePublishFormButtonClicked(e) {
        if(e.cancelable) {
            e.preventDefault();
        }
        this.globalEventDispatcher.publish(Settings.Events.FORM_EDITOR_PUBLISH_FORM_BUTTON_CLICKED);
    }

    static markup({portalInternalIdentifier, form}) {

        return `            
            <nav class="navbar fixed-top navbar-expand-sm l-top-bar justify-content-end">
                 <div class="container-fluid">
                    <div class="navbar-collapse collapse dual-nav w-50 order-0">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="btn btn-link" style="color:#FFF" data-bypass="true" href="${Routing.generate('form_settings', {internalIdentifier: portalInternalIdentifier})}" role="button"><i class="fa fa-angle-left" aria-hidden="true"></i> Back to forms</a>
                            </li>
                        </ul>
                    </div>
                    
                    <input style="width: 200px; text-align: left !important;" class="form-control navbar-brand mx-auto d-block text-center order-1 w-25 js-form-name" type="search" placeholder="Form name" aria-label="Search" value="${form.name}">
                    
                    <div class="navbar-collapse collapse dual-nav w-50 order-3">
                        <ul class="nav navbar-nav ml-auto">
                            <li class="nav-item">
                            <span style="color: #FFF; margin-right: 20px;" class="js-autosave-message"></span> <button class="btn btn-lg btn-secondary ml-auto js-publish-form-button">Publish</button>
                            </li>
                        </ul>
                    </div>
                </div>                
             </nav> 
    `;
    }
}

export default FormEditorEditFormTopBar;