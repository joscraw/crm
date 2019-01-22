'use strict';

import $ from 'jquery';
import Settings from '../../Settings';
import PropertySettingsTopBar from './../PropertySettingsTopBar';
import PropertyList from "./../PropertyList";
import PropertyGroupFormModal from "./../PropertyGroupFormModal";
import CustomObjectSettingsTopBar from "../CustomObjectSettingsTopBar";
import CustomObjectList from "../CustomObjectList";
import PropertyCreateForm from "../PropertyCreateForm";
import PropertyEditForm from "../PropertyEditForm";


class EditProperty {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param propertyInternalName
     */
    constructor($wrapper, globalEventDispatcher, propertyInternalName) {
        debugger;
        this.customObject = $wrapper.data('customObject');
        this.portal = $wrapper.data('portal');
        this.customObjectInternalName = $wrapper.data('customObjectInternalName');
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.propertyInternalName = propertyInternalName;

        this.render();
    }

    render() {
        this.$wrapper.html(EditProperty.markup());

        debugger;
        new PropertyEditForm($('.js-main-content'), this.globalEventDispatcher, this.portal, this.customObject, this.propertyInternalName);

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

export default EditProperty;