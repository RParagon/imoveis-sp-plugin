<?php
/**
 * Template customizado para exibir o CPT "Imóvel"
 * (V3: estilizado com ícones, layout etc.)
 */
get_header(); // Cabeçalho do tema

if ( have_posts() ) :
    while ( have_posts() ) : the_post();

        // Buscar metadados
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
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('single-imovel-v3'); ?>>
            <div class="imovel-header-v3">
                <h1 class="imovel-title-v3"><?php the_title(); ?></h1>
                <span class="imovel-tipo-v3"><i class="fa fa-building"></i> <?php echo esc_html( $tipo ); ?></span>
            </div>

            <div class="imovel-content-v3">
                <div class="imovel-gallery-v3">
                    <?php if ( has_post_thumbnail() ) {
                        the_post_thumbnail( 'large' );
                    } ?>
                </div>

                <div class="imovel-info-v3">
                    <h2><i class="fa fa-map-marker-alt"></i> <?php _e( 'Localização', 'imoveis-sp' ); ?></h2>
                    <p><strong><?php _e( 'Endereço:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $endereco ); ?></p>
                    <p><strong><?php _e( 'Bairro:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $bairro ); ?></p>
                    <p><strong><?php _e( 'Cidade:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $cidade ); ?></p>

                    <?php if ( !empty($latitude) && !empty($longitude) ) : ?>
                        <div class="imovel-map-v3">
                            <!-- Exemplo de iframe do Google Maps, ou pode usar a JS API para renderizar um mapa -->
                            <iframe
                              width="100%"
                              height="300"
                              style="border:0"
                              loading="lazy"
                              allowfullscreen
                              referrerpolicy="no-referrer-when-downgrade"
                              src="https://www.google.com/maps/embed/v1/view?key=<?php echo esc_attr( get_option('imoveis_sp_google_api_key','') ); ?>&center=<?php echo esc_attr($latitude); ?>,<?php echo esc_attr($longitude); ?>&zoom=15">
                            </iframe>
                        </div>
                    <?php endif; ?>

                    <h2><i class="fa fa-home"></i> <?php _e( 'Detalhes', 'imoveis-sp' ); ?></h2>
                    <ul class="imovel-list-v3">
                        <li><i class="fa fa-ruler-combined"></i> <?php echo esc_html( $area ); ?> m²</li>
                        <li><i class="fa fa-bed"></i> <?php echo esc_html( $quartos ); ?> <?php _e( 'Quartos', 'imoveis-sp' ); ?></li>
                        <li><i class="fa fa-bath"></i> <?php echo esc_html( $banheiros ); ?> <?php _e( 'Banheiros', 'imoveis-sp' ); ?></li>
                        <li><i class="fa fa-user"></i> <?php echo esc_html( $suites ); ?> <?php _e( 'Suítes', 'imoveis-sp' ); ?></li>
                        <li><i class="fa fa-car"></i> <?php echo esc_html( $vagas ); ?> <?php _e( 'Vagas', 'imoveis-sp' ); ?></li>
                    </ul>

                    <p class="imovel-preco-v3"><strong><?php _e( 'Preço:', 'imoveis-sp' ); ?></strong> R$ <?php echo esc_html( $preco ); ?></p>

                    <h2><i class="fa fa-info-circle"></i> <?php _e( 'Descrição', 'imoveis-sp' ); ?></h2>
                    <div class="imovel-descricao-v3">
                        <?php echo wpautop( esc_html( $descricao ) ); ?>
                    </div>
                </div>
            </div>
        </article>
        <?php
    endwhile;
endif;

get_footer(); // Rodapé do tema
