INSERT INTO `s_order_basket_saved` (`cookie_value`, `user_id`, `shop_id`, `expire`, `modified`, `name`, `published`) VALUES
('testCookieValue', 1, 1, '2018-03-06', '2017-03-06 14:07:17', 'sdfsf', 0);

INSERT INTO `s_order_basket_saved_items` (`basket_id`, `article_ordernumber`, `quantity`) VALUES
(LAST_INSERT_ID(), 'SW10083', 1);