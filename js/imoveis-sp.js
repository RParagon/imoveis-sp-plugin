jQuery(document).ready(function($){
    // Exemplo de fadeIn no carregamento
    $('.imovel-item-v2').hide().each(function(index){
        $(this).delay(200*index).fadeIn(500);
    });

    // Se quiser usar algo como AOS, você pode iniciar aqui
    // AOS.init();
    
    // Outras animações/transições podem ser colocadas aqui
});
