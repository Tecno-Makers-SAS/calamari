(function ($, Drupal, drupalSettings){
  'use strict';
  Drupal.behaviors.calamari_config = {
    attach: function (context, settings) {
       let images = drupalSettings.bgfront;
       if(images) {
        let cant = images.length;
        let rnd = Math.floor(Math.random() * cant);
        jQuery(".path-frontpage").css('background', 'url("'+ images[rnd]['url'] +'") no-repeat top');
        jQuery(".path-frontpage").css('background-size', 'cover');
        let fp = images[rnd]['name'];
        fp = fp.split('.');
        jQuery("#place").html( fp[0]);
      }

      $( "#views-exposed-form-documentos-page-1" ).once('form-documents').each(function () {
        var tema_geo1 = $('#views-exposed-form-documentos-page-1 [data-drupal-selector="edit-tema-geo1"]').attr('data-default-value');
        $('#views-exposed-form-documentos-page-1 [data-drupal-selector="edit-tema-geo1"]  option[value="'+tema_geo1+'"]').prop('selected', true);

        var tema_geo2 = $('#views-exposed-form-documentos-page-1 [data-drupal-selector="edit-tema-geo2"]').attr('data-default-value');
        $('#views-exposed-form-documentos-page-1 [data-drupal-selector="edit-tema-geo2"]  option[value="'+tema_geo2+'"]').prop('selected', true);

        var tema_geo3 = $('#views-exposed-form-documentos-page-1 [data-drupal-selector="edit-tema-geo3"]').attr('data-default-value');
        $('#views-exposed-form-documentos-page-1 [data-drupal-selector="edit-tema-geo3"]  option[value="'+tema_geo3+'"]').prop('selected', true);
      });
    }
  }

  /************************ SELECTS TEMATICOS *******************************/
  $(document).on('change', 'select[name="tema_nivel1"]', function (){
    var tema1 = $(this).val();
    $("select[name='tema_nivel2']").find("option").remove().end();
    var empty_option = `<option value="All">- Cualquier Tema -</option>`;
    $("select[name='tema_nivel2']").append(empty_option);
    if( tema1 != 'All') {
      var url = "/dtematicos/" + tema1;
      $.get(url, function (data) {
        for (let item of data) {
          if (data.length > 0) {
            let html = `<option value="${item.tid}">${item.name}</option>`;
            $("select[name='tema_nivel2']").append(html);
            $("select[name='tema_nivel2']").removeAttr('disabled');
          }
          else {
            $("select[name='tema_nivel2']").attr('disabled', 'disabled');
          }
        }
      });
    } else {
      $("select[name='tema_nivel2']").attr('disabled', 'disabled');
    }
    $('select[name="field_ref_tematicos"]').val(tema1);
  });

  $(document).on('change', 'select[name="tema_nivel2"]', function (){
    var tema2 = $(this).val();
    $("select[name='tema_nivel3']").find("option").remove().end();
    var empty_option = `<option value="All">- Cualquier Tema -</option>`;
    $("select[name='tema_nivel3']").append(empty_option);
    if( tema2 != 'All') {
      var url = "/dtematicos/" + tema2;
      $.get(url, function (data) {
        for (let item of data) {
          if (data.length > 0) {
            let html = `<option value="${item.tid}">${item.name}</option>`;
            $("select[name='tema_nivel3']").append(html);
            $("select[name='tema_nivel3']").removeAttr('disabled');
          }
          else {
            $("select[name='tema_nivel3']").attr('disabled', 'disabled');
          }
        }
      });
      $('select[name="field_ref_tematicos"]').val(tema2);
    } else {
      $("select[name='tema_nivel3']").attr('disabled', 'disabled');
      var tini = $('select[name="tema_nivel1"]').val();
      $('select[name="field_ref_tematicos"]').val(tini);
    }

  });

  $(document).on('change', 'select[name="tema_nivel3"]', function (){
     var tema3 = $(this).val();
     if( tema3 != 'All'){
       $('select[name="field_ref_tematicos"]').val(tema3);
     } else {
       var tema2 = $('select[name="tema_nivel2"]').val();
       $('select[name="field_ref_tematicos"]').val(tema2);
     }
  });


  /************************ SELECTS GEOGRAFICOS ******************************/
  $(document).on('change', 'select[name="tema_geo1"]', function (){
    var tema1 = $(this).val();
    $("select[name='tema_geo2']").find("option").remove().end();
    var empty_option = `<option value="All">- Cualquier Geográfico -</option>`;
    $("select[name='tema_geo2']").append(empty_option);
    if( tema1 != 'All') {
      var url = "/dgeograficos/" + tema1;
      $.get(url, function (data) {
        for (let item of data) {
          if (data.length > 0) {
            let html = `<option value="${item.tid}">${item.name}</option>`;
            $("select[name='tema_geo2']").append(html);
            $("select[name='tema_geo2']").removeAttr('disabled');
          }
          else {
            $("select[name='tema_geo2']").attr('disabled', 'disabled');
          }
        }
      });
    } else {
      $("select[name='tema_geo2']").attr('disabled', 'disabled');
    }
    $('select[name="field_ref_geograficos"]').val(tema1);
  });

  $(document).on('change', 'select[name="tema_geo2"]', function (){
    var tema2 = $(this).val();
    $("select[name='tema_geo3']").find("option").remove().end();
    var empty_option = `<option value="All">- Cualquier Geográfico -</option>`;
    $("select[name='tema_geo3']").append(empty_option);
    if( tema2 != 'All') {
      var url = "/dgeograficos/" + tema2;
      $.get(url, function (data) {
        for (let item of data) {
          if (data.length > 0) {
            let html = `<option value="${item.tid}">${item.name}</option>`;
            $("select[name='tema_geo3']").append(html);
            $("select[name='tema_geo3']").removeAttr('disabled');
          }
          else {
            $("select[name='tema_geo3']").attr('disabled', 'disabled');
          }
        }
      });
      $('select[name="field_ref_geograficos"]').val(tema2);
    } else {
      $("select[name='tema_geo3']").attr('disabled', 'disabled');
      var tini = $('select[name="tema_geo1"]').val();
      $('select[name="field_ref_geograficos"]').val(tini);
    }

  });

  $(document).on('change', 'select[name="tema_geo3"]', function (){
    var tema3 = $(this).val();
    if( tema3 != 'All'){
      $('select[name="field_ref_geograficos"]').val(tema3);
    } else {
      var tema2 = $('select[name="tema_geo2"]').val();
      $('select[name="field_ref_geograficos"]').val(tema2);
    }
  });

})(jQuery, Drupal, drupalSettings);
