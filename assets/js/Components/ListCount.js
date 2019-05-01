'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';
import ContextHelper from "../ContextHelper";

class ListCount {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portal
     */
    constructor($wrapper, globalEventDispatcher, portal) {

        debugger;

        this.$wrapper = $wrapper;
        this.portal = portal;

        /**
         * @type {EventDispatcher}
         */
        this.globalEventDispatcher = globalEventDispatcher;

        this.globalEventDispatcher.singleSubscribe(
            Settings.Events.LIST_DELETED,
            ContextHelper.bind(this.loadListCount, this)
        );

        this.loadListCount();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            newFolderForm: '.js-new-folder-form',
        }
    }

    loadListCount() {
        debugger;
        $.ajax({
            url: Routing.generate('list_count', {internalIdentifier: this.portal}),
        }).then(data => {

            let html = `(${data.data})`;
            this.$wrapper.html(html);
        })
    }

}

export default ListCount;