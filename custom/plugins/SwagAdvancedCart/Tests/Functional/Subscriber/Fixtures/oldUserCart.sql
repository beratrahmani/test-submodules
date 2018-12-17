INSERT INTO `s_order_basket_saved` (`cookie_value`, `user_id`, `shop_id`, `expire`, `modified`, `name`, `published`)
VALUES
  ('cookieValue', 1, 1, '2222-03-01', '0000-00-00 00:00:00', NULL, NULL);

INSERT INTO `s_order_basket_saved_items` (`basket_id`, `article_ordernumber`, `quantity`) VALUES
  (LAST_INSERT_ID(), 'SW10178', 1);

INSERT INTO `s_order_basket` (sessionID, `userID`, `articlename`, `articleID`, `ordernumber`, `shippingfree`, `quantity`, `price`, `netprice`, `tax_rate`, `datum`, `modus`, `esdarticle`, `partnerID`, `lastviewport`, `useragent`, `config`, `currencyFactor`)
VALUES
  ('sessionId', 0, 'Strandtuch \"Ibiza\"', 178, 'SW10178', 0, 1, 19.95, 16.764705882353, 19, '2012-08-31 11:16:04', 0,
   0, '', 'index', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:14.0) Gecko/20100101 Firefox/14.0.1', '', 1);

INSERT INTO `s_user_attributes` (`id`, `userID`, `swag_advanced_cart_cookie_name_hash`) VALUES ('1', '1', 'CookieName');