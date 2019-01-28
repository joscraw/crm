'use strict';

import $ from 'jquery';
import Settings from '../../Settings';

import RecordEditForm from "../RecordEditForm";


class EditRecord {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param internalIdentifier
     * @param internalName
     * @param recordId
     */
    constructor($wrapper, globalEventDispatcher, internalIdentifier, internalName, recordId) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.internalIdentifier = internalIdentifier;
        this.internalName = internalName;
        this.recordId = recordId;

        this.render();
    }

    render() {
        this.$wrapper.html(EditRecord.markup());

        debugger;
        new RecordEditForm($('.js-main-content'), this.globalEventDispatcher, this.internalIdentifier, this.internalName, this.recordId);

/*        new PropertySettingsTopBar(this.$wrapper.find('.js-top-bar'), this.globalEventDispatcher, this.portal, this.customObject);
        new PropertyList(this.$wrapper.find('.js-main-content'), this.globalEventDispatcher, this.portal, this.customObject, this.customObjectInternalName);*/

    }

    static markup() {

        return `
      <div class="js-record-list-page">
        <div class="l-grid">
            <div class="l-grid__top-bar js-top-bar"></div>
            <div class="l-grid__sub-bar js-sub-bar"></div>
            <div class="l-grid__main-content js-main-content"></div>
        </div>
      </div>
    `;
    }

}

export default EditRecord;