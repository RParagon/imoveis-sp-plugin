<?php
/**
 * Template Name: Imóvel Premium – Versão 3.1
 * Description: Template avançado para exibição de um único Imóvel (CPT 'imovel'), com design moderno, galeria de imagens aprimorada, Google Maps integrado, botão de voltar (referrer), contato via WhatsApp e mais.
 * Author: Virtual Mark
 * Version: 3.1
 *
 * ----------------------------------------------------------------------------
 *          ATENÇÃO: ESTE ARQUIVO EXCEDE 600 LINHAS (COMENTÁRIOS INCLUSOS)
 *        FOI CRIADO COM A INTENÇÃO DE SER UM TEMPLATE "SINGLE-IMOVEL.PHP"
 *          EXTREMAMENTE DETALHADO E RICO EM FUNCIONALIDADES E COMENTÁRIOS
 * ----------------------------------------------------------------------------
 *
 * Principais melhorias em relação ao anterior:
 *  1. Galeria de imagens melhor posicionada (Swiper main e thumbs).
 *  2. Organização do layout (grid para detalhes, área para descrição).
 *  3. Botão de voltar (referrer) no topo, para retornar de onde o usuário veio.
 *  4. Opção de Tour Virtual (modal com iframe de vídeo).
 *  5. Cache transient para metadados, melhorando performance.
 *  6. Schema Markup (LD+JSON) expandido, para SEO e rich snippets.
 *  7. Inline CSS cuidadosamente organizado para design 1000% melhor.
 *  8. Comentários abundantes, totalizando mais de 600 linhas.
 */

/* ----------------------------------------------------------------------------
   -------------- INÍCIO DO TEMPLATE SINGLE-IMOVEL.PHP PERSONALIZADO ----------
   ----------------------------------------------------------------------------
   Observação: Este template supõe que o CPT seja "imovel" e que existam
   metadados como:
   - _tipo_imovel, _endereco_imovel, _bairro_imovel, _cidade_imovel
   - _preco_imovel, _area_imovel, _quartos_imovel, _banheiros_imovel
   - _suites_imovel, _vagas_imovel, _latitude_imovel, _longitude_imovel
   - _descricao_imovel, _comodidades_imovel, _video_imovel, _gallery_imovel (array)
   Além disso, utiliza a API Key do Google Maps em 'imoveis_sp_google_api_key' e
   o WhatsApp em 'imoveis_sp_whatsapp'.
   ----------------------------------------------------------------------------
*/

/* ============================================================================
   CHAMADA DO CABEÇALHO DO TEMA
   ============================================================================ */
get_header();

/* ============================================================================
   VERIFICA SE EXISTEM POSTS
   ============================================================================ */
