jQuery(document).ready(function($){

    // Mostra/oculta filtros avançados
    $('#btn-filtros-avancados').on('click', function(e){
      e.preventDefault();
      var $filtros = $('#catalogo-filtros-avancados');
      if( $filtros.is(':visible') ){
        $filtros.slideUp();
        $(this).html('<i class="fa fa-plus-circle"></i> +Filtros');
      } else {
        $filtros.slideDown();
        $(this).html('<i class="fa fa-minus-circle"></i> -Filtros');
      }
    });
  
    // Autocomplete (Google Places)
    if ( typeof google !== 'undefined' && typeof google.maps !== 'undefined' ) {
      var input = document.querySelector('.google-places-autocomplete');
      if ( input ) {
        var autocomplete = new google.maps.places.Autocomplete(input, { types:['geocode'] });
  
        autocomplete.addListener('place_changed', function(){
          var place = autocomplete.getPlace();
          if(place.geometry && place.geometry.location){
            var lat = place.geometry.location.lat();
            var lng = place.geometry.location.lng();
            $('#latitude_imovel').val(lat);
            $('#longitude_imovel').val(lng);
          }
          // Se quiser extrair bairro, cidade etc. via place.address_components
          // e preencher automaticamente, é possível.
        });
      }
    }
  
  });
  