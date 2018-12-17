INSERT INTO `s_order_basket` (
  `id`,
  `sessionID`,
  `userID`,
  `articlename`,
  `articleID`,
  `ordernumber`,
  `shippingfree`,
  `quantity`,
  `price`,
  `netprice`,
  `tax_rate`,
  `datum`,
  `modus`,
  `esdarticle`,
  `partnerID`,
  `lastviewport`,
  `useragent`,
  `config`,
  `currencyFactor`
)
VALUES
  (12345, 'test-session', 1, 'Cigar Special 40%', 6, 'SW10006', 0, 2, 35.95, 30.210084033613, 19, '2017-06-27 16:03:42', 0, 0, '', 'checkout', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.109 Safari/537.36', '', 1),
  (123456, 'test-session', 1, 'Strandtuch "Ibiza"', 178, 'SW10178', 0, 1, 19.95, 16.764705882353, 19, '2017-06-27 16:03:42', 0, 0, '', 'checkout', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.109 Safari/537.36', '', 1)