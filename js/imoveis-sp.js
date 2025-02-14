jQuery(function($) {
    "use strict";

    /**
     * Inicializa o Google Places Autocomplete para inputs com a classe
     * .google-places-autocomplete, preenchendo automaticamente os campos
     * de latitude e longitude (caso existam) quando o usuário selecionar
     * um endereço sugerido.
     */
    const initializeGooglePlacesAutocomplete = () => {
        // Verifica se a API do Google está realmente carregada
        if (typeof google !== 'undefined' && google.maps && google.maps.places) {
            $('.google-places-autocomplete').each(function() {
                const inputField = this;
                
                // 'types: ["address"]' ou '["geocode"]' são opções comuns
                const autocomplete = new google.maps.places.Autocomplete(inputField, {
                    types: ['address']
                });

                // Escuta o evento "place_changed" para obter detalhes do lugar
                autocomplete.addListener('place_changed', function() {
                    const place = autocomplete.getPlace();

                    // Se o place tiver geometry e location, obtemos lat e lng
                    if (place && place.geometry && place.geometry.location) {
                        const lat = place.geometry.location.lat();
                        const lng = place.geometry.location.lng();

                        // Preenche os campos de latitude e longitude, se existirem
                        if ($('#latitude_imovel').length) {
                            $('#latitude_imovel').val(lat);
                        }
                        if ($('#longitude_imovel').length) {
                            $('#longitude_imovel').val(lng);
                        }
                    } else {
                        console.warn('Nenhuma localização válida foi retornada pelo Autocomplete.');
                    }
                });
            });
        } else {
            console.warn('Google Maps ou Google Places API não está carregada.');
        }
    };

    /**
     * Configura o toggle (exibir/esconder) para os filtros avançados
     * na página do catálogo. Exemplo: um botão que abre/fecha opções
     * adicionais de pesquisa.
     */
    const initializeToggleFiltros = () => {
        $('.btn-toggle-filtros').on('click', function() {
            // slideToggle() dá um efeito de animação ao exibir/esconder
            $('.filtros-adicionais').slideToggle({
                duration: 300,
                start: function() {
                    // Podemos disparar algo no início da animação se quisermos
                },
                complete: function() {
                    // Ou algo ao final da animação
                }
            });
        });
    };

    // Inicializa todas as funcionalidades quando o DOM estiver pronto
    $(document).ready(() => {
        initializeGooglePlacesAutocomplete();
        initializeToggleFiltros();
    });
});
