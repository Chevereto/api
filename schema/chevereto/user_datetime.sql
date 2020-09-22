create table user_datetime
(
	user_id int unsigned not null,
	datetime datetime not null,
	constraint user_datetime_user_id_fk
		unique (user_id),
	constraint user_datetime_user_id_fk
		foreign key (user_id) references user (id)
)
comment 'UTC';

create index user_datetime_datetime_index
	on user_datetime (datetime);

