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

  // Bind image picker to a single element (called on init + new repeater rows)
  function initImageField($field) {
    if ($field.data('jalaversity-image-init')) return;
    $field.data('jalaversity-image-init', true);

    var $input   = $field.find('.jalaversity-image-field__input');
    var $preview = $field.find('.jalaversity-image-field__preview');
    var $img     = $preview.find('img');
    var $select  = $field.find('.jalaversity-image-field__select');
    var $remove  = $field.find('.jalaversity-image-field__remove');
    var frame;

    $select.on('click', function (e) {
      e.preventDefault();
      if (frame) { frame.open(); return; }
      frame = wp.media({
        title: jalaversityAdmin.mediaTitle,
        button: { text: jalaversityAdmin.mediaButton },
        multiple: false,
      });
      frame.on('select', function () {
        var att = frame.state().get('selection').first().toJSON();
        var url = (att.sizes && att.sizes.thumbnail) ? att.sizes.thumbnail.url : att.url;
        $input.val(att.id);
        $img.attr('src', url);
        $preview.show();
        $remove.show();
      });
      frame.open();
    });

    $remove.on('click', function (e) {
      e.preventDefault();
      $input.val('');
      $preview.hide();
      $remove.hide();
    });
  }

  $(document).ready(function () {
    $('.jalaversity-color-field').wpColorPicker();

    // Tim Layanan repeater
    var $repeater = $('#jalaversity-tim-layanan-repeater');

    function makeRowHtml(idx) {
      var base = 'jalaversity_options[tim_layanan_contacts][' + idx + ']';
      return '<div class="jalaversity-tim-row">' +
        '<div class="jalaversity-tim-row__handle" title="Drag to reorder">⠣</div>' +
        '<div class="jalaversity-tim-row__fields">' +
          '<label>Nama <input type="text" name="' + base + '[nama]" value="" class="regular-text" required></label>' +
          '<label>Jabatan <input type="text" name="' + base + '[jabatan]" value="" class="regular-text"></label>' +
          '<label>Nomor WhatsApp <input type="text" name="' + base + '[whatsapp]" value="" class="regular-text" placeholder="6281234567890"><span class="description">Angka saja, tanpa + atau spasi.</span></label>' +
        '</div>' +
        '<div class="jalaversity-tim-row__photo jalaversity-image-field">' +
          '<input type="hidden" name="' + base + '[photo]" value="" class="jalaversity-image-field__input">' +
          '<div class="jalaversity-image-field__preview" style="display:none;"><img src="" alt="" width="60" height="60" style="border-radius:50%;object-fit:cover;"></div>' +
          '<button type="button" class="button jalaversity-image-field__select">Foto</button>' +
          '<button type="button" class="button jalaversity-image-field__remove" style="display:none;">Hapus</button>' +
        '</div>' +
        '<button type="button" class="button jalaversity-tim-row__remove" style="align-self:start;">✕ Hapus</button>' +
      '</div>';
    }

    function reindex() {
      $repeater.find('.jalaversity-tim-row').each(function (i) {
        $(this).find('input, textarea').each(function () {
          var name = $(this).attr('name') || '';
          $(this).attr('name', name.replace(/\[tim_layanan_contacts\]\[\d+\]/, '[tim_layanan_contacts][' + i + ']'));
        });
      });
    }

    $('#jalaversity-tim-add-row').on('click', function () {
      var idx  = $repeater.find('.jalaversity-tim-row').length;
      var $row = $(makeRowHtml(idx));
      $repeater.append($row);
      initImageField($row.find('.jalaversity-image-field'));
    });

    $repeater.on('click', '.jalaversity-tim-row__remove', function () {
      $(this).closest('.jalaversity-tim-row').remove();
      reindex();
    });

    // Init image fields for existing rows
    $repeater.find('.jalaversity-image-field').each(function () {
      initImageField($(this));
    });

    // Image fields di luar repeater (tab lain)
    $('.jalaversity-image-field').not($repeater.find('.jalaversity-image-field')).each(function () {
      initImageField($(this));
    });
  });

}(jQuery));
