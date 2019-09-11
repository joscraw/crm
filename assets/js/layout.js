'use strict';

import EventDispatcher from './EventDispatcher';

window.globalEventDispatcher = new EventDispatcher();

import 'babel-polyfill';
require('bootstrap');
require('selectize');

window.toastr = require('toastr');
require('toastr/build/toastr.min.css');

require('font-awesome/css/font-awesome.css');
require('../css/main.scss');
let _ = require('lodash');
require('pace-js-amd-fix');




require('ckeditor');

