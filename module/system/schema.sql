DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `user_active` int(1) UNSIGNED NOT NULL DEFAULT '1',
  `user_created` datetime NOT NULL,
  `user_updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `user_active` (`user_active`),
  ADD KEY `user_created` (`user_created`),
  ADD KEY `user_updated` (`user_updated`),
  ADD KEY `user_name` (`user_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
