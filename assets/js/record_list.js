import $ from 'jquery';
import RecordList from './Components/Page/RecordList';

$(document).ready(function() {
    new RecordList($('#app'), window.globalEventDispatcher);
});