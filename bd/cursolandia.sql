CREATE DATABASE cursolandia;

USE cursolandia;


CREATE TABLE Usuario (
    idUsuario INT NOT NULL AUTO_INCREMENT,
    Nombre VARCHAR(20) NOT NULL,
    Apellido VARCHAR(20) NOT NULL,
    FechaNacimiento DATE,
    email VARCHAR(100) NOT NULL UNIQUE,
    NombreUsuario VARCHAR(20) NOT NULL UNIQUE,
    Contraseña VARCHAR(255) NOT NULL,
    FotoPerfil VARCHAR(255),
    PlanTipo VARCHAR(20) NOT NULL DEFAULT 'gratuito',
    Antecedentes VARCHAR (500),
    FechaVencimiento DATE,
    Rol_Usuario VARCHAR (20) NOT NULL DEFAULT 'usuario',
    PRIMARY KEY (idUsuario)
);

CREATE TABLE Interes (
    idInteres INT NOT NULL AUTO_INCREMENT,
    NombreInteres VARCHAR (20) NOT NULL UNIQUE,
    PRIMARY KEY (idInteres)

);


INSERT INTO Interes (NombreInteres)
VALUES ('Aficiones');

INSERT INTO Interes (NombreInteres)
VALUES ('Musica');

INSERT INTO Interes (NombreInteres)
VALUES ('Tecnologia');

INSERT INTO Interes (NombreInteres)
VALUES ('Salud');

INSERT INTO Interes (NombreInteres)
VALUES ('Deportes');


CREATE TABLE UsuarioInteres (

    idUsuario INT NOT NULL,

    idInteres INT NOT NULL,

    PRIMARY KEY (idUsuario, idInteres),

    FOREIGN KEY (idUsuario)
        REFERENCES Usuario(idUsuario),

    FOREIGN KEY (idInteres)
        REFERENCES Interes(idInteres)

);



//TABLA DE NOTIFICACIONES

CREATE TABLE notificacion (
    idNotificacion INT NOT NULL AUTO_INCREMENT,
    idUsuario INT NOT NULL,
    idUsuarioActor INT,
    idCurso INT,
    idPregunta INT,
    idRespuesta INT,
    Estado TINYINT NOT NULL,
    TextoReferencia VARCHAR(255),
    Leida TINYINT(1) NOT NULL DEFAULT 0,
    Fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (idNotificacion),

    FOREIGN KEY (idUsuario)
    REFERENCES usuario(idUsuario),

    FOREIGN KEY (idUsuarioActor)
    REFERENCES usuario(idUsuario)
);

CREATE TABLE configuracionnotificacion (
    idUsuario INT NOT NULL,

    PreguntasCreadas TINYINT(1) NOT NULL DEFAULT 1,
    Respuestas TINYINT(1) NOT NULL DEFAULT 1,
    PreguntasCerradas TINYINT(1) NOT NULL DEFAULT 1,
    RespuestasDestacadas TINYINT(1) NOT NULL DEFAULT 1,
    PreguntasEliminadas TINYINT(1) NOT NULL DEFAULT 1,

    PRIMARY KEY (idUsuario),

    FOREIGN KEY (idUsuario)
    REFERENCES usuario(idUsuario)
);