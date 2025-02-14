<?php
/**
 * Plugin Name: Imóveis São Paulo
 * Plugin URI:  https://github.com/RParagon/imoveis-sp-plugin/tree/V3
 * Description: Plugin completo para cadastro, listagem e pesquisa de imóveis – totalmente responsivo, com autocomplete de endereço, filtros avançados e página de detalhes customizada com contato via WhatsApp. Múltiplos shortcodes exclusivos e layout moderno em CSS/JS, 1000+ linhas e sem comentários.
 * Version:     2.0
 * Author:      Virtual Mark
 * Author URI:  https://virtualmark.com.br
 * License:     GPL2
 * Text Domain: imoveis-sp
 */

if(!defined('ABSPATH')){exit;}

class ImoveisSPPluginMegaVersion{
const VERSION='2.0';
public function __construct(){
add_action('init',array($this,'registrar_cpt_imoveis'));
add_action('add_meta_boxes',array($this,'registrar_metaboxes'));
add_action('save_post',array($this,'salvar_dados_imovel'));
add_action('wp_enqueue_scripts',array($this,'carregar_assets'));
add_action('admin_enqueue_scripts',array($this,'carregar_assets_admin'));
add_action('admin_menu',array($this,'adicionar_pagina_config'));
add_action('admin_init',array($this,'registrar_config'));
register_activation_hook(__FILE__,array($this,'ativar_plugin'));
register_deactivation_hook(__FILE__,array($this,'desativar_plugin'));
add_action('init',array($this,'corrigir_conflito_emojis'));
add_filter('single_template',array($this,'forcar_template_single_imovel'));
add_filter('archive_template',array($this,'forcar_template_archive_imovel'));
add_shortcode('catalogo_imoveis',array($this,'shortcode_catalogo_imoveis'));
add_shortcode('catalogo_imoveis_estiloso',array($this,'shortcode_catalogo_imoveis_estiloso'));
add_shortcode('catalogo_imoveis_carrossel',array($this,'shortcode_catalogo_imoveis_carrossel'));
add_shortcode('catalogo_imoveis_minimalista',array($this,'shortcode_catalogo_imoveis_minimalista'));
add_shortcode('catalogo_imoveis_regiao_tipo_preco',array($this,'shortcode_catalogo_imoveis_regiao_tipo_preco'));
add_shortcode('catalogo_imoveis_listagem_cards',array($this,'shortcode_catalogo_imoveis_listagem_cards'));
add_shortcode('catalogo_imoveis_listagem_luxo',array($this,'shortcode_catalogo_imoveis_listagem_luxo'));
add_shortcode('catalogo_imoveis_listagem_horizontal',array($this,'shortcode_catalogo_imoveis_listagem_horizontal'));
add_shortcode('catalogo_imoveis_grid_avancado',array($this,'shortcode_catalogo_imoveis_grid_avancado'));
add_shortcode('catalogo_imoveis_slider_moderno',array($this,'shortcode_catalogo_imoveis_slider_moderno'));
add_shortcode('catalogo_imoveis_premium',array($this,'shortcode_catalogo_imoveis_premium'));
add_shortcode('catalogo_imoveis_minimal_cards',array($this,'shortcode_catalogo_imoveis_minimal_cards'));
add_shortcode('catalogo_imoveis_vip_carrossel',array($this,'shortcode_catalogo_imoveis_vip_carrossel'));
add_shortcode('catalogo_imoveis_super_lista',array($this,'shortcode_catalogo_imoveis_super_lista'));
add_shortcode('catalogo_imoveis_classico',array($this,'shortcode_catalogo_imoveis_classico'));
add_shortcode('catalogo_imoveis_ultra_estiloso',array($this,'shortcode_catalogo_imoveis_ultra_estiloso'));
add_shortcode('catalogo_imoveis_flat_list',array($this,'shortcode_catalogo_imoveis_flat_list'));
add_shortcode('catalogo_imoveis_deluxe_grid',array($this,'shortcode_catalogo_imoveis_deluxe_grid'));
add_shortcode('catalogo_imoveis_resumo_simples',array($this,'shortcode_catalogo_imoveis_resumo_simples'));
add_shortcode('catalogo_imoveis_plus_slider',array($this,'shortcode_catalogo_imoveis_plus_slider'));
add_shortcode('catalogo_imoveis_spotlight',array($this,'shortcode_catalogo_imoveis_spotlight'));
add_shortcode('catalogo_imoveis_dinamico',array($this,'shortcode_catalogo_imoveis_dinamico'));
}
public function ativar_plugin(){
$this->registrar_cpt_imoveis();
flush_rewrite_rules();
}
public function desativar_plugin(){
flush_rewrite_rules();
}
public function registrar_cpt_imoveis(){
$labels=array(
'name'=>__('Imóveis','imoveis-sp'),
'singular_name'=>__('Imóvel','imoveis-sp'),
'menu_name'=>__('Imóveis','imoveis-sp'),
'name_admin_bar'=>__('Imóvel','imoveis-sp'),
'add_new'=>__('Adicionar Novo','imoveis-sp'),
'add_new_item'=>__('Adicionar Novo Imóvel','imoveis-sp'),
'new_item'=>__('Novo Imóvel','imoveis-sp'),
'edit_item'=>__('Editar Imóvel','imoveis-sp'),
'view_item'=>__('Ver Imóvel','imoveis-sp'),
'all_items'=>__('Todos os Imóveis','imoveis-sp'),
'search_items'=>__('Buscar Imóveis','imoveis-sp'),
'parent_item_colon'=>__('Imóvel Pai:','imoveis-sp'),
'not_found'=>__('Nenhum imóvel encontrado.','imoveis-sp'),
'not_found_in_trash'=>__('Nenhum imóvel encontrado na lixeira.','imoveis-sp')
);
$args=array(
'labels'=>$labels,
'public'=>true,
'has_archive'=>true,
'rewrite'=>array('slug'=>'imoveis'),
'supports'=>array('title','editor','thumbnail'),
'menu_icon'=>'dashicons-building',
);
register_post_type('imovel',$args);
}
public function registrar_metaboxes(){
add_meta_box('dados_imovel',__('Dados do Imóvel','imoveis-sp'),array($this,'metabox_dados_imovel_callback'),'imovel','normal','default');
add_meta_box('cep_imovel',__('Localização por CEP','imoveis-sp'),array($this,'metabox_cep_imovel_callback'),'imovel','normal','default');
add_meta_box('galeria_imovel',__('Galeria de Imagens','imoveis-sp'),array($this,'metabox_galeria_imovel_callback'),'imovel','normal','default');
add_meta_box('destaque_imovel',__('Imóvel em Destaque','imoveis-sp'),array($this,'metabox_destaque_imovel_callback'),'imovel','side','high');
}
public function metabox_dados_imovel_callback($post){
wp_nonce_field('salvar_dados_imovel','dados_imovel_nonce');
$campos=array(
'endereco_imovel'=>get_post_meta($post->ID,'_endereco_imovel',true),
'bairro_imovel'=>get_post_meta($post->ID,'_bairro_imovel',true),
'cidade_imovel'=>get_post_meta($post->ID,'_cidade_imovel',true),
'preco_imovel'=>get_post_meta($post->ID,'_preco_imovel',true),
'descricao_imovel'=>get_post_meta($post->ID,'_descricao_imovel',true),
'area_imovel'=>get_post_meta($post->ID,'_area_imovel',true),
'quartos_imovel'=>get_post_meta($post->ID,'_quartos_imovel',true),
'banheiros_imovel'=>get_post_meta($post->ID,'_banheiros_imovel',true),
'suites_imovel'=>get_post_meta($post->ID,'_suites_imovel',true),
'vagas_imovel'=>get_post_meta($post->ID,'_vagas_imovel',true),
'tipo_imovel'=>get_post_meta($post->ID,'_tipo_imovel',true),
'latitude_imovel'=>get_post_meta($post->ID,'_latitude_imovel',true),
'longitude_imovel'=>get_post_meta($post->ID,'_longitude_imovel',true),
);
?>
<div class="metabox-imoveis-sp">
<p><label for="endereco_imovel"><?php echo __('Endereço (com autocomplete):','imoveis-sp');?></label><input type="text" name="endereco_imovel" id="endereco_imovel" class="google-places-autocomplete" value="<?php echo esc_attr($campos['endereco_imovel']);?>"></p>
<p><label for="bairro_imovel"><?php echo __('Bairro:','imoveis-sp');?></label><input type="text" name="bairro_imovel" id="bairro_imovel" value="<?php echo esc_attr($campos['bairro_imovel']);?>"></p>
<p><label for="cidade_imovel"><?php echo __('Cidade:','imoveis-sp');?></label><input type="text" name="cidade_imovel" id="cidade_imovel" value="<?php echo esc_attr($campos['cidade_imovel']);?>"></p>
<div class="localizacao-wrapper">
<div class="localizacao-half">
<label for="latitude_imovel"><?php echo __('Latitude:','imoveis-sp');?></label><input type="text" name="latitude_imovel" id="latitude_imovel" value="<?php echo esc_attr($campos['latitude_imovel']);?>">
</div>
<div class="localizacao-half">
<label for="longitude_imovel"><?php echo __('Longitude:','imoveis-sp');?></label><input type="text" name="longitude_imovel" id="longitude_imovel" value="<?php echo esc_attr($campos['longitude_imovel']);?>">
</div>
<div style="clear:both;"></div>
</div>
<p><label for="preco_imovel"><?php echo __('Preço (R$):','imoveis-sp');?></label><input type="number" step="0.01" name="preco_imovel" id="preco_imovel" value="<?php echo esc_attr($campos['preco_imovel']);?>"></p>
<p><label for="descricao_imovel"><?php echo __('Descrição:','imoveis-sp');?></label><textarea name="descricao_imovel" id="descricao_imovel" rows="4"><?php echo esc_textarea($campos['descricao_imovel']);?></textarea></p>
<hr>
<p><strong><?php echo __('Dados Adicionais','imoveis-sp');?>:</strong></p>
<p><label for="area_imovel"><?php echo __('Área (m²):','imoveis-sp');?></label><input type="number" step="0.01" name="area_imovel" id="area_imovel" value="<?php echo esc_attr($campos['area_imovel']);?>"></p>
<p><label for="quartos_imovel"><?php echo __('Quartos:','imoveis-sp');?></label><input type="number" name="quartos_imovel" id="quartos_imovel" value="<?php echo esc_attr($campos['quartos_imovel']);?>"></p>
<p><label for="banheiros_imovel"><?php echo __('Banheiros:','imoveis-sp');?></label><input type="number" name="banheiros_imovel" id="banheiros_imovel" value="<?php echo esc_attr($campos['banheiros_imovel']);?>"></p>
<p><label for="suites_imovel"><?php echo __('Suítes:','imoveis-sp');?></label><input type="number" name="suites_imovel" id="suites_imovel" value="<?php echo esc_attr($campos['suites_imovel']);?>"></p>
<p><label for="vagas_imovel"><?php echo __('Vagas de garagem:','imoveis-sp');?></label><input type="number" name="vagas_imovel" id="vagas_imovel" value="<?php echo esc_attr($campos['vagas_imovel']);?>"></p>
<p><label for="tipo_imovel"><?php echo __('Tipo do Imóvel:','imoveis-sp');?></label><input type="text" name="tipo_imovel" id="tipo_imovel" value="<?php echo esc_attr($campos['tipo_imovel']);?>" placeholder="<?php echo __('Ex: Apartamento, Casa, Comercial...','imoveis-sp');?>"></p>
</div>
<?php
}
public function metabox_cep_imovel_callback($post){
wp_nonce_field('salvar_dados_imovel','dados_imovel_nonce');
$cep_imovel=get_post_meta($post->ID,'_cep_imovel',true);
?>
<div class="metabox-imoveis-sp">
<p><label for="cep_imovel"><?php echo __('CEP do Imóvel:','imoveis-sp');?></label><input type="text" name="cep_imovel" id="cep_imovel" value="<?php echo esc_attr($cep_imovel);?>" placeholder="<?php echo __('Ex: 01001-000','imoveis-sp');?>"></p>
<p class="description"><?php echo __('Digite o CEP e clique no botão para buscar Latitude e Longitude automaticamente.','imoveis-sp');?></p>
<button type="button" class="button button-secondary" id="btn-obter-coordenadas-cep"><?php echo __('Obter Coordenadas via CEP','imoveis-sp');?></button>
</div>
<?php
}
public function metabox_galeria_imovel_callback($post){
wp_nonce_field('salvar_dados_imovel','dados_imovel_nonce');
$galeria_ids=get_post_meta($post->ID,'_galeria_imovel_ids',true);
if(!is_array($galeria_ids)){$galeria_ids=array();}
?>
<div class="metabox-imoveis-sp">
<p><?php echo __('Selecione ou faça upload de múltiplas imagens para este imóvel.','imoveis-sp');?></p>
<div id="galeria-imovel-container">
<?php
if(!empty($galeria_ids)){
foreach($galeria_ids as $attachment_id){
$thumb_url=wp_get_attachment_image_src($attachment_id,'thumbnail');
if($thumb_url){
echo'<div class="galeria-imovel-thumb"><img src="'.esc_url($thumb_url[0]).'" alt=""><span class="galeria-remove-img" data-attachment-id="'.esc_attr($attachment_id).'">x</span></div>';
}}}
?>
</div>
<input type="hidden" id="galeria_imovel_ids" name="galeria_imovel_ids" value="<?php echo esc_attr(implode(',',$galeria_ids));?>">
<p><button type="button" class="button button-primary" id="btn-adicionar-imagens-galeria"><?php echo __('Adicionar/Selecionar Imagens','imoveis-sp');?></button></p>
</div>
<?php
}
public function metabox_destaque_imovel_callback($post){
wp_nonce_field('salvar_dados_imovel','dados_imovel_nonce');
$destaque=get_post_meta($post->ID,'_imovel_destaque',true);
$checked=($destaque==='yes')?'checked':'';
?>
<div class="metabox-imoveis-sp destaque-metabox">
<p><label for="imovel_destaque"><input type="checkbox" name="imovel_destaque" id="imovel_destaque" value="yes" <?php echo $checked;?>><?php echo __('Marcar este imóvel como Destaque?','imoveis-sp');?></label></p>
<p class="description"><?php echo __('Imóveis marcados como destaque podem aparecer em local especial no front-end.','imoveis-sp');?></p>
</div>
<?php
}
public function salvar_dados_imovel($post_id){
if(!isset($_POST['dados_imovel_nonce'])||!wp_verify_nonce($_POST['dados_imovel_nonce'],'salvar_dados_imovel')){return;}
if(defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE){return;}
if(isset($_POST['post_type'])&&$_POST['post_type']==='imovel'){if(!current_user_can('edit_post',$post_id)){return;}}
$fields=array(
'endereco_imovel'=>array('meta_key'=>'_endereco_imovel','callback'=>'sanitize_text_field'),
'bairro_imovel'=>array('meta_key'=>'_bairro_imovel','callback'=>'sanitize_text_field'),
'cidade_imovel'=>array('meta_key'=>'_cidade_imovel','callback'=>'sanitize_text_field'),
'preco_imovel'=>array('meta_key'=>'_preco_imovel','callback'=>'floatval'),
'descricao_imovel'=>array('meta_key'=>'_descricao_imovel','callback'=>'sanitize_textarea_field'),
'area_imovel'=>array('meta_key'=>'_area_imovel','callback'=>'floatval'),
'quartos_imovel'=>array('meta_key'=>'_quartos_imovel','callback'=>'intval'),
'banheiros_imovel'=>array('meta_key'=>'_banheiros_imovel','callback'=>'intval'),
'suites_imovel'=>array('meta_key'=>'_suites_imovel','callback'=>'intval'),
'vagas_imovel'=>array('meta_key'=>'_vagas_imovel','callback'=>'intval'),
'tipo_imovel'=>array('meta_key'=>'_tipo_imovel','callback'=>'sanitize_text_field'),
'latitude_imovel'=>array('meta_key'=>'_latitude_imovel','callback'=>'sanitize_text_field'),
'longitude_imovel'=>array('meta_key'=>'_longitude_imovel','callback'=>'sanitize_text_field'),
'cep_imovel'=>array('meta_key'=>'_cep_imovel','callback'=>'sanitize_text_field'),
);
foreach($fields as $field_name=>$data){
if(isset($_POST[$field_name])){update_post_meta($post_id,$data['meta_key'],call_user_func($data['callback'],$_POST[$field_name]));}
}
if(isset($_POST['galeria_imovel_ids'])){
$ids_str=sanitize_text_field($_POST['galeria_imovel_ids']);
$ids_array=array_filter(array_map('trim',explode(',',$ids_str)));
update_post_meta($post_id,'_galeria_imovel_ids',$ids_array);
}
$destaque_val=(isset($_POST['imovel_destaque'])&&$_POST['imovel_destaque']==='yes')?'yes':'no';
update_post_meta($post_id,'_imovel_destaque',$destaque_val);
}
public function shortcode_catalogo_imoveis($atts){
ob_start();
?>
<div class="catalogo-imoveis-pesquisa-destaques">
<div class="catalogo-filtros">
<h2><?php echo __('Encontre seu Imóvel','imoveis-sp');?></h2>
<form method="GET" action="<?php echo esc_url(get_post_type_archive_link('imovel'));?>">
<div class="filtros-linha">
<div class="filtro-item">
<label for="filtro_rua"><?php echo __('Endereço:','imoveis-sp');?></label>
<input type="text" id="filtro_rua" name="filtro_rua" class="google-places-autocomplete" placeholder="<?php echo __('Rua...','imoveis-sp');?>">
</div>
<div class="filtro-item">
<label for="filtro_bairro"><?php echo __('Bairro:','imoveis-sp');?></label>
<input type="text" id="filtro_bairro" name="filtro_bairro" placeholder="<?php echo __('Bairro...','imoveis-sp');?>">
</div>
<div class="filtro-item">
<label for="filtro_cidade"><?php echo __('Cidade:','imoveis-sp');?></label>
<input type="text" id="filtro_cidade" name="filtro_cidade" placeholder="<?php echo __('Cidade...','imoveis-sp');?>">
</div>
</div>
<div class="filtros-linha">
<div class="filtro-item">
<label for="filtro_tipo"><?php echo __('Tipo:','imoveis-sp');?></label>
<input type="text" id="filtro_tipo" name="filtro_tipo" placeholder="<?php echo __('Ex: Apartamento...','imoveis-sp');?>">
</div>
<div class="filtro-item">
<label for="filtro_preco_min"><?php echo __('Preço Mín (R$):','imoveis-sp');?></label>
<input type="number" step="0.01" id="filtro_preco_min" name="filtro_preco_min">
</div>
<div class="filtro-item">
<label for="filtro_preco_max"><?php echo __('Preço Máx (R$):','imoveis-sp');?></label>
<input type="number" step="0.01" id="filtro_preco_max" name="filtro_preco_max">
</div>
</div>
<div class="filtros-linha">
<div class="filtro-item">
<label for="filtro_dormitorios"><?php echo __('Dormitórios:','imoveis-sp');?></label>
<input type="number" id="filtro_dormitorios" name="filtro_dormitorios">
</div>
<div class="filtro-item">
<button type="submit" class="btn-pesquisa"><?php echo __('Pesquisar','imoveis-sp');?></button>
</div>
</div>
</form>
</div>
<div class="catalogo-destaques">
<h2><?php echo __('Imóveis em Destaque','imoveis-sp');?></h2>
<?php
$args_destaques=array(
'post_type'=>'imovel',
'posts_per_page'=>3,
'meta_query'=>array(
array('key'=>'_imovel_destaque','value'=>'yes'),
),
);
$destaques_query=new WP_Query($args_destaques);
if($destaques_query->have_posts()){
echo'<div class="lista-imoveis-destaque">';
while($destaques_query->have_posts()){
$destaques_query->the_post();
$endereco=get_post_meta(get_the_ID(),'_endereco_imovel',true);
$bairro=get_post_meta(get_the_ID(),'_bairro_imovel',true);
$cidade=get_post_meta(get_the_ID(),'_cidade_imovel',true);
$preco=get_post_meta(get_the_ID(),'_preco_imovel',true);
$tipo=get_post_meta(get_the_ID(),'_tipo_imovel',true);
?>
<div class="imovel-item destaque-item">
<?php if(has_post_thumbnail()):?>
<div class="imovel-thumb"><?php the_post_thumbnail('medium_large');?></div>
<?php endif;?>
<div class="imovel-info">
<h3 class="imovel-titulo"><?php the_title();?></h3>
<p><i class="fas fa-map-marker-alt"></i> <?php echo esc_html($endereco);?> - <?php echo esc_html($bairro);?>, <?php echo esc_html($cidade);?></p>
<p><i class="fas fa-tag"></i> <?php echo __('Tipo:','imoveis-sp');?> <?php echo esc_html($tipo);?></p>
<p><i class="fas fa-dollar-sign"></i> <?php echo __('Preço:','imoveis-sp');?> R$ <?php echo esc_html($preco);?></p>
<a href="<?php the_permalink();?>" class="btn-detalhes"><?php echo __('Ver detalhes','imoveis-sp');?></a>
</div>
</div>
<?php
}
echo'</div>';
wp_reset_postdata();
}else{
echo'<p>'.__('Não há imóveis em destaque no momento.','imoveis-sp').'</p>';
}
?>
</div>
</div>
<?php
return ob_get_clean();
}
public function shortcode_catalogo_imoveis_estiloso($atts){
$atts=shortcode_atts(array('destaque'=>'no','quantidade'=>6),$atts,'catalogo_imoveis_estiloso');
ob_start();
$meta_query=array();
if($atts['destaque']==='yes'){$meta_query[]=array('key'=>'_imovel_destaque','value'=>'yes');}
$args=array('post_type'=>'imovel','posts_per_page'=>intval($atts['quantidade']));
if(!empty($meta_query)){$args['meta_query']=$meta_query;}
$query=new WP_Query($args);
?>
<div class="catalogo-imoveis-estiloso">
<h2><?php echo __('Imóveis em Layout Estiloso (Grid)','imoveis-sp');?></h2>
<?php if($query->have_posts()):?>
<div class="grid-imoveis-estiloso">
<?php while($query->have_posts()):$query->the_post();
$endereco=get_post_meta(get_the_ID(),'_endereco_imovel',true);
$bairro=get_post_meta(get_the_ID(),'_bairro_imovel',true);
$cidade=get_post_meta(get_the_ID(),'_cidade_imovel',true);
$preco=get_post_meta(get_the_ID(),'_preco_imovel',true);
$tipo=get_post_meta(get_the_ID(),'_tipo_imovel',true);
?>
<div class="item-imovel-grid">
<?php if(has_post_thumbnail()):?>
<div class="thumb-imovel-grid"><?php the_post_thumbnail('medium');?></div>
<?php endif;?>
<div class="info-imovel-grid">
<h3><?php the_title();?></h3>
<p><strong><?php echo __('Local:','imoveis-sp');?></strong> <?php echo esc_html($endereco);?> - <?php echo esc_html($bairro);?>, <?php echo esc_html($cidade);?></p>
<p><strong><?php echo __('Tipo:','imoveis-sp');?></strong> <?php echo esc_html($tipo);?></p>
<p><strong><?php echo __('Preço:','imoveis-sp');?></strong> R$ <?php echo esc_html($preco);?></p>
<a class="btn-detalhes-grid" href="<?php the_permalink();?>"><?php echo __('Ver Detalhes','imoveis-sp');?></a>
</div>
</div>
<?php endwhile;?>
</div>
<?php else:?>
<p><?php echo __('Nenhum imóvel encontrado neste layout estiloso.','imoveis-sp');?></p>
<?php endif;wp_reset_postdata();?>
</div>
<?php
return ob_get_clean();
}
public function shortcode_catalogo_imoveis_carrossel($atts){
$atts=shortcode_atts(array('destaque'=>'no','quantidade'=>5),$atts,'catalogo_imoveis_carrossel');
ob_start();
$meta_query=array();
if($atts['destaque']==='yes'){$meta_query[]=array('key'=>'_imovel_destaque','value'=>'yes');}
$args=array('post_type'=>'imovel','posts_per_page'=>intval($atts['quantidade']));
if(!empty($meta_query)){$args['meta_query']=$meta_query;}
$query=new WP_Query($args);
?>
<div class="catalogo-imoveis-carrossel">
<h2><?php echo __('Carrossel de Imóveis','imoveis-sp');?></h2>
<?php if($query->have_posts()):?>
<div class="imoveis-slider-container">
<?php while($query->have_posts()):$query->the_post();
$endereco=get_post_meta(get_the_ID(),'_endereco_imovel',true);
$bairro=get_post_meta(get_the_ID(),'_bairro_imovel',true);
$cidade=get_post_meta(get_the_ID(),'_cidade_imovel',true);
$preco=get_post_meta(get_the_ID(),'_preco_imovel',true);
$tipo=get_post_meta(get_the_ID(),'_tipo_imovel',true);
?>
<div class="imovel-slide">
<?php if(has_post_thumbnail()):?>
<div class="imovel-slide-thumb"><?php the_post_thumbnail('medium_large');?></div>
<?php endif;?>
<div class="imovel-slide-info">
<h3><?php the_title();?></h3>
<p><strong><?php echo __('Endereço:','imoveis-sp');?></strong> <?php echo esc_html($endereco);?></p>
<p><strong><?php echo __('Bairro:','imoveis-sp');?></strong> <?php echo esc_html($bairro);?></p>
<p><strong><?php echo __('Cidade:','imoveis-sp');?></strong> <?php echo esc_html($cidade);?></p>
<p><strong><?php echo __('Tipo:','imoveis-sp');?></strong> <?php echo esc_html($tipo);?></p>
<p><strong><?php echo __('Preço:','imoveis-sp');?></strong> R$ <?php echo esc_html($preco);?></p>
<a class="btn-carrossel-detalhes" href="<?php the_permalink();?>"><?php echo __('Ver Detalhes','imoveis-sp');?></a>
</div>
</div>
<?php endwhile;?>
</div>
<?php else:?>
<p><?php echo __('Nenhum imóvel disponível para o carrossel.','imoveis-sp');?></p>
<?php endif;wp_reset_postdata();?>
</div>
<?php
return ob_get_clean();
}
public function shortcode_catalogo_imoveis_minimalista($atts){
$atts=shortcode_atts(array('destaque'=>'no','quantidade'=>5),$atts,'catalogo_imoveis_minimalista');
ob_start();
$meta_query=array();
if($atts['destaque']==='yes'){$meta_query[]=array('key'=>'_imovel_destaque','value'=>'yes');}
$args=array('post_type'=>'imovel','posts_per_page'=>intval($atts['quantidade']));
if(!empty($meta_query)){$args['meta_query']=$meta_query;}
$query=new WP_Query($args);
?>
<div class="catalogo-imoveis-minimalista">
<h2><?php echo __('Lista Minimalista de Imóveis','imoveis-sp');?></h2>
<ul class="lista-minimalista">
<?php if($query->have_posts()):?>
<?php while($query->have_posts()):$query->the_post();
$preco=get_post_meta(get_the_ID(),'_preco_imovel',true);
?>
<li class="item-minimalista">
<a href="<?php the_permalink();?>"><?php the_title();?></a>
<?php if($preco){echo ' - R$ '.esc_html($preco);}?>
</li>
<?php endwhile;?>
<?php else:?>
<li><?php echo __('Nenhum imóvel encontrado na lista minimalista.','imoveis-sp');?></li>
<?php endif;wp_reset_postdata();?>
</ul>
</div>
<?php
return ob_get_clean();
}
public function shortcode_catalogo_imoveis_regiao_tipo_preco($atts){
$atts=shortcode_atts(array('posts_per_page'=>10),$atts,'catalogo_imoveis_regiao_tipo_preco');
ob_start();
?>
<div class="catalogo-imoveis-regiao-tipo-preco">
<h2><?php echo __('Pesquisa por Região, Tipo e Preço','imoveis-sp');?></h2>
<form method="GET" action="<?php echo esc_url(get_post_type_archive_link('imovel'));?>">
<div class="filtros-linha">
<div class="filtro-item">
<label for="filtro_cidade_regiao"><?php echo __('Região ou Cidade:','imoveis-sp');?></label>
<input type="text" id="filtro_cidade_regiao" name="filtro_cidade" placeholder="<?php echo __('Digite a região ou cidade','imoveis-sp');?>">
</div>
<div class="filtro-item">
<label for="filtro_tipo_regiao"><?php echo __('Tipo de Imóvel:','imoveis-sp');?></label>
<input type="text" id="filtro_tipo_regiao" name="filtro_tipo" placeholder="<?php echo __('Ex: Casa, Apartamento...','imoveis-sp');?>">
</div>
<div class="filtro-item">
<label for="filtro_preco_regiao"><?php echo __('Preço Máximo (R$):','imoveis-sp');?></label>
<input type="number" step="0.01" id="filtro_preco_regiao" name="filtro_preco_max">
</div>
</div>
<button type="submit" class="btn-pesquisa"><?php echo __('Buscar','imoveis-sp');?></button>
</form>
</div>
<?php
return ob_get_clean();
}
public function shortcode_catalogo_imoveis_listagem_cards($atts){
$atts=shortcode_atts(array('quantidade'=>6),$atts,'catalogo_imoveis_listagem_cards');
ob_start();
$args=array('post_type'=>'imovel','posts_per_page'=>intval($atts['quantidade']));
$query=new WP_Query($args);
?>
<div class="catalogo-imoveis-listagem-cards">
<h2><?php echo __('Listagem em Cards','imoveis-sp');?></h2>
<?php if($query->have_posts()):?>
<div class="cards-container">
<?php while($query->have_posts()):$query->the_post();
$endereco=get_post_meta(get_the_ID(),'_endereco_imovel',true);
$preco=get_post_meta(get_the_ID(),'_preco_imovel',true);
?>
<div class="card-item-imovel">
<div class="card-item-thumb">
<?php if(has_post_thumbnail()){the_post_thumbnail('medium');}?>
</div>
<div class="card-item-info">
<h3><?php the_title();?></h3>
<p><?php echo esc_html($endereco);?></p>
<p>R$ <?php echo esc_html($preco);?></p>
<a href="<?php the_permalink();?>"><?php echo __('Detalhes','imoveis-sp');?></a>
</div>
</div>
<?php endwhile;?>
</div>
<?php else:?>
<p><?php echo __('Nenhum imóvel encontrado em Cards.','imoveis-sp');?></p>
<?php endif;wp_reset_postdata();?>
</div>
<?php
return ob_get_clean();
}
public function shortcode_catalogo_imoveis_listagem_luxo($atts){
$atts=shortcode_atts(array('quantidade'=>4),$atts,'catalogo_imoveis_listagem_luxo');
ob_start();
$args=array('post_type'=>'imovel','posts_per_page'=>intval($atts['quantidade']));
$query=new WP_Query($args);
?>
<div class="catalogo-imoveis-listagem-luxo">
<h2><?php echo __('Listagem de Imóveis de Luxo','imoveis-sp');?></h2>
<?php if($query->have_posts()):?>
<div class="luxo-container">
<?php while($query->have_posts()):$query->the_post();
$preco=get_post_meta(get_the_ID(),'_preco_imovel',true);
?>
<div class="luxo-item">
<div class="luxo-thumb"><?php if(has_post_thumbnail()){the_post_thumbnail('large');}?></div>
<div class="luxo-info">
<h3><?php the_title();?></h3>
<p>R$ <?php echo esc_html($preco);?></p>
<a href="<?php the_permalink();?>"><?php echo __('Ver Mais','imoveis-sp');?></a>
</div>
</div>
<?php endwhile;?>
</div>
<?php else:?>
<p><?php echo __('Nenhum imóvel de luxo disponível.','imoveis-sp');?></p>
<?php endif;wp_reset_postdata();?>
</div>
<?php
return ob_get_clean();
}
public function shortcode_catalogo_imoveis_listagem_horizontal($atts){
$atts=shortcode_atts(array('quantidade'=>5),$atts,'catalogo_imoveis_listagem_horizontal');
ob_start();
$args=array('post_type'=>'imovel','posts_per_page'=>intval($atts['quantidade']));
$query=new WP_Query($args);
?>
<div class="catalogo-imoveis-listagem-horizontal">
<h2><?php echo __('Listagem Horizontal','imoveis-sp');?></h2>
<?php if($query->have_posts()):?>
<?php while($query->have_posts()):$query->the_post();
$preco=get_post_meta(get_the_ID(),'_preco_imovel',true);
?>
<div class="horizontal-item">
<div class="horizontal-thumb"><?php if(has_post_thumbnail()){the_post_thumbnail('medium');}?></div>
<div class="horizontal-info">
<h3><?php the_title();?></h3>
<p>R$ <?php echo esc_html($preco);?></p>
<a href="<?php the_permalink();?>"><?php echo __('Saiba Mais','imoveis-sp');?></a>
</div>
</div>
<?php endwhile;?>
<?php else:?>
<p><?php echo __('Nenhum imóvel encontrado.','imoveis-sp');?></p>
<?php endif;wp_reset_postdata();?>
</div>
<?php
return ob_get_clean();
}
public function shortcode_catalogo_imoveis_grid_avancado($atts){
$atts=shortcode_atts(array('quantidade'=>8),$atts,'catalogo_imoveis_grid_avancado');
ob_start();
$args=array('post_type'=>'imovel','posts_per_page'=>intval($atts['quantidade']));
$query=new WP_Query($args);
?>
<div class="catalogo-imoveis-grid-avancado">
<h2><?php echo __('Grid Avançado','imoveis-sp');?></h2>
<?php if($query->have_posts()):?>
<div class="grid-avancado-container">
<?php while($query->have_posts()):$query->the_post();?>
<div class="grid-avancado-item">
<div class="grid-avancado-thumb"><?php if(has_post_thumbnail()){the_post_thumbnail('medium');}?></div>
<div class="grid-avancado-info">
<h3><?php the_title();?></h3>
<a href="<?php the_permalink();?>"><?php echo __('Detalhes','imoveis-sp');?></a>
</div>
</div>
<?php endwhile;?>
</div>
<?php else:?>
<p><?php echo __('Nenhum imóvel no grid avançado.','imoveis-sp');?></p>
<?php endif;wp_reset_postdata();?>
</div>
<?php
return ob_get_clean();
}
public function shortcode_catalogo_imoveis_slider_moderno($atts){
$atts=shortcode_atts(array('quantidade'=>5),$atts,'catalogo_imoveis_slider_moderno');
ob_start();
$args=array('post_type'=>'imovel','posts_per_page'=>intval($atts['quantidade']));
$query=new WP_Query($args);
?>
<div class="catalogo-imoveis-slider-moderno">
<h2><?php echo __('Slider Moderno','imoveis-sp');?></h2>
<?php if($query->have_posts()):?>
<div class="slider-moderno-container">
<?php while($query->have_posts()):$query->the_post();?>
<div class="slider-moderno-item">
<?php if(has_post_thumbnail()){the_post_thumbnail('medium_large');}?>
<h3><?php the_title();?></h3>
<a href="<?php the_permalink();?>"><?php echo __('Ver Detalhes','imoveis-sp');?></a>
</div>
<?php endwhile;?>
</div>
<?php else:?>
<p><?php echo __('Nenhum imóvel para slider moderno.','imoveis-sp');?></p>
<?php endif;wp_reset_postdata();?>
</div>
<?php
return ob_get_clean();
}
public function shortcode_catalogo_imoveis_premium($atts){
$atts=shortcode_atts(array('quantidade'=>3),$atts,'catalogo_imoveis_premium');
ob_start();
$args=array('post_type'=>'imovel','posts_per_page'=>intval($atts['quantidade']));
$query=new WP_Query($args);
?>
<div class="catalogo-imoveis-premium">
<h2><?php echo __('Imóveis Premium','imoveis-sp');?></h2>
<?php if($query->have_posts()):?>
<div class="premium-container">
<?php while($query->have_posts()):$query->the_post();?>
<div class="premium-item">
<div class="premium-thumb"><?php if(has_post_thumbnail()){the_post_thumbnail('large');}?></div>
<h3><?php the_title();?></h3>
<a href="<?php the_permalink();?>"><?php echo __('Mais Detalhes','imoveis-sp');?></a>
</div>
<?php endwhile;?>
</div>
<?php else:?>
<p><?php echo __('Nenhum imóvel premium encontrado.','imoveis-sp');?></p>
<?php endif;wp_reset_postdata();?>
</div>
<?php
return ob_get_clean();
}
public function shortcode_catalogo_imoveis_minimal_cards($atts){
$atts=shortcode_atts(array('quantidade'=>6),$atts,'catalogo_imoveis_minimal_cards');
ob_start();
$args=array('post_type'=>'imovel','posts_per_page'=>intval($atts['quantidade']));
$query=new WP_Query($args);
?>
<div class="catalogo-imoveis-minimal-cards">
<h2><?php echo __('Minimal Cards','imoveis-sp');?></h2>
<?php if($query->have_posts()):?>
<div class="minimal-cards-container">
<?php while($query->have_posts()):$query->the_post();?>
<div class="minimal-card-item">
<h3><?php the_title();?></h3>
<a href="<?php the_permalink();?>"><?php echo __('Abrir','imoveis-sp');?></a>
</div>
<?php endwhile;?>
</div>
<?php else:?>
<p><?php echo __('Nenhum imóvel minimal.','imoveis-sp');?></p>
<?php endif;wp_reset_postdata();?>
</div>
<?php
return ob_get_clean();
}
public function shortcode_catalogo_imoveis_vip_carrossel($atts){
$atts=shortcode_atts(array('quantidade'=>5),$atts,'catalogo_imoveis_vip_carrossel');
ob_start();
$args=array('post_type'=>'imovel','posts_per_page'=>intval($atts['quantidade']));
$query=new WP_Query($args);
?>
<div class="catalogo-imoveis-vip-carrossel">
<h2><?php echo __('Carrossel VIP','imoveis-sp');?></h2>
<?php if($query->have_posts()):?>
<div class="vip-carrossel-container">
<?php while($query->have_posts()):$query->the_post();?>
<div class="vip-carrossel-item">
<?php if(has_post_thumbnail()){the_post_thumbnail('medium');}?>
<h3><?php the_title();?></h3>
<a href="<?php the_permalink();?>"><?php echo __('Ver Imóvel','imoveis-sp');?></a>
</div>
<?php endwhile;?>
</div>
<?php else:?>
<p><?php echo __('Nenhum imóvel VIP encontrado.','imoveis-sp');?></p>
<?php endif;wp_reset_postdata();?>
</div>
<?php
return ob_get_clean();
}
public function shortcode_catalogo_imoveis_super_lista($atts){
$atts=shortcode_atts(array('quantidade'=>10),$atts,'catalogo_imoveis_super_lista');
ob_start();
$args=array('post_type'=>'imovel','posts_per_page'=>intval($atts['quantidade']));
$query=new WP_Query($args);
?>
<div class="catalogo-imoveis-super-lista">
<h2><?php echo __('Super Lista de Imóveis','imoveis-sp');?></h2>
<?php if($query->have_posts()):?>
<ol class="super-lista">
<?php while($query->have_posts()):$query->the_post();?>
<li><a href="<?php the_permalink();?>"><?php the_title();?></a></li>
<?php endwhile;?>
</ol>
<?php else:?>
<p><?php echo __('Nenhum imóvel na super lista.','imoveis-sp');?></p>
<?php endif;wp_reset_postdata();?>
</div>
<?php
return ob_get_clean();
}
public function shortcode_catalogo_imoveis_classico($atts){
$atts=shortcode_atts(array('quantidade'=>6),$atts,'catalogo_imoveis_classico');
ob_start();
$args=array('post_type'=>'imovel','posts_per_page'=>intval($atts['quantidade']));
$query=new WP_Query($args);
?>
<div class="catalogo-imoveis-classico">
<h2><?php echo __('Estilo Clássico','imoveis-sp');?></h2>
<?php if($query->have_posts()):?>
<div class="classico-container">
<?php while($query->have_posts()):$query->the_post();?>
<div class="classico-item">
<h3><?php the_title();?></h3>
<a href="<?php the_permalink();?>"><?php echo __('Acessar','imoveis-sp');?></a>
</div>
<?php endwhile;?>
</div>
<?php else:?>
<p><?php echo __('Nenhum imóvel clássico.','imoveis-sp');?></p>
<?php endif;wp_reset_postdata();?>
</div>
<?php
return ob_get_clean();
}
public function shortcode_catalogo_imoveis_ultra_estiloso($atts){
$atts=shortcode_atts(array('quantidade'=>6),$atts,'catalogo_imoveis_ultra_estiloso');
ob_start();
$args=array('post_type'=>'imovel','posts_per_page'=>intval($atts['quantidade']));
$query=new WP_Query($args);
?>
<div class="catalogo-imoveis-ultra-estiloso">
<h2><?php echo __('Ultra Estiloso','imoveis-sp');?></h2>
<?php if($query->have_posts()):?>
<div class="ultra-estiloso-grid">
<?php while($query->have_posts()):$query->the_post();?>
<div class="ultra-estiloso-item">
<?php if(has_post_thumbnail()){the_post_thumbnail('medium_large');}?>
<h3><?php the_title();?></h3>
<a href="<?php the_permalink();?>"><?php echo __('Ver Imóvel','imoveis-sp');?></a>
</div>
<?php endwhile;?>
</div>
<?php else:?>
<p><?php echo __('Nenhum imóvel ultra estiloso.','imoveis-sp');?></p>
<?php endif;wp_reset_postdata();?>
</div>
<?php
return ob_get_clean();
}
public function shortcode_catalogo_imoveis_flat_list($atts){
$atts=shortcode_atts(array('quantidade'=>8),$atts,'catalogo_imoveis_flat_list');
ob_start();
$args=array('post_type'=>'imovel','posts_per_page'=>intval($atts['quantidade']));
$query=new WP_Query($args);
?>
<div class="catalogo-imoveis-flat-list">
<h2><?php echo __('Flat List','imoveis-sp');?></h2>
<?php if($query->have_posts()):?>
<ul class="flat-list-ul">
<?php while($query->have_posts()):$query->the_post();?>
<li class="flat-list-li"><a href="<?php the_permalink();?>"><?php the_title();?></a></li>
<?php endwhile;?>
</ul>
<?php else:?>
<p><?php echo __('Nenhum imóvel na flat list.','imoveis-sp');?></p>
<?php endif;wp_reset_postdata();?>
</div>
<?php
return ob_get_clean();
}
public function shortcode_catalogo_imoveis_deluxe_grid($atts){
$atts=shortcode_atts(array('quantidade'=>9),$atts,'catalogo_imoveis_deluxe_grid');
ob_start();
$args=array('post_type'=>'imovel','posts_per_page'=>intval($atts['quantidade']));
$query=new WP_Query($args);
?>
<div class="catalogo-imoveis-deluxe-grid">
<h2><?php echo __('Deluxe Grid','imoveis-sp');?></h2>
<?php if($query->have_posts()):?>
<div class="deluxe-grid-container">
<?php while($query->have_posts()):$query->the_post();?>
<div class="deluxe-grid-item">
<?php if(has_post_thumbnail()){the_post_thumbnail('medium');}?>
<h3><?php the_title();?></h3>
<a href="<?php the_permalink();?>"><?php echo __('Detalhes','imoveis-sp');?></a>
</div>
<?php endwhile;?>
</div>
<?php else:?>
<p><?php echo __('Nenhum imóvel no deluxe grid.','imoveis-sp');?></p>
<?php endif;wp_reset_postdata();?>
</div>
<?php
return ob_get_clean();
}
public function shortcode_catalogo_imoveis_resumo_simples($atts){
$atts=shortcode_atts(array('quantidade'=>5),$atts,'catalogo_imoveis_resumo_simples');
ob_start();
$args=array('post_type'=>'imovel','posts_per_page'=>intval($atts['quantidade']));
$query=new WP_Query($args);
?>
<div class="catalogo-imoveis-resumo-simples">
<h2><?php echo __('Resumo Simples','imoveis-sp');?></h2>
<?php if($query->have_posts()):?>
<?php while($query->have_posts()):$query->the_post();?>
<div class="resumo-simples-item">
<h3><?php the_title();?></h3>
<a href="<?php the_permalink();?>"><?php echo __('Ver','imoveis-sp');?></a>
</div>
<?php endwhile;?>
<?php else:?>
<p><?php echo __('Nenhum imóvel no resumo simples.','imoveis-sp');?></p>
<?php endif;wp_reset_postdata();?>
</div>
<?php
return ob_get_clean();
}
public function shortcode_catalogo_imoveis_extra_19($atts){
    $atts = shortcode_atts(array('quantidade'=>12), $atts, 'catalogo_imoveis_extra_19');
    ob_start();
    $paged = max(1, get_query_var('paged'), get_query_var('page'));
    $args = array(
      'post_type'      => 'imovel',
      'posts_per_page' => $atts['quantidade'],
      'paged'          => $paged
    );
    $q = new WP_Query($args);
    if($q->have_posts()):
      ?>
      <div class="catalogo-imoveis-extra-19">
        <h2>Extra 19</h2>
        <div class="extra-19-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:20px;">
        <?php
        while($q->have_posts()):
          $q->the_post();
          $post_id = get_the_ID();
          $destaque = get_post_meta($post_id,'_imovel_destaque',true);
          $preco = get_post_meta($post_id,'_preco_imovel',true);
          $area = get_post_meta($post_id,'_area_imovel',true);
          $quartos = get_post_meta($post_id,'_quartos_imovel',true);
          $banheiros = get_post_meta($post_id,'_banheiros_imovel',true);
          $vagas = get_post_meta($post_id,'_vagas_imovel',true);
          ?>
          <div class="extra-19-card" style="border:1px solid #ccc;border-radius:6px;overflow:hidden;position:relative;">
            <?php if($destaque==='yes'):?>
            <div style="position:absolute;top:0;left:0;background:#3498db;color:#fff;padding:5px 10px;border-bottom-right-radius:6px;">Destaque</div>
            <?php endif;?>
            <div class="extra-19-img-wrap" style="width:100%;height:200px;overflow:hidden;">
              <?php if(has_post_thumbnail()):?>
                <?php the_post_thumbnail('medium',array('style'=>'width:100%;height:100%;object-fit:cover;'));?>
              <?php else:?>
                <div style="width:100%;height:100%;background:#eee;display:flex;align-items:center;justify-content:center;">Sem Imagem</div>
              <?php endif;?>
            </div>
            <div class="extra-19-info" style="padding:10px;">
              <h3 style="margin:0 0 8px;"><?php the_title();?></h3>
              <div class="extra-19-metros" style="font-size:0.9rem;color:#666;">
                <?php if($area):?>Área: <?php echo esc_html($area);?> m²<br><?php endif;?>
                <?php if($quartos):?>Quartos: <?php echo esc_html($quartos);?><br><?php endif;?>
                <?php if($banheiros):?>Banheiros: <?php echo esc_html($banheiros);?><br><?php endif;?>
                <?php if($vagas):?>Vagas: <?php echo esc_html($vagas);?><br><?php endif;?>
              </div>
              <div class="extra-19-preco" style="margin:8px 0;font-weight:bold;font-size:1rem;">
                <?php if($preco):?>R$ <?php echo esc_html($preco);?><?php else:?>Consulte<?php endif;?>
              </div>
              <a href="<?php the_permalink();?>" class="extra-19-botao" style="display:inline-block;padding:8px 14px;background:#2ecc71;color:#fff;border-radius:4px;text-decoration:none;">Ver Mais</a>
            </div>
          </div>
          <?php
        endwhile;
        ?>
        </div>
        <div class="extra-19-paginacao" style="margin-top:20px;text-align:center;">
        <?php
        echo paginate_links(array(
          'total'=>$q->max_num_pages,
          'current'=>$paged,
          'format'=>'?paged=%#%',
          'show_all'=>false,
          'type'=>'plain'
        ));
        ?>
        </div>
      </div>
      <?php
    else:
      ?>
      <p>Nenhum imóvel no extra 19</p>
      <?php
    endif;
    wp_reset_postdata();
    return ob_get_clean();
  }
  
