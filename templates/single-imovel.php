<?php
/**
 * Template customizado para o CPT Imóvel
 * Melhorias: performance, segurança, organização e semântica
 */
get_header();

if ( have_posts() ) :
    while ( have_posts() ) : the_post();
        $post_id = get_the_ID();
        $title   = get_the_title();

        // Otimização: obter todos os metadados de uma vez
        $meta = get_post_meta( $post_id );

        // Dados estruturados para schema.org
        $schema_data = [
            "@context"    => "https://schema.org",
            "@type"       => "RealEstateListing",
            "name"        => $title,
            "description" => wp_strip_all_tags( $meta['_descricao_imovel'][0] ?? '' ),
            "address"     => [
                "@type"           => "PostalAddress",
                "streetAddress"   => $meta['_endereco_imovel'][0] ?? '',
                "addressLocality" => $meta['_cidade_imovel'][0] ?? '',
                "addressRegion"   => "SP"
            ],
            "geo"         => [
                "@type"     => "GeoCoordinates",
                "latitude"  => $meta['_latitude_imovel'][0] ?? '',
                "longitude" => $meta['_longitude_imovel'][0] ?? ''
            ],
            "offers"      => [
                "@type"         => "Offer",
                "price"         => $meta['_preco_imovel'][0] ?? '',
                "priceCurrency" => "BRL"
            ]
        ];

        // Dados sanitizados para exibição
        $property = [
            'tipo'      => esc_html( $meta['_tipo_imovel'][0] ?? __( 'Não especificado', 'imoveis-sp' ) ),
            'endereco'  => esc_html( implode( ', ', array_filter( [
                $meta['_endereco_imovel'][0] ?? '',
                $meta['_bairro_imovel'][0] ?? '',
                $meta['_cidade_imovel'][0] ?? ''
            ] ) ) ),
            'preco'     => number_format( (float)( $meta['_preco_imovel'][0] ?? 0 ), 2, ',', '.' ),
            'area'      => (int)( $meta['_area_imovel'][0] ?? 0 ),
            'quartos'   => (int)( $meta['_quartos_imovel'][0] ?? 0 ),
            'banheiros' => (int)( $meta['_banheiros_imovel'][0] ?? 0 ),
            'suites'    => (int)( $meta['_suites_imovel'][0] ?? 0 ),
            'vagas'     => (int)( $meta['_vagas_imovel'][0] ?? 0 ),
            'descricao' => wp_kses_post( wpautop( $meta['_descricao_imovel'][0] ?? '' ) ),
            'coords'    => [
                'lat' => (float)( $meta['_latitude_imovel'][0] ?? 0 ),
                'lng' => (float)( $meta['_longitude_imovel'][0] ?? 0 )
            ]
        ];

        // Link para contato via WhatsApp
        $whatsapp_number = get_option( 'imoveis_sp_whatsapp', '' );
        $whatsapp_link   = $whatsapp_number ? 'https://wa.me/' . preg_replace( '/[^0-9]/', '', $whatsapp_number ) : false;

        // Configuração do Google Maps
        $gmap_api_key = esc_attr( get_option( 'imoveis_sp_google_api_key', '' ) );
        $show_map     = $gmap_api_key && $property['coords']['lat'] && $property['coords']['lng'];
        ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class( 'imovel-detalhado' ); ?> itemscope itemtype="https://schema.org/RealEstateListing">
            <meta itemprop="description" content="<?= esc_attr( $schema_data['description'] ); ?>">

            <header class="imovel-cabecalho">
                <h1 class="imovel-titulo" itemprop="name"><?= esc_html( $title ); ?></h1>
                <div class="imovel-meta">
                    <span class="tipo-propriedade" itemprop="category">
                        <i class="fas fa-home" aria-hidden="true"></i>
                        <?= $property['tipo']; ?>
                    </span>
                    <span class="preco-propriedade" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                        <meta itemprop="price" content="<?= esc_attr( $meta['_preco_imovel'][0] ?? '' ); ?>">
                        <meta itemprop="priceCurrency" content="BRL">
                        R$ <?= $property['preco']; ?>
                    </span>
                </div>
            </header>

            <section class="imovel-conteudo">
                <div class="imovel-galeria">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <figure itemprop="image" itemscope itemtype="https://schema.org/ImageObject">
                            <?php 
                                the_post_thumbnail( 'large', [
                                    'itemprop' => 'contentUrl',
                                    'alt'      => esc_attr( $title ),
                                    'loading'  => 'lazy'
                                ] ); 
                            ?>
                        </figure>
                    <?php endif; ?>
                </div>

                <div class="imovel-informacoes">
                    <section class="localizacao" aria-label="<?= esc_attr__( 'Localização do imóvel', 'imoveis-sp' ); ?>">
                        <h2><i class="fas fa-map-marked-alt"></i> <?= esc_html__( 'Localização', 'imoveis-sp' ); ?></h2>
                        <p itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">
                            <span itemprop="streetAddress"><?= esc_html( $meta['_endereco_imovel'][0] ?? '' ); ?></span>,
                            <span itemprop="addressLocality"><?= esc_html( $meta['_bairro_imovel'][0] ?? '' ); ?></span>, 
                            <span itemprop="addressRegion"><?= esc_html( $meta['_cidade_imovel'][0] ?? '' ); ?></span>
                        </p>

                        <?php if ( $show_map ) : ?>
                        <div class="mapa-container" role="region" aria-label="<?= esc_attr__( 'Mapa da localização', 'imoveis-sp' ); ?>">
                            <iframe 
                                loading="lazy"
                                width="100%"
                                height="450"
                                frameborder="0"
                                style="border:0"
                                src="https://www.google.com/maps/embed/v1/view?key=<?= esc_attr( $gmap_api_key ); ?>&center=<?= $property['coords']['lat']; ?>,<?= $property['coords']['lng']; ?>&zoom=16&maptype=roadmap"
                                allowfullscreen>
                            </iframe>
                        </div>
                        <?php endif; ?>
                    </section>

                    <section class="caracteristicas" aria-label="<?= esc_attr__( 'Características do imóvel', 'imoveis-sp' ); ?>">
                        <h2><i class="fas fa-building"></i> <?= esc_html__( 'Detalhes', 'imoveis-sp' ); ?></h2>
                        <ul class="detalhes-lista">
                            <?php 
                            $detalhes = [
                                ['icon' => 'ruler-combined', 'label' => __( 'Área', 'imoveis-sp' ), 'value' => $property['area'] ? $property['area'] . ' m²' : '' ],
                                ['icon' => 'bed',            'label' => __( 'Quartos', 'imoveis-sp' ), 'value' => $property['quartos'] ],
                                ['icon' => 'bath',           'label' => __( 'Banheiros', 'imoveis-sp' ), 'value' => $property['banheiros'] ],
                                ['icon' => 'star',           'label' => __( 'Suítes', 'imoveis-sp' ), 'value' => $property['suites'] ],
                                ['icon' => 'car',            'label' => __( 'Vagas', 'imoveis-sp' ), 'value' => $property['vagas'] ]
                            ];
                            foreach ( $detalhes as $item ) :
                                if ( ! empty( $item['value'] ) ) :
                            ?>
                                <li>
                                    <i class="fas fa-<?= esc_attr( $item['icon'] ); ?>" aria-hidden="true"></i>
                                    <span><?= esc_html( $item['label'] ); ?>: </span>
                                    <strong><?= esc_html( $item['value'] ); ?></strong>
                                </li>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </ul>
                    </section>

                    <?php if ( $property['descricao'] ) : ?>
                    <section class="descricao" itemprop="description">
                        <h2><i class="fas fa-align-left"></i> <?= esc_html__( 'Descrição', 'imoveis-sp' ); ?></h2>
                        <?= $property['descricao']; ?>
                    </section>
                    <?php endif; ?>

                    <?php if ( $whatsapp_link ) : ?>
                    <div class="cta-contato">
                        <a href="<?= esc_url( $whatsapp_link ); ?>" 
                           class="botao-whatsapp"
                           target="_blank"
                           rel="noopener noreferrer">
                            <i class="fab fa-whatsapp"></i>
                            <?= esc_html__( 'Contato via WhatsApp', 'imoveis-sp' ); ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </section>
        </article>

        <script type="application/ld+json">
            <?= json_encode( $schema_data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ); ?>
        </script>

    <?php endwhile;
endif;

get_footer();
