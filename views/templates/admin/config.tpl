<div class="dfc-admin-scope">

  <div class="panel dfc-admin-panel dfc-admin-panel-config">
    <div class="dfc-panel-head">
      <div class="dfc-panel-head__left">
        <h3><i class="icon icon-cogs"></i> DF Collection – konfiguracja</h3>
        <p class="dfc-panel-subtitle">Dodawanie i edycja kolekcji wyświetlanych w module.</p>
      </div>

      <div class="dfc-panel-head__right">
        {if $dfc_edit.id_dfcollection|intval > 0}
          <div class="dfc-edit-badge">
            Edytujesz ID: <strong>{$dfc_edit.id_dfcollection|intval}</strong>
          </div>
        {else}
          <div class="dfc-edit-badge dfc-edit-badge--new">
            Nowa kolekcja
          </div>
        {/if}
      </div>
    </div>

    <form method="post" action="{$dfc_action}" enctype="multipart/form-data" class="dfc-admin-form">
      <input type="hidden" name="id_dfcollection" value="{$dfc_edit.id_dfcollection|intval}">

      <div class="dfc-form-layout">

        {* =========================================
           LEWA KOLUMNA
           ========================================= *}
        <div class="dfc-form-main">

          <div class="dfc-card dfc-card--module">
            <div class="dfc-card__head">
              <h4>Ustawienia modułu</h4>
              <p>Te opcje są wspólne dla całego modułu i nie zależą od aktualnie edytowanej kolekcji.</p>
            </div>

            <div class="dfc-card__body">
              <div class="dfc-grid dfc-grid--2">
                <div class="form-group dfc-form-group">
                  <label for="dfc_heading">{l s='Nazwa sekcji (nagłówek modułu)' mod='dfcollection'}</label>
                  <input
                    type="text"
                    id="dfc_heading"
                    name="dfc_heading"
                    class="form-control dfc-input dfc-input-heading"
                    value="{$dfc_heading|default:'KOLEKCJE'|escape:'html'}"
                    placeholder="{l s='np. KOLEKCJE' mod='dfcollection'}"
                  />
                  <p class="help-block dfc-help-block">
                    {l s='Jedna wspólna nazwa dla całej sekcji. Wyświetla się jako duży tytuł nad zakładkami.' mod='dfcollection'}
                  </p>
                </div>

                <div class="form-group dfc-form-group">
                  <label for="dfc_heading_link_category">{l s='Link nagłówka (opcjonalnie)' mod='dfcollection'}</label>
                  <select
                    id="dfc_heading_link_category"
                    name="dfc_heading_link_category"
                    class="form-control dfc-select2"
                    data-placeholder="— wybierz kategorię —"
                  >
                    <option value="0">— brak linku —</option>
                    {foreach from=$dfc_categories item=opt}
                      <option value="{$opt.id|intval}" {if $opt.id == $dfc_heading_link_category}selected{/if}>
                        {$opt.label|escape}
                      </option>
                    {/foreach}
                  </select>
                  <p class="help-block dfc-help-block">
                    Jeśli wybierzesz kategorię, główny nagłówek modułu na froncie będzie klikalny i przeniesie do tej kategorii.
                  </p>
                </div>
              </div>

              <div class="form-group dfc-form-group dfc-form-group--full dfc-form-group--excluded">
                <label for="dfc_excluded_category_roots">{l s='Wykluczone kategorie dla wyboru produktów' mod='dfcollection'}</label>

                <select
                  id="dfc_excluded_category_roots"
                  name="dfc_excluded_category_roots[]"
                  class="form-control dfc-select2 dfc-select2-multiple"
                  multiple="multiple"
                  data-placeholder="— wybierz kategorie do wykluczenia —"
                >
                  {foreach from=$dfc_categories item=opt}
                    <option
                      value="{$opt.id|intval}"
                      {if isset($dfc_excluded_category_roots) && in_array($opt.id, $dfc_excluded_category_roots)}selected="selected"{/if}
                    >
                      {$opt.label|escape}
                    </option>
                  {/foreach}
                </select>

                <div id="dfc-excluded-preview" class="dfc-excluded-preview">
                  {if isset($dfc_excluded_category_roots) && $dfc_excluded_category_roots|@count}
                    {foreach from=$dfc_categories item=opt}
                      {if in_array($opt.id, $dfc_excluded_category_roots)}
                        <span class="dfc-excluded-badge" data-id="{$opt.id|intval}">
                          <span class="dfc-excluded-badge__text">{$opt.label|escape}</span>
                          <button
                            type="button"
                            class="dfc-excluded-badge__remove"
                            data-id="{$opt.id|intval}"
                            aria-label="Usuń"
                          >×</button>
                        </span>
                      {/if}
                    {/foreach}
                  {/if}
                </div>

                <p class="help-block dfc-help-block">
                  Wybrane kategorie oraz wszystkie ich podkategorie zostaną wykluczone z listy „Produkt polecany”.
                  Możesz tu dodawać, usuwać i zmieniać wykluczenia w dowolnym momencie.
                </p>
              </div>
            </div>
          </div>

          <div class="dfc-card dfc-card--general">
            <div class="dfc-card__head">
              <h4>Ustawienia kolekcji</h4>
              <p>Te opcje dotyczą tylko aktualnie edytowanej kolekcji.</p>
            </div>

            <div class="dfc-card__body">

              <div class="dfc-row dfc-row--top">
                <div class="dfc-col dfc-col--toggle">
                  <div class="form-group dfc-form-group dfc-form-group-toggle">
                    <label for="dfc_active">Aktywna</label>
                    <input type="hidden" name="active" value="0">

                    <label class="dfc-toggle">
                      <input
                        type="checkbox"
                        name="active"
                        id="dfc_active"
                        value="1"
                        {if $dfc_edit.active}checked="checked"{/if}
                      >
                      <span class="dfc-track" aria-hidden="true"></span>
                    </label>

                    <p class="help-block dfc-help-block">
                      Włącz lub wyłącz tę kolekcję na froncie.
                    </p>
                  </div>
                </div>
              </div>

              <div class="dfc-grid dfc-grid--2">
                <div class="form-group dfc-form-group">
                  <label>Kategoria</label>
                  <select
                    name="id_category"
                    id="dfc_category_select"
                    class="form-control dfc-select2"
                    data-placeholder="— szukaj kategorii —"
                    required
                  >
                    {foreach from=$dfc_categories item=opt}
                      <option value="{$opt.id|intval}" {if $opt.id == $dfc_edit.id_category}selected{/if}>
                        {$opt.label|escape}
                      </option>
                    {/foreach}
                  </select>
                  <p class="help-block dfc-help-block">Wybierz kategorię z drzewa (ID w nawiasie).</p>
                </div>

                <div class="form-group dfc-form-group">
                  <label>Produkt polecany</label>
                  <select
                    name="id_featured_product"
                    id="dfc_featured_product"
                    class="form-control dfc-select2"
                    data-placeholder="— szukaj produktu —"
                  >
                    <option value=""></option>
                    {foreach from=$dfc_products item=p}
                      <option
                        value="{$p.id|intval}"
                        data-product-name="{$p.name|escape:'html'}"
                        data-product-image="{$p.image|escape:'html'}"
                        data-product-id="{$p.id|intval}"
                        {if $p.id == $dfc_edit.id_featured_product}selected{/if}
                      >
                        {$p.label|escape}
                      </option>
                    {/foreach}
                  </select>

                  <p class="help-block dfc-help-block">
                    Zacznij pisać nazwę – lista się przefiltruje. Wybierz pustą opcję, by wyczyścić.
                  </p>

                  <div
                    id="dfc-featured-preview"
                    class="dfc-featured-preview{if !$dfc_edit.id_featured_product} dfc-featured-preview--hidden{/if}"
                  >
                    {assign var=selectedFeatured value=null}
                    {foreach from=$dfc_products item=p}
                      {if $p.id == $dfc_edit.id_featured_product}
                        {assign var=selectedFeatured value=$p}
                      {/if}
                    {/foreach}

                    <div class="dfc-featured-preview__inner">
                      <div class="dfc-featured-preview__image-wrap">
                        {if $selectedFeatured && $selectedFeatured.image}
                          <img
                            src="{$selectedFeatured.image|escape:'html'}"
                            alt=""
                            class="dfc-featured-preview__image"
                            id="dfc-featured-preview-image"
                          >
                        {else}
                          <img
                            src=""
                            alt=""
                            class="dfc-featured-preview__image"
                            id="dfc-featured-preview-image"
                            style="display:none;"
                          >
                        {/if}
                      </div>

                      <div class="dfc-featured-preview__content">
                        <div class="dfc-featured-preview__label">Wybrany produkt</div>

                        <div class="dfc-featured-preview__name" id="dfc-featured-preview-name">
                          {if $selectedFeatured}{$selectedFeatured.name|escape}{/if}
                        </div>

                        <div class="dfc-featured-preview__meta" id="dfc-featured-preview-id">
                          {if $selectedFeatured}ID: {$selectedFeatured.id|intval}{/if}
                        </div>

	                    <div class="dfc-featured-preview__meta" id="dfc-featured-countdown-state">
                          {if $selectedFeatured}
                            Countdown:
                            {if $dfc_edit.show_featured_countdown|default:0}
                              włączony
                            {else}
                              wyłączony
                            {/if}
                          {/if}
                        </div>

                        <button
                          type="button"
                          class="btn btn-default dfc-featured-preview__remove"
                          id="dfc-featured-preview-remove"
                        >
                          Usuń wybór
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

	          <div class="dfc-grid dfc-grid--1">
                <div class="form-group dfc-form-group dfc-form-group-toggle dfc-form-group-toggle-inline">
                  <label class="control-label" for="dfc_show_featured_countdown">
                    Countdown z produktu polecanego
                  </label>

                  <input type="hidden" name="show_featured_countdown" value="0">

                  <label class="dfc-toggle">
                    <input
                      type="checkbox"
                      name="show_featured_countdown"
                      id="dfc_show_featured_countdown"
                      value="1"
                      {if $dfc_edit.show_featured_countdown|default:0}checked="checked"{/if}
                      {if !$dfc_edit.id_featured_product}disabled="disabled"{/if}
                    >
                    <span class="dfc-track" aria-hidden="true"></span>
                  </label>

                  <p class="help-block dfc-help-block" id="dfc_show_featured_countdown_help">
                    {if $dfc_edit.id_featured_product}
                      Po włączeniu moduł pokaże countdown na froncie, korzystając z ID aktualnie wybranego produktu polecanego.
                    {else}
                      Aby włączyć countdown, najpierw musisz wybrać produkt polecany. Countdown działa wyłącznie na podstawie ID produktu polecanego.
                    {/if}
                  </p>
                </div>
              </div>

              <div class="dfc-grid dfc-grid--3">
                <div class="form-group dfc-form-group">
                  <label for="dfc_title">Tytuł (opcjonalnie)</label>
                  <input
                    type="text"
                    id="dfc_title"
                    name="title"
                    class="form-control dfc-input"
                    value="{$dfc_edit.title|escape:'html'}"
                    placeholder="np. LUSSO"
                  />
                  <p class="help-block dfc-help-block">
                    Jeśli zostawisz puste, na froncie zostanie użyta nazwa kategorii.
                  </p>
                </div>

                <div class="form-group dfc-form-group">
                  <label class="control-label">
                    {l s='Liczba produktów w sliderze' mod='dfcollection'}
                  </label>

                  <select name="slider_limit" class="form-control dfc-select2 dfc-select-limit">
                    {for $i=1 to 20}
                      <option value="{$i}" {if $dfc_edit.slider_limit|default:8 == $i}selected="selected"{/if}>{$i}</option>
                    {/for}
                  </select>

                  <p class="help-block dfc-help-block">
                    {l s='Wybierz liczbę od 1 do 20. Zapisywane dla tej kolekcji.' mod='dfcollection'}
                  </p>
                </div>

                <div class="form-group dfc-form-group">
                  <label for="dfc_slider_sort" class="control-label">
                    {l s='Kolejność produktów w sliderze' mod='dfcollection'}
                  </label>

                  <select name="slider_sort" id="dfc_slider_sort" class="form-control dfc-select2">
                    <option value="position" {if $dfc_edit.slider_sort|default:'position' == 'position'}selected="selected"{/if}>
                      {l s='Pozycja w kategorii' mod='dfcollection'}
                    </option>
                    <option value="random" {if $dfc_edit.slider_sort|default:'position' == 'random'}selected="selected"{/if}>
                      {l s='Losowe' mod='dfcollection'}
                    </option>
                    <option value="newest" {if $dfc_edit.slider_sort|default:'position' == 'newest'}selected="selected"{/if}>
                      {l s='Najnowsze' mod='dfcollection'}
                    </option>
                    <option value="price_asc" {if $dfc_edit.slider_sort|default:'position' == 'price_asc'}selected="selected"{/if}>
                      {l s='Najtańsze' mod='dfcollection'}
                    </option>
                    <option value="price_desc" {if $dfc_edit.slider_sort|default:'position' == 'price_desc'}selected="selected"{/if}>
                      {l s='Najdroższe' mod='dfcollection'}
                    </option>
                    <option value="bestseller" {if $dfc_edit.slider_sort|default:'position' == 'bestseller'}selected="selected"{/if}>
                      {l s='Bestsellery' mod='dfcollection'}
                    </option>
                  </select>

                  <p class="help-block dfc-help-block">
                    {l s='Wybierz sposób pobierania produktów do slidera dla tej kolekcji.' mod='dfcollection'}
                  </p>
                </div>
              </div>

              <div class="dfc-grid dfc-grid--3">
                <div class="form-group dfc-form-group dfc-form-group-toggle dfc-form-group-toggle-inline">
                  <label class="control-label" for="dfc_slider_infinite">
                    {l s='Tryb slidera' mod='dfcollection'}
                  </label>

                  <input type="hidden" name="slider_infinite" value="0">

                  <label class="dfc-toggle">
                    <input
                      type="checkbox"
                      name="slider_infinite"
                      id="dfc_slider_infinite"
                      value="1"
                      {if $dfc_edit.slider_infinite|default:1}checked="checked"{/if}
                    >
                    <span class="dfc-track" aria-hidden="true"></span>
                  </label>

                  <p class="help-block dfc-help-block">
                    {l s='Włączone: slider zapętla się w nieskończoność. Wyłączone: slider dojeżdża do końca i strzałka znika.' mod='dfcollection'}
                  </p>
                </div>

                <div class="form-group dfc-form-group">
                  <label for="compare_start_percent">Pozycja startowa suwaka compare (%)</label>

                  <div class="dfc-range-wrap">
                    <input
                      type="range"
                      id="compare_start_percent"
                      name="compare_start_percent"
                      class="dfc-range-input"
                      min="0"
                      max="100"
                      step="1"
                      value="{$dfc_edit.compare_start_percent|default:50|intval}"
                    >

                    <div class="dfc-range-meta">
                      <span class="dfc-range-min">0%</span>
                      <span class="dfc-range-current" id="compare_start_percent_value">
                        {$dfc_edit.compare_start_percent|default:50|intval}%
                      </span>
                      <span class="dfc-range-max">100%</span>
                    </div>
                  </div>

                  <p class="help-block dfc-help-block">
                     Przesuń suwak, aby ustawić pozycję startową pionowego suwaka compare na dużym zdjęciu desktopowym. 50% = środek, 70% = bardziej w prawo.
                  </p>
                </div>

                <div class="form-group dfc-form-group">
                  <label for="compare_label">Etykieta compare (opcjonalnie)</label>
                  <input
                    type="text"
                    id="compare_label"
                    name="compare_label"
                    class="form-control dfc-input"
                    value="{$dfc_edit.compare_label|escape:'html'}"
                    placeholder="np. Zobacz inny kolor"
                  />
                  <p class="help-block dfc-help-block">
                    Krótki tekst pomocniczy wyświetlany na zdjęciu compare, np. „Przesuń, aby zobaczyć inny kolor”.
                  </p>
                </div>
              </div>

              <div class="form-group dfc-form-group dfc-form-group--full">
                <label for="dfc_short_description">Krótki opis kolekcji</label>
                <textarea
                  id="dfc_short_description"
                  name="short_description"
                  class="form-control dfc-input"
                  rows="6"
                  placeholder="np. Krótki opis kolekcji widoczny na froncie."
                >{$dfc_edit.short_description|escape:'html'}</textarea>

                <p class="help-block dfc-help-block">
                  Krótki opis wyświetlany na froncie pod obrazem kolekcji. Dłuższy tekst będzie można rozwinąć przyciskiem „Zobacz więcej”.
                </p>
              </div>

              <div class="form-group dfc-form-group dfc-form-group--full">
                <label for="dfc_collection_scope">Zakres kolekcji</label>
                <input
                  type="text"
                  id="dfc_collection_scope"
                  name="collection_scope"
                  class="form-control dfc-input"
                  value="{$dfc_edit.collection_scope|escape:'html'}"
                  placeholder="np. Komody, szafki RTV, stoliki, witryny, szafy"
                />

                <p class="help-block dfc-help-block">
                   To krótkie pole pokaże na froncie, jakie typy mebli zawiera kolekcja.
                   Wyświetli się pod badge jako uporządkowany wiersz, np. „Komody, szafki RTV, stoliki, witryny”.
                   Najlepiej wpisywać krótką listę elementów oddzielonych przecinkami.
                </p>
              </div>

              <div class="dfc-card dfc-card--bundle">
                <div class="dfc-card__head">
                  <h4>Najczęściej kupowane razem</h4>
                  <p>Dodaj produkty, które będą tworzyć zestaw pod produktem polecanym.</p>
                </div>

                <div class="dfc-card__body">

                  <div id="dfc-bundle-list">

                    {if isset($dfc_edit.dfc_bundle_items) && $dfc_edit.dfc_bundle_items|@count}
                      {foreach from=$dfc_edit.dfc_bundle_items item=item name=bundle}

                        {assign var=selectedBundleProduct value=null}
                        {foreach from=$dfc_products item=p}
                          {if $p.id == $item.id_product}
                            {assign var=selectedBundleProduct value=$p}
                          {/if}
                        {/foreach}

                        <div class="dfc-bundle-row">

                          <div class="dfc-bundle-row__top">
                            <div class="dfc-bundle-row__product">
                              <label>Produkt</label>

                              <select
                                name="dfc_bundle_product[{$smarty.foreach.bundle.index|intval}]"
                                class="form-control dfc-select2 dfc-bundle-product"
                                data-placeholder="— wybierz produkt —"
                                data-bundle-index="{$smarty.foreach.bundle.index|intval}"
                              >
                                <option value=""></option>
                                {foreach from=$dfc_products item=p}
                                  <option
                                    value="{$p.id|intval}"
                                    data-product-name="{$p.name|escape:'html'}"
                                    data-product-image="{$p.image|escape:'html'}"
                                    data-product-id="{$p.id|intval}"
                                    {if $p.id == $item.id_product}selected{/if}
                                  >
                                    {$p.label|escape}
                                  </option>
                                {/foreach}
                              </select>

                              <div
                                class="dfc-bundle-preview{if !$item.id_product} dfc-bundle-preview--hidden{/if}"
                                data-bundle-preview
                              >
                                <div class="dfc-bundle-preview__inner">
                                  <div class="dfc-bundle-preview__image-wrap">
                                    {if $selectedBundleProduct && $selectedBundleProduct.image}
                                      <img
                                        src="{$selectedBundleProduct.image|escape:'html'}"
                                        alt=""
                                        class="dfc-bundle-preview__image"
                                        data-bundle-preview-image
                                      >
                                    {else}
                                      <img
                                        src=""
                                        alt=""
                                        class="dfc-bundle-preview__image"
                                        data-bundle-preview-image
                                        style="display:none;"
                                      >
                                    {/if}
                                  </div>

                                  <div class="dfc-bundle-preview__content">
                                    <div class="dfc-bundle-preview__label">Wybrany produkt</div>

                                    <div class="dfc-bundle-preview__name" data-bundle-preview-name>
                                      {if $selectedBundleProduct}{$selectedBundleProduct.name|escape}{/if}
                                    </div>

                                    <div class="dfc-bundle-preview__meta" data-bundle-preview-id>
                                      {if $selectedBundleProduct}ID: {$selectedBundleProduct.id|intval}{/if}
                                    </div>

                                    <button
                                      type="button"
                                      class="btn btn-default dfc-bundle-preview__remove"
                                      data-bundle-preview-remove
                                    >
                                      Usuń wybór
                                    </button>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>

                          <div class="dfc-bundle-row__bottom">
                            <div class="dfc-bundle-row__label">
                              <label>Podpis na froncie</label>
                              <input
                                type="text"
                                name="dfc_bundle_label[{$smarty.foreach.bundle.index|intval}]"
                                class="form-control dfc-input"
                                value="{$item.custom_label|escape:'html'}"
                                placeholder="np. Komoda / Lustro / Toaletka"
                              >
                              <p class="help-block dfc-help-block">
                                Krótka nazwa widoczna na froncie zamiast pełnej nazwy produktu.
                              </p>
                            </div>

                            <div class="dfc-bundle-row__side">
                              <div class="dfc-bundle-row__active">
                                <label class="dfc-bundle-checkbox">
                                  <input
                                    type="checkbox"
                                    name="dfc_bundle_active[{$smarty.foreach.bundle.index|intval}]"
                                    value="1"
                                    {if $item.active}checked{/if}
                                  >
                                  <span>Aktywny produkt w zestawie</span>
                                </label>
                              </div>

                              <div class="dfc-bundle-row__remove">
                                <button type="button" class="btn btn-danger dfc-bundle-remove">
                                  Usuń produkt z zestawu
                                </button>
                              </div>
                            </div>
                          </div>

                        </div>
                      {/foreach}
                    {/if}
                  </div>

                  <div class="dfc-bundle-actions">
                    <button type="button" class="btn btn-default" id="dfc-add-bundle-item">
                      + Dodaj produkt do zestawu
                    </button>
                  </div>

                  <p class="help-block dfc-help-block">
                    Możesz dodać dowolną liczbę produktów do zestawu (np. komoda + witryna + stolik).
                  </p>

                </div>
              </div>

              <div class="dfc-card-separator"></div>

              <div class="form-group dfc-form-group dfc-form-group--full dfc-badges-section">
                <label>Cechy kolekcji / badge na froncie</label>

                <p class="help-block dfc-help-block" style="margin-top: 0;">
                  Te krótkie hasła wyświetlą się na froncie jako małe badge pod głównym zdjęciem kolekcji, nad opisem.
                  Dzięki nim klient szybciej zobaczy najważniejsze informacje, np. styl, przeznaczenie, dostępne kolory albo typ kolekcji.
                  Możesz wpisać od 1 do 4 krótkich cech. Najlepiej używać krótkich, konkretnych fraz.
                </p>

                <div class="dfc-grid dfc-grid--2">
                  <div class="form-group dfc-form-group">
                    <label for="dfc_badge_1">Badge 1</label>
                    <input
                      type="text"
                      id="dfc_badge_1"
                      name="badge_1"
                      class="form-control dfc-input"
                      value="{$dfc_edit.badge_1|escape:'html'}"
                      placeholder="np. dostępne kolory"
                    />
                    <p class="help-block dfc-help-block">
                      Pierwsza krótka cecha kolekcji.
                    </p>
                  </div>

                  <div class="form-group dfc-form-group">
                    <label for="dfc_badge_2">Badge 2</label>
                    <input
                      type="text"
                      id="dfc_badge_2"
                      name="badge_2"
                      class="form-control dfc-input"
                      value="{$dfc_edit.badge_2|escape:'html'}"
                      placeholder="np. do salonu i jadalni"
                    />
                    <p class="help-block dfc-help-block">
                      Druga krótka cecha kolekcji.
                    </p>
                  </div>

                  <div class="form-group dfc-form-group">
                    <label for="dfc_badge_3">Badge 3</label>
                    <input
                      type="text"
                      id="dfc_badge_3"
                      name="badge_3"
                      class="form-control dfc-input"
                      value="{$dfc_edit.badge_3|escape:'html'}"
                      placeholder="np. styl nowoczesny"
                    />
                    <p class="help-block dfc-help-block">
                      Trzecia krótka cecha kolekcji.
                    </p>
                  </div>

                  <div class="form-group dfc-form-group">
                    <label for="dfc_badge_4">Badge 4</label>
                    <input
                      type="text"
                      id="dfc_badge_4"
                      name="badge_4"
                      class="form-control dfc-input"
                      value="{$dfc_edit.badge_4|escape:'html'}"
                      placeholder="np. od ręki / na zamówienie"
                    />
                    <p class="help-block dfc-help-block">
                      Czwarta krótka cecha kolekcji.
                    </p>
                  </div>
                </div>

                <div class="alert alert-info" style="margin-top: 10px;">
                  <strong>Przykłady:</strong><br>
                   dostępne kolory<br>
                   do salonu i sypialni<br>
                   styl loftowy<br>
                   meble systemowe
                </div>
              </div>

            </div>
          </div>
        </div>

        {* =========================================
           PRAWA KOLUMNA
           ========================================= *}
        <div class="dfc-form-side">

          <div class="dfc-card dfc-card--images">
            <div class="dfc-card__head">
              <h4>Obrazy kolekcji</h4>
              <p>Osobne pliki dla desktopu, mobile i małych ekranów.</p>
            </div>

            <div class="dfc-card__body">

              {* DESKTOP > 768px *}
              <div class="form-group dfc-form-group dfc-upload-group">
                <label class="dfc-label">Obraz kolekcji (desktop &gt; 768px)</label>

                <div class="dummyfile input-group dfc-upload-row">
                  <input
                    id="DFCOLL_IMG_DESKTOP"
                    type="file"
                    name="image_file"
                    accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                    class="hide dfc-file-input"
                  >

                  <span class="input-group-addon"><i class="icon-file"></i></span>

                  <input
                    id="DFCOLL_IMG_DESKTOP-name"
                    type="text"
                    class="disabled input-file-name dfc-file-name"
                    readonly
                    value="{if $dfc_edit.image_url}{$dfc_edit.image_url|escape}{/if}"
                  >

                  <span class="input-group-btn">
                    <button
                      id="DFCOLL_IMG_DESKTOP-selectbutton"
                      type="button"
                      class="btn btn-default dfc-file-button"
                    >
                      {l s='Wybierz plik' mod='dfcollection'}
                    </button>
                  </span>
                </div>

                <p class="help-block dfc-help-block">Wgraj plik JPG lub PNG.</p>

                {if $dfc_edit.image_url}
                  <div class="dfc-image-preview-block">
                    <div class="dfc-image-preview-top">
                      <a href="{$dfc_edit.image_url|escape:'html'}" target="_blank" rel="noopener" class="dfc-image-preview-link">
                        <img src="{$dfc_edit.image_url|escape:'html'}" alt="" class="dfc-image-preview">
                      </a>
                    </div>

                    <div class="dfc-image-preview-bottom">
                      <label class="checkbox-inline dfc-delete-checkbox">
                        <input type="checkbox" name="delete_image_url" value="1"> {l s='usuń obraz' mod='dfcollection'}
                      </label>

                      {if $dfc_edit.image_url_dims || $dfc_edit.image_url_size}
                        <div class="text-muted dfc-image-meta">
                          {$dfc_edit.image_url_dims|escape}{if $dfc_edit.image_url_dims && $dfc_edit.image_url_size} — {/if}{$dfc_edit.image_url_size|escape}
                        </div>
                      {/if}
                    </div>
                  </div>
                {/if}
              </div>

              {* DESKTOP COMPARE > 768px *}
              <div class="form-group dfc-form-group dfc-upload-group">
                <label class="dfc-label">Drugie zdjęcie compare (desktop &gt; 768px)</label>

                <div class="dummyfile input-group dfc-upload-row">
                  <input
                    id="DFCOLL_IMG_COMPARE"
                    type="file"
                    name="image_compare_file"
                    accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                    class="hide dfc-file-input"
                  >

                  <span class="input-group-addon"><i class="icon-file"></i></span>

                  <input
                    id="DFCOLL_IMG_COMPARE-name"
                    type="text"
                    class="disabled input-file-name dfc-file-name"
                    readonly
                    value="{if $dfc_edit.image_compare_url}{$dfc_edit.image_compare_url|escape}{/if}"
                  >

                  <span class="input-group-btn">
                    <button
                      id="DFCOLL_IMG_COMPARE-selectbutton"
                      type="button"
                      class="btn btn-default dfc-file-button"
                    >
                      {l s='Wybierz plik' mod='dfcollection'}
                    </button>
                  </span>
                </div>

                <p class="help-block dfc-help-block">
                  Opcjonalnie — drugie zdjęcie JPG lub PNG do efektu compare na dużym obrazku kolekcji na desktopie.
                </p>

                {if $dfc_edit.image_compare_url}
                  <div class="dfc-image-preview-block">
                    <div class="dfc-image-preview-top">
                      <a href="{$dfc_edit.image_compare_url|escape:'html'}" target="_blank" rel="noopener" class="dfc-image-preview-link">
                        <img src="{$dfc_edit.image_compare_url|escape:'html'}" alt="" class="dfc-image-preview">
                      </a>
                    </div>

                    <div class="dfc-image-preview-bottom">
                      <label class="checkbox-inline dfc-delete-checkbox">
                        <input type="checkbox" name="delete_image_compare_url" value="1"> {l s='usuń obraz compare' mod='dfcollection'}
                      </label>

                      {if $dfc_edit.image_compare_url_dims || $dfc_edit.image_compare_url_size}
                        <div class="text-muted dfc-image-meta">
                          {$dfc_edit.image_compare_url_dims|escape}{if $dfc_edit.image_compare_url_dims && $dfc_edit.image_compare_url_size} — {/if}{$dfc_edit.image_compare_url_size|escape}
                        </div>
                      {/if}
                    </div>
                  </div>
                {/if}
              </div>

              {* ARANŻACJA / OVERLAY *}
              <div class="form-group dfc-form-group dfc-upload-group">
                <label class="dfc-label">Zdjęcie aranżacji do overlayu</label>

                <div class="dummyfile input-group dfc-upload-row">
                  <input
                    id="DFCOLL_IMG_ARRANGEMENT"
                    type="file"
                    name="arrangement_image_file"
                    accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                    class="hide dfc-file-input"
                  >

                  <span class="input-group-addon"><i class="icon-file"></i></span>

                  <input
                    id="DFCOLL_IMG_ARRANGEMENT-name"
                    type="text"
                    class="disabled input-file-name dfc-file-name"
                    readonly
                    value="{if $dfc_edit.arrangement_image_url}{$dfc_edit.arrangement_image_url|escape}{/if}"
                  >

                  <span class="input-group-btn">
                    <button
                      id="DFCOLL_IMG_ARRANGEMENT-selectbutton"
                      type="button"
                      class="btn btn-default dfc-file-button"
                    >
                      {l s='Wybierz plik' mod='dfcollection'}
                    </button>
                  </span>
                </div>

                <p class="help-block dfc-help-block">
                  Opcjonalnie — zdjęcie aranżacji wyświetlane po kliknięciu przycisku „Zobacz zestaw w aranżacji” w dolnym overlayu na froncie.
                </p>

                {if $dfc_edit.arrangement_image_url}
                  <div class="dfc-image-preview-block">
                    <div class="dfc-image-preview-top">
                      <a href="{$dfc_edit.arrangement_image_url|escape:'html'}" target="_blank" rel="noopener" class="dfc-image-preview-link">
                        <img src="{$dfc_edit.arrangement_image_url|escape:'html'}" alt="" class="dfc-image-preview">
                      </a>
                    </div>

                    <div class="dfc-image-preview-bottom">
                      <label class="checkbox-inline dfc-delete-checkbox">
                        <input type="checkbox" name="delete_arrangement_image_url" value="1"> {l s='usuń obraz aranżacji' mod='dfcollection'}
                      </label>

                      {if $dfc_edit.arrangement_image_url_dims || $dfc_edit.arrangement_image_url_size}
                        <div class="text-muted dfc-image-meta">
                          {$dfc_edit.arrangement_image_url_dims|escape}{if $dfc_edit.arrangement_image_url_dims && $dfc_edit.arrangement_image_url_size} — {/if}{$dfc_edit.arrangement_image_url_size|escape}
                        </div>
                      {/if}
                    </div>
                  </div>
                {/if}
              </div>

              {* MOBILE < 768px *}
              <div class="form-group dfc-form-group dfc-upload-group">
                <label class="dfc-label">Obraz kolekcji (mobile &lt; 768px)</label>

                <div class="dummyfile input-group dfc-upload-row">
                  <input
                    id="DFCOLL_IMG_MOBILE"
                    type="file"
                    name="image_mobile_file"
                    accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                    class="hide dfc-file-input"
                  >

                  <span class="input-group-addon"><i class="icon-file"></i></span>

                  <input
                    id="DFCOLL_IMG_MOBILE-name"
                    type="text"
                    class="disabled input-file-name dfc-file-name"
                    readonly
                    value="{if $dfc_edit.image_url_mobile}{$dfc_edit.image_url_mobile|escape}{/if}"
                  >

                  <span class="input-group-btn">
                    <button
                      id="DFCOLL_IMG_MOBILE-selectbutton"
                      type="button"
                      class="btn btn-default dfc-file-button"
                    >
                      {l s='Wybierz plik' mod='dfcollection'}
                    </button>
                  </span>
                </div>

                <p class="help-block dfc-help-block">
                  Opcjonalnie — osobny plik JPG lub PNG dla telefonów/tabletów.
                </p>

                {if $dfc_edit.image_url_mobile}
                  <div class="dfc-image-preview-block">
                    <div class="dfc-image-preview-top">
                      <a href="{$dfc_edit.image_url_mobile|escape:'html'}" target="_blank" rel="noopener" class="dfc-image-preview-link">
                        <img src="{$dfc_edit.image_url_mobile|escape:'html'}" alt="" class="dfc-image-preview">
                      </a>
                    </div>

                    <div class="dfc-image-preview-bottom">
                      <label class="checkbox-inline dfc-delete-checkbox">
                        <input type="checkbox" name="delete_image_url_mobile" value="1"> {l s='usuń obraz' mod='dfcollection'}
                      </label>

                      {if $dfc_edit.image_url_mobile_dims || $dfc_edit.image_url_mobile_size}
                        <div class="text-muted dfc-image-meta">
                          {$dfc_edit.image_url_mobile_dims|escape}{if $dfc_edit.image_url_mobile_dims && $dfc_edit.image_url_mobile_size} — {/if}{$dfc_edit.image_url_mobile_size|escape}
                        </div>
                      {/if}
                    </div>
                  </div>
                {/if}
              </div>

              {* SMALL < 500px *}
              <div class="form-group dfc-form-group dfc-upload-group">
                <label class="dfc-label">Obraz kolekcji (small &lt; 500px)</label>

                <div class="dummyfile input-group dfc-upload-row">
                  <input
                    id="DFCOLL_IMG_XS"
                    type="file"
                    name="image_xs_file"
                    accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                    class="hide dfc-file-input"
                  >

                  <span class="input-group-addon"><i class="icon-file"></i></span>

                  <input
                    id="DFCOLL_IMG_XS-name"
                    type="text"
                    class="disabled input-file-name dfc-file-name"
                    readonly
                    value="{if $dfc_edit.image_url_xs}{$dfc_edit.image_url_xs|escape}{/if}"
                  >

                  <span class="input-group-btn">
                    <button
                      id="DFCOLL_IMG_XS-selectbutton"
                      type="button"
                      class="btn btn-default dfc-file-button"
                    >
                      {l s='Wybierz plik' mod='dfcollection'}
                    </button>
                  </span>
                </div>

                <p class="help-block dfc-help-block">
                  Opcjonalnie — najmniejszy obraz JPG lub PNG (np. &lt; 500px).
                </p>

                {if $dfc_edit.image_url_xs}
                  <div class="dfc-image-preview-block">
                    <div class="dfc-image-preview-top">
                      <a href="{$dfc_edit.image_url_xs|escape:'html'}" target="_blank" rel="noopener" class="dfc-image-preview-link">
                        <img src="{$dfc_edit.image_url_xs|escape:'html'}" alt="" class="dfc-image-preview">
                      </a>
                    </div>

                    <div class="dfc-image-preview-bottom">
                      <label class="checkbox-inline dfc-delete-checkbox">
                        <input type="checkbox" name="delete_image_url_xs" value="1"> {l s='usuń obraz' mod='dfcollection'}
                      </label>

                      {if $dfc_edit.image_url_xs_dims || $dfc_edit.image_url_xs_size}
                        <div class="text-muted dfc-image-meta">
                          {$dfc_edit.image_url_xs_dims|escape}{if $dfc_edit.image_url_xs_dims && $dfc_edit.image_url_xs_size} — {/if}{$dfc_edit.image_url_xs_size|escape}
                        </div>
                      {/if}
                    </div>
                  </div>
                {/if}
              </div>

            </div>
          </div>
        </div>
      </div>

      <div class="panel-footer clearfix dfc-panel-footer">
        <div class="pull-right dfc-panel-footer-actions">
          <button
            type="button"
            class="btn btn-default dfc-btn-reset"
            onclick="window.location.href='{$dfc_action|escape:'html'}&dfc_reset=1'"
          >
            <i class="process-icon-eraser"></i>
            Wyczyść formularz
          </button>

          <button type="submit" name="dfc_save" class="btn btn-default dfc-btn-save">
            <i class="process-icon-save"></i>
            Zapisz
          </button>

          {if $dfc_edit.id_dfcollection|intval > 0}
            <button
              type="submit"
              name="dfc_duplicate"
              class="btn btn-default dfc-btn-duplicate"
              onclick="return confirm('Skopiować tę kolekcję?');"
            >
              <i class="material-icons">content_copy</i>
              Kopiuj
            </button>
            
            <button
              type="submit"
              name="dfc_delete"
              class="btn btn-default dfc-btn-delete"
              onclick="return confirm('Usunąć tę kategorię?');"
            >
              <i class="process-icon-delete"></i>
              Usuń
            </button>
          {/if}
        </div>
      </div>
    </form>
  </div>

  <div class="panel dfc-admin-panel dfc-admin-panel-list">
    <div class="dfc-panel-head">
      <div class="dfc-panel-head__left">
        <h3><i class="icon-list"></i> Kolekcje</h3>
        <p class="dfc-panel-subtitle">Lista wszystkich zapisanych pozycji w module.</p>
      </div>
    </div>

    <div class="dfc-table-wrap">
      <table class="table dfc-admin-table">
        <thead>
          <tr>
            <th class="dfc-col-handle"></th>
            <th>ID</th>
            <th>Pozycja</th>
            <th>Status</th>
            <th>Kategoria</th>
            <th>Produkt polecany</th>
			<th>Countdown</th>
            <th>Najczęściej kupowane razem</th>
            <th>Tytuł</th>
            <th>Krótki opis</th>
            <th>Zakres kolekcji</th>
            <th>Badge</th>
            <th>Slajdy</th>
            <th>Sortowanie</th>
            <th>Tryb</th>
            <th>Desktop</th>
            <th>Compare</th>
            <th>Aranżacja</th>
            <th>Start compare</th>
            <th>Etykieta compare</th>
            <th>Mobile</th>
            <th>Small</th>
            <th>Akcje</th>
          </tr>
        </thead>

        <tbody id="dfc-sortable" data-url="{$dfc_action|escape:'html'}&dfc_sort=1">
          {foreach from=$dfc_rows item=row}
            <tr data-id="{$row.id_dfcollection|intval}">
              <td class="dfc-handle">⇅</td>
              <td>{$row.id_dfcollection|intval}</td>
              <td class="dfc-pos">{$row.position|intval}</td>
              <td>
                <span class="dfc-status-badge {if $row.active}dfc-status-badge--on{else}dfc-status-badge--off{/if}">
                  {if $row.active}TAK{else}NIE{/if}
                </span>
              </td>
              <td>
                {if $row.cat_name}{$row.cat_name|escape}{else}?{/if}
              </td>
              <td class="dfc-table-featured-col">
                {if $row.id_featured_product}
                  <div class="dfc-table-featured-card">
                    <div class="dfc-table-featured-thumb-wrap">
                      {if $row.prod_image}
                        <img
                          src="{$row.prod_image|escape:'html'}"
                          alt=""
                          class="dfc-table-featured-thumb"
                          draggable="false"
                        >
                      {else}
                        <div class="dfc-table-featured-thumb dfc-table-featured-thumb--empty">—</div>
                      {/if}
                    </div>

                    <div class="dfc-table-featured-content">
                      <div class="dfc-table-featured-name">
                        {if $row.prod_name}{$row.prod_name|escape}{else}—{/if}
                      </div>

                      <div class="dfc-table-image-meta">
                        ID: {$row.id_featured_product|intval}
                      </div>
                    </div>
                  </div>
                {else}
                  Brak
                {/if}
              </td>
			  <td class="dfc-table-countdown-col">
                {if $row.id_featured_product}
                  {if $row.show_featured_countdown|default:0}
                    <span class="dfc-status-badge dfc-status-badge--on">
                      TAK
                    </span>
                    <div class="dfc-table-image-meta">
                      z produktu polecanego
                    </div>
                  {else}
                    <span class="dfc-status-badge dfc-status-badge--off">
                      NIE
                    </span>
                    <div class="dfc-table-image-meta">
                      wyłączony
                    </div>
                  {/if}
                {else}
                  <span class="dfc-status-badge dfc-status-badge--off">
                    BRAK
                  </span>
                  <div class="dfc-table-image-meta">
                    brak produktu polecanego
                  </div>
                {/if}
              </td>
              <td class="dfc-table-bundle-col">
                {if isset($row.bundle_items) && $row.bundle_items|@count}
                  <div class="dfc-table-bundle">

                    {foreach from=$row.bundle_items item=b}
                      <div class="dfc-table-bundle-item">

                        <div class="dfc-table-bundle-thumb-wrap">
                          {if $b.product_image}
                            <img
                              src="{$b.product_image|escape:'html'}"
                              alt=""
                              class="dfc-table-bundle-thumb"
                            >
                          {else}
                            <div class="dfc-table-bundle-thumb dfc-table-bundle-thumb--empty">—</div>
                          {/if}
                        </div>

                        <div class="dfc-table-bundle-content">
                          <div class="dfc-table-bundle-name">
                            {if $b.custom_label|trim}
                              {$b.custom_label|escape}
                            {else}
                              {$b.product_name|escape}
                            {/if}
                          </div>

                          <div class="dfc-table-image-meta">
                            ID: {$b.id_product|intval}
                          </div>
                        </div>

                      </div>
                    {/foreach}

                  </div>
                {else}
                  <span class="dfc-empty-description">Brak</span>
                {/if}
              </td>
              <td class="dfc-table-title-col">
                {if $row.title|trim}
                  <div class="dfc-table-title-main">
                    {$row.title|escape}
                  </div>
                {elseif $row.effective_title|trim}
                  <div class="dfc-table-title-main">
                    {$row.effective_title|escape}
                  </div>
                  <div class="dfc-table-image-meta">
                    użyto nazwy kategorii
                  </div>
                {else}
                  <span class="dfc-empty-image">Brak</span>
                {/if}
              </td>
              <td class="dfc-table-description-col">
                {if $row.short_description|trim}
                  <div class="dfc-table-description">
                    {$row.short_description|strip_tags|truncate:180:"..."|escape:'html'}
                  </div>
                {else}
                  <span class="dfc-empty-description">Brak</span>
                {/if}
              </td>
              <td class="dfc-table-scope-col">
                {if $row.collection_scope|trim}
                  <div class="dfc-table-scope">
                    {$row.collection_scope|escape}
                  </div>
                {else}
                  <span class="dfc-empty-description">Brak</span>
                {/if}
              </td>
              <td class="dfc-table-badges-col">
                {if $row.badge_1|trim || $row.badge_2|trim || $row.badge_3|trim || $row.badge_4|trim}
                  <div class="dfc-table-badges">
                    {if $row.badge_1|trim}
                      <span class="dfc-table-badge">{$row.badge_1|escape}</span>
                    {/if}
                    {if $row.badge_2|trim}
                      <span class="dfc-table-badge">{$row.badge_2|escape}</span>
                    {/if}
                    {if $row.badge_3|trim}
                      <span class="dfc-table-badge">{$row.badge_3|escape}</span>
                    {/if}
                    {if $row.badge_4|trim}
                      <span class="dfc-table-badge">{$row.badge_4|escape}</span>
                    {/if}
                  </div>
                {else}
                  <span class="dfc-empty-description">Brak</span>
                {/if}
              </td>
              <td>{$row.slider_limit|default:8|intval}</td>
              <td>
                {if $row.slider_sort|default:'position' == 'position'}
                  Pozycja w kategorii
                {elseif $row.slider_sort == 'random'}
                  Losowe
                {elseif $row.slider_sort == 'newest'}
                  Najnowsze
                {elseif $row.slider_sort == 'price_asc'}
                  Najtańsze
                {elseif $row.slider_sort == 'price_desc'}
                  Najdroższe
                {elseif $row.slider_sort == 'bestseller'}
                  Bestsellery
                {else}
                  Pozycja w kategorii
                {/if}
              </td>
              <td>
                {if isset($row.slider_infinite) && $row.slider_infinite}
                  Zapętlony
                {else}
                  Kończący się
                {/if}
              </td>

              <td class="dfc-table-image-col">
                {if $row.image_url}
                  <div class="dfc-table-image-card">
                    <div class="dfc-table-image-thumb-wrap">
                      <img
                        src="{$row.image_url|escape:'html'}"
                        alt=""
                        class="dfc-table-image-thumb"
                        draggable="false"
                      >
                    </div>

                    <div class="dfc-table-image-url">
                      {$row.image_url|escape}

                      {if $row.image_url_size || $row.image_url_dims}
                        <div class="dfc-table-image-meta">
                          {$row.image_url_size|escape}{if $row.image_url_size && $row.image_url_dims} — {/if}{$row.image_url_dims|escape}
                        </div>
                      {/if}
                    </div>
                  </div>
                {else}
                  <span class="dfc-empty-image">Brak</span>
                {/if}
              </td>

              <td class="dfc-table-image-col">
                {if $row.image_compare_url}
                  <div class="dfc-table-image-card">
                    <div class="dfc-table-image-thumb-wrap">
                      <img
                        src="{$row.image_compare_url|escape:'html'}"
                        alt=""
                        class="dfc-table-image-thumb"
                        draggable="false"
                      >
                    </div>

                    <div class="dfc-table-image-url">
                      {$row.image_compare_url|escape}

                      {if $row.image_compare_url_size || $row.image_compare_url_dims}
                        <div class="dfc-table-image-meta">
                          {$row.image_compare_url_size|escape}{if $row.image_compare_url_size && $row.image_compare_url_dims} — {/if}{$row.image_compare_url_dims|escape}
                        </div>
                      {/if}
                    </div>
                  </div>
                {else}
                  <span class="dfc-empty-image">Brak</span>
                {/if}
              </td>

              <td class="dfc-table-image-col">
                {if $row.arrangement_image_url}
                  <div class="dfc-table-image-card">
                    <div class="dfc-table-image-thumb-wrap">
                      <img
                        src="{$row.arrangement_image_url|escape:'html'}"
                        alt=""
                        class="dfc-table-image-thumb"
                        draggable="false"
                      >
                    </div>

                    <div class="dfc-table-image-url">
                      {$row.arrangement_image_url|escape}

                      {if $row.arrangement_image_url_size || $row.arrangement_image_url_dims}
                        <div class="dfc-table-image-meta">
                          {$row.arrangement_image_url_size|escape}{if $row.arrangement_image_url_size && $row.arrangement_image_url_dims} — {/if}{$row.arrangement_image_url_dims|escape}
                        </div>
                      {/if}
                    </div>
                  </div>
                {else}
                  <span class="dfc-empty-image">Brak</span>
                {/if}
              </td>

              <td class="dfc-table-compare-start-col">
                <div class="dfc-compare-start-badge">
                  {$row.compare_start_percent|default:50|intval}%
                </div>
                <div class="dfc-table-image-meta">
                  start
                </div>
              </td>

              <td class="dfc-table-compare-label-col">
                {if $row.compare_label|trim}
                  <div class="dfc-compare-label-preview">
                    {$row.compare_label|escape}
                  </div>
                  <div class="dfc-table-image-meta">
                    etykieta
                  </div>
                {else}
                  <span class="dfc-empty-image">Brak</span>
                {/if}
              </td>

              <td class="dfc-table-image-col">
                {if $row.image_url_mobile}
                  <div class="dfc-table-image-card">
                    <div class="dfc-table-image-thumb-wrap">
                      <img
                        src="{$row.image_url_mobile|escape:'html'}"
                        alt=""
                        class="dfc-table-image-thumb"
                        draggable="false"
                      >
                    </div>

                    <div class="dfc-table-image-url">
                      {$row.image_url_mobile|escape}

                      {if $row.image_url_mobile_size || $row.image_url_mobile_dims}
                        <div class="dfc-table-image-meta">
                          {$row.image_url_mobile_size|escape}{if $row.image_url_mobile_size && $row.image_url_mobile_dims} — {/if}{$row.image_url_mobile_dims|escape}
                        </div>
                      {/if}
                    </div>
                  </div>
                {else}
                  <span class="dfc-empty-image">Brak</span>
                {/if}
              </td>

              <td class="dfc-table-image-col">
                {if $row.image_url_xs}
                  <div class="dfc-table-image-card">
                    <div class="dfc-table-image-thumb-wrap">
                      <img
                        src="{$row.image_url_xs|escape:'html'}"
                        alt=""
                        class="dfc-table-image-thumb"
                        draggable="false"
                      >
                    </div>

                    <div class="dfc-table-image-url">
                      {$row.image_url_xs|escape}

                      {if $row.image_url_xs_size || $row.image_url_xs_dims}
                        <div class="dfc-table-image-meta">
                          {$row.image_url_xs_size|escape}{if $row.image_url_xs_size && $row.image_url_xs_dims} — {/if}{$row.image_url_xs_dims|escape}
                        </div>
                      {/if}
                    </div>
                  </div>
                {else}
                  <span class="dfc-empty-image">Brak</span>
                {/if}
              </td>

              <td class="dfc-table-actions">
                <a
                  class="btn btn-default dfc-btn-edit"
                  href="{$dfc_action}&id_dfcollection={$row.id_dfcollection|intval}&dfc_load=1"
                >
                  Edytuj
                </a>

                <form method="post" action="{$dfc_action}" class="dfc-inline-form">
                  <input type="hidden" name="id_dfcollection" value="{$row.id_dfcollection|intval}">
                  <button
                    type="submit"
                    class="btn btn-default dfc-btn-row-clone"
                    name="dfc_duplicate"
                    onclick="return confirm('Skopiować tę kolekcję?');"
                  >
                    Kopiuj
                 </button>
                </form>

                <form method="post" action="{$dfc_action}" class="dfc-inline-form">
                  <input type="hidden" name="id_dfcollection" value="{$row.id_dfcollection|intval}">
                  <button
                    type="submit"
                    class="btn btn-danger dfc-btn-row-delete"
                    name="dfc_delete"
                    onclick="return confirm('Usunąć?')"
                  >
                    Usuń
                  </button>
                </form>
              </td>
            </tr>
          {/foreach}
        </tbody>
      </table>
    </div>

