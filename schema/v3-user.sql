DROP TABLE IF EXISTS `%table_prefix%users`;
namespace Chevereto;
CREATE TABLE `%table_prefix%users` (
  `user_avatar_filename` varchar(255) DEFAULT NULL,
  `user_facebook_username` varchar(255) DEFAULT NULL,
  `user_twitter_username` varchar(255) DEFAULT NULL,
  `user_background_filename` varchar(255) DEFAULT NULL,
  `user_timezone` varchar(255) NOT NULL,
  `user_language` varchar(255) DEFAULT NULL,
  `user_status` enum('valid','awaiting-confirmation','awaiting-email','banned') NOT NULL,
  `user_is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `user_is_manager` tinyint(1) NOT NULL DEFAULT '0',
  `user_is_private` tinyint(1) NOT NULL DEFAULT '0',
  `user_is_dark_mode` tinyint(1) NOT NULL DEFAULT '0',
  `user_newsletter_subscribe` tinyint(1) NOT NULL DEFAULT '1',
  `user_show_nsfw_listings` tinyint(1) NOT NULL DEFAULT '0',
  `user_image_keep_exif` tinyint(1) NOT NULL DEFAULT '1',
  `user_image_expiration` varchar(191) DEFAULT NULL,
  `user_content_views` bigint(32) NOT NULL DEFAULT '0',
  `user_notifications_unread` bigint(32) NOT NULL DEFAULT '0',
  KEY `user_status` (`user_status`),
  KEY `user_is_admin` (`user_is_admin`),
  KEY `user_is_manager` (`user_is_manager`),
  KEY `user_is_private` (`user_is_private`),
  KEY `user_is_dark_mode` (`user_is_dark_mode`),
  KEY `user_newsletter_subscribe` (`user_newsletter_subscribe`),
  KEY `user_show_nsfw_listings` (`user_show_nsfw_listings`),
  KEY `user_image_keep_exif` (`user_image_keep_exif`),
  KEY `user_image_expiration` (`user_image_expiration`),
  KEY `user_content_views` (`user_content_views`),

) ENGINE=%table_engine% DEFAULT CHARSET=utf8mb4;

SELECT u.user_name, u.user_username
LEFT JOIN user u on i.user_id = u.user_id