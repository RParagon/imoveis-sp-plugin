<?php
/**
 * Template Name: Listagem de Imóveis
 * Template Post Type: imovel
 */
get_header();

// Configurações do tema
$primary_color = '#1A365D';
$secondary_color = '#285E61';
$accent_color = '#4A7C59';
?>

<div class="imoveis-plugin-container">
    <!-- Cabeçalho da Busca -->
    <header class="imoveis-header" style="background: linear-gradient(135deg, <?php echo $primary_color; ?>, <?php echo $secondary_color; ?>);">
        <div class="search-container">
            <form action="<?php echo esc_url(home_url('/imoveis/')); ?>" method="get">
                <div class="search-grid">
                    <input type="text" name="s" placeholder="Buscar imóveis..." value="<?php echo get_search_query(); ?>">
                    <select name="tipo">
                        <option value="">Todos os Tipos</option>
                        <?php
                        $tipos = get_terms(['taxonomy' => 'tipo_imovel', 'hide_empty' => false]);
                        foreach ($tipos as $tipo) {
                            echo '<option value="' . $tipo->slug . '"' . selected($_GET['tipo'] ?? '', $tipo->slug) . '>' . $tipo->name . '</option>';
                        }
                        ?>
                    </select>
                    <button type="submit">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </form>
        </div>
    </header>

    <?php if (is_singular('imovel')) : ?>
        <!-- Template Single Imóvel -->
        <?php
        $post_id = get_the_ID();
        $meta = get_post_meta($post_id);
        $property = [
            'title' => get_the_title(),
            'preco' => number_format((float)($meta['_preco_imovel'][0] ?? 0), 2, ',', '.'),
            'tipo' => esc_html($meta['_tipo_imovel'][0] ?? ''),
            'gallery' => array_map('esc_url', explode(',', $meta['_gallery_imovel'][0] ?? '')),
            'detalhes' => [
                'area' => $meta['_area_imovel'][0] ?? 0,
                'quartos' => $meta['_quartos_imovel'][0] ?? 0,
                'banheiros' => $meta['_banheiros_imovel'][0] ?? 0,
                'vagas' => $meta['_vagas_imovel'][0] ?? 0
            ]
        ];
        ?>
        
        <main class="single-imovel-template">
            <section class="property-gallery">
                <div class="swiper gallery-main">
                    <div class="swiper-wrapper">
                        <?php foreach ($property['gallery'] as $img) : ?>
                        <div class="swiper-slide">
                            <img src="<?php echo esc_url($img); ?>" 
                                 alt="<?php echo esc_attr($property['title']); ?>" 
                                 loading="lazy">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </section>

            <div class="property-content">
                <div class="property-header">
                    <h1><?php echo esc_html($property['title']); ?></h1>
                    <div class="price-badge">R$ <?php echo $property['preco']; ?></div>
                </div>

                <div class="property-details-grid">
                    <?php foreach ($property['detalhes'] as $key => $value) : ?>
                    <div class="detail-item">
                        <span class="label"><?php echo ucfirst($key); ?></span>
                        <span class="value"><?php echo esc_html($value); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="property-description">
                    <?php the_content(); ?>
                </div>
            </div>
        </main>

    <?php else : ?>
        <!-- Template Listagem -->
        <div class="imoveis-list-container">
            <?php
            $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
            $args = [
                'post_type' => 'imovel',
                'posts_per_page' => 12,
                'paged' => $paged,
                's' => sanitize_text_field($_GET['s'] ?? ''),
                'tax_query' => []
            ];

            if (!empty($_GET['tipo'])) {
                $args['tax_query'][] = [
                    'taxonomy' => 'tipo_imovel',
                    'field' => 'slug',
                    'terms' => sanitize_text_field($_GET['tipo'])
                ];
            }

            $query = new WP_Query($args);
            
            if ($query->have_posts()) :
                echo '<div class="imoveis-grid">';
                while ($query->have_posts()) : $query->the_post();
                    $meta = get_post_meta(get_the_ID());
                    ?>
                    <article class="imovel-card">
                        <a href="<?php the_permalink(); ?>">
                            <div class="card-image">
                                <?php if (has_post_thumbnail()) : ?>
                                    <?php the_post_thumbnail('large', ['loading' => 'lazy']); ?>
                                <?php endif; ?>
                                <span class="card-price">R$ <?php echo number_format((float)($meta['_preco_imovel'][0] ?? 0), 2, ',', '.'); ?></span>
                            </div>
                            <div class="card-content">
                                <h3><?php the_title(); ?></h3>
                                <div class="card-details">
                                    <span><i class="fas fa-bed"></i> <?php echo $meta['_quartos_imovel'][0] ?? 0; ?></span>
                                    <span><i class="fas fa-bath"></i> <?php echo $meta['_banheiros_imovel'][0] ?? 0; ?></span>
                                    <span><i class="fas fa-car"></i> <?php echo $meta['_vagas_imovel'][0] ?? 0; ?></span>
                                </div>
                            </div>
                        </a>
                    </article>
                <?php
                endwhile;
                echo '</div>';

                // Paginação
                echo '<div class="imoveis-pagination">';
                echo paginate_links([
                    'total' => $query->max_num_pages,
                    'prev_text' => '<i class="fas fa-chevron-left"></i>',
                    'next_text' => '<i class="fas fa-chevron-right"></i>'
                ]);
                echo '</div>';

                wp_reset_postdata();
            else :
                echo '<p class="no-results">Nenhum imóvel encontrado com esses critérios.</p>';
            endif;
            ?>
        </div>
    <?php endif; ?>
