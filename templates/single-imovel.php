<?php
/**
 * Template para exibir o Imóvel (CPT) de forma customizada.
 * Se o tema não possuir single-imovel.php, este será usado.
 */
get_header();

if ( have_posts() ) :
    while ( have_posts() ) : the_post();

        // Obtém os metadados
        $endereco  = get_post_meta( get_the_ID(), '_endereco_imovel', true );
        $bairro    = get_post_meta( get_the_ID(), '_bairro_imovel', true );
        $cidade    = get_post_meta( get_the_ID(), '_cidade_imovel', true );
        $preco     = get_post_meta( get_the_ID(), '_preco_imovel', true );
        $descricao = get_post_meta( get_the_ID(), '_descricao_imovel', true );
        $area      = get_post_meta( get_the_ID(), '_area_imovel', true );
        $quartos   = get_post_meta( get_the_ID(), '_quartos_imovel', true );
        $banheiros = get_post_meta( get_the_ID(), '_banheiros_imovel', true );
        $suites    = get_post_meta( get_the_ID(), '_suites_imovel', true );
        $vagas     = get_post_meta( get_the_ID(), '_vagas_imovel', true );
        $tipo      = get_post_meta( get_the_ID(), '_tipo_imovel', true );
        $latitude  = get_post_meta( get_the_ID(), '_latitude_imovel', true );
        $longitude = get_post_meta( get_the_ID(), '_longitude_imovel', true );

        // Recupera o número do WhatsApp configurado
        $whatsapp = get_option( 'imoveis_sp_whatsapp', '' );
        // Formata o link para WhatsApp
        $whatsapp_link = !empty($whatsapp) ? 'https://wa.me/' . esc_attr( $whatsapp ) : '#';
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('single-imovel'); ?>>
            <div class="imovel-header">
                <h1 class="imovel-title"><?php the_title(); ?></h1>
                <p class="imovel-tipo"><i class="fas fa-home"></i> <?php echo esc_html( $tipo ); ?></p>
            </div>
            <div class="imovel-content">
                <div class="imovel-gallery">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <?php the_post_thumbnail( 'large' ); ?>
                    <?php endif; ?>
                </div>
                <div class="imovel-detalhes">
                    <h2><i class="fas fa-map-marker-alt"></i> <?php _e( 'Localização', 'imoveis-sp' ); ?></h2>
                    <p><?php echo esc_html( $endereco ); ?>, <?php echo esc_html( $bairro ); ?>, <?php echo esc_html( $cidade ); ?></p>
                    <?php if ( ! empty( $latitude ) && ! empty( $longitude ) ) : ?>
                        <div class="imovel-map">
                            <iframe
                              width="100%"
                              height="300"
                              frameborder="0" style="border:0"
                              src="https://www.google.com/maps/embed/v1/view?key=<?php echo esc_attr( get_option('imoveis_sp_google_api_key','') ); ?>&center=<?php echo esc_attr( $latitude ); ?>,<?php echo esc_attr( $longitude ); ?>&zoom=15" allowfullscreen>
                            </iframe>
                        </div>
                    <?php endif; ?>

                    <h2><i class="fas fa-info-circle"></i> <?php _e( 'Detalhes do Imóvel', 'imoveis-sp' ); ?></h2>
                    <ul class="detalhes-lista">
                        <li><i class="fas fa-ruler-combined"></i> <?php echo esc_html( $area ); ?> m²</li>
                        <li><i class="fas fa-bed"></i> <?php echo esc_html( $quartos ); ?> <?php _e( 'Quartos', 'imoveis-sp' ); ?></li>
                        <li><i class="fas fa-bath"></i> <?php echo esc_html( $banheiros ); ?> <?php _e( 'Banheiros', 'imoveis-sp' ); ?></li>
                        <li><i class="fas fa-user"></i> <?php echo esc_html( $suites ); ?> <?php _e( 'Suítes', 'imoveis-sp' ); ?></li>
                        <li><i class="fas fa-car"></i> <?php echo esc_html( $vagas ); ?> <?php _e( 'Vagas', 'imoveis-sp' ); ?></li>
                        <li><i class="fas fa-dollar-sign"></i> <?php _e( 'Preço:', 'imoveis-sp' ); ?> R$ <?php echo esc_html( $preco ); ?></li>
                    </ul>

                    <h2><i class="fas fa-align-left"></i> <?php _e( 'Descrição', 'imoveis-sp' ); ?></h2>
                    <div class="imovel-descricao">
                        <?php echo wpautop( esc_html( $descricao ) ); ?>
                    </div>

                    <?php if ( ! empty( $whatsapp ) ) : ?>
                        <a href="<?php echo esc_url( $whatsapp_link ); ?>" target="_blank" class="btn-contato">
                            <i class="fab fa-whatsapp"></i> <?php _e( 'Fale conosco via WhatsApp', 'imoveis-sp' ); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </article>
        <?php
    endwhile;
endif;

get_footer();
