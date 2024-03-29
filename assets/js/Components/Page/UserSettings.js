'use strict';

import $ from 'jquery';
import Settings from '../../Settings';
import PropertySettingsTopBar from './../PropertySettingsTopBar';
import PropertyList from "./../PropertyList";
import PropertyGroupFormModal from "./../PropertyGroupFormModal";
import CustomObjectSettingsTopBar from "../CustomObjectSettingsTopBar";
import CustomObjectList from "../CustomObjectList";
import UserSettingsTopBar from "../UserSettingsTopBar";
import UserList from "../UserList";
import FilterWidget from "../FilterWidget";
import UserFilterWidget from "../UserFilterWidget";


class UserSettings {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;

        this.render();
    }

    render() {
        this.$wrapper.html(UserSettings.markup());
        new UserSettingsTopBar(this.$wrapper.find('.js-top-bar'), this.globalEventDispatcher, this.portalInternalIdentifier);
        new UserList(this.$wrapper.find('.js-user-list'), this.globalEventDispatcher, this.portalInternalIdentifier);
        new UserFilterWidget(this.$wrapper.find('.js-user-filter-widget'), this.globalEventDispatcher, this.portalInternalIdentifier);

    }

    static markup() {

        return `
      <div class="js-user-settings-page">
        <div class="l-grid">
            <div class="l-grid__top-bar js-top-bar"></div>
            <div class="l-grid__sub-bar js-sub-bar"></div>
            <div class="l-grid__main-content js-main-content">
                <div class="row">
                    <div class="col-md-3 js-user-filter-widget"></div>
                    <div class="col-md-9 js-user-list" style="min-height: 700px"></div>
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

export default UserSettings;