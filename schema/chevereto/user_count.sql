create table user_count
(
	user_id int unsigned not null,
	count_album int unsigned default 0 not null,
	count_follower int unsigned default 0 not null,
	count_following int unsigned default 0 not null,
	count_image int unsigned default 0 not null,
	count_like int unsigned default 0 not null comment 'likes given by other users to user_id',
	count_liked int unsigned default 0 not null comment 'likes made from user_id to other users',
	constraint user_count_user_id_fk
		unique (user_id),
	constraint user_count_user_id_fk
		foreign key (user_id) references user (id)
);

create index user_count_count_album_index
	on user_count (count_album);

create index user_count_count_combo_index
	on user_count (count_like, count_liked, count_image, count_follower);

create index user_count_count_follower_index
	on user_count (count_follower);

create index user_count_count_following_index
	on user_count (count_following);

create index user_count_count_image_index
	on user_count (count_image);

create index user_count_count_like_index
	on user_count (count_like);

create index user_count_count_liked_index
	on user_count (count_liked);

alter table user_count
	add primary key (user_id);

