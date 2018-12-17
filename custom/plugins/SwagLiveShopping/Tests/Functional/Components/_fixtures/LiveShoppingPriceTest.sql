INSERT INTO `s_articles_lives` (`id`, `article_id`, `type`, `name`, `active`, `order_number`, `max_quantity_enable`, `max_quantity`, `max_purchase`, `valid_from`, `valid_to`, `datum`, `sells`) VALUES
  (1, 272, 1, 'My Liveshopping', 1, '08154', 0, 0, 0, '2018-07-20 00:00:00', '2018-07-31 00:00:00', '2018-07-20 12:31:30', 0);

INSERT INTO `s_articles_live_customer_groups` (`id`, `live_shopping_id`, `customer_group_id`) VALUES
  (1, 1, 1);

INSERT INTO `s_articles_live_prices` (`id`, `live_shopping_id`, `customer_group_id`, `price`, `endprice`) VALUES
  (1, 1, 1, 114.01869158879, 93.457943925234);

INSERT INTO `s_articles_live_shoprelations` (`id`, `live_shopping_id`, `shop_id`) VALUES
  (1, 1, 1);
