<div id="dfcollection" class="dfc">

  {if $dfc_heading_link}
    <h2 class="dfc-heading">
      <a href="{$dfc_heading_link|escape:'html'}" class="dfc-heading-link">
        {$dfc_heading|default:'KOLEKCJE'|escape}
      </a>
    </h2>
  {else}
    <h2 class="dfc-heading">{$dfc_heading|default:'KOLEKCJE'|escape}</h2>
  {/if}
  
  <nav class="dfc-tabs" role="tablist" aria-label="Wybierz kolekcję">
    {foreach from=$dfc_collections item=col}
      {assign var=isActive value=($col.id_category == $dfc_collections[0].id_category)}
      <button 
        id="dfc-tab-{$col.id_category|intval}"
        class="dfc-tab{if $isActive} active{/if}" 
        type="button"
        role="tab"
        aria-selected="{if $isActive}true{else}false{/if}"
        aria-controls="dfc-panel"
        tabindex="{if $isActive}0{else}-1{/if}"
        data-cat="{$col.id_category|intval}">
        {if $col.title}{$col.title|escape}{else}{$col.cat_name|escape}{/if}
      </button>
    {/foreach}
  </nav>

  <div class="dfc-tabs-scrollbar">
    <div class="dfc-tabs-scrollbar-track">
      <div class="dfc-tabs-scrollbar-thumb"></div>
    </div>
  </div>
  
  <section id="dfc-panel" role="tabpanel" aria-labelledby="dfc-tab-{$dfc_collections[0].id_category|intval}">
    <div id="dfc-main">
       {include file='module:dfcollection/views/templates/hook/partials/main.tpl'}
    </div>

    <div class="dfc-slider-heading" aria-live="polite">
      <span class="dfc-slider-label">produkty z kolekcji</span>
      <span class="dfc-slider-title">{$dfc_title|escape}</span>
    </div>

    <div class="dfc-slider-meta">
      <span class="dfc-slider-count">
        {$dfc_products_count|intval} produktów
      </span>
    </div>

    <div id="dfc-slider">
      {include file='module:dfcollection/views/templates/hook/partials/slider.tpl'}
    </div>
  </section>

  <ul id="dfc-ids" class="hidden">
    {foreach from=$dfc_collections item=col}
      <li
        data-cat="{$col.id_category|intval}"
        data-img="{$col.image_url|escape:'html'}"
        data-img-mobile="{$col.image_url_mobile|escape:'html'}"
        data-img-xs="{$col.image_url_xs|escape:'html'}"
        data-img-compare="{$col.image_compare_url|default:''|escape:'html'}"
        data-compare-start="{$col.compare_start_percent|default:50|intval}"
        data-compare-label="{$col.compare_label|default:''|escape:'html'}"
        data-title="{if $col.title}{$col.title|escape}{else}{$col.cat_name|escape}{/if}"
        data-link="{$link->getCategoryLink($col.id_category)|escape:'html'}">
      </li>
    {/foreach}
  </ul>

  <div class="dfc-sticky-bar" data-dfc-sticky-bar aria-hidden="true">
    <div class="dfc-sticky-bar__inner">

      <div class="dfc-sticky-bar__left">
        <div class="dfc-sticky-bar__tabs-wrap">
          <nav
            class="dfc-sticky-tabs"
            data-dfc-sticky-tabs
            role="tablist"
            aria-label="Wybierz kolekcję - sticky bar"
          >
            {foreach from=$dfc_collections item=col}
              {assign var=isActiveSticky value=($col.id_category == $dfc_collections[0].id_category)}
              <button
                id="dfc-sticky-tab-{$col.id_category|intval}"
                class="dfc-sticky-tab{if $isActiveSticky} active{/if}"
                type="button"
                role="tab"
                aria-selected="{if $isActiveSticky}true{else}false{/if}"
                tabindex="{if $isActiveSticky}0{else}-1{/if}"
                data-cat="{$col.id_category|intval}"
              >
                {if $col.title}{$col.title|escape}{else}{$col.cat_name|escape}{/if}
              </button>
            {/foreach}
          </nav>
        </div>

        <div class="dfc-sticky-tabs-scrollbar" data-dfc-sticky-scrollbar>
          <div class="dfc-sticky-tabs-scrollbar-track">
            <div class="dfc-sticky-tabs-scrollbar-thumb"></div>
          </div>
        </div>
      </div>

      <div class="dfc-sticky-bar__nav">
        <div class="dfc-sticky-bar__counter" aria-hidden="true">
          <span class="dfc-sticky-bar__current">1</span>
          <span class="dfc-sticky-bar__separator"></span>
          <span class="dfc-sticky-bar__total">{$dfc_total_collections|intval}</span>
        </div>

        <div class="dfc-sticky-bar__arrows">
          <button
            type="button"
            class="dfc-sticky-bar__arrow dfc-sticky-prev"
            aria-label="Poprzednia kolekcja"
          >
            <svg width="40" height="40" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
              <path d="M18 12H6M6 12L11 17M6 12L11 7"
                    stroke="#232323" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>

          <button
            type="button"
            class="dfc-sticky-bar__arrow dfc-sticky-next"
            aria-label="Następna kolekcja"
          >
            <svg width="40" height="40" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
              <path d="M6 12H18M18 12L13 7M18 12L13 17"
                    stroke="#232323" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
        </div>
      </div>

    </div>
  </div>
</div>
