/**
 * Jalaversity — Admin Panel JavaScript
 *
 * Loaded only on the theme's own settings page (hook contains 'jalaversity').
 * Uses jQuery — acceptable in wp-admin context.
 *
 * @package Jalaversity
 */

/* global jalaversityAdmin, wp */

(function ($) {
  'use strict';

  $(document).ready(function () {
    $('.jalaversity-color-field').wpColorPicker();

    $('.jalaversity-image-field').each(function () {
      var $field = $(this);
      var $input = $field.find('.jalaversity-image-field__input');
      var $preview = $field.find('.jalaversity-image-field__preview');
      var $previewImg = $preview.find('img');
      var $selectBtn = $field.find('.jalaversity-image-field__select');
      var $removeBtn = $field.find('.jalaversity-image-field__remove');
      var frame;

      $selectBtn.on('click', function (e) {
        e.preventDefault();

        if (frame) {
          frame.open();
          return;
        }

        frame = wp.media({
          title: jalaversityAdmin.mediaTitle,
          button: { text: jalaversityAdmin.mediaButton },
          multiple: false,
        });

        frame.on('select', function () {
          var attachment = frame.state().get('selection').first().toJSON();
          var url = (attachment.sizes && attachment.sizes.medium) ? attachment.sizes.medium.url : attachment.url;

          $input.val(attachment.id);
          $previewImg.attr('src', url);
          $preview.show();
          $removeBtn.show();
        });

        frame.open();
      });

      $removeBtn.on('click', function (e) {
        e.preventDefault();
        $input.val('');
        $preview.hide();
        $removeBtn.hide();
      });
    });
  });

}(jQuery));
