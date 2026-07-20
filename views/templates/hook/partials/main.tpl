
<div class="dfc-main-grid" data-cat="{$dfc_current_cat_id|intval}">
    <div class="dfc-left-wrap">
      <div class="dfc-left">

        {if $dfc_image_url}

          {if $dfc_image_compare_url}
            <div
              class="dfc-compare"
              data-start="{$dfc_compare_start_percent|intval}"
              data-label="{$dfc_compare_label|escape:'html'}"
            >
              <div class="dfc-compare-before">
                <picture class="dfc-pic-current">
                  {if $dfc_image_url_xs}
                    <source media="(max-width: 499.98px)" srcset="{$dfc_image_url_xs|escape:'html'}">
                  {/if}
                  {if $dfc_image_url_mobile}
                    <source media="(max-width: 767.98px)" srcset="{$dfc_image_url_mobile|escape:'html'}">
                  {/if}
                  <img class="dfc-img current" src="{$dfc_image_url|escape:'html'}" alt="" loading="lazy">
                </picture>
              </div>

              <div
                class="dfc-compare-after"
                style="clip-path: inset(0 0 0 {$dfc_compare_start_percent|intval}%);"
              >
                <img src="{$dfc_image_compare_url|escape:'html'}" alt="" loading="lazy">
              </div>

              <input
                type="range"
                class="dfc-compare-range"
                min="0"
                max="100"
                step="0.01"
                value="{$dfc_compare_start_percent|intval}"
                aria-label="Porównanie zdjęć kolekcji"
              >

              <div
                class="dfc-compare-handle"
                style="left: {$dfc_compare_start_percent|intval}%"
                aria-hidden="true"
              >
                <span class="dfc-compare-line"></span>
                <span class="dfc-compare-grip"></span>
              </div>

              {if $dfc_compare_label|trim}
                <div class="dfc-compare-label">
                  <span class="dfc-compare-label__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" focusable="false">
                      <path
                        d="M7 8L3 12L7 16M17 8L21 12L17 16"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.9"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                      <path
                        d="M12 5V19"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.9"
                        stroke-linecap="round"
                      />
                      <circle
                        cx="12"
                        cy="12"
                        r="1.8"
                        fill="currentColor"
                      />
                    </svg>
                  </span>

                  <span class="dfc-compare-label__text">
                    {$dfc_compare_label|escape}
                  </span>
                </div>
              {/if}
            </div>

            <picture class="dfc-pic-next">
              {if $dfc_image_url_xs}
                <source media="(max-width: 499.98px)" srcset="{$dfc_image_url_xs|escape:'html'}">
              {/if}
              {if $dfc_image_url_mobile}
                <source media="(max-width: 767.98px)" srcset="{$dfc_image_url_mobile|escape:'html'}">
              {/if}
              <img class="dfc-img next" src="{$dfc_image_url|escape:'html'}" alt="" loading="lazy">
            </picture>

            <a
              class="dfc-left-link dfc-left-link-overlay"
              href="{$dfc_category_link}"
              aria-label="Poznaj kolekcję {$dfc_title|escape}"
            ></a>

          {else}
            <a class="dfc-left-link" href="{$dfc_category_link}" aria-label="Poznaj kolekcję {$dfc_title|escape}">
              <picture class="dfc-pic-current">
                {if $dfc_image_url_xs}
                  <source media="(max-width: 499.98px)" srcset="{$dfc_image_url_xs|escape:'html'}">
                {/if}
                {if $dfc_image_url_mobile}
                  <source media="(max-width: 767.98px)" srcset="{$dfc_image_url_mobile|escape:'html'}">
                {/if}
                <img class="dfc-img current" src="{$dfc_image_url|escape:'html'}" alt="" loading="lazy">
              </picture>

              <picture class="dfc-pic-next">
                {if $dfc_image_url_xs}
                  <source media="(max-width: 499.98px)" srcset="{$dfc_image_url_xs|escape:'html'}">
                {/if}
                {if $dfc_image_url_mobile}
                  <source media="(max-width: 767.98px)" srcset="{$dfc_image_url_mobile|escape:'html'}">
                {/if}
                <img class="dfc-img next" src="{$dfc_image_url|escape:'html'}" alt="" loading="lazy">
              </picture>
            </a>
          {/if}

        {else}
          <div class="dfc-placeholder">Brak obrazu kolekcji</div>
        {/if}

        <div class="dfc-overlay">
          <div class="dfc-title">{$dfc_title|escape}</div>

          <div class="dfc-overlay-nav">
            <div class="dfc-collection-counter" aria-hidden="true">
              <span class="dfc-collection-counter-current">1</span>
              <span class="dfc-collection-counter-separator"></span>
              <span class="dfc-collection-counter-total">{$dfc_total_collections|intval}</span>
            </div>

            <div class="dfc-arrows">
              <span
                class="dfc-nav dfc-prev"
                role="button"
                aria-label="Poprzednia kolekcja">
                <svg width="40" height="40" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                  <path d="M18 12H6M6 12L11 17M6 12L11 7"
                        stroke="#232323" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </span>

              <span
                class="dfc-nav dfc-next"
                role="button"
                aria-label="Następna kolekcja">
                <svg width="40" height="40" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                  <path d="M6 12H18M18 12L13 7M18 12L13 17"
                        stroke="#232323" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </span>
            </div>
          </div>
        </div>
      </div>

      {if $dfc_badge_1|trim || $dfc_badge_2|trim || $dfc_badge_3|trim || $dfc_badge_4|trim}
        <div class="dfc-badges">
          {if $dfc_badge_1|trim}
            <span class="dfc-badge">{$dfc_badge_1|escape}</span>
          {/if}
          {if $dfc_badge_2|trim}
            <span class="dfc-badge">{$dfc_badge_2|escape}</span>
          {/if}
          {if $dfc_badge_3|trim}
            <span class="dfc-badge">{$dfc_badge_3|escape}</span>
          {/if}
          {if $dfc_badge_4|trim}
            <span class="dfc-badge">{$dfc_badge_4|escape}</span>
          {/if}
        </div>
      {/if}

      {if $dfc_collection_scope|trim}
        <div class="dfc-collection-scope">
          <span class="dfc-collection-scope__label">W kolekcji</span>
          <span class="dfc-collection-scope__text">{$dfc_collection_scope|escape}</span>
        </div>
      {/if}

      {if $dfc_short_description|trim}
        <div class="product-description dfc-short-description-block">
          <div class="dfc-short-description-text collapsed" data-collapsible-content>
            {$dfc_short_description nofilter}
          </div>

          <button type="button" class="dfc-short-description-toggle button-transition" data-toggle-description>
            Zobacz więcej
          </button>
        </div>
      {/if}

      <a class="section button-transition dfc-all-link dfc-all-link-left"
         href="{$dfc_category_link}"
         aria-label="Poznaj kolekcję {$dfc_title|escape}">
        POZNAJ KOLEKCJĘ&nbsp;<span class="dfc-cat-name">{$dfc_title|escape}</span>
      </a>
	  {if $dfc_show_featured_countdown && $dfc_featured_countdown_product_id|intval > 0}
        <div
          class="dfc-featured-countdown-wrap"
          data-dfc-featured-countdown-wrap
          data-dfc-featured-countdown-product-id="{$dfc_featured_countdown_product_id|intval}"
        >
          <div class="dfc-featured-countdown-inner">
            {hook h='pspc' id_product=$dfc_featured_countdown_product_id}
          </div>
        </div>
      {/if}
    </div>

    <div class="dfc-right">
      <h3 class="dfc-subheading">
        {if !empty($dfc_featured_url)}
          <a href="{$dfc_featured_url|escape:'html':'UTF-8'}" class="dfc-subheading-link">
            <span class="dfc-bestseller">BESTSELLER</span>
            <span class="dfc-cat-name">{$dfc_title|escape:'html':'UTF-8'}</span>
          </a>
        {else}
          <span class="dfc-bestseller">BESTSELLER</span>
          <span class="dfc-cat-name">{$dfc_title|escape:'html':'UTF-8'}</span>
        {/if}
      </h3>

      <div class="dfc-featured" aria-live="polite">
        <div class="dfc-featured-item current">
          {if $dfc_featured}
            {include file='catalog/_partials/miniatures/product.tpl' product=$dfc_featured}
          {else}
            <div class="dfc-placeholder">Wybierz polecany produkt</div>
          {/if}
        </div>
        <div class="dfc-featured-item next" aria-hidden="true"></div>
      </div>

      {if $dfc_lowest_price|trim}
        <div class="dfc-lowest-price">
          <div class="dfc-lowest-price__top">
            <span class="dfc-lowest-price__label">Ceny w kolekcji od</span>

            {if $dfc_lowest_price_has_discount}
              <span class="dfc-lowest-price__promo">PROMOCJA</span>
            {/if}
          </div>

          <div class="dfc-lowest-price__main">
            <span class="dfc-lowest-price__value">{$dfc_lowest_price nofilter}</span>

            {if $dfc_lowest_price_has_discount && $dfc_lowest_price_regular|trim}
              <span class="dfc-lowest-price__regular">{$dfc_lowest_price_regular nofilter}</span>
            {/if}
          </div>

          {if $dfc_lowest_price_has_discount && $dfc_lowest_price_discount_label|trim}
            <div class="dfc-lowest-price__bottom">
              <span class="dfc-lowest-price__discount">{$dfc_lowest_price_discount_label nofilter}</span>
            </div>
          {/if}

          {if $dfc_free_shipping_from|trim}
            <div class="dfc-lowest-price__shipping">
    
              <span class="dfc-lowest-price__shipping-icon" aria-hidden="true">
                <svg viewBox="0 0 48 48" width="18" height="18" fill="currentColor">
                  <path d="M0 0h48v48H0z" fill="none"/>
                  <g>
                    <path d="M42,6H18c-2.2,0-4,1.8-4,4v4C7.4,14,2,19.4,2,26v12h4c0,3.314,2.686,6,6,6s6-2.686,6-6h8c0,3.314,2.686,6,6,6s6-2.686,6-6
                      h4c2.2,0,4-1.8,4-4V10C46,7.8,44.2,6,42,6z M34,38c0,1.103-0.897,2-2,2s-2-0.897-2-2s0.897-2,2-2S34,36.897,34,38z M12,40
                      c-1.103,0-2-0.897-2-2s0.897-2,2-2c1.103,0,2,0.897,2,2S13.103,40,12,40z M14,17v4c-1.862,0-3.412,1.278-3.859,3h-4.91
                      C6.145,19.998,9.726,17,14,17z"/>
                  </g>
                </svg>
              </span>

              <span class="dfc-lowest-price__shipping-label">
                 Darmowa dostawa od
              </span>

              <span class="dfc-lowest-price__shipping-value">
                 {$dfc_free_shipping_from nofilter}
              </span>

            </div>
          {/if}
        </div>
      {/if}
    </div>
  </div>

  {if isset($dfc_bundle_items) && $dfc_bundle_items|@count}
    <div class="dfc-bundle-section">
      <div
        class="dfc-bundle"
        data-dfc-bundle-box
      >
        <div class="dfc-bundle-heading">Najczęściej kupowane razem</div>

        <div class="dfc-bundle-layout">
          <div class="dfc-bundle-products">
            <div class="dfc-bundle-items">
              {foreach from=$dfc_bundle_items item=item}
                {if $item.active}
                  <div
                    class="dfc-bundle-item"
                    data-dfc-bundle-item
                    data-id-product="{$item.id_product|intval}"
                    data-price-amount="{$item.price_amount|floatval}"
                    data-regular-price-amount="{$item.regular_price_amount|floatval}"
                  >
                    <div class="dfc-bundle-item__check">
                      <label class="dfc-bundle-item__checkbox-label">
                        <input
                          type="checkbox"
                          class="dfc-bundle-item__checkbox"
                          data-dfc-bundle-checkbox
                          checked="checked"
                        >
                        <span class="dfc-bundle-item__checkbox-ui"></span>
                      </label>
                    </div>

                    <div class="dfc-bundle-item__image_section">
                      <a
                        class="dfc-bundle-item__image"
                        href="{$item.url|escape:'html':'UTF-8'}"
                        aria-label="{$item.name|escape:'html':'UTF-8'}"
                      >
                        {if $item.image}
                          <img
                            src="{$item.image|escape:'html':'UTF-8'}"
                            alt="{$item.name|escape:'html':'UTF-8'}"
                            loading="lazy"
                          >
                        {else}
                          <span class="dfc-bundle-item__image-placeholder">Brak zdjęcia</span>
                        {/if}
                      </a>
                    </div>

                    <div class="dfc-bundle-item__content">
                      <a
                        class="dfc-bundle-item__label-link"
                        href="{$item.url|escape:'html':'UTF-8'}"
                        aria-label="{$item.name|escape:'html':'UTF-8'}"
                        title="{if $item.custom_label|trim}{$item.custom_label|escape:'html':'UTF-8'}{else}{$item.name|escape:'html':'UTF-8'}{/if}"
                      >
                        <span class="dfc-bundle-item__label">
                          {if $item.custom_label|trim}
                            {$item.custom_label|escape:'html':'UTF-8'}
                          {else}
                            {$item.name|escape:'html':'UTF-8'}
                          {/if}
                        </span>
                      </a>

                      <div class="dfc-bundle-item__price-wrap">
                        {if !empty($item.has_discount) && !empty($item.regular_price)}
                          <div class="dfc-bundle-item__price-top">
                            <div class="dfc-bundle-item__price-regular">
                              {$item.regular_price nofilter}
                            </div>

                            {assign var=discountLabel value=''}
                            {if isset($item.discount_percentage) && $item.discount_percentage}
                              {assign var=discountLabel value=$item.discount_percentage}
                            {elseif isset($item.discount) && $item.discount}
                              {assign var=discountLabel value=$item.discount}
                            {/if}

                            {if $discountLabel|trim}
                              <div class="dfc-bundle-item__discount">
                                {$discountLabel nofilter}
                              </div>
                            {/if}
                          </div>
                        {/if}

                        <div class="dfc-bundle-item__price-current">
                          {$item.price nofilter}
                        </div>
                      </div>

                      {if $item.delivery_text|trim}
                        <div class="dfc-bundle-item__delivery">
                          {$item.delivery_text|escape:'html':'UTF-8'}
                        </div>
                      {/if}
                    </div>
                  </div>
                {/if}
              {/foreach}

              {* CTA – aranżacja *}
              {if $dfc_arrangement_image_url|trim}
                <div class="dfc-bundle-arrangement">
                  <button
                    type="button"
                    class="button button-transition dfc-bundle-arrangement-btn"
                    data-dfc-arrangement-open
                    aria-haspopup="dialog"
                    aria-controls="dfc-arrangement-overlay"
                  >
                    ZOBACZ ZESTAW W ARANŻACJI
                  </button>
                </div>
              {/if}
            </div>
          </div>

          <div class="dfc-bundle-summary-wrap">
            <div class="dfc-bundle-footer">
              <div class="dfc-bundle-summary">

                <div class="dfc-bundle-selected-count">
                  <span class="dfc-bundle-selected-count__label">Wybrane produkty:</span>
                  <span class="dfc-bundle-selected-count__value" data-dfc-bundle-count>
                    {$dfc_bundle_items|@count}
                  </span>
                </div>

                {if isset($dfc_bundle_total_regular_price) && $dfc_bundle_total_regular_price|trim && $dfc_bundle_total_regular_price != $dfc_bundle_total_price}
                  <div
                    class="dfc-bundle-regular-total"
                    data-dfc-bundle-regular-row
                  >
                    <span class="dfc-bundle-regular-total__label">Cena przed promocją:</span>
                    <span
                      class="dfc-bundle-regular-total__value"
                      data-dfc-bundle-regular-total
                      data-default-value="{$dfc_bundle_total_regular_price|escape:'html':'UTF-8'}"
                    >
                      {$dfc_bundle_total_regular_price nofilter}
                    </span>
                  </div>
                {/if}

                <div class="dfc-bundle-total">
                  <span class="dfc-bundle-total__label">Razem:</span>
                  <span
                    class="dfc-bundle-total__value"
                    data-dfc-bundle-total
                    data-default-value="{if isset($dfc_bundle_total_price)}{$dfc_bundle_total_price|escape:'html':'UTF-8'}{/if}"
                  >
                    {if isset($dfc_bundle_total_price)}
                      {$dfc_bundle_total_price nofilter}
                    {/if}
                  </span>
                </div>

                {if $dfc_bundle_delivery_text|trim || $dfc_bundle_shipping_from|trim}
                  <div class="dfc-bundle-delivery">

                    {if $dfc_bundle_delivery_text|trim}
                      <div class="dfc-bundle-delivery__time">
                        {$dfc_bundle_delivery_text|escape:'html':'UTF-8'}
                      </div>
                    {/if}

                    {if $dfc_bundle_shipping_from|trim}
                      <div class="dfc-bundle-delivery__shipping">
                        Dostawa od: {$dfc_bundle_shipping_from nofilter}
                      </div>
                    {/if}

                  </div>
                {/if}

                {if isset($dfc_bundle_total_savings) && $dfc_bundle_total_savings|trim}
                  <div
                    class="dfc-bundle-savings"
                    data-dfc-bundle-savings-row
                  >
                    <span class="dfc-bundle-savings__label">Oszczędzasz:</span>
                    <span
                      class="dfc-bundle-savings__value"
                      data-dfc-bundle-savings
                      data-default-value="{$dfc_bundle_total_savings|escape:'html':'UTF-8'}"
                    >
                      {$dfc_bundle_total_savings nofilter}
                    </span>
                  </div>
                {/if}
              </div>

              <button
                type="button"
                class="button button-transition dfc-bundle-add"
                data-dfc-bundle-add
                {if !isset($dfc_bundle_items) || !$dfc_bundle_items|@count}disabled="disabled"{/if}
              >
                <span class="df-btn__label">Dodaj zaznaczone do koszyka</span>
                <span class="df-btn__spinner" aria-hidden="true"></span>
              </button>
            </div>
          </div>
        </div>

        {if $dfc_arrangement_image_url|trim}
          <div
            class="dfc-arrangement-overlay"
            id="dfc-arrangement-overlay"
            data-dfc-arrangement-overlay
            aria-hidden="true"
          >
            <div class="dfc-arrangement-overlay__backdrop" data-dfc-arrangement-close></div>

            <div
              class="dfc-arrangement-overlay__panel"
              role="dialog"
              aria-modal="true"
              aria-label="Aranżacja zestawu"
            >
              <button
                type="button"
                class="dfc-arrangement-overlay__close"
                data-dfc-arrangement-close
                aria-label="Zamknij"
              >
                ×
              </button>

              <div class="dfc-arrangement-overlay__body">
                <img
                  src="{$dfc_arrangement_image_url|escape:'html':'UTF-8'}"
                  alt="Aranżacja zestawu {$dfc_title|escape:'html':'UTF-8'}"
                  class="dfc-arrangement-overlay__image"
                  loading="lazy"
                >
              </div>
            </div>
          </div>
        {/if}
      </div>
    </div>
{/if}
