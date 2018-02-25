--
-- Dumping data for table `role`
--

INSERT INTO `role` (`role_id`, `role_name`, `role_permissions`, `role_type`, `role_created`, `role_updated`) VALUES
(1, 'Super Admin', '["user:create", "user:update", "user:remove", "user:restore", "auth:create", "auth:update", "auth:remove", "auth:restore", "role:create", "role:update", "role:remove", "role:restore", "schema:create", "schema:update", "schema:remove", "schema:restore", "object:create", "object:update", "object:remove", "object:restore", "object:export", "object:import"]', 'admin', '2018-02-03 01:45:16', '2018-02-03 01:45:16');
