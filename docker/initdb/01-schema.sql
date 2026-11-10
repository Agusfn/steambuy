-- =============================================================================
-- SteamBuy demo schema (MySQL 5.6, InnoDB, utf8)
--
-- REVERSE-ENGINEERED FROM THE PHP CODE (public_html/ and lib/) on 2026-11-10.
-- The original schema/dump was never part of the repository, so this is an
-- APPROXIMATION: table and column names come from SQL statements and
-- $row['col'] accesses; types, lengths and indexes are inferred from usage.
--
-- Conventions:
--   * Boolean-like flags are TINYINT(1) NOT NULL DEFAULT 0.
--   * Money is DECIMAL(10,2).
--   * Zero datetimes ('0000-00-00 00:00:00') are written explicitly by the code
--     as "not set", so DATETIME columns default to that value. This relies on
--     the MySQL 5.6 default (non-strict) sql_mode; it will fail on 5.7+ strict.
--   * Legacy INSERTs omit columns, so every column has a default or is NULL.
-- =============================================================================

SET NAMES utf8;

-- -----------------------------------------------------------------------------
-- settings: key/value store (dollar quote, service switch, region quotes, ...)
-- Used by: admin/index.php, global_scripts/php/purchase-functions.php,
--   update_quote_tags.php, update_order_status.php, juegos/index.php.
-- The code only UPDATEs these rows, so they must exist (see 02-seed.sql).
-- Known names: autoupdate_dollar_value, fixed_dollar_value, updated_dollar_value,
--   dollar_retrieve_attemps, service_enabled, brl_quote, mxn_quote,
--   alicuota_menor32, alicuota_mayor32, frecuent_tags, payments_last_revised,
--   today_payments, today_payments_names, today_sitepayments, today_totalpayments.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `name`  VARCHAR(64) NOT NULL,
  `value` TEXT,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- -----------------------------------------------------------------------------
