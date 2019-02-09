'use strict';

import $ from 'jquery';
import Settings from '../../Settings';

import RecordEditForm from "../RecordEditForm";
import EditRecordTopBar from "../EditRecordTopBar";
import EditDefaultPropertiesWidget from "../EditDefaultPropertiesWidget";


class EditRecord {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     * @param recordId
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, recordId) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.recordId = recordId;

        this.render();
    }

    render() {
        this.$wrapper.html(EditRecord.markup());

        new RecordEditForm($('.js-forms'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, this.recordId);
        new EditRecordTopBar(this.$wrapper.find('.js-top-bar'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, this.recordId);
        new EditDefaultPropertiesWidget(this.$wrapper.find('.js-edit-default-properties-widget'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
    }

    static markup() {

        return `
      <div class="js-record-list-page">
        <div class="l-grid">
            <div class="l-grid__top-bar js-top-bar"></div>
            <div class="l-grid__sub-bar js-sub-bar"></div>
            <div class="l-grid__main-content js-main-content">
                <div class="row">
                    <div class="col-md-4 js-edit-default-properties-widget"></div>
                    <div class="col-md-8 js-forms"></div>
                    <div class="col-md-3"></div>
                    <div class="col-md-3"></div>
                    <div class="col-md-3"></div>
                    <div class="col-md-3"></div>
                </div>  
            </div>
         </div>
       </div>
    `;
    }

}

export default EditRecord;