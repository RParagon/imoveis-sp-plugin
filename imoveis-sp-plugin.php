<?php
/**
 * Plugin Name: Imóveis São Paulo
 * Plugin URI:  https://github.com/RParagon/imoveis-sp-plugin/tree/V3
 * Description: Plugin completo para cadastro, listagem e pesquisa de imóveis – totalmente responsivo, com autocomplete de endereço, filtros avançados e página de detalhes customizada com contato via WhatsApp.
 * Version:     1.1
 * Author:      Virtual Mark
 * Author URI:  https://virtualmark.com.br
 * License:     GPL2
 * Text Domain: imoveis-sp
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Evita acesso direto
}

class ImoveisSPPlugin {

    const VERSION = '1.1';

    public function __construct() {
        // Registra o CPT e metaboxes
        add_action( 'init', array( $this, 'registrar_cpt_imoveis' ) );
        add_action( 'add_meta_boxes', array( $this, 'registrar_metaboxes' ) );
        add_action( 'save_post', array( $this, 'salvar_dados_imovel' ) );

        // Shortcode – catálogo completo com filtros modernos
        add_shortcode( 'catalogo_imoveis', array( $this, 'shortcode_catalogo_imoveis' ) );

        // Força template customizado para single-imovel (se o tema não tiver um)
        add_filter( 'single_template', array( $this, 'forcar_template_single_imovel' ) );

        // Carrega scripts e estilos para frontend
        add_action( 'wp_enqueue_scripts', array( $this, 'carregar_assets' ) );
        // Carrega assets para o admin
        add_action( 'admin_enqueue_scripts', array( $this, 'carregar_assets_admin' ) );

        // Página de configurações no admin
        add_action( 'admin_menu', array( $this, 'adicionar_pagina_config' ) );
        add_action( 'admin_init', array( $this, 'registrar_config' ) );

        // Flush rewrite rules na ativação e desativação
        register_activation_hook( __FILE__, array( $this, 'ativar_plugin' ) );
        register_deactivation_hook( __FILE__, array( $this, 'desativar_plugin' ) );
    }

    /**
     * Ativação do plugin: registra o CPT e executa flush
     */
    public function ativar_plugin() {
        $this->registrar_cpt_imoveis();
        flush_rewrite_rules();
    }

    /**
     * Desativação do plugin: executa flush rewrite rules
     */
    public function desativar_plugin() {
        flush_rewrite_rules();
    }

    /* =========================================================================
       ======================= REGISTRO DO CPT "IMÓVEL" =======================
       ========================================================================= */
    public function registrar_cpt_imoveis() {
        $labels = array(
            'name'               => __( 'Imóveis', 'imoveis-sp' ),
            'singular_name'      => __( 'Imóvel', 'imoveis-sp' ),
            'menu_name'          => __( 'Imóveis', 'imoveis-sp' ),
            'name_admin_bar'     => __( 'Imóvel', 'imoveis-sp' ),
            'add_new'            => __( 'Adicionar Novo', 'imoveis-sp' ),
            'add_new_item'       => __( 'Adicionar Novo Imóvel', 'imoveis-sp' ),
            'new_item'           => __( 'Novo Imóvel', 'imoveis-sp' ),
            'edit_item'          => __( 'Editar Imóvel', 'imoveis-sp' ),
            'view_item'          => __( 'Ver Imóvel', 'imoveis-sp' ),
            'all_items'          => __( 'Todos os Imóveis', 'imoveis-sp' ),
            'search_items'       => __( 'Buscar Imóveis', 'imoveis-sp' ),
            'parent_item_colon'  => __( 'Imóvel Pai:', 'imoveis-sp' ),
            'not_found'          => __( 'Nenhum imóvel encontrado.', 'imoveis-sp' ),
            'not_found_in_trash' => __( 'Nenhum imóvel encontrado na lixeira.', 'imoveis-sp' )
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'has_archive'        => true,
            'rewrite'            => array( 'slug' => 'imoveis' ),
            'supports'           => array( 'title', 'editor', 'thumbnail' ),
            'menu_icon'          => 'dashicons-building',
        );

        register_post_type( 'imovel', $args );
    }

    /* =========================================================================
       ======================= METABOXES & CAMPOS ==============================
       ========================================================================= */
    public function registrar_metaboxes() {
        add_meta_box(
            'dados_imovel',
            __( 'Dados do Imóvel', 'imoveis-sp' ),
            array( $this, 'metabox_dados_imovel_callback' ),
            'imovel',
            'normal',
            'default'
        );
    }

    public function metabox_dados_imovel_callback( $post ) {
        wp_nonce_field( 'salvar_dados_imovel', 'dados_imovel_nonce' );

        // Recupera os valores dos campos
        $campos = array(
            'endereco_imovel'  => get_post_meta( $post->ID, '_endereco_imovel', true ),
            'bairro_imovel'    => get_post_meta( $post->ID, '_bairro_imovel', true ),
            'cidade_imovel'    => get_post_meta( $post->ID, '_cidade_imovel', true ),
            'preco_imovel'     => get_post_meta( $post->ID, '_preco_imovel', true ),
            'descricao_imovel' => get_post_meta( $post->ID, '_descricao_imovel', true ),
            'area_imovel'      => get_post_meta( $post->ID, '_area_imovel', true ),
            'quartos_imovel'   => get_post_meta( $post->ID, '_quartos_imovel', true ),
            'banheiros_imovel' => get_post_meta( $post->ID, '_banheiros_imovel', true ),
            'suites_imovel'    => get_post_meta( $post->ID, '_suites_imovel', true ),
            'vagas_imovel'     => get_post_meta( $post->ID, '_vagas_imovel', true ),
            'tipo_imovel'      => get_post_meta( $post->ID, '_tipo_imovel', true ),
            'latitude_imovel'  => get_post_meta( $post->ID, '_latitude_imovel', true ),
            'longitude_imovel' => get_post_meta( $post->ID, '_longitude_imovel', true ),
        );
        ?>
        <div class="metabox-imoveis-sp">
            <p>
                <label for="endereco_imovel"><?php _e( 'Endereço (com autocomplete):', 'imoveis-sp' ); ?></label>
                <input type="text" name="endereco_imovel" id="endereco_imovel" class="google-places-autocomplete" value="<?php echo esc_attr( $campos['endereco_imovel'] ); ?>">
            </p>
            <p>
                <label for="bairro_imovel"><?php _e( 'Bairro:', 'imoveis-sp' ); ?></label>
                <input type="text" name="bairro_imovel" id="bairro_imovel" value="<?php echo esc_attr( $campos['bairro_imovel'] ); ?>">
            </p>
            <p>
                <label for="cidade_imovel"><?php _e( 'Cidade:', 'imoveis-sp' ); ?></label>
                <input type="text" name="cidade_imovel" id="cidade_imovel" value="<?php echo esc_attr( $campos['cidade_imovel'] ); ?>">
            </p>
            <div class="localizacao-wrapper">
                <div class="localizacao-half">
                    <label for="latitude_imovel"><?php _e( 'Latitude:', 'imoveis-sp' ); ?></label>
                    <input type="text" name="latitude_imovel" id="latitude_imovel" value="<?php echo esc_attr( $campos['latitude_imovel'] ); ?>">
                </div>
                <div class="localizacao-half">
                    <label for="longitude_imovel"><?php _e( 'Longitude:', 'imoveis-sp' ); ?></label>
                    <input type="text" name="longitude_imovel" id="longitude_imovel" value="<?php echo esc_attr( $campos['longitude_imovel'] ); ?>">
                </div>
                <div style="clear:both;"></div>
            </div>
            <p>
                <label for="preco_imovel"><?php _e( 'Preço (R$):', 'imoveis-sp' ); ?></label>
                <input type="number" step="0.01" name="preco_imovel" id="preco_imovel" value="<?php echo esc_attr( $campos['preco_imovel'] ); ?>">
            </p>
            <p>
                <label for="descricao_imovel"><?php _e( 'Descrição:', 'imoveis-sp' ); ?></label>
                <textarea name="descricao_imovel" id="descricao_imovel" rows="4"><?php echo esc_textarea( $campos['descricao_imovel'] ); ?></textarea>
            </p>
            <hr>
            <p><strong><?php _e( 'Dados Adicionais', 'imoveis-sp' ); ?>:</strong></p>
            <p>
                <label for="area_imovel"><?php _e( 'Área (m²):', 'imoveis-sp' ); ?></label>
                <input type="number" step="0.01" name="area_imovel" id="area_imovel" value="<?php echo esc_attr( $campos['area_imovel'] ); ?>">
            </p>
            <p>
                <label for="quartos_imovel"><?php _e( 'Quartos:', 'imoveis-sp' ); ?></label>
                <input type="number" name="quartos_imovel" id="quartos_imovel" value="<?php echo esc_attr( $campos['quartos_imovel'] ); ?>">
            </p>
            <p>
                <label for="banheiros_imovel"><?php _e( 'Banheiros:', 'imoveis-sp' ); ?></label>
                <input type="number" name="banheiros_imovel" id="banheiros_imovel" value="<?php echo esc_attr( $campos['banheiros_imovel'] ); ?>">
            </p>
            <p>
                <label for="suites_imovel"><?php _e( 'Suítes:', 'imoveis-sp' ); ?></label>
                <input type="number" name="suites_imovel" id="suites_imovel" value="<?php echo esc_attr( $campos['suites_imovel'] ); ?>">
            </p>
            <p>
                <label for="vagas_imovel"><?php _e( 'Vagas de garagem:', 'imoveis-sp' ); ?></label>
                <input type="number" name="vagas_imovel" id="vagas_imovel" value="<?php echo esc_attr( $campos['vagas_imovel'] ); ?>">
            </p>
            <p>
                <label for="tipo_imovel"><?php _e( 'Tipo do Imóvel:', 'imoveis-sp' ); ?></label>
                <input type="text" name="tipo_imovel" id="tipo_imovel" value="<?php echo esc_attr( $campos['tipo_imovel'] ); ?>" placeholder="<?php _e('Ex: Apartamento, Casa, Comercial...', 'imoveis-sp'); ?>">
            </p>
        </div>
        <?php
    }

    public function salvar_dados_imovel( $post_id ) {
        // Verifica nonce, autosave e permissões do usuário
        if ( ! isset( $_POST['dados_imovel_nonce'] ) || ! wp_verify_nonce( $_POST['dados_imovel_nonce'], 'salvar_dados_imovel' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( isset( $_POST['post_type'] ) && 'imovel' === $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }
        }

        // Define os campos e os callbacks de sanitização apropriados
        $fields = array(
            'endereco_imovel'  => array( 'meta_key' => '_endereco_imovel',  'callback' => 'sanitize_text_field' ),
            'bairro_imovel'    => array( 'meta_key' => '_bairro_imovel',    'callback' => 'sanitize_text_field' ),
            'cidade_imovel'    => array( 'meta_key' => '_cidade_imovel',    'callback' => 'sanitize_text_field' ),
            'preco_imovel'     => array( 'meta_key' => '_preco_imovel',     'callback' => 'floatval' ),
            'descricao_imovel' => array( 'meta_key' => '_descricao_imovel', 'callback' => 'sanitize_textarea_field' ),
            'area_imovel'      => array( 'meta_key' => '_area_imovel',      'callback' => 'floatval' ),
            'quartos_imovel'   => array( 'meta_key' => '_quartos_imovel',   'callback' => 'intval' ),
            'banheiros_imovel' => array( 'meta_key' => '_banheiros_imovel', 'callback' => 'intval' ),
            'suites_imovel'    => array( 'meta_key' => '_suites_imovel',    'callback' => 'intval' ),
            'vagas_imovel'     => array( 'meta_key' => '_vagas_imovel',     'callback' => 'intval' ),
            'tipo_imovel'      => array( 'meta_key' => '_tipo_imovel',      'callback' => 'sanitize_text_field' ),
            'latitude_imovel'  => array( 'meta_key' => '_latitude_imovel',  'callback' => 'sanitize_text_field' ),
            'longitude_imovel' => array( 'meta_key' => '_longitude_imovel', 'callback' => 'sanitize_text_field' ),
        );

        foreach ( $fields as $field_name => $data ) {
            if ( isset( $_POST[ $field_name ] ) ) {
                update_post_meta( $post_id, $data['meta_key'], call_user_func( $data['callback'], $_POST[ $field_name ] ) );
            }
        }
    }

    /* =========================================================================
       ============================ SHORTCODE: CATÁLOGO ========================
       =========================================================================
       Exibe uma página de catálogo completa com filtros avançados e paginação.
    */
    public function shortcode_catalogo_imoveis( $atts ) {
        ob_start();

        // Recupera os filtros enviados via GET e prepara a query
        $meta_query = array( 'relation' => 'AND' );

        if ( ! empty( $_GET['filtro_rua'] ) ) {
            $meta_query[] = array(
                'key'     => '_endereco_imovel',
                'value'   => sanitize_text_field( $_GET['filtro_rua'] ),
                'compare' => 'LIKE'
            );
        }
        if ( ! empty( $_GET['filtro_bairro'] ) ) {
            $meta_query[] = array(
                'key'     => '_bairro_imovel',
                'value'   => sanitize_text_field( $_GET['filtro_bairro'] ),
                'compare' => 'LIKE'
            );
        }
        if ( ! empty( $_GET['filtro_cidade'] ) ) {
            $meta_query[] = array(
                'key'     => '_cidade_imovel',
                'value'   => sanitize_text_field( $_GET['filtro_cidade'] ),
                'compare' => 'LIKE'
            );
        }
        if ( ! empty( $_GET['filtro_tipo'] ) ) {
            $meta_query[] = array(
                'key'     => '_tipo_imovel',
                'value'   => sanitize_text_field( $_GET['filtro_tipo'] ),
                'compare' => 'LIKE'
            );
        }
        if ( ! empty( $_GET['filtro_preco_min'] ) ) {
            $meta_query[] = array(
                'key'     => '_preco_imovel',
                'value'   => floatval( $_GET['filtro_preco_min'] ),
                'compare' => '>=',
                'type'    => 'NUMERIC'
            );
        }
        if ( ! empty( $_GET['filtro_preco_max'] ) ) {
            $meta_query[] = array(
                'key'     => '_preco_imovel',
                'value'   => floatval( $_GET['filtro_preco_max'] ),
                'compare' => '<=',
                'type'    => 'NUMERIC'
            );
        }
        if ( ! empty( $_GET['filtro_dormitorios'] ) ) {
            $meta_query[] = array(
                'key'     => '_quartos_imovel',
                'value'   => intval( $_GET['filtro_dormitorios'] ),
                'compare' => '>='
            );
        }

        // Configura a paginação
        $paged = max( 1, get_query_var('paged'), isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1 );

        $args = array(
            'post_type'      => 'imovel',
            'posts_per_page' => 10,
            'paged'          => $paged,
            'meta_query'     => $meta_query,
        );
        $query = new WP_Query( $args );
        ?>
        <div class="catalogo-imoveis">
            <div class="catalogo-filtros">
                <form method="GET" action="">
                    <div class="filtro-item">
                        <label for="filtro_rua"><?php _e( 'Rua/Endereço:', 'imoveis-sp' ); ?></label>
                        <input type="text" id="filtro_rua" name="filtro_rua" class="google-places-autocomplete" placeholder="<?php _e( 'Digite a rua...', 'imoveis-sp' ); ?>" value="<?php echo isset($_GET['filtro_rua']) ? esc_attr($_GET['filtro_rua']) : ''; ?>">
                    </div>
                    <div class="filtro-item">
                        <label for="filtro_bairro"><?php _e( 'Bairro:', 'imoveis-sp' ); ?></label>
                        <input type="text" id="filtro_bairro" name="filtro_bairro" placeholder="<?php _e( 'Digite o bairro...', 'imoveis-sp' ); ?>" value="<?php echo isset($_GET['filtro_bairro']) ? esc_attr($_GET['filtro_bairro']) : ''; ?>">
                    </div>
                    <div class="filtro-item">
                        <label for="filtro_cidade"><?php _e( 'Cidade:', 'imoveis-sp' ); ?></label>
                        <input type="text" id="filtro_cidade" name="filtro_cidade" placeholder="<?php _e( 'Digite a cidade...', 'imoveis-sp' ); ?>" value="<?php echo isset($_GET['filtro_cidade']) ? esc_attr($_GET['filtro_cidade']) : ''; ?>">
                    </div>
                    <div class="filtro-item">
                        <label for="filtro_tipo"><?php _e( 'Tipo do Imóvel:', 'imoveis-sp' ); ?></label>
                        <input type="text" id="filtro_tipo" name="filtro_tipo" placeholder="<?php _e( 'Apartamento, Casa, etc.', 'imoveis-sp' ); ?>" value="<?php echo isset($_GET['filtro_tipo']) ? esc_attr($_GET['filtro_tipo']) : ''; ?>">
                    </div>
                    <div class="filtro-item">
                        <label for="filtro_preco_min"><?php _e( 'Preço Mínimo (R$):', 'imoveis-sp' ); ?></label>
                        <input type="number" step="0.01" id="filtro_preco_min" name="filtro_preco_min" value="<?php echo isset($_GET['filtro_preco_min']) ? esc_attr($_GET['filtro_preco_min']) : ''; ?>">
                    </div>
                    <div class="filtro-item">
                        <label for="filtro_preco_max"><?php _e( 'Preço Máximo (R$):', 'imoveis-sp' ); ?></label>
                        <input type="number" step="0.01" id="filtro_preco_max" name="filtro_preco_max" value="<?php echo isset($_GET['filtro_preco_max']) ? esc_attr($_GET['filtro_preco_max']) : ''; ?>">
                    </div>
                    <div class="filtro-avancado">
                        <button type="button" class="btn-toggle-filtros"><?php _e( '+ Filtros', 'imoveis-sp' ); ?></button>
                        <div class="filtros-adicionais" style="display:none;">
                            <div class="filtro-item">
                                <label for="filtro_dormitorios"><?php _e( 'Dormitórios:', 'imoveis-sp' ); ?></label>
                                <input type="number" id="filtro_dormitorios" name="filtro_dormitorios" value="<?php echo isset($_GET['filtro_dormitorios']) ? esc_attr($_GET['filtro_dormitorios']) : ''; ?>">
                            </div>
                            <!-- Mais filtros podem ser adicionados aqui -->
                        </div>
                    </div>
                    <div class="filtro-item">
                        <button type="submit" class="btn-pesquisa"><?php _e( 'Pesquisar', 'imoveis-sp' ); ?></button>
                    </div>
                </form>
            </div>
            <div class="catalogo-listagem">
                <?php
                if ( $query->have_posts() ) {
                    echo '<div class="lista-imoveis">';
                    while ( $query->have_posts() ) {
                        $query->the_post();
                        $endereco  = get_post_meta( get_the_ID(), '_endereco_imovel', true );
                        $bairro    = get_post_meta( get_the_ID(), '_bairro_imovel', true );
                        $cidade    = get_post_meta( get_the_ID(), '_cidade_imovel', true );
                        $preco     = get_post_meta( get_the_ID(), '_preco_imovel', true );
                        $tipo      = get_post_meta( get_the_ID(), '_tipo_imovel', true );
                        ?>
                        <div class="imovel-item">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <div class="imovel-thumb">
                                    <?php the_post_thumbnail( 'medium_large' ); ?>
                                </div>
                            <?php endif; ?>
                            <div class="imovel-info">
                                <h3 class="imovel-titulo"><?php the_title(); ?></h3>
                                <p><i class="fas fa-map-marker-alt"></i> <?php echo esc_html( $endereco ); ?> - <?php echo esc_html( $bairro ); ?>, <?php echo esc_html( $cidade ); ?></p>
                                <p><i class="fas fa-tag"></i> <?php _e( 'Tipo:', 'imoveis-sp' ); ?> <?php echo esc_html( $tipo ); ?></p>
                                <p><i class="fas fa-dollar-sign"></i> <?php _e( 'Preço:', 'imoveis-sp' ); ?> R$ <?php echo esc_html( $preco ); ?></p>
                                <a href="<?php the_permalink(); ?>" class="btn-detalhes"><?php _e( 'Ver detalhes', 'imoveis-sp' ); ?></a>
                            </div>
                        </div>
                        <?php
                    }
                    echo '</div>';
                    // Exibe a paginação
                    $big = 999999999;
                    echo paginate_links( array(
                        'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
                        'format'  => '?paged=%#%',
                        'current' => $paged,
                        'total'   => $query->max_num_pages,
                    ) );
                } else {
                    echo '<p>' . __( 'Nenhum imóvel encontrado para estes filtros.', 'imoveis-sp' ) . '</p>';
                }
                wp_reset_postdata();
                ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /* =========================================================================
       ===================== TEMPLATE SINGLE CUSTOMIZADO =======================
       ========================================================================= */
    public function forcar_template_single_imovel( $single_template ) {
        global $post;
        if ( 'imovel' === $post->post_type ) {
            $template_plugin = plugin_dir_path( __FILE__ ) . 'templates/single-imovel.php';
            if ( file_exists( $template_plugin ) ) {
                return $template_plugin;
            }
        }
        return $single_template;
    }

    /* =========================================================================
       ======================= CARREGAR ASSETS (CSS/JS) ========================
       ========================================================================= */
    public function carregar_assets() {
        // CSS base do plugin
        wp_enqueue_style(
            'imoveis-sp-css',
            plugin_dir_url( __FILE__ ) . 'css/imoveis-sp.css',
            array(),
            self::VERSION
        );

        // Font Awesome
        wp_enqueue_style(
            'font-awesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css',
            array(),
            '6.0.0'
        );

        // JS customizado
        wp_enqueue_script(
            'imoveis-sp-js',
            plugin_dir_url( __FILE__ ) . 'js/imoveis-sp.js',
            array('jquery'),
            self::VERSION,
            true
        );
    }

    /**
     * Carrega assets específicos para o admin (CSS/JS)
     */
    public function carregar_assets_admin( $hook ) {
        global $post_type;
        // Carrega os assets apenas nas telas de edição de 'imovel' ou na página de configurações do plugin
        if ( ( 'post.php' === $hook || 'post-new.php' === $hook ) && 'imovel' === $post_type ) {
            wp_enqueue_style(
                'imoveis-sp-admin-css',
                plugin_dir_url( __FILE__ ) . 'css/imoveis-sp-admin.css',
                array(),
                self::VERSION
            );
        }
        if ( $hook === 'settings_page_imoveis-sp-config' ) {
            wp_enqueue_style(
                'imoveis-sp-admin-css',
                plugin_dir_url( __FILE__ ) . 'css/imoveis-sp-admin.css',
                array(),
                self::VERSION
            );
        }
    }

    /* =========================================================================
       ========================= PÁGINA DE CONFIGURAÇÕES ========================
       ========================================================================= */
    public function adicionar_pagina_config() {
        add_options_page(
            __( 'Config Imóveis SP', 'imoveis-sp' ),
            __( 'Imóveis SP Config', 'imoveis-sp' ),
            'manage_options',
            'imoveis-sp-config',
            array( $this, 'pagina_config_callback' )
        );
    }

    public function pagina_config_callback() {
        ?>
        <div class="wrap">
            <h1><?php _e( 'Configurações do Plugin Imóveis SP', 'imoveis-sp' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'imoveis_sp_config_group' );
                do_settings_sections( 'imoveis-sp-config' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function registrar_config() {
        // Chave da API do Google Places
        register_setting(
            'imoveis_sp_config_group',
            'imoveis_sp_google_api_key',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => ''
            )
        );
        // Número do WhatsApp para contato
        register_setting(
            'imoveis_sp_config_group',
            'imoveis_sp_whatsapp',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => ''
            )
        );

        add_settings_section(
            'imoveis_sp_config_section',
            __( 'Configurações de API e Contato', 'imoveis-sp' ),
            function() {
                echo '<p>' . __( 'Insira sua chave da API do Google para habilitar o autocomplete e o número de WhatsApp para contato.', 'imoveis-sp' ) . '</p>';
            },
            'imoveis-sp-config'
        );

        add_settings_field(
            'imoveis_sp_google_api_key_field',
            __( 'Chave da API do Google:', 'imoveis-sp' ),
            array( $this, 'campo_api_key_callback' ),
            'imoveis-sp-config',
            'imoveis_sp_config_section'
        );

        add_settings_field(
            'imoveis_sp_whatsapp_field',
            __( 'WhatsApp (somente números e código do país):', 'imoveis-sp' ),
            array( $this, 'campo_whatsapp_callback' ),
            'imoveis-sp-config',
            'imoveis_sp_config_section'
        );
    }

    public function campo_api_key_callback() {
        $value = get_option( 'imoveis_sp_google_api_key', '' );
        echo '<input type="text" name="imoveis_sp_google_api_key" value="' . esc_attr( $value ) . '" size="50">';
    }

    public function campo_whatsapp_callback() {
        $value = get_option( 'imoveis_sp_whatsapp', '' );
        echo '<input type="text" name="imoveis_sp_whatsapp" value="' . esc_attr( $value ) . '" size="20">';
        echo '<p class="description">' . __( 'Exemplo: 5511999999999', 'imoveis-sp' ) . '</p>';
    }
}

new ImoveisSPPlugin();
