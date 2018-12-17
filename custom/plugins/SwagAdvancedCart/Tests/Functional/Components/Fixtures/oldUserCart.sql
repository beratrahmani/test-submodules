INSERT INTO `s_order_basket_saved` (`cookie_value`, `user_id`, `shop_id`, `expire`, `modified`, `name`, `published`) VALUES
  ('cookieValue', -1, 1, '2222-03-01', NOW(), NULL, NULL);

SET @lastId = LAST_INSERT_ID();

INSERT INTO `s_order_basket_saved_items` (`basket_id`, `article_ordernumber`, `quantity`) VALUES
  (@lastId, 'SW10178', 1);

INSERT INTO `s_order_basket` (`sessionID`, `userID`, `articlename`, `articleID`, `ordernumber`, `shippingfree`, `quantity`, `price`, `netprice`, `tax_rate`, `datum`, `modus`, `esdarticle`, `partnerID`, `lastviewport`, `useragent`, `config`, `currencyFactor`) VALUES
  ('sessionId', 1, 'Spachtelmasse', 178, 'SW10178', 0, 1, 18.99, 15.957983193277, 19, '2017-03-07 08:20:32', 0, 0, '', 'checkout', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36', '', 1);

SET @lastId = LAST_INSERT_ID();

INSERT INTO `s_order_basket_attributes` (basketID)
VALUES (@lastId);