</div>

<style>
:root {
    --primary-color: <?php echo $primary_color; ?>;
    --secondary-color: <?php echo $secondary_color; ?>;
    --accent-color: <?php echo $accent_color; ?>;
    --text-color: #2D3748;
    --light-bg: #F7FAFC;
    --border-radius: 12px;
    --box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.imoveis-plugin-container {
    max-width: 1440px;
    margin: 0 auto;
    padding: 40px 120px;
}

.imoveis-header {
    padding: 60px 0;
    margin-bottom: 40px;
    border-radius: var(--border-radius);
}

.search-grid {
    display: grid;
    grid-template-columns: 1fr 200px 150px;
    gap: 15px;
    max-width: 1200px;
    margin: 0 auto;
}

.search-grid input,
.search-grid select {
    padding: 15px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
}

.search-grid button {
    background: var(--accent-color);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: transform 0.2s;
}

.imoveis-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 30px;
    margin: 40px 0;
}

.imovel-card {
    background: white;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--box-shadow);
    transition: transform 0.3s;
}

.imovel-card:hover {
    transform: translateY(-5px);
}

.card-image {
    position: relative;
    height: 250px;
    overflow: hidden;
}

.card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.card-price {
    position: absolute;
    bottom: 15px;
    left: 15px;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 8px 15px;
    border-radius: 5px;
}

.card-content {
    padding: 20px;
}

.card-details {
    display: flex;
    gap: 15px;
    margin-top: 15px;
    color: var(--primary-color);
}

.imoveis-pagination {
    display: flex;
    justify-content: center;
    gap: 10px;
    padding: 30px 0;
}

@media (max-width: 768px) {
    .imoveis-plugin-container {
        padding: 20px;
    }
    
    .search-grid {
        grid-template-columns: 1fr;
    }
    
    .imoveis-grid {
        grid-template-columns: 1fr;
    }
}

/* Single Template Styles */
.single-imovel-template {
    margin-top: 40px;
}

.property-gallery {
    border-radius: var(--border-radius);
    overflow: hidden;
    margin-bottom: 40px;
}

.property-content {
    margin: 40px 0;
}

.property-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.price-badge {
    background: var(--primary-color);
    color: white;
    padding: 12px 25px;
    border-radius: var(--border-radius);
}

.property-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
    margin: 30px 0;
}

.detail-item {
    text-align: center;
    padding: 20px;
    background: var(--light-bg);
    border-radius: var(--border-radius);
}

.detail-item .label {
    display: block;
    color: var(--secondary-color);
    font-weight: 600;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Swiper para galeria
    const swiper = new Swiper('.gallery-main', {
        pagination: {
            el: '.swiper-pagination',
            clickable: true
        }
    });

    // Smooth scroll para elementos internos
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
});
</script>

<?php
get_footer();