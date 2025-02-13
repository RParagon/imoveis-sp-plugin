<?php
/**
 * Plugin Name: Imóveis São Paulo
 * Plugin URI:  https://seudominio.com
 * Description: Plugin para cadastro e listagem de imóveis (Versão 3 completa com melhorias, página de detalhes customizada, autocomplete de endereço etc).
 * Version:     3.0
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

        // === Registro do CPT e Metaboxes (V1, V2 e V3) ===
        add_action( 'init', array( $this, 'registrar_cpt_imoveis' ) );
        add_action( 'add_meta_boxes', array( $this, 'registrar_metaboxes' ) );
        add_action( 'save_post', array( $this, 'salvar_dados_imovel' ) );

        // === Shortcodes V1 ===
        add_shortcode( 'listar_imoveis', array( $this, 'shortcode_listar_imoveis' ) );
        add_shortcode( 'pesquisar_imoveis', array( $this, 'shortcode_pesquisar_imoveis' ) );

        // === Shortcodes V2 ===
        add_shortcode( 'listar_imoveis_v2', array( $this, 'shortcode_listar_imoveis_v2' ) );
        add_shortcode( 'pesquisar_imoveis_v2', array( $this, 'shortcode_pesquisar_imoveis_v2' ) );
        add_shortcode( 'imoveis_custom_page', array( $this, 'shortcode_imoveis_custom_page' ) );

        // === Novidades V3 ===
        // 1) Carregar CSS/JS (incluindo Font Awesome e integração Google Places)
        add_action( 'wp_enqueue_scripts', array( $this, 'adicionar_estilos_e_scripts_v3' ) );

        // 2) Novo shortcode unindo pesquisa + listagem
        add_shortcode( 'catalogo_imoveis', array( $this, 'shortcode_catalogo_imoveis' ) );

        // 3) Forçar template single-imovel custom (se o tema não tiver um)
        add_filter( 'single_template', array( $this, 'forcar_template_single_imovel' ) );

        // 4) Admin Settings para Google Maps API Key
        add_action( 'admin_menu', array( $this, 'adicionar_pagina_config' ) );
        add_action( 'admin_init', array( $this, 'registrar_config' ) );
    }

    /**
     * =========================================================================
     * ==================== CPT: Registra "Imóvel" (V1, V2, V3) =================
     * =========================================================================
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
     * =========================================================================
     * ======================== Metaboxes (V1, V2, V3) =========================
     * =========================================================================
     */
    public function registrar_metaboxes() {
        add_meta_box(
            'dados_imovel',
            __( 'Dados do Imóvel (com Autocomplete V3)', 'imoveis-sp' ),
            array( $this, 'metabox_dados_imovel_callback' ),
            'imovel',
            'normal',
            'default'
        );
    }

    /**
     * Exibe campos no metabox (versão 1 + novos da V2 + melhorias V3)
     */
    public function metabox_dados_imovel_callback( $post ) {
        wp_nonce_field( 'salvar_dados_imovel', 'dados_imovel_nonce' );

        // Campos básicos (V1)
        $endereco  = get_post_meta( $post->ID, '_endereco_imovel', true );
        $bairro    = get_post_meta( $post->ID, '_bairro_imovel', true );
        $cidade    = get_post_meta( $post->ID, '_cidade_imovel', true );
        $preco     = get_post_meta( $post->ID, '_preco_imovel', true );
        $descricao = get_post_meta( $post->ID, '_descricao_imovel', true );

        // Campos V2
        $area      = get_post_meta( $post->ID, '_area_imovel', true );
        $quartos   = get_post_meta( $post->ID, '_quartos_imovel', true );
        $banheiros = get_post_meta( $post->ID, '_banheiros_imovel', true );
        $suites    = get_post_meta( $post->ID, '_suites_imovel', true );
        $vagas     = get_post_meta( $post->ID, '_vagas_imovel', true );
        $tipo      = get_post_meta( $post->ID, '_tipo_imovel', true );

        // Campos V3 (exemplo: latitude/longitude)
        $latitude  = get_post_meta( $post->ID, '_latitude_imovel', true );
        $longitude = get_post_meta( $post->ID, '_longitude_imovel', true );
        ?>
        <style>
        .metabox-imoveis-sp label {
            font-weight: bold;
        }
        </style>
        <div class="metabox-imoveis-sp">
            <p>
                <label for="endereco_imovel"><?php _e( 'Endereço (com autocomplete):', 'imoveis-sp' ); ?></label><br>
                <input type="text" 
                       name="endereco_imovel" 
                       id="endereco_imovel" 
                       class="google-places-autocomplete" 
                       value="<?php echo esc_attr( $endereco ); ?>" 
                       style="width:100%;" />
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
                <label for="latitude_imovel"><?php _e( 'Latitude:', 'imoveis-sp' ); ?></label><br>
                <input type="text" name="latitude_imovel" id="latitude_imovel" value="<?php echo esc_attr( $latitude ); ?>" style="width:48%;" />
                
                <label for="longitude_imovel" style="margin-left:2%;"><?php _e( 'Longitude:', 'imoveis-sp' ); ?></label><br>
                <input type="text" name="longitude_imovel" id="longitude_imovel" value="<?php echo esc_attr( $longitude ); ?>" style="width:48%;" />
            </p>
            <p>
                <label for="preco_imovel"><?php _e( 'Preço (R$):', 'imoveis-sp' ); ?></label><br>
                <input type="number" step="0.01" name="preco_imovel" id="preco_imovel" value="<?php echo esc_attr( $preco ); ?>" style="width:100%;" />
            </p>
            <p>
                <label for="descricao_imovel"><?php _e( 'Descrição:', 'imoveis-sp' ); ?></label><br>
                <textarea name="descricao_imovel" id="descricao_imovel" rows="4" style="width:100%;"><?php echo esc_textarea( $descricao ); ?></textarea>
            </p>

            <hr>
            <p><strong><?php _e( 'Dados Adicionais', 'imoveis-sp' ); ?>:</strong></p>
            <p>
                <label for="area_imovel"><?php _e( 'Área (m²):', 'imoveis-sp' ); ?></label><br>
                <input type="number" step="0.01" name="area_imovel" id="area_imovel" value="<?php echo esc_attr( $area ); ?>" style="width:100%;" />
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
                <input type="text" name="tipo_imovel" id="tipo_imovel" value="<?php echo esc_attr( $tipo ); ?>" placeholder="<?php _e('Ex: Apartamento, Casa...', 'imoveis-sp'); ?>" style="width:100%;" />
            </p>
        </div>
        <?php
    }

    /**
     * Salvando dados do imóvel (V1 + V2 + V3)
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

        // Mapeia campos do formulário => chaves do meta
        $campos = array(
            'endereco_imovel'  => '_endereco_imovel',
            'bairro_imovel'    => '_bairro_imovel',
            'cidade_imovel'    => '_cidade_imovel',
            'preco_imovel'     => '_preco_imovel',
            'descricao_imovel' => '_descricao_imovel',
            'area_imovel'      => '_area_imovel',
            'quartos_imovel'   => '_quartos_imovel',
            'banheiros_imovel' => '_banheiros_imovel',
            'suites_imovel'    => '_suites_imovel',
            'vagas_imovel'     => '_vagas_imovel',
            'tipo_imovel'      => '_tipo_imovel',
            'latitude_imovel'  => '_latitude_imovel',
            'longitude_imovel' => '_longitude_imovel',
        );

        foreach ( $campos as $campo_form => $meta_key ) {
            if ( isset( $_POST[ $campo_form ] ) ) {
                update_post_meta( $post_id, $meta_key, sanitize_text_field( $_POST[ $campo_form ] ) );
            }
        }
    }

    /**
     * =========================================================================
     * =========================== SHORTCODES V1 ===============================
     * =========================================================================
     */

    /**
     * [listar_imoveis]
     * Lista imóveis de forma simples
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
     * [pesquisar_imoveis]
     * Pesquisa simples por bairro e cidade
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
     * =========================================================================
     * =========================== SHORTCODES V2 ===============================
     * =========================================================================
     */

    /**
     * [listar_imoveis_v2]
     * Lista imóveis com design V2 (mostrando novos campos etc)
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
                    // Metadados
                    $endereco  = get_post_meta( get_the_ID(), '_endereco_imovel', true );
                    $bairro    = get_post_meta( get_the_ID(), '_bairro_imovel', true );
                    $cidade    = get_post_meta( get_the_ID(), '_cidade_imovel', true );
                    $preco     = get_post_meta( get_the_ID(), '_preco_imovel', true );
                    $descricao = get_post_meta( get_the_ID(), '_descricao_imovel', true );

                    // Novos
                    $area      = get_post_meta( get_the_ID(), '_area_imovel', true );
                    $quartos   = get_post_meta( get_the_ID(), '_quartos_imovel', true );
                    $banheiros = get_post_meta( get_the_ID(), '_banheiros_imovel', true );
                    $suites    = get_post_meta( get_the_ID(), '_suites_imovel', true );
                    $vagas     = get_post_meta( get_the_ID(), '_vagas_imovel', true );
                    $tipo      = get_post_meta( get_the_ID(), '_tipo_imovel', true );
                    ?>
                    <div class="imovel-item-v2">
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
     * [pesquisar_imoveis_v2]
     * Pesquisa por bairro, cidade, faixa de preço, tipo etc. (listagem aparecendo abaixo)
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

        // Verifica se há algum filtro GET
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

            echo '<div class="imoveis-listagem-v2">';
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
                    <div class="imovel-item-v2">
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
            echo '</div>';

            wp_reset_postdata();
        }

        return ob_get_clean();
    }

    /**
     * [imoveis_custom_page]
     * Exemplo de "página especial" unindo listagem V2 e pesquisa V2
     */
    public function shortcode_imoveis_custom_page( $atts ) {
        ob_start();
        echo '<h2>' . __( 'Página Especial de Imóveis', 'imoveis-sp' ) . '</h2>';

        // Listagem V2 (4 imóveis)
        echo do_shortcode('[listar_imoveis_v2 quantidade="4"]');

        // Formulário de pesquisa V2
        echo '<hr><h3>'. __( 'Pesquisar com mais filtros', 'imoveis-sp' ) .'</h3>';
        echo do_shortcode('[pesquisar_imoveis_v2]');

        return ob_get_clean();
    }

    /**
     * =========================================================================
     * =========================== NOVIDADES V3 ================================
     * =========================================================================
     */

    /**
     * Carrega estilos e scripts (V3), incluindo Font Awesome e Google Places
     */
    public function adicionar_estilos_e_scripts_v3() {
        // Estilos V1 (já existentes)
        wp_enqueue_style(
            'imoveis-sp-css',
            plugin_dir_url(__FILE__) . 'css/imoveis-sp.css',
            array(),
            '1.0'
        );
        // Estilos V2
        wp_enqueue_style(
            'imoveis-sp-v2-css',
            plugin_dir_url(__FILE__) . 'css/imoveis-sp-v2.css',
            array(),
            '2.0'
        );
        // Estilos V3
        wp_enqueue_style(
            'imoveis-sp-v3-css',
            plugin_dir_url(__FILE__) . 'css/imoveis-sp-v3.css',
            array(),
            '3.0'
        );

        // Font Awesome (CDN)
        wp_enqueue_style(
            'font-awesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css',
            array(),
            '6.0.0'
        );

        // Scripts v2
        wp_enqueue_script(
            'imoveis-sp-v2-js',
            plugin_dir_url(__FILE__) . 'js/imoveis-sp-v2.js',
            array('jquery'),
            '2.0',
            true
        );
        // Script v3
        wp_enqueue_script(
            'imoveis-sp-v3-js',
            plugin_dir_url(__FILE__) . 'js/imoveis-sp-v3.js',
            array('jquery'),
            '3.0',
            true
        );

        // Google Places API (se houver API Key)
        $api_key = get_option( 'imoveis_sp_google_api_key', '' );
        if ( ! empty( $api_key ) ) {
            wp_enqueue_script(
                'google-places-api',
                'https://maps.googleapis.com/maps/api/js?key=' . esc_attr( $api_key ) . '&libraries=places',
                array(),
                null,
                true
            );
        }
    }

    /**
     * [catalogo_imoveis]
     * Novo shortcode V3: Um "catálogo" unindo pesquisa e listagem no mesmo lugar
     */
    public function shortcode_catalogo_imoveis( $atts ) {
        ob_start();

        echo '<h2 class="catalogo-title">' . __( 'Catálogo de Imóveis', 'imoveis-sp' ) . '</h2>';

        // Inclui o formulário de pesquisa V2
        echo do_shortcode('[pesquisar_imoveis_v2]');

        // Se não houver filtros GET, mostra listagem V2 com base em "quantidade" do shortcode
        $params = shortcode_atts( array( 'quantidade' => 6 ), $atts, 'catalogo_imoveis' );
        echo '<hr>';
        echo do_shortcode('[listar_imoveis_v2 quantidade="' . $params['quantidade'] . '"]');

        return ob_get_clean();
    }

    /**
     * Força template single-imovel customizado se o tema não tiver single-imovel.php
     */
    public function forcar_template_single_imovel( $single_template ) {
        global $post;
        if ( 'imovel' === $post->post_type ) {
            $template_no_plugin = plugin_dir_path( __FILE__ ) . 'templates/single-imovel.php';
            if ( file_exists( $template_no_plugin ) ) {
                $single_template = $template_no_plugin;
            }
        }
        return $single_template;
    }

    /**
     * =========================================================================
     * ================== Configurações (API Key Google) no Admin ==============
     * =========================================================================
     */
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
        register_setting(
            'imoveis_sp_config_group',
            'imoveis_sp_google_api_key',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => ''
            )
        );

        add_settings_section(
            'imoveis_sp_config_section',
            __( 'API Key do Google Places', 'imoveis-sp' ),
            function() {
                echo '<p>' . __( 'Insira sua chave de API para habilitar autocomplete de endereço.', 'imoveis-sp' ) . '</p>';
            },
            'imoveis-sp-config'
        );

        add_settings_field(
            'imoveis_sp_google_api_key_field',
            __( 'Chave de API:', 'imoveis-sp' ),
            array( $this, 'campo_api_key_callback' ),
            'imoveis-sp-config',
            'imoveis_sp_config_section'
        );
    }

    public function campo_api_key_callback() {
        $value = get_option( 'imoveis_sp_google_api_key', '' );
        echo '<input type="text" name="imoveis_sp_google_api_key" value="' . esc_attr( $value ) . '" size="50">';
    }

} // Fim da classe principal

// Instancia o plugin
new ImoveisSPPlugin();
