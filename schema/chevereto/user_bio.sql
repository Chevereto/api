create table user_bio
(
	user_id int unsigned not null,
	bio text not null,
	constraint user_bio_user_id_fk
		unique (user_id),
	constraint user_bio_user_id_fk
		foreign key (user_id) references user (id)
);

