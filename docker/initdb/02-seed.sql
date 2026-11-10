-- =============================================================================
-- SteamBuy demo seed data. ALL DATA IS FAKE (demo only).
-- Requires 01-schema.sql. Game images come from docker/seed-assets (git-ignored; the
-- Dockerfile stages them in the image and the entrypoint copies them into data/img).
-- Admin access is NOT seeded: the admin password hash is hardcoded in
-- public_html/global_scripts/php/admlogin_functions.php (not in the DB).
-- =============================================================================

SET NAMES utf8;

-- Settings the code expects to exist (it only UPDATEs them).
-- Dollar quote is fixed (autoupdate off) so no network call is needed.
INSERT INTO `settings` (`name`, `value`) VALUES
  ('autoupdate_dollar_value', '0'),
  ('fixed_dollar_value', '1000'),
  ('updated_dollar_value', '1000'),
  ('dollar_retrieve_attemps', '0'),
  ('service_enabled', '1'),
  ('brl_quote', '5.00'),
  ('mxn_quote', '20.00'),
  ('alicuota_menor32', '1.30'),
  ('alicuota_mayor32', '1.20'),
  ('frecuent_tags', 'accion,aventura,indie,rpg,estrategia,carreras,simulacion,deportes,puzzle,terror'),
  ('payments_last_revised', ''),
  ('today_payments', ''),
  ('today_payments_names', ''),
  ('today_sitepayments', '0'),
  ('today_totalpayments', '0')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

-- Demo games (ids 1-42), images from the recovered box art set (docker/seed-assets).
-- Offer types: regular price, internal stock offer in USD (custom price + limited
-- units), external (Steam) limited-time offer, internal offer in ARS (Magicka),
-- out of stock (NBA 2K13, id 38), disabled (Team Fortress 2, id 4).
INSERT INTO `products`
  (`product_id`, `product_enabled`, `product_update_error`, `product_name`, `product_rating`, `product_platform`,
   `product_sellingsite`, `product_site_url`, `product_has_limited_units`, `product_limited_units`,
   `product_has_customprice`, `product_customprice_currency`, `product_external_limited_offer`,
   `product_external_offer_endtime`, `product_listprice`, `product_steam_discount_price`, `product_finalprice`,
   `product_mainpicture`, `product_pics`, `product_description`, `product_tags`,
   `product_singleplayer`, `product_multiplayer`, `product_cooperative`)
