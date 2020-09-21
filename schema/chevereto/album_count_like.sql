create table album_count_like
(
	album_id int unsigned not null,
	count_like int unsigned null,
	constraint album_count_like_album_id_fk
		foreign key (album_id) references album (id)
);

create index album_count_like_count_like_index
	on album_count_like (count_like);

