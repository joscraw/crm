'use strict';

import EventDispatcher from './EventDispatcher';

window.globalEventDispatcher = new EventDispatcher();

import 'babel-polyfill';
require('bootstrap');
require('selectize');
require('font-awesome/css/font-awesome.css');
require('../css/main.scss');
let _ = require('lodash');
require('pace-js-amd-fix');
