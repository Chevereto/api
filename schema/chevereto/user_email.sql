create table user_email
(
	user_id int unsigned not null,
	email varchar(320) not null,
	constraint user_email_email_uindex
		unique (email),
	constraint user_email_user_id_fk
		unique (user_id),
	constraint user_email_user_id_fk
		foreign key (user_id) references user (id)
);

