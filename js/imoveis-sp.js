jQuery(function($) {
    /**
     * Inicializa o Google Places Autocomplete para inputs com a classe .google-places-autocomplete.
     */
    const initializeGooglePlacesAutocomplete = () => {
        if ( typeof google !== 'undefined' && google.maps && google.maps.places ) {
            $('.google-places-autocomplete').each(function() {
                const inputField = this;
                const autocomplete = new google.maps.places.Autocomplete(inputField, {
                    types: ['geocode']
                });
                
                autocomplete.addListener('place_changed', function() {
                    const place = autocomplete.getPlace();
                    if ( place.geometry && place.geometry.location ) {
                        const lat = place.geometry.location.lat();
                        const lng = place.geometry.location.lng();
                        
                        // Preenche os campos de latitude e longitude, se existirem
                        $('#latitude_imovel').val(lat);
                        $('#longitude_imovel').val(lng);
                    }
                });
            });
        } else {
            console.warn('Google Maps ou Google Places API não está carregado.');
        }
    };

    /**
     * Configura o toggle para os filtros avançados na página do catálogo.
     */
    const initializeToggleFiltros = () => {
        $('.btn-toggle-filtros').on('click', function(){
            $('.filtros-adicionais').slideToggle();
        });
    };

    // Inicializa todas as funcionalidades
    initializeGooglePlacesAutocomplete();
    initializeToggleFiltros();
});
