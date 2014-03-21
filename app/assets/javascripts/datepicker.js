(function (window) {
  'use strict';
  var document = window.document;
  
  
  $(document).ready(function () {
    $('input[type="datetime"]').datepicker({
      dateFormat: 'yy-mm-dd'
    });
  });
}(window));