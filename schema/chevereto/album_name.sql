create table album_name
(
	album_id int unsigned not null,
	name varchar(255) not null,
	constraint album_name_album_id_fk
		foreign key (album_id) references album (id)
);

create fulltext index album_name_name_index
	on album_name (name);

