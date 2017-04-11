CREATE DATABASE `wssa_db` CHARACTER SET utf8 COLLATE utf8_general_ci;
GRANT ALL ON `wssa_db`.* TO `wssa_db_usr`@localhost IDENTIFIED BY 'lF4eP3UQEzP';

CREATE TABLE `ws_logs_onlapps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_request_time` int(11) NOT NULL DEFAULT '0',
  `api_version` decimal(10,2) NOT NULL DEFAULT '1.00',
  `api_resource` varchar(100) NOT NULL DEFAULT '',
  `api_resource_id` int(11) NOT NULL DEFAULT '0',
  `api_args` varchar(300) NOT NULL DEFAULT '',
  `api_response_type` int(1) NOT NULL DEFAULT '0',
  `api_gzoutput` int(1) NOT NULL DEFAULT '0',
  `api_call_url` varchar(300) NOT NULL DEFAULT '',
  `api_remote_ip` varchar(20) NOT NULL DEFAULT '',
  `api_remote_ua` varchar(300) NOT NULL DEFAULT '',
  `api_nonce_hash` varchar(300) NOT NULL DEFAULT '',
  `query_sql` text NOT NULL,
  `server_time` varchar(300) NOT NULL DEFAULT '',
  `authenticated` int(1) NOT NULL DEFAULT '0',
  `user_key` int(11) NOT NULL DEFAULT '0',
  `user_name` varchar(30) NOT NULL DEFAULT '',
  `client_name` varchar(30) NOT NULL DEFAULT '',
  `client_api_state` int(11) NOT NULL DEFAULT '0',
  `input_file` varchar(200) NOT NULL DEFAULT '',
  `output_file` varchar(200) NOT NULL DEFAULT '',
  `api_method` varchar(10) NOT NULL DEFAULT '',
  `public_key` varchar(16) NOT NULL DEFAULT '',
  `error` int(1) NOT NULL DEFAULT '0',
  `error_msg` varchar(200) NOT NULL DEFAULT '',
  `record_count` int(11) NOT NULL DEFAULT '0',
  `fieldset` varchar(200) NOT NULL DEFAULT '',
  `request_state` int(2) NOT NULL DEFAULT '0',
  `request_initd` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `request_trmd` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `request_drtn` varchar(20) NOT NULL DEFAULT '',
  `rkey_p5` varchar(16) NOT NULL DEFAULT '',
  `rkey_p6` varchar(24) NOT NULL DEFAULT '',
  `rkey_p7` varchar(32) NOT NULL DEFAULT '',
  `log_file_name` varchar(80) NOT NULL DEFAULT '',
  `log_file_path` varchar(200) NOT NULL DEFAULT '',
  `log_file_emailed` int(1) NOT NULL DEFAULT '0',
  `log_file_email_recipients` varchar(200) NOT NULL DEFAULT '',
  `log_file_contents` text NOT NULL,
  `creation_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `update_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `creation_user_id` varchar(100) NOT NULL DEFAULT '',
  `update_user_id` varchar(100) NOT NULL DEFAULT '',
  `status` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