-- products: game catalog.
-- Used by: index.php, juegos/index.php, juegos/juego.php, resources/php/
--   catalog_functions.php, ajax-search-autocomplete.php, comprar/*,
--   purchase-functions.php, admin/products/*, update_product_prices.php,
--   lib/mass-discounts.php.
--   product_platform:       1=steam, 2=origin
--   product_sellingsite:    1=steam, 2=amazon, 3=humblebundle, 4=bundlestars, 5=origin
--   product_customprice_currency: 'usd' | 'ars'
--   product_pics:           screenshot file names separated by ';'
--   product_tags:           comma-separated, lowercase
--   product_external_offer_endtime: '0000-00-00 00:00:00' = no end time
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `product_id`                     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_enabled`                TINYINT(1) NOT NULL DEFAULT 0,
  `product_update_error`           TINYINT(1) NOT NULL DEFAULT 0,
  `product_name`                   VARCHAR(255) NOT NULL DEFAULT '',
  `product_rating`                 INT NOT NULL DEFAULT 0,
  `product_platform`               TINYINT NOT NULL DEFAULT 1,
  `product_sellingsite`            TINYINT NOT NULL DEFAULT 0,
  `product_site_url`               VARCHAR(255) NOT NULL DEFAULT '',
  `product_has_limited_units`      TINYINT(1) NOT NULL DEFAULT 0,
  `product_limited_units`          INT NOT NULL DEFAULT 0,
  `product_has_customprice`        TINYINT(1) NOT NULL DEFAULT 0,
  `product_customprice_currency`   VARCHAR(3) NOT NULL DEFAULT 'usd',
  `product_external_limited_offer` TINYINT(1) NOT NULL DEFAULT 0,
  `product_external_offer_endtime` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `product_listprice`              DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `product_steam_discount_price`   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `product_finalprice`             DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `product_mainpicture`            VARCHAR(255) NOT NULL DEFAULT '',
  `product_pics`                   TEXT,
  `product_description`            TEXT,
  `product_tags`                   TEXT,
  `product_singleplayer`           TINYINT(1) NOT NULL DEFAULT 0,
  `product_multiplayer`            TINYINT(1) NOT NULL DEFAULT 0,
  `product_cooperative`            TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`product_id`),
  KEY `idx_enabled_rating` (`product_enabled`, `product_rating`),
  KEY `idx_sellingsite` (`product_sellingsite`, `product_enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- -----------------------------------------------------------------------------
-- products_giftcards: gift cards (no INSERT found in code; managed by hand).
-- Used by: index.php, giftcards/index.php, comprar/pago.php, comprar/datos.php,
--   purchase-functions.php, admin/products/giftcards.php.
--   type: 1=Steam Wallet, 2=PlayStation Store, 3=Xbox
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products_giftcards` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type`              TINYINT NOT NULL DEFAULT 1,
  `name`              VARCHAR(100) NOT NULL DEFAULT '',
  `usd_ammount`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `selling_price_usd` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `stock`             INT NOT NULL DEFAULT 0,
  `relevance`         INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_type_stock` (`type`, `stock`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- -----------------------------------------------------------------------------
-- orders: purchase orders.
-- Used by: comprar/*, pedido/detalles.php, cancelar/index.php, informar/index.php,
--   factura/ver_factura.php, purchase-functions.php, process_payments.php,
--   update_order_status.php, scripts/php/ajax_order_validation.php,
--   admin/index.php, admin/pedidos.php, admin/pedido.php, admin/process.php.
-- order_id is "J" + order_number (computed from SHOW TABLE STATUS Auto_increment
-- before insert), e.g. 'J10001'.
--   order_status:        1=pending, 2=sent, 3=cancelled
--   order_paymentmethod: 1=ticket (CuentaDigital), 2=bank transfer
--   order_send_method:   0=email, 1=steam gift to friend
--   product_type:        1=game, 2=gift card
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `orders` (
  `order_number`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_send_method`       TINYINT NOT NULL DEFAULT 0,
  `order_id`                VARCHAR(16) NOT NULL DEFAULT '',
  `order_password`          VARCHAR(32) NOT NULL DEFAULT '',
  `order_date`              DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `order_status`            TINYINT NOT NULL DEFAULT 1,
  `order_status_change`     DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `order_confirmed_payment` TINYINT(1) NOT NULL DEFAULT 0,
  `order_payment_time`      DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `order_purchaseticket`    VARCHAR(255) NOT NULL DEFAULT '',
  `product_fromcatalog`     TINYINT(1) NOT NULL DEFAULT 0,
  `product_type`            TINYINT NOT NULL DEFAULT 1,
  `product_id_catalog`      INT UNSIGNED NOT NULL DEFAULT 0,
  `product_limited_unit`    INT NOT NULL DEFAULT 0,
  `order_paymentmethod`     TINYINT NOT NULL DEFAULT 1,
  `order_discount_coupon`   VARCHAR(32) NOT NULL DEFAULT '',
  `coupon_discount_amt`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `product_usdprice`        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `product_arsprice`        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `product_cur_steam_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `product_name`            VARCHAR(255) NOT NULL DEFAULT '',
  `product_sellingsite`     TINYINT NOT NULL DEFAULT 0,
  `product_site_url`        VARCHAR(255) NOT NULL DEFAULT '',
  `product_limited_discount` TINYINT(1) NOT NULL DEFAULT 0,
  `order_informedpayment`   TINYINT(1) NOT NULL DEFAULT 0,
  `order_informed_date`     DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `order_informed_image`    VARCHAR(255) NOT NULL DEFAULT '',
  `order_reserved_game`     TINYINT(1) NOT NULL DEFAULT 0,
  `order_sentkeys`          TEXT,
  `buyer_name`              VARCHAR(100) NOT NULL DEFAULT '',
  `buyer_email`             VARCHAR(100) NOT NULL DEFAULT '',
  `buyer_ip`                VARCHAR(45) NOT NULL DEFAULT '',
  `buyer_steam_url`         VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`order_number`),
  UNIQUE KEY `uq_order_id` (`order_id`),
  KEY `idx_status` (`order_status`, `order_date`),
  KEY `idx_buyer_email` (`buyer_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- -----------------------------------------------------------------------------
-- cd_payments: payments received through CuentaDigital accounts (1..3).
-- Used by: process_payments.php, cargar_pago_manualmente.php, payment_list.php,
--   admin/index.php, admin/pedidos.php.
-- `date` is compared against 'Y-m-d' and CURRENT_DATE, so it is a DATE.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cd_payments` (
  `number`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cd_account`     TINYINT NOT NULL DEFAULT 0,
  `date`           DATE DEFAULT NULL,
  `net_ammount`    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `invoice_number` VARCHAR(32) NOT NULL DEFAULT '',
  `site_payment`   TINYINT(1) NOT NULL DEFAULT 0,
  `order_id`       VARCHAR(16) NOT NULL DEFAULT '',
  `description`    VARCHAR(255) NOT NULL DEFAULT '',
  `price_warning`  TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`number`),
  KEY `idx_date` (`date`),
  KEY `idx_order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- -----------------------------------------------------------------------------
-- facturas: electronic invoices (AFIP) associated to orders.
-- Used by: factura/ver_factura.php, process_payments.php, payment_list.php,
--   admin/pedido.php.
-- factura_productos: "cod|name|qty|price" items separated by ','.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `facturas` (
  `factura_id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `factura_cuit`       VARCHAR(16) NOT NULL DEFAULT '',
  `factura_iibb`       VARCHAR(16) NOT NULL DEFAULT '',
  `factura_ptovta`     INT NOT NULL DEFAULT 0,
  `factura_nro`        INT NOT NULL DEFAULT 0,
  `factura_fecha`      DATE DEFAULT NULL,
  `factura_cae`        VARCHAR(20) NOT NULL DEFAULT '',
  `factura_vtocae`     DATE DEFAULT NULL,
  `factura_receptor`   VARCHAR(100) NOT NULL DEFAULT '',
  `factura_productos`  TEXT,
  `factura_total`      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `factura_pedidoasoc` VARCHAR(16) NOT NULL DEFAULT '',
  PRIMARY KEY (`factura_id`),
  KEY `idx_pedidoasoc` (`factura_pedidoasoc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- -----------------------------------------------------------------------------
-- discount_coupons: discount codes applied at checkout (games only).
-- Used by: purchase-functions.php (checkCouponValidity), comprar/*.
-- coupon_id is an assumption (never referenced in code).
-- coupon_excluded_products / coupon_included_products: comma-separated product ids.
-- coupon_discount_criteria: comma-separated keywords (only 'stock' is known).
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `discount_coupons` (
  `coupon_id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `coupon_code`                VARCHAR(32) NOT NULL DEFAULT '',
  `coupon_discount_percentage` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `coupon_start_date`          DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `coupon_end_date`            DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `coupon_excluded_products`   TEXT,
  `coupon_included_products`   TEXT,
  `coupon_discount_criteria`   VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`coupon_id`),
  UNIQUE KEY `uq_coupon_code` (`coupon_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- -----------------------------------------------------------------------------
-- game_categories: tag list shown in the header "explore" menu; rebuilt
-- (TRUNCATE + INSERT) by global_scripts/php/update_game_tag_list.php.
-- Used by: global_scripts/php/header.php.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `game_categories` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tag_name`      VARCHAR(100) NOT NULL DEFAULT '',
  `product_count` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_product_count` (`product_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- -----------------------------------------------------------------------------
-- banlist: banned client IPs.
-- Used by: global_scripts/php/client_page_preload.php, admin/scripts/php/ajax_banlist.php.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `banlist` (
  `id`     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip`     VARCHAR(45) NOT NULL DEFAULT '',
  `reason` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- -----------------------------------------------------------------------------
-- logintries: admin login attempts per IP (rate limiting; 5 failures = locked).
-- Admin credentials are NOT stored here (hash is hardcoded in
-- global_scripts/php/admlogin_functions.php).
-- Used by: admin/index.php.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `logintries` (
  `ip_index`     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip`           VARCHAR(45) NOT NULL DEFAULT '',
  `failed_tries` INT NOT NULL DEFAULT 0,
  `passwords`    TEXT,
  `last_try`     DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`ip_index`),
  UNIQUE KEY `uq_ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
