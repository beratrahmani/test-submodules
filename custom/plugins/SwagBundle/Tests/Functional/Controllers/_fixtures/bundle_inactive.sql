INSERT INTO `s_articles_bundles` (`id`, `articleID`, `name`, `show_name`, `active`, `description`, `rab_type`, `taxID`, `ordernumber`, `max_quantity_enable`, `display_global`, `display_delivery`, `max_quantity`, `valid_from`, `valid_to`, `datum`, `sells`, `bundle_type`, `bundle_position`) VALUES
(:bundleId, 178, 'New Bundle', 1, 0, '', 'pro', NULL, 'SWTESTBUNDLE', 0, 1, 1, 0, NULL, NULL, '2017-05-03 11:47:15', 0, 1, 0);

INSERT INTO `s_articles_bundles_articles` (`bundle_id`, `article_detail_id`, `quantity`, `configurable`, `bundle_group_id`, `position`) VALUES
(:bundleId, 827, 1, 0, NULL, 1);

INSERT INTO `s_articles_bundles_customergroups` (`bundle_id`, `customer_group_id`) VALUES
(:bundleId, 1);

INSERT INTO `s_articles_bundles_prices` (`bundle_id`, `customer_group_id`, `price`) VALUES
(:bundleId, '1', 10),
(:bundleId, '2', 5);