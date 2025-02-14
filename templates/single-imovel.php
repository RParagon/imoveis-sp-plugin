<?php
/* Template Name: Imóvel Premium – Versão 3.2 - Ajustado */
get_header();
if(!defined('ABSPATH')){exit;}
if(have_posts()):
while(have_posts()):the_post();
$post_id=get_the_ID();
$meta_transient_key="imovel_{$post_id}_meta_v3_2";
$meta=get_transient($meta_transient_key);
if($meta===false){
  $raw_meta=get_post_meta($post_id);
  $meta=array_map(function($a){return maybe_unserialize($a[0]);},$raw_meta);
  set_transient($meta_transient_key,$meta,HOUR_IN_SECONDS);
}
$gmap_api_key=esc_attr(get_option('imoveis_sp_google_api_key'));
$whatsapp_raw=get_option('imoveis_sp_whatsapp','');
$whatsapp=preg_replace('/[^0-9]/','',$whatsapp_raw);
$title=esc_html(get_the_title());
$tipo=esc_html($meta['_tipo_imovel']??'Não especificado');
$endereco_raw=$meta['_endereco_imovel']??'';
$bairro_raw=$meta['_bairro_imovel']??'';
$cidade_raw=$meta['_cidade_imovel']??'SP';
$endereco_sanitized=esc_html(implode(', ',array_filter([$endereco_raw,$bairro_raw,$cidade_raw])));
$preco_val=(isset($meta['_preco_imovel'])&&is_numeric($meta['_preco_imovel']))?number_format((float)$meta['_preco_imovel'],2,',','.'):'Consulte';
$area_val=(int)($meta['_area_imovel']??0);
$quartos_val=(int)($meta['_quartos_imovel']??0);
$banheiros_val=(int)($meta['_banheiros_imovel']??0);
$suites_val=(int)($meta['_suites_imovel']??0);
$vagas_val=(int)($meta['_vagas_imovel']??0);
$lat=(float)($meta['_latitude_imovel']??0);
$lng=(float)($meta['_longitude_imovel']??0);
$gallery_raw=$meta['_gallery_imovel']??[];
$gallery=array_map('esc_url',$gallery_raw);
if(empty($gallery)){
  if(has_post_thumbnail($post_id)){
    $featured_img_url=wp_get_attachment_url(get_post_thumbnail_id($post_id));
    if($featured_img_url){$gallery[]=$featured_img_url;}
  }
}
$video_url=esc_url($meta['_video_imovel']??'');
$descricao_raw=$meta['_descricao_imovel']??'';
$comodidades_raw=$meta['_comodidades_imovel']??'';
$comodidades_list=array_map('trim',explode(',',$comodidades_raw));
$comodidades_list=array_filter($comodidades_list);
$mensagem=urlencode("Olá, gostaria de informações sobre o imóvel: ".$title." - Endereço: ".$endereco_raw);
$whatsapp_link="https://wa.me/{$whatsapp}?text={$mensagem}";
$schema_data=[
"@context"=>"https://schema.org",
"@type"=>"RealEstateListing",
"name"=>$title,
"description"=>wp_strip_all_tags($descricao_raw),
"image"=>$gallery,
"address"=>[
"@type"=>"PostalAddress",
"streetAddress"=>$endereco_raw,
"addressLocality"=>$cidade_raw,
"addressRegion"=>"SP"
],
"offers"=>[
"@type"=>"Offer",
"price"=>($meta['_preco_imovel']??0),
"priceCurrency"=>"BRL"
]
];
?>
<main class="imovel-premium-v3-2" itemscope itemtype="https://schema.org/RealEstateListing">
<script type="application/ld+json"><?php echo json_encode($schema_data,JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);?></script>
<?php
$back_url=wp_get_referer();
if(!$back_url){$back_url=get_post_type_archive_link('imovel');}
?>
<div class="breadcrumb-container">
  <a href="<?php echo esc_url($back_url);?>" class="breadcrumb-back">&larr; Voltar</a>
  <span class="breadcrumb-path">Imóveis &raquo; <?php echo ucfirst($tipo);?> &raquo; <?php echo $title;?></span>
