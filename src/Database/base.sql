CREATE SEQUENCE email_id_seq;

CREATE TABLE email (
	id integer PRIMARY KEY DEFAULT nextval('email_id_seq'),
	status integer NOT NULL,
	send_to varchar(255) NOT NULL,
	from_name varchar(255),
	subject varchar(255),
	message text
);
