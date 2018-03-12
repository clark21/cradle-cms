DROP TABLE IF EXISTS `profile`;

CREATE TABLE `user` (
  `profile_id` int(10) UNSIGNED NOT NULL,
  `profile_name` varchar(255) NOT NULL,
  `profile_active` int(1) UNSIGNED NOT NULL DEFAULT '1',
  `profile_created` datetime NOT NULL,
  `profile_updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `profile`
--
ALTER TABLE `profile`
  ADD PRIMARY KEY (`profile_id`),
  ADD KEY `profile_active` (`profile_active`),
  ADD KEY `profile_created` (`profile_created`),
  ADD KEY `profile_updated` (`profile_updated`),
  ADD KEY `profile_name` (`profile_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `profile`
--
ALTER TABLE `profile`
  MODIFY `profile_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