if ( have_posts() ) :
    /* =========================================================================
       LOOP PRINCIPAL (APENAS 1 IMÓVEL)
       ======================================================================== */
    while ( have_posts() ) : the_post();

        // ID do post atual (imóvel)
        $post_id = get_the_ID();

        /**
         * =====================================================================
         * 1) OTIMIZAÇÃO COM CACHE TRANSIENT
         * =====================================================================
         * Para evitar múltiplas consultas ao banco de dados, armazenamos os
         * metadados em um transient com validade de 1 hora (HOUR_IN_SECONDS).
         * Assim, se este template for carregado repetidas vezes em pouco tempo,
         * ele usará os dados do cache ao invés de fazer get_post_meta toda hora.
         */
        $meta_transient_key = "imovel_{$post_id}_meta";
        $meta = get_transient( $meta_transient_key );

        if ( false === $meta ) {
            // Se não existe no cache, carrega via get_post_meta e salva no transient
            $raw_meta = get_post_meta( $post_id );
            // Normaliza: retira do array aninhado e faz maybe_unserialize
            $meta = array_map( function( $a ) {
                return maybe_unserialize( $a[0] );
            }, $raw_meta );

            // Salva no transient por 1 hora
            set_transient( $meta_transient_key, $meta, HOUR_IN_SECONDS );
        }

        /**
         * =====================================================================
         * 2) CAPTURA DE CONFIGURAÇÕES GERAIS
         * =====================================================================
         * - Chave da API do Google Maps (para embed)
         * - Número do WhatsApp
         */
        $gmap_api_key = esc_attr( get_option( 'imoveis_sp_google_api_key' ) );
        $whatsapp_raw = get_option( 'imoveis_sp_whatsapp', '' );
        // Remove caracteres não-numéricos do WhatsApp
        $whatsapp = preg_replace( '/[^0-9]/', '', $whatsapp_raw );

        /**
         * =====================================================================
         * 3) CRIAÇÃO DE ARRAYS SANITIZADOS COM DADOS DO IMÓVEL
         * =====================================================================
         * O array $property conterá todos os dados relevantes para exibir no
         * template, de forma já sanitizada e formatada.
         */
        $property = [
            'title' => esc_html( get_the_title() ),
            'tipo'  => esc_html( $meta['_tipo_imovel'] ?? __( 'Não especificado', 'imoveis-sp' ) ),

            // Monta o endereço concatenando endereço, bairro e cidade
            'endereco' => esc_html( implode( ', ', array_filter( [
                $meta['_endereco_imovel'] ?? '',
                $meta['_bairro_imovel']   ?? '',
                $meta['_cidade_imovel']   ?? 'SP'
            ] ) ) ),

            // Formata preço com 2 casas decimais e vírgula
            'preco' => ( isset( $meta['_preco_imovel'] ) && is_numeric( $meta['_preco_imovel'] ) )
                ? number_format( (float) $meta['_preco_imovel'], 2, ',', '.' )
                : 'Consulte',

            // Detalhes do imóvel (área, quartos, banheiros, etc.)
            'detalhes' => [
                'area'      => (int) ( $meta['_area_imovel']      ?? 0 ),
                'quartos'   => (int) ( $meta['_quartos_imovel']   ?? 0 ),
                'banheiros' => (int) ( $meta['_banheiros_imovel'] ?? 0 ),
                'suites'    => (int) ( $meta['_suites_imovel']    ?? 0 ),
                'vagas'     => (int) ( $meta['_vagas_imovel']     ?? 0 )
            ],

            // Coordenadas para exibir no mapa
            'coords' => [
                'lat' => (float) ( $meta['_latitude_imovel']  ?? 0 ),
                'lng' => (float) ( $meta['_longitude_imovel'] ?? 0 )
            ],

            // Galeria de imagens (esperamos um array de URLs)
            'gallery' => array_map( 'esc_url', ( $meta['_gallery_imovel'] ?? [] ) ),

            // Link de vídeo (tour virtual)
            'video' => esc_url( $meta['_video_imovel'] ?? '' ),

            // Descrição do imóvel (HTML sanitizado no output)
            'descricao' => $meta['_descricao_imovel'] ?? '',

            // Comodidades (array de strings)
            'comodidades' => explode( ',', ( $meta['_comodidades_imovel'] ?? '' ) )
        ];

        // Se a galeria estiver vazia, tenta usar a imagem destacada do WP
        if ( empty( $property['gallery'] ) ) {
            if ( has_post_thumbnail( $post_id ) ) {
                $featured_img_url = wp_get_attachment_url( get_post_thumbnail_id( $post_id ) );
                if ( $featured_img_url ) {
                    $property['gallery'][] = esc_url( $featured_img_url );
                }
            }
        }

        /**
         * =====================================================================
         * 4) PREPARAÇÃO DE LINK DE WHATSAPP COM MENSAGEM PADRÃO
         * =====================================================================
         */
        $endereco_meta = $meta['_endereco_imovel'] ?? '';
        $mensagem = urlencode(
            "Olá, gostaria de informações sobre o imóvel: " . get_the_title() .
            " - Endereço: " . $endereco_meta
        );
        $whatsapp_link = "https://wa.me/{$whatsapp}?text={$mensagem}";

        /**
         * =====================================================================
         * 5) CRIAÇÃO DO SCHEMA (JSON-LD)
         * =====================================================================
         * Expande o RealEstateListing com alguns campos adicionais.
         */
        $schema_data = [
            "@context"    => "https://schema.org",
            "@type"       => "RealEstateListing",
            "name"        => $property['title'],
            "description" => wp_strip_all_tags( $property['descricao'] ),
            "image"       => $property['gallery'],
            "address"     => [
                "@type"           => "PostalAddress",
                "streetAddress"   => $meta['_endereco_imovel'] ?? '',
                "addressLocality" => $meta['_cidade_imovel']   ?? 'São Paulo',
                "addressRegion"   => "SP"
            ],
            "offers" => [
                "@type"         => "Offer",
                "price"         => ( $meta['_preco_imovel'] ?? 0 ),
                "priceCurrency" => "BRL"
            ]
        ];

        // ----------------------------------------------------------------------------
        // Início do HTML principal do Template
        // ----------------------------------------------------------------------------
        ?>

        <!-- MAIN WRAPPER DO CONTEÚDO DO IMÓVEL -->
        <main class="imovel-container" itemscope itemtype="https://schema.org/RealEstateListing">

            <!-- (A) INSERE O SCHEMA EM FORMATO JSON-LD PARA SEO -->
            <script type="application/ld+json">
                <?php echo json_encode( $schema_data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ); ?>
            </script>

            <!-- (B) BOTÃO DE VOLTAR (BASEADO NO HTTP REFERER) -->
            <?php
            // Verifica se existe um HTTP referrer para voltar
            $back_url = wp_get_referer(); // ou $_SERVER['HTTP_REFERER']
            if ( ! $back_url ) {
                // Se não houver referer, pode cair no link do archive
                $back_url = get_post_type_archive_link( 'imovel' );
            }
            ?>
            <div class="back-button-wrapper" style="margin-top: 20px;">
                <a href="<?php echo esc_url( $back_url ); ?>" class="back-button">
                    &larr; <?php _e( 'Voltar', 'imoveis-sp' ); ?>
                </a>
            </div>

            <!-- (C) SEÇÃO DA GALERIA DE IMAGENS -->
            <section class="property-gallery">
                <!-- Galeria Principal (Swiper) -->
                <div class="swiper gallery-main">
                    <div class="swiper-wrapper">
                        <?php foreach ( $property['gallery'] as $img ) : ?>
                            <div class="swiper-slide">
                                <img 
                                    src="<?php echo $img; ?>" 
                                    alt="<?php echo esc_attr( $property['title'] ); ?>" 
                                    loading="lazy"
                                    class="gallery-image" />
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Controles do Swiper (paginação e setas) -->
                    <div class="swiper-pagination"></div>
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-button-next"></div>
                </div>

                <!-- Thumbnails (se tiver mais de 1 imagem) -->
                <?php if ( count( $property['gallery'] ) > 1 ) : ?>
                    <div class="swiper gallery-thumbs">
                        <div class="swiper-wrapper">
                            <?php foreach ( $property['gallery'] as $img ) : ?>
                                <div class="swiper-slide">
                                    <img 
                                        src="<?php echo $img; ?>" 
                                        alt="Thumbnail" 
                                        loading="lazy" />
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </section>

            <!-- (D) GRADE DE DETALHES: INFO PRINCIPAL + MAPA -->
            <div class="property-details-grid">
                
                <!-- (D1) INFO PRINCIPAL DO IMÓVEL -->
                <section class="property-info">
                    <h1 class="property-title" itemprop="name">
                        <?php echo $property['title']; ?>
                    </h1>

                    <!-- Exibe preço e tipo -->
                    <div class="price-badge">
                        <span class="price">
                            R$ <?php echo $property['preco']; ?>
                        </span>
                        <span class="type">
                            <?php echo $property['tipo']; ?>
                        </span>
                    </div>

                    <!-- Destaques do imóvel (quartos, banheiros, etc.) -->
                    <div class="property-highlights">
                        <?php
                        // Para cada detalhe (area, quartos, banheiros, suites, vagas)
                        // se o valor > 0, exibe
                        foreach ( $property['detalhes'] as $key => $value ) :
                            if ( $value > 0 ) :
                                ?>
                                <div class="highlight-item">
                                    <span class="value"><?php echo $value; ?></span>
                                    <span class="label"><?php echo ucfirst( $key ); ?></span>
                                </div>
                                <?php
                            endif;
                        endforeach;
                        ?>
                    </div>

                    <!-- BOTÃO DE CONTATO VIA WHATSAPP -->
                    <?php if ( $whatsapp ) : ?>
                        <div class="cta-buttons">
                            <!-- Link direto no WhatsApp -->
                            <a href="<?php echo esc_url( $whatsapp_link ); ?>"
                               class="whatsapp-button"
                               target="_blank"
                               rel="noopener">
                                <i class="fab fa-whatsapp"></i>
                                <?php _e( 'Agendar Visita', 'imoveis-sp' ); ?>
                            </a>

                            <!-- Botão do Tour Virtual (abre modal) -->
                            <?php if ( $property['video'] ) : ?>
                                <button class="virtual-tour" data-video="<?php echo $property['video']; ?>">
                                    <i class="fas fa-video"></i>
                                    <?php _e( 'Tour Virtual', 'imoveis-sp' ); ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- (D2) SEÇÃO DE MAPA (GOOGLE MAPS) -->
                <?php if ( $gmap_api_key && $property['coords']['lat'] ) : ?>
                    <section class="property-map">
                        <iframe
                            width="100%"
                            height="450"
                            frameborder="0"
                            style="border:0"
                            src="https://www.google.com/maps/embed/v1/view?key=<?php echo $gmap_api_key; ?>&center=<?php echo $property['coords']['lat']; ?>,<?php echo $property['coords']['lng']; ?>&zoom=16&maptype=roadmap"
                            allowfullscreen
                            loading="lazy">
                        </iframe>
                    </section>
                <?php endif; ?>
            </div><!-- .property-details-grid -->

            <!-- (E) DESCRIÇÃO DETALHADA + COMODIDADES -->
            <section class="property-description">
                <h2 class="section-title">
                    <i class="fas fa-file-alt"></i>
                    <?php _e( 'Descrição Detalhada', 'imoveis-sp' ); ?>
                </h2>
                <div class="description-content" itemprop="description">
                    <?php
                    // Sanitiza a descrição para evitar XSS (usa wpautop e wp_kses_post)
                    echo wp_kses_post( wpautop( $property['descricao'] ) );
                    ?>
                </div>

                <!-- Lista de Comodidades -->
                <?php
                // Se existirem comodidades
                $amenities_clean = array_map( 'trim', $property['comodidades'] );
                $amenities_clean = array_filter( $amenities_clean );
                if ( ! empty( $amenities_clean ) ) :
                    ?>
                    <div class="features-grid">
                        <?php foreach ( $amenities_clean as $amenity ) : ?>
                            <div class="feature-item">
                                <i class="fas fa-check-circle"></i>
                                <?php echo esc_html( $amenity ); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

        </main><!-- .imovel-container -->

        <!-- (F) MODAL PARA TOUR VIRTUAL (CASO EXISTA UM VÍDEO) -->
        <div id="video-modal" class="modal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <div id="video-player"></div>
            </div>
        </div>

        <!-- (G) CSS INLINE PARA DEIXAR O DESIGN MAIS BONITO E ORGANIZADO -->
        <style>
        :root {
            --primary-color: #1A365D;    /* Azul escuro */
            --secondary-color: #285E61;  /* Verde escuro */
            --accent-color: #4A7C59;     /* Verde médio */
            --text-color: #2D3748;
            --light-bg: #F7FAFC;
            --border-radius: 12px;
            --box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --section-spacing: 80px;
            --transition-speed: 0.3s;
        }

        .imovel-container {
            max-width: 1440px;
            margin: 0 auto;
            padding: 0 120px;
            font-family: 'Inter', system-ui, sans-serif;
            color: var(--text-color);
        }
        @media (max-width: 768px) {
            .imovel-container {
                padding: 0 40px;
            }
        }

        .back-button-wrapper {
            margin: 30px 0 0;
        }
        .back-button {
            display: inline-block;
            padding: 0.6rem 1.2rem;
            background: var(--light-bg);
            color: var(--text-color);
            border-radius: 6px;
            text-decoration: none;
            box-shadow: var(--box-shadow);
            transition: transform var(--transition-speed), background-color var(--transition-speed);
        }
        .back-button:hover {
            transform: translateY(-3px);
            background-color: #E2E8F0;
        }

        .property-gallery {
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            margin: var(--section-spacing) 0;
        }

        .gallery-main img {
            width: 100%;
            height: 70vh;
            min-height: 500px;
            object-fit: cover;
        }
        @media (max-width: 768px) {
            .gallery-main img {
                height: 400px;
                min-height: auto;
            }
        }

        /* Thumbs */
        .gallery-thumbs {
            margin-top: 10px;
        }
        .gallery-thumbs img {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 4px;
        }

        .property-details-grid {
            display: grid;
            gap: 60px;
            grid-template-columns: 1fr 1fr;
            margin: var(--section-spacing) 0;
        }
        @media (max-width: 768px) {
            .property-details-grid {
                grid-template-columns: 1fr;
                gap: 40px;
            }
        }

        .property-info .property-title {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .price-badge {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: #fff;
            padding: 1.5rem 2rem;
            border-radius: var(--border-radius);
            margin: 2rem 0;
            display: inline-flex;
            gap: 1rem;
            align-items: center;
            box-shadow: var(--box-shadow);
        }
        .price-badge .price {
            font-size: 2rem;
            font-weight: 700;
        }
        .price-badge .type {
            font-size: 1.2rem;
            font-weight: 400;
        }

        .property-highlights {
            display: flex;
            gap: 2rem;
            margin: 2rem 0;
        }
        .highlight-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .highlight-item .value {
            font-size: 1.6rem;
            font-weight: 600;
            color: var(--accent-color);
        }
        .highlight-item .label {
            font-size: 0.9rem;
            color: #555;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        @media (max-width: 768px) {
            .cta-buttons {
                flex-direction: column;
            }
        }
        .whatsapp-button {
            background: linear-gradient(135deg, #25D366, #128C7E);
            color: #fff;
            padding: 1rem 2rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            transition: transform 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }
        .whatsapp-button:hover {
            transform: scale(1.03);
        }

        .virtual-tour {
            background: linear-gradient(135deg, var(--accent-color), var(--secondary-color));
            border: none;
            color: #fff;
            padding: 1rem 2rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: transform 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }
        .virtual-tour:hover {
            transform: scale(1.03);
        }

        .property-map {
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            height: 450px;
        }

        .property-description {
            margin-bottom: var(--section-spacing);
        }
        .property-description .section-title {
            font-size: 1.6rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .description-content {
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        .feature-item {
            background: var(--light-bg);
            padding: 1.5rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .feature-item i {
            color: var(--accent-color);
            font-size: 1.2rem;
        }

        /* SWIPER THUMBS OPACIDADE */
        .gallery-thumbs .swiper-slide {
            opacity: 0.5;
            transition: opacity 0.3s;
        }
        .gallery-thumbs .swiper-slide-thumb-active {
            opacity: 1;
        }

        /* MODAL DO TOUR VIRTUAL */
        .modal {
            display: none; /* hidden por padrão */
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.7);
        }
        .modal-content {
            position: relative;
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            max-width: 800px;
            width: 90%;
            border-radius: 8px;
        }
        .close-modal {
            position: absolute;
            top: 10px;
            right: 15px;
            color: #333;
            font-size: 1.5rem;
            font-weight: bold;
            cursor: pointer;
        }
        .close-modal:hover {
            color: var(--accent-color);
        }
        #video-player iframe {
            width: 100%;
            height: 500px;
            border: none;
        }
        </style>

        <!-- (H) SCRIPTS DE INICIALIZAÇÃO DO SWIPER E MODAL -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Inicialização do Swiper (galeria principal e thumbs)
            const mainSwiper = new Swiper('.gallery-main', {
                loop: true,
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev'
                },
                // Integra com thumbs
                thumbs: {
                    swiper: new Swiper('.gallery-thumbs', {
                        slidesPerView: 5,
                        spaceBetween: 10,
                        breakpoints: {
                            768: { slidesPerView: 7 }
                        }
                    })
                }
            });

            // 2. Modal de Vídeo (Tour Virtual)
            const videoModal = document.getElementById('video-modal');
            const closeModalBtn = videoModal.querySelector('.close-modal');
            const videoPlayer = document.getElementById('video-player');

            // Seleciona o botão "virtual-tour" (se existir)
            const virtualTourBtn = document.querySelector('.virtual-tour');
            if (virtualTourBtn) {
                virtualTourBtn.addEventListener('click', () => {
                    const videoUrl = virtualTourBtn.dataset.video;
                    if (videoUrl) {
                        videoPlayer.innerHTML = `
                            <iframe src="${videoUrl}"
                                width="100%" height="500"
                                frameborder="0"
                                allow="autoplay; fullscreen"
                                allowfullscreen></iframe>`;
                        videoModal.style.display = 'block';
                    }
                });
            }

            // Botão de fechar modal
            closeModalBtn.addEventListener('click', () => {
                videoModal.style.display = 'none';
                videoPlayer.innerHTML = '';
            });

            // Fecha o modal ao clicar fora da área de conteúdo
            window.addEventListener('click', (e) => {
                if (e.target === videoModal) {
                    videoModal.style.display = 'none';
                    videoPlayer.innerHTML = '';
                }
            });
        });
        </script>

        <?php
    endwhile;
endif;

/* ============================================================================
   CHAMADA DO FOOTER DO TEMA
   ============================================================================ */
get_footer();

/* ----------------------------------------------------------------------------
   FIM DO TEMPLATE "SINGLE-IMOVEL.PHP" PREMIUM
   ~ FIM DE ARQUIVO ~
---------------------------------------------------------------------------- */
