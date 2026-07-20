{* Slider produktów (Slick) *}
<section class="dfc-slider-section">
  <div class="df-dots-wrap">
    <div class="df-slider-dots df-slider-dots--dfcollection"></div>
  </div>

  <div class="dfc-products" data-slider-infinite="{$dfc_slider_infinite|default:1|intval}">
    {foreach from=$dfc_products item=product}
      {include file='catalog/_partials/miniatures/product.tpl' product=$product df_slider_lazy=true}
    {/foreach}
  </div>

  <a class="section button-transition dfc-all-link dfc-all-link-slider"
     href="{$dfc_category_link}"
     aria-label="Zobacz wszystkie produkty {$dfc_title|escape}">
    ZOBACZ WSZYSTKIE PRODUKTY&nbsp;<span class="dfc-cat-name">{$dfc_title|escape}</span>
  </a>
</section>