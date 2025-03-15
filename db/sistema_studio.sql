-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 15, 2025 at 01:19 AM
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

--
-- Dumping data for table `argomenti`
--

INSERT INTO `argomenti` (`id`, `esame_id`, `titolo`, `descrizione`, `livello_importanza`) VALUES
(1, 1, 'Studio di una funzione', 'Intero procedimento dello studio di una funzione', 5),
(2, 2, 'testamento', 'testicolo', 3),
(3, 1, 'fdsafds', 'fdsafds', 3),
(4, 3, 'argomento', 'dsafd', 3);

-- --------------------------------------------------------

--
-- Table structure for table `commenti`
--

CREATE TABLE `commenti` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tipo_elemento` varchar(50) NOT NULL,
  `elemento_id` int(11) NOT NULL,
  `testo` text NOT NULL,
  `data_creazione` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_modifica` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `commenti`
--

INSERT INTO `commenti` (`id`, `user_id`, `tipo_elemento`, `elemento_id`, `testo`, `data_creazione`, `data_modifica`) VALUES
(1, 3, 'piano', 1, 'Pezzo di merda', '2025-03-15 00:16:39', NULL),
(2, 3, 'argomento', 1, 'testolo sei un babbo', '2025-03-15 00:16:39', NULL),
(3, 3, 'argomento', 1, 'testolo sei un babbo', '2025-03-15 00:16:39', NULL),
(4, 3, 'argomento', 1, 'fd', '2025-03-15 00:16:39', NULL),
(5, 3, 'piano', 2, 'dio', '2025-03-15 00:16:39', NULL),
(6, 3, 'esame', 1, 'test', '2025-03-15 00:16:39', NULL),
(7, 1, 'esame', 3, 'fdsafdsa', '2025-03-15 00:16:39', NULL),
(8, 1, 'esame', 3, 'non ho capito', '2025-03-15 00:16:39', NULL),
(9, 1, 'argomento', 4, 'fdsafdsa', '2025-03-15 00:16:39', NULL),
(10, 1, 'esercizio', 1, 'fd', '2025-03-15 00:16:39', NULL);

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

--
-- Dumping data for table `esami`
--

INSERT INTO `esami` (`id`, `piano_id`, `nome`, `codice`, `crediti`, `descrizione`, `anno`, `semestre`) VALUES
(1, 1, 'Prova Matematica', 'speranza', 4, 'Insieme di argomenti orali e pratici', NULL, NULL),
(2, 2, 'test', 'fdsa', 6, 'dfsafs', NULL, NULL),
(3, 3, 'fddfs', 'fdsa', 6, 'fdsa', NULL, NULL);

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

--
-- Dumping data for table `esercizi`
--

INSERT INTO `esercizi` (`id`, `sottoargomento_id`, `titolo`, `testo`, `soluzione`, `difficolta`) VALUES
(1, 3, 'Normal exercise', 'x - 2', 'x xsfjdsalf', 2);

-- --------------------------------------------------------

--
-- Table structure for table `esercizio_correlato`
--

