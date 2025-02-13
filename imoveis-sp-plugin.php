<?php
/**
 * Plugin Name: Imóveis São Paulo Premium
 * Plugin URI:  https://seudominio.com/imoveis-sp-premium
 * Description: Plugin completo para cadastro, listagem e pesquisa de imóveis em São Paulo – com design moderno, animações e auto-atualizações via Git.
 * Version:     2.0
 * Author:      Seu Nome
 * Author URI:  https://seudominio.com
 * License:     GPL2
 * Text Domain: imoveis-sp
 *
 * Atualizações automáticas:
 * Este plugin suporta atualizações automáticas a partir de um repositório Git.
 * Utilize a biblioteca "Plugin Update Checker" (incluída no pacote) para ativar essa funcionalidade.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ImoveisSPPremium {

    public function __construct() {
        // Registra o CPT e metaboxes
        add_action( 'init', array( $this, 'registrar_cpt_imoveis' ) );
        add_action( 'add_meta_boxes', array( $this, 'registrar_metaboxes' ) );
        add_action( 'save_post', array( $this, 'salvar_dados_imovel' ) );
        
        // Shortcodes
        add_shortcode( 'listar_imoveis', array( $this, 'shortcode_listar_imoveis' ) );
        add_shortcode( 'pesquisar_imoveis', array( $this, 'shortcode_pesquisar_imoveis' ) );
        add_shortcode( 'imoveis_custom', array( $this, 'shortcode_imoveis_custom' ) );
        
        // Enfileira CSS e JS
        add_action( 'wp_enqueue_scripts', array( $this, 'enfileirar_assets' ) );
        
        // Configura auto-update a partir do Git
        add_action( 'init', array( $this, 'setup_auto_update' ) );
    }
    
    /**
     * Configura o update checker para auto-atualizações via Git
     */
    public function setup_auto_update() {
        if ( ! class_exists( 'Puc_v4_Factory' ) ) {
            require_once plugin_dir_path( __FILE__ ) . 'plugin-update-checker/plugin-update-checker.php';
        }
        
        $updateChecker = Puc_v4_Factory::buildUpdateChecker(
            'https://github.com/RParagon/imoveis-sp-plugin', // Coloque aqui a URL do seu repositório Git
            __FILE__,
            'imoveis-sp-plugin'
        );
    }
    
    /**
     * Registra o Custom Post Type de Imóveis
     */
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
            'not_found'          => __( 'Nenhum imóvel encontrado.', 'imoveis-sp' ),
            'not_found_in_trash' => __( 'Nenhum imóvel encontrado na lixeira.', 'imoveis-sp' ),
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
    
    /**
     * Registra os metaboxes com os detalhes do imóvel
     */
    public function registrar_metaboxes() {
        add_meta_box(
            'dados_imovel',
            __( 'Detalhes do Imóvel', 'imoveis-sp' ),
            array( $this, 'metabox_dados_imovel_callback' ),
            'imovel',
            'normal',
            'high'
        );
    }
    
    /**
     * Renderiza o metabox com os campos personalizados
     */
    public function metabox_dados_imovel_callback( $post ) {
        wp_nonce_field( 'salvar_dados_imovel', 'dados_imovel_nonce' );
        
        // Recupera os metadados
        $endereco       = get_post_meta( $post->ID, '_endereco_imovel', true );
        $bairro         = get_post_meta( $post->ID, '_bairro_imovel', true );
        $cidade         = get_post_meta( $post->ID, '_cidade_imovel', true );
        $preco          = get_post_meta( $post->ID, '_preco_imovel', true );
        $descricao      = get_post_meta( $post->ID, '_descricao_imovel', true );
        $tipo           = get_post_meta( $post->ID, '_tipo_imovel', true );
        $quartos        = get_post_meta( $post->ID, '_quartos_imovel', true );
        $banheiros      = get_post_meta( $post->ID, '_banheiros_imovel', true );
        $garagens       = get_post_meta( $post->ID, '_garagens_imovel', true );
        $area           = get_post_meta( $post->ID, '_area_imovel', true );
        $ano_construcao = get_post_meta( $post->ID, '_ano_construcao_imovel', true );
        $status         = get_post_meta( $post->ID, '_status_imovel', true );
        ?>
        <style>
            .imoveis-meta-box label {
                display: block;
                margin-top: 10px;
                font-weight: bold;
            }
            .imoveis-meta-box input, .imoveis-meta-box select, .imoveis-meta-box textarea {
                width: 100%;
                padding: 6px;
                margin-top: 5px;
            }
        </style>
        <div class="imoveis-meta-box">
            <label for="endereco_imovel"><?php _e( 'Endereço:', 'imoveis-sp' ); ?></label>
            <input type="text" name="endereco_imovel" id="endereco_imovel" value="<?php echo esc_attr( $endereco ); ?>">
            
            <label for="bairro_imovel"><?php _e( 'Bairro:', 'imoveis-sp' ); ?></label>
            <input type="text" name="bairro_imovel" id="bairro_imovel" value="<?php echo esc_attr( $bairro ); ?>">
            
            <label for="cidade_imovel"><?php _e( 'Cidade:', 'imoveis-sp' ); ?></label>
            <input type="text" name="cidade_imovel" id="cidade_imovel" value="<?php echo esc_attr( $cidade ); ?>">
            
            <label for="preco_imovel"><?php _e( 'Preço (R$):', 'imoveis-sp' ); ?></label>
            <input type="number" step="0.01" name="preco_imovel" id="preco_imovel" value="<?php echo esc_attr( $preco ); ?>">
            
            <label for="descricao_imovel"><?php _e( 'Descrição:', 'imoveis-sp' ); ?></label>
            <textarea name="descricao_imovel" id="descricao_imovel" rows="4"><?php echo esc_textarea( $descricao ); ?></textarea>
            
            <label for="tipo_imovel"><?php _e( 'Tipo de Imóvel:', 'imoveis-sp' ); ?></label>
            <select name="tipo_imovel" id="tipo_imovel">
                <option value="casa" <?php selected( $tipo, 'casa' ); ?>><?php _e( 'Casa', 'imoveis-sp' ); ?></option>
                <option value="apartamento" <?php selected( $tipo, 'apartamento' ); ?>><?php _e( 'Apartamento', 'imoveis-sp' ); ?></option>
                <option value="comercial" <?php selected( $tipo, 'comercial' ); ?>><?php _e( 'Comercial', 'imoveis-sp' ); ?></option>
                <option value="terreno" <?php selected( $tipo, 'terreno' ); ?>><?php _e( 'Terreno', 'imoveis-sp' ); ?></option>
            </select>
            
            <label for="quartos_imovel"><?php _e( 'Número de Quartos:', 'imoveis-sp' ); ?></label>
            <input type="number" name="quartos_imovel" id="quartos_imovel" value="<?php echo esc_attr( $quartos ); ?>">
            
            <label for="banheiros_imovel"><?php _e( 'Número de Banheiros:', 'imoveis-sp' ); ?></label>
            <input type="number" name="banheiros_imovel" id="banheiros_imovel" value="<?php echo esc_attr( $banheiros ); ?>">
            
            <label for="garagens_imovel"><?php _e( 'Vagas de Garagem:', 'imoveis-sp' ); ?></label>
            <input type="number" name="garagens_imovel" id="garagens_imovel" value="<?php echo esc_attr( $garagens ); ?>">
            
            <label for="area_imovel"><?php _e( 'Área (m²):', 'imoveis-sp' ); ?></label>
            <input type="number" name="area_imovel" id="area_imovel" value="<?php echo esc_attr( $area ); ?>">
            
            <label for="ano_construcao_imovel"><?php _e( 'Ano de Construção:', 'imoveis-sp' ); ?></label>
            <input type="number" name="ano_construcao_imovel" id="ano_construcao_imovel" value="<?php echo esc_attr( $ano_construcao ); ?>">
            
            <label for="status_imovel"><?php _e( 'Status:', 'imoveis-sp' ); ?></label>
            <select name="status_imovel" id="status_imovel">
                <option value="venda" <?php selected( $status, 'venda' ); ?>><?php _e( 'Venda', 'imoveis-sp' ); ?></option>
                <option value="aluguel" <?php selected( $status, 'aluguel' ); ?>><?php _e( 'Aluguel', 'imoveis-sp' ); ?></option>
            </select>
        </div>
        <?php
    }
    
    /**
     * Salva os dados do imóvel ao salvar o post
     */
    public function salvar_dados_imovel( $post_id ) {
        if ( ! isset( $_POST['dados_imovel_nonce'] ) || ! wp_verify_nonce( $_POST['dados_imovel_nonce'], 'salvar_dados_imovel' ) ) {
            return;
        }
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
            return;
        }
        if ( isset( $_POST['post_type'] ) && 'imovel' === $_POST['post_type'] && ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        
        $fields = array(
            'endereco_imovel'       => '_endereco_imovel',
            'bairro_imovel'         => '_bairro_imovel',
            'cidade_imovel'         => '_cidade_imovel',
            'preco_imovel'          => '_preco_imovel',
            'descricao_imovel'      => '_descricao_imovel',
            'tipo_imovel'           => '_tipo_imovel',
            'quartos_imovel'        => '_quartos_imovel',
            'banheiros_imovel'      => '_banheiros_imovel',
            'garagens_imovel'       => '_garagens_imovel',
            'area_imovel'           => '_area_imovel',
            'ano_construcao_imovel' => '_ano_construcao_imovel',
            'status_imovel'         => '_status_imovel'
        );
        
        foreach ( $fields as $field => $meta_key ) {
            if ( isset( $_POST[$field] ) ) {
                if ( in_array( $field, array( 'preco_imovel', 'area_imovel' ) ) ) {
                    update_post_meta( $post_id, $meta_key, floatval( $_POST[$field] ) );
                } elseif ( in_array( $field, array( 'quartos_imovel', 'banheiros_imovel', 'garagens_imovel', 'ano_construcao_imovel' ) ) ) {
                    update_post_meta( $post_id, $meta_key, intval( $_POST[$field] ) );
                } else {
                    update_post_meta( $post_id, $meta_key, sanitize_text_field( $_POST[$field] ) );
                }
            }
        }
    }
    
    /**
     * Enfileira os arquivos CSS e JS do plugin
     */
    public function enfileirar_assets() {
        wp_enqueue_style(
            'imoveis-sp-css',
            plugin_dir_url( __FILE__ ) . 'css/imoveis-sp.css',
            array(),
            '2.0'
        );
        
        wp_enqueue_script(
            'imoveis-sp-js',
            plugin_dir_url( __FILE__ ) . 'js/imoveis-sp.js',
            array('jquery'),
            '2.0',
            true
        );
    }
    
    /**
     * Shortcode [listar_imoveis] – Lista os imóveis (parâmetro: quantidade)
     */
    public function shortcode_listar_imoveis( $atts ) {
        $atts = shortcode_atts( array(
            'quantidade' => 10,
        ), $atts, 'listar_imoveis' );
        
        ob_start();
        
        $args = array(
            'post_type'      => 'imovel',
            'posts_per_page' => $atts['quantidade'],
        );
        
        $query = new WP_Query( $args );
        
        if ( $query->have_posts() ) {
            echo '<div class="imoveis-listagem">';
            while ( $query->have_posts() ) {
                $query->the_post();
                $this->render_imovel_item();
            }
            echo '</div>';
        } else {
            echo '<p>' . __( 'Nenhum imóvel encontrado.', 'imoveis-sp' ) . '</p>';
        }
        wp_reset_postdata();
        
        return ob_get_clean();
    }
    
    /**
     * Shortcode [pesquisar_imoveis] – Exibe formulário e resultados de busca avançada
     */
    public function shortcode_pesquisar_imoveis( $atts ) {
        ob_start();
        ?>
        <form method="GET" action="" class="imoveis-pesquisa-form">
            <div class="form-group">
                <label for="pesquisa_bairro"><?php _e( 'Bairro:', 'imoveis-sp' ); ?></label>
                <input type="text" id="pesquisa_bairro" name="pesquisa_bairro" value="<?php echo isset($_GET['pesquisa_bairro']) ? esc_attr($_GET['pesquisa_bairro']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="pesquisa_cidade"><?php _e( 'Cidade:', 'imoveis-sp' ); ?></label>
                <input type="text" id="pesquisa_cidade" name="pesquisa_cidade" value="<?php echo isset($_GET['pesquisa_cidade']) ? esc_attr($_GET['pesquisa_cidade']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="pesquisa_tipo"><?php _e( 'Tipo:', 'imoveis-sp' ); ?></label>
                <select id="pesquisa_tipo" name="pesquisa_tipo">
                    <option value=""><?php _e( 'Selecione', 'imoveis-sp' ); ?></option>
                    <option value="casa" <?php selected( isset($_GET['pesquisa_tipo']) ? $_GET['pesquisa_tipo'] : '', 'casa' ); ?>><?php _e( 'Casa', 'imoveis-sp' ); ?></option>
                    <option value="apartamento" <?php selected( isset($_GET['pesquisa_tipo']) ? $_GET['pesquisa_tipo'] : '', 'apartamento' ); ?>><?php _e( 'Apartamento', 'imoveis-sp' ); ?></option>
                    <option value="comercial" <?php selected( isset($_GET['pesquisa_tipo']) ? $_GET['pesquisa_tipo'] : '', 'comercial' ); ?>><?php _e( 'Comercial', 'imoveis-sp' ); ?></option>
                    <option value="terreno" <?php selected( isset($_GET['pesquisa_tipo']) ? $_GET['pesquisa_tipo'] : '', 'terreno' ); ?>><?php _e( 'Terreno', 'imoveis-sp' ); ?></option>
                </select>
            </div>
            <div class="form-group">
                <label for="pesquisa_quartos"><?php _e( 'Quartos Mínimos:', 'imoveis-sp' ); ?></label>
                <input type="number" id="pesquisa_quartos" name="pesquisa_quartos" min="0" value="<?php echo isset($_GET['pesquisa_quartos']) ? intval($_GET['pesquisa_quartos']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="pesquisa_preco_min"><?php _e( 'Preço Mínimo:', 'imoveis-sp' ); ?></label>
                <input type="number" id="pesquisa_preco_min" name="pesquisa_preco_min" min="0" step="0.01" value="<?php echo isset($_GET['pesquisa_preco_min']) ? floatval($_GET['pesquisa_preco_min']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="pesquisa_preco_max"><?php _e( 'Preço Máximo:', 'imoveis-sp' ); ?></label>
                <input type="number" id="pesquisa_preco_max" name="pesquisa_preco_max" min="0" step="0.01" value="<?php echo isset($_GET['pesquisa_preco_max']) ? floatval($_GET['pesquisa_preco_max']) : ''; ?>">
            </div>
            <button type="submit" class="btn-imoveis"><?php _e( 'Buscar', 'imoveis-sp' ); ?></button>
        </form>
        <?php
        
        if ( isset($_GET['pesquisa_bairro']) || isset($_GET['pesquisa_cidade']) || isset($_GET['pesquisa_tipo']) || isset($_GET['pesquisa_quartos']) || isset($_GET['pesquisa_preco_min']) || isset($_GET['pesquisa_preco_max']) ) {
            $meta_query = array('relation' => 'AND');
            
            if ( ! empty( $_GET['pesquisa_bairro'] ) ) {
                $meta_query[] = array(
                    'key'     => '_bairro_imovel',
                    'value'   => sanitize_text_field( $_GET['pesquisa_bairro'] ),
                    'compare' => 'LIKE'
                );
            }
            
            if ( ! empty( $_GET['pesquisa_cidade'] ) ) {
                $meta_query[] = array(
                    'key'     => '_cidade_imovel',
                    'value'   => sanitize_text_field( $_GET['pesquisa_cidade'] ),
                    'compare' => 'LIKE'
                );
            }
            
            if ( ! empty( $_GET['pesquisa_tipo'] ) ) {
                $meta_query[] = array(
                    'key'     => '_tipo_imovel',
                    'value'   => sanitize_text_field( $_GET['pesquisa_tipo'] ),
                    'compare' => '='
                );
            }
            
            if ( isset($_GET['pesquisa_quartos']) && intval($_GET['pesquisa_quartos']) > 0 ) {
                $meta_query[] = array(
                    'key'     => '_quartos_imovel',
                    'value'   => intval($_GET['pesquisa_quartos']),
                    'type'    => 'NUMERIC',
                    'compare' => '>='
                );
            }
            
            if ( isset($_GET['pesquisa_preco_min']) && floatval($_GET['pesquisa_preco_min']) > 0 ) {
                $meta_query[] = array(
                    'key'     => '_preco_imovel',
                    'value'   => floatval($_GET['pesquisa_preco_min']),
                    'type'    => 'NUMERIC',
                    'compare' => '>='
                );
            }
            
            if ( isset($_GET['pesquisa_preco_max']) && floatval($_GET['pesquisa_preco_max']) > 0 ) {
                $meta_query[] = array(
                    'key'     => '_preco_imovel',
                    'value'   => floatval($_GET['pesquisa_preco_max']),
                    'type'    => 'NUMERIC',
                    'compare' => '<='
                );
            }
            
            $args = array(
                'post_type'      => 'imovel',
                'posts_per_page' => -1,
                'meta_query'     => $meta_query,
            );
            
            $query = new WP_Query( $args );
            echo '<div class="imoveis-listagem">';
            if ( $query->have_posts() ) {
                while ( $query->have_posts() ) {
                    $query->the_post();
                    $this->render_imovel_item();
                }
            } else {
                echo '<p>' . __( 'Nenhum imóvel encontrado para os critérios informados.', 'imoveis-sp' ) . '</p>';
            }
            echo '</div>';
            wp_reset_postdata();
        }
        
        return ob_get_clean();
    }
    
    /**
     * Shortcode [imoveis_custom] – Cria uma página customizada de imóveis (parâmetros: local, tipo, titulo)
     */
    public function shortcode_imoveis_custom( $atts ) {
        $atts = shortcode_atts( array(
            'local' => '',
            'tipo'  => '',
            'titulo'=> __( 'Imóveis', 'imoveis-sp' ),
        ), $atts, 'imoveis_custom' );
        
        ob_start();
        
        echo '<h2 class="imoveis-custom-title">' . esc_html( $atts['titulo'] ) . '</h2>';
        
        $meta_query = array();
        
        if ( ! empty( $atts['local'] ) ) {
            $meta_query[] = array(
                'key'     => '_bairro_imovel',
                'value'   => sanitize_text_field( $atts['local'] ),
                'compare' => 'LIKE'
            );
        }
        
        if ( ! empty( $atts['tipo'] ) ) {
            $meta_query[] = array(
                'key'     => '_tipo_imovel',
                'value'   => sanitize_text_field( $atts['tipo'] ),
                'compare' => '='
            );
        }
        
        $args = array(
            'post_type'      => 'imovel',
            'posts_per_page' => -1,
        );
        if ( ! empty( $meta_query ) ) {
            $args['meta_query'] = $meta_query;
        }
        
        $query = new WP_Query( $args );
        if ( $query->have_posts() ) {
            echo '<div class="imoveis-listagem imoveis-custom-list">';
            while ( $query->have_posts() ) {
                $query->the_post();
                $this->render_imovel_item();
            }
            echo '</div>';
        } else {
            echo '<p>' . __( 'Nenhum imóvel encontrado para estes critérios.', 'imoveis-sp' ) . '</p>';
        }
        wp_reset_postdata();
        
        return ob_get_clean();
    }
    
    /**
     * Renderiza o layout de cada item de imóvel
     */
    private function render_imovel_item() {
        $endereco       = get_post_meta( get_the_ID(), '_endereco_imovel', true );
        $bairro         = get_post_meta( get_the_ID(), '_bairro_imovel', true );
        $cidade         = get_post_meta( get_the_ID(), '_cidade_imovel', true );
        $preco          = get_post_meta( get_the_ID(), '_preco_imovel', true );
        $descricao      = get_post_meta( get_the_ID(), '_descricao_imovel', true );
        $tipo           = get_post_meta( get_the_ID(), '_tipo_imovel', true );
        $quartos        = get_post_meta( get_the_ID(), '_quartos_imovel', true );
        $banheiros      = get_post_meta( get_the_ID(), '_banheiros_imovel', true );
        $garagens       = get_post_meta( get_the_ID(), '_garagens_imovel', true );
        $area           = get_post_meta( get_the_ID(), '_area_imovel', true );
        $ano_construcao = get_post_meta( get_the_ID(), '_ano_construcao_imovel', true );
        $status         = get_post_meta( get_the_ID(), '_status_imovel', true );
        ?>
        <div class="imovel-item">
            <?php if ( has_post_thumbnail() ) : ?>
                <div class="imovel-thumb">
                    <?php the_post_thumbnail( 'medium' ); ?>
                </div>
            <?php endif; ?>
            <div class="imovel-dados">
                <h3 class="imovel-titulo"><?php the_title(); ?></h3>
                <p><strong><?php _e( 'Endereço:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $endereco ); ?></p>
                <p><strong><?php _e( 'Bairro:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $bairro ); ?></p>
                <p><strong><?php _e( 'Cidade:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $cidade ); ?></p>
                <p><strong><?php _e( 'Tipo:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( ucfirst($tipo) ); ?></p>
                <p><strong><?php _e( 'Quartos:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $quartos ); ?></p>
                <p><strong><?php _e( 'Banheiros:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $banheiros ); ?></p>
                <p><strong><?php _e( 'Garagem:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $garagens ); ?></p>
                <p><strong><?php _e( 'Área:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $area ); ?> m²</p>
                <p><strong><?php _e( 'Ano de Construção:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $ano_construcao ); ?></p>
                <p><strong><?php _e( 'Preço:', 'imoveis-sp' ); ?></strong> R$ <?php echo esc_html( $preco ); ?></p>
                <p class="imovel-descricao"><?php echo esc_html( $descricao ); ?></p>
                <a href="<?php the_permalink(); ?>" class="btn-imoveis"><?php _e( 'Ver detalhes', 'imoveis-sp' ); ?></a>
            </div>
        </div>
        <?php
    }
}

new ImoveisSPPremium();
