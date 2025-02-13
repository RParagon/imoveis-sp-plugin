<?php
/**
 * Template custom para single de "Imóvel"
 */
get_header();

if ( have_posts() ) :
    while ( have_posts() ) : the_post();

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
        $lat       = get_post_meta( get_the_ID(), '_latitude_imovel', true );
        $lng       = get_post_meta( get_the_ID(), '_longitude_imovel', true );
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('single-imovel'); ?>>
            <header class="single-imovel-header">
                <h1 class="single-imovel-title"><?php the_title(); ?></h1>
                <?php if ( $tipo ) : ?>
                    <span class="single-imovel-tipo"><i class="fa fa-home"></i> <?php echo esc_html($tipo); ?></span>
                <?php endif; ?>
            </header>

            <div class="single-imovel-content">
                <div class="single-imovel-galeria">
                    <?php if ( has_post_thumbnail() ) {
                        the_post_thumbnail( 'large' );
                    } ?>
                </div>

                <div class="single-imovel-detalhes">
                    <h2><i class="fa fa-map-marker-alt"></i> Localização</h2>
                    <p><strong>Endereço:</strong> <?php echo esc_html($endereco); ?></p>
                    <p><strong>Bairro:</strong> <?php echo esc_html($bairro); ?></p>
                    <p><strong>Cidade:</strong> <?php echo esc_html($cidade); ?></p>

                    <?php if ( $lat && $lng ): ?>
                        <div class="single-imovel-mapa">
                            <iframe
                              width="100%"
                              height="300"
                              style="border:0"
                              loading="lazy"
                              allowfullscreen
                              src="https://www.google.com/maps/embed/v1/view?key=<?php echo esc_attr( get_option('imoveis_sp_google_api_key','') ); ?>&center=<?php echo esc_attr($lat); ?>,<?php echo esc_attr($lng); ?>&zoom=15">
                            </iframe>
                        </div>
                    <?php endif; ?>

                    <h2><i class="fa fa-info-circle"></i> Informações do Imóvel</h2>
                    <ul class="single-imovel-list">
                        <?php if($area): ?>
                        <li><i class="fa fa-ruler-combined"></i> <?php echo esc_html($area); ?> m²</li>
                        <?php endif; ?>
                        <li><i class="fa fa-bed"></i> <?php echo (int)$quartos; ?> Dorm.</li>
                        <li><i class="fa fa-bath"></i> <?php echo (int)$banheiros; ?> Banheiros</li>
                        <li><i class="fa fa-user"></i> <?php echo (int)$suites; ?> Suíte(s)</li>
                        <li><i class="fa fa-car"></i> <?php echo (int)$vagas; ?> Vaga(s)</li>
                    </ul>

                    <p class="single-imovel-preco">
                        <strong><i class="fa fa-dollar-sign"></i> Preço:</strong> R$ <?php echo esc_html($preco); ?>
                    </p>

                    <h2><i class="fa fa-file-alt"></i> Descrição</h2>
                    <div class="single-imovel-descricao">
                        <?php echo wpautop( esc_html($descricao) ); ?>
                    </div>
                </div>
            </div>
        </article>
        <?php
    endwhile;
endif;

get_footer();
