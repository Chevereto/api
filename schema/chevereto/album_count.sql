create table album_count
(
	album_id int unsigned not null,
	count_image int unsigned not null,
	count_like int unsigned null,
	count_view int unsigned not null,
	constraint album_count_album_id_fk
		unique (album_id),
	constraint album_count_album_id_fk
		foreign key (album_id) references album (id)
);

create index album_count_count_image_index
	on album_count (count_image);

create index album_count_count_like_index
	on album_count (count_like);

create index album_count_count_view_index
	on album_count (count_view);