VALUES
  (1, 1, 0, 'The Witcher 3: Wild Hunt', 990, 1, 1, 'http://store.steampowered.com/app/20001/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 39.99, 0.00, 39.99, 'the-witcher-3-wild-hunt.jpg', '', 'Demo description for The Witcher 3: Wild Hunt. Fictional placeholder text for the demo.', 'rpg,mundo abierto,accion', 1, 0, 0),
  (2, 1, 0, 'Portal 2', 980, 1, 1, 'http://store.steampowered.com/app/20002/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 9.99, 0.00, 9.99, 'portal2.jpg', '', 'Demo description for Portal 2. Fictional placeholder text for the demo.', 'puzzle,ciencia ficcion,cooperativo', 1, 0, 1),
  (3, 1, 0, 'Terraria', 970, 1, 1, 'http://store.steampowered.com/app/20003/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 9.99, 0.00, 9.99, 'terraria.jpg', '', 'Demo description for Terraria. Fictional placeholder text for the demo.', 'aventura,indie,sandbox', 1, 1, 1),
  (4, 0, 0, 'Team Fortress 2', 960, 1, 1, 'http://store.steampowered.com/app/20004/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 0.00, 0.00, 0.00, 'team-fortress-2.jpg', '', 'Demo description for Team Fortress 2. Fictional placeholder text for the demo.', 'accion,multijugador,shooter', 0, 1, 0),
  (5, 1, 0, 'Counter-Strike: Global Offensive', 950, 1, 1, 'http://store.steampowered.com/app/20005/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 14.99, 0.00, 14.99, 'counter-strike-global-offensive.jpg', '', 'Demo description for Counter-Strike: Global Offensive. Fictional placeholder text for the demo.', 'accion,shooter,multijugador', 0, 1, 0),
  (6, 1, 0, 'Fallout 4', 940, 1, 1, 'http://store.steampowered.com/app/20006/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 29.99, 0.00, 29.99, 'fallout-4.jpg', '', 'Demo description for Fallout 4. Fictional placeholder text for the demo.', 'rpg,mundo abierto,accion', 1, 0, 0),
  (7, 1, 0, 'The Elder Scrolls V: Skyrim', 930, 1, 1, 'http://store.steampowered.com/app/20007/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 19.99, 0.00, 19.99, 'the-elder-scrolls-v-skyrim.jpg', '', 'Demo description for The Elder Scrolls V: Skyrim. Fictional placeholder text for the demo.', 'rpg,mundo abierto,aventura', 1, 0, 0),
  (8, 1, 0, 'Left 4 Dead 2', 920, 1, 1, 'http://store.steampowered.com/app/20008/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 9.99, 0.00, 9.99, 'left-4-dead-2.jpg', '', 'Demo description for Left 4 Dead 2. Fictional placeholder text for the demo.', 'accion,cooperativo,zombies', 1, 1, 1),
  (9, 1, 0, 'BioShock Infinite', 910, 1, 1, 'http://store.steampowered.com/app/20009/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 29.99, 0.00, 29.99, 'bioshockinfinite.jpg', '', 'Demo description for BioShock Infinite. Fictional placeholder text for the demo.', 'accion,shooter,historia', 1, 0, 0),
  (10, 1, 0, 'Borderlands 2', 900, 1, 1, 'http://store.steampowered.com/app/20010/', 0, 0, 0, 'usd', 1, '2037-12-31 23:59:59', 19.99, 9.99, 9.99, 'borderlands2.jpg', '', 'Demo description for Borderlands 2. Fictional placeholder text for the demo.', 'accion,rpg,cooperativo', 1, 1, 1),
  (11, 1, 0, 'Far Cry 4', 890, 1, 1, 'http://store.steampowered.com/app/20011/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 29.99, 0.00, 29.99, 'far-cry-4.jpg', '', 'Demo description for Far Cry 4. Fictional placeholder text for the demo.', 'accion,mundo abierto,shooter', 1, 1, 1),
  (12, 1, 0, 'Dark Souls II', 880, 1, 1, 'http://store.steampowered.com/app/20012/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 39.99, 0.00, 39.99, 'dark-souls-ii.jpg', '', 'Demo description for Dark Souls II. Fictional placeholder text for the demo.', 'rpg,accion,dificil', 1, 1, 0),
  (13, 1, 0, 'Assassin''s Creed IV: Black Flag', 870, 1, 1, 'http://store.steampowered.com/app/20013/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 29.99, 0.00, 29.99, 'assassins-creed-iv-black-flag.jpg', '', 'Demo description for Assassin''s Creed IV: Black Flag. Fictional placeholder text for the demo.', 'accion,aventura,mundo abierto', 1, 1, 0),
  (14, 1, 0, 'Just Cause 3', 860, 1, 1, 'http://store.steampowered.com/app/20014/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 34.99, 0.00, 34.99, 'just-cause-3.jpg', '', 'Demo description for Just Cause 3. Fictional placeholder text for the demo.', 'accion,mundo abierto', 1, 0, 0),
  (15, 1, 0, 'PAYDAY 2', 850, 1, 1, 'http://store.steampowered.com/app/20015/', 0, 0, 0, 'usd', 1, '2037-12-31 23:59:59', 19.99, 9.99, 9.99, 'payday2.jpg', '', 'Demo description for PAYDAY 2. Fictional placeholder text for the demo.', 'accion,cooperativo,shooter', 0, 1, 1),
  (16, 1, 0, 'The Forest', 840, 1, 1, 'http://store.steampowered.com/app/20016/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 16.99, 0.00, 16.99, 'the-forest.jpg', '', 'Demo description for The Forest. Fictional placeholder text for the demo.', 'supervivencia,terror,aventura', 1, 1, 1),
  (17, 1, 0, 'Rust', 830, 1, 1, 'http://store.steampowered.com/app/20017/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 19.99, 0.00, 19.99, 'rust.jpg', '', 'Demo description for Rust. Fictional placeholder text for the demo.', 'supervivencia,multijugador', 0, 1, 0),
  (18, 1, 0, 'Starbound', 820, 1, 1, 'http://store.steampowered.com/app/20018/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 14.99, 0.00, 14.99, 'starbound.jpg', '', 'Demo description for Starbound. Fictional placeholder text for the demo.', 'indie,sandbox,aventura', 1, 1, 1),
  (19, 1, 0, 'Hotline Miami', 810, 1, 3, '', 1, 10, 1, 'usd', 0, '0000-00-00 00:00:00', 9.99, 0.00, 5.99, 'hotline-miami.jpg', '', 'Demo description for Hotline Miami. Fictional placeholder text for the demo.', 'accion,indie,retro', 1, 0, 0),
  (20, 1, 0, 'Castle Crashers', 800, 1, 3, '', 1, 11, 1, 'usd', 0, '0000-00-00 00:00:00', 14.99, 0.00, 8.99, 'castle-crashers.jpg', '', 'Demo description for Castle Crashers. Fictional placeholder text for the demo.', 'accion,indie,cooperativo', 1, 1, 1),
  (21, 1, 0, 'FEZ', 790, 1, 3, '', 1, 5, 1, 'usd', 0, '0000-00-00 00:00:00', 9.99, 0.00, 5.99, 'fez.jpg', '', 'Demo description for FEZ. Fictional placeholder text for the demo.', 'puzzle,indie,plataformas', 1, 0, 0),
  (22, 1, 0, 'To the Moon', 780, 1, 3, '', 1, 6, 1, 'usd', 0, '0000-00-00 00:00:00', 9.99, 0.00, 5.99, 'to-the-moon.jpg', '', 'Demo description for To the Moon. Fictional placeholder text for the demo.', 'indie,historia,aventura', 1, 0, 0),
  (23, 1, 0, 'Thomas Was Alone', 770, 1, 1, 'http://store.steampowered.com/app/20023/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 9.99, 0.00, 9.99, 'thomas-was-alone.jpg', '', 'Demo description for Thomas Was Alone. Fictional placeholder text for the demo.', 'puzzle,indie,plataformas', 1, 0, 0),
  (24, 1, 0, 'The Stanley Parable', 760, 1, 1, 'http://store.steampowered.com/app/20024/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 14.99, 0.00, 14.99, 'the-stanley-parable.jpg', '', 'Demo description for The Stanley Parable. Fictional placeholder text for the demo.', 'indie,aventura,historia', 1, 0, 0),
  (25, 1, 0, 'Dear Esther', 750, 1, 1, 'http://store.steampowered.com/app/20025/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 9.99, 0.00, 9.99, 'dear-esther.jpg', '', 'Demo description for Dear Esther. Fictional placeholder text for the demo.', 'indie,aventura,historia', 1, 0, 0),
  (26, 1, 0, 'Antichamber', 740, 1, 1, 'http://store.steampowered.com/app/20026/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 19.99, 0.00, 19.99, 'antichamber.jpg', '', 'Demo description for Antichamber. Fictional placeholder text for the demo.', 'puzzle,indie', 1, 0, 0),
  (27, 1, 0, 'Magicka', 730, 1, 3, '', 1, 10, 1, 'ars', 0, '0000-00-00 00:00:00', 0.00, 0.00, 4500.00, 'magicka.jpg', '', 'Demo description for Magicka. Fictional placeholder text for the demo.', 'accion,cooperativo,indie', 1, 1, 1),
  (28, 1, 0, 'Torchlight II', 720, 1, 1, 'http://store.steampowered.com/app/20028/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 19.99, 0.00, 19.99, 'torchlight2.jpg', '', 'Demo description for Torchlight II. Fictional placeholder text for the demo.', 'rpg,accion,cooperativo', 1, 1, 1),
  (29, 1, 0, 'Brothers: A Tale of Two Sons', 710, 1, 1, 'http://store.steampowered.com/app/20029/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 14.99, 0.00, 14.99, 'brothers-a-tale-of-two-sons.jpg', '', 'Demo description for Brothers: A Tale of Two Sons. Fictional placeholder text for the demo.', 'aventura,indie,historia', 1, 0, 1),
  (30, 1, 0, 'Mark of the Ninja', 700, 1, 1, 'http://store.steampowered.com/app/20030/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 14.99, 0.00, 14.99, 'mark-of-the-ninja.jpg', '', 'Demo description for Mark of the Ninja. Fictional placeholder text for the demo.', 'accion,indie,sigilo', 1, 0, 0),
  (31, 1, 0, 'Sid Meier''s Civilization V', 690, 1, 1, 'http://store.steampowered.com/app/20031/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 29.99, 0.00, 29.99, 'sid-meiers-civilization-v.jpg', '', 'Demo description for Sid Meier''s Civilization V. Fictional placeholder text for the demo.', 'estrategia,turnos', 1, 1, 0),
  (32, 1, 0, 'Total War: ROME II', 680, 1, 1, 'http://store.steampowered.com/app/20032/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 59.99, 0.00, 59.99, 'total-war-rome-ii.jpg', '', 'Demo description for Total War: ROME II. Fictional placeholder text for the demo.', 'estrategia,historia', 1, 1, 0),
  (33, 1, 0, 'Age of Empires II HD', 670, 1, 1, 'http://store.steampowered.com/app/20033/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 19.99, 0.00, 19.99, 'age2hd.jpg', '', 'Demo description for Age of Empires II HD. Fictional placeholder text for the demo.', 'estrategia,clasico,multijugador', 1, 1, 0),
  (34, 1, 0, 'Endless Space', 660, 1, 1, 'http://store.steampowered.com/app/20034/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 19.99, 0.00, 19.99, 'endless_space.jpg', '', 'Demo description for Endless Space. Fictional placeholder text for the demo.', 'estrategia,ciencia ficcion,espacio', 1, 1, 0),
  (35, 1, 0, 'Tropico 5', 650, 1, 1, 'http://store.steampowered.com/app/20035/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 29.99, 0.00, 29.99, 'tropico-5.jpg', '', 'Demo description for Tropico 5. Fictional placeholder text for the demo.', 'estrategia,simulacion', 1, 1, 0),
  (36, 1, 0, 'Space Engineers', 640, 1, 1, 'http://store.steampowered.com/app/20036/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 19.99, 0.00, 19.99, 'space-engineers.jpg', '', 'Demo description for Space Engineers. Fictional placeholder text for the demo.', 'simulacion,sandbox,espacio', 1, 1, 1),
  (37, 1, 0, 'Assetto Corsa', 630, 1, 1, 'http://store.steampowered.com/app/20037/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 19.99, 0.00, 19.99, 'assetto-corsa.jpg', '', 'Demo description for Assetto Corsa. Fictional placeholder text for the demo.', 'carreras,simulacion,deportes', 1, 1, 0),
  (38, 1, 0, 'NBA 2K13', 620, 1, 3, '', 1, 0, 1, 'usd', 0, '0000-00-00 00:00:00', 19.99, 0.00, 11.99, 'nba2k13.jpg', '', 'Demo description for NBA 2K13. Fictional placeholder text for the demo.', 'deportes,simulacion', 1, 1, 0),
  (39, 1, 0, 'Sleeping Dogs', 610, 1, 1, 'http://store.steampowered.com/app/20039/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 14.99, 0.00, 14.99, 'sleeping_dogs.jpg', '', 'Demo description for Sleeping Dogs. Fictional placeholder text for the demo.', 'accion,mundo abierto', 1, 0, 0),
  (40, 1, 0, 'Dead Space 3', 600, 1, 1, 'http://store.steampowered.com/app/20040/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 19.99, 0.00, 19.99, 'dead_space_3.jpg', '', 'Demo description for Dead Space 3. Fictional placeholder text for the demo.', 'terror,accion,cooperativo', 1, 0, 1),
  (41, 1, 0, 'Alien: Isolation', 590, 1, 1, 'http://store.steampowered.com/app/20041/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 29.99, 0.00, 29.99, 'alien-isolation.jpg', '', 'Demo description for Alien: Isolation. Fictional placeholder text for the demo.', 'terror,supervivencia,sigilo', 1, 0, 0),
  (42, 1, 0, 'State of Decay', 580, 1, 3, '', 1, 5, 1, 'usd', 0, '0000-00-00 00:00:00', 19.99, 0.00, 11.99, 'state-of-decay.jpg', '', 'Demo description for State of Decay. Fictional placeholder text for the demo.', 'zombies,supervivencia,accion', 1, 0, 0),
  (43, 1, 0, 'The Wolf Among Us', 570, 1, 1, 'http://store.steampowered.com/app/20043/', 0, 0, 0, 'usd', 0, '0000-00-00 00:00:00', 14.99, 0.00, 14.99, 'the-wolf-among-us.jpg', '', 'Demo description for The Wolf Among Us. Fictional placeholder text for the demo.', 'aventura,historia,episodico', 1, 0, 0);

-- Tag list shown in the header menu (normally rebuilt by update_game_tag_list.php).
INSERT INTO `game_categories` (`tag_name`, `product_count`) VALUES
  ('accion', 19), ('indie', 13), ('aventura', 10), ('cooperativo', 8), ('historia', 7), ('mundo abierto', 7), ('rpg', 6), ('estrategia', 5), ('puzzle', 4), ('shooter', 4), ('supervivencia', 4), ('multijugador', 3), ('sandbox', 3), ('simulacion', 3), ('terror', 3), ('ciencia ficcion', 2), ('espacio', 2), ('plataformas', 2), ('sigilo', 2), ('zombies', 2);

-- Demo gift cards (type: 1=Steam Wallet, 2=PlayStation Store, 3=Xbox).
INSERT INTO `products_giftcards` (`id`, `type`, `name`, `usd_ammount`, `selling_price_usd`, `stock`, `relevance`) VALUES
  (1, 1, 'Steam Wallet 5 USD (demo)', 5.00, 5.50, 20, 100),
  (2, 1, 'Steam Wallet 10 USD (demo)', 10.00, 10.80, 20, 90),
  (3, 1, 'Steam Wallet 20 USD (demo)', 20.00, 21.40, 10, 80),
  (4, 2, 'PlayStation Store 10 USD (demo)', 10.00, 11.00, 10, 70),
  (5, 2, 'PlayStation Store 25 USD (demo)', 25.00, 26.80, 5, 60),
  (6, 3, 'Xbox 10 USD (demo)', 10.00, 11.00, 10, 50),
  (7, 3, 'Xbox 25 USD (demo)', 25.00, 26.80, 5, 40);

-- Demo coupon: DEMO10 = 10% off, valid until 2037, games only (excludes ids 4 and 38).
INSERT INTO `discount_coupons`
  (`coupon_code`, `coupon_discount_percentage`, `coupon_start_date`, `coupon_end_date`,
   `coupon_excluded_products`, `coupon_included_products`, `coupon_discount_criteria`)
VALUES
  ('DEMO10', 10.00, '2020-01-01 00:00:00', '2037-12-31 23:59:59', '4,38', '', 'stock');

-- Two fake orders so the admin order lists render. Order passwords are demo values.
-- Order ids follow the "J" + order_number convention.
INSERT INTO `orders`
  (`order_number`, `order_send_method`, `order_id`, `order_password`, `order_date`, `order_status`, `order_status_change`,
   `order_confirmed_payment`, `order_payment_time`, `order_purchaseticket`, `product_fromcatalog`, `product_type`,
   `product_id_catalog`, `product_limited_unit`, `order_paymentmethod`, `order_discount_coupon`, `coupon_discount_amt`,
   `product_usdprice`, `product_arsprice`, `product_cur_steam_price`, `product_name`, `product_sellingsite`,
   `product_site_url`, `product_limited_discount`, `order_informedpayment`, `order_informed_date`, `order_informed_image`,
   `order_reserved_game`, `order_sentkeys`, `buyer_name`, `buyer_email`, `buyer_ip`, `buyer_steam_url`)
VALUES
  (10001, 0, 'J10001', 'demopass01', '2026-01-15 12:00:00', 1, '0000-00-00 00:00:00',
   0, '0000-00-00 00:00:00', '', 1, 1,
   1, 0, 2, '', 0.00,
   39.99, 63000.00, 39.99, 'The Witcher 3: Wild Hunt', 1,
   'http://store.steampowered.com/app/20001/', 0, 0, '0000-00-00 00:00:00', '',
   0, '', 'Demo Buyer', 'buyer1@example.com', '127.0.0.1', ''),
  (10002, 0, 'J10002', 'demopass02', '2026-01-10 09:30:00', 2, '2026-01-10 10:00:00',
   1, '2026-01-10 09:45:00', '', 1, 1,
   2, 0, 2, '', 0.00,
   9.99, 15800.00, 9.99, 'Portal 2', 1,
   'http://store.steampowered.com/app/20002/', 0, 0, '0000-00-00 00:00:00', '',
   0, 'DEMO0-FAKE0-KEY00', 'Demo Buyer Two', 'buyer2@example.com', '127.0.0.1', '');

ALTER TABLE `orders` AUTO_INCREMENT = 10003;
