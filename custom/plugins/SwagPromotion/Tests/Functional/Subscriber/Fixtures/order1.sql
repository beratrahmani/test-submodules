INSERT INTO `s_order` (`id`, `ordernumber`, `userID`, `invoice_amount`, `invoice_amount_net`, `invoice_shipping`, `invoice_shipping_net`, `ordertime`, `status`, `cleared`, `paymentID`, `transactionID`, `comment`, `customercomment`, `internalcomment`, `net`, `taxfree`, `partnerID`, `temporaryID`, `referer`, `cleareddate`, `trackingcode`, `language`, `dispatchID`, `currency`, `currencyFactor`, `subshopID`, `remote_addr`, `deviceType`) VALUES
(590000, '20003005', 1, 39.95, 33.57, 0, 0, '2017-03-24 12:34:27', 0, 17, 5, '', '', '', '', 0, 0, '', '', '', NULL, '', '1', 9, 'EUR', 1, 1, '172.18.0.1', 'desktop');

INSERT INTO `s_order_attributes` (`id`, `orderID`, `attribute1`, `attribute2`, `attribute3`, `attribute4`, `attribute5`, `attribute6`) VALUES
(40000, 590000, NULL, NULL, NULL, NULL, NULL, NULL);

INSERT INTO `s_order_details` (`id`, `orderID`, `ordernumber`, `articleID`, `articleordernumber`, `price`, `quantity`, `name`, `status`, `shipped`, `shippedgroup`, `releasedate`, `modus`, `esdarticle`, `taxID`, `tax_rate`, `config`, `ean`, `unit`, `pack_unit`) VALUES
(209000, 590000, '20003005', 170, 'SW10170', 39.95, 1, 'Sonnenbrille "Red"', 0, 0, 0, '0000-00-00', 0, 0, 1, 19, '', '', '', ''),
(210000, 590000, '20003005', 178, 'SW10178', 19.95, 1, 'Strandtuch "Ibiza"', 0, 0, 0, '0000-00-00', 0, 0, 1, 19, '', '', '', 'Stück'),
(211000, 590000, '20003005', 0, 'promo_gratisartikel', -19.95, 1, 'Meine neue Promotion', 0, 0, 0, '0000-00-00', 4, 0, 0, 19, '', '', '', '');

INSERT INTO `s_order_details_attributes` (`id`, `detailID`, `attribute1`, `attribute2`, `attribute3`, `attribute4`, `attribute5`, `attribute6`) VALUES
(120000, 209, '', NULL, NULL, NULL, NULL, NULL),
(130000, 210, '', NULL, NULL, NULL, NULL, NULL),
(140000, 211, NULL, NULL, NULL, NULL, NULL, NULL);
