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