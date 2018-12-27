'use strict';

import EventDispatcher from './EventDispatcher';

window.globalEventDispatcher = new EventDispatcher();

require('bootstrap');
require('../css/main.scss');