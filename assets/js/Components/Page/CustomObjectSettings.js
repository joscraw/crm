'use strict';

import $ from 'jquery';
import Settings from '../../Settings';
import CustomObjectFormModal from './../CustomObjectFormModal';
import CustomObjectList from './../CustomObjectList';
import OpenCreateCustomObjectModalButton from './../OpenCreateCustomObjectModalButton';
import CustomObjectSettingsTopBar from './../CustomObjectSettingsTopBar';


class CustomObjectSettings {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     */
    constructor($wrapper, globalEventDispatcher) {
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portal = $wrapper.data('portal');

        this.render();
    }

    render() {
        this.$wrapper.html(CustomObjectSettings.markup());
        new CustomObjectSettingsTopBar(this.$wrapper.find('.js-top-bar'), this.globalEventDispatcher, this.portal);
        new CustomObjectList(this.$wrapper.find('.js-main-content'), this.globalEventDispatcher, this.portal);

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

export default CustomObjectSettings;