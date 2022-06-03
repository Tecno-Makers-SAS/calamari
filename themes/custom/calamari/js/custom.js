jQuery(document).ready(function() {
	var windowW = jQuery(window).width();
    var URLactual = window.location.pathname;

    /*show advanced search*/

    var params = new window.URLSearchParams(window.location.search);
     if(!params.get('f[0]')){
         console.log("paramssss",params.get('tema_nivel1'));
       jQuery('#block-formularioexpuestodocumentospage-1').css('display','block');
    }
     if(params.get('tema_nivel1')){
         jQuery('#block-formularioexpuestodocumentospage-1').css('display','none');
     }

    jQuery('.path-documentos #block-headerdocumentos .btn-filters .btn_green').click(function(){
       jQuery('#block-formularioexpuestodocumentospage-1').slideToggle();
    });

    /*add class to pages*/
    if(URLactual == "/contacto"){
        jQuery(".main_container .container").addClass("container_medium");
    }
    else if (URLactual == "/proyecto"){
        jQuery("body").addClass("proyect_page");
    }
    /*open menu mobile*/
    jQuery('.btn_toggle_mobile').click(function(){
    	jQuery(this).toggleClass('change_icon');
    	jQuery('header #block-calamari-main-menu').toggleClass('open_menu');
    });

    /*show/hide filters mobile*/
    jQuery('.filter_section').click(function(){
        jQuery(this).find('.show_filters').toggleClass('collapsed');
        jQuery('.path-documentos .sidebar_left_content .region-sidebar-first').slideToggle();
    });
    /*accordions docss*/
    jQuery('.page-node-type-documento .card .card-header').click(function(){
        jQuery(this).toggleClass('active');
        jQuery(this).parent().find('.card-body').slideToggle();
    });
    jQuery('.page-node-type-documento .first-card .card-header').trigger("click");

    /*acordeon checks filters*/
    var $all_checks = jQuery('.region-sidebar-first .block-facets .block-title');
    jQuery('.region-sidebar-first .block-facets .block-title').click(function () {
        console.log('clic');
        $all_checks.not(this).removeClass("active");
        $all_checks.not(this).parent().find('.facets-widget-checkbox').slideUp();
        jQuery(this).parent().find('.facets-widget-checkbox').slideToggle();
        jQuery(this).addClass('active');
    });



    /*slick home years*/
    jQuery('.facets-widget-links ul').slick({
        slidesToShow: 11,
        slidesToScroll: 11,
        infinite:false,
        variableWidth: true,
        arrows:true,
        dots:false,
        responsive: [{
            breakpoint: 1024,
            settings: {
                slidesToShow: 5,
                slidesToScroll: 5
                }
            }, {
            breakpoint: 767,
            settings: {
                slidesToShow: 3,
                slidesToScroll: 3,
                infinite: false,
                variableWidth: true
            }
        }]
    });

    jQuery('.facets-widget-links ul').css({
        'opacity': 1
    });

    /*slick slider proyectos*/
    jQuery('.view-que-es-calamari .view-content').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        autoplay: true,
        speed: 800,
        infinite:true,
        arrows:false,
        dots:true
    });

    jQuery('.view-que-es-calamari .view-content').css({
        'opacity': 1
    });

    /*slicks mobile*/
    /*carrousel the project allies*/
    if(windowW < 768){
	  	var slickAllies = jQuery('.field--name-field-gnal-parrafo .paragraph--type--title-images .field--name-field-gnal-images').slick({
	        slidesToShow: 2,
            slidesToScroll: 2,
            infinite:true,
            variableWidth: true,
            arrows:false,
            dots:false
	    });

	    jQuery('.field--name-field-gnal-parrafo .paragraph--type--title-images .field--name-field-gnal-images').css({
	          'opacity':1
	    });

	    jQuery('.view-calamari-blocks .view-content').slick({
	        slidesToShow: 2,
            slidesToScroll: 2,
            infinite:true,
            variableWidth: true,
            arrows:false,
            dots:false
	    });


	    jQuery('.view-calamari-blocks .view-content').css({
	          'opacity':1
	    });
    }

    jQuery('.view-id-documentos .views-field-title a').attr('target', '_blank');

  /*/Validacion botón de busqueda
  let cont = 0;
  jQuery( ".path-documentos #views-exposed-form-documentos-page-1 .form-select" ).each(function( index ) {
    const valor = jQuery(this).val();
    if( valor != '' ) {
      if( valor != 'All'  && valor != 'field_doc_fexpedicion' ) {
        cont++;
      }
    }
  });
  if( cont > 0 ){
    jQuery( ".path-documentos #views-exposed-form-documentos-page-1" ).show();
  } else {
    jQuery( ".path-documentos #views-exposed-form-documentos-page-1" ).hide();
  }*/

});
