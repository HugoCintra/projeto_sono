CREATE DATABASE IF NOT EXISTS administrador_agenda_sono;
USE administrador_agenda_sono;

CREATE TABLE IF NOT EXISTS usuarios
(
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome          VARCHAR(120) NOT NULL,
    email         VARCHAR(190) NOT NULL UNIQUE,
    senha_hash    VARCHAR(255) NOT NULL,
    fuso_horario  VARCHAR(64)  NOT NULL DEFAULT 'America/Sao_Paulo',
    criado_em     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS planejamentos_sono
(
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id   BIGINT UNSIGNED NOT NULL,
    hora_dormir  TIME      NOT NULL,
    hora_acordar TIME      NOT NULL,
    valido_desde DATE      NOT NULL,
    valido_ate   DATE NULL,
    ativo        TINYINT(1) NOT NULL DEFAULT 1,
    criado_em    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_plan_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE,
    CONSTRAINT chk_plan_periodo CHECK (valido_ate IS NULL OR valido_ate >= valido_desde),
    INDEX        idx_plan_usuario_ativo (usuario_id,ativo,valido_desde)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS planejamentos_dias
(
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    planejamento_id BIGINT UNSIGNED NOT NULL,
    dia_semana      TINYINT UNSIGNED NOT NULL,
    CONSTRAINT fk_dias_plan FOREIGN KEY (planejamento_id) REFERENCES planejamentos_sono (id) ON DELETE CASCADE,
    CONSTRAINT chk_dia_semana CHECK (dia_semana BETWEEN 1 AND 7),
    UNIQUE KEY uq_plan_dia (planejamento_id,dia_semana)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS excecoes_agenda
(
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id   BIGINT UNSIGNED NOT NULL,
    data_inicio  DATE      NOT NULL,
    cancelada    TINYINT(1) NOT NULL DEFAULT 0,
    hora_dormir  TIME NULL,
    hora_acordar TIME NULL,
    criado_em    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_excecao_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE,
    CONSTRAINT chk_excecao_horarios CHECK ((cancelada = 1 AND hora_dormir IS NULL AND hora_acordar IS NULL) OR
                                           (cancelada = 0 AND hora_dormir IS NOT NULL AND hora_acordar IS NOT NULL)),
    UNIQUE KEY uq_excecao_data (usuario_id,data_inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS registros_sono
(
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id    BIGINT UNSIGNED NOT NULL,
    inicio_em     DATETIME  NOT NULL,
    fim_em        DATETIME  NOT NULL,
    observacao    TEXT NULL,
    qualidade     TINYINT UNSIGNED NULL,
    criado_em     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_registro_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE,
    CONSTRAINT chk_registro_intervalo CHECK (fim_em > inicio_em),
    CONSTRAINT chk_registro_qualidade CHECK (qualidade IS NULL OR qualidade BETWEEN 1 AND 5),
    INDEX         idx_reg_usuario_inicio (usuario_id,inicio_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
