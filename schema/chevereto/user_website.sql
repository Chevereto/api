create table user_website
(
	user_id int unsigned not null,
	website text not null,
	constraint user_website_user_id_fk
		foreign key (user_id) references user (id)
);