</div>
<div class="imovel-gallery-wrapper">
  <div class="imovel-gallery-main">
    <div class="imovel-gallery-large-image">
      <?php if(!empty($gallery)):?>
      <img src="<?php echo $gallery[0];?>" alt="<?php echo esc_attr($title);?>" class="large-image"/>
      <?php else:?>
      <div class="no-image">Sem Imagem</div>
      <?php endif;?>
    </div>
    <div class="imovel-gallery-thumbs">
      <?php foreach($gallery as $img):?>
      <div class="thumb-item"><img src="<?php echo $img;?>" alt="Thumb"/></div>
      <?php endforeach;?>
    </div>
  </div>
  <div class="imovel-info-sidebar">
    <h1 class="imovel-title"><?php echo $title;?></h1>
    <div class="imovel-price-type">
      <span class="imovel-price">R$ <?php echo $preco_val;?></span>
      <span class="imovel-type"><?php echo $tipo;?></span>
    </div>
    <div class="imovel-details-grid">
      <?php if($area_val>0):?>
      <div class="detail-item"><span class="detail-label">Área:</span><span class="detail-value"><?php echo $area_val;?> m²</span></div>
      <?php endif;?>
      <?php if($quartos_val>0):?>
      <div class="detail-item"><span class="detail-label">Quartos:</span><span class="detail-value"><?php echo $quartos_val;?></span></div>
      <?php endif;?>
      <?php if($banheiros_val>0):?>
      <div class="detail-item"><span class="detail-label">Banheiros:</span><span class="detail-value"><?php echo $banheiros_val;?></span></div>
      <?php endif;?>
      <?php if($suites_val>0):?>
      <div class="detail-item"><span class="detail-label">Suítes:</span><span class="detail-value"><?php echo $suites_val;?></span></div>
      <?php endif;?>
      <?php if($vagas_val>0):?>
      <div class="detail-item"><span class="detail-label">Vagas:</span><span class="detail-value"><?php echo $vagas_val;?></span></div>
      <?php endif;?>
    </div>
    <div class="imovel-address-standalone">
      <strong>Endereço:</strong> <?php echo $endereco_sanitized;?>
    </div>
    <div class="imovel-buttons">
      <?php if($video_url):?>
      <button class="tour-button" data-video="<?php echo $video_url;?>">Tour Virtual (Street View)</button>
      <?php endif;?>
      <?php if($whatsapp):?>
      <a href="<?php echo esc_url($whatsapp_link);?>" class="whatsapp-button" target="_blank" rel="noopener">Falar no WhatsApp</a>
      <?php endif;?>
    </div>
  </div>
</div>
<div class="imovel-description-section">
  <h2 class="section-title">Descrição Completa</h2>
  <div class="imovel-description-content"><?php echo wp_kses_post(wpautop($descricao_raw));?></div>
  <?php if(!empty($comodidades_list)):?>
  <div class="imovel-comodidades">
    <h3>Comodidades</h3>
    <ul>
      <?php foreach($comodidades_list as $com):?>
      <li><?php echo esc_html($com);?></li>
      <?php endforeach;?>
    </ul>
  </div>
  <?php endif;?>
</div>
<div class="imovel-map-section">
  <?php if($gmap_api_key&&$lat!==0):?>
  <iframe width="100%" height="450" frameborder="0" style="border:0" src="https://www.google.com/maps/embed/v1/view?key=<?php echo $gmap_api_key;?>&center=<?php echo $lat;?>,<?php echo $lng;?>&zoom=16&maptype=roadmap" allowfullscreen loading="lazy"></iframe>
  <?php else:?>
  <div class="no-map">Mapa Indisponível</div>
  <?php endif;?>
