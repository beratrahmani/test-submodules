INSERT INTO `s_articles_bundles` (`id`, `articleID`, `name`, `show_name`, `active`, `description`, `rab_type`, `taxID`, `ordernumber`, `max_quantity_enable`, `display_global`, `display_delivery`, `max_quantity`, `valid_from`, `valid_to`, `datum`, `sells`, `bundle_type`, `bundle_position`) VALUES
  (2, 153, 'New Bundle', 0, 1, '', 'pro', NULL, 'fhndhgdh08154712', 0, 0, 1, 4, NULL, NULL, '2018-04-18 09:52:04', 0, 1, 0);


INSERT INTO `s_articles_bundles_articles` (`id`, `bundle_id`, `article_detail_id`, `quantity`, `configurable`, `bundle_group_id`, `position`) VALUES
  (2, 2, 407, 1, 0, NULL, 1);


INSERT INTO `s_articles_bundles_customergroups` (`id`, `bundle_id`, `customer_group_id`) VALUES
  (11, 2, 1);


INSERT INTO `s_articles_bundles_prices` (`id`, `bundle_id`, `customer_group_id`, `price`) VALUES
  (2, 2, '1', 10);