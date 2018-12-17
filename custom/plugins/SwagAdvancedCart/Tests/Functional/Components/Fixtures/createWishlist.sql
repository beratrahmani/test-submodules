INSERT INTO `s_order_basket_saved` (`id`, `cookie_value`, `user_id`, `shop_id`, `expire`, `modified`, `name`, `published`) VALUES
(169111, 'customCookieValue', 1, 1, '2018-03-07', '2017-03-07 08:21:40', NULL, 0);

INSERT INTO `s_order_basket_saved_items` (`id`, `basket_id`, `article_ordernumber`, `quantity`) VALUES
(323111, 169111, 'SW10239', 1),
(324111, 169111, 'SW10038', 1),
(325111, 169111, 'SW10036', 1),
(326111, 169111, 'SW10083', 1);

INSERT INTO `s_order_basket` (`id`, `sessionID`, `userID`, `articlename`, `articleID`, `ordernumber`, `shippingfree`, `quantity`, `price`, `netprice`, `tax_rate`, `datum`, `modus`, `esdarticle`, `partnerID`, `lastviewport`, `useragent`, `config`, `currencyFactor`) VALUES
(1177111, '97fcspagi9ifeohseltkvp3u00', 1, 'Spachtelmasse', 272, 'SW10239', 0, 1, 18.99, 15.957983193277, 19, '2017-03-07 08:20:32', 0, 0, '', 'checkout', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36', '', 1),
(1178111, '97fcspagi9ifeohseltkvp3u00', 1, 'Mehrzwecknudeln', 39, 'SW10038', 0, 1, 14.99, 12.596638655462, 19, '2017-03-07 08:20:32', 0, 0, '', 'checkout', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36', '', 1),
(1179111, '97fcspagi9ifeohseltkvp3u00', 1, 'Esslack', 37, 'SW10036', 0, 1, 23.99, 20.159663865546, 19, '2017-03-07 08:20:32', 0, 0, '', 'checkout', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36', '', 1),
(1180111, '97fcspagi9ifeohseltkvp3u00', 1, 'Pralinen-Backform', 82, 'SW10083', 0, 1, 7.99, 6.7142857142857, 19, '2017-03-07 08:20:33', 0, 0, '', 'checkout', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36', '', 1),
(1185111, '97fcspagi9ifeohseltkvp3u00', 1, 'Warenkorbrabatt', 0, 'SHIPPINGDISCOUNT', 0, 1, -2, -1.68, 19, '2017-03-07 08:21:24', 4, 0, '', 'checkout', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36', '', 1);

INSERT INTO `s_order_basket_attributes` (`id`, `basketID`, `attribute1`, `attribute2`, `attribute3`, `attribute4`, `attribute5`, `attribute6`) VALUES
(231111, 1177111, NULL, NULL, NULL, NULL, NULL, NULL),
(232111, 1178111, NULL, NULL, NULL, NULL, NULL, NULL),
(233111, 1179111, NULL, NULL, NULL, NULL, NULL, NULL),
(234111, 1180111, NULL, NULL, NULL, NULL, NULL, NULL);


