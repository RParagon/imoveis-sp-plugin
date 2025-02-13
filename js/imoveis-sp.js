jQuery(document).ready(function($) {
    // Exemplo: Adiciona a classe "animated" quando o item entra na viewport
    function animateOnScroll() {
        $('.imovel-item').each(function() {
            if ($(this).offset().top < $(window).scrollTop() + $(window).height() - 100) {
                $(this).addClass('animated');
            }
        });
    }
    $(window).on('scroll', animateOnScroll);
    animateOnScroll();
});
