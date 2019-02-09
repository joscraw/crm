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
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;

        this.render();
    }

    render() {
        this.$wrapper.html(PropertySettings.markup());
        new PropertySettingsTopBar(this.$wrapper.find('.js-top-bar'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
        new PropertyList(this.$wrapper.find('.js-main-content'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);

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