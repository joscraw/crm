'use strict';

import $ from 'jquery';
import Settings from '../Settings';
import CustomObjectFormModal from './CustomObjectFormModal';
import CustomObjectList from './CustomObjectList';
import OpenCreateCustomObjectModalButton from './OpenCreateCustomObjectModalButton';
import CustomObjectSettingsTopBar from './CustomObjectSettingsTopBar';


class CustomObjectSettings {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     */
    constructor($wrapper, globalEventDispatcher) {
        this.init($wrapper, globalEventDispatcher);
    }

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     */
    init($wrapper, globalEventDispatcher) {
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;

        this.render();
    }

    render() {
        const $topBar = this.$wrapper.find('.js-top-bar');
        const $mainContent = this.$wrapper.find('.js-main-content');

  /*      const container = document.createElement("div");
        document.body.appendChild(container);

        const $div = $("<div>", {"class": "js-top-bar"});
        $("#box").append($div);
*/
        new CustomObjectSettingsTopBar($topBar, this.globalEventDispatcher);
        new CustomObjectList($mainContent, this.globalEventDispatcher);

    }

/*    static markup() {
        return `
        <div class="l-grid">
            <div class="l-grid__top-bar"></div>
            <div class="l-grid__main-content"></div>
        </div>
    `;
    }*/
}

export default CustomObjectSettings;