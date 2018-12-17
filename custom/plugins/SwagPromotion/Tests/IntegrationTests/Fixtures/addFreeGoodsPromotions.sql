INSERT INTO `s_plugin_promotion` (`id`, `name`, `rules`, `apply_rules`, `type`, `number`, `description`, `detail_description`, `max_usage`, `voucher_id`, `no_vouchers`, `valid_from`, `valid_to`, `stack_mode`, `amount`, `step`, `max_quantity`, `active`, `exclusive`, `shipping_free`, `priority`, `stop_processing`, `show_badge`, `badge_text`, `apply_rules_first`) VALUES
(399941, 'Meine neue Promotion', '{"and":{"basketCompareRule0.24919979813464255":["amountGross",">=","80"]}}', '{"and":{"true1":[]}}', 'product.freegoods', 'freeGoods1', '', '', 0, NULL, 0, NULL, NULL, 'global', 0, NULL, NULL, 1, 0, 0, 0, 0, 1, '', 0),
(499942, 'Meine neue Promotion', '{"and":{"true0.45103727028182994":[null,null,""]}}', '{"and":{"productCompareRule0.9031993217174947":["categories.id","=","14"]}}', 'product.freegoods', 'freeGoods2', '', '', 0, NULL, 0, NULL, NULL, 'global', 0, NULL, NULL, 1, 0, 0, 0, 0, 1, '', 0),
(599943, 'Meine neue Promotion', '{"and":{"true1":[]}}', '{"and":{"true1":[]}}', 'product.freegoods', 'freeGoods3', '', '', 0, NULL, 0, NULL, NULL, 'global', 0, NULL, NULL, 1, 0, 0, 0, 0, 1, '', 0),
(699944, 'Meine neue Promotion', '{"and":{"basketCompareRule0.1907117051389995":["amountGross",">=","10"]}}', '{"and":{"true1":[]}}', 'basket.percentage', 'percentageBasket', '', '', 0, NULL, 0, NULL, NULL, 'global', 10, NULL, NULL, 1, 0, 0, 0, 0, 1, '', 0);

INSERT INTO `s_plugin_promotion_free_goods` (`promotionID`, `articleID`) VALUES
(399941, 178),
(499942, 175),
(599943, 178);

INSERT INTO `s_order_basket` (`id`, `sessionID`, `userID`, `articlename`, `articleID`, `ordernumber`, `shippingfree`, `quantity`, `price`, `netprice`, `tax_rate`, `datum`, `modus`, `esdarticle`, `partnerID`, `lastviewport`, `useragent`, `config`, `currencyFactor`) VALUES
(67500158, 'sessionId', 0, 'Cigar Special 40%', 6, 'SW10006', 0, 2, 35.95, 30.210084033613, 19, '2017-03-27 14:51:51', 0, 0, '', 'detail', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36', '', 1),
(67800158, 'sessionId', 0, 'Strandtuch "Ibiza"', 178, 'SW10178', 0, 3, 19.95, 16.764705882353, 19, '2017-03-27 14:52:34', 0, 0, '', 'detail', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36', '', 1),
(70000158, 'sessionId', 0, 'Strandtuch Sunny', 175, 'SW10175', 0, 1, 59.99, 50.411764705882, 19, '2017-03-27 14:56:20', 0, 0, '', '', '', '', 1),
(70500158, 'sessionId', 0, 'Warenkorbrabatt', 0, 'SHIPPINGDISCOUNT', 0, 1, -2, -1.68, 19, '2017-03-27 14:56:20', 4, 0, '', '', '', '', 1),
(70600158, 'sessionId', 0, 'Meine neue Promotion1', 0, 'freeGoods1', 0, 1, -19.95, -16.764705882353, 19, '0000-00-00 00:00:00', 4, 0, '', '', '', '', 1),
(70700158, 'sessionId', 0, 'Meine neue Promotion2', 0, 'freeGoods2', 0, 1, -59.99, -50.411764705882, 19, '0000-00-00 00:00:00', 4, 0, '', '', '', '', 1),
(70800158, 'sessionId', 0, 'Meine neue Promotion3', 0, 'freeGoods3', 0, 1, -19.95, -16.764705882353, 19, '0000-00-00 00:00:00', 4, 0, '', '', '', '', 1),
(70900158, 'sessionId', 0, 'Meine neue Promotion4', 0, 'percentageBasket', 0, 1, -8.985, -7.5504201680672, 19, '0000-00-00 00:00:00', 4, 0, '', '', '', '', 1);

