INSERT INTO `s_order_basket` (`sessionID`, `userID`, `articlename`, `articleID`, `ordernumber`, `shippingfree`, `quantity`, `price`, `netprice`, `tax_rate`, `datum`, `modus`, `esdarticle`, `partnerID`, `lastviewport`, `useragent`, `config`, `currencyFactor`) VALUES
('sessionId', 1, 'Strandtuch "Ibiza"', 178, 'SW10178', 0, 1, 19.95, 16.764705882353, 19, '2017-03-02 10:55:55', 0, 0, '', 'account', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36', '', 1),
('sessionId', 1, 'Warenkorbrabatt', 0, 'SHIPPINGDISCOUNT', 0, 1, -2, -1.68, 19, '2017-03-02 10:56:04', 4, 0, '', 'account', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36', '', 1);

SET @lastId = LAST_INSERT_ID();

INSERT INTO `s_order_basket_attributes` (basketID)
VALUES (@lastId);
