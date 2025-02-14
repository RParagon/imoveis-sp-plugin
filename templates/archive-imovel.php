<?php
/**
 * ARQUIVO: archive-imovel.php
 * Este template exibe a listagem de imóveis do CPT "imovel"
 * com um formulário de busca na parte superior e layout aprimorado.
 *
 * Melhoria de design em 1000% (exemplo ilustrativo), com margem superior
 * de 120px no desktop e 40px no mobile. A barra de pesquisa exibe o termo
 * previamente pesquisado (caso exista em $_GET).
 */

// Impede acesso direto
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header(); 
?>

<style>
/* -------------------------------------------------------------------------
   ESTILOS PARA MELHORAR O DESIGN EM 1000% (EXEMPLO)
   -------------------------------------------------------------------------
   - Margem superior: 120px no desktop, 40px no mobile.
   - Layout mais moderno para a barra de pesquisa e a listagem de imóveis.
   - Você pode ajustar cores, tipografia e detalhes conforme seu tema.
*/

/* Container geral */
.archive-imoveis-container {
    margin-top: 120px; /* desktop */
    padding: 20px;
    background-color: #f8f8f8; /* Fundo suave */
    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
    color: #333;
}
@media screen and (max-width: 768px) {
    .archive-imoveis-container {
        margin-top: 80px; /* mobile */
        padding: 15px;
    }
}

/* Título principal */
.archive-imoveis-container h1,
.archive-imoveis-container h2 {
    text-align: center;
    margin-bottom: 20px;
    font-weight: 600;
    color: #222;
}

/* Barra de pesquisa */
.imoveis-search-bar {
    max-width: 1100px;
    margin: 0 auto 30px auto;
    background: #ffffff;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.06);
}
.imoveis-search-bar form {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: space-between;
}
.imoveis-search-bar .search-field-group {
    flex: 1;
    min-width: 220px;
}
.imoveis-search-bar label {
    display: block;
    font-weight: 500;
    margin-bottom: 5px;
}
.imoveis-search-bar input[type="text"],
.imoveis-search-bar input[type="number"] {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
}
.imoveis-search-bar .btn-pesquisa {
    background: #0073aa;
    color: #fff;
    border: none;
    padding: 10px 25px;
    font-size: 16px;
    border-radius: 4px;
    cursor: pointer;
    align-self: flex-end;
    transition: background 0.3s;
}
.imoveis-search-bar .btn-pesquisa:hover {
    background: #005b82;
}

/* Listagem de imóveis */
.lista-imoveis-archive {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 25px;
    max-width: 1200px;
    margin: 0 auto;
}
.imovel-item-archive {
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0,0,0,0.06);
    transition: transform 0.3s, box-shadow 0.3s;
}
.imovel-item-archive:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 14px rgba(0,0,0,0.08);
}
.imovel-thumb-archive img {
    width: 100%;
    height: auto;
    display: block;
}
.imovel-info-archive {
    padding: 15px;
}
.imovel-titulo-archive {
    font-size: 18px;
    margin: 0 0 10px 0;
    color: #0073aa;
}
.imovel-info-archive p {
    margin: 0 0 8px 0;
    line-height: 1.4;
}
.imovel-info-archive a {
    display: inline-block;
    margin-top: 10px;
    background: #0073aa;
    color: #fff;
    padding: 8px 16px;
    border-radius: 4px;
    text-decoration: none;
    transition: background 0.3s;
}
.imovel-info-archive a:hover {
    background: #005b82;
}

/* Paginação */
.page-numbers {
    display: inline-block;
    margin: 5px;
    padding: 8px 12px;
    background: #fff;
    color: #0073aa;
    border-radius: 4px;
    text-decoration: none;
    border: 1px solid #ddd;
    transition: background 0.3s, color 0.3s;
}
.page-numbers.current {
    background: #0073aa;
    color: #fff;
    border-color: #0073aa;
}
.page-numbers:hover {
    background: #f0f0f0;
    color: #0073aa;
}
</style>

