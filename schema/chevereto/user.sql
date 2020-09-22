create table user
(
	id int unsigned auto_increment,
	username varchar(255) not null,
	name varchar(255) not null,
	constraint user_id_uindex
		unique (id),
	constraint user_username_uindex
		unique (username)
);

create index user_username_index
	on user (username);

create fulltext index user_username_name_index
	on user (username, name);

alter table user
	add primary key (id);

