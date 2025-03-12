-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 12, 2025 at 08:33 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sistema_studio`
--

-- --------------------------------------------------------

--
-- Table structure for table `argomenti`
--

CREATE TABLE `argomenti` (
  `id` int(11) NOT NULL,
  `esame_id` int(11) NOT NULL,
  `titolo` varchar(100) NOT NULL,
  `descrizione` text DEFAULT NULL,
  `livello_importanza` int(11) DEFAULT 3
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `esami`
--

CREATE TABLE `esami` (
  `id` int(11) NOT NULL,
  `piano_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `codice` varchar(20) DEFAULT NULL,
  `crediti` int(11) DEFAULT 6,
  `descrizione` text DEFAULT NULL,
  `anno` int(11) DEFAULT NULL,
  `semestre` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `esercizi`
--

CREATE TABLE `esercizi` (
  `id` int(11) NOT NULL,
  `sottoargomento_id` int(11) NOT NULL,
  `titolo` varchar(100) NOT NULL,
  `testo` text NOT NULL,
  `soluzione` text DEFAULT NULL,
  `difficolta` int(11) DEFAULT 2
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `formula_argomento`
--

CREATE TABLE `formula_argomento` (
  `formula_id` int(11) NOT NULL,
  `argomento_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `formule`
--

CREATE TABLE `formule` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `espressione` text NOT NULL,
  `descrizione` text DEFAULT NULL,
  `immagine` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `piani_di_studio`
--

CREATE TABLE `piani_di_studio` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descrizione` text DEFAULT NULL,
  `data_creazione` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) NOT NULL,
  `visibility` varchar(20) NOT NULL DEFAULT 'private'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `requisiti`
--

CREATE TABLE `requisiti` (
  `id` int(11) NOT NULL,
  `esercizio_id` int(11) NOT NULL,
  `descrizione` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sottoargomenti`
--

CREATE TABLE `sottoargomenti` (
  `id` int(11) NOT NULL,
  `argomento_id` int(11) NOT NULL,
  `titolo` varchar(100) NOT NULL,
  `descrizione` text DEFAULT NULL,
  `livello_profondita` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `argomenti`
--
ALTER TABLE `argomenti`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_argomenti_esame` (`esame_id`);

--
-- Indexes for table `esami`
--
ALTER TABLE `esami`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_esami_piano` (`piano_id`);

--
-- Indexes for table `esercizi`
--
ALTER TABLE `esercizi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_esercizi_sottoargomento` (`sottoargomento_id`);

--
-- Indexes for table `formula_argomento`
--
ALTER TABLE `formula_argomento`
  ADD PRIMARY KEY (`formula_id`,`argomento_id`),
  ADD KEY `argomento_id` (`argomento_id`);

--
-- Indexes for table `formule`
--
ALTER TABLE `formule`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `piani_di_studio`
--
ALTER TABLE `piani_di_studio`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_piani_user` (`user_id`),
  ADD KEY `idx_piani_visibility` (`visibility`);

--
-- Indexes for table `requisiti`
--
ALTER TABLE `requisiti`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_requisiti_esercizio` (`esercizio_id`);

--
-- Indexes for table `sottoargomenti`
--
ALTER TABLE `sottoargomenti`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sottoargomenti_argomento` (`argomento_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `argomenti`
--
ALTER TABLE `argomenti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `esami`
--
ALTER TABLE `esami`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `esercizi`
--
ALTER TABLE `esercizi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `formule`
--
ALTER TABLE `formule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `piani_di_studio`
--
ALTER TABLE `piani_di_studio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `requisiti`
--
ALTER TABLE `requisiti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sottoargomenti`
--
ALTER TABLE `sottoargomenti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `argomenti`
--
ALTER TABLE `argomenti`
  ADD CONSTRAINT `argomenti_ibfk_1` FOREIGN KEY (`esame_id`) REFERENCES `esami` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `esami`
--
ALTER TABLE `esami`
  ADD CONSTRAINT `esami_ibfk_1` FOREIGN KEY (`piano_id`) REFERENCES `piani_di_studio` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `esercizi`
--
ALTER TABLE `esercizi`
  ADD CONSTRAINT `esercizi_ibfk_1` FOREIGN KEY (`sottoargomento_id`) REFERENCES `sottoargomenti` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `formula_argomento`
--
ALTER TABLE `formula_argomento`
  ADD CONSTRAINT `formula_argomento_ibfk_1` FOREIGN KEY (`formula_id`) REFERENCES `formule` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `formula_argomento_ibfk_2` FOREIGN KEY (`argomento_id`) REFERENCES `argomenti` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `piani_di_studio`
--
ALTER TABLE `piani_di_studio`
  ADD CONSTRAINT `piani_di_studio_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `requisiti`
--
ALTER TABLE `requisiti`
  ADD CONSTRAINT `requisiti_ibfk_1` FOREIGN KEY (`esercizio_id`) REFERENCES `esercizi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sottoargomenti`
--
ALTER TABLE `sottoargomenti`
  ADD CONSTRAINT `sottoargomenti_ibfk_1` FOREIGN KEY (`argomento_id`) REFERENCES `argomenti` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
