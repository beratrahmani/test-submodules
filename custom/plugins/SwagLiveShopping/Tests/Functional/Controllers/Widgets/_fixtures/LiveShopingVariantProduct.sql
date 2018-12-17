INSERT INTO `s_articles_lives` (`id`, `article_id`, `type`, `name`, `active`, `order_number`, `max_quantity_enable`, `max_quantity`, `max_purchase`, `valid_from`, `valid_to`, `datum`, `sells`) VALUES
  (20001, 153, 1, 'My variant Liveshopping', 1, '08154712', 0, 0, 0, '2017-05-01 00:00:00', '2100-05-31 00:00:00', '2017-05-23 10:36:29', 0);

INSERT INTO `s_articles_live_customer_groups` (`id`, `live_shopping_id`, `customer_group_id`) VALUES
  (2, 20001, 1);

INSERT INTO `s_articles_live_prices` (`id`, `live_shopping_id`, `customer_group_id`, `price`, `endprice`) VALUES
  (2, 20001, 1, 5.8739495798319, 4.2016806722689);

INSERT INTO `s_articles_live_shoprelations` (`id`, `live_shopping_id`, `shop_id`) VALUES
  (2, 20001, 1);

INSERT INTO `s_articles_live_stint` (`id`, `live_shopping_id`, `article_detail_id`) VALUES
  (1, 20001, 322);
