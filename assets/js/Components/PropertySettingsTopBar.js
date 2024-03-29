'use strict';

import $ from 'jquery';
import Settings from '../Settings';
import OpenCreatePropertyGroupModalButton from './OpenCreatePropertyGroupModalButton';
import OpenCreatePropertyModalButton from './OpenCreatePropertyModalButton';
import CustomObjectNavigation from './CustomObjectNavigation';


class PropertySettingsTopBar {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;

        this.$wrapper.on(
            'keyup',
            '.js-propery-search-input',
            this.handleKeyupEvent.bind(this)
        );

        this.render();
    }

    handleKeyupEvent(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        const searchValue = $(e.target).val();
        const searchObject = {
          searchValue: searchValue
        };

        this.globalEventDispatcher.publish(Settings.Events.PROPERTY_SETTINGS_TOP_BAR_SEARCH_KEY_UP, searchObject);
    }

    render() {
        this.$wrapper.html(PropertySettingsTopBar.markup());
        new OpenCreatePropertyGroupModalButton(this.$wrapper.find('.js-top-bar-button-container'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
        new OpenCreatePropertyModalButton(this.$wrapper.find('.js-top-bar-button-container'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
        new CustomObjectNavigation(this.$wrapper.find('.js-custom-object-navigation'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
    }

    static markup() {
        return `
        <div class="row">
            <div class="col-md-6 js-top-bar-search-container">
                <div class="input-group c-search-control">
                  <input class="form-control c-search-control__input js-propery-search-input" type="search" placeholder="Search for a property">
                  <span class="c-search-control__foreground"><i class="fa fa-search"></i></span>
                </div>
            </div>
        <div class="col-md-6 text-right js-top-bar-button-container"></div>
        </div>
        <br>
        <br>
        <div class="row">
            <div class="col-md-12 js-custom-object-navigation"></div>
        </div>
    `;
    }
}

export default PropertySettingsTopBar;