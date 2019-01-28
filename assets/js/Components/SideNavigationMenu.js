'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';

class SideNavigationMenu {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param internalIdentifier
     */
    constructor($wrapper, globalEventDispatcher, internalIdentifier) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.internalIdentifier = internalIdentifier;

        this.globalEventDispatcher.subscribe(
            Settings.Events.CUSTOM_OBJECT_CREATED,
            this.reloadSideNavigationMenu.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.CUSTOM_OBJECT_EDITED,
            this.reloadSideNavigationMenu.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.CUSTOM_OBJECT_DELETED,
            this.reloadSideNavigationMenu.bind(this)
        );

        this.loadSideNavigationMenu().then(() => {});
    }

    reloadSideNavigationMenu() {
        debugger;
        this.loadSideNavigationMenu();
    }

    loadSideNavigationMenu() {
        return new Promise((resolve, reject) => {
            debugger;
            $.ajax({
                url: Routing.generate('get_side_navigation_menu', {internalIdentifier: this.internalIdentifier}),
            }).then(data => {
                debugger;
                this.$wrapper.html(data.markup);
                resolve(data);
            }).catch(errorData => {
                debugger;
                reject(errorData);
            });
        });
    }
}

export default SideNavigationMenu;