CREATE TABLE `esercizio_correlato` (
  `id` int(11) NOT NULL,
  `esercizio_id` int(11) NOT NULL,
  `esercizio_correlato_id` int(11) NOT NULL,
  `tipo_relazione` varchar(50) NOT NULL,
  `data_creazione` timestamp NOT NULL DEFAULT current_timestamp()
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

--
-- Dumping data for table `piani_di_studio`
--

INSERT INTO `piani_di_studio` (`id`, `nome`, `descrizione`, `data_creazione`, `user_id`, `visibility`) VALUES
(1, '5^info', 'Piano per completare la 5^ superiore', '2025-03-15 00:16:39', 1, 'public'),
(2, 'Mio piano', 'fda', '2025-03-15 00:16:39', 3, 'public'),
(3, 'fdsa', 'fdsa', '2025-03-15 00:16:39', 1, 'private');

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
-- Table structure for table `requisito_argomento`
--

CREATE TABLE `requisito_argomento` (
  `requisito_id` int(11) NOT NULL,
  `argomento_id` int(11) NOT NULL
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

--
-- Dumping data for table `sottoargomenti`
--

INSERT INTO `sottoargomenti` (`id`, `argomento_id`, `titolo`, `descrizione`, `livello_profondita`) VALUES
(1, 1, 'Dominio', '', 2),
(2, 1, 'Simmetrie', '', 2),
(3, 1, 'Intero Studio di funzione', '', 1),
(4, 2, 'safdf', 'dsaas', 1);

-- --------------------------------------------------------

--
-- Table structure for table `sottoargomento_argomento_prerequisito`
--

CREATE TABLE `sottoargomento_argomento_prerequisito` (
  `sottoargomento_id` int(11) NOT NULL,
  `argomento_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sottoargomento_requisito`
--

CREATE TABLE `sottoargomento_requisito` (
  `id` int(11) NOT NULL,
  `sottoargomento_id` int(11) NOT NULL,
  `requisito_tipo` varchar(50) NOT NULL,
  `requisito_id` int(11) NOT NULL,
  `data_creazione` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sottoargomento_sottoargomento_prerequisito`
--

CREATE TABLE `sottoargomento_sottoargomento_prerequisito` (
  `sottoargomento_id` int(11) NOT NULL,
  `prerequisito_id` int(11) NOT NULL
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
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$GFyFe.FrTjJZfdb98WY5w.DOfLeeK4Gdfa2Wgi93BO5jRoW2zgBfG', 'admin@example.com', 'admin', '2025-03-15 00:16:39'),
(2, 'fra', '$2y$10$Q3i5LI1cz0N8fg9B1ChumeUdZbVxkrCBIsuFoI8vkzoA0e7ue5q5S', 'fraff@gmail.com', 'user', '2025-03-15 00:16:39'),
(3, 'mezze', '$2y$10$nzBf0bCklAceusPLqCU/ruifsi6vNt/zfqOBpoHtVXe2yqoTdzgcq', 'ff.@ff.it', 'user', '2025-03-15 00:16:39');

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
-- Indexes for table `commenti`
--
ALTER TABLE `commenti`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_commenti_user` (`user_id`),
  ADD KEY `idx_commenti_elemento` (`tipo_elemento`,`elemento_id`);

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
-- Indexes for table `esercizio_correlato`
--
ALTER TABLE `esercizio_correlato`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_esercizio` (`esercizio_id`),
  ADD KEY `idx_esercizio_correlato` (`esercizio_correlato_id`);

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
-- Indexes for table `requisito_argomento`
--
ALTER TABLE `requisito_argomento`
  ADD PRIMARY KEY (`requisito_id`,`argomento_id`),
  ADD KEY `argomento_id` (`argomento_id`);

--
-- Indexes for table `sottoargomenti`
--
ALTER TABLE `sottoargomenti`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sottoargomenti_argomento` (`argomento_id`);

--
-- Indexes for table `sottoargomento_argomento_prerequisito`
--
ALTER TABLE `sottoargomento_argomento_prerequisito`
  ADD PRIMARY KEY (`sottoargomento_id`,`argomento_id`),
  ADD KEY `idx_argomento` (`argomento_id`);

--
-- Indexes for table `sottoargomento_requisito`
--
ALTER TABLE `sottoargomento_requisito`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sottoargomento` (`sottoargomento_id`);

--
-- Indexes for table `sottoargomento_sottoargomento_prerequisito`
--
ALTER TABLE `sottoargomento_sottoargomento_prerequisito`
  ADD PRIMARY KEY (`sottoargomento_id`,`prerequisito_id`),
  ADD KEY `idx_prerequisito` (`prerequisito_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `commenti`
--
ALTER TABLE `commenti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `esami`
--
ALTER TABLE `esami`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `esercizi`
--
ALTER TABLE `esercizi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `esercizio_correlato`
--
ALTER TABLE `esercizio_correlato`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `requisiti`
--
ALTER TABLE `requisiti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sottoargomenti`
--
ALTER TABLE `sottoargomenti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sottoargomento_requisito`
--
ALTER TABLE `sottoargomento_requisito`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `argomenti`
--
ALTER TABLE `argomenti`
  ADD CONSTRAINT `argomenti_ibfk_1` FOREIGN KEY (`esame_id`) REFERENCES `esami` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `commenti`
--
ALTER TABLE `commenti`
  ADD CONSTRAINT `commenti_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `esercizio_correlato`
--
ALTER TABLE `esercizio_correlato`
  ADD CONSTRAINT `esercizio_correlato_ibfk_1` FOREIGN KEY (`esercizio_id`) REFERENCES `esercizi` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `esercizio_correlato_ibfk_2` FOREIGN KEY (`esercizio_correlato_id`) REFERENCES `esercizi` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `requisito_argomento`
--
ALTER TABLE `requisito_argomento`
  ADD CONSTRAINT `requisito_argomento_ibfk_1` FOREIGN KEY (`requisito_id`) REFERENCES `requisiti` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `requisito_argomento_ibfk_2` FOREIGN KEY (`argomento_id`) REFERENCES `argomenti` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sottoargomenti`
--
ALTER TABLE `sottoargomenti`
  ADD CONSTRAINT `sottoargomenti_ibfk_1` FOREIGN KEY (`argomento_id`) REFERENCES `argomenti` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sottoargomento_argomento_prerequisito`
--
ALTER TABLE `sottoargomento_argomento_prerequisito`
  ADD CONSTRAINT `sottoargomento_argomento_prerequisito_ibfk_1` FOREIGN KEY (`sottoargomento_id`) REFERENCES `sottoargomenti` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sottoargomento_argomento_prerequisito_ibfk_2` FOREIGN KEY (`argomento_id`) REFERENCES `argomenti` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sottoargomento_requisito`
--
ALTER TABLE `sottoargomento_requisito`
  ADD CONSTRAINT `sottoargomento_requisito_ibfk_1` FOREIGN KEY (`sottoargomento_id`) REFERENCES `sottoargomenti` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sottoargomento_sottoargomento_prerequisito`
--
ALTER TABLE `sottoargomento_sottoargomento_prerequisito`
  ADD CONSTRAINT `sottoargomento_sottoargomento_prerequisito_ibfk_1` FOREIGN KEY (`sottoargomento_id`) REFERENCES `sottoargomenti` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sottoargomento_sottoargomento_prerequisito_ibfk_2` FOREIGN KEY (`prerequisito_id`) REFERENCES `sottoargomenti` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