</div>
<div class="imovel-similares-section">
  <h2 class="section-title">Imóveis Semelhantes</h2>
  <?php
  $similar_args=array(
    'post_type'=>'imovel',
    'posts_per_page'=>4,
    'post__not_in'=>array($post_id),
    'meta_query'=>array(
      array('key'=>'_tipo_imovel','value'=>$tipo,'compare'=>'LIKE')
    )
  );
  $similar_query=new WP_Query($similar_args);
  if($similar_query->have_posts()):?>
  <div class="similares-grid">
    <?php while($similar_query->have_posts()):$similar_query->the_post();
    $sim_id=get_the_ID();
    $sim_title=esc_html(get_the_title());
    $sim_preco_raw=get_post_meta($sim_id,'_preco_imovel',true);
    $sim_preco=(is_numeric($sim_preco_raw))?number_format((float)$sim_preco_raw,2,',','.'):'Consulte';
    $sim_thumb='';
    if(has_post_thumbnail($sim_id)){
      $sim_thumb=wp_get_attachment_url(get_post_thumbnail_id($sim_id));
    }
    ?>
    <div class="similar-item">
      <?php if($sim_thumb):?>
      <img src="<?php echo esc_url($sim_thumb);?>" alt="<?php echo esc_attr($sim_title);?>" class="similar-thumb"/>
      <?php else:?>
      <div class="no-thumb-similar">Sem Imagem</div>
      <?php endif;?>
      <div class="similar-info">
        <h4 class="similar-title"><?php echo $sim_title;?></h4>
        <span class="similar-price">R$ <?php echo $sim_preco;?></span>
        <a href="<?php the_permalink();?>" class="similar-link">Ver Detalhes</a>
      </div>
    </div>
    <?php endwhile;?>
  </div>
  <?php else:?>
  <div class="no-similar">Nenhum imóvel semelhante encontrado.</div>
  <?php endif;wp_reset_postdata();?>
</div>
</main>
<div id="tour-modal" class="tour-modal">
  <div class="tour-modal-content">
    <span class="tour-close">&times;</span>
    <div class="tour-video-area"></div>
  </div>
