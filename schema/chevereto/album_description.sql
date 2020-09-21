create table album_description
(
	album_id int unsigned not null,
	description mediumtext not null,
	constraint album_description_album_id_fk
		foreign key (album_id) references album (id)
);

create fulltext index album_description_description_index
	on album_description (description);