{literal}
<script>
(function () {
  function bindFileRow(inputId){
    var input = document.getElementById(inputId);
    if (!input) return;

    var row = document.getElementById(inputId + '-name');
    var button = document.getElementById(inputId + '-selectbutton');

    if (button) {
      button.addEventListener('click', function(e){
        e.preventDefault();
        input.click();
      });
    }

    input.addEventListener('change', function(){
      var name = (this.files && this.files.length) ? this.files[0].name : '';
      if (row) {
        row.value = name;
      }
    });
  }

  function initComparePercentSlider() {
    var input = document.getElementById('compare_start_percent');
    var value = document.getElementById('compare_start_percent_value');

    if (!input || !value) {
      return;
    }

    function updateValue() {
      value.textContent = input.value + '%';
    }

    input.addEventListener('input', updateValue);
    input.addEventListener('change', updateValue);

    updateValue();
  }

  function initFeaturedPreview() {
    var select = document.getElementById('dfc_featured_product');
    var preview = document.getElementById('dfc-featured-preview');
    var image = document.getElementById('dfc-featured-preview-image');
    var name = document.getElementById('dfc-featured-preview-name');
    var id = document.getElementById('dfc-featured-preview-id');
    var removeBtn = document.getElementById('dfc-featured-preview-remove');

    if (!select || !preview || !image || !name || !id || !removeBtn) {
      return;
    }

    function updatePreview() {
      var option = select.options[select.selectedIndex];
      var productId = option ? (option.getAttribute('data-product-id') || '') : '';
      var productName = option ? (option.getAttribute('data-product-name') || '') : '';
      var productImage = option ? (option.getAttribute('data-product-image') || '') : '';

      if (!productId || productId === '0') {
        preview.classList.add('dfc-featured-preview--hidden');
        name.textContent = '';
        id.textContent = '';
        image.setAttribute('src', '');
        image.style.display = 'none';
        return;
      }

      preview.classList.remove('dfc-featured-preview--hidden');
      name.textContent = productName;
      id.textContent = 'ID: ' + productId;

      if (productImage) {
        image.setAttribute('src', productImage);
        image.style.display = '';
      } else {
        image.setAttribute('src', '');
        image.style.display = 'none';
      }
    }

    if (!select.__dfcFeaturedPreviewBound) {
      select.__dfcFeaturedPreviewBound = true;

      select.addEventListener('change', updatePreview);

      if (typeof jQuery !== 'undefined') {
        jQuery(select).on('change.dfcFeaturedPreview', updatePreview);
      }

      removeBtn.addEventListener('click', function(e){
        e.preventDefault();
        select.value = '';

        if (typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.select2) {
          jQuery(select).val('').trigger('change');
        } else {
          updatePreview();
        }
      });
    }

    select.__dfcFeaturedPreviewUpdate = updatePreview;
    updatePreview();
  }

  function initFeaturedCountdownToggle() {
    var featuredSelect = document.getElementById('dfc_featured_product');
    var toggle = document.getElementById('dfc_show_featured_countdown');
    var help = document.getElementById('dfc_show_featured_countdown_help');
    var state = document.getElementById('dfc-featured-countdown-state');

    if (!featuredSelect || !toggle || !help) {
      return;
    }

    function updateToggleState() {
      var option = featuredSelect.options[featuredSelect.selectedIndex];
      var productId = option ? (option.getAttribute('data-product-id') || '') : '';

      if (!productId || productId === '0') {
        toggle.checked = false;
        toggle.disabled = true;

        help.textContent = 'Aby włączyć countdown, najpierw musisz wybrać produkt polecany. Countdown działa wyłącznie na podstawie ID produktu polecanego.';

        if (state) {
          state.textContent = '';
        }

        return;
      }

      toggle.disabled = false;
      help.textContent = 'Po włączeniu moduł pokaże countdown na froncie, korzystając z ID aktualnie wybranego produktu polecanego.';

      if (state) {
        state.textContent = 'Countdown: ' + (toggle.checked ? 'włączony' : 'wyłączony');
      }
    }

    if (!featuredSelect.__dfcFeaturedCountdownBound) {
      featuredSelect.__dfcFeaturedCountdownBound = true;

      featuredSelect.addEventListener('change', updateToggleState);

      if (typeof jQuery !== 'undefined') {
        jQuery(featuredSelect).on('change.dfcFeaturedCountdown', updateToggleState);
      }

      toggle.addEventListener('change', function () {
        if (state && !toggle.disabled) {
          state.textContent = 'Countdown: ' + (toggle.checked ? 'włączony' : 'wyłączony');
        }
      });
    }

    featuredSelect.__dfcFeaturedCountdownUpdate = updateToggleState;
    updateToggleState();
  }

  function initBundlePreviewRow(row) {
    if (!row) return;

    var select = row.querySelector('.dfc-bundle-product');
    var preview = row.querySelector('[data-bundle-preview]');
    var image = row.querySelector('[data-bundle-preview-image]');
    var name = row.querySelector('[data-bundle-preview-name]');
    var id = row.querySelector('[data-bundle-preview-id]');
    var removeBtn = row.querySelector('[data-bundle-preview-remove]');

    if (!select || !preview || !image || !name || !id || !removeBtn) {
      return;
    }

    function updatePreview() {
      var option = select.options[select.selectedIndex];
      var productId = option ? (option.getAttribute('data-product-id') || '') : '';
      var productName = option ? (option.getAttribute('data-product-name') || '') : '';
      var productImage = option ? (option.getAttribute('data-product-image') || '') : '';

      if (!productId || productId === '0') {
        preview.classList.add('dfc-bundle-preview--hidden');
        name.textContent = '';
        id.textContent = '';
        image.setAttribute('src', '');
        image.style.display = 'none';
        return;
      }

      preview.classList.remove('dfc-bundle-preview--hidden');
      name.textContent = productName;
      id.textContent = 'ID: ' + productId;

      if (productImage) {
        image.setAttribute('src', productImage);
        image.style.display = '';
      } else {
        image.setAttribute('src', '');
        image.style.display = 'none';
      }
    }

    select.addEventListener('change', updatePreview);

    removeBtn.addEventListener('click', function(e){
      e.preventDefault();
      select.value = '';

      if (typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.select2) {
        jQuery(select).trigger('change');
      }

      updatePreview();
    });

    updatePreview();
  }

  function initExcludedCategoriesField() {
    if (typeof jQuery === 'undefined' || !jQuery.fn || !jQuery.fn.select2) {
      return;
    }

    var $select = jQuery('#dfc_excluded_category_roots');
    var $preview = jQuery('#dfc-excluded-preview');

    if (!$select.length || !$preview.length) {
      return;
    }

    if ($select.hasClass('select2-hidden-accessible')) {
      $select.select2('destroy');
    }

    $select.select2({
      width: '100%',
      placeholder: $select.attr('data-placeholder') || '— wybierz kategorie do wykluczenia —',
      closeOnSelect: false,
      allowClear: false
    });

    function escapeHtml(text) {
      return jQuery('<div>').text(text || '').html();
    }

    function renderPreview() {
      var html = '';

      $select.find('option:selected').each(function () {
        var $option = jQuery(this);
        var id = String($option.val() || '');
        var text = $option.text() || '';

        html += ''
          + '<span class="dfc-excluded-badge" data-id="' + escapeHtml(id) + '">'
          +   '<span class="dfc-excluded-badge__text">' + escapeHtml(text) + '</span>'
          +   '<button type="button" class="dfc-excluded-badge__remove" data-id="' + escapeHtml(id) + '" aria-label="Usuń">×</button>'
          + '</span>';
      });

      $preview.html(html);
    }

    $select.off('change.dfcExcluded').on('change.dfcExcluded', function () {
      renderPreview();
    });

    $preview.off('click.dfcExcludedRemove').on('click.dfcExcludedRemove', '.dfc-excluded-badge__remove', function (e) {
      e.preventDefault();

      var id = String(jQuery(this).data('id'));
      $select.find('option[value="' + id + '"]').prop('selected', false);
      $select.trigger('change');
    });

    renderPreview();
  }

  function initFeaturedProductSelect() {
    if (typeof jQuery === 'undefined' || !jQuery.fn || !jQuery.fn.select2) {
      return;
    }

    var $select = jQuery('#dfc_featured_product');

    if (!$select.length) {
      return;
    }

    if ($select.hasClass('select2-offscreen')) {
      $select.select2('destroy');
    }

    function escapeHtml(text) {
      return jQuery('<div>').text(text || '').html();
    }

    function formatProductOption(option) {
      if (!option || !option.element) {
        return '';
      }

      var $option = jQuery(option.element);
      var productId = $option.attr('data-product-id') || '';
      var productName = $option.attr('data-product-name') || option.text || '';
      var productImage = $option.attr('data-product-image') || '';

      if (!productId) {
        return '<div class="dfc-featured-option--empty">— brak —</div>';
      }

      var thumb = '';
      if (productImage) {
        thumb = '<div class="dfc-featured-option__thumb"><img src="' + escapeHtml(productImage) + '" alt=""></div>';
      } else {
        thumb = '<div class="dfc-featured-option__thumb"></div>';
      }

      return ''
        + '<div class="dfc-featured-option">'
        +   thumb
        +   '<div class="dfc-featured-option__meta">'
        +     '<div class="dfc-featured-option__name">' + escapeHtml(productName) + '</div>'
        +     '<div class="dfc-featured-option__id">ID: ' + escapeHtml(productId) + '</div>'
        +   '</div>'
        + '</div>';
    }

    function formatProductSelection(option) {
      if (!option || !option.element) {
        return '<div class="dfc-featured-select-choice--empty">— szukaj produktu —</div>';
      }

      var $option = jQuery(option.element);
      var productId = $option.attr('data-product-id') || '';
      var productName = $option.attr('data-product-name') || option.text || '';
      var productImage = $option.attr('data-product-image') || '';

      if (!productId) {
        return '<div class="dfc-featured-select-choice--empty">— szukaj produktu —</div>';
      }

      var thumb = '';
      if (productImage) {
        thumb = '<div class="dfc-featured-select-choice__thumb"><img src="' + escapeHtml(productImage) + '" alt=""></div>';
      } else {
        thumb = '<div class="dfc-featured-select-choice__thumb"></div>';
      }

      return ''
        + '<div class="dfc-featured-select-choice">'
        +   thumb
        +   '<div class="dfc-featured-select-choice__meta">'
        +     '<div class="dfc-featured-select-choice__name">' + escapeHtml(productName) + '</div>'
        +     '<div class="dfc-featured-select-choice__id">ID: ' + escapeHtml(productId) + '</div>'
        +   '</div>'
        + '</div>';
    } 

    $select.select2({
      width: '100%',
      allowClear: true,
      placeholder: $select.attr('data-placeholder') || '— szukaj produktu —',
      formatResult: formatProductOption,
      formatSelection: formatProductSelection,
      escapeMarkup: function (m) { return m; }
    });

    $select.off('change.dfcFeaturedSync').on('change.dfcFeaturedSync', function () {
      var nativeSelect = $select.get(0);

      if (nativeSelect && typeof nativeSelect.__dfcFeaturedPreviewUpdate === 'function') {
        nativeSelect.__dfcFeaturedPreviewUpdate();
      }

      if (nativeSelect && typeof nativeSelect.__dfcFeaturedCountdownUpdate === 'function') {
        nativeSelect.__dfcFeaturedCountdownUpdate();
      }
    });
  }

  function initBundleSection() {
    var container = document.getElementById('dfc-bundle-list');
    var addBtn = document.getElementById('dfc-add-bundle-item');
    var featuredSelect = document.getElementById('dfc_featured_product');

    if (!container || !addBtn || !featuredSelect) return;

    function getNextBundleIndex() {
      var rows = container.querySelectorAll('.dfc-bundle-row');
      return rows.length;
    }

    addBtn.addEventListener('click', function () {
      var index = getNextBundleIndex();

      var row = document.createElement('div');
      row.className = 'dfc-bundle-row';

      row.innerHTML = `
        <div class="dfc-bundle-row__top">
          <div class="dfc-bundle-row__product">
            <label>Produkt</label>

            <select
              name="dfc_bundle_product[${index}]"
              class="form-control dfc-select2 dfc-bundle-product"
              data-placeholder="— wybierz produkt —"
              data-bundle-index="${index}"
            >
              <option value=""></option>
                ${featuredSelect.innerHTML}
            </select>

            <div class="dfc-bundle-preview dfc-bundle-preview--hidden" data-bundle-preview>
              <div class="dfc-bundle-preview__inner">
                <div class="dfc-bundle-preview__image-wrap">
                  <img
                    src=""
                    alt=""
                    class="dfc-bundle-preview__image"
                    data-bundle-preview-image
                    style="display:none;"
                  >
                </div>

                <div class="dfc-bundle-preview__content">
                  <div class="dfc-bundle-preview__label">Wybrany produkt</div>
                  <div class="dfc-bundle-preview__name" data-bundle-preview-name></div>
                  <div class="dfc-bundle-preview__meta" data-bundle-preview-id></div>

                  <button
                    type="button"
                    class="btn btn-default dfc-bundle-preview__remove"
                    data-bundle-preview-remove
                  >
                    Usuń wybór
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="dfc-bundle-row__bottom">
          <div class="dfc-bundle-row__label">
            <label>Podpis na froncie</label>
            <input
              type="text"
              name="dfc_bundle_label[${index}]"
              class="form-control dfc-input"
              placeholder="np. Komoda / Lustro / Toaletka"
            >
            <p class="help-block dfc-help-block">
               Krótka nazwa widoczna na froncie zamiast pełnej nazwy produktu.
            </p>
          </div>

          <div class="dfc-bundle-row__side">
            <div class="dfc-bundle-row__active">
              <label class="dfc-bundle-checkbox">
                <input
                  type="checkbox"
                  name="dfc_bundle_active[${index}]"
                  value="1"
                  checked
                >
                <span>Aktywny produkt w zestawie</span>
              </label>
            </div>

            <div class="dfc-bundle-row__remove">
              <button type="button" class="btn btn-danger dfc-bundle-remove">
                Usuń produkt z zestawu
              </button>
            </div>
          </div>
        </div>
      `;

      container.appendChild(row);

      if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
        jQuery(row).find('.dfc-select2').select2({
          width: '100%'
        });
      }

      initBundlePreviewRow(row);
    });

    container.addEventListener('click', function (e) {
      if (e.target.classList.contains('dfc-bundle-remove')) {
        var row = e.target.closest('.dfc-bundle-row');
        if (row) {
          row.remove();
        }
      }
    });
  }

  bindFileRow('DFCOLL_IMG_DESKTOP');
  bindFileRow('DFCOLL_IMG_COMPARE');
  bindFileRow('DFCOLL_IMG_ARRANGEMENT');
  bindFileRow('DFCOLL_IMG_MOBILE');
  bindFileRow('DFCOLL_IMG_XS');

  initFeaturedProductSelect();
  initFeaturedPreview();
  initFeaturedCountdownToggle();
  initComparePercentSlider();
  initExcludedCategoriesField();
  initBundleSection();

  document.querySelectorAll('.dfc-bundle-row').forEach(function(row) {
    initBundlePreviewRow(row);
  });
})();
</script>
{/literal}