<div class="archive-imoveis-container">

    <h1><?php _e( 'Lista de Imóveis', 'imoveis-sp' ); ?></h1>

    <!-- =================== BARRA DE PESQUISA (COM OS VALORES PESQUISADOS) =================== -->
    <div class="imoveis-search-bar">
        <form method="GET" action="">
            <div class="search-field-group">
                <label for="filtro_rua"><?php _e( 'Endereço:', 'imoveis-sp' ); ?></label>
                <input type="text" name="filtro_rua" id="filtro_rua" value="<?php echo isset($_GET['filtro_rua']) ? esc_attr($_GET['filtro_rua']) : ''; ?>" placeholder="<?php _e('Ex: Avenida Paulista', 'imoveis-sp'); ?>">
            </div>
            <div class="search-field-group">
                <label for="filtro_bairro"><?php _e( 'Bairro:', 'imoveis-sp' ); ?></label>
                <input type="text" name="filtro_bairro" id="filtro_bairro" value="<?php echo isset($_GET['filtro_bairro']) ? esc_attr($_GET['filtro_bairro']) : ''; ?>" placeholder="<?php _e('Ex: Bela Vista', 'imoveis-sp'); ?>">
            </div>
            <div class="search-field-group">
                <label for="filtro_cidade"><?php _e( 'Cidade:', 'imoveis-sp' ); ?></label>
                <input type="text" name="filtro_cidade" id="filtro_cidade" value="<?php echo isset($_GET['filtro_cidade']) ? esc_attr($_GET['filtro_cidade']) : ''; ?>" placeholder="<?php _e('Ex: São Paulo', 'imoveis-sp'); ?>">
            </div>
            <div class="search-field-group">
                <label for="filtro_tipo"><?php _e( 'Tipo:', 'imoveis-sp' ); ?></label>
                <input type="text" name="filtro_tipo" id="filtro_tipo" value="<?php echo isset($_GET['filtro_tipo']) ? esc_attr($_GET['filtro_tipo']) : ''; ?>" placeholder="<?php _e('Ex: Apartamento', 'imoveis-sp'); ?>">
            </div>
            <div class="search-field-group">
                <label for="filtro_preco_min"><?php _e( 'Preço Mín (R$):', 'imoveis-sp' ); ?></label>
                <input type="number" name="filtro_preco_min" id="filtro_preco_min" step="0.01" value="<?php echo isset($_GET['filtro_preco_min']) ? esc_attr($_GET['filtro_preco_min']) : ''; ?>">
            </div>
            <div class="search-field-group">
                <label for="filtro_preco_max"><?php _e( 'Preço Máx (R$):', 'imoveis-sp' ); ?></label>
                <input type="number" name="filtro_preco_max" id="filtro_preco_max" step="0.01" value="<?php echo isset($_GET['filtro_preco_max']) ? esc_attr($_GET['filtro_preco_max']) : ''; ?>">
            </div>
            <div class="search-field-group">
                <label for="filtro_dormitorios"><?php _e( 'Dormitórios:', 'imoveis-sp' ); ?></label>
                <input type="number" name="filtro_dormitorios" id="filtro_dormitorios" value="<?php echo isset($_GET['filtro_dormitorios']) ? esc_attr($_GET['filtro_dormitorios']) : ''; ?>">
            </div>
            <button type="submit" class="btn-pesquisa">
                <?php _e( 'Pesquisar', 'imoveis-sp' ); ?>
            </button>
        </form>
    </div>

    <?php
    // ================== PREPARA A QUERY DE ACORDO COM GET ==================
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

    // Constrói a query
    $paged = get_query_var('paged') ? get_query_var('paged') : 1;

    $args = array(
        'post_type'      => 'imovel',
        'paged'          => $paged,
        'meta_query'     => $meta_query,
    );

    $wp_query = new WP_Query( $args );

    // ================ LOOP PARA LISTAR OS IMÓVEIS ================
    if ( $wp_query->have_posts() ) :
        echo '<div class="lista-imoveis-archive">';
        while ( $wp_query->have_posts() ) :
            $wp_query->the_post();

            // Exemplo de exibição de cada imóvel
            $endereco  = get_post_meta( get_the_ID(), '_endereco_imovel', true );
            $bairro    = get_post_meta( get_the_ID(), '_bairro_imovel', true );
            $cidade    = get_post_meta( get_the_ID(), '_cidade_imovel', true );
            $preco     = get_post_meta( get_the_ID(), '_preco_imovel', true );
            $tipo      = get_post_meta( get_the_ID(), '_tipo_imovel', true );
            ?>
            <div class="imovel-item-archive">
                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="imovel-thumb-archive">
                        <?php the_post_thumbnail( 'medium_large' ); ?>
                    </div>
                <?php endif; ?>
                <div class="imovel-info-archive">
                    <h3 class="imovel-titulo-archive"><?php the_title(); ?></h3>
                    <p><?php echo esc_html( $endereco ); ?> - <?php echo esc_html( $bairro ); ?>, <?php echo esc_html( $cidade ); ?></p>
                    <p><?php _e( 'Tipo:', 'imoveis-sp' ); ?> <?php echo esc_html( $tipo ); ?></p>
                    <p><?php _e( 'Preço:', 'imoveis-sp' ); ?> R$ <?php echo esc_html( $preco ); ?></p>
                    <a href="<?php the_permalink(); ?>"><?php _e( 'Ver detalhes', 'imoveis-sp' ); ?></a>
                </div>
            </div>
            <?php
        endwhile;
        echo '</div>';

        // Paginação
        echo paginate_links( array(
            'total' => $wp_query->max_num_pages,
        ) );

    else :
        echo '<p>' . __( 'Nenhum imóvel encontrado para estes filtros.', 'imoveis-sp' ) . '</p>';
    endif;

    wp_reset_postdata();
    ?>

</div><!-- /.archive-imoveis-container -->

<?php
get_footer();
