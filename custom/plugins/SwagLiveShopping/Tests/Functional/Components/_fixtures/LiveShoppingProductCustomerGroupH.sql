INSERT INTO `s_articles_lives` (`id`, `article_id`, `type`, `name`, `active`, `order_number`, `max_quantity_enable`, `max_quantity`, `max_purchase`, `valid_from`, `valid_to`, `datum`, `sells`) VALUES
  (10003, 178, 1, 'My Liveshopping', 1, '08154711', 0, 0, 0, '2017-05-01 00:00:00', '2100-01-31 00:00:00', '2017-05-23 10:11:55', 0);

INSERT INTO `s_articles_live_customer_groups` (`id`, `live_shopping_id`, `customer_group_id`) VALUES
  (10003, 10003, 2);

INSERT INTO `s_articles_live_prices` (`id`, `live_shopping_id`, `customer_group_id`, `price`, `endprice`) VALUES
  (10003, 10003, 2, 16.764705882353, 8.4033613445378);

INSERT INTO `s_articles_live_shoprelations` (`id`, `live_shopping_id`, `shop_id`) VALUES
  (10003, 10003, 1);