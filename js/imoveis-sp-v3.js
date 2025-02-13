jQuery(document).ready(function($){

    // Se a API do Google estiver carregada, inicializa Autocomplete
    if ( typeof google !== 'undefined' && typeof google.maps !== 'undefined' ) {
      var input = document.querySelector('.google-places-autocomplete');
      if ( input ) {
        var autocomplete = new google.maps.places.Autocomplete(input, {
          types: ['geocode'] 
        });
  
        // Quando selecionar um endereço, podemos extrair alguns dados
        autocomplete.addListener('place_changed', function(){
          var place = autocomplete.getPlace();
          // Exemplo: extrair lat/lng
          if ( place.geometry && place.geometry.location ) {
            var lat = place.geometry.location.lat();
            var lng = place.geometry.location.lng();
            $('#latitude_imovel').val(lat);
            $('#longitude_imovel').val(lng);
          }
          // É possível também buscar bairro, cidade etc. via place.address_components
        });
      }
    }
  
    // Outras interações ou animações V3
    console.log("imoveis-sp-v3.js carregado!");
  });
  