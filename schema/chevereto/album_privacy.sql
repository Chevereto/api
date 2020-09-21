create table album_privacy
(
	album_id int unsigned not null,
	album_privacy_value_id int unsigned not null,
	constraint album_privacy_album_id_fk
		foreign key (album_id) references album (id),
	constraint album_privacy_album_privacy_value_id_fk
		foreign key (album_privacy_value_id) references album_privacy_value (id)
);

