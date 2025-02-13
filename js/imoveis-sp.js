jQuery(document).ready(function($) {
    // Inicializa o Google Places Autocomplete (se disponível)
    if ( typeof google !== 'undefined' && typeof google.maps !== 'undefined' ) {
        // Para todos os campos com a classe .google-places-autocomplete
        $('.google-places-autocomplete').each(function() {
            var input = this;
            var autocomplete = new google.maps.places.Autocomplete(input, {
                types: ['geocode']
            });
            // Se estiver no metabox, atualiza latitude/longitude automaticamente
            autocomplete.addListener('place_changed', function(){
                var place = autocomplete.getPlace();
                if ( place.geometry && place.geometry.location ) {
                    var lat = place.geometry.location.lat();
                    var lng = place.geometry.location.lng();
                    // Se existir os campos, preenche-os
                    $('#latitude_imovel').val(lat);
                    $('#longitude_imovel').val(lng);
                }
            });
        });
    }

    // Toggle de filtros avançados na página de catálogo
    $('.btn-toggle-filtros').on('click', function(){
        $('.filtros-adicionais').slideToggle();
    });
});
