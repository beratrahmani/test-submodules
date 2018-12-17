INSERT INTO `s_order_basket` (`id`, `sessionID`, `userID`, `articlename`, `articleID`, `ordernumber`, `shippingfree`, `quantity`, `price`, `netprice`, `tax_rate`, `datum`, `modus`, `esdarticle`, `partnerID`, `lastviewport`, `useragent`, `config`, `currencyFactor`) VALUES
(13128, 'sessionID', 0, 'Spachtelmasse', 272, 'SW10239', 0, 1, 18.99, 15.957983193277, 19, '2017-03-03 14:30:49', 0, 0, '', 'detail', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36', '', 1),
(13129, 'sessionID', 0, 'Mehrzwecknudeln', 39, 'SW10038', 0, 1, 14.99, 12.596638655462, 19, '2017-03-03 14:30:49', 0, 0, '', 'detail', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36', '', 1),
(13130, 'sessionID', 0, 'Esslack', 37, 'SW10036', 0, 1, 23.99, 20.159663865546, 19, '2017-03-03 14:30:49', 0, 0, '', 'detail', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36', '', 1),
(13131, 'sessionID', 0, 'Pralinen-Backform', 82, 'SW10083', 0, 1, 7.99, 6.7142857142857, 19, '2017-03-03 14:30:49', 0, 0, '', 'detail', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36', '', 1),
(13132, 'sessionID', 0, 'Bundle discount', 0, '08154711', 0, 1, -6.59617, -5.543, 19, '0000-00-00 00:00:00', 10, 0, '', 'detail', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36', '', 1),
(13134, 'sessionID', 0, 'Strandtuch in mehreren Farben blau', 179, 'SW10179.1', 0, 1, 44.99, 37.806722689076, 19, '2017-03-03 14:31:11', 0, 0, '', '', '', '', 1),
(13135, 'sessionID', 0, 'Warenkorbrabatt', 0, 'SHIPPINGDISCOUNT', 0, 1, -2, -1.68, 19, '2017-03-03 14:31:11', 4, 0, '', '', '', '', 1);

INSERT INTO `s_order_basket_attributes` (`id`, `basketID`, `attribute1`, `attribute2`, `attribute3`, `attribute4`, `attribute5`, `attribute6`, `bundle_id`) VALUES
(21333, 13128, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(21433, 13129, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(21533, 13130, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(21633, 13131, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(21733, 13132, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(21833, 13134, '', NULL, NULL, NULL, NULL, NULL, NULL);