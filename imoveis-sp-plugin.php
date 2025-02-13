<?php
/**
 * Plugin Name: Imóveis São Paulo
 * Plugin URI:  https://seudominio.com
 * Description: Plugin de exemplo para cadastro e listagem de imóveis na cidade de São Paulo (Versão 2.0 com melhorias).
 * Version:     2.0
 * Author:      Seu Nome
 * Author URI:  https://seudominio.com
 * License:     GPL2
 * Text Domain: imoveis-sp
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Segurança
}

class ImoveisSPPlugin {

    public function __construct() {
        // Versão 1 (original)
        add_action( 'init', array( $this, 'registrar_cpt_imoveis' ) );
        add_action( 'add_meta_boxes', array( $this, 'registrar_metaboxes' ) );
        add_action( 'save_post', array( $this, 'salvar_dados_imovel' ) );

        // Shortcodes versão 1
        add_shortcode( 'listar_imoveis', array( $this, 'shortcode_listar_imoveis' ) );
        add_shortcode( 'pesquisar_imoveis', array( $this, 'shortcode_pesquisar_imoveis' ) );

        // Versão 2 (melhorias e novos recursos)
        add_action( 'wp_enqueue_scripts', array( $this, 'adicionar_estilos_e_scripts_frontend' ) );

        // Novos shortcodes versão 2
        add_shortcode( 'listar_imoveis_v2', array( $this, 'shortcode_listar_imoveis_v2' ) );
        add_shortcode( 'pesquisar_imoveis_v2', array( $this, 'shortcode_pesquisar_imoveis_v2' ) );
        add_shortcode( 'imoveis_custom_page', array( $this, 'shortcode_imoveis_custom_page' ) );
    }

    /**
     * =====================================================
     * =============== PARTE 1 (Versão 1) ==================
     * =====================================================
     * Abaixo está todo o código legado da versão 1,
     * mantendo compatibilidade e funcionamento.
     */

    /**
     * Registra o CPT de Imóveis (versão 1)
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
     * Registra metaboxes (versão 1)
     */
    public function registrar_metaboxes() {
        add_meta_box(
            'dados_imovel',
            __( 'Dados do Imóvel (v1 + novos campos v2)', 'imoveis-sp' ),
            array( $this, 'metabox_dados_imovel_callback' ),
            'imovel',
            'normal',
            'default'
        );
    }

    /**
     * Callback do metabox (versão 1 + novos campos v2)
     */
    public function metabox_dados_imovel_callback( $post ) {
        // Nonce para segurança
        wp_nonce_field( 'salvar_dados_imovel', 'dados_imovel_nonce' );

        // Campos versão 1 (básicos)
        $endereco  = get_post_meta( $post->ID, '_endereco_imovel', true );
        $bairro    = get_post_meta( $post->ID, '_bairro_imovel', true );
        $cidade    = get_post_meta( $post->ID, '_cidade_imovel', true );
        $preco     = get_post_meta( $post->ID, '_preco_imovel', true );
        $descricao = get_post_meta( $post->ID, '_descricao_imovel', true );

        // Novos campos (versão 2)
        $area     = get_post_meta( $post->ID, '_area_imovel', true );
        $quartos  = get_post_meta( $post->ID, '_quartos_imovel', true );
        $banheiros= get_post_meta( $post->ID, '_banheiros_imovel', true );
        $suites   = get_post_meta( $post->ID, '_suites_imovel', true );
        $vagas    = get_post_meta( $post->ID, '_vagas_imovel', true );
        $tipo     = get_post_meta( $post->ID, '_tipo_imovel', true ); // Ex: Apartamento, Casa, etc.
        ?>
        <!-- Campos versão 1 -->
        <p>
            <label for="endereco_imovel"><?php _e( 'Endereço:', 'imoveis-sp' ); ?></label><br>
            <input type="text" name="endereco_imovel" id="endereco_imovel" value="<?php echo esc_attr( $endereco ); ?>" style="width:100%;" />
        </p>
        <p>
            <label for="bairro_imovel"><?php _e( 'Bairro:', 'imoveis-sp' ); ?></label><br>
            <input type="text" name="bairro_imovel" id="bairro_imovel" value="<?php echo esc_attr( $bairro ); ?>" style="width:100%;" />
        </p>
        <p>
            <label for="cidade_imovel"><?php _e( 'Cidade:', 'imoveis-sp' ); ?></label><br>
            <input type="text" name="cidade_imovel" id="cidade_imovel" value="<?php echo esc_attr( $cidade ); ?>" style="width:100%;" />
        </p>
        <p>
            <label for="preco_imovel"><?php _e( 'Preço (R$):', 'imoveis-sp' ); ?></label><br>
            <input type="number" name="preco_imovel" id="preco_imovel" value="<?php echo esc_attr( $preco ); ?>" style="width:100%;" />
        </p>
        <p>
            <label for="descricao_imovel"><?php _e( 'Descrição:', 'imoveis-sp' ); ?></label><br>
            <textarea name="descricao_imovel" id="descricao_imovel" rows="4" style="width:100%;"><?php echo esc_textarea( $descricao ); ?></textarea>
        </p>

        <hr>
        <h3><?php _e( 'Dados adicionais (Versão 2)', 'imoveis-sp' ); ?></h3>

        <!-- Campos versão 2 -->
        <p>
            <label for="area_imovel"><?php _e( 'Área (m²):', 'imoveis-sp' ); ?></label><br>
            <input type="number" name="area_imovel" id="area_imovel" value="<?php echo esc_attr( $area ); ?>" style="width:100%;" />
        </p>
        <p>
            <label for="quartos_imovel"><?php _e( 'Quartos:', 'imoveis-sp' ); ?></label><br>
            <input type="number" name="quartos_imovel" id="quartos_imovel" value="<?php echo esc_attr( $quartos ); ?>" style="width:100%;" />
        </p>
        <p>
            <label for="banheiros_imovel"><?php _e( 'Banheiros:', 'imoveis-sp' ); ?></label><br>
            <input type="number" name="banheiros_imovel" id="banheiros_imovel" value="<?php echo esc_attr( $banheiros ); ?>" style="width:100%;" />
        </p>
        <p>
            <label for="suites_imovel"><?php _e( 'Suítes:', 'imoveis-sp' ); ?></label><br>
            <input type="number" name="suites_imovel" id="suites_imovel" value="<?php echo esc_attr( $suites ); ?>" style="width:100%;" />
        </p>
        <p>
            <label for="vagas_imovel"><?php _e( 'Vagas de garagem:', 'imoveis-sp' ); ?></label><br>
            <input type="number" name="vagas_imovel" id="vagas_imovel" value="<?php echo esc_attr( $vagas ); ?>" style="width:100%;" />
        </p>
        <p>
            <label for="tipo_imovel"><?php _e( 'Tipo do Imóvel:', 'imoveis-sp' ); ?></label><br>
            <input type="text" name="tipo_imovel" id="tipo_imovel" value="<?php echo esc_attr( $tipo ); ?>" placeholder="<?php _e('Ex: Apartamento, Casa, Comercial...', 'imoveis-sp'); ?>" style="width:100%;" />
        </p>
        <?php
    }

    /**
     * Salva os dados do imóvel (versão 1 e 2)
     */
    public function salvar_dados_imovel( $post_id ) {
        if ( ! isset( $_POST['dados_imovel_nonce'] ) ) {
            return;
        }
        if ( ! wp_verify_nonce( $_POST['dados_imovel_nonce'], 'salvar_dados_imovel' ) ) {
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

        // Salva campos versão 1
        if ( isset( $_POST['endereco_imovel'] ) ) {
            update_post_meta( $post_id, '_endereco_imovel', sanitize_text_field( $_POST['endereco_imovel'] ) );
        }
        if ( isset( $_POST['bairro_imovel'] ) ) {
            update_post_meta( $post_id, '_bairro_imovel', sanitize_text_field( $_POST['bairro_imovel'] ) );
        }
        if ( isset( $_POST['cidade_imovel'] ) ) {
            update_post_meta( $post_id, '_cidade_imovel', sanitize_text_field( $_POST['cidade_imovel'] ) );
        }
        if ( isset( $_POST['preco_imovel'] ) ) {
            update_post_meta( $post_id, '_preco_imovel', floatval( $_POST['preco_imovel'] ) );
        }
        if ( isset( $_POST['descricao_imovel'] ) ) {
            update_post_meta( $post_id, '_descricao_imovel', sanitize_textarea_field( $_POST['descricao_imovel'] ) );
        }

        // Salva novos campos versão 2
        if ( isset( $_POST['area_imovel'] ) ) {
            update_post_meta( $post_id, '_area_imovel', floatval( $_POST['area_imovel'] ) );
        }
        if ( isset( $_POST['quartos_imovel'] ) ) {
            update_post_meta( $post_id, '_quartos_imovel', intval( $_POST['quartos_imovel'] ) );
        }
        if ( isset( $_POST['banheiros_imovel'] ) ) {
            update_post_meta( $post_id, '_banheiros_imovel', intval( $_POST['banheiros_imovel'] ) );
        }
        if ( isset( $_POST['suites_imovel'] ) ) {
            update_post_meta( $post_id, '_suites_imovel', intval( $_POST['suites_imovel'] ) );
        }
        if ( isset( $_POST['vagas_imovel'] ) ) {
            update_post_meta( $post_id, '_vagas_imovel', intval( $_POST['vagas_imovel'] ) );
        }
        if ( isset( $_POST['tipo_imovel'] ) ) {
            update_post_meta( $post_id, '_tipo_imovel', sanitize_text_field( $_POST['tipo_imovel'] ) );
        }
    }

    /**
     * Shortcode versão 1: [listar_imoveis]
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

        if ( $query->have_posts() ) : ?>
            <div class="imoveis-listagem">
                <?php
                while ( $query->have_posts() ) : $query->the_post();
                    $endereco  = get_post_meta( get_the_ID(), '_endereco_imovel', true );
                    $bairro    = get_post_meta( get_the_ID(), '_bairro_imovel', true );
                    $cidade    = get_post_meta( get_the_ID(), '_cidade_imovel', true );
                    $preco     = get_post_meta( get_the_ID(), '_preco_imovel', true );
                    $descricao = get_post_meta( get_the_ID(), '_descricao_imovel', true );
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
                            <p><strong><?php _e( 'Preço:', 'imoveis-sp' ); ?></strong> R$ <?php echo esc_html( $preco ); ?></p>
                            <p><strong><?php _e( 'Descrição:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $descricao ); ?></p>
                            <a href="<?php the_permalink(); ?>" class="imovel-link"><?php _e( 'Ver detalhes', 'imoveis-sp' ); ?></a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php
        else:
            echo '<p>' . __( 'Nenhum imóvel encontrado.', 'imoveis-sp' ) . '</p>';
        endif;

        wp_reset_postdata();

        return ob_get_clean();
    }

    /**
     * Shortcode versão 1: [pesquisar_imoveis]
     */
    public function shortcode_pesquisar_imoveis( $atts ) {
        ob_start();
        ?>
        <form method="GET" action="">
            <label for="pesquisa_bairro"><?php _e( 'Bairro:', 'imoveis-sp' ); ?></label>
            <input type="text" id="pesquisa_bairro" name="pesquisa_bairro" value="<?php echo isset($_GET['pesquisa_bairro']) ? esc_attr($_GET['pesquisa_bairro']) : ''; ?>">

            <label for="pesquisa_cidade"><?php _e( 'Cidade:', 'imoveis-sp' ); ?></label>
            <input type="text" id="pesquisa_cidade" name="pesquisa_cidade" value="<?php echo isset($_GET['pesquisa_cidade']) ? esc_attr($_GET['pesquisa_cidade']) : ''; ?>">

            <button type="submit"><?php _e( 'Buscar', 'imoveis-sp' ); ?></button>
        </form>
        <?php

        if ( isset($_GET['pesquisa_bairro']) || isset($_GET['pesquisa_cidade']) ) {
            $bairro_buscado = isset($_GET['pesquisa_bairro']) ? sanitize_text_field($_GET['pesquisa_bairro']) : '';
            $cidade_buscada = isset($_GET['pesquisa_cidade']) ? sanitize_text_field($_GET['pesquisa_cidade']) : '';

            $meta_query = array('relation' => 'AND');

            if ( ! empty($bairro_buscado) ) {
                $meta_query[] = array(
                    'key'     => '_bairro_imovel',
                    'value'   => $bairro_buscado,
                    'compare' => 'LIKE'
                );
            }
            if ( ! empty($cidade_buscada) ) {
                $meta_query[] = array(
                    'key'     => '_cidade_imovel',
                    'value'   => $cidade_buscada,
                    'compare' => 'LIKE'
                );
            }

            $args = array(
                'post_type'      => 'imovel',
                'posts_per_page' => -1,
                'meta_query'     => $meta_query
            );

            $query = new WP_Query( $args );

            echo '<div class="imoveis-listagem">';
            if ( $query->have_posts() ) {
                while ( $query->have_posts() ) {
                    $query->the_post();
                    $endereco  = get_post_meta( get_the_ID(), '_endereco_imovel', true );
                    $bairro    = get_post_meta( get_the_ID(), '_bairro_imovel', true );
                    $cidade    = get_post_meta( get_the_ID(), '_cidade_imovel', true );
                    $preco     = get_post_meta( get_the_ID(), '_preco_imovel', true );
                    $descricao = get_post_meta( get_the_ID(), '_descricao_imovel', true );
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
                            <p><strong><?php _e( 'Preço:', 'imoveis-sp' ); ?></strong> R$ <?php echo esc_html( $preco ); ?></p>
                            <p><strong><?php _e( 'Descrição:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $descricao ); ?></p>
                            <a href="<?php the_permalink(); ?>" class="imovel-link"><?php _e( 'Ver detalhes', 'imoveis-sp' ); ?></a>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<p>' . __( 'Nenhum imóvel encontrado para esses critérios.', 'imoveis-sp' ) . '</p>';
            }
            echo '</div>';

            wp_reset_postdata();
        }

        return ob_get_clean();
    }

    /**
     * =====================================================
     * =============== PARTE 2 (Versão 2) ==================
     * =====================================================
     * Abaixo estão as novidades e melhorias.
     */

    /**
     * Carrega estilos e scripts da versão 2
     * Mantém imoveis-sp.css (versão 1) e adiciona imoveis-sp-v2.css e imoveis-sp-v2.js
     */
    public function adicionar_estilos_e_scripts_frontend() {
        // Mantemos o estilo antigo para não quebrar nada
        wp_enqueue_style(
            'imoveis-sp-css',
            plugin_dir_url(__FILE__) . 'css/imoveis-sp.css',
            array(),
            '1.0'
        );

        // Novo estilo mais moderno (versão 2)
        wp_enqueue_style(
            'imoveis-sp-v2-css',
            plugin_dir_url(__FILE__) . 'css/imoveis-sp-v2.css',
            array(),
            '2.0'
        );

        // Script de animações/efeitos (versão 2)
        wp_enqueue_script(
            'imoveis-sp-v2-js',
            plugin_dir_url(__FILE__) . 'js/imoveis-sp-v2.js',
            array('jquery'),
            '2.0',
            true
        );
    }

    /**
     * NOVO Shortcode v2: [listar_imoveis_v2]
     * - Listagem com design atualizado, mostrando novos campos (quartos, banheiros, etc).
     */
    public function shortcode_listar_imoveis_v2( $atts ) {
        $atts = shortcode_atts( array(
            'quantidade' => 6,
        ), $atts, 'listar_imoveis_v2' );

        ob_start();

        $args = array(
            'post_type'      => 'imovel',
            'posts_per_page' => $atts['quantidade'],
        );
        $query = new WP_Query( $args );

        if ( $query->have_posts() ) : ?>
            <div class="imoveis-listagem-v2">
                <?php
                while ( $query->have_posts() ) : $query->the_post();
                    $endereco  = get_post_meta( get_the_ID(), '_endereco_imovel', true );
                    $bairro    = get_post_meta( get_the_ID(), '_bairro_imovel', true );
                    $cidade    = get_post_meta( get_the_ID(), '_cidade_imovel', true );
                    $preco     = get_post_meta( get_the_ID(), '_preco_imovel', true );
                    $descricao = get_post_meta( get_the_ID(), '_descricao_imovel', true );

                    // Novos campos
                    $area      = get_post_meta( get_the_ID(), '_area_imovel', true );
                    $quartos   = get_post_meta( get_the_ID(), '_quartos_imovel', true );
                    $banheiros = get_post_meta( get_the_ID(), '_banheiros_imovel', true );
                    $suites    = get_post_meta( get_the_ID(), '_suites_imovel', true );
                    $vagas     = get_post_meta( get_the_ID(), '_vagas_imovel', true );
                    $tipo      = get_post_meta( get_the_ID(), '_tipo_imovel', true );
                    ?>
                    <div class="imovel-item-v2" data-aos="fade-up">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="imovel-thumb-v2">
                                <?php the_post_thumbnail( 'medium_large' ); ?>
                            </div>
                        <?php endif; ?>

                        <div class="imovel-dados-v2">
                            <h3 class="imovel-titulo-v2"><?php the_title(); ?></h3>
                            <p class="imovel-tipo-v2"><?php echo esc_html( $tipo ); ?></p>
                            <ul class="imovel-info-list-v2">
                                <li><strong><?php _e( 'Endereço:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $endereco ); ?></li>
                                <li><strong><?php _e( 'Bairro:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $bairro ); ?></li>
                                <li><strong><?php _e( 'Cidade:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $cidade ); ?></li>
                                <li><strong><?php _e( 'Área:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $area ); ?> m²</li>
                                <li><strong><?php _e( 'Quartos:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $quartos ); ?></li>
                                <li><strong><?php _e( 'Banheiros:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $banheiros ); ?></li>
                                <li><strong><?php _e( 'Suítes:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $suites ); ?></li>
                                <li><strong><?php _e( 'Vagas:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $vagas ); ?></li>
                                <li><strong><?php _e( 'Preço:', 'imoveis-sp' ); ?></strong> R$ <?php echo esc_html( $preco ); ?></li>
                            </ul>
                            <p class="imovel-desc-v2"><?php echo esc_html( $descricao ); ?></p>

                            <a href="<?php the_permalink(); ?>" class="imovel-link-v2"><?php _e( 'Ver detalhes', 'imoveis-sp' ); ?></a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php
        else:
            echo '<p>' . __( 'Nenhum imóvel encontrado.', 'imoveis-sp' ) . '</p>';
        endif;

        wp_reset_postdata();

        return ob_get_clean();
    }

    /**
     * NOVO Shortcode v2: [pesquisar_imoveis_v2]
     * - Pesquisa com mais filtros: bairro, cidade, faixa de preço, tipo.
     */
    public function shortcode_pesquisar_imoveis_v2( $atts ) {
        ob_start();
        ?>
        <form method="GET" action="" class="form-pesquisa-v2">
            <div class="form-group">
                <label for="pesquisa_bairro"><?php _e( 'Bairro:', 'imoveis-sp' ); ?></label>
                <input type="text" id="pesquisa_bairro" name="pesquisa_bairro" value="<?php echo isset($_GET['pesquisa_bairro']) ? esc_attr($_GET['pesquisa_bairro']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="pesquisa_cidade"><?php _e( 'Cidade:', 'imoveis-sp' ); ?></label>
                <input type="text" id="pesquisa_cidade" name="pesquisa_cidade" value="<?php echo isset($_GET['pesquisa_cidade']) ? esc_attr($_GET['pesquisa_cidade']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="preco_min"><?php _e( 'Preço mín.:', 'imoveis-sp' ); ?></label>
                <input type="number" step="0.01" id="preco_min" name="preco_min" value="<?php echo isset($_GET['preco_min']) ? esc_attr($_GET['preco_min']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="preco_max"><?php _e( 'Preço máx.:', 'imoveis-sp' ); ?></label>
                <input type="number" step="0.01" id="preco_max" name="preco_max" value="<?php echo isset($_GET['preco_max']) ? esc_attr($_GET['preco_max']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="tipo_imovel_busca"><?php _e( 'Tipo do Imóvel:', 'imoveis-sp' ); ?></label>
                <input type="text" id="tipo_imovel_busca" name="tipo_imovel_busca" placeholder="<?php _e('Ex: Apartamento, Casa...', 'imoveis-sp'); ?>" value="<?php echo isset($_GET['tipo_imovel_busca']) ? esc_attr($_GET['tipo_imovel_busca']) : ''; ?>">
            </div>

            <div class="form-group">
                <button type="submit" class="btn-pesquisa-v2"><?php _e( 'Buscar', 'imoveis-sp' ); ?></button>
            </div>
        </form>
        <?php

        // Verifica pesquisa
        $has_search = (
            isset($_GET['pesquisa_bairro']) ||
            isset($_GET['pesquisa_cidade']) ||
            isset($_GET['preco_min']) ||
            isset($_GET['preco_max']) ||
            isset($_GET['tipo_imovel_busca'])
        );

        if ( $has_search ) {
            $bairro_buscado  = isset($_GET['pesquisa_bairro']) ? sanitize_text_field($_GET['pesquisa_bairro']) : '';
            $cidade_buscada  = isset($_GET['pesquisa_cidade']) ? sanitize_text_field($_GET['pesquisa_cidade']) : '';
            $preco_min       = isset($_GET['preco_min']) ? floatval($_GET['preco_min']) : '';
            $preco_max       = isset($_GET['preco_max']) ? floatval($_GET['preco_max']) : '';
            $tipo_buscado    = isset($_GET['tipo_imovel_busca']) ? sanitize_text_field($_GET['tipo_imovel_busca']) : '';

            $meta_query = array('relation' => 'AND');

            if ( ! empty($bairro_buscado) ) {
                $meta_query[] = array(
                    'key'     => '_bairro_imovel',
                    'value'   => $bairro_buscado,
                    'compare' => 'LIKE'
                );
            }
            if ( ! empty($cidade_buscada) ) {
                $meta_query[] = array(
                    'key'     => '_cidade_imovel',
                    'value'   => $cidade_buscada,
                    'compare' => 'LIKE'
                );
            }

            if ( ! empty($tipo_buscado) ) {
                $meta_query[] = array(
                    'key'     => '_tipo_imovel',
                    'value'   => $tipo_buscado,
                    'compare' => 'LIKE'
                );
            }

            // Faixa de preço
            if ( $preco_min !== '' && $preco_max !== '' && $preco_min <= $preco_max ) {
                $meta_query[] = array(
                    'key'     => '_preco_imovel',
                    'value'   => array( $preco_min, $preco_max ),
                    'compare' => 'BETWEEN',
                    'type'    => 'NUMERIC'
                );
            } elseif ( $preco_min !== '' ) {
                $meta_query[] = array(
                    'key'     => '_preco_imovel',
                    'value'   => $preco_min,
                    'compare' => '>=',
                    'type'    => 'NUMERIC'
                );
            } elseif ( $preco_max !== '' ) {
                $meta_query[] = array(
                    'key'     => '_preco_imovel',
                    'value'   => $preco_max,
                    'compare' => '<=',
                    'type'    => 'NUMERIC'
                );
            }

            $args = array(
                'post_type'      => 'imovel',
                'posts_per_page' => -1,
                'meta_query'     => $meta_query
            );

            $query = new WP_Query( $args );
            ?>

            <div class="imoveis-listagem-v2">
            <?php
            if ( $query->have_posts() ) {
                while ( $query->have_posts() ) {
                    $query->the_post();
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
                    ?>
                    <div class="imovel-item-v2" data-aos="fade-up">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="imovel-thumb-v2">
                                <?php the_post_thumbnail( 'medium_large' ); ?>
                            </div>
                        <?php endif; ?>

                        <div class="imovel-dados-v2">
                            <h3 class="imovel-titulo-v2"><?php the_title(); ?></h3>
                            <p class="imovel-tipo-v2"><?php echo esc_html( $tipo ); ?></p>
                            <ul class="imovel-info-list-v2">
                                <li><strong><?php _e( 'Endereço:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $endereco ); ?></li>
                                <li><strong><?php _e( 'Bairro:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $bairro ); ?></li>
                                <li><strong><?php _e( 'Cidade:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $cidade ); ?></li>
                                <li><strong><?php _e( 'Área:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $area ); ?> m²</li>
                                <li><strong><?php _e( 'Quartos:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $quartos ); ?></li>
                                <li><strong><?php _e( 'Banheiros:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $banheiros ); ?></li>
                                <li><strong><?php _e( 'Suítes:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $suites ); ?></li>
                                <li><strong><?php _e( 'Vagas:', 'imoveis-sp' ); ?></strong> <?php echo esc_html( $vagas ); ?></li>
                                <li><strong><?php _e( 'Preço:', 'imoveis-sp' ); ?></strong> R$ <?php echo esc_html( $preco ); ?></li>
                            </ul>
                            <p class="imovel-desc-v2"><?php echo esc_html( $descricao ); ?></p>
                            <a href="<?php the_permalink(); ?>" class="imovel-link-v2"><?php _e( 'Ver detalhes', 'imoveis-sp' ); ?></a>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<p>' . __( 'Nenhum imóvel encontrado para esses critérios.', 'imoveis-sp' ) . '</p>';
            }
            ?>
            </div>
            <?php

            wp_reset_postdata();
        }

        return ob_get_clean();
    }

    /**
     * Exemplo de "página customizada" (versão 2) [imoveis_custom_page]
     * - Poderia ser usada para exibir algo ainda mais custom ou avançado.
     */
    public function shortcode_imoveis_custom_page( $atts ) {
        // Aqui você pode criar um layout diferente, ou mixar listagem + formulário de pesquisa
        // Para exemplo, vamos exibir a listagem V2 seguida de um formulário de pesquisa V2.
        ob_start();
        echo '<h2>' . __( 'Página Especial de Imóveis', 'imoveis-sp' ) . '</h2>';
        
        // Reutilizando a listagem V2
        echo do_shortcode('[listar_imoveis_v2 quantidade="4"]');

        // Reutilizando a pesquisa V2
        echo '<hr><h3>'. __( 'Pesquisar com mais filtros', 'imoveis-sp' ) .'</h3>';
        echo do_shortcode('[pesquisar_imoveis_v2]');

        return ob_get_clean();
    }
}

// Inicializa o plugin
new ImoveisSPPlugin();
