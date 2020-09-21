create table album_user_id
(
	album_id int unsigned not null,
	user_id int unsigned not null,
	constraint album_user_id_album_id_fk
		foreign key (album_id) references album (id),
	constraint album_user_id_user_id_fk
		foreign key (user_id) references user (id)
);

