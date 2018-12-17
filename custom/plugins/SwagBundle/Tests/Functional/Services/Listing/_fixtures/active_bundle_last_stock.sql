INSERT INTO `s_articles_bundles` (`id`, `articleID`, `name`, `show_name`, `active`, `description`, `rab_type`, `taxID`, `ordernumber`, `max_quantity_enable`, `display_global`, `display_delivery`, `max_quantity`, `valid_from`, `valid_to`, `datum`, `sells`, `bundle_type`, `bundle_position`) VALUES
  (1, 272, 'New Bundle', 0, 1, '', 'pro', NULL, '08154711', 0, 1, 1, 0, NULL, NULL, '2018-04-23 13:42:17', 0, 1, 0);

INSERT INTO `s_articles_bundles_articles` (`id`, `bundle_id`, `article_detail_id`, `quantity`, `configurable`, `bundle_group_id`, `position`) VALUES
  (1, 1, 45, 1, 0, NULL, 1);

INSERT INTO `s_articles_bundles_customergroups` (`id`, `bundle_id`, `customer_group_id`) VALUES
  (12, 1, 1),
  (13, 1, 2);

INSERT INTO `s_articles_bundles_prices` (`id`, `bundle_id`, `customer_group_id`, `price`) VALUES
  (1, 1, '1', 10),
  (2, 1, '2', 20);

