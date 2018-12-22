import $ from 'jquery';

import CustomObjectSettings from './Components/CustomObjectSettings';

$(document).ready(function() {
    var $wrapper = $('.js-custom-object-settings');
    new CustomObjectSettings($wrapper);
});