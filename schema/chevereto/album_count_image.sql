create table album_count_image
(
	album_id int unsigned not null,
	count_image int unsigned not null,
	constraint album_count_image_album_id_fk
		foreign key (album_id) references album (id)
);

