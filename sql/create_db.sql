DROP TABLE IF EXISTS Animals;
DROP TABLE IF EXISTS Users;
DROP TABLE IF EXISTS Resources;

CREATE TABLE IF NOT EXISTS Animals (
	id INT AUTO_INCREMENT, 
	name VARCHAR(80) UNIQUE,
	info_url VARCHAR(255),
	image_url VARCHAR(255),
	PRIMARY KEY(id)
);

CREATE TABLE IF NOT EXISTS Resources (
	id INT AUTO_INCREMENT, 
	name VARCHAR(80) UNIQUE,
	url VARCHAR(255) UNIQUE,
	description VARCHAR(255),
	PRIMARY KEY(id)
);

CREATE TABLE IF NOT EXISTS Users (
	id INT AUTO_INCREMENT,
	username VARCHAR(80) UNIQUE,
	password VARCHAR(255),
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(id)
);

INSERT INTO Animals (name, info_url, image_url)
VALUES (
'squirrel', 'squirrel.txt', 'squirrel.jpg'
);

INSERT INTO Animals (name, info_url, image_url)
VALUES (
'frog', 'frog.txt', 'frog.jpg'
);

INSERT INTO Animals (name, info_url, image_url)
VALUES (
'bear', 'bear.txt', 'bear.jpg'
);

INSERT INTO Animals (name, info_url, image_url)
VALUES (
'american robin', 'american_robin.txt', 'american_robin.jpg'
);
