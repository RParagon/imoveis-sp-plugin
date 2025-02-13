<?php
/**
 * Plugin Name: Imóveis São Paulo (Avançado)
 * Plugin URI:  https://seudominio.com
 * Description: Plugin avançado para cadastro, listagem e busca de Imóveis em São Paulo, com design moderno e campos extras.
 * Version:     1.1
 * Author:      Seu Nome
 * Author URI:  https://seudominio.com
 * Text Domain: imoveis-sp
 * Domain Path: /languages
 *
 * ;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;
 *    ABAIXO estão cabeçalhos para integração com GitHub
 *    Updater ou similar, para autoatualização via GIT.
 * ;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;
 *
 * GitHub Plugin URI: SeuUsuarioGitHub/imoveis-sp-plugin
 * GitHub Branch: main
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Segurança: se acessar diretamente, sai.
}

class ImoveisSPPluginAvancado {

    public function __construct() {
        // Inicializa Custom Post Type
        add_action( 'init', array( $this, 'registrar_cpt_imoveis' ) );

        // Metaboxes (dados do imóvel)
        add_action( 'add_meta_boxes', array( $this, 'registrar_metaboxes' ) );
        add_action( 'save_post', array( $this, 'salvar_dados_imovel' ) );

        // Shortcodes
        add_shortcode( 'listar_imoveis', array( $this, 'shortcode_listar_imoveis' ) );
        add_shortcode( 'pesquisar_imoveis_avancado', array( $this, 'shortcode_pesquisar_imoveis_avancado' ) );
        add_shortcode( 'imoveis_detalhe', array( $this, 'shortcode_imovel_detalhe' ) );
        add_shortcode( 'imoveis_cidade', array( $this, 'shortcode_imoveis_cidade' ) );
        add_shortcode( 'imoveis_bairro', array( $this, 'shortcode_imoveis_bairro' ) );

        // Estilos e scripts (front-end)
        add_action( 'wp_enqueue_scripts', array( $this, 'adicionar_estilos_scripts_frontend' ) );
    }

    /**
     * Registrar o Custom Post Type "Imóveis"
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

    /**
     * Registrar metaboxes (dados extras do imóvel)
     */
    public function registrar_metaboxes() {
        add_meta_box(
            'dados_imovel_avancado',
            __( 'Dados Avançados do Imóvel', 'imoveis-sp' ),
            array( $this, 'metabox_dados_imovel_callback' ),
            'imovel',
            'normal',
            'high'
        );
    }

    /**
     * Exibe o metabox com vários campos
     */
    public function metabox_dados_imovel_callback( $post ) {
        wp_nonce_field( 'salvar_dados_imovel', 'dados_imovel_nonce' );

        // Recuperar valores existentes
        $endereco    = get_post_meta( $post->ID, '_endereco_imovel', true );
        $bairro      = get_post_meta( $post->ID, '_bairro_imovel', true );
        $cidade      = get_post_meta( $post->ID, '_cidade_imovel', true );
        $preco       = get_post_meta( $post->ID, '_preco_imovel', true );
        $descricao   = get_post_meta( $post->ID, '_descricao_imovel', true );
        $quartos     = get_post_meta( $post->ID, '_quartos_imovel', true );
        $banheiros   = get_post_meta( $post->ID, '_banheiros_imovel', true );
        $garagem     = get_post_meta( $post->ID, '_garagem_imovel', true );
        $area        = get_post_meta( $post->ID, '_area_imovel', true );
        $anoConstr   = get_post_meta( $post->ID, '_ano_construcao_imovel', true );
        $destaque    = get_post_meta( $post->ID, '_destaque_imovel', true ); // Ex: checkbox se é destaque

        ?>
        <style>
            /* Estilização leve do metabox no Admin */
            .imovel-metabox label {
                font-weight: bold;
                margin-top: 10px;
                display: block;
            }
            .imovel-metabox input[type="text"],
            .imovel-metabox input[type="number"],
            .imovel-metabox textarea {
                width: 100%;
                margin-bottom: 10px;
            }
        </style>
        <div class="imovel-metabox">
            <label for="endereco_imovel"><?php _e( 'Endereço', 'imoveis-sp' ); ?></label>
            <input type="text" id="endereco_imovel" name="endereco_imovel" value="<?php echo esc_attr( $endereco ); ?>">

            <label for="bairro_imovel"><?php _e( 'Bairro', 'imoveis-sp' ); ?></label>
            <input type="text" id="bairro_imovel" name="bairro_imovel" value="<?php echo esc_attr( $bairro ); ?>">

            <label for="cidade_imovel"><?php _e( 'Cidade', 'imoveis-sp' ); ?></label>
            <input type="text" id="cidade_imovel" name="cidade_imovel" value="<?php echo esc_attr( $cidade ); ?>">

            <label for="preco_imovel"><?php _e( 'Preço (R$)', 'imoveis-sp' ); ?></label>
            <input type="number" id="preco_imovel" name="preco_imovel" value="<?php echo esc_attr( $preco ); ?>">

            <label for="descricao_imovel"><?php _e( 'Descrição', 'imoveis-sp' ); ?></label>
            <textarea id="descricao_imovel" name="descricao_imovel" rows="3"><?php echo esc_textarea( $descricao ); ?></textarea>

            <hr>

            <label for="quartos_imovel"><?php _e( 'Número de Quartos', 'imoveis-sp' ); ?></label>
            <input type="number" id="quartos_imovel" name="quartos_imovel" value="<?php echo esc_attr( $quartos ); ?>">

            <label for="banheiros_imovel"><?php _e( 'Número de Banheiros', 'imoveis-sp' ); ?></label>
            <input type="number" id="banheiros_imovel" name="banheiros_imovel" value="<?php echo esc_attr( $banheiros ); ?>">

            <label for="garagem_imovel"><?php _e( 'Vagas de Garagem', 'imoveis-sp' ); ?></label>
            <input type="number" id="garagem_imovel" name="garagem_imovel" value="<?php echo esc_attr( $garagem ); ?>">

            <label for="area_imovel"><?php _e( 'Área (m²)', 'imoveis-sp' ); ?></label>
            <input type="number" id="area_imovel" name="area_imovel" value="<?php echo esc_attr( $area ); ?>">

            <label for="ano_construcao_imovel"><?php _e( 'Ano de Construção', 'imoveis-sp' ); ?></label>
            <input type="number" id="ano_construcao_imovel" name="ano_construcao_imovel" value="<?php echo esc_attr( $anoConstr ); ?>">

            <p>
                <label for="destaque_imovel"><?php _e( 'Este imóvel é destaque?', 'imoveis-sp' ); ?></label><br>
                <input type="checkbox" id="destaque_imovel" name="destaque_imovel" value="1" <?php checked( $destaque, '1' ); ?>>
                <span><?php _e( 'Marque se quer destacar este imóvel.', 'imoveis-sp' ); ?></span>
            </p>
        </div>
        <?php
    }

    /**
     * Salvar dados do imóvel (metabox)
     */
    public function salvar_dados_imovel( $post_id ) {
        // Verifica nonce
        if ( ! isset( $_POST['dados_imovel_nonce'] ) ) {
            return;
        }
        if ( ! wp_verify_nonce( $_POST['dados_imovel_nonce'], 'salvar_dados_imovel' ) ) {
            return;
        }

        // Se for auto-save ou não tiver permissões, retorna
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( isset( $_POST['post_type'] ) && 'imovel' === $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }
        }

        // Salva cada campo (sanitizando)
        $campos = array(
            'endereco_imovel'         => '_endereco_imovel',
            'bairro_imovel'           => '_bairro_imovel',
            'cidade_imovel'           => '_cidade_imovel',
            'descricao_imovel'        => '_descricao_imovel',
            'quartos_imovel'          => '_quartos_imovel',
            'banheiros_imovel'        => '_banheiros_imovel',
            'garagem_imovel'          => '_garagem_imovel',
            'ano_construcao_imovel'   => '_ano_construcao_imovel'
        );
        foreach ( $campos as $campo_form => $campo_meta ) {
            if ( isset( $_POST[$campo_form] ) ) {
                update_post_meta( $post_id, $campo_meta, sanitize_text_field( $_POST[$campo_form] ) );
            }
        }

        // Campos numéricos que precisam de float ou int
        if ( isset( $_POST['preco_imovel'] ) ) {
            update_post_meta( $post_id, '_preco_imovel', floatval( $_POST['preco_imovel'] ) );
        }
        if ( isset( $_POST['area_imovel'] ) ) {
            update_post_meta( $post_id, '_area_imovel', floatval( $_POST['area_imovel'] ) );
        }

        // Checkbox destaque
        $destaque_value = isset( $_POST['destaque_imovel'] ) ? '1' : '0';
        update_post_meta( $post_id, '_destaque_imovel', $destaque_value );
    }

    /**
     * Shortcode [listar_imoveis]
     * Lista todos os imóveis em um grid moderno.
     */
    public function shortcode_listar_imoveis( $atts ) {
        $atts = shortcode_atts( array(
            'quantidade' => 9, // padrão
        ), $atts, 'listar_imoveis' );

        ob_start();

        $args = array(
            'post_type'      => 'imovel',
            'posts_per_page' => $atts['quantidade'],
        );
        $query = new WP_Query( $args );

        if ( $query->have_posts() ) : ?>
            <div class="imoveis-listagem imoveis-grid">
                <?php
                while ( $query->have_posts() ) : $query->the_post();
                    $this->get_imovel_card_html( get_the_ID() );
                endwhile;
                ?>
            </div>
        <?php
        else:
            echo '<p>' . __( 'Nenhum imóvel encontrado.', 'imoveis-sp' ) . '</p>';
        endif;

        wp_reset_postdata();

        return ob_get_clean();
    }

    /**
     * Shortcode [pesquisar_imoveis_avancado]
     * Formulário de busca por vários campos (bairro, cidade, preço máx, quartos etc.)
     */
    public function shortcode_pesquisar_imoveis_avancado( $atts ) {
        ob_start();
        ?>
        <form class="form-pesquisa-imoveis" method="GET">
            <div class="form-group">
                <label for="pesq_bairro"><?php _e( 'Bairro:', 'imoveis-sp' ); ?></label>
                <input type="text" id="pesq_bairro" name="pesq_bairro" value="<?php echo isset($_GET['pesq_bairro']) ? esc_attr($_GET['pesq_bairro']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="pesq_cidade"><?php _e( 'Cidade:', 'imoveis-sp' ); ?></label>
                <input type="text" id="pesq_cidade" name="pesq_cidade" value="<?php echo isset($_GET['pesq_cidade']) ? esc_attr($_GET['pesq_cidade']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="pesq_preco_max"><?php _e( 'Preço Máximo (R$):', 'imoveis-sp' ); ?></label>
                <input type="number" id="pesq_preco_max" name="pesq_preco_max" value="<?php echo isset($_GET['pesq_preco_max']) ? esc_attr($_GET['pesq_preco_max']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="pesq_quartos_min"><?php _e( 'Mínimo de Quartos:', 'imoveis-sp' ); ?></label>
                <input type="number" id="pesq_quartos_min" name="pesq_quartos_min" value="<?php echo isset($_GET['pesq_quartos_min']) ? esc_attr($_GET['pesq_quartos_min']) : ''; ?>">
            </div>

            <button type="submit" class="btn-pesquisar"><?php _e( 'Buscar', 'imoveis-sp' ); ?></button>
        </form>
        <?php

        // Exibe resultados de pesquisa se existirem parâmetros
        if ( isset($_GET['pesq_bairro']) || isset($_GET['pesq_cidade']) || isset($_GET['pesq_preco_max']) || isset($_GET['pesq_quartos_min']) ) {
            $bairro_buscado  = sanitize_text_field( $_GET['pesq_bairro'] ?? '' );
            $cidade_buscada  = sanitize_text_field( $_GET['pesq_cidade'] ?? '' );
            $preco_max       = floatval( $_GET['pesq_preco_max'] ?? 0 );
            $quartos_min     = intval( $_GET['pesq_quartos_min'] ?? 0 );

            $meta_query = array( 'relation' => 'AND' );

            if ( $bairro_buscado ) {
                $meta_query[] = array(
                    'key'     => '_bairro_imovel',
                    'value'   => $bairro_buscado,
                    'compare' => 'LIKE'
                );
            }
            if ( $cidade_buscada ) {
                $meta_query[] = array(
                    'key'     => '_cidade_imovel',
                    'value'   => $cidade_buscada,
                    'compare' => 'LIKE'
                );
            }
            if ( $preco_max > 0 ) {
                $meta_query[] = array(
                    'key'     => '_preco_imovel',
                    'value'   => $preco_max,
                    'type'    => 'numeric',
                    'compare' => '<='
                );
            }
            if ( $quartos_min > 0 ) {
                $meta_query[] = array(
                    'key'     => '_quartos_imovel',
                    'value'   => $quartos_min,
                    'type'    => 'numeric',
                    'compare' => '>='
                );
            }

            $args = array(
                'post_type'      => 'imovel',
                'posts_per_page' => -1,
                'meta_query'     => $meta_query
            );

            $resultado = new WP_Query( $args );

            echo '<div class="imoveis-listagem imoveis-grid">';
            if ( $resultado->have_posts() ) {
                while ( $resultado->have_posts() ) {
                    $resultado->the_post();
                    $this->get_imovel_card_html( get_the_ID() );
                }
            } else {
                echo '<p>' . __( 'Nenhum imóvel encontrado com os critérios informados.', 'imoveis-sp' ) . '</p>';
            }
            echo '</div>';

            wp_reset_postdata();
        }

        return ob_get_clean();
    }

    /**
     * Shortcode [imoveis_detalhe id="123"]
     * Exibe o detalhe completo de um imóvel específico
     */
    public function shortcode_imovel_detalhe( $atts ) {
        $atts = shortcode_atts( array(
            'id' => null,
        ), $atts, 'imoveis_detalhe' );

        if ( empty( $atts['id'] ) ) {
            return '<p>'.__( 'Nenhum ID informado.', 'imoveis-sp' ).'</p>';
        }

        $post_id = intval( $atts['id'] );
        $post = get_post( $post_id );

        if ( ! $post || $post->post_type !== 'imovel' ) {
            return '<p>'.__( 'Imóvel não encontrado.', 'imoveis-sp' ).'</p>';
        }

        ob_start();

        // Recupera metadados
        $endereco  = get_post_meta( $post_id, '_endereco_imovel', true );
        $bairro    = get_post_meta( $post_id, '_bairro_imovel', true );
        $cidade    = get_post_meta( $post_id, '_cidade_imovel', true );
        $preco     = get_post_meta( $post_id, '_preco_imovel', true );
        $descricao = get_post_meta( $post_id, '_descricao_imovel', true );
        $quartos   = get_post_meta( $post_id, '_quartos_imovel', true );
        $banheiros = get_post_meta( $post_id, '_banheiros_imovel', true );
        $garagem   = get_post_meta( $post_id, '_garagem_imovel', true );
        $area      = get_post_meta( $post_id, '_area_imovel', true );
        $anoConstr = get_post_meta( $post_id, '_ano_construcao_imovel', true );

        ?>
        <div class="imovel-detalhe-container">
            <?php if ( has_post_thumbnail( $post_id ) ) : ?>
                <div class="imovel-thumb-detalhe">
                    <?php echo get_the_post_thumbnail( $post_id, 'large' ); ?>
                </div>
            <?php endif; ?>

            <div class="imovel-detalhe-info">
                <h2><?php echo esc_html( $post->post_title ); ?></h2>
                <p><strong><?php _e('Endereço:', 'imoveis-sp'); ?></strong> <?php echo esc_html( $endereco ); ?></p>
                <p><strong><?php _e('Bairro:', 'imoveis-sp'); ?></strong> <?php echo esc_html( $bairro ); ?></p>
                <p><strong><?php _e('Cidade:', 'imoveis-sp'); ?></strong> <?php echo esc_html( $cidade ); ?></p>
                <p><strong><?php _e('Preço:', 'imoveis-sp'); ?></strong> R$ <?php echo number_format($preco, 2, ',', '.'); ?></p>
                <p><strong><?php _e('Quartos:', 'imoveis-sp'); ?></strong> <?php echo esc_html( $quartos ); ?></p>
                <p><strong><?php _e('Banheiros:', 'imoveis-sp'); ?></strong> <?php echo esc_html( $banheiros ); ?></p>
                <p><strong><?php _e('Vagas de Garagem:', 'imoveis-sp'); ?></strong> <?php echo esc_html( $garagem ); ?></p>
                <p><strong><?php _e('Área (m²):', 'imoveis-sp'); ?></strong> <?php echo esc_html( $area ); ?></p>
                <p><strong><?php _e('Ano de Construção:', 'imoveis-sp'); ?></strong> <?php echo esc_html( $anoConstr ); ?></p>
                <p><strong><?php _e('Descrição:', 'imoveis-sp'); ?></strong> <?php echo esc_html( $descricao ); ?></p>
            </div>
        </div>
        <?php

        return ob_get_clean();
    }

    /**
     * Shortcode [imoveis_cidade cidade="São Paulo"]
     * Lista imóveis filtrados pela cidade
     */
    public function shortcode_imoveis_cidade( $atts ) {
        $atts = shortcode_atts( array(
            'cidade' => 'São Paulo',
            'quantidade' => 6,
        ), $atts, 'imoveis_cidade' );

        ob_start();

        $args = array(
            'post_type'      => 'imovel',
            'posts_per_page' => $atts['quantidade'],
            'meta_query'     => array(
                array(
                    'key'     => '_cidade_imovel',
                    'value'   => $atts['cidade'],
                    'compare' => 'LIKE',
                ),
            )
        );
        $query = new WP_Query( $args );

        if ( $query->have_posts() ) {
            echo '<div class="imoveis-listagem imoveis-grid">';
            while ( $query->have_posts() ) {
                $query->the_post();
                $this->get_imovel_card_html( get_the_ID() );
            }
            echo '</div>';
        } else {
            echo '<p>' . __( 'Nenhum imóvel encontrado nessa cidade.', 'imoveis-sp' ) . '</p>';
        }

        wp_reset_postdata();

        return ob_get_clean();
    }

    /**
     * Shortcode [imoveis_bairro bairro="Pinheiros"]
     * Lista imóveis filtrados pelo bairro
     */
    public function shortcode_imoveis_bairro( $atts ) {
        $atts = shortcode_atts( array(
            'bairro' => 'Pinheiros',
            'quantidade' => 6,
        ), $atts, 'imoveis_bairro' );

        ob_start();

        $args = array(
            'post_type'      => 'imovel',
            'posts_per_page' => $atts['quantidade'],
            'meta_query'     => array(
                array(
                    'key'     => '_bairro_imovel',
                    'value'   => $atts['bairro'],
                    'compare' => 'LIKE',
                ),
            )
        );
        $query = new WP_Query( $args );

        if ( $query->have_posts() ) {
            echo '<div class="imoveis-listagem imoveis-grid">';
            while ( $query->have_posts() ) {
                $query->the_post();
                $this->get_imovel_card_html( get_the_ID() );
            }
            echo '</div>';
        } else {
            echo '<p>' . __( 'Nenhum imóvel encontrado nesse bairro.', 'imoveis-sp' ) . '</p>';
        }

        wp_reset_postdata();

        return ob_get_clean();
    }

    /**
     * Função auxiliar para renderizar o "card" de um imóvel (usada em vários lugares)
     */
    private function get_imovel_card_html( $imovel_id ) {
        $endereco  = get_post_meta( $imovel_id, '_endereco_imovel', true );
        $bairro    = get_post_meta( $imovel_id, '_bairro_imovel', true );
        $cidade    = get_post_meta( $imovel_id, '_cidade_imovel', true );
        $preco     = get_post_meta( $imovel_id, '_preco_imovel', true );
        $quartos   = get_post_meta( $imovel_id, '_quartos_imovel', true );
        $destaque  = get_post_meta( $imovel_id, '_destaque_imovel', true ) === '1';
        ?>
        <div class="imovel-item <?php echo $destaque ? 'imovel-destaque' : ''; ?>">
            <?php if ( has_post_thumbnail( $imovel_id ) ): ?>
                <div class="imovel-thumb">
                    <?php echo get_the_post_thumbnail( $imovel_id, 'medium' ); ?>
                </div>
            <?php endif; ?>
            <div class="imovel-dados">
                <h3 class="imovel-titulo">
                    <?php echo esc_html( get_the_title( $imovel_id ) ); ?>
                    <?php if ( $destaque ) : ?>
                        <span class="tag-destaque"><?php _e( 'Destaque', 'imoveis-sp' ); ?></span>
                    <?php endif; ?>
                </h3>
                <p><strong><?php _e( 'Endereço:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $endereco ); ?></p>
                <p><strong><?php _e( 'Bairro:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $bairro ); ?></p>
                <p><strong><?php _e( 'Cidade:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $cidade ); ?></p>
                <p><strong><?php _e( 'Preço:', 'imoveis-sp' ); ?></strong> R$ <?php echo number_format($preco, 2, ',', '.'); ?></p>
                <p><strong><?php _e( 'Quartos:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $quartos ); ?></p>
                <a href="<?php echo get_permalink( $imovel_id ); ?>" class="btn-ver-detalhes"><?php _e( 'Ver detalhes', 'imoveis-sp' ); ?></a>
            </div>
        </div>
        <?php
    }

    /**
     * Adiciona CSS e JS no front-end
     */
    public function adicionar_estilos_scripts_frontend() {
        if ( ! is_admin() ) {
            wp_enqueue_style(
                'imoveis-sp-advanced-css',
                plugin_dir_url(__FILE__) . 'css/imoveis-sp-advanced.css',
                array(),
                '1.1'
            );
            // Se precisar de JS para animações extras:
            // wp_enqueue_script(
            //     'imoveis-sp-advanced-js',
            //     plugin_dir_url(__FILE__) . 'js/imoveis-sp-advanced.js',
            //     array('jquery'),
            //     '1.1',
            //     true
            // );
        }
    }
}

// Inicializa
new ImoveisSPPluginAvancado();