</div>
<style>
.imovel-premium-v3-2{margin-top:200px;max-width:1400px;margin-left:auto;margin-right:auto;padding:0 80px;font-family:Arial,sans-serif;color:#333;box-sizing:border-box;}
.imovel-premium-v3-2 *{box-sizing:inherit;}
.breadcrumb-container{margin-top:20px;margin-bottom:20px;display:flex;flex-direction:column;gap:5px;}
.breadcrumb-back{display:inline-block;background:#f5f5f5;color:#333;padding:8px 16px;border-radius:4px;text-decoration:none;transition:0.3s;max-width:100px;text-align:center;}
.breadcrumb-back:hover{background:#e1e1e1;transform:translateY(-2px);}
.breadcrumb-path{font-size:0.9rem;color:#777;}
.imovel-gallery-wrapper{display:grid;grid-template-columns:1.5fr 1fr;gap:40px;align-items:start;margin-bottom:60px;}
@media(max-width:992px){.imovel-gallery-wrapper{grid-template-columns:1fr;gap:20px;}}
.imovel-gallery-main{display:flex;flex-direction:column;gap:20px;}
.imovel-gallery-large-image{width:100%;height:600px;overflow:hidden;border-radius:6px;position:relative;}
.imovel-gallery-large-image img.large-image{width:100%;height:100%;object-fit:cover;}
.no-image{width:100%;height:100%;background:#ddd;display:flex;justify-content:center;align-items:center;font-size:1.2rem;color:#555;}
.imovel-gallery-thumbs{display:flex;gap:10px;overflow-x:auto;}
.thumb-item{width:120px;height:80px;flex:0 0 auto;cursor:pointer;border-radius:4px;overflow:hidden;position:relative;}
.thumb-item img{width:100%;height:100%;object-fit:cover;transition:0.3s;}
.thumb-item:hover img{opacity:0.8;transform:scale(1.05);}
.imovel-info-sidebar{background:#fff;border-radius:6px;box-shadow:0 2px 5px rgba(0,0,0,0.1);padding:20px;display:flex;flex-direction:column;gap:20px;}
.imovel-title{font-size:1.8rem;font-weight:bold;margin:0;}
.imovel-price-type{display:flex;flex-direction:column;gap:8px;}
.imovel-price{font-size:2rem;color:#2c3e50;font-weight:bold;}
.imovel-type{font-size:1.2rem;color:#555;}
.imovel-details-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:10px;}
.detail-item{background:#f9f9f9;border-radius:4px;padding:10px;display:flex;flex-direction:column;gap:5px;}
.detail-label{font-size:0.8rem;color:#666;}
.detail-value{font-size:1rem;font-weight:bold;}
.imovel-address-standalone{font-size:0.95rem;color:#444;}
.imovel-buttons{display:flex;flex-direction:column;gap:10px;}
.tour-button{background:#3498db;color:#fff;padding:12px 20px;border:none;border-radius:4px;cursor:pointer;transition:0.3s;font-size:1rem;display:inline-flex;align-items:center;justify-content:center;gap:8px;font-weight:500;}
.tour-button:hover{background:#2d80b0;transform:translateY(-2px);}
.whatsapp-button{display:inline-flex;align-items:center;justify-content:center;gap:8px;font-weight:500;background:#25d366;color:#fff;padding:12px 20px;border-radius:4px;text-decoration:none;transition:0.3s;}
.whatsapp-button:hover{background:#1b9f50;transform:translateY(-2px);}
.imovel-description-section{margin-bottom:60px;}
.section-title{font-size:1.6rem;margin-bottom:20px;font-weight:bold;color:#333;}
.imovel-description-content{line-height:1.6;font-size:1rem;color:#444;}
.imovel-comodidades{margin-top:30px;}
.imovel-comodidades h3{font-size:1.2rem;margin-bottom:10px;color:#333;}
.imovel-comodidades ul{list-style:none;display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;}
.imovel-comodidades li{background:#f0f0f0;padding:10px;border-radius:4px;font-size:0.9rem;color:#333;}
.imovel-map-section{margin-bottom:60px;}
.no-map{background:#eee;padding:40px;text-align:center;border-radius:4px;font-size:1rem;color:#666;}
.imovel-similares-section{margin-bottom:80px;}
.similares-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;}
.similar-item{background:#fff;border-radius:6px;box-shadow:0 2px 5px rgba(0,0,0,0.08);overflow:hidden;transition:0.3s;}
.similar-item:hover{transform:translateY(-3px);}
.similar-thumb{width:100%;height:180px;object-fit:cover;}
.similar-info{padding:10px;display:flex;flex-direction:column;gap:5px;}
.similar-title{font-size:1rem;margin:0;}
.similar-price{font-size:0.9rem;color:#666;}
.similar-link{display:inline-block;margin-top:5px;background:#3498db;color:#fff;padding:8px 14px;border-radius:4px;text-decoration:none;transition:0.3s;}
.similar-link:hover{background:#2d80b0;}
.no-similar{background:#f2f2f2;padding:20px;text-align:center;border-radius:4px;color:#666;}
.tour-modal{display:none;position:fixed;z-index:9999;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,0.7);align-items:center;justify-content:center;}
.tour-modal-content{background:#fff;position:relative;max-width:900px;width:90%;padding:20px;border-radius:8px;margin:0 auto;}
.tour-close{position:absolute;top:10px;right:20px;font-size:1.5rem;cursor:pointer;}
.tour-video-area{width:100%;height:500px;}
@media(max-width:768px){
.imovel-premium-v3-2{padding:0 20px;margin-top:200px;}
.imovel-gallery-large-image{height:300px;}
.imovel-description-content{font-size:0.95rem;}
}
</style>
<script>
document.addEventListener("DOMContentLoaded",function(){
const largeImg=document.querySelector(".imovel-gallery-large-image img.large-image");
const thumbItems=document.querySelectorAll(".imovel-gallery-thumbs .thumb-item img");
if(thumbItems.length>0){
  thumbItems.forEach(t=>{
    t.addEventListener("click",function(){
      if(largeImg){largeImg.src=this.src;}
    });
  });
}
const tourBtn=document.querySelector(".tour-button");
const tourModal=document.getElementById("tour-modal");
const tourClose=document.querySelector(".tour-close");
const tourVideoArea=document.querySelector(".tour-video-area");
if(tourBtn){
  tourBtn.addEventListener("click",function(){
    const vid=this.getAttribute("data-video");
    if(vid){
      tourModal.style.display="flex";
      tourVideoArea.innerHTML='<iframe src="'+vid+'" width="100%" height="100%" frameborder="0" allowfullscreen></iframe>';
    }
  });
}
if(tourClose){
  tourClose.addEventListener("click",function(){
    tourModal.style.display="none";
    tourVideoArea.innerHTML="";
  });
}
window.addEventListener("click",function(e){
  if(e.target===tourModal){
    tourModal.style.display="none";
    tourVideoArea.innerHTML="";
  }
});
});
</script>
<?php
endwhile;endif;
get_footer();
