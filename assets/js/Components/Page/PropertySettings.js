'use strict';

import $ from 'jquery';
import Settings from '../../Settings';
import PropertySettingsTopBar from './../PropertySettingsTopBar';
import PropertyList from "./../PropertyList";
import PropertyGroupFormModal from "./../PropertyGroupFormModal";
import CustomObjectSettingsTopBar from "../CustomObjectSettingsTopBar";
import CustomObjectList from "../CustomObjectList";


class PropertySettings {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param internalIdentifier
     * @param internalName
     */
    constructor($wrapper, globalEventDispatcher, internalIdentifier, internalName) {
        debugger;
        this.customObject = $wrapper.data('customObject');
        this.portal = $wrapper.data('portal');
        this.customObjectInternalName = $wrapper.data('customObjectInternalName');
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.internalIdentifier = internalIdentifier;
        this.internalName = internalName;

        this.render();
    }

    render() {
        this.$wrapper.html(PropertySettings.markup());
        new PropertySettingsTopBar(this.$wrapper.find('.js-top-bar'), this.globalEventDispatcher, this.portal, this.customObject, this.internalIdentifier, this.internalName);
        new PropertyList(this.$wrapper.find('.js-main-content'), this.globalEventDispatcher, this.portal, this.customObject, this.customObjectInternalName, this.internalIdentifier, this.internalName);

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

export default PropertySettings;