INSERT INTO `s_plugin_promotion` (`id`, `name`, `rules`, `apply_rules`, `type`, `number`, `description`, `detail_description`, `max_usage`, `voucher_id`, `no_vouchers`, `valid_from`, `valid_to`, `stack_mode`, `amount`, `step`, `max_quantity`, `active`, `exclusive`, `shipping_free`, `priority`, `stop_processing`, `show_badge`, `badge_text`, `apply_rules_first`) VALUES
(1, 'Meine neue Promotion', '{"and":{"basketCompareRule0.5621785805656028":["amountGross",">","10"]}}', '{"and":{"true1":[]}}', 'product.freegoods', 'promo_gratisartikel', '', '', 0, NULL, 0, NULL, NULL, 'global', 0, NULL, NULL, 1, 0, 1, 0, 0, 1, 'gratisartikel', 0);

INSERT INTO `s_plugin_promotion_free_goods` (`promotionID`, `articleID`) VALUES
(1, 178);

INSERT INTO `s_order_basket` (`id`, `sessionID`, `userID`, `articlename`, `articleID`, `ordernumber`, `shippingfree`, `quantity`, `price`, `netprice`, `tax_rate`, `datum`, `modus`, `esdarticle`, `partnerID`, `lastviewport`, `useragent`, `config`, `currencyFactor`) VALUES
(670658, 'f3e4eeda1d0b38ffa5acfb336886a1193c0bc0e4992555424cd59c155f2e1e89', 0, 'Sonnenbrille "Red"', 170, 'SW10170', 0, 1, 39.95, 33.571428571429, 19, '2017-03-24 07:32:24', 0, 0, '', '', '', '', 1),
(672659, 'f3e4eeda1d0b38ffa5acfb336886a1193c0bc0e4992555424cd59c155f2e1e89', 0, 'Strandtuch "Ibiza"', 178, 'SW10178', 0, 1, 19.95, 16.764705882353, 19, '2017-03-24 07:32:26', 0, 0, '', '', '', '', 1),
(674660, 'f3e4eeda1d0b38ffa5acfb336886a1193c0bc0e4992555424cd59c155f2e1e89', 0, 'Meine neue Promotion', 0, 'promo_gratisartikel', 1, 1, -19.95, -16.764705882353, 19, '0000-00-00 00:00:00', 4, 0, '', '', '', '', 1);

INSERT INTO `s_order_basket_attributes` (`id`, `basketID`, `attribute1`, `attribute2`, `attribute3`, `attribute4`, `attribute5`, `attribute6`, `swag_promotion_id`, `swag_is_free_good_by_promotion_id`) VALUES
(3365823, 670658, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3465823, 672659, '', NULL, NULL, NULL, NULL, NULL, NULL, 'a:1:{i:0;i:1;}'),
(3665823, 674660, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL);
