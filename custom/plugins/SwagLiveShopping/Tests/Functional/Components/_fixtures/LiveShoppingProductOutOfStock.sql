INSERT INTO `s_articles_lives` (`id`, `article_id`, `type`, `name`, `active`, `order_number`, `max_quantity_enable`, `max_quantity`, `max_purchase`, `valid_from`, `valid_to`, `datum`, `sells`) VALUES
  (30001, 170, 1, 'My Liveshopping', 1, '08154712', 1, 0, 0, '2017-05-01 00:00:00', '2100-05-31 00:00:00', '2017-05-23 11:17:35', 9999);

INSERT INTO `s_articles_live_customer_groups` (`id`, `live_shopping_id`, `customer_group_id`) VALUES
  (30001, 30001, 1);

INSERT INTO `s_articles_live_prices` (`id`, `live_shopping_id`, `customer_group_id`, `price`, `endprice`) VALUES
  (30001, 30001, 1, 33.571428571429, 25.210084033613);

INSERT INTO `s_articles_live_shoprelations` (`id`, `live_shopping_id`, `shop_id`) VALUES
  (30001, 30001, 1);