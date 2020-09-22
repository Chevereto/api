create table album
(
	id int unsigned auto_increment,
	name varchar(255) not null,
	constraint album_id_uindex
		unique (id)
);

create index album_name_index
	on album (name);

alter table album
	add primary key (id);

