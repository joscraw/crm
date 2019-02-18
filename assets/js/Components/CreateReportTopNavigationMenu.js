'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';

class CreateReportTopNavigationMenu {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param internalIdentifier
     */
    constructor($wrapper, globalEventDispatcher, internalIdentifier) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.internalIdentifier = internalIdentifier;

        this.render()
    }

    render() {
        this.$wrapper.html(CreateReportTopNavigationMenu.markup(this));
    }

    static markup({internalIdentifier}) {

        return `
        <nav class="navbar navbar-expand-sm l-top-bar">
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link l-top-bar__link" href="${ Routing.generate('report_list', {internalIdentifier: internalIdentifier}) }"><i class="fa fa-angle-left" aria-hidden="true"></i> Back to all reports</a>
            </li>
          </ul>
         </nav>
    `;
    }
}

export default CreateReportTopNavigationMenu;