<?php
/**
 * Plugin Name: Imóveis São Paulo
 * Plugin URI:  https://seudominio.com
 * Description: Plugin de exemplo para cadastro e listagem de imóveis na cidade de São Paulo.
 * Version:     1.0
 * Author:      Seu Nome
 * Author URI:  https://seudominio.com
 * License:     GPL2
 * Text Domain: imoveis-sp
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Segurança: Sai se acessado diretamente
}

class ImoveisSPPlugin {

    public function __construct() {
        // Ações para inicializar
        add_action( 'init', array( $this, 'registrar_cpt_imoveis' ) );
        add_action( 'add_meta_boxes', array( $this, 'registrar_metaboxes' ) );
        add_action( 'save_post', array( $this, 'salvar_dados_imovel' ) );

        // Shortcodes
        add_shortcode( 'listar_imoveis', array( $this, 'shortcode_listar_imoveis' ) );
        add_shortcode( 'pesquisar_imoveis', array( $this, 'shortcode_pesquisar_imoveis' ) );

        // Adicionar estilos no front-end
        add_action( 'wp_enqueue_scripts', array( $this, 'adicionar_estilos_frontend' ) );
    }

    /**
     * Registra o CPT de Imóveis
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
     * Registra metaboxes para dados específicos do imóvel
     */
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

    /**
     * Callback do metabox
     */
    public function metabox_dados_imovel_callback( $post ) {
        // Cria um nonce field para verificação de segurança
        wp_nonce_field( 'salvar_dados_imovel', 'dados_imovel_nonce' );

        // Busca valores existentes (se houver)
        $endereco  = get_post_meta( $post->ID, '_endereco_imovel', true );
        $bairro    = get_post_meta( $post->ID, '_bairro_imovel', true );
        $cidade    = get_post_meta( $post->ID, '_cidade_imovel', true );
        $preco     = get_post_meta( $post->ID, '_preco_imovel', true );
        $descricao = get_post_meta( $post->ID, '_descricao_imovel', true );
        ?>

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

        <?php
    }

    /**
     * Salva os dados do imóvel
     */
    public function salvar_dados_imovel( $post_id ) {
        // Verifica nonce
        if ( ! isset( $_POST['dados_imovel_nonce'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_POST['dados_imovel_nonce'], 'salvar_dados_imovel' ) ) {
            return;
        }

        // Previne salvamentos automáticos
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Verifica permissões do usuário
        if ( isset( $_POST['post_type'] ) && 'imovel' === $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }
        }

        // Salva/metas
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
    }

    /**
     * Shortcode para listar todos os imóveis
     * Uso: [listar_imoveis]
     */
    public function shortcode_listar_imoveis( $atts ) {
        $atts = shortcode_atts( array(
            'quantidade' => 10, // pode personalizar
        ), $atts, 'listar_imoveis' );

        ob_start();

        // Query para buscar imóveis
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
     * Shortcode para pesquisar imóveis por Bairro ou Cidade
     * Uso: [pesquisar_imoveis]
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

        // Se houve busca, exibimos a listagem filtrada
        if ( isset($_GET['pesquisa_bairro']) || isset($_GET['pesquisa_cidade']) ) {
            $bairro_buscado = isset($_GET['pesquisa_bairro']) ? sanitize_text_field($_GET['pesquisa_bairro']) : '';
            $cidade_buscada = isset($_GET['pesquisa_cidade']) ? sanitize_text_field($_GET['pesquisa_cidade']) : '';

            // Monta array meta_query conforme parâmetros
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
     * Adiciona estilos no front-end
     */
    public function adicionar_estilos_frontend() {
        // Somente se estivermos em páginas do site (não no admin)
        if ( ! is_admin() ) {
            wp_enqueue_style(
                'imoveis-sp-css',
                plugin_dir_url(__FILE__) . 'css/imoveis-sp.css',
                array(),
                '1.0'
            );
        }
    }
}

// Inicializa o plugin
new ImoveisSPPlugin();

/**
 * Adicionamos um arquivo CSS na pasta /css/ para estilizar:
 * Se quiser, você pode colocar esse estilo inline ou incluir aqui
 * mas o ideal é criar o arquivo separado para melhor organização
 */
