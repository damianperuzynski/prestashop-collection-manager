<?php
if (!defined('_PS_VERSION_')) { exit; }

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;

class DfCollection extends Module
{
    public function __construct()
    {
        $this->name = 'dfcollection';
        $this->version = '1.0.0';
        $this->author = 'Damian Perużyński';
        $this->tab = 'front_office_features';
        $this->need_instance = 0;

        parent::__construct();
        $this->displayName = $this->l('DF Collection Block');
        $this->description = $this->l('Twórz sekcje kolekcji na stronie głównej – z dużym obrazem, krótkim opisem, polecanym produktem i sliderem produktów z wybranej kategorii.');
    }

    public function install()
    {
        return parent::install()
            && $this->installDb()
            && Configuration::updateValue('DFC_HEADING', 'KOLEKCJE')
            && Configuration::updateValue('DFC_HEADING_LINK_CATEGORY', 0)
            && Configuration::updateValue('DFC_EXCLUDED_CATEGORY_ROOTS', '')
            && Configuration::updateValue('DFC_CACHE_MTIME', time())
            && $this->registerHook('displayHome')
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayBackOfficeHeader')
            && ($this->ensureUploadDir() || true);
    }

    public function uninstall()
    {
        Configuration::deleteByName('DFC_HEADING');
        Configuration::deleteByName('DFC_HEADING_LINK_CATEGORY');
        Configuration::deleteByName('DFC_EXCLUDED_CATEGORY_ROOTS');
        Configuration::deleteByName('DFC_CACHE_MTIME');
        Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'dfcollection_bundle_item`');
        $this->dfcCacheClearAllFiles();
        return parent::uninstall();
    }

    protected function installDb()
    {
        // CREATE z trzema kolumnami obrazów (desktop/mobile/xs)
        $ok = Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'dfcollection` (
                `id_dfcollection` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `position` INT NOT NULL DEFAULT 0,
                `active` TINYINT(1) NOT NULL DEFAULT 1,
                `id_category` INT UNSIGNED NOT NULL,
                `id_featured_product` INT UNSIGNED DEFAULT NULL,
				`show_featured_countdown` TINYINT(1) NOT NULL DEFAULT 0,
                `title` VARCHAR(255) DEFAULT NULL,
                `image_url` VARCHAR(1024) DEFAULT NULL,
                `image_url_mobile` VARCHAR(1024) DEFAULT NULL,
                `image_url_xs` VARCHAR(1024) DEFAULT NULL,
                `image_compare_url` VARCHAR(1024) DEFAULT NULL,
                `arrangement_image_url` VARCHAR(1024) DEFAULT NULL,
                `compare_start_percent` INT NOT NULL DEFAULT 50,
                `compare_label` VARCHAR(255) DEFAULT NULL,
                `slider_infinite` TINYINT(1) NOT NULL DEFAULT 1,
                `slider_sort` VARCHAR(32) NOT NULL DEFAULT "random",
                `short_description` TEXT DEFAULT NULL,
                `collection_scope` VARCHAR(1000) DEFAULT NULL,
                `badge_1` VARCHAR(255) DEFAULT NULL,
                `badge_2` VARCHAR(255) DEFAULT NULL,
                `badge_3` VARCHAR(255) DEFAULT NULL,
                `badge_4` VARCHAR(255) DEFAULT NULL,
                PRIMARY KEY (`id_dfcollection`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8mb4;
        ');

        // MIGRACJE: dołóż brakujące kolumny, jeśli tabela już istniała
        $db = Db::getInstance();

		$hasShowFeaturedCountdown = (int)$db->getValue('
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "'._DB_PREFIX_.'dfcollection"
              AND COLUMN_NAME = "show_featured_countdown"
        ');
        if (!$hasShowFeaturedCountdown) {
            $ok &= $db->execute('
                ALTER TABLE `'._DB_PREFIX_.'dfcollection`
                ADD `show_featured_countdown` TINYINT(1) NOT NULL DEFAULT 0
            ');
        }

        $hasMobile = (int)$db->getValue('
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "'._DB_PREFIX_.'dfcollection"
              AND COLUMN_NAME = "image_url_mobile"
        ');
        if (!$hasMobile) {
            $ok &= $db->execute('
                ALTER TABLE `'._DB_PREFIX_.'dfcollection`
                ADD `image_url_mobile` VARCHAR(1024) DEFAULT NULL
            ');
        }

        $hasXS = (int)$db->getValue('
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "'._DB_PREFIX_.'dfcollection"
              AND COLUMN_NAME = "image_url_xs"
        ');
        if (!$hasXS) {
            $ok &= $db->execute('
                ALTER TABLE `'._DB_PREFIX_.'dfcollection`
                ADD `image_url_xs` VARCHAR(1024) DEFAULT NULL
            ');
        }

        $hasCompareImage = (int)$db->getValue('
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "'._DB_PREFIX_.'dfcollection"
              AND COLUMN_NAME = "image_compare_url"
        ');
        if (!$hasCompareImage) {
            $ok &= $db->execute('
                ALTER TABLE `'._DB_PREFIX_.'dfcollection`
                ADD `image_compare_url` VARCHAR(1024) DEFAULT NULL
            ');
        }

        $hasArrangementImage = (int)$db->getValue('
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "'._DB_PREFIX_.'dfcollection"
              AND COLUMN_NAME = "arrangement_image_url"
        ');
        if (!$hasArrangementImage) {
            $ok &= $db->execute('
                ALTER TABLE `'._DB_PREFIX_.'dfcollection`
                ADD `arrangement_image_url` VARCHAR(1024) DEFAULT NULL
            ');
        }

        $hasCompareStart = (int)$db->getValue('
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "'._DB_PREFIX_.'dfcollection"
              AND COLUMN_NAME = "compare_start_percent"
        ');
        if (!$hasCompareStart) {
            $ok &= $db->execute('
                ALTER TABLE `'._DB_PREFIX_.'dfcollection`
                ADD `compare_start_percent` INT NOT NULL DEFAULT 50
            ');
        }

        $hasCompareLabel = (int)$db->getValue('
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "'._DB_PREFIX_.'dfcollection"
              AND COLUMN_NAME = "compare_label"
        ');
        if (!$hasCompareLabel) {
             $ok &= $db->execute('
                 ALTER TABLE `'._DB_PREFIX_.'dfcollection`
                 ADD `compare_label` VARCHAR(255) DEFAULT NULL
             ');
        }

        $hasSliderLimit = (int)$db->getValue('
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "'._DB_PREFIX_.'dfcollection"
              AND COLUMN_NAME = "slider_limit"
        ');
        if (!$hasSliderLimit) {
            $ok &= $db->execute('
                ALTER TABLE `'._DB_PREFIX_.'dfcollection`
                ADD `slider_limit` INT NOT NULL DEFAULT 8
            ');
        }

        $hasSliderInfinite = (int)$db->getValue('
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "'._DB_PREFIX_.'dfcollection"
              AND COLUMN_NAME = "slider_infinite"
        ');
        if (!$hasSliderInfinite) {
            $ok &= $db->execute('
                ALTER TABLE `'._DB_PREFIX_.'dfcollection`
                ADD `slider_infinite` TINYINT(1) NOT NULL DEFAULT 1
           ');
        }

        $hasSliderSort = (int)$db->getValue('
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "'._DB_PREFIX_.'dfcollection"
             AND COLUMN_NAME = "slider_sort"
        ');
        if (!$hasSliderSort) {
            $ok &= $db->execute('
                ALTER TABLE `'._DB_PREFIX_.'dfcollection`
                ADD `slider_sort` VARCHAR(32) NOT NULL DEFAULT "random"
            ');
        }

        $hasShortDescription = (int)$db->getValue('
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "'._DB_PREFIX_.'dfcollection"
              AND COLUMN_NAME = "short_description"
        ');
        if (!$hasShortDescription) {
            $ok &= $db->execute('
                ALTER TABLE `'._DB_PREFIX_.'dfcollection`
                ADD `short_description` TEXT DEFAULT NULL
            ');
        }

        $hasCollectionScope = (int)$db->getValue('
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "'._DB_PREFIX_.'dfcollection"
              AND COLUMN_NAME = "collection_scope"
        ');
        if (!$hasCollectionScope) {
            $ok &= $db->execute('
                ALTER TABLE `'._DB_PREFIX_.'dfcollection`
                ADD `collection_scope` VARCHAR(1000) DEFAULT NULL
            ');
        }

        $hasBadge1 = (int)$db->getValue('
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "'._DB_PREFIX_.'dfcollection"
              AND COLUMN_NAME = "badge_1"
        ');
        if (!$hasBadge1) {
            $ok &= $db->execute('
                ALTER TABLE `'._DB_PREFIX_.'dfcollection`
                ADD `badge_1` VARCHAR(255) DEFAULT NULL
            ');
        }

        $hasBadge2 = (int)$db->getValue('
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "'._DB_PREFIX_.'dfcollection"
              AND COLUMN_NAME = "badge_2"
        ');
        if (!$hasBadge2) {
            $ok &= $db->execute('
                ALTER TABLE `'._DB_PREFIX_.'dfcollection`
                ADD `badge_2` VARCHAR(255) DEFAULT NULL
           ');
        }

        $hasBadge3 = (int)$db->getValue('
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "'._DB_PREFIX_.'dfcollection"
              AND COLUMN_NAME = "badge_3"
        ');
        if (!$hasBadge3) {
            $ok &= $db->execute('
                ALTER TABLE `'._DB_PREFIX_.'dfcollection`
                ADD `badge_3` VARCHAR(255) DEFAULT NULL
            ');
        }

        $hasBadge4 = (int)$db->getValue('
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "'._DB_PREFIX_.'dfcollection"
              AND COLUMN_NAME = "badge_4"
        ');
        if (!$hasBadge4) {
            $ok &= $db->execute('
                ALTER TABLE `'._DB_PREFIX_.'dfcollection`
                ADD `badge_4` VARCHAR(255) DEFAULT NULL
            ');
        }

        $ok &= Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'dfcollection_bundle_item` (
                `id_dfcollection_bundle_item` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_dfcollection` INT UNSIGNED NOT NULL,
                `id_product` INT UNSIGNED NOT NULL,
                `custom_label` VARCHAR(255) DEFAULT NULL,
                `position` INT NOT NULL DEFAULT 0,
                `active` TINYINT(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id_dfcollection_bundle_item`),
                KEY `idx_dfc_bundle_collection` (`id_dfcollection`),
                KEY `idx_dfc_bundle_product` (`id_product`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8mb4;
        ');

        return (bool)$ok;
    }

    public function hookDisplayHeader()
    {
        Media::addJsDef([
            '__dfc' => [
                'ajax' => $this->context->link->getModuleLink($this->name, 'switch'),
                'bundlecart' => $this->context->link->getModuleLink($this->name, 'bundlecart'),
                'currency' => [
                    'sign' => (string)$this->context->currency->sign,
                    'format' => (int)$this->context->currency->format,
                    'blank' => (int)$this->context->currency->blank,
                    'decimals' => 2,
                ],        
            ]
        ]);

        $this->context->controller->registerJavascript(
            'dfcollection-js',
            'modules/'.$this->name.'/views/js/dfcollection.js',
            ['position' => 'bottom', 'priority' => 150]
        );
        $this->context->controller->registerStylesheet(
            'dfcollection-css',
            'modules/'.$this->name.'/views/css/dfcollection.css',
            ['media' => 'all', 'priority' => 150]
        );
    }

    public function hookDisplayHome($params)
    {
        if ($this->dfcCacheEnabled()) {
            $key = $this->dfcGetContextCacheKey('displayHome');
            $cached = $this->dfcCacheRead($key);

            if ($cached !== false) {
                return $cached;
           }
        }
      
        // Taby / kolekcje – tylko aktywne kolekcje
        $collections = $this->getCollections(true);
        $tabs = $collections;

        // Aktywne – do pierwszego renderu
        if (empty($collections) || empty($tabs)) {
            if ($this->dfcCacheEnabled()) {
                $this->dfcCacheWrite($key, '');
            }
            return '';
        }

        $current  = $collections[0];
        $assigned = $this->buildFrontData(
            (int)$current['id_dfcollection'],
            (int)$current['id_category'],
            (int)$current['id_featured_product'],
            $current['image_url'],
            isset($current['effective_title']) ? $current['effective_title'] : $current['title'],
            isset($current['image_url_mobile']) ? $current['image_url_mobile'] : null,
            isset($current['image_url_xs']) ? $current['image_url_xs'] : null,
            isset($current['image_compare_url']) ? $current['image_compare_url'] : null,
            isset($current['arrangement_image_url']) ? $current['arrangement_image_url'] : null,
            isset($current['compare_start_percent']) ? (int)$current['compare_start_percent'] : 50,
            isset($current['compare_label']) ? $current['compare_label'] : '',
            isset($current['slider_limit']) ? (int)$current['slider_limit'] : 8,
            isset($current['slider_infinite']) ? (int)$current['slider_infinite'] : 1,
            isset($current['slider_sort']) ? (string)$current['slider_sort'] : 'random',
            isset($current['short_description']) ? $current['short_description'] : '',
            isset($current['collection_scope']) ? $current['collection_scope'] : '',
            isset($current['badge_1']) ? $current['badge_1'] : '',
            isset($current['badge_2']) ? $current['badge_2'] : '',
            isset($current['badge_3']) ? $current['badge_3'] : '',
            isset($current['badge_4']) ? $current['badge_4'] : '',
			isset($current['show_featured_countdown']) ? (int)$current['show_featured_countdown'] : 0
        );

        $heading = Configuration::get('DFC_HEADING') ?: 'KOLEKCJE';
        $headingLinkCategory = (int)Configuration::get('DFC_HEADING_LINK_CATEGORY');
        $headingLink = '';

        if ($headingLinkCategory > 0) {
            $headingLink = $this->context->link->getCategoryLink($headingLinkCategory);
        }

        $this->context->smarty->assign(array_merge($assigned, [
            'dfc_tabs'                  => $tabs,
            'dfc_collections'           => $collections,
            'dfc_total_collections'     => (int)count($collections),
            'dfc_ajax_url'              => $this->context->link->getModuleLink($this->name, 'switch'),
            'dfc_heading'               => $heading,
            'dfc_heading_link'          => $headingLink,
            'dfc_heading_link_category' => $headingLinkCategory,
        ]));

        $html = $this->fetch('module:'.$this->name.'/views/templates/hook/dfcollection.tpl');

        if ($this->dfcCacheEnabled()) {
            $this->dfcCacheWrite($key, $html);
        }

        return $html;
    }

    public function hookDisplayBackOfficeHeader($params)
    {
        // ładuj tylko na stronie konfiguracji tego modułu
        if (Tools::getValue('controller') !== 'AdminModules' || Tools::getValue('configure') !== $this->name) {
            return;
        }

        $ctrl = $this->context->controller;

        // jQuery + Select2 (w BO jest dostępny jako plugin)
        if (method_exists($ctrl, 'addJquery')) {
            $ctrl->addJquery();
        }

        // ⬅️ Doładuj Select2 z Presty
        if (method_exists($ctrl, 'addJqueryPlugin')) {
            $ctrl->addJqueryPlugin('select2');
        }

        // <-- TUTAJ DODAJ CSS ADMINA
        $ctrl->addCSS(_MODULE_DIR_.$this->name.'/views/css/admin.css');

        // Sortable (lokalny albo CDN)
        if (is_file(_PS_MODULE_DIR_.$this->name.'/views/js/Sortable.min.js')) {
           $ctrl->addJS(_MODULE_DIR_.$this->name.'/views/js/Sortable.min.js');
        } else {
            // awaryjnie z CDN
            $ctrl->addJS('https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js');
        }

        // nasz skrypt (drag&drop + Select2 init + komunikaty)
        $ctrl->addJS(_MODULE_DIR_.$this->name.'/views/js/dfc-admin-sort.js');
    }

    protected function getCollections($onlyActive = true)
    {
        $idLang = (int)$this->context->language->id;
        $idShop = (int)$this->context->shop->id;

        $sql = new DbQuery();
        $sql->select('c.*, cl.name AS cat_name, pl.name AS prod_name, i.id_image AS prod_cover_id')
            ->from('dfcollection', 'c')
            ->leftJoin(
                'category_lang',
                'cl',
                'cl.id_category = c.id_category AND cl.id_lang = '.$idLang.Shop::addSqlRestrictionOnLang('cl')
            )
            ->leftJoin(
                'product_lang',
                'pl',
                'pl.id_product = c.id_featured_product AND pl.id_lang = '.$idLang.' AND pl.id_shop = '.$idShop
            )
            ->leftJoin(
                'image_shop',
                'i',
                'i.id_product = c.id_featured_product AND i.cover = 1 AND i.id_shop = '.$idShop
           )
           ->orderBy('c.position ASC');

        if ($onlyActive) {
            $sql->where('c.active = 1');
        }

        $rows = Db::getInstance()->executeS($sql) ?: [];

        foreach ($rows as &$r) {
			$r['show_featured_countdown'] = isset($r['show_featured_countdown']) ? (int)$r['show_featured_countdown'] : 0;
            $t = isset($r['title']) ? trim((string)$r['title']) : '';
            $r['effective_title'] = ($t !== '') ? $t : (string)($r['cat_name'] ?? '');

            $r['prod_image'] = '';

            if (!empty($r['id_featured_product']) && !empty($r['prod_cover_id']) && !empty($r['prod_name'])) {
                $r['prod_image'] = $this->context->link->getImageLink(
                    Tools::link_rewrite((string)$r['prod_name']),
                    (int)$r['id_featured_product'].'-'.(int)$r['prod_cover_id'],
                    ImageType::getFormattedName('small')
                );
            }
            $r['bundle_items'] = $this->getBundleItems((int)$r['id_dfcollection'], true);
        }
        unset($r);

        return $rows;
    }

    // ⬇️ rozszerzone o $imageUrlXS
    protected function buildFrontData(
        $idDfcollection,
        $idCategory,
        $idFeaturedProduct = null,
        $imageUrl = null,
        $title = null,
        $imageUrlMobile = null,
        $imageUrlXS = null,
        $imageCompareUrl = null,
        $arrangementImageUrl = null,
        $compareStartPercent = 50,
        $compareLabel = '',
        $sliderLimit = 8,
        $sliderInfinite = 1,
        $sliderSort = 'random',
        $shortDescription = '',
        $collectionScope = '',
        $badge1 = '',
        $badge2 = '',
        $badge3 = '',
        $badge4 = '',
		$showFeaturedCountdown = 0
    )
    {
        $idDfcollection = (int)$idDfcollection;
        $idLang = (int)$this->context->language->id;
        $productsCount = $this->getCollectionProductsCount((int)$idCategory);
        $freeShippingData = $this->getCollectionFreeShippingFromData((int)$idCategory);

        $featured = null;
        $featuredUrl = '';

        $bundleItems = $this->getBundleFrontItems($idDfcollection);

        $bundleTotalRegularPriceAmount = $this->getBundleTotalRegularPriceAmount($bundleItems);
        $bundleTotalPriceAmount = $this->getBundleTotalPriceAmount($bundleItems);
        $bundleTotalSavingsAmount = $this->getBundleTotalSavingsAmount($bundleItems);
        $bundleShippingFromAmount = $this->getBundleShippingFromAmount($bundleItems);
        $bundleDeliveryText = $this->getBundleDeliveryText($bundleItems);

        $bundleTotalRegularPrice = $this->formatBundleAmount($bundleTotalRegularPriceAmount);
        $bundleTotalPrice = $this->formatBundleAmount($bundleTotalPriceAmount);
        $bundleTotalSavings = $this->formatBundleAmount($bundleTotalSavingsAmount);
        $bundleShippingFrom = $this->formatBundleAmount($bundleShippingFromAmount);
        $lowestPriceData = $this->getCollectionLowestPriceData((int)$idCategory);

        if ($idFeaturedProduct) {
            $featured = $this->presentOneById((int)$idFeaturedProduct);

            if ($featured && !empty($featured['url'])) {
                $featuredUrl = (string)$featured['url'];
            } else {
               $featuredUrl = $this->context->link->getProductLink((int)$idFeaturedProduct);
            }
        }

		$showFeaturedCountdown = (int)$showFeaturedCountdown ? 1 : 0;
        $featuredCountdownProductId = 0;

        if ($showFeaturedCountdown && (int)$idFeaturedProduct > 0) {
            $featuredCountdownProductId = (int)$idFeaturedProduct;
        }

        // limit per kategoria (zakres 1..20)
        $limit = (int)$sliderLimit;
        if ($limit < 1)  { $limit = 1; }
        if ($limit > 20) { $limit = 20; }

        // pobierz większą pulę produktów, żeby po wykluczeniu featured nadal było z czego ciąć
        $products = $this->presentFromCategory((int)$idCategory, $idLang, 30, $sliderSort);

        // wyklucz produkt polecany ze slidera
        if ((int)$idFeaturedProduct > 0) {
            $featuredId = (int)$idFeaturedProduct;

            $products = array_values(array_filter($products, function ($product) use ($featuredId) {
                if (is_array($product) && isset($product['id_product'])) {
                    return (int)$product['id_product'] !== $featuredId;
                }

                if (is_object($product) && isset($product->id_product)) {
                    return (int)$product->id_product !== $featuredId;
                }

                return true;
            }));
        }

        // dopiero po odfiltrowaniu featured przytnij do ustawionego limitu
        $products = array_slice($products, 0, $limit);

        $cat  = new Category($idCategory, $idLang);
        $link = $this->context->link->getCategoryLink($idCategory);

        return [
            'dfc_title'             => $title ?: $cat->name,
            'dfc_short_description' => isset($shortDescription) ? (string)$shortDescription : '',
            'dfc_collection_scope'  => trim((string)$collectionScope),
            'dfc_badge_1'           => trim((string)$badge1),
            'dfc_badge_2'           => trim((string)$badge2),
            'dfc_badge_3'           => trim((string)$badge3),
            'dfc_badge_4'           => trim((string)$badge4),
            'dfc_lowest_price'                 => $lowestPriceData['price'],
            'dfc_lowest_price_amount'          => $lowestPriceData['price_amount'],
            'dfc_lowest_price_regular'         => $lowestPriceData['regular_price'],
            'dfc_lowest_price_regular_amount'  => $lowestPriceData['regular_price_amount'],
            'dfc_lowest_price_has_discount'    => $lowestPriceData['has_discount'],
            'dfc_lowest_price_discount_label'  => $lowestPriceData['discount_label'],
            'dfc_lowest_price_product_id'      => $lowestPriceData['id_product'],
            'dfc_lowest_price_product_name'    => $lowestPriceData['name'],
            'dfc_lowest_price_product_url'     => $lowestPriceData['url'],
            'dfc_image_url'         => $imageUrl ?: '',
            'dfc_image_url_mobile'  => $imageUrlMobile ?: '',
            'dfc_image_url_xs'      => $imageUrlXS ?: '',
            'dfc_image_compare_url'     => $imageCompareUrl ?: '',
            'dfc_arrangement_image_url' => $arrangementImageUrl ?: '',
            'dfc_compare_start_percent' => max(0, min(100, (int)$compareStartPercent)),
            'dfc_compare_label'         => (string)$compareLabel,
            'dfc_featured'          => $featured,
            'dfc_featured_url'      => $featuredUrl,
			'dfc_show_featured_countdown'        => $showFeaturedCountdown,
            'dfc_featured_countdown_product_id'  => $featuredCountdownProductId,
            'dfc_products'          => $products,
            'dfc_products_count'    => (int)$productsCount,
            'dfc_current_cat_id'    => (int)$idCategory,
            'dfc_category_link'     => $link,
            'dfc_category_name'     => (string)$cat->name,
            'dfc_slider_infinite'   => (int)$sliderInfinite,
            'dfc_bundle_items'                    => $bundleItems,
            'dfc_bundle_total_regular_price'      => $bundleTotalRegularPrice,
            'dfc_bundle_total_price'              => $bundleTotalPrice,
            'dfc_bundle_total_savings'            => $bundleTotalSavings,
            'dfc_bundle_total_regular_price_amount' => $bundleTotalRegularPriceAmount,
            'dfc_bundle_total_price_amount'         => $bundleTotalPriceAmount,
            'dfc_bundle_total_savings_amount'       => $bundleTotalSavingsAmount,
            'dfc_bundle_delivery_text'              => $bundleDeliveryText,
            'dfc_bundle_shipping_from'              => $bundleShippingFrom,
            'dfc_bundle_shipping_from_amount'       => $bundleShippingFromAmount,
            'dfc_free_shipping_from_amount' => $freeShippingData['amount'],
            'dfc_free_shipping_from'        => $freeShippingData['formatted'],
        ];
    }

    protected function presentProductsForListing(array $rawProducts)
    {
        $productsForTemplate = [];

        if (empty($rawProducts)) {
            return $productsForTemplate;
        }

        $assembler = new ProductAssembler($this->context);
        $presenterFactory = new ProductPresenterFactory($this->context);
        $presentationSettings = $presenterFactory->getPresentationSettings();

        $presenter = new ProductListingPresenter(
            new ImageRetriever($this->context->link),
            $this->context->link,
            new PriceFormatter(),
            new ProductColorsRetriever(),
            $this->context->getTranslator()
        );

        foreach ($rawProducts as $rawProduct) {
            if (!$rawProduct) {
                continue;
            }

            $productsForTemplate[] = $presenter->present(
                $presentationSettings,
                $assembler->assembleProduct($rawProduct),
                $this->context->language
            );
        }

        return $productsForTemplate;
    }
  
    protected function presentFromCategory($idCategory, $idLang, $limit = null, $sort = 'random')
    {
        if ($limit === null) {
            $limit = 8;
        }
        if ($limit < 1) {
            $limit = 1;
        }
        if ($limit > 20) {
            $limit = 20;
        }

        $orderBy = 'position';
        $orderWay = 'ASC';

        switch ($sort) {
            case 'random':
                $orderBy = 'position';
                $orderWay = 'ASC';
                break;

            case 'newest':
                $orderBy = 'date_add';
                $orderWay = 'DESC';
                break;

            case 'price_asc':
                $orderBy = 'price';
                $orderWay = 'ASC';
                break;

            case 'price_desc':
                $orderBy = 'price';
                $orderWay = 'DESC';
                break;

            case 'bestseller':
                $orderBy = 'sales';
                $orderWay = 'DESC';
                break;

            case 'position':
            default:
                $orderBy = 'position';
                $orderWay = 'ASC';
                break;
        }

        $ids = Product::getProducts($idLang, 0, 30, $orderBy, $orderWay, (int)$idCategory, true);
        if (!is_array($ids) || empty($ids)) {
            return [];
        }

        if ($sort === 'random') {
            shuffle($ids);
        }

        $ids = array_slice($ids, 0, $limit);

        $rawProducts = [];

        foreach ($ids as $prod) {
            if (empty($prod['id_product'])) {
                continue;
            }

            $idProduct = (int)$prod['id_product'];
            $raw = (array) new Product($idProduct, false, (int)$idLang);

            if (empty($raw['id'])) {
                continue;
            }

            $raw['id_product'] = $idProduct;

            $cover = Product::getCover($idProduct);
            if (!empty($cover['id_image'])) {
                $raw['id_image'] = $idProduct . '-' . (int)$cover['id_image'];
            }

            $rawProducts[] = $raw;
        }

        return $this->presentProductsForListing($rawProducts);
    }

    protected function presentOneById($idProduct)
    {
        $idProduct = (int)$idProduct;
        $idLang = (int)$this->context->language->id;

        $raw = (array) new Product($idProduct, false, $idLang);
        if (empty($raw['id'])) {
            return null;
        }

        $raw['id_product'] = $idProduct;

        $cover = Product::getCover($idProduct);
        if (!empty($cover['id_image'])) {
            $raw['id_image'] = $idProduct . '-' . (int)$cover['id_image'];
        }

        $presented = $this->presentProductsForListing([$raw]);

        return !empty($presented[0]) ? $presented[0] : null;
    }

    // ⬇️ ZAMIANA: dbaj o obie kolumny (mobile + xs)
    private function ensureImageCols()
    {
        $db = Db::getInstance();

        $hasMobile = (int)$db->getValue('
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "'._DB_PREFIX_.'dfcollection"
              AND COLUMN_NAME = "image_url_mobile"
        ');
        if (!$hasMobile) {
            $db->execute('
                ALTER TABLE `'._DB_PREFIX_.'dfcollection`
                ADD `image_url_mobile` VARCHAR(1024) DEFAULT NULL
            ');
        }

        $hasXS = (int)$db->getValue('
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "'._DB_PREFIX_.'dfcollection"
              AND COLUMN_NAME = "image_url_xs"
        ');
        if (!$hasXS) {
            $db->execute('
                ALTER TABLE `'._DB_PREFIX_.'dfcollection`
                ADD `image_url_xs` VARCHAR(1024) DEFAULT NULL
            ');
        }
    }

    private function ensureCompareImageCol()
    {
        $db = Db::getInstance();
        $has = (int)$db->getValue('
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "'._DB_PREFIX_.'dfcollection"
              AND COLUMN_NAME = "image_compare_url"
        ');
        if (!$has) {
            $db->execute('
                ALTER TABLE `'._DB_PREFIX_.'dfcollection`
                ADD `image_compare_url` VARCHAR(1024) DEFAULT NULL
            ');
        }
    }

    private function ensureArrangementImageCol()
    {
        $db = Db::getInstance();
        $has = (int)$db->getValue('
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "'._DB_PREFIX_.'dfcollection"
              AND COLUMN_NAME = "arrangement_image_url"
        ');
        if (!$has) {
            $db->execute('
                ALTER TABLE `'._DB_PREFIX_.'dfcollection`
                ADD `arrangement_image_url` VARCHAR(1024) DEFAULT NULL
            ');
        }
    }

    private function ensureCompareStartPercentCol()
    {
        $db = Db::getInstance();
        $has = (int)$db->getValue('
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "'._DB_PREFIX_.'dfcollection"
              AND COLUMN_NAME = "compare_start_percent"
        ');
        if (!$has) {
            $db->execute('
                ALTER TABLE `'._DB_PREFIX_.'dfcollection`
                ADD `compare_start_percent` INT NOT NULL DEFAULT 50
            ');
        }
    }

    private function ensureCompareLabelCol()
    {
        $db = Db::getInstance();
        $has = (int)$db->getValue('
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "'._DB_PREFIX_.'dfcollection"
              AND COLUMN_NAME = "compare_label"
        ');
        if (!$has) {
            $db->execute('
                ALTER TABLE `'._DB_PREFIX_.'dfcollection`
                ADD `compare_label` VARCHAR(255) DEFAULT NULL
            ');
        }
    }

    private function ensureSliderLimitCol()
    {
        $db = Db::getInstance();
        $has = (int)$db->getValue('
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "'._DB_PREFIX_.'dfcollection"
              AND COLUMN_NAME = "slider_limit"
        ');
        if (!$has) {
            $db->execute('
                ALTER TABLE `'._DB_PREFIX_.'dfcollection`
                ADD `slider_limit` INT NOT NULL DEFAULT 8
            ');
        }
    }

    private function ensureSliderInfiniteCol()
    {
        $db = Db::getInstance();
        $has = (int)$db->getValue('
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "'._DB_PREFIX_.'dfcollection"
              AND COLUMN_NAME = "slider_infinite"
        ');
        if (!$has) {
            $db->execute('
                ALTER TABLE `'._DB_PREFIX_.'dfcollection`
                ADD `slider_infinite` TINYINT(1) NOT NULL DEFAULT 1
            ');
        }
    }

	private function ensureShowFeaturedCountdownCol()
    {
        $db = Db::getInstance();
        $has = (int)$db->getValue('
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "'._DB_PREFIX_.'dfcollection"
              AND COLUMN_NAME = "show_featured_countdown"
        ');
        if (!$has) {
            $db->execute('
                ALTER TABLE `'._DB_PREFIX_.'dfcollection`
                ADD `show_featured_countdown` TINYINT(1) NOT NULL DEFAULT 0
            ');
        }
    }

    private function ensureSliderSortCol()
    {
        $db = Db::getInstance();
        $has = (int)$db->getValue('
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "'._DB_PREFIX_.'dfcollection"
              AND COLUMN_NAME = "slider_sort"
        ');
        if (!$has) {
            $db->execute('
                ALTER TABLE `'._DB_PREFIX_.'dfcollection`
                ADD `slider_sort` VARCHAR(32) NOT NULL DEFAULT "random"
            ');
        }
    }

    private function ensureShortDescriptionCol()
    {
        $db = Db::getInstance();
        $has = (int)$db->getValue('
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "'._DB_PREFIX_.'dfcollection"
              AND COLUMN_NAME = "short_description"
        ');
        if (!$has) {
            $db->execute('
                ALTER TABLE `'._DB_PREFIX_.'dfcollection`
                ADD `short_description` TEXT DEFAULT NULL
            ');
        }
    }

    private function ensureCollectionScopeCol()
    {
        $db = Db::getInstance();
        $has = (int)$db->getValue('
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "'._DB_PREFIX_.'dfcollection"
              AND COLUMN_NAME = "collection_scope"
        ');
        if (!$has) {
            $db->execute('
                ALTER TABLE `'._DB_PREFIX_.'dfcollection`
                ADD `collection_scope` VARCHAR(1000) DEFAULT NULL
            ');
        }
    }

    private function ensureBundleTable()
    {
        $db = Db::getInstance();

        $db->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'dfcollection_bundle_item` (
                `id_dfcollection_bundle_item` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_dfcollection` INT UNSIGNED NOT NULL,
                `id_product` INT UNSIGNED NOT NULL,
                `custom_label` VARCHAR(255) DEFAULT NULL,
                `position` INT NOT NULL DEFAULT 0,
                `active` TINYINT(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id_dfcollection_bundle_item`),
                KEY `idx_dfc_bundle_collection` (`id_dfcollection`),
                KEY `idx_dfc_bundle_product` (`id_product`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8mb4;
        ');
    }

    private function ensureBadgeCols()
    {
        $db = Db::getInstance();

        $badges = ['badge_1', 'badge_2', 'badge_3', 'badge_4'];

        foreach ($badges as $col) {
            $has = (int)$db->getValue('
                SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = "'._DB_PREFIX_.'dfcollection"
                  AND COLUMN_NAME = "'.pSQL($col).'"
            ');

            if (!$has) {
                $db->execute('
                    ALTER TABLE `'._DB_PREFIX_.'dfcollection`
                    ADD `'.bqSQL($col).'` VARCHAR(255) DEFAULT NULL
                ');
            }
        }
    }

    /** Zwraca wszystkie ID kategorii należących do drzew o podanych ID (łącznie z korzeniami). */
    private function getCategoryTreeIds(array $rootIds)
    {
        $rootIds = array_values(array_unique(array_map('intval', $rootIds)));
        if (empty($rootIds)) return [];

        $db     = Db::getInstance();
        $prefix = _DB_PREFIX_;

        // Używamy modelu zagnieżdżonych zbiorów (nleft/nright)
        // Pobieramy wszystkie kategorie c, które leżą w zakresie dowolnego korzenia r
        $sql = '
            SELECT DISTINCT c.id_category
            FROM '.$prefix.'category c
            INNER JOIN '.$prefix.'category r ON r.id_category IN ('.implode(',', $rootIds).')
            WHERE c.nleft BETWEEN r.nleft AND r.nright
        ';

        $rows = $db->executeS($sql) ?: [];
        $out  = [];
        foreach ($rows as $row) {
            $out[] = (int)$row['id_category'];
        }
        return $out;
    }
    
    /** Sformatuj bajty do KB/MB */
    private function humanSize($bytes) {
        if (!is_numeric($bytes) || $bytes < 0) return '';
        $units = ['B','KB','MB','GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units)-1) { $bytes /= 1024; $i++; }
        return rtrim(rtrim(number_format($bytes, ($i ? 1 : 0), '.', ''), '0'), '.') . ' ' . $units[$i];
    }

    /** Spróbuj ustalić rozmiar obrazka (lokalny plik → filesize, zdalny → HEAD Content-Length) */
    private function probeImageSize($url) {
        $url = trim((string)$url);
        if ($url === '') return '';

        // 1) jeśli to ścieżka relatywna do sklepu (albo absolutny URL naszego sklepu) – mapuj na dysk
        $shopBaseUrl = Tools::getShopDomainSsl(true) . __PS_BASE_URI__; // np. https://example.com/
        $localPath = '';

        if (strpos($url, '//') === false || strpos($url, $shopBaseUrl) === 0) {
            // Zrób ścieżkę absolutną w obrębie sklepu
            $relative = $url;
            if (strpos($url, $shopBaseUrl) === 0) {
                $relative = substr($url, strlen($shopBaseUrl)); // utnij domenę i base URI
            } elseif ($url[0] === '/') {
                $relative = ltrim($url, '/');
            }
            $localPath = _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);
        }

        if ($localPath && @is_file($localPath)) {
            $size = @filesize($localPath);
            return $size ? $this->humanSize($size) : '';
        }

        // 2) Próba HEAD dla zewnętrznych URL (Content-Length)
        // użyj cURL jeśli dostępny
        if (function_exists('curl_init') && preg_match('#^https?://#i', $url)) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_NOBODY        => true,
                CURLOPT_RETURNTRANSFER=> true,
                CURLOPT_FOLLOWLOCATION=> true,
                CURLOPT_TIMEOUT       => 4,
            ]);
            curl_exec($ch);
            $len = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
            curl_close($ch);
            if (is_numeric($len) && $len > 0) {
                return $this->humanSize((float)$len);
            }
        } else if (preg_match('#^https?://#i', $url)) {
            // awaryjnie: get_headers
            $headers = @get_headers($url, 1);
            if (is_array($headers)) {
                // nagłówek może być tablicą (przekierowania) – weź ostatni
                $cl = isset($headers['Content-Length']) ? $headers['Content-Length'] : null;
                if (is_array($cl)) $cl = end($cl);
                if (is_numeric($cl) && $cl > 0) {
                    return $this->humanSize((float)$cl);
                }
            }
        }

        return '';
    }

    private function probeImageDims($url)
    {
        if (!$url) return '';
        $path = _PS_ROOT_DIR_ . '/' . ltrim(parse_url($url, PHP_URL_PATH), '/');
        if (is_file($path)) {
            $info = @getimagesize($path);
            if ($info) {
                return $info[0] . ' x ' . $info[1] . ' px';
            }
        }
        return '';
    }

    /** Zwraca spłaszczoną listę kategorii do selecta: [ ['id'=>12,'label'=>'— — Sofy'], ... ] */
    private function getCategoryOptions()
    {
        $idLang = (int)$this->context->language->id;

        // Root kategorii dla sklepu (multishop-safe)
        $root = Category::getRootCategory($idLang);
        if (!$root || !Validate::isLoadedObject($root)) {
            $root = new Category((int)Configuration::get('PS_HOME_CATEGORY'), $idLang);
        }

        // Pobierz drzewo
        $tree = Category::getNestedCategories((int)$root->id, $idLang, false);

        $out = [];
        $walk = function ($nodes, $depth = 0) use (&$walk, &$out) {
            foreach ((array)$nodes as $n) {
                // pomiń samo "Home" na poziomie 0 jeśli nie chcesz go wybierać
                if ($depth === 0 && !empty($n['is_root_category'])) {
                    // ale przejdź w dzieci
                    if (!empty($n['children'])) $walk($n['children'], $depth + 1);
                    continue;
                }

                $label = str_repeat('— ', max(0, $depth)) . (string)$n['name'] . ' (ID ' . (int)$n['id_category'] . ')';
                $out[] = ['id' => (int)$n['id_category'], 'label' => $label];

                if (!empty($n['children'])) {
                    $walk($n['children'], $depth + 1);
                }
            }
        };
        $walk($tree, 0);

        return $out;
    }

    /** Pobiera z konfiguracji listę korzeni kategorii wykluczonych z wyboru produktów */
    private function getExcludedCategoryRootIds(): array
    {
        $raw = (string)Configuration::get('DFC_EXCLUDED_CATEGORY_ROOTS', '');

        if ($raw === '') {
            return []; // ❗ zamiast fallbacka
        }

        $ids = array_map('trim', explode(',', $raw));
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids, function ($id) {
            return $id > 0;
        });
        $ids = array_values(array_unique($ids));

        return $ids;
    }

    /** Zapisuje listę korzeni kategorii wykluczonych do konfiguracji modułu */
    private function saveExcludedCategoryRootIds(array $ids): void
    {
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids, function ($id) {
            return $id > 0;
        });
        $ids = array_values(array_unique($ids));

        Configuration::updateValue('DFC_EXCLUDED_CATEGORY_ROOTS', implode(',', $ids));
    }

    private function getProductOptions()
    {
        $idLang = (int)$this->context->language->id;
        $idShop = (int)$this->context->shop->id;

        // korzenie do wykluczenia z konfiguracji modułu
        $excludedRoots = $this->getExcludedCategoryRootIds();
        $excludedIds   = !empty($excludedRoots) ? $this->getCategoryTreeIds($excludedRoots) : [];

        $whereNotIn = '';
        if (!empty($excludedIds)) {
            $whereNotIn = ' AND p.id_category_default NOT IN ('.implode(',', array_map('intval', $excludedIds)).') ';
        }

        $sql = '
            SELECT p.id_product, pl.name
            FROM '._DB_PREFIX_.'product p
            INNER JOIN '._DB_PREFIX_.'product_shop ps
                ON ps.id_product = p.id_product AND ps.id_shop = '.$idShop.'
            INNER JOIN '._DB_PREFIX_.'product_lang pl
                ON pl.id_product = p.id_product
                AND pl.id_lang = '.$idLang.'
                AND pl.id_shop = '.$idShop.'
            WHERE ps.active = 1
            '.$whereNotIn.'
            ORDER BY pl.name ASC
        ';

        $rows = Db::getInstance()->executeS($sql) ?: [];
        $out  = [];

        $out[] = [
            'id'    => 0,
            'label' => '— brak —',
            'name'  => '',
            'image' => '',
        ];

        foreach ($rows as $r) {
            $id   = (int)$r['id_product'];
            $name = (string)$r['name'];

            $imageUrl = '';
            $cover = Product::getCover($id);

            if (!empty($cover['id_image'])) {
                $imageUrl = $this->context->link->getImageLink(
                    Tools::link_rewrite($name),
                    $id . '-' . (int)$cover['id_image'],
                    ImageType::getFormattedName('small')
                );
            }

            $out[] = [
                'id'    => $id,
                'label' => $name.' (ID '.$id.')',
                'name'  => $name,
                'image' => $imageUrl,
            ];
        }
      
        return $out;
    }

    protected function getCollectionProductsCount($idCategory)
    {
        $idCategory = (int)$idCategory;
        $idShop = (int)$this->context->shop->id;

        if ($idCategory <= 0) {
            return 0;
        }

        $sql = '
            SELECT COUNT(DISTINCT cp.id_product)
            FROM `'._DB_PREFIX_.'category_product` cp
            INNER JOIN `'._DB_PREFIX_.'product_shop` ps
                ON ps.id_product = cp.id_product
               AND ps.id_shop = '.(int)$idShop.'
               AND ps.active = 1
            WHERE cp.id_category = '.(int)$idCategory;

        return (int)Db::getInstance()->getValue($sql);
    }

    private function getCollectionLowestPriceData($idCategory)
    {
        $idCategory = (int)$idCategory;
        $idShop = (int)$this->context->shop->id;

        if ($idCategory <= 0) {
            return [
                'id_product' => 0,
                'name' => '',
                'url' => '',
                'price' => '',
                'price_amount' => 0.0,
                'regular_price' => '',
                'regular_price_amount' => 0.0,
                'has_discount' => 0,
                'discount_label' => '',
            ];
        }

        $rows = Db::getInstance()->executeS('
            SELECT DISTINCT cp.id_product
            FROM `'._DB_PREFIX_.'category_product` cp
            INNER JOIN `'._DB_PREFIX_.'product_shop` ps
                ON ps.id_product = cp.id_product
               AND ps.id_shop = '.(int)$idShop.'
               AND ps.active = 1
            WHERE cp.id_category = '.(int)$idCategory.'
            ORDER BY cp.position ASC
        ');

        if (empty($rows)) {
            return [
                'id_product' => 0,
                'name' => '',
                'url' => '',
                'price' => '',
                'price_amount' => 0.0,
                'regular_price' => '',
                'regular_price_amount' => 0.0,
                'has_discount' => 0,
                'discount_label' => '',
            ];
        }

        $best = null;

        foreach ($rows as $row) {
            $idProduct = (int)$row['id_product'];
            if ($idProduct <= 0) {
                continue;
            }

            $currentPrice = (float)Product::getPriceStatic(
                $idProduct,
                true,
                null,
                6,
                null,
                false,
                true
            );

            $regularPrice = (float)Product::getPriceStatic(
                $idProduct,
                true,
                null,
                6,
                null,
                false,
                false
            );

            if ($currentPrice <= 0) {
                continue;
            }

            if ($best === null || $currentPrice < $best['price_amount']) {
                $product = $this->presentOneById($idProduct);

                $productName = '';
                $productUrl = '';

                if ($product) {
                    $productName = !empty($product['name']) ? (string)$product['name'] : '';
                    $productUrl = !empty($product['url']) ? (string)$product['url'] : '';
                }

                if ($productUrl === '') {
                    $productUrl = $this->context->link->getProductLink($idProduct);
                }

                $hasDiscount = ($regularPrice > $currentPrice && $currentPrice > 0) ? 1 : 0;
                $discountLabel = '';

                if ($hasDiscount) {
                    $percent = (int)round((($regularPrice - $currentPrice) / $regularPrice) * 100);
                    if ($percent > 0) {
                        $discountLabel = '-'.$percent.'%';
                    }
                }

                $best = [
                    'id_product' => $idProduct,
                    'name' => $productName,
                    'url' => $productUrl,
                    'price' => Tools::displayPrice($currentPrice, $this->context->currency),
                    'price_amount' => $currentPrice,
                    'regular_price' => $hasDiscount ? Tools::displayPrice($regularPrice, $this->context->currency) : '',
                    'regular_price_amount' => $hasDiscount ? $regularPrice : 0.0,
                    'has_discount' => $hasDiscount,
                    'discount_label' => $discountLabel,
                ];
            }
        }

        if ($best === null) {
            return [
                'id_product' => 0,
                'name' => '',
                'url' => '',
                'price' => '',
                'price_amount' => 0.0,
                'regular_price' => '',
                'regular_price_amount' => 0.0,
                'has_discount' => 0,
                'discount_label' => '',
            ];
        }

        return $best;
    }

    private function getCollectionFreeShippingFromData($idCategory)
    {
        $idCategory = (int)$idCategory;

        $result = [
            'amount' => 0.0,
            'formatted' => '',
        ];

        if ($idCategory <= 0) {
            return $result;
        }

        if (!Module::isInstalled('freelivery') || !Module::isEnabled('freelivery')) {
            return $result;
        }

        $defaultCountryId = (int)Configuration::get('PS_COUNTRY_DEFAULT');
        $defaultZoneId = (int)Country::getIdZone($defaultCountryId);

        $groups = [];
        if (!empty($this->context->customer) && (int)$this->context->customer->id > 0) {
            $groups = Customer::getGroupsStatic((int)$this->context->customer->id);
            if (!empty($groups)) {
                $groups = array_map('intval', $groups);
            }
        }

        if (empty($groups)) {
            $groups = [(int)Configuration::get('PS_UNIDENTIFIED_GROUP')];
        }

        $rules = Db::getInstance()->executeS('
            SELECT f.*
            FROM `'._DB_PREFIX_.'freelivery` f
            WHERE (f.`id_zone` = 0 OR f.`id_zone` = '.(int)$defaultZoneId.')
              AND (
                    (f.`date_start` IS NULL AND f.`date_end` IS NULL)
                 OR (f.`date_start` IS NULL AND f.`date_end` > NOW())
                 OR (f.`date_end` IS NULL AND f.`date_start` <= NOW())
                 OR (NOW() BETWEEN f.`date_start` AND f.`date_end`)
              )
            ORDER BY
                CASE WHEN f.`min_price` > 0 THEN f.`min_price` ELSE 999999999 END ASC,
                f.`id` ASC
        ');

        if (empty($rules)) {
            return $result;
        }

        foreach ($rules as $rule) {
            $idCondition = (int)$rule['id'];

            $ruleGroups = Db::getInstance()->executeS('
                SELECT `id_group`
                FROM `'._DB_PREFIX_.'freelivery_groups`
                WHERE `id_condition` = '.(int)$idCondition
            );

            if (!empty($ruleGroups)) {
                $ruleGroupIds = array_map('intval', array_column($ruleGroups, 'id_group'));
                if (!array_intersect($groups, $ruleGroupIds)) {
                    continue;
                }
            }

            $excludedRows = Db::getInstance()->executeS('
                SELECT `id_category`
                FROM `'._DB_PREFIX_.'freelivery_excluded_categories`
                WHERE `id_condition` = '.(int)$idCondition
            );

            if (!empty($excludedRows)) {
                $excludedIds = array_map('intval', array_column($excludedRows, 'id_category'));
                if (in_array((int)$idCategory, $excludedIds, true)) {
                    continue;
                }
            }

            $amount = isset($rule['min_price']) ? (float)$rule['min_price'] : 0.0;

            $result['amount'] = $amount;
            $result['formatted'] = $amount > 0
                ? Tools::displayPrice($amount, $this->context->currency)
                : '';

            return $result;
        }

        return $result;
    }

    private function getDeliveryInfoForProduct($idProduct)
    {
        $idProduct = (int)$idProduct;

        if ($idProduct <= 0) {
            return [
                'delivery_text' => '',
                'shipping_cost_gross' => 0.0,
                'shipping_cost_gross_formatted' => '',
                'free_shipping_from' => 0.0,
                'free_shipping_from_formatted' => '',
            ];
        }

        $row = Db::getInstance()->getRow('
            SELECT
                `additional_delivery_times`,
                `delivery_information`,
                `delivery_in_stock`,
                `delivery_out_stock`,
                `shipping_cost_gross`,
                `free_shipping_from`,
                `listing_delivery_text_auto`
            FROM `'._DB_PREFIX_.'dfdeliveryinfo_product_source`
            WHERE `id_product` = '.(int)$idProduct
        );

        if (!$row) {
            return [
                'delivery_text' => '',
                'shipping_cost_gross' => 0.0,
                'shipping_cost_gross_formatted' => '',
                'free_shipping_from' => 0.0,
                'free_shipping_from_formatted' => '',
            ];
        }

        $deliveryText = '';
        $mode = isset($row['additional_delivery_times']) ? (int)$row['additional_delivery_times'] : 0;

        if ($mode === 1 && !empty($row['delivery_information'])) {
            $deliveryText = trim((string)$row['delivery_information']);
        } elseif ($mode === 2 && !empty($row['delivery_in_stock'])) {
            $deliveryText = trim((string)$row['delivery_in_stock']);
        } elseif (!empty($row['listing_delivery_text_auto'])) {
            $deliveryText = trim((string)$row['listing_delivery_text_auto']);
        }

        $shippingCostGross = isset($row['shipping_cost_gross']) ? (float)$row['shipping_cost_gross'] : 0.0;
        $freeShippingFrom = isset($row['free_shipping_from']) ? (float)$row['free_shipping_from'] : 0.0;

        return [
            'delivery_text' => $deliveryText,
            'shipping_cost_gross' => $shippingCostGross,
            'shipping_cost_gross_formatted' => $shippingCostGross > 0
                ? Tools::displayPrice($shippingCostGross, $this->context->currency)
                : '',
            'free_shipping_from' => $freeShippingFrom,
            'free_shipping_from_formatted' => $freeShippingFrom > 0
                ? Tools::displayPrice($freeShippingFrom, $this->context->currency)
                : '',
        ];
    }

    private function getBundleItems($idDfcollection, $onlyActive = false)
    {
        $idDfcollection = (int)$idDfcollection;
        $idLang = (int)$this->context->language->id;
        $idShop = (int)$this->context->shop->id;

        if ($idDfcollection <= 0) {
            return [];
        }

        $sql = new DbQuery();
        $sql->select('bi.*, pl.name AS product_name, i.id_image AS product_cover_id')
            ->from('dfcollection_bundle_item', 'bi')
            ->leftJoin(
                'product_lang',
                'pl',
                'pl.id_product = bi.id_product AND pl.id_lang = '.$idLang.' AND pl.id_shop = '.$idShop
            )
            ->leftJoin(
                'image_shop',
                'i',
                'i.id_product = bi.id_product AND i.cover = 1 AND i.id_shop = '.$idShop
            )
            ->where('bi.id_dfcollection = '.(int)$idDfcollection)
            ->orderBy('bi.position ASC, bi.id_dfcollection_bundle_item ASC');

        if ($onlyActive) {
            $sql->where('bi.active = 1');
        }

        $rows = Db::getInstance()->executeS($sql) ?: [];

        foreach ($rows as &$row) {
            $row['product_image'] = '';

            if (!empty($row['id_product']) && !empty($row['product_cover_id']) && !empty($row['product_name'])) {
                $row['product_image'] = $this->context->link->getImageLink(
                    Tools::link_rewrite((string)$row['product_name']),
                    (int)$row['id_product'].'-'.(int)$row['product_cover_id'],
                    ImageType::getFormattedName('small')
                );
            }
        }
        unset($row);

        return $rows;
    }

    private function deleteBundleItems($idDfcollection)
    {
        $idDfcollection = (int)$idDfcollection;

        if ($idDfcollection <= 0) {
            return;
        }

        Db::getInstance()->delete(
            'dfcollection_bundle_item',
            'id_dfcollection='.(int)$idDfcollection
        );
    }

    private function saveBundleItems($idDfcollection, array $bundleItems)
    {
        $idDfcollection = (int)$idDfcollection;

        if ($idDfcollection <= 0) {
            return;
        }

        $this->deleteBundleItems($idDfcollection);

        $position = 1;

        foreach ($bundleItems as $item) {
            $idProduct = isset($item['id_product']) ? (int)$item['id_product'] : 0;
            $customLabel = isset($item['custom_label']) ? trim((string)$item['custom_label']) : '';
            $active = !empty($item['active']) ? 1 : 0;

            if ($idProduct <= 0) {
                continue;
            }

            Db::getInstance()->insert('dfcollection_bundle_item', [
                'id_dfcollection' => $idDfcollection,
                'id_product' => $idProduct,
                'custom_label' => pSQL($customLabel),
                'position' => (int)$position,
                'active' => (int)$active,
            ]);

            $position++;
        }
    }

    private function duplicateBundleItems($sourceIdDfcollection, $targetIdDfcollection)
    {
        $sourceIdDfcollection = (int)$sourceIdDfcollection;
        $targetIdDfcollection = (int)$targetIdDfcollection;

        if ($sourceIdDfcollection <= 0 || $targetIdDfcollection <= 0) {
            return;
        }

        $items = $this->getBundleItems($sourceIdDfcollection, false);

        if (empty($items)) {
            return;
        }

        $position = 1;

        foreach ($items as $item) {
            $idProduct = isset($item['id_product']) ? (int)$item['id_product'] : 0;
            if ($idProduct <= 0) {
                continue;
            }

            Db::getInstance()->insert('dfcollection_bundle_item', [
                'id_dfcollection' => $targetIdDfcollection,
                'id_product'      => $idProduct,
                'custom_label'    => pSQL((string)$item['custom_label']),
                'position'        => (int)$position,
                'active'          => isset($item['active']) ? (int)$item['active'] : 1,
            ]);

            $position++;
        }
    }

    private function getBundleItemsFromRequest()
    {
        $bundleProducts = Tools::getValue('dfc_bundle_product', []);
        $bundleLabels = Tools::getValue('dfc_bundle_label', []);
        $bundleActive = Tools::getValue('dfc_bundle_active', []);

        if (!is_array($bundleProducts)) {
            $bundleProducts = [];
        }
        if (!is_array($bundleLabels)) {
            $bundleLabels = [];
        }
        if (!is_array($bundleActive)) {
            $bundleActive = [];
        }

        $items = [];

        foreach ($bundleProducts as $index => $productValue) {
            $idProduct = (int)$productValue;
            $customLabel = isset($bundleLabels[$index]) ? trim((string)$bundleLabels[$index]) : '';
            $active = isset($bundleActive[$index]) ? (int)$bundleActive[$index] : 0;

            if ($idProduct <= 0) {
                continue;
            }

            $items[] = [
                'id_product' => $idProduct,
                'custom_label' => $customLabel,
                'active' => $active ? 1 : 0,
            ];
        }

        return $items;
    }

    private function getBundleFrontItems($idDfcollection)
    {
        $rows = $this->getBundleItems((int)$idDfcollection, true);

        if (empty($rows)) {
            return [];
        }

        $items = [];

        foreach ($rows as $row) {
            $idProduct = (int)$row['id_product'];
            if ($idProduct <= 0) {
                continue;
            }

            $presented = $this->presentOneById($idProduct);
            if (!$presented) {
                continue;
            }

            $deliveryInfo = $this->getDeliveryInfoForProduct($idProduct);

            $productName = '';
            if (!empty($presented['name'])) {
                $productName = (string)$presented['name'];
            } elseif (!empty($row['product_name'])) {
                $productName = (string)$row['product_name'];
            }

            $productUrl = '';
            if (!empty($presented['url'])) {
                $productUrl = (string)$presented['url'];
            } else {
                $productUrl = $this->context->link->getProductLink($idProduct);
            }

            $productImage = '';
            if (!empty($row['product_image'])) {
                $productImage = (string)$row['product_image'];
            }

            $items[] = [
                'id_product'            => $idProduct,
                'name'                  => $productName,
                'image'                 => $productImage,
                'url'                   => $productUrl,
                'custom_label'          => isset($row['custom_label']) ? (string)$row['custom_label'] : '',
                'price'                 => !empty($presented['price']) ? $presented['price'] : '',
                'regular_price'         => !empty($presented['regular_price']) ? $presented['regular_price'] : '',
                'price_amount'          => isset($presented['price_amount']) ? (float)$presented['price_amount'] : 0,
                'regular_price_amount'  => isset($presented['regular_price_amount']) ? (float)$presented['regular_price_amount'] : 0,
                'has_discount'          => !empty($presented['has_discount']) ? 1 : 0,
                'discount_percentage'   => !empty($presented['discount_percentage']) ? $presented['discount_percentage'] : '',
                'discount'              => !empty($presented['discount']) ? $presented['discount'] : '',
                'delivery_text'                 => $deliveryInfo['delivery_text'],
                'shipping_cost_gross'           => $deliveryInfo['shipping_cost_gross'],
                'shipping_cost_gross_formatted' => $deliveryInfo['shipping_cost_gross_formatted'],
                'free_shipping_from'            => $deliveryInfo['free_shipping_from'],
                'free_shipping_from_formatted'  => $deliveryInfo['free_shipping_from_formatted'],
                'active'                => isset($row['active']) ? (int)$row['active'] : 1,
                'is_selected_default'     => 1,
            ];
        }

        return $items;
    }

    private function getBundleDeliveryText(array $bundleItems)
    {
        if (empty($bundleItems)) {
            return '';
        }

        $texts = [];

        foreach ($bundleItems as $item) {
            if (!empty($item['delivery_text'])) {
                $texts[] = trim((string)$item['delivery_text']);
            }
        }

        if (empty($texts)) {
            return '';
        }

        usort($texts, function ($a, $b) {
            return mb_strlen($b) - mb_strlen($a);
        });

        return (string)$texts[0];
    }

    private function getBundleTotalPrice(array $bundleItems)
    {
        if (empty($bundleItems)) {
            return '';
        }

        $total = 0.0;

        foreach ($bundleItems as $item) {
            if (empty($item['id_product'])) {
                continue;
            }

            $price = (float)Product::getPriceStatic(
                (int)$item['id_product'],
                true
            );

            $total += $price;
        }

        return Tools::displayPrice($total, $this->context->currency);
    }

    private function getBundleTotalRegularPrice(array $bundleItems)
    {
        if (empty($bundleItems)) {
            return '';
        }

        $totalRegular = 0.0;

        foreach ($bundleItems as $item) {
            if (empty($item['id_product'])) {
                continue;
            }

            $regular = 0.0;
            $current = 0.0;

            if (!empty($item['regular_price_amount'])) {
                $regular = (float)$item['regular_price_amount'];
            }

            if (!empty($item['price_amount'])) {
                $current = (float)$item['price_amount'];
            }

            if ($regular > $current && $current > 0) {
                $totalRegular += $regular;
            } else {
                $totalRegular += $current;
            }
        }

        if ($totalRegular <= 0) {
            return '';
        }

        return Tools::displayPrice($totalRegular, $this->context->currency);
    }

    private function getBundleTotalSavings(array $bundleItems)
    {
        if (empty($bundleItems)) {
            return '';
        }

        $totalSavings = 0.0;

        foreach ($bundleItems as $item) {
            if (empty($item['id_product'])) {
                continue;
            }

            $regular = 0.0;
            $current = 0.0;

            if (!empty($item['regular_price_amount'])) {
                $regular = (float)$item['regular_price_amount'];
            }

            if (!empty($item['price_amount'])) {
                $current = (float)$item['price_amount'];
            }

            if ($regular > $current && $current > 0) {
                $totalSavings += ($regular - $current);
            }
        }

        if ($totalSavings <= 0) {
            return '';
        }

        return Tools::displayPrice($totalSavings, $this->context->currency);
    }

    private function getBundleTotalPriceAmount(array $bundleItems)
    {
        if (empty($bundleItems)) {
            return 0.0;
        }

        $total = 0.0;

        foreach ($bundleItems as $item) {
            if (empty($item['id_product'])) {
                continue;
            }

            if (isset($item['price_amount'])) {
                $total += (float)$item['price_amount'];
            }
        }

        return (float)$total;
    }

    private function getBundleTotalRegularPriceAmount(array $bundleItems)
    {
        if (empty($bundleItems)) {
            return 0.0;
        }

        $totalRegular = 0.0;

        foreach ($bundleItems as $item) {
            if (empty($item['id_product'])) {
                continue;
            }

            $regular = !empty($item['regular_price_amount']) ? (float)$item['regular_price_amount'] : 0.0;
            $current = !empty($item['price_amount']) ? (float)$item['price_amount'] : 0.0;

            if ($regular > $current && $current > 0) {
                $totalRegular += $regular;
            } else {
                $totalRegular += $current;
            }
        }

        return (float)$totalRegular;
    }

    private function getBundleTotalSavingsAmount(array $bundleItems)
    {
        if (empty($bundleItems)) {
            return 0.0;
        }

        $totalSavings = 0.0;

        foreach ($bundleItems as $item) {
            if (empty($item['id_product'])) {
                continue;
            }

            $regular = !empty($item['regular_price_amount']) ? (float)$item['regular_price_amount'] : 0.0;
            $current = !empty($item['price_amount']) ? (float)$item['price_amount'] : 0.0;

            if ($regular > $current && $current > 0) {
                $totalSavings += ($regular - $current);
            }
        }

        return (float)$totalSavings;
    }

    private function getBundleShippingFromAmount(array $bundleItems)
    {
        if (empty($bundleItems)) {
            return 0.0;
        }

        $maxShipping = 0.0;

        foreach ($bundleItems as $item) {
            $shipping = isset($item['shipping_cost_gross']) ? (float)$item['shipping_cost_gross'] : 0.0;

            if ($shipping > $maxShipping) {
                $maxShipping = $shipping;
            }
        }

        return (float)$maxShipping;
    }

    private function formatBundleAmount($amount)
    {
        $amount = (float)$amount;

        if ($amount <= 0) {
            return '';
        }

        return Tools::displayPrice($amount, $this->context->currency);
    }

    /** Ustaw pozycje rosnąco 1..N wg bieżącego sortu */
    private function reindexPositions()
    {
        $db   = Db::getInstance();
        $list = $db->executeS('SELECT id_dfcollection FROM '._DB_PREFIX_.'dfcollection ORDER BY position ASC, id_dfcollection ASC') ?: [];
        $pos  = 1;

        $db->execute('START TRANSACTION');
        foreach ($list as $row) {
            $db->update('dfcollection', ['position' => (int)$pos], 'id_dfcollection='.(int)$row['id_dfcollection']);
            $pos++;
        }
        $db->execute('COMMIT');
    }

    /**
     * Panel konfiguracyjny:
     * - klik „Edytuj” używa &dfc_load=1&id_dfcollection=XX (wypełnia formularz)
     * - po Zapisz/Usuń robimy redirect na czysto (puste pola)
     */
    public function getContent()
    {
        // upewnij się, że kolumny istnieją (gdy moduł był zainstalowany wcześniej)
        $this->ensureImageCols();
        $this->ensureCompareImageCol();
        $this->ensureArrangementImageCol();
        $this->ensureCompareStartPercentCol();
        $this->ensureCompareLabelCol();
        $this->ensureUploadDir();
        $this->ensureSliderLimitCol();
        $this->ensureSliderInfiniteCol();
        $this->ensureSliderSortCol();
		$this->ensureShowFeaturedCountdownCol();
        $this->ensureShortDescriptionCol();
        $this->ensureCollectionScopeCol();
        $this->ensureBadgeCols();
        $this->ensureBundleTable();

        // Upewnij się, że hook BO jest podpięty — jeśli nie, podpinamy od razu
        if (!$this->isRegisteredInHook('displayBackOfficeHeader')) {
            $this->registerHook('displayBackOfficeHeader');
        }

        $db = Db::getInstance();
        $actionUrl = AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules');
        $dfc_heading = (string)Configuration::get('DFC_HEADING', null);
        $dfc_heading_link_category = (int)Configuration::get('DFC_HEADING_LINK_CATEGORY');
        $dfc_excluded_category_roots = $this->getExcludedCategoryRootIds();

        // 👉 RESET FORMULARZA
        if (Tools::isSubmit('dfc_reset')) {
            Tools::redirectAdmin($actionUrl); // czysta karta
            return '';
        }
        
        // 🔄 AJAX: sortowanie w tabeli (drag & drop)
        if (Tools::getIsset('dfc_sort')) {
            header('Content-Type: application/json; charset=utf-8');

            $ids = [];

            // nowy sposób: ids=1,2,3
            $csv = trim((string)Tools::getValue('ids', ''));
            if ($csv !== '') {
                foreach (explode(',', $csv) as $v) {
                    $v = (int)trim($v);
                    if ($v > 0) { $ids[] = $v; }
                }
            }

            // stary sposób: order[]=1&order[]=2...
            if (!$ids) {
                $order = Tools::getValue('order');
                if (is_array($order)) {
                    foreach ($order as $v) {
                        $v = (int)$v;
                        if ($v > 0) { $ids[] = $v; }
                    }
                }
            }

            // sanity
            $ids = array_values(array_unique($ids));
            if (!$ids) {
                die(json_encode(['ok' => 0, 'msg' => 'Empty order']));
            }

            $db = Db::getInstance();

            // ⚠️ Jeżeli masz UNIQUE INDEX na kolumnie `position`, zrób 2 przebiegi,
            // żeby nie wpaść w chwilowy konflikt wartości:

            $db->execute('START TRANSACTION');

            // 1) przestaw tymczasowo pozycje na duże wartości, np. 1000 + i
            $tmpPos = 1000;
            foreach ($ids as $id) {
                $db->update('dfcollection', ['position' => (int)$tmpPos], 'id_dfcollection='.(int)$id);
                $tmpPos++;
            }

            // 2) nadaj docelowo 1..N wg nowej kolejności
            $pos = 1;
            foreach ($ids as $id) {
                $db->update('dfcollection', ['position' => (int)$pos], 'id_dfcollection='.(int)$id);
                $pos++;
            }

            $db->execute('COMMIT');

            $this->dfcCacheBumpMtime();

            die(json_encode(['ok' => 1, 'msg' => $this->l('Kolejność została zapisana.')]));
        }

        // KOPIUJ
        if (Tools::isSubmit('dfc_duplicate')) {
            $idSource = (int)Tools::getValue('id_dfcollection');

            if ($idSource > 0) {
                $source = $db->getRow('
                    SELECT *
                    FROM `'._DB_PREFIX_.'dfcollection`
                    WHERE id_dfcollection = '.(int)$idSource
                );

                if ($source) {
                    $nextPos = (int)Db::getInstance()->getValue(
                        'SELECT IFNULL(MAX(position), 0) + 1 FROM '._DB_PREFIX_.'dfcollection'
                    );

                    $sourceTitle = trim((string)$source['title']);
                    $newTitle = $sourceTitle !== ''
                        ? $sourceTitle . ' (kopia)'
                        : 'Kopia';

                    $newRow = [
                        'position'               => $nextPos,
                        'active'                 => 0,
                        'id_category'            => (int)$source['id_category'],
                        'id_featured_product'    => (int)$source['id_featured_product'],
						'show_featured_countdown'  => isset($source['show_featured_countdown']) ? (int)$source['show_featured_countdown'] : 0,
                        'title'                  => pSQL($newTitle),
                        'image_url'              => pSQL($this->duplicateLocalImage((string)$source['image_url'])),
                        'image_url_mobile'       => pSQL($this->duplicateLocalImage((string)$source['image_url_mobile'])),
                        'image_url_xs'           => pSQL($this->duplicateLocalImage((string)$source['image_url_xs'])),
                        'image_compare_url'      => pSQL($this->duplicateLocalImage((string)$source['image_compare_url'])),
                        'arrangement_image_url'  => pSQL($this->duplicateLocalImage((string)$source['arrangement_image_url'])),
                        'compare_start_percent'  => isset($source['compare_start_percent']) ? (int)$source['compare_start_percent'] : 50,
                        'compare_label'          => pSQL((string)$source['compare_label']),
                        'slider_limit'           => isset($source['slider_limit']) ? (int)$source['slider_limit'] : 8,
                        'slider_infinite'        => isset($source['slider_infinite']) ? (int)$source['slider_infinite'] : 1,
                        'slider_sort'            => pSQL((string)$source['slider_sort']),
                        'short_description'      => pSQL((string)$source['short_description'], true),
                        'collection_scope'       => pSQL((string)$source['collection_scope']),
                        'badge_1'                => pSQL((string)$source['badge_1']),
                        'badge_2'                => pSQL((string)$source['badge_2']),
                        'badge_3'                => pSQL((string)$source['badge_3']),
                        'badge_4'                => pSQL((string)$source['badge_4']),
                    ];

                    Db::getInstance()->insert('dfcollection', $newRow);

                    $newId = (int)Db::getInstance()->Insert_ID();

                    if ($newId > 0) {
                        $this->duplicateBundleItems($idSource, $newId);
                        $this->dfcCacheBumpMtime();
                        Tools::redirectAdmin($actionUrl.'&conf=4');
                        return '';
                    }
                }

                $this->context->controller->errors[] = $this->l('Nie udało się skopiować kolekcji.');
            } else {
                $this->context->controller->errors[] = $this->l('Brak poprawnego ID kolekcji do skopiowania.');
            }
        }

        // ZAPISZ
        if (Tools::isSubmit('dfc_save')) {

            $heading = Tools::getValue('dfc_heading', '');
            $heading = trim((string)$heading);
            if ($heading === '') { $heading = 'KOLEKCJE'; }
            Configuration::updateValue('DFC_HEADING', $heading);

            $headingLinkCategory = (int)Tools::getValue('dfc_heading_link_category', 0);
            Configuration::updateValue('DFC_HEADING_LINK_CATEGORY', $headingLinkCategory);

            $excludedCategoryRoots = Tools::getValue('dfc_excluded_category_roots', []);
            if (!is_array($excludedCategoryRoots)) {
                $excludedCategoryRoots = [];
            }
            $this->saveExcludedCategoryRootIds($excludedCategoryRoots);

            $id  = (int)Tools::getValue('id_dfcollection', 0);
            
            // przed zbudowaniem $base:
            $sliderLimit = (int)Tools::getValue('slider_limit', 8);
            if ($sliderLimit < 1)  { $sliderLimit = 1; }
            if ($sliderLimit > 20) { $sliderLimit = 20; }

            $sliderInfinite = (int)Tools::getValue('slider_infinite', 1);
            $sliderInfinite = $sliderInfinite ? 1 : 0;
            $allowedSliderSorts = ['position', 'random', 'newest', 'price_asc', 'price_desc', 'bestseller'];
            $sliderSort = (string)Tools::getValue('slider_sort', 'random');
            if (!in_array($sliderSort, $allowedSliderSorts, true)) {
                $sliderSort = 'position';
            }

            $compareStartPercent = (int)Tools::getValue('compare_start_percent', 50);
            if ($compareStartPercent < 0) {
                $compareStartPercent = 0;
            }
            if ($compareStartPercent > 100) {
                $compareStartPercent = 100;
            }

            $compareLabel = trim((string)Tools::getValue('compare_label', ''));
            $collectionScope = trim((string)Tools::getValue('collection_scope', ''));
            $badge1 = trim((string)Tools::getValue('badge_1', ''));
            $badge2 = trim((string)Tools::getValue('badge_2', ''));
            $badge3 = trim((string)Tools::getValue('badge_3', ''));
            $badge4 = trim((string)Tools::getValue('badge_4', ''));

			$idFeaturedProduct = (int)Tools::getValue('id_featured_product', 0);
            $showFeaturedCountdown = (int)Tools::getValue('show_featured_countdown', 0);

            if ($idFeaturedProduct <= 0) {
                $showFeaturedCountdown = 0;
            }

            // Pola zawsze aktualizowane (bez 'position')
            $base = [
                'active'              => (int)Tools::getValue('active', 1),
                'id_category'         => (int)Tools::getValue('id_category', 0),
                'id_featured_product'     => $idFeaturedProduct,
                'show_featured_countdown' => $showFeaturedCountdown,
                'title'               => pSQL((string)Tools::getValue('title', '')),
                'short_description'   => pSQL((string)Tools::getValue('short_description', ''), true),
                'collection_scope'    => pSQL($collectionScope),
                'badge_1'               => pSQL($badge1),
                'badge_2'               => pSQL($badge2),
                'badge_3'               => pSQL($badge3),
                'badge_4'               => pSQL($badge4),
                'compare_start_percent' => $compareStartPercent,
                'compare_label'         => pSQL($compareLabel),
                'slider_limit'        => $sliderLimit,
                'slider_infinite'     => $sliderInfinite,
                'slider_sort'         => pSQL($sliderSort),
            ];

            if ($id > 0) {
                // UPDATE: nie dotykamy position
                Db::getInstance()->update('dfcollection', $base, 'id_dfcollection='.(int)$id);
                
                // ⬇️ POBIERZ aktualne URL-e, żeby móc usunąć stare pliki
                $current = $db->getRow('
                    SELECT image_url, image_url_mobile, image_url_xs, image_compare_url, arrangement_image_url
                    FROM '._DB_PREFIX_.'dfcollection
                    WHERE id_dfcollection='.(int)$id
                );
                if (!is_array($current)) {
                    $current = [
                        'image_url' => '',
                        'image_url_mobile' => '',
                        'image_url_xs' => '',
                        'image_compare_url' => '',
                        'arrangement_image_url' => '',
                    ];
                }

                // Kolumny obrazów — każdy warunkowo i osobno (kasowanie / podmiana / upload)
                $imgCols = [
                    'image_url'        => 'delete_image_url',
                    'image_url_mobile' => 'delete_image_url_mobile',
                    'image_url_xs'     => 'delete_image_url_xs',
                    'image_compare_url' => 'delete_image_compare_url',
                    'arrangement_image_url' => 'delete_arrangement_image_url',
                ];

                // mapowanie kolumna -> nazwa pola pliku z formularza
                $fieldMap = [
                    'image_url'        => 'image_file',
                    'image_url_mobile' => 'image_mobile_file',
                    'image_url_xs'     => 'image_xs_file',
                    'image_compare_url' => 'image_compare_file',
                    'arrangement_image_url' => 'arrangement_image_file',
                ];

                foreach ($imgCols as $col => $delKey) {
                    $toDelete = (bool)Tools::getValue($delKey);

                    // 0️⃣ Upload pliku – spróbuj nowej i starej nazwy pola
                    $uploaded = '';
                    $candidates = [];
                    // nowa nazwa pola z mapy
                    if (!empty($fieldMap[$col])) { $candidates[] = $fieldMap[$col]; }
                    // stara nazwa pola = taka sama jak kolumna (np. image_url)
                    $candidates[] = $col;

                    foreach ($candidates as $fname) {
                        if (!empty($_FILES[$fname]) && !empty($_FILES[$fname]['tmp_name'])) {
                            $uploaded = $this->handleImageUpload($fname); // zapisze do /img/
                            break;
                        }
                    }

                    if ($uploaded !== '') {
                        // usuń stary plik (jeśli nasz) i podmień URL
                        $this->tryUnlinkIfLocal($current[$col] ?? '');
                        Db::getInstance()->update('dfcollection', [$col => pSQL($uploaded)], 'id_dfcollection='.(int)$id);
                        continue;
                    }


                    // 1️⃣ Usuwanie
                    if ($toDelete) {
                        // ⬇️ usuń fizyczny plik, jeśli był nasz
                        $this->tryUnlinkIfLocal($current[$col] ?? '');
                        Db::getInstance()->update('dfcollection', [$col => ''], 'id_dfcollection='.(int)$id);
                        continue;
                    }
                }

                $bundleItems = $this->getBundleItemsFromRequest();
                $this->saveBundleItems((int)$id, $bundleItems);

                $this->dfcCacheBumpMtime();
                Tools::redirectAdmin($actionUrl.'&conf=4');
                return '';
            } else {
                // INSERT: nadaj automatycznie ostatnią pozycję (MAX(position)+1)
                $nextPos = (int)Db::getInstance()->getValue(
                    'SELECT IFNULL(MAX(position),0)+1 FROM '._DB_PREFIX_.'dfcollection'
                );

                // Wczytaj uploady TYLKO dla nowego wpisu
                $newDesktop = $this->handleImageUpload('image_file');
                $newMobile  = $this->handleImageUpload('image_mobile_file');
                $newXS      = $this->handleImageUpload('image_xs_file');
                $newCompare = $this->handleImageUpload('image_compare_file');
                $newArrangement = $this->handleImageUpload('arrangement_image_file');

                $row = array_merge($base, [
                    'position'         => $nextPos,
                    'image_url'        => pSQL($newDesktop !== '' ? $newDesktop : ''),
                    'image_url_mobile' => pSQL($newMobile  !== '' ? $newMobile  : ''),
                    'image_url_xs'     => pSQL($newXS      !== '' ? $newXS      : ''),
                    'image_compare_url' => pSQL($newCompare !== '' ? $newCompare : ''),
                    'arrangement_image_url' => pSQL($newArrangement !== '' ? $newArrangement : ''),               
                ]);

                Db::getInstance()->insert('dfcollection', $row);

                $newIdDfcollection = (int)Db::getInstance()->Insert_ID();
                $bundleItems = $this->getBundleItemsFromRequest();
                $this->saveBundleItems($newIdDfcollection, $bundleItems);

                $this->dfcCacheBumpMtime();
              
                Tools::redirectAdmin($actionUrl.'&conf=4'); // 4 = zapisano / ustawienia zaktualizowane
                return '';
            }
        }

        // Domyślne puste wartości formularza (bez 'position')
        $edit = [
            'id_dfcollection'      => 0,
            'active'               => 1,
            'id_category'          => '',
            'id_featured_product'  => '',
			'show_featured_countdown' => 0,
            'title'                => '',
            'short_description'    => '',
            'collection_scope'     => '',
            'dfc_bundle_items'     => [],
            'badge_1'              => '',
            'badge_2'              => '',
            'badge_3'              => '',
            'badge_4'              => '',
            'image_url'            => '',
            'image_url_mobile'     => '',
            'image_url_xs'         => '',
            'image_compare_url'     => '',
            'arrangement_image_url' => '',
            'compare_start_percent' => 50,
            'compare_label'         => '',
            'slider_limit'         => 8,
            'slider_infinite'      => 1,
            'slider_sort'          => 'random',
        ];

        // ŁADOWANIE DO EDYCJI
        if (Tools::getIsset('dfc_load') && ($id = (int)Tools::getValue('id_dfcollection'))) {
            $row = $db->getRow('SELECT * FROM '._DB_PREFIX_.'dfcollection WHERE id_dfcollection='.(int)$id);
            if ($row) {
                $edit = [
                    'id_dfcollection'      => (int)$row['id_dfcollection'],
                    'active'               => (int)$row['active'],
                    'id_category'          => (int)$row['id_category'],
                    'id_featured_product'  => (int)$row['id_featured_product'],
					'show_featured_countdown' => isset($row['show_featured_countdown']) ? (int)$row['show_featured_countdown'] : 0,
                    'title'                => (string)$row['title'],
                    'short_description'   => (string)($row['short_description'] ?? ''),
                    'collection_scope'     => (string)($row['collection_scope'] ?? ''),
                    'dfc_bundle_items'     => $this->getBundleItems((int)$row['id_dfcollection'], false),
                    'badge_1'              => (string)($row['badge_1'] ?? ''),
                    'badge_2'              => (string)($row['badge_2'] ?? ''),
                    'badge_3'              => (string)($row['badge_3'] ?? ''),
                    'badge_4'              => (string)($row['badge_4'] ?? ''),
                    'image_url'            => (string)$row['image_url'],
                    'image_url_mobile'     => (string)($row['image_url_mobile'] ?? ''),
                    'image_url_xs'         => (string)($row['image_url_xs'] ?? ''),
                    'image_compare_url'     => (string)($row['image_compare_url'] ?? ''),
                    'arrangement_image_url'   => (string)($row['arrangement_image_url'] ?? ''),
                    'compare_start_percent' => isset($row['compare_start_percent']) ? (int)$row['compare_start_percent'] : 50,
                    'compare_label'         => (string)($row['compare_label'] ?? ''),
                    'slider_limit'         => isset($row['slider_limit']) ? (int)$row['slider_limit'] : 8,
                    'slider_infinite'      => isset($row['slider_infinite']) ? (int)$row['slider_infinite'] : 1,
                    'slider_sort'          => isset($row['slider_sort']) ? (string)$row['slider_sort'] : 'random',
                ];
            }
        }

        // USUŃ
        if (Tools::isSubmit('dfc_delete')) {
            $idDel = (int)Tools::getValue('id_dfcollection');
            if ($idDel > 0) {
                // 📌 Pobierz aktualne ścieżki obrazów dla rekordu
                $toClean = $db->getRow('
                    SELECT image_url, image_url_mobile, image_url_xs, image_compare_url, arrangement_image_url
                    FROM '._DB_PREFIX_.'dfcollection
                    WHERE id_dfcollection='.(int)$idDel
                );

                // 📌 Usuń pliki lokalne, jeśli istnieją
                if ($toClean) {
                    $this->tryUnlinkIfLocal($toClean['image_url'] ?? '');
                    $this->tryUnlinkIfLocal($toClean['image_url_mobile'] ?? '');
                    $this->tryUnlinkIfLocal($toClean['image_url_xs'] ?? '');
                    $this->tryUnlinkIfLocal($toClean['image_compare_url'] ?? '');
                    $this->tryUnlinkIfLocal($toClean['arrangement_image_url'] ?? '');
                }
                $this->deleteBundleItems((int)$idDel);
                $ok = Db::getInstance()->delete('dfcollection', 'id_dfcollection='.(int)$idDel);
                if ($ok) {
                   $this->reindexPositions();
                   $this->dfcCacheBumpMtime();
                   Tools::redirectAdmin($actionUrl.'&conf=1'); // zielony komunikat Prestashop
                } else {
                    $this->context->controller->errors[] = $this->l('Nie udało się usunąć rekordu.');
                }
            } else {
                $this->context->controller->errors[] = $this->l('Brak poprawnego ID rekordu do usunięcia.');
            }
        }
        
        // Rozmiary plików dla widoku edycji
        $edit['image_url_size']        = $this->probeImageSize($edit['image_url']);
        $edit['image_url_mobile_size'] = $this->probeImageSize($edit['image_url_mobile']);
        $edit['image_url_xs_size']     = $this->probeImageSize($edit['image_url_xs']);
        $edit['image_compare_url_size'] = $this->probeImageSize($edit['image_compare_url']);
        $edit['arrangement_image_url_size'] = $this->probeImageSize($edit['arrangement_image_url']);

        // Wymiary plików dla widoku edycji (NOWE)
        $edit['image_url_dims']        = $this->probeImageDims($edit['image_url']);
        $edit['image_url_mobile_dims'] = $this->probeImageDims($edit['image_url_mobile']);
        $edit['image_url_xs_dims']     = $this->probeImageDims($edit['image_url_xs']);
        $edit['image_compare_url_dims'] = $this->probeImageDims($edit['image_compare_url']);
        $edit['arrangement_image_url_dims'] = $this->probeImageDims($edit['arrangement_image_url']);

        // Lista rekordów + action
        $collections = $this->getCollections(false);
        foreach ($collections as &$r) {
            $r['image_url_size']        = $this->probeImageSize($r['image_url']);
            $r['image_url_mobile_size'] = $this->probeImageSize($r['image_url_mobile']);
            $r['image_url_xs_size']     = $this->probeImageSize($r['image_url_xs']);
            $r['image_compare_url_size'] = $this->probeImageSize($r['image_compare_url']);
            $r['arrangement_image_url_size'] = $this->probeImageSize($r['arrangement_image_url']);

            $r['image_url_dims']        = $this->probeImageDims($r['image_url']);
            $r['image_url_mobile_dims'] = $this->probeImageDims($r['image_url_mobile']);
            $r['image_url_xs_dims']     = $this->probeImageDims($r['image_url_xs']);
            $r['image_compare_url_dims'] = $this->probeImageDims($r['image_compare_url']);
            $r['arrangement_image_url_dims'] = $this->probeImageDims($r['arrangement_image_url']);
            
        }
        unset($r);
        $categoryOptions = $this->getCategoryOptions();
        $productOptions  = $this->getProductOptions();
        $this->context->smarty->assign([
            'dfc_rows'   => $collections,
            'dfc_edit'   => $edit,
            'dfc_action' => $actionUrl,
            'dfc_categories' => $categoryOptions,
            'dfc_products'    => $productOptions,
            'dfc_heading' => $dfc_heading,
            'dfc_heading_link_category'   => $dfc_heading_link_category,
            'dfc_excluded_category_roots' => $dfc_excluded_category_roots,                           
        ]);

        return $this->fetch('module:'.$this->name.'/views/templates/admin/config.tpl');
    }

    // ================================
    // DF COLLECTION CACHE (HTML output)
    // - cache pod /var/cache/prod/dfcollection/
    // - auto-inwalidacja po zmianach w module
    // - osobne klucze per shop/lang/currency/groups
    // ================================

    private function dfcCacheEnabled()
    {
        return true;
    }

    private function dfcGetCacheDir()
    {
        $base = _PS_CACHE_DIR_ . 'dfcollection' . DIRECTORY_SEPARATOR;

        if (!is_dir($base)) {
            @mkdir($base, 0755, true);
        }

        return $base;
    }

    private function dfcGetContextCacheKey($suffix = '')
    {
        $idShop = (int)$this->context->shop->id;
        $idLang = (int)$this->context->language->id;
        $idCurrency = (int)$this->context->currency->id;

        $groups = [];
        if (Group::isFeatureActive()) {
            $groups = (array)FrontController::getCurrentCustomerGroups();
            sort($groups);
        }
        $groupsKey = $groups ? implode('-', $groups) : 'nogroup';

        $mtime = (int)Configuration::get('DFC_CACHE_MTIME', 0);

        $raw = implode('|', [
            'dfcollection_home',
            $idShop,
            $idLang,
            $idCurrency,
            $groupsKey,
            $mtime,
            _PS_VERSION_,
            $suffix
        ]);

        return sha1($raw);
    }

    private function dfcCacheRead($key)
    {
        $file = $this->dfcGetCacheDir() . $key . '.html';

        if (!is_file($file)) {
            return false;
        }

        $content = @file_get_contents($file);

        return ($content !== false ? $content : false);
    }

    private function dfcCacheWrite($key, $html)
    {
        $file = $this->dfcGetCacheDir() . $key . '.html';
        @file_put_contents($file, $html, LOCK_EX);
    }

    private function dfcCacheClearAllFiles()
    {
        $dir = $this->dfcGetCacheDir();

        foreach (glob($dir . '*.html') ?: [] as $f) {
            @unlink($f);
        }
    }

    private function dfcCacheBumpMtime()
    {
        Configuration::updateValue('DFC_CACHE_MTIME', time());
        $this->dfcCacheClearAllFiles();
    }

    /* ======================= DODANE: obsługa uploadu plików ======================= */

    /** Ścieżka FS do katalogu z obrazami modułu */
    private function uploadDirFs(): string {
        return _PS_MODULE_DIR_.$this->name.'/img/';
    }
    /** URL do katalogu z obrazami modułu */
    private function uploadDirUrl(): string {
        return _MODULE_DIR_.$this->name.'/img/';
    }
    /** Zapewnij istnienie katalogu upload + prostą ochronę */
    private function ensureUploadDir(): bool {
        $dir = $this->uploadDirFs();
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        if (!is_file($dir.'index.php')) {
            @file_put_contents($dir.'index.php', "<?php\nhttp_response_code(404);");
        }
        return true;
    }
    /**
     * Obsługa uploadu obrazu (jpg/png) bez rekompresji.
     * Zwraca kompletny URL albo pusty string, jeśli nic nie wgrano lub plik jest niepoprawny.
     */
    private function handleImageUpload(string $field): string {
        if (empty($_FILES[$field]) || !isset($_FILES[$field]['tmp_name']) || !is_uploaded_file($_FILES[$field]['tmp_name'])) {
            return '';
        }
        $tmp  = $_FILES[$field]['tmp_name'];
        $name = $_FILES[$field]['name'];
        $size = (int)$_FILES[$field]['size'];

        $maxBytes = 20 * 1024 * 1024; // 20 MB
        if ($size <= 0 || $size > $maxBytes) {
            $this->context->controller->errors[] = $this->l('Plik jest pusty albo za duży (max 20 MB).');
            return '';
        }

        // sprawdź mime
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $tmp);
        finfo_close($finfo);

        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
        ];
        if (!isset($allowed[$mime])) {
            $this->context->controller->errors[] = $this->l('Niedozwolony typ pliku. Dozwolone: JPG, PNG.');
            return '';
        }

        $ext = $allowed[$mime];
        $base = pathinfo($name, PATHINFO_FILENAME);
        $slug = Tools::str2url($base);
        $file = date('Ymd-His').'-'.substr(sha1($name.microtime(true)),0,8).($slug ? '-'.$slug : '').'.'.$ext;

        $this->ensureUploadDir();
        $dest = $this->uploadDirFs().$file;
        if (!@move_uploaded_file($tmp, $dest)) {
            $this->context->controller->errors[] = $this->l('Nie udało się zapisać pliku na serwerze.');
            return '';
        }
        @chmod($dest, 0644);

        return $this->uploadDirUrl().$file;
    }

    private function duplicateLocalImage(string $url): string
    {
        $url = trim((string)$url);
        if ($url === '') {
            return '';
        }

        $prefix = $this->uploadDirUrl(); // /modules/dfcollection/img/

        // jeśli to nie jest lokalny plik naszego modułu, zostaw jak jest
        if (strpos($url, $prefix) !== 0) {
            return $url;
        }

        $rel = substr($url, strlen($prefix));
        $src = $this->uploadDirFs() . $rel;

        if (!is_file($src)) {
            return '';
        }

        $ext = pathinfo($src, PATHINFO_EXTENSION);
        $base = pathinfo($src, PATHINFO_FILENAME);

        $newFile = date('Ymd-His') . '-' . substr(sha1($base . microtime(true)), 0, 10) . '-copy.' . $ext;
        $dst = $this->uploadDirFs() . $newFile;

        $this->ensureUploadDir();

        if (!@copy($src, $dst)) {
            return '';
        }

        @chmod($dst, 0644);

        return $this->uploadDirUrl() . $newFile;
    }
  
    /** Usuń fizyczny plik, jeżeli wskazuje na nasz katalog modułu (uploads/ lub stare img/) */
    private function tryUnlinkIfLocal(string $url): void {
        $url = (string)$url;
        if ($url === '') return;

        $prefix = $this->uploadDirUrl();   // .../modules/dfcollection/img/
        if (strpos($url, $prefix) === 0) {
            $rel = substr($url, strlen($prefix));
            $fs  = $this->uploadDirFs().$rel; // .../modules/dfcollection/img/filename
            if (is_file($fs)) { @unlink($fs); }
        }
    }
}