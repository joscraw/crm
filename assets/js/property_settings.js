import $ from 'jquery';
import PropertySettings from './Components/Page/PropertySettings';

$(document).ready(function() {
    new PropertySettings($('#app'), window.globalEventDispatcher);
});