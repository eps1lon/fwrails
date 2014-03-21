(function (window, $, undef) {
  "use strict";
  var document = window.document;
  $(document).ready(function () {
    $('input[type=color]').each(function (i, elem) {
      $(elem).ColorPicker({
        flat: false,
        onChange: function (hsb, hex, rgb) {
          $(elem).val('#' + hex);
        },
        onBeforeShow: function () {
          $(this).ColorPickerSetColor(this.value);
        }
      });
    });
  });  
}(this, jQuery));