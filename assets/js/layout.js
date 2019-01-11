'use strict';

import EventDispatcher from './EventDispatcher';

window.globalEventDispatcher = new EventDispatcher();

require('bootstrap');
require('selectize');
require('font-awesome/css/font-awesome.css');
/*require('bootstrap/dist/css/bootstrap.css');*/
require('../css/main.scss');

require('bootstrap-datepicker');
/*require('bootstrap-datepicker/dist/css/bootstrap-datepicker.css');*/
require('bootstrap-datepicker/dist/css/bootstrap-datepicker.standalone.css');

/*require('selectize/dist/css/selectize.css');*/

/*
.swal2-popup #swal2-content {
    text-align: left;
    }

 */