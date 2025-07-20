-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 20/07/2025 às 17:57
-- Versão do servidor: 10.11.10-MariaDB-log
-- Versão do PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `u486024982_financeirotest`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `categoria_investimentos`
--

CREATE TABLE `categoria_investimentos` (
  `id_investimentos` int(11) NOT NULL,
  `nome_categoria` varchar(255) NOT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `categoria_lancamento`
--

CREATE TABLE `categoria_lancamento` (
  `id_categoria` int(11) NOT NULL,
  `nome_categoria` varchar(100) DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `lancamento_financeiro`
--

CREATE TABLE `lancamento_financeiro` (
  `id_lancamento` int(11) NOT NULL,
  `data_registro` datetime DEFAULT current_timestamp(),
  `data_venc` date DEFAULT NULL,
  `data_pagamento` date DEFAULT NULL,
  `data_investimento` date DEFAULT NULL,
  `tipo_lancamento` enum('Receita','Despesa','Investimentos') DEFAULT NULL,
  `categoria_lancamento` int(11) DEFAULT NULL,
  `valor_lancamento` decimal(10,2) DEFAULT NULL,
  `status_lancamento` enum('Pendente','Pago','Cancelado') DEFAULT NULL,
  `observacao_lancamento` text DEFAULT NULL,
  `arquivo_lancamento` varchar(255) DEFAULT NULL,
  `recorrente` tinyint(1) DEFAULT 0,
  `id_usuario` int(11) DEFAULT NULL,
  `saldo_adiantado` enum('Sim ( Em poupança )','Nao','','') NOT NULL,
  `categoria_investimento` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `registro_pagamento`
--

CREATE TABLE `registro_pagamento` (
  `id_registro` int(11) NOT NULL,
  `tipo_registro` enum('BAIXAR','CANCELAMENTO','EXCLUSAO') DEFAULT NULL,
  `ip_origem` varchar(45) DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `data_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tarefas`
--

CREATE TABLE `tarefas` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `anexo` varchar(255) DEFAULT NULL,
  `data_tarefa` date DEFAULT NULL,
  `status` enum('Pendente','Concluída') DEFAULT 'Pendente',
  `criado_em` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `login` varchar(50) DEFAULT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `saldo_inicial` decimal(10,2) DEFAULT NULL,
  `token_recuperacao` varchar(100) DEFAULT NULL,
  `token_expira` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `categoria_investimentos`
--
ALTER TABLE `categoria_investimentos`
  ADD PRIMARY KEY (`id_investimentos`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Índices de tabela `categoria_lancamento`
--
ALTER TABLE `categoria_lancamento`
  ADD PRIMARY KEY (`id_categoria`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Índices de tabela `lancamento_financeiro`
--
ALTER TABLE `lancamento_financeiro`
  ADD PRIMARY KEY (`id_lancamento`),
  ADD KEY `categoria_lancamento` (`categoria_lancamento`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `fk_categoria_investimento` (`categoria_investimento`);

--
-- Índices de tabela `registro_pagamento`
--
ALTER TABLE `registro_pagamento`
  ADD PRIMARY KEY (`id_registro`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Índices de tabela `tarefas`
--
ALTER TABLE `tarefas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `login` (`login`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `categoria_investimentos`
--
ALTER TABLE `categoria_investimentos`
  MODIFY `id_investimentos` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `categoria_lancamento`
--
ALTER TABLE `categoria_lancamento`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de tabela `lancamento_financeiro`
--
ALTER TABLE `lancamento_financeiro`
  MODIFY `id_lancamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=163;

--
-- AUTO_INCREMENT de tabela `registro_pagamento`
--
ALTER TABLE `registro_pagamento`
  MODIFY `id_registro` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tarefas`
--
ALTER TABLE `tarefas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `categoria_investimentos`
--
ALTER TABLE `categoria_investimentos`
  ADD CONSTRAINT `categoria_investimentos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `categoria_lancamento`
--
ALTER TABLE `categoria_lancamento`
  ADD CONSTRAINT `categoria_lancamento_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Restrições para tabelas `lancamento_financeiro`
--
ALTER TABLE `lancamento_financeiro`
  ADD CONSTRAINT `fk_categoria_investimento` FOREIGN KEY (`categoria_investimento`) REFERENCES `categoria_investimentos` (`id_investimentos`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `lancamento_financeiro_ibfk_1` FOREIGN KEY (`categoria_lancamento`) REFERENCES `categoria_lancamento` (`id_categoria`),
  ADD CONSTRAINT `lancamento_financeiro_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Restrições para tabelas `registro_pagamento`
--
ALTER TABLE `registro_pagamento`
  ADD CONSTRAINT `registro_pagamento_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Restrições para tabelas `tarefas`
--
ALTER TABLE `tarefas`
  ADD CONSTRAINT `tarefas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
