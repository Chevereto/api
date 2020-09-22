create table album_datetime
(
	album_id int unsigned not null,
	datetime datetime not null,
	constraint album_datetime_album_id_fk
		unique (album_id),
	constraint album_datetime_album_id_fk
		foreign key (album_id) references album (id)
)
comment 'UTC';

create index album_datetime_datetime_index
	on album_datetime (datetime);

