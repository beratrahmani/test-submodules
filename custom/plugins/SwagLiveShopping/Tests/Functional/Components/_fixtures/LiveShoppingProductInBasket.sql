INSERT INTO `s_articles_lives` (`id`, `article_id`, `type`, `name`, `active`, `order_number`, `max_quantity_enable`, `max_quantity`, `max_purchase`, `valid_from`, `valid_to`, `datum`, `sells`) VALUES
  (3, 170, 1, 'My Liveshopping', 1, '08154712', 0, 0, 0, '2017-05-01 00:00:00', '2100-05-31 00:00:00', '2017-05-23 11:17:35', 9999);

INSERT INTO `s_articles_live_customer_groups` (`id`, `live_shopping_id`, `customer_group_id`) VALUES
  (6, 3, 1);

INSERT INTO `s_articles_live_prices` (`id`, `live_shopping_id`, `customer_group_id`, `price`, `endprice`) VALUES
  (3, 3, 1, 33.571428571429, 25.210084033613);

INSERT INTO `s_articles_live_shoprelations` (`id`, `live_shopping_id`, `shop_id`) VALUES
  (7, 3, 1);

INSERT INTO `s_order_basket` (`id`, `sessionID`, `userID`, `articlename`, `articleID`, `ordernumber`, `shippingfree`, `quantity`, `price`, `netprice`, `tax_rate`, `datum`, `modus`, `esdarticle`, `partnerID`, `lastviewport`, `useragent`, `config`, `currencyFactor`) VALUES
  (672, 'sessionId', 1, 'Sonnenbrille \"Red\"', 170, 'SW10170', 0, 1, 29.999999999999, 25.210084033613, 19, '2017-05-23 13:45:54', 0, 0, '', '', '', '', 1),
  (673, 'sessionId', 1, 'Warenkorbrabatt', 0, 'SHIPPINGDISCOUNT', 0, 1, -2, -1.68, 19, '2017-05-23 13:45:54', 4, 0, '', '', '', '', 1);

INSERT INTO `s_order_basket_attributes` (`id`, `basketID`, `attribute1`, `attribute2`, `attribute3`, `attribute4`, `attribute5`, `attribute6`, `swag_live_shopping_timestamp`, `swag_live_shopping_id`) VALUES
  (34, 672, NULL, NULL, NULL, NULL, NULL, NULL, '2017-05-23 01:45:54', 3);