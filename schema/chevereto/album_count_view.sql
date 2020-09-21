create table album_count_view
(
	album_id int unsigned not null,
	count_view int unsigned not null,
	constraint album_count_view_album_id_fk
		foreign key (album_id) references album (id)
);

