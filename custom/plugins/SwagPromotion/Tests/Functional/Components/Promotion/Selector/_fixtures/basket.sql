INSERT INTO `s_order_basket` (
  `id`,
  `sessionID`,
  `userID`,
  `articlename`,
  `articleID`,
  `ordernumber`,
  `shippingfree`,
  `quantity`,
  `price`,
  `netprice`,
  `tax_rate`,
  `datum`,
  `modus`,
  `esdarticle`,
  `partnerID`,
  `lastviewport`,
  `useragent`,
  `config`,
  `currencyFactor`
)
VALUES
  (12345, 'test-session', 1, 'Cigar Special 40%', 6, 'SW10006', 0, 1, 35.95, 30.210084033613, 19, '2017-06-27 16:03:42', 0, 0, '', 'checkout', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.109 Safari/537.36', '', 1),
  (123456, 'test-session', 1, 'Cigar Special 40%', 6, 'SW10006', 0, 1, 35.95, 30.210084033613, 19, '2017-06-27 16:03:42', 0, 0, '', 'checkout', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.109 Safari/537.36', '', 1),
  (1234567, 'test-session', 1, 'Warenkorbrabatt', 0, 'SHIPPINGDISCOUNT', 0, 1, -2, -1.68, 19, '2017-06-27 16:04:23', 4, 0, '', '', '', '', 1);

INSERT INTO `s_order_basket_attributes` (basketID, swag_is_free_good_by_promotion_id)
VALUES (123456, 'a:1:{i:0;i:9999;}');