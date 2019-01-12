'use strict';

import EventDispatcher from './EventDispatcher';

window.globalEventDispatcher = new EventDispatcher();

require('bootstrap');
require('selectize');
require('font-awesome/css/font-awesome.css');
require('../css/main.scss');
require('bootstrap-datepicker');
require('bootstrap-datepicker/dist/css/bootstrap-datepicker.standalone.css');