  public function shortcode_catalogo_imoveis_extra_20($atts){
    $atts = shortcode_atts(array('quantidade'=>12), $atts, 'catalogo_imoveis_extra_20');
    ob_start();
    $paged = max(1, get_query_var('paged'), get_query_var('page'));
    $args = array(
      'post_type'=>'imovel',
      'posts_per_page'=>$atts['quantidade'],
      'paged'=>$paged
    );
    $q = new WP_Query($args);
    if($q->have_posts()):
      ?>
      <div class="catalogo-imoveis-extra-20">
        <h2>Extra 20</h2>
        <div class="extra-20-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:20px;">
        <?php
        while($q->have_posts()):
          $q->the_post();
          $post_id=get_the_ID();
          $destaque=get_post_meta($post_id,'_imovel_destaque',true);
          $preco=get_post_meta($post_id,'_preco_imovel',true);
          $area=get_post_meta($post_id,'_area_imovel',true);
          $quartos=get_post_meta($post_id,'_quartos_imovel',true);
          $banheiros=get_post_meta($post_id,'_banheiros_imovel',true);
          $vagas=get_post_meta($post_id,'_vagas_imovel',true);
          ?>
          <div class="extra-20-card" style="border:1px solid #ccc;border-radius:6px;overflow:hidden;position:relative;">
            <?php if($destaque==='yes'):?>
            <div style="position:absolute;top:0;left:0;background:#2980b9;color:#fff;padding:5px 10px;border-bottom-right-radius:6px;">Destaque</div>
            <?php endif;?>
            <div class="extra-20-img-wrap" style="width:100%;height:200px;overflow:hidden;">
              <?php if(has_post_thumbnail()):?>
                <?php the_post_thumbnail('medium',array('style'=>'width:100%;height:100%;object-fit:cover;'));?>
              <?php else:?>
                <div style="width:100%;height:100%;background:#eee;display:flex;align-items:center;justify-content:center;">Sem Imagem</div>
              <?php endif;?>
            </div>
            <div class="extra-20-info" style="padding:10px;">
              <h3 style="margin:0 0 8px;"><?php the_title();?></h3>
              <div class="extra-20-metros" style="font-size:0.9rem;color:#666;">
                <?php if($area):?>Área: <?php echo esc_html($area);?> m²<br><?php endif;?>
                <?php if($quartos):?>Quartos: <?php echo esc_html($quartos);?><br><?php endif;?>
                <?php if($banheiros):?>Banheiros: <?php echo esc_html($banheiros);?><br><?php endif;?>
                <?php if($vagas):?>Vagas: <?php echo esc_html($vagas);?><br><?php endif;?>
              </div>
              <div class="extra-20-preco" style="margin:8px 0;font-weight:bold;font-size:1rem;">
                <?php if($preco):?>R$ <?php echo esc_html($preco);?><?php else:?>Consulte<?php endif;?>
              </div>
              <a href="<?php the_permalink();?>" class="extra-20-botao" style="display:inline-block;padding:8px 14px;background:#27ae60;color:#fff;border-radius:4px;text-decoration:none;">Ver Mais</a>
            </div>
          </div>
          <?php
        endwhile;
        ?>
        </div>
        <div class="extra-20-paginacao" style="margin-top:20px;text-align:center;">
        <?php
        echo paginate_links(array(
          'total'=>$q->max_num_pages,
          'current'=>$paged,
          'format'=>'?paged=%#%',
          'show_all'=>false,
          'type'=>'plain'
        ));
        ?>
        </div>
      </div>
      <?php
    else:
      ?>
      <p>Nenhum imóvel no extra 20</p>
      <?php
    endif;
    wp_reset_postdata();
    return ob_get_clean();
  }
  
public function shortcode_catalogo_imoveis_spotlight($atts){
$atts=shortcode_atts(array('quantidade'=>3),$atts,'catalogo_imoveis_spotlight');
ob_start();
$args=array('post_type'=>'imovel','posts_per_page'=>intval($atts['quantidade']));
$query=new WP_Query($args);
?>
<div class="catalogo-imoveis-spotlight">
<h2><?php echo __('Spotlight','imoveis-sp');?></h2>
<?php if($query->have_posts()):?>
<div class="spotlight-container">
<?php while($query->have_posts()):$query->the_post();?>
<div class="spotlight-item">
<?php if(has_post_thumbnail()){the_post_thumbnail('large');}?>
<h3><?php the_title();?></h3>
<a href="<?php the_permalink();?>"><?php echo __('Mais','imoveis-sp');?></a>
</div>
<?php endwhile;?>
</div>
<?php else:?>
<p><?php echo __('Nenhum imóvel em spotlight.','imoveis-sp');?></p>
<?php endif;wp_reset_postdata();?>
</div>
<?php
return ob_get_clean();
}
public function shortcode_catalogo_imoveis_dinamico($atts){
$atts=shortcode_atts(array('quantidade'=>6),$atts,'catalogo_imoveis_dinamico');
ob_start();
$args=array('post_type'=>'imovel','posts_per_page'=>intval($atts['quantidade']));
$query=new WP_Query($args);
?>
<div class="catalogo-imoveis-dinamico">
<h2><?php echo __('Layout Dinâmico','imoveis-sp');?></h2>
<?php if($query->have_posts()):?>
<div class="dinamico-grid">
<?php while($query->have_posts()):$query->the_post();?>
<div class="dinamico-item">
<?php if(has_post_thumbnail()){the_post_thumbnail('medium');}?>
<h3><?php the_title();?></h3>
<a href="<?php the_permalink();?>"><?php echo __('Ver','imoveis-sp');?></a>
</div>
<?php endwhile;?>
</div>
<?php else:?>
<p><?php echo __('Nenhum imóvel no layout dinâmico.','imoveis-sp');?></p>
<?php endif;wp_reset_postdata();?>
</div>
<?php
return ob_get_clean();
}
public function forcar_template_single_imovel($single_template){
global $post;
if($post->post_type==='imovel'){
$template_plugin=plugin_dir_path(__FILE__).'templates/single-imovel.php';
if(file_exists($template_plugin)){return $template_plugin;}
}
return $single_template;
}
public function forcar_template_archive_imovel($archive_template){
if(is_post_type_archive('imovel')){
$template_plugin=plugin_dir_path(__FILE__).'templates/archive-imovel.php';
if(file_exists($template_plugin)){return $template_plugin;}
}
return $archive_template;
}
public function carregar_assets(){
wp_enqueue_style('imoveis-sp-css',plugin_dir_url(__FILE__).'css/imoveis-sp.css',array(),self::VERSION);
wp_enqueue_style('font-awesome','https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css',array(),'6.0.0');
wp_enqueue_script('imoveis-sp-js',plugin_dir_url(__FILE__).'js/imoveis-sp.js',array('jquery'),self::VERSION,true);
$api_key=get_option('imoveis_sp_google_api_key','');
if(!empty($api_key)){
wp_enqueue_script('google-maps-api','https://maps.googleapis.com/maps/api/js?key='.$api_key.'&libraries=places',array(),null,true);
}
}
public function carregar_assets_admin($hook){
global $post_type;
if(($hook==='post.php'||$hook==='post-new.php')&&$post_type==='imovel'){
wp_enqueue_style('imoveis-sp-admin-css',plugin_dir_url(__FILE__).'css/imoveis-sp-admin.css',array(),self::VERSION);
wp_enqueue_media();
wp_enqueue_script('imoveis-sp-admin-js',plugin_dir_url(__FILE__).'js/imoveis-sp-admin.js',array('jquery','wp-util','wp-api'),self::VERSION,true);
$api_key=get_option('imoveis_sp_google_api_key','');
wp_localize_script('imoveis-sp-admin-js','ImoveisSPAdminVars',array('googleApiKey'=>$api_key,'ajaxUrl'=>admin_url('admin-ajax.php')));
}
if($hook==='settings_page_imoveis-sp-config'){
wp_enqueue_style('imoveis-sp-admin-css',plugin_dir_url(__FILE__).'css/imoveis-sp-admin.css',array(),self::VERSION);
}
}
public function adicionar_pagina_config(){
add_options_page(__('Config Imóveis SP','imoveis-sp'),__('Imóveis SP Config','imoveis-sp'),'manage_options','imoveis-sp-config',array($this,'pagina_config_callback'));
}
public function pagina_config_callback(){
?>
<div class="wrap">
<h1><?php echo __('Configurações do Plugin Imóveis SP','imoveis-sp');?></h1>
<form method="post" action="options.php">
<?php
settings_fields('imoveis_sp_config_group');
do_settings_sections('imoveis-sp-config');
submit_button();
?>
</form>
</div>
<?php
}
public function registrar_config(){
register_setting('imoveis_sp_config_group','imoveis_sp_google_api_key',array('type'=>'string','sanitize_callback'=>'sanitize_text_field','default'=>''));
register_setting('imoveis_sp_config_group','imoveis_sp_whatsapp',array('type'=>'string','sanitize_callback'=>'sanitize_text_field','default'=>''));
add_settings_section('imoveis_sp_config_section',__('Configurações de API e Contato','imoveis-sp'),function(){echo'<p>'.__('Insira sua chave da API do Google para habilitar o autocomplete e o número de WhatsApp para contato.','imoveis-sp').'</p>';},'imoveis-sp-config');
add_settings_field('imoveis_sp_google_api_key_field',__('Chave da API do Google:','imoveis-sp'),array($this,'campo_api_key_callback'),'imoveis-sp-config','imoveis_sp_config_section');
add_settings_field('imoveis_sp_whatsapp_field',__('WhatsApp (somente números e código do país):','imoveis-sp'),array($this,'campo_whatsapp_callback'),'imoveis-sp-config','imoveis_sp_config_section');
}
public function campo_api_key_callback(){
$value=get_option('imoveis_sp_google_api_key','');
echo'<input type="text" name="imoveis_sp_google_api_key" value="'.esc_attr($value).'" size="50">';
}
public function campo_whatsapp_callback(){
$value=get_option('imoveis_sp_whatsapp','');
echo'<input type="text" name="imoveis_sp_whatsapp" value="'.esc_attr($value).'" size="20">';
echo'<p class="description">'.__('Exemplo: 5511999999999','imoveis-sp').'</p>';
}
public function corrigir_conflito_emojis(){
remove_action('wp_head','print_emoji_detection_script',7);
remove_action('admin_print_scripts','print_emoji_detection_script');
remove_action('wp_print_styles','print_emoji_styles');
remove_action('admin_print_styles','print_emoji_styles');
add_action('wp_head','print_emoji_detection_script',7);
add_action('admin_print_scripts','print_emoji_detection_script');
add_action('wp_print_styles','print_emoji_styles');
add_action('admin_print_styles','print_emoji_styles');
global $wpdb;
$wpdb->query("SET NAMES 'utf8mb4'");
}
}
new ImoveisSPPluginMegaVersion();
