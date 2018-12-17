INSERT INTO `s_articles_bundles` (`id`, `articleID`, `name`, `show_name`, `active`, `description`, `rab_type`, `taxID`, `ordernumber`, `max_quantity_enable`, `display_global`, `display_delivery`, `max_quantity`, `valid_from`, `valid_to`, `datum`, `sells`, `bundle_type`, `bundle_position`) VALUES
(:bundleId, 178, 'New Bundle in basket', 1, 1, '', 'pro', NULL, 'SWTESTBUNDLE', 0, 1, 1, 0, NULL, NULL, '2017-05-03 11:47:15', 0, 1, 0);

INSERT INTO `s_articles_bundles_articles` (`bundle_id`, `article_detail_id`, `quantity`, `configurable`, `bundle_group_id`, `position`) VALUES
(:bundleId, 827, 1, 0, NULL, 1);

INSERT INTO `s_articles_bundles_customergroups` (`bundle_id`, `customer_group_id`) VALUES
(:bundleId, 1);

INSERT INTO `s_articles_bundles_prices` (`bundle_id`, `customer_group_id`, `price`) VALUES
(:bundleId, '1', 10),
(:bundleId, '2', 5);

INSERT INTO `s_order_basket` (`id`, `sessionID`, `userID`, `articlename`, `articleID`, `ordernumber`, `shippingfree`, `quantity`, `price`, `netprice`, `tax_rate`, `datum`, `modus`, `esdarticle`, `partnerID`, `lastviewport`, `useragent`, `config`, `currencyFactor`) VALUES
(10999, :sessionId, 1, 'Latte Macchiato 17%', 4, 'SW10004', 0, 1, 10, 8.4033613445378, 19, '2018-04-23 16:41:56', 0, 0, '', 'checkout', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.117 Safari/537.36', '', 1),
(11000, :sessionId, 1, 'Bundle discount', 0, 'SWTESTBUNDLE', 0, 1, -0.5, 0, 19, '0000-00-00 00:00:00', 10, 0, '', 'checkout', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.81 Safari/537.36', '', 1),
(12500, :sessionId, 1, 'Spachtelmasse', 272, 'SW10239', 0, 1, 20, 16.806722689076, 19, '2017-05-03 15:22:39', 0, 0, '', 'checkout', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.81 Safari/537.36', '', 1),
(:basketId, :sessionId, 1, 'Strandtuch \"Ibiza\"', 178, 'SW10178', 0, 1, 20, 16.806722689076, 19, '2017-05-03 15:22:39', 0, 0, '', 'checkout', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.81 Safari/537.36', '', 1),
(12612, :sessionId, 0, 'Gutschein 10 %', 2, 'GUTPROZ', 0, 1, -4, -3.361, 19, '2018-04-24 08:24:08', 2, 0, '', 'checkout', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.117 Safari/537.36', '', 1);

INSERT INTO `s_order_basket_attributes` (`basketID`, `attribute1`, `attribute2`, `attribute3`, `attribute4`, `attribute5`, `attribute6`, `bundle_id`, `bundle_article_ordernumber`, `bundle_package_id`) VALUES
(11000, NULL, NULL, NULL, NULL, NULL, NULL, :bundleId, 'SW10178', 100),
(12500, NULL, NULL, NULL, NULL, NULL, NULL, :bundleId, 'SW10178', 100),
(:basketId, NULL, NULL, NULL, NULL, NULL, NULL, :bundleId, 'SW10178', 100);
