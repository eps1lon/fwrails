(function (window) {
  'use strict';
  var document = window.document;
  
  
  $(document).ready(function () {
    $('input[type="datetime"], input[type="date').datepicker({
      dateFormat: 'yy-mm-dd'
    });
  });
}(window));