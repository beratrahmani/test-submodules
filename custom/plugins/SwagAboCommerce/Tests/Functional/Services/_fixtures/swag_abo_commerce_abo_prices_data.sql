INSERT INTO `s_plugin_swag_abo_commerce_articles` (`id`,
                                                   `article_id`,
                                                   `active`,
                                                   `exclusive`,
                                                   `ordernumber`,
                                                   `min_duration`,
                                                   `max_duration`,
                                                   `duration_unit`,
                                                   `min_delivery_interval`,
                                                   `max_delivery_interval`,
                                                   `delivery_interval_unit`,
                                                   `endless_subscription`,
                                                   `period_of_notice_interval`,
                                                   `period_of_notice_unit`,
                                                   `direct_termination`,
                                                   `limited`,
                                                   `max_units_per_week`,
                                                   `description`)
VALUES (1, 2, 1, 1, 'SW10002.3.ABO', NULL, NULL, '', 2, 4, 'weeks', 1, 3, 'months', 0, 0, 50, '');

INSERT INTO `s_plugin_swag_abo_commerce_prices` (`id`,
                                                 `customer_group_id`,
                                                 `abo_article_id`,
                                                 `duration_from`,
                                                 `discount_absolute`,
                                                 `discount_percent`)
VALUES (3, 1, 1, 1, 0, 10),
       (10, 1, 1, 3, 12.605042016807, 0),
       (11, 1, 1, 4, 0, 22.5),
       (12, 1, 1, 5, 13.865546218487, 0);
