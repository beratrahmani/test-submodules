INSERT INTO `s_order_basket_saved` (`cookie_value`, `user_id`, `shop_id`, `expire`, `modified`, `name`, `published`) VALUES
('oldSessionId', -1, 1, '2222-03-01', '0000-00-00 00:00:00', NULL, NULL);

INSERT INTO `s_order_basket_saved_items` (`basket_id`, `article_ordernumber`, `quantity`) VALUES
(LAST_INSERT_ID(), 'SW10002.3', 1);