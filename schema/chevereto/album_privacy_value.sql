create table album_privacy_value
(
	id int unsigned auto_increment,
	value varchar(100) not null,
	constraint album_privacy_value_id_uindex
		unique (id)
);

alter table album_privacy_value
	add primary key (id);

