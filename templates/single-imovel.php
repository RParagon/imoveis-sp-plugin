<?php
/**
 * Template Premium para CPT Imóvel - Versão 3.0
 * Features: Design Moderno, Multi-galeria, Google Maps Integrado, Performance
 */
get_header();

if (have_posts()) : 
    while (have_posts()) : the_post();
        $post_id = get_the_ID();
        
        // Otimização com cache transient
        $meta = get_transient("imovel_{$post_id}_meta");
        if(false === $meta) {
            $meta = array_map(function($a) { return maybe_unserialize($a[0]); }, get_post_meta($post_id));
            set_transient("imovel_{$post_id}_meta", $meta, HOUR_IN_SECONDS);
        }

        // Configurações
        $gmap_api_key = esc_attr(get_option('imoveis_sp_google_api_key'));
        $whatsapp = preg_replace('/[^0-9]/', '', get_option('imoveis_sp_whatsapp', ''));
        
        // Dados sanitizados
        $property = [
            'title'     => esc_html(get_the_title()),
            'tipo'      => esc_html($meta['_tipo_imovel'] ?? __('Não especificado', 'imoveis-sp')),
            'endereco'  => esc_html(implode(', ', array_filter([
                $meta['_endereco_imovel'] ?? '',
                $meta['_bairro_imovel'] ?? '',
                $meta['_cidade_imovel'] ?? 'SP'
            ]))),
            'preco'     => is_numeric($meta['_preco_imovel'] ?? 0) ? number_format((float)$meta['_preco_imovel'], 2, ',', '.') : 'Consulte',
            'detalhes'  => [
                'area'      => (int)($meta['_area_imovel'] ?? 0),
                'quartos'   => (int)($meta['_quartos_imovel'] ?? 0),
                'banheiros' => (int)($meta['_banheiros_imovel'] ?? 0),
                'suites'    => (int)($meta['_suites_imovel'] ?? 0),
                'vagas'     => (int)($meta['_vagas_imovel'] ?? 0)
            ],
            'coords'    => [
                'lat' => (float)($meta['_latitude_imovel'] ?? 0),
                'lng' => (float)($meta['_longitude_imovel'] ?? 0)
            ],
            'gallery'   => array_map('esc_url', get_post_meta($post_id, '_gallery_imovel', true) ?: []),
            'video'     => esc_url($meta['_video_imovel'] ?? '')
        ];
        ?>
        
        <main class="imovel-container" itemscope itemtype="http://schema.org/RealEstateListing">
            <!-- Schema Structured Data -->
            <script type="application/ld+json">
                <?php echo json_encode([
                    "@context"    => "http://schema.org",
                    "@type"       => "RealEstateListing",
                    "name"        => $property['title'],
                    "description" => wp_strip_all_tags($meta['_descricao_imovel'] ?? ''),
                    "image"       => $property['gallery'],
                    "address"     => [
                        "@type"           => "PostalAddress",
                        "streetAddress"   => $meta['_endereco_imovel'] ?? '',
                        "addressLocality" => $meta['_cidade_imovel'] ?? 'São Paulo',
                        "addressRegion"   => "SP"
                    ],
                    "offers" => [
                        "@type" => "Offer",
                        "price" => $meta['_preco_imovel'] ?? 0,
                        "priceCurrency" => "BRL"
                    ]
                ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>
            </script>

            <!-- Gallery Section -->
            <section class="property-gallery">
                <div class="swiper gallery-main">
                    <div class="swiper-wrapper">
                        <?php foreach($property['gallery'] as $img) : ?>
                        <div class="swiper-slide">
                            <img src="<?php echo $img; ?>" 
                                 alt="<?php echo $property['title']; ?>" 
                                 loading="lazy"
                                 class="gallery-image">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-pagination"></div>
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-button-next"></div>
                </div>
                
                <?php if(count($property['gallery']) > 1) : ?>
                <div class="swiper gallery-thumbs">
                    <div class="swiper-wrapper">
                        <?php foreach($property['gallery'] as $img) : ?>
                        <div class="swiper-slide">
                            <img src="<?php echo $img; ?>" 
                                 alt="Thumbnail" 
                                 loading="lazy">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </section>

            <!-- Property Details -->
            <div class="property-details-grid">
                <!-- Main Info -->
                <section class="property-info">
                    <h1 class="property-title" itemprop="name"><?php echo $property['title']; ?></h1>
                    <div class="price-badge">
                        <span class="price">R$ <?php echo $property['preco']; ?></span>
                        <span class="type"><?php echo $property['tipo']; ?></span>
                    </div>
                    
                    <div class="property-highlights">
                        <?php foreach($property['detalhes'] as $key => $value) : 
                            if($value > 0) : ?>
                            <div class="highlight-item">
                                <span class="value"><?php echo $value; ?></span>
                                <span class="label"><?php echo ucfirst($key); ?></span>
                            </div>
                            <?php endif;
                        endforeach; ?>
                    </div>
                    <?php
                    $whatsapp_num = get_option('imoveis_sp_whatsapp','');
                    $endereco = get_post_meta(get_the_ID(), '_endereco_imovel', true);
                    $mensagem = urlencode("Olá, gostaria de informações sobre o imóvel: " . get_the_title() . " - Endereço: " . $endereco);
                     $whatsapp_link = "https://wa.me/{$whatsapp_num}?text={$mensagem}";
                    ?>
                    <a href="<?php echo esc_url($whatsapp_link); ?>" target="_blank">Contatar via WhatsApp</a>
                    <?php if($whatsapp) : ?>
                    <div class="cta-buttons">
                        <a href="https://wa.me/<?php echo $whatsapp; ?>" 
                           class="whatsapp-button"
                           target="_blank"
                           rel="noopener">
                            <i class="fab fa-whatsapp"></i> Agendar Visita
                        </a>
                        <button class="virtual-tour" data-video="<?php echo $property['video']; ?>">
                            <i class="fas fa-video"></i> Tour Virtual
                        </button>
                    </div>
                    <?php endif; ?>
                </section>

                <!-- Map Section -->
                <?php if($gmap_api_key && $property['coords']['lat']) : ?>
                <section class="property-map">
                    <iframe 
                        width="100%"
                        height="450"
                        frameborder="0"
                        style="border:0"
                        src="https://www.google.com/maps/embed/v1/view?key=<?= $gmap_api_key ?>&center=<?= $property['coords']['lat'] ?>,<?= $property['coords']['lng'] ?>&zoom=16&maptype=roadmap"
                        allowfullscreen
                        loading="lazy">
                    </iframe>
                </section>
                <?php endif; ?>
            </div>

            <!-- Description & Features -->
            <section class="property-description">
                <h2 class="section-title"><i class="fas fa-file-alt"></i> Descrição Detalhada</h2>
                <div class="description-content" itemprop="description">
                    <?php echo wp_kses_post(wpautop($meta['_descricao_imovel'] ?? '')); ?>
                </div>
                
                <div class="features-grid">
                    <?php $amenities = explode(',', $meta['_comodidades_imovel'] ?? ''); 
                    foreach($amenities as $amenity) : 
                        if(!empty(trim($amenity))) : ?>
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <?php echo esc_html(trim($amenity)); ?>
                        </div>
                        <?php endif;
                    endforeach; ?>
                </div>
            </section>
        </main>

        <!-- Modal para Tour Virtual -->
        <div id="video-modal" class="modal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <div id="video-player"></div>
            </div>
        </div>

        <?php
    endwhile;
endif;

get_footer();
?>

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
}

.imovel-container {
    max-width: 1440px;
    margin: 0 auto;
    padding: 0 120px;
    font-family: 'Inter', system-ui;
    color: var(--text-color);
}

@media (max-width: 768px) {
    .imovel-container {
        padding: 0 40px;
    }
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

.property-details-grid {
    display: grid;
    gap: 60px;
    grid-template-columns: 1fr 1fr;
    margin: var(--section-spacing) 0;
}

.price-badge {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
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

.cta-buttons {
    display: flex;
    gap: 1rem;
    margin-top: 3rem;
}

.whatsapp-button {
    background: linear-gradient(135deg, #25D366, #128C7E);
    color: white;
    padding: 1rem 2rem;
    border-radius: var(--border-radius);
    text-decoration: none;
    transition: transform 0.2s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
}

.virtual-tour {
    background: linear-gradient(135deg, var(--accent-color), var(--secondary-color));
    border: none;
    color: white;
    padding: 1rem 2rem;
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: transform 0.2s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
}

.property-map {
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--box-shadow);
    height: 450px;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.feature-item {
    background: var(--light-bg);
    padding: 1.5rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.swiper-thumbs .swiper-slide {
    opacity: 0.5;
    transition: opacity 0.3s;
}

.swiper-thumbs .swiper-slide-thumb-active {
    opacity: 1;
}

@media (max-width: 768px) {
    .property-details-grid {
        grid-template-columns: 1fr;
        gap: 40px;
    }
    
    .gallery-main img {
        height: 400px;
        min-height: auto;
    }
    
    .cta-buttons {
        flex-direction: column;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicialização do Swiper
    const mainSwiper = new Swiper('.gallery-main', {
        loop: true,
        pagination: { el: '.swiper-pagination', clickable: true },
        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
        thumbs: { swiper: new Swiper('.gallery-thumbs', { 
            slidesPerView: 5,
            spaceBetween: 10,
            breakpoints: {
                768: { slidesPerView: 7 }
            }
        }) }
    });

    // Modal de Vídeo
    const videoModal = document.getElementById('video-modal');
    document.querySelector('.virtual-tour')?.addEventListener('click', () => {
        const videoUrl = document.querySelector('.virtual-tour').dataset.video;
        if(videoUrl) {
            document.getElementById('video-player').innerHTML = `
                <iframe src="${videoUrl}" 
                    width="100%" height="500" 
                    frameborder="0" allow="autoplay; fullscreen" 
                    allowfullscreen></iframe>`;
            videoModal.style.display = 'block';
        }
    });
    
    videoModal.querySelector('.close-modal').addEventListener('click', () => {
        videoModal.style.display = 'none';
        document.getElementById('video-player').innerHTML = '';
    });
});
</